<?php
namespace webtoolsnz\scheduler;

use webtoolsnz\scheduler\models\SchedulerLog;

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
     * @param bool $forceRun
     */
    public function runTask($forceRun = false)
    {
        $this->errorSetup();
        $task = $this->getTask();

        if ($task->shouldRun($forceRun)) {
            $task->start();
            ob_start();
            $task->run();
            $output = ob_get_contents();
            ob_end_clean();
            $task->stop();
            $this->log($output);
        }

        $this->errorTearDown();
        $task->getModel()->save();
    }

    /**
     * Register custom error handlers so any errors that occur in a task will be captured and
     * logged against the task, and not prevent other tasks from running.
     */
    public function errorSetup()
    {
        set_error_handler(function($errorNumber, $errorText, $errorFile, $errorLine) {
            throw new \ErrorException($errorText, 0, $errorNumber, $errorFile, $errorLine);
        });

        set_exception_handler(function($e) {
            $this->handleError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        });

        register_shutdown_function(function() {
            if (null !== ($error = error_get_last())) {
                list($errorCode, $errorMessage, $errorFile, $errorLine) = $error;
                $this->handleError($errorCode, $errorMessage, $errorFile, $errorLine);
            }
        });
    }

    /**
     * Restore the default error handlers.
     */
    public function errorTearDown()
    {
        restore_error_handler();
        restore_exception_handler();
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
        echo sprintf('ERROR FILE: %s %s', $message, PHP_EOL);
        echo sprintf('ERROR LINE: %s %s', $file, PHP_EOL);
        echo sprintf('ERROR MESSAGE: %s %s', $lineNumber, PHP_EOL);

        $output = ob_get_contents();
        ob_end_clean();

        $this->error = true;
        $this->getTask()->getModel()->stop();
        $this->log($output);
    }

    /**
     * @param string $output
     */
    public function log($output)
    {
        $model = $this->getTask()->getModel();
        $log = new SchedulerLog();
        $log->started_at = $model->started_at;
        $log->ended_at = date('Y-m-d H:i:s');
        $log->error = $this->error ? 1 : 0;
        $log->output = $output;
        $log->scheduled_task_id = $model->id;
        $log->save(false);
    }

}