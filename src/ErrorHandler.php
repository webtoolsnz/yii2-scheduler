<?php

namespace webtoolsnz\scheduler;

/**
 * Class ErrorHandler
 * @package webtoolsnz\scheduler
 */
class ErrorHandler extends \yii\console\ErrorHandler
{
    /**
     * @var TaskRunner
     */
    public $taskRunner;

    /**
     *  We need to override the register method to inject our own shutdown handler before the internal yii handler
     *  is registered.
     */
    public function register()
    {
        register_shutdown_function([$this, 'schedulerShutdownHandler']);
        return parent::register();
    }

    public function schedulerShutdownHandler()
    {
        $error = error_get_last();
        if ($this->taskRunner && E_ERROR == $error['type']) {
            $this->taskRunner->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}
