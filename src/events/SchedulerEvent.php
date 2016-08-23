<?php


namespace webtoolsnz\scheduler\events;

use yii\base\Event;


class SchedulerEvent extends Event
{
    const EVENT_BEFORE_RUN = 'SchedulerBeforeRun';
    const EVENT_AFTER_RUN = 'SchedulerAfterRun';

    public $tasks;
    public $exceptions;
    public $success;
}