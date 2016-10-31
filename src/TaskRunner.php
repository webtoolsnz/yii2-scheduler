<?php
namespace webtoolsnz\scheduler;

use Yii;
use webtoolsnz\scheduler\events\TaskEvent;
use webtoolsnz\scheduler\models\SchedulerLog;
use webtoolsnz\scheduler\models\SchedulerTask;

/**
 * Class TaskRunner
 *
 * @package webtoolsnz\scheduler
 * @property \webtoolsnz\scheduler\Task $task
 */
class TaskRunner extends \yii\base\Component
{

    /**
     * Indicates whether an error occured during the executing of the task.
     * @var bool
     */
    public $error;

    /**
     * The task that will be executed.
     *
     * @var \webtoolsnz\scheduler\Task
     */
    private $_task;

    /**
     * @var \webtoolsnz\scheduler\models\SchedulerLog
     */
    private $_log;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @param Task $task
     */
    public function setTask(Task $task)
    {
        $this->_task = $task;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->_task;
    }

    /**
     * @param \webtoolsnz\scheduler\models\SchedulerLog $log
     */
    public function setLog($log)
    {
        $this->_log = $log;
    }

    /**
     * @return SchedulerLog
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     * @param bool $forceRun
     */
    public function runTask($forceRun = false)
    {
        $task = $this->getTask();

        if ($task->shouldRun($forceRun)) {
            $event = new TaskEvent([
                'task' => $task,
                'success' => true,
            ]);
            $this->trigger(Task::EVENT_BEFORE_RUN, $event);
            if (!$event->cancel) {
                $task->start();
                ob_start();
                try {
                    $this->running = true;
                    $this->shutdownHandler();
                    $task->run();
                    $this->running = false;
                    $output = ob_get_contents();
                    ob_end_clean();
                    $this->log($output);
                    $task->stop();
                } catch (\Exception $e) {
                    $this->running = false;
                    $task->exception = $e;
                    $event->exception = $e;
                    $event->success = false;
                    $this->handleError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
                }
                $this->trigger(Task::EVENT_AFTER_RUN, $event);
            }
        }
        $task->getModel()->save();
    }

    /**
     * If the yii error handler has been overridden with `\webtoolsnz\scheduler\ErrorHandler`,
     * pass it this instance of TaskRunner, so it can update the state of tasks in the event of a fatal error.
     */
    public function shutdownHandler()
    {
        $errorHandler = Yii::$app->getErrorHandler();

        if ($errorHandler instanceof \webtoolsnz\scheduler\ErrorHandler) {
            Yii::$app->getErrorHandler()->taskRunner = $this;
        }
    }

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $lineNumber
     */
    public function handleError($code, $message, $file, $lineNumber)
    {
        echo sprintf('ERROR: %s %s', $code, PHP_EOL);
        echo sprintf('ERROR FILE: %s %s', $file, PHP_EOL);
        echo sprintf('ERROR LINE: %s %s', $lineNumber, PHP_EOL);
        echo sprintf('ERROR MESSAGE: %s %s', $message, PHP_EOL);

        // if the failed task was mid transaction, rollback so we can save.
        if (null !== ($tx = \Yii::$app->db->getTransaction())) {
            $tx->rollBack();
        }

        $output = ob_get_contents();
        ob_end_clean();

        $this->error = true;
        $this->log($output);
        $this->getTask()->getModel()->status_id = SchedulerTask::STATUS_ERROR;
        $this->getTask()->stop();
    }

    /**
     * @param string $output
     */
    public function log($output)
    {
        $model = $this->getTask()->getModel();
        $log = $this->getLog();
        $log->started_at = $model->started_at;
        $log->ended_at = date('Y-m-d H:i:s');
        $log->error = $this->error ? 1 : 0;
        $log->output = $output;
        $log->scheduler_task_id = $model->id;
        $log->save(false);
    }
}
