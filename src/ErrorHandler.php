<?php

namespace webtoolsnz\scheduler;

use yii\base\ErrorException;

/**
 * Class ErrorHandler
 * @package webtoolsnz\scheduler
 */
class ErrorHandler extends \yii\console\ErrorHandler
{
    public $memoryReserveSize = 2097152;

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

        if ($error && $this->taskRunner && E_ERROR == $error['type']) {
            $exception = new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->taskRunner->handleError($exception);
        }

        // Allow yiis error handler to take over and handle logging
        parent::handleFatalError();
    }
}
