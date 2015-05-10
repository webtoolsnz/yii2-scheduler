<?php
namespace webtoolsnz\scheduler\tests\tasks;

class NumberTask extends \webtoolsnz\scheduler\Task
{
    public $description = 'Prints the numbers from 0 to 100';
    public $schedule = '*/1 * * * *';

    public function run()
    {
        foreach (range(0, 100) as $number) {
            echo $number;
        }
    }
}