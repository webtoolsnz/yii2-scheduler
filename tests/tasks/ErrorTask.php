<?php
namespace webtoolsnz\scheduler\tests\tasks;

/**
 * Class ErrorTask
 * @package webtoolsnz\scheduler\tests\tasks
 */
class ErrorTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Throws an Error';
    public $schedule = '*/1 * * * *';

    public function run()
    {
        trigger_error('this is an error');
    }
}