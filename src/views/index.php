<?php
/**
 * Index View for scheduled tasks
 *
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \webtoolsnz\scheduler\models\SchedulerTask $model
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;


$this->title = \webtoolsnz\scheduler\models\SchedulerTask::label(2);
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="scheduler-index">

    <h1><?= $this->title ?></h1>

    <div class="table-responsive">
        <?php \yii\widgets\Pjax::begin(); ?>
        <?= GridView::widget([
            'layout' => '{summary}{pager}{items}{pager}',
            'dataProvider' => $dataProvider,
            'pager' => [
                'class' => yii\widgets\LinkPager::className(),
                'firstPageLabel' => Yii::t('app', 'First'),
                'lastPageLabel' => Yii::t('app', 'Last'),
            ],
            'columns' => [
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function ($t) {
                        return Html::a($t->name, ['update', 'id' => $t->id]);
                    }
                ],

                'name',
                'description',
                'schedule',
                'status'
            ],
        ]); ?>
        <?php \yii\widgets\Pjax::end(); ?>
    </div>
</div>
