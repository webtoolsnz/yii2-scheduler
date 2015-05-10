<?php

namespace webtoolsnz\scheduler\tests;

use \webtoolsnz\scheduler\tests\tasks\AlphabetTask;
use \webtoolsnz\scheduler\TaskRunner;

class TaskRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetTask()
    {
        $runner = new TaskRunner();
        $task = new AlphabetTask();

        $runner->setTask($task);
        $this->assertEquals($task, $runner->getTask());

    }

    public function testBadCodeException()
    {
        $runner = new TaskRunner();
        $runner->errorSetup();
        $e = null;

        try {
            eval('echo $foo;');
        } catch (\ErrorException $e) {

        }

        $this->assertEquals('Undefined variable: foo', $e->getMessage());
        $this->assertEquals(1, $e->getLine());
        $this->assertEquals(0, $e->getCode());

        $runner->errorTearDown();
    }

    public function testTest()
    {




    }
}