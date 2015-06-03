<?php


namespace webtoolsnz\scheduler;

use webtoolsnz\scheduler\models\SchedulerTask;
use yii\helpers\StringHelper;
use Cron\CronExpression;

/**
 * Class Task
 * @package webtoolsnz\scheduler
 */
abstract class Task extends \yii\base\Component
{
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
     * How many seconds after due date to wait until the task becomes overdue.
     *
     * @var int
     */
    public $overdueThreshold = 3600;

    /**
     * @var null|SchedulerTask
     */
    private $_model;

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
        $isDue = in_array($model->status_id, [SchedulerTask::STATUS_DUE, SchedulerTask::STATUS_OVERDUE]);
        $isRunning = $model->status_id == SchedulerTask::STATUS_RUNNING;

        return (!$isRunning && $model->active && ($isDue || $forceRun));
    }

}
