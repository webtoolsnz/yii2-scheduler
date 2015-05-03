<?php
namespace webtoolsnz\scheduler\tests\tasks;

/**
 * Class AlphabetTask
 * @package webtoolsnz\scheduler\tests\tasks
 */
class AlphabetTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Prints the alphabet';
    public $schedule = '*/1 * * * *';

    public function run()
    {
        foreach (range('A', 'Z') as $letter) {
            echo $letter;
        }
    }
}