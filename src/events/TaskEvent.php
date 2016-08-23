<?php


namespace webtoolsnz\scheduler\events;

use yii\base\Event;


class TaskEvent extends Event
{
    public $task;
    public $exception;
    public $success;
}