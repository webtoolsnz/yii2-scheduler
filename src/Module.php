<?php
namespace webtoolsnz\scheduler;

use Yii;
use yii\base\BootstrapInterface;
use webtoolsnz\scheduler\models\SchedulerTask;

/**
 * Class Module
 * @package webtoolsnz\scheduler
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * Path where task files can be found in the application structure.
     * @var string
     */
    public $taskPath = '@app/tasks';

    /**
     * Namespace that tasks use.
     * @var string
     */
    public $taskNameSpace = 'app\tasks';

    /**
     * Bootstrap the console controllers.
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                'class' => 'webtoolsnz\scheduler\console\SchedulerController',
                'module' => $this,
            ];
        }
    }

    /**
     * Scans the taskPath for any task files, if any are found it attempts to load them,
     * creates a new instance of each class and appends it to an array, which it returns.
     *
     * @return array
     * @throws \yii\base\ErrorException
     */
    public function getTasks()
    {
        $dir = Yii::getAlias($this->taskPath);

        if (!is_readable($dir)) {
            throw new \yii\base\ErrorException("Task directory ($dir) does not exist");
        }

        $files = array_diff(scandir($dir), array('..', '.'));
        $tasks = [];

        foreach ($files as $fileName) {
            // strip out the file extension to derive the class name
            $className = preg_replace('/\.[^.]*$/', '', $fileName);

            // validate class name
            if (preg_match('/^[a-zA-Z0-9_]*Task$/', $className)) {
                $tasks[] = $this->loadTask($className);
            }

        }

        return $tasks;
    }

    /**
     * Given the className of a task, it will return
     * a new instance of that task.
     *
     * @param $className
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function loadTask($className)
    {
        $className = implode('\\', [$this->taskNameSpace, $className]);
        $task = Yii::createObject($className);
        $task->setModel(SchedulerTask::createTaskModel($task));

        return $task;
    }


}
