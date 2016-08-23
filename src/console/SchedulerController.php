<?php

namespace webtoolsnz\scheduler\console;

use webtoolsnz\scheduler\events\SchedulerEvent;
use webtoolsnz\scheduler\models\base\SchedulerLog;
use webtoolsnz\scheduler\models\SchedulerTask;
use webtoolsnz\scheduler\Task;
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
        SchedulerTask::STATUS_ERROR => Console::FG_RED,
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
     * @return \webtoolsnz\scheduler\Module
     */
    private function getScheduler()
    {
        return Yii::$app->getModule('scheduler');
    }

    /**
     * List all tasks
     */
    public function actionIndex()
    {
        // Update task index
        $this->getScheduler()->getTasks();
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
        $tasks = $this->getScheduler()->getTasks();

        echo 'Running Tasks:'.PHP_EOL;
        $event = new SchedulerEvent([
            'tasks' => $tasks,
            'success' => true,
        ]);
        $this->trigger(SchedulerEvent::EVENT_BEFORE_RUN, $event);
        foreach ($tasks as $task) {
            $this->runTask($task);
            if ($task->exception) {
                $event->success = false;
                $event->exceptions[] = $task->exception;
            }
        }
        $this->trigger(SchedulerEvent::EVENT_AFTER_RUN, $event);
        echo PHP_EOL;
    }

    /**
     * Run the specified task (if due)
     */
    public function actionRun()
    {
        if (null === $this->taskName) {
            throw new InvalidParamException('taskName must be specified');
        }

        /* @var Task $task */
        $task = $this->getScheduler()->loadTask($this->taskName);

        if (!$task) {
            throw new InvalidParamException('Invalid taskName');
        }
        $event = new SchedulerEvent([
            'tasks' => [$task],
            'success' => true,
        ]);
        $this->trigger(SchedulerEvent::EVENT_BEFORE_RUN, $event);
        $this->runTask($task);
        if ($task->exception) {
            $event->success = false;
            $event->exceptions = [$task->exception];
        }
        $this->trigger(SchedulerEvent::EVENT_AFTER_RUN, $event);
    }

    /**
     * @param Task $task
     */
    private function runTask(Task $task)
    {
        echo sprintf("\tRunning %s...", $task->getName());
        if ($task->shouldRun($this->force)) {
            $runner = new TaskRunner();
            $runner->setTask($task);
            $runner->setLog(new SchedulerLog());
            $runner->runTask($this->force);
            echo $runner->error ? 'error' : 'done'.PHP_EOL;
        } else {
            echo "Task is not due, use --force to run anyway".PHP_EOL;
        }
    }
}
