<?php

namespace webtoolsnz\scheduler\tests;

use Yii;


class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTasks()
    {

        $module = Yii::createObject([
            'class' => '\webtoolsnz\scheduler\Module',
            'taskPath' => '@tests/tasks',
            'taskNameSpace' => '\webtoolsnz\scheduler\tests\tasks'
        ], ['scheduler']);

        $tasks = $module->getTasks();

        $this->assertEquals(2, count($tasks));

        $this->assertEquals('AlphabetTask', $tasks[0]->getName());
        $this->assertEquals('NumberTask', $tasks[1]->getName());
    }

    public function testGetTaskInvalidPath()
    {
        $this->setExpectedException('ErrorException');

        $module = Yii::createObject([
            'class' => '\webtoolsnz\scheduler\Module',
            'taskPath' => '@tests/some/random/path',
        ], ['scheduler']);

        $module->getTasks();
    }
}