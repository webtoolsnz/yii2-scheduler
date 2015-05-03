<?php

namespace webtoolsnz\scheduler\console;

use webtoolsnz\scheduler\models\SchedulerTask;
use webtoolsnz\scheduler\TaskRunner;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;


/**
 * Scheduled task runner for Yii2
 *
 * You can use this command to generate models, controllers, etc. For example,
 * to generate an ActiveRecord model based on a DB table, you can run:
 *
 * ```
 * $ ./yii gii/model --tableName=city --modelClass=City
 * ```
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class SchedulerController extends Controller
{
    /**
     * @var \webtoolsnz\scheduler\Module
     */
    public $module;

    private $_statusColors = [
        SchedulerTask::STATUS_PENDING => Console::FG_BLUE,
        SchedulerTask::STATUS_DUE => Console::FG_YELLOW,
        SchedulerTask::STATUS_OVERDUE => Console::FG_RED,
        SchedulerTask::STATUS_RUNNING => Console::FG_GREEN,
    ];

    public function actionIndex()
    {
        // Update task index
        $this->module->getTasks();
        $models = SchedulerTask::find()->all();

        echo $this->ansiFormat('Scheduled Tasks', Console::UNDERLINE).PHP_EOL;

        foreach ($models as $model) { /* @var SchedulerTask $model */
            $row = sprintf(
                "%s\t%s\t%s\t%s\t%s",
                $model->name,
                $model->schedule,
                is_null($model->last_run) ? 'NULL' : $model->last_run,
                $model->next_run,
                $model->getStatus()
            );

            $color = isset($this->_statusColors[$model->status_id]) ? $this->_statusColors[$model->status_id] : null;
            echo $this->ansiFormat($row, $color).PHP_EOL;
        }
    }

    public function actionRunAll()
    {
        $tasks = $this->module->getTasks();

        echo 'Running Tasks:'.PHP_EOL;

        foreach ($tasks as $task) {
            echo sprintf("\tRunning %s...", $task->getName());
            $runner = new TaskRunner();
            $runner->setTask($task);
            $runner->runTask();
            echo 'done'.PHP_EOL;
        }

        //var_dump($this);
    }


}
