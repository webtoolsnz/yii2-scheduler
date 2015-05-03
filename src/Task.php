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
    public $description;
    public $schedule;
    public $active = true;
    public $overdueThreshold = 3600;

    private $_model;

    /**
     * @return mixed
     */
    abstract public function run();

    /**
     *
     */
    public function init()
    {
        parent::init();

        //$model = SchedulerTask::createTaskModel($this);
        //$this->setModel($model);
    }

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
     * @param $model
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * @return /webtoolsnz/scheduler/models/SchedulerTask
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
     *
     */
    public function start()
    {
        $model = $this->getModel();
        $model->started_at = date('Y-m-d H:i:s');
        $model->save(false);
    }

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
