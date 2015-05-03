<?php
namespace webtoolsnz\scheduler;

use webtoolsnz\scheduler\models\SchedulerLog;

class TaskRunner extends \yii\base\Component
{
    public $error;
    private $_task;

    public function setTask(Task $task)
    {
        $this->_task = $task;
    }

    public function getTask()
    {
        return $this->_task;
    }

    /**
     * @param Task $task
     * @param bool $forceRun
     */
    public function runTask($forceRun = false)
    {
        $this->errorSetup();

        if($this->task->shouldRun($forceRun)) {
            $this->task->start();
            ob_start();
            $this->task->run();
            $output = ob_get_contents();
            ob_end_clean();
            $this->task->stop();
            $this->log($output);
        }

        $this->errorTearDown();
        $this->task->model->save();
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
            $this->task->getModel()->stop();
            echo $this->errorSummary($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            $this->error = true;
            $this->log(ob_get_contents());
            ob_end_clean();
        });

        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error) {
                $this->task->getModel()->stop();
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