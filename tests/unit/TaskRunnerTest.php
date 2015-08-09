<?php

namespace webtoolsnz\scheduler\tests;

use \webtoolsnz\scheduler\tests\tasks\AlphabetTask;
use \webtoolsnz\scheduler\TaskRunner;
use \webtoolsnz\scheduler\models\SchedulerTask;
use \webtoolsnz\scheduler\models\SchedulerLog;
use webtoolsnz\scheduler\tests\tasks\ErrorTask;
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
/*
    public function testBadCodeException()
    {
        $runner = new TaskRunner();
        $runner->errorSetup();
        $e = null;

        try {
            eval('echo $foo;');
            $this->fail('Error not caught');
        } catch (\ErrorException $e) {

        }

        $this->assertEquals('Undefined variable: foo', $e->getMessage());
        $this->assertEquals(1, $e->getLine());
        $this->assertEquals(0, $e->getCode());

        $runner->errorTearDown();
    }
*/
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
        $this->assertLessThan(2, abs(strtotime($logModel->started_at) - strtotime($started_at)));
        $this->assertLessThan(2, abs(strtotime($logModel->ended_at) - strtotime($ended_at)));
        $this->assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $logModel->output);
    }
    public function testRunErrorTask()
    {
        $task = new ErrorTask();
        /* @var SchedulerTask $model */
        $model = SchedulerTask::find()->where(['name' => $task->getName()])->one();

        $model->attributes = [
            'name' => $task->getName(),
            'description' => $task->description,
            'status_id' => SchedulerTask::STATUS_DUE,
            'active' => 1,
        ];
        $model->save();

        $task->setModel($model);

        /* @var SchedulerLog $logModel */
        $logModel = test::double(new SchedulerLog(), ['save' => function () {
            return true;
        }]);

        $runner = new TaskRunner();
        $runner->setTask($task);
        $runner->setLog($logModel);

        $runner->runTask(true);
        $model->refresh();
        $this->assertEquals(SchedulerTask::STATUS_ERROR, $model->status_id);
        $this->assertEquals($model->id, $logModel->scheduler_task_id);
        $this->assertEquals(1, $logModel->error);
        $this->assertContains('this is an error', $logModel->output);
    }
    public function testRunningErroredTask()
    {
        $task = new ErrorTask();
        /* @var SchedulerTask $model */
        $model = SchedulerTask::find()->where(['name' => $task->getName()])->one();

        $model->attributes = [
            'name' => $task->getName(),
            'description' => $task->description,
            'status_id' => SchedulerTask::STATUS_ERROR,
            'active' => 1,
            'next_run' => date('Y-m-d H:i:s', strtotime('-1 week'))
        ];
        $model->save();

        $task->setModel($model);
        $this->assertTrue($task->shouldRun());
    }
}