<?php

namespace webtoolsnz\scheduler\tests;

use \webtoolsnz\scheduler\tests\tasks\AlphabetTask;
use \webtoolsnz\scheduler\TaskRunner;
use \webtoolsnz\scheduler\models\SchedulerTask;
use \webtoolsnz\scheduler\models\SchedulerLog;
use \yii\codeception\TestCase;
use AspectMock\Test as test;

class TaskRunnerTest extends TestCase
{
    public $appConfig = '@tests/config/unit.php';

    public function testSetGetTask()
    {
        $runner = new TaskRunner();
        $task = new AlphabetTask();

        $runner->setTask($task);
        $this->assertEquals($task, $runner->getTask());

    }

    public function testGetSetLog()
    {
        $runner = new TaskRunner();
        $log = new SchedulerLog();

        $runner->setLog($log);

        $this->assertEquals($log, $runner->getLog());

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

    public function testRunTask()
    {
        $task = new AlphabetTask();

        $model = test::double(new SchedulerTask(), ['save' => function () {
            $this->beforeSave(false);
            return true;
        }]);

        $model->id = 1;

        $model->attributes = [
            'name' => $task->getName(),
            'description' => $task->description,
            'status_id' => SchedulerTask::STATUS_DUE,
            'active' => 1,
        ];

        $task->setModel($model);

        $logModel = test::double(new SchedulerLog(), ['save' => function () {
            return true;
        }]);

        $runner = new TaskRunner();
        $runner->setTask($task);
        $runner->setLog($logModel);

        $this->assertEquals(SchedulerTask::STATUS_DUE, $model->status_id);

        $started_at = date('Y-m-d H:i:s');
        $runner->runTask(true);
        $ended_at = date('Y-m-d H:i:s');

        $this->assertEquals(SchedulerTask::STATUS_PENDING, $model->status_id);
        $this->assertEquals($model->id, $logModel->scheduler_task_id);
        $this->assertEquals($started_at, $logModel->started_at);
        $this->assertEquals($ended_at, $logModel->ended_at);
        $this->assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $logModel->output);
    }
}