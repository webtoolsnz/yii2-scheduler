<?php
/**
 * Update Task View
 *
 * @var yii\web\View $this
 * @var webtoolsnz\scheduler\models\SchedulerTask $model
 */

use yii\helpers\Html;
use webtoolsnz\scheduler\models\SchedulerTask;
use yii\bootstrap\Tabs;
use yii\bootstrap\ActiveForm;
use webtoolsnz\widgets\RadioButtonGroup;
use yii\grid\GridView;


$this->title = $model->__toString();
$this->params['breadcrumbs'][] = ['label' => SchedulerTask::label(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->__toString();
?>
<div class="task-update">

    <h1><?=$this->title ?></h1>

    <?php $this->beginBlock('main'); ?>
    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= $form->field($model, 'name', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    <?= $form->field($model, 'description', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    <?= $form->field($model, 'schedule', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    <?= $form->field($model, 'status', ['inputOptions' => ['disabled' => 'disabled']]) ?>

    <?php if ($model->status_id == SchedulerTask::STATUS_RUNNING): ?>
        <?= $form->field($model, 'started_at', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    <?php endif ?>

    <?= $form->field($model, 'last_run', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    <?= $form->field($model, 'next_run', ['inputOptions' => ['disabled' => 'disabled']]) ?>

    <?= $form->field($model, 'active')->widget(RadioButtonGroup::className(), [
        'items' => [1 => 'Yes', 0 => 'No'],
        'itemOptions' => [
            'buttons' => [0 => ['activeState' => 'btn active btn-danger']]
        ]
    ]); ?>

    <?= Html::submitButton('<span class="glyphicon glyphicon-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-primary'
    ]); ?>

    <?php ActiveForm::end(); ?>
    <?php $this->endBlock(); ?>



    <?php $this->beginBlock('logs'); ?>
    <div class="table-responsive">
        <?php \yii\widgets\Pjax::begin(['id' => 'logs']); ?>
        <?= GridView::widget([
            'layout' => '{summary}{pager}{items}{pager}',
            'dataProvider' => $logDataProvider,
            'pager' => [
                'class' => yii\widgets\LinkPager::className(),
                'firstPageLabel' => Yii::t('app', 'First'),
                'lastPageLabel' => Yii::t('app', 'Last'),
            ],
            'columns' => [
                [
                    'attribute' => 'started_at',
                    'format' => 'raw',
                    'value' => function ($m) {
                        return Html::a($m->started_at, ['view-log', 'id' => $m->id]);
                    }
                ],
                'ended_at:datetime',
                [
                    'label' => 'Duration',
                    'value' => function ($m) {
                        return $m->getDuration();
                    }
                ],
                [
                    'label' => 'Result',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($m) {
                        return Html::tag('span', '', [
                            'class' => $m->error == 0 ? 'text-success glyphicon glyphicon-ok-circle' : 'text-danger glyphicon glyphicon-remove-circle'
                        ]);
                    }
                ],
            ],
        ]); ?>
        <?php \yii\widgets\Pjax::end(); ?>
    </div>
    <?php $this->endBlock(); ?>

    <?= Tabs::widget([
        'encodeLabels' => false,
        'id' => 'customer',
        'items' => [
            'overview' => [
                'label'   => Yii::t('app', 'Overview'),
                'content' => $this->blocks['main'],
                'active'  => true,
            ],
            'logs' => [
                'label' => 'Logs',
                'content' => $this->blocks['logs'],
            ],
        ]
    ]);?>
</div>
