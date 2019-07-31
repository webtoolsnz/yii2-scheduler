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
     *  We need to override the register method to register our own shutdown handler and prevent yii from
     *  intercepting our error handler.
     */
    public function register()
    {
        register_shutdown_function([$this, 'schedulerShutdownHandler']);
    }

    public function schedulerShutdownHandler()
    {
        $error = error_get_last();
        if ($this->taskRunner && E_ERROR == $error['type']) {
            $this->taskRunner->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }

        // Allow yiis error handler to take over and handle logging
        parent::handleFatalError();
    }
}
