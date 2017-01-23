<?php


namespace webtoolsnz\scheduler;

use webtoolsnz\scheduler\events\TaskEvent;
use webtoolsnz\scheduler\models\SchedulerTask;
use yii\helpers\StringHelper;
use Cron\CronExpression;

/**
 * Class Task
 * @package webtoolsnz\scheduler
 */
abstract class Task extends \yii\base\Component
{
    const EVENT_BEFORE_RUN = 'TaskBeforeRun';
    const EVENT_AFTER_RUN = 'TaskAfterRun';

    /**
     * @var bool create a database lock to ensure the task only runs once
     */
    public $databaseLock = true;

    /**
     * Exception raised during run (if any)
     *
     * @var \Exception|null
     */
    public $exception;

    /**
     * Brief description of the task.
     *
     * @var String
     */
    public $description;

    /**
     * The cron expression that determines how often this task should run.
     *
     * @var String
     */
    public $schedule;

    /**
     * Active flag allows you to set the task to inactive (meaning it will not run)
     *
     * @var bool
     */
    public $active = true;

    /**
     * How many seconds after due date to wait until the task becomes overdue and is re-run.
     * This should be set to at least 2x the amount of time the task takes to run as the task will be restarted.
     *
     * @var int
     */
    public $overdueThreshold = 3600;

    /**
     * @var null|SchedulerTask
     */
    private $_model;

    public function init()
    {
        parent::init();

        $lockName = 'TaskLock'.\yii\helpers\Inflector::camelize(self::className());
        \yii\base\Event::on(self::className(), self::EVENT_BEFORE_RUN, function ($event) use ($lockName) {
            /* @var $event TaskEvent */
            $db = \Yii::$app->db;
            $result = $db->createCommand("GET_LOCK(:lockname, 1)", [':lockname' => $lockName])->queryScalar();

            if (!$result) {
                // we didn't get the lock which means the task is still running
                $event->cancel = true;
            }
        });
        \yii\base\Event::on(self::className(), self::EVENT_AFTER_RUN, function ($event) use ($lockName) {
            // release the lock
            /* @var $event TaskEvent */
            $db = \Yii::$app->db;
            $db->createCommand("RELEASE_LOCK(:lockname, 1)", [':lockname' => $lockName])->queryScalar();
        });
    }

    /**
     * The main method that gets invoked whenever a task is ran, any errors that occur
     * inside this method will be captured by the TaskRunner and logged against the task.
     *
     * @return mixed
     */
    abstract public function run();

    /**
     * @param string|\DateTime $currentTime
     * @return string
     */
    public function getNextRunDate($currentTime = 'now')
    {
        return CronExpression::factory($this->schedule)
            ->getNextRunDate($currentTime)
            ->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return StringHelper::basename(get_class($this));
    }

    /**
     * @param SchedulerTask $model
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * @return SchedulerTask
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param $str
     */
    public function writeLine($str)
    {
        echo $str.PHP_EOL;
    }

    /**
     * Mark the task as started
     */
    public function start()
    {
        $model = $this->getModel();
        $model->started_at = date('Y-m-d H:i:s');
        $model->save(false);}

    /**
     * Mark the task as stopped.
     */
    public function stop()
    {
        $model = $this->getModel();
        $model->last_run = $model->started_at;
        $model->next_run = $this->getNextRunDate();
        $model->started_at = null;
        $model->save(false);
    }

    /**
     * @param bool $forceRun
     * @return bool
     */
    public function shouldRun($forceRun = false)
    {
        $model = $this->getModel();
        $isDue = in_array($model->status_id, [SchedulerTask::STATUS_DUE, SchedulerTask::STATUS_OVERDUE, SchedulerTask::STATUS_ERROR]);
        $isRunning = $model->status_id == SchedulerTask::STATUS_RUNNING;
        $overdue = false;
        if((strtotime($model->started_at) + $this->overdueThreshold) > strtotime("now")) {
            $overdue = true;
        }

        return ($model->active && ((!$isRunning && ($isDue || $forceRun)) || ($isRunning && $overdue));
    }

}
