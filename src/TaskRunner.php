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

        if($task->shouldRun($forceRun)) {
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
     *
     */
    public function errorSetup()
    {
        set_error_handler(function ($errorNumber, $errorText, $errorFile, $errorLine ) {
            throw new \ErrorException($errorText, 0, $errorNumber, $errorFile, $errorLine);
        });

        set_exception_handler(function ($e) {
            $this->getTask->getModel()->stop();
            echo $this->errorSummary($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            $this->error = true;
            $this->log(ob_get_contents());
            ob_end_clean();
        });

        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error) {
                $this->getTask()->getModel()->stop();
                $this->error = true;
                echo $this->errorSummary($error['type'], $error['message'], $error['file'], $error['line']);
                $this->log(ob_get_contents());
                ob_end_clean();
            }

            exit;
        });
    }

    /**
     *
     */
    public function errorTearDown()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Custom Error handler for PHP errors, we only assign this right before running a task and then unassign the handler,
     * this is done to capture any fatal errors and insert them into the log record for the given task.
     * Note the error property is also set to 1 if this handler is fired, this is propagated into the
     * log record to indicate an error occured when the task executed
     *
     * @return true
     */
    public function errorSummary($errorno, $errorstr, $errorfile, $errorline)
    {
        $summary = '';
        $summary .= sprintf('ERROR: %s %s', $errorno, PHP_EOL);
        $summary .= sprintf('ERROR FILE: %s %s', $errorfile, PHP_EOL);
        $summary .= sprintf('ERROR LINE: %s %s', $errorline, PHP_EOL);
        $summary .= sprintf('ERROR MESSAGE: %s %s', $errorstr, PHP_EOL);

        $this->error = true;

        return $summary;
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