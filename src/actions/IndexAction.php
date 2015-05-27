<?php
namespace webtoolsnz\scheduler\actions;

use Yii;
use yii\base\Action;
use webtoolsnz\scheduler\models\SchedulerTask;

/**
 * Class IndexAction
 * @package webtoolsnz\scheduler\actions
 */
class IndexAction extends Action
{
    /**
     * @var string the view file to be rendered. If not set, it will take the value of [[id]].
     * That means, if you name the action as "index" in "SchedulerController", then the view name
     * would be "index", and the corresponding view file would be "views/scheduler/index.php".
     */
    public $view;

    /**
     * Runs the action
     *
     * @return string result content
     */
    public function run()
    {
        $model  = new SchedulerTask();
        $dataProvider = $model->search($_GET);

        return $this->controller->render($this->view ?: $this->id, [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }
}
