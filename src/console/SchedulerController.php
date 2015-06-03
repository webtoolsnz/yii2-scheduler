<?php

namespace webtoolsnz\scheduler\console;

use webtoolsnz\scheduler\models\base\SchedulerLog;
use webtoolsnz\scheduler\models\SchedulerTask;
use webtoolsnz\scheduler\TaskRunner;
use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\helpers\Console;


/**
 * Scheduled task runner for Yii2
 *
 * You can use this command to manage scheduler tasks
 *
 * ```
 * $ ./yii scheduler/run-all
 * ```
 *
 */
class SchedulerController extends Controller
{
    /**
     * @var \webtoolsnz\scheduler\Module
     */
    public $module;

    /**
     * Force pending tasks to run.
     * @var bool
     */
    public $force = false;

    /**
     * Name of the task to run
     * @var null|string
     */
    public $taskName;

    /**
     * Colour map for SchedulerTask status ids
     * @var array
     */
    private $_statusColors = [
        SchedulerTask::STATUS_PENDING => Console::FG_BLUE,
        SchedulerTask::STATUS_DUE => Console::FG_YELLOW,
        SchedulerTask::STATUS_OVERDUE => Console::FG_RED,
        SchedulerTask::STATUS_RUNNING => Console::FG_GREEN,
    ];

    /**
     * @param string $actionId
     * @return array
     */
    public function options($actionId)
    {
        $options = [];

        switch ($actionId) {
            case 'run-all':
                $options[] = 'force';
                break;
            case 'run':
                $options[] = 'force';
                $options[] = 'taskName';
                break;
        }

        return $options;
    }

    /**
     * List all tasks
     */
    public function actionIndex()
    {
        // Update task index
        $this->module->getTasks();
        $models = SchedulerTask::find()->all();

        echo $this->ansiFormat('Scheduled Tasks', Console::UNDERLINE).PHP_EOL;

        foreach ($models as $model) { /* @var SchedulerTask $model */
            $row = sprintf(
                "%s\t%s\t%s\t%s\t%s",
                $model->name,
                $model->schedule,
                is_null($model->last_run) ? 'NULL' : $model->last_run,
                $model->next_run,
                $model->getStatus()
            );

            $color = isset($this->_statusColors[$model->status_id]) ? $this->_statusColors[$model->status_id] : null;
            echo $this->ansiFormat($row, $color).PHP_EOL;
        }
    }

    /**
     * Run all due tasks
     */
    public function actionRunAll()
    {
        $tasks = $this->module->getTasks();

        echo 'Running Tasks:'.PHP_EOL;

        foreach ($tasks as $task) {
            echo sprintf("\tRunning %s...", $task->getName());
            $runner = new TaskRunner();
            $runner->setTask($task);
            $runner->setLog(new SchedulerLog());
            $runner->runTask($this->force);
            echo 'done'.PHP_EOL;
        }
    }

    /**
     * Run the specified task (if due)
     */
    public function actionRun()
    {
        if (null === $this->taskName) {
            throw new InvalidParamException('taskName must be specified');
        }

        $task = $this->module->loadTask($this->taskName);

        if (!$task) {
            throw new InvalidParamException('Invalid taskName');
        }

        echo sprintf("\tRunning %s...", $task->getName());
        $runner = new TaskRunner();
        $runner->setTask($task);
        $runner->setLog(new SchedulerLog());
        $runner->runTask($this->force);
        echo 'done'.PHP_EOL;
    }
}
