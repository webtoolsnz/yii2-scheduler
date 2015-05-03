<?php

namespace webtoolsnz\scheduler\models\base;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the base-model class for table "scheduler_log".
 *
 * @property integer $id
 * @property integer $scheduled_task_id
 * @property string $started_at
 * @property string $ended_at
 * @property string $output
 * @property integer $error
 *
 * @property \webtoolsnz\scheduler\models\SchedulerTask $scheduledTask
 */
class SchedulerLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scheduler_log';
    }

    /**
     *
     */
    public static function label($n = 1)
    {
        return Yii::t('app', '{n, plural, =1{Scheduler Log} other{Scheduler Logs}}', ['n' => $n]);
    }

    /**
     *
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheduled_task_id', 'output'], 'required'],
            [['scheduled_task_id', 'error'], 'integer'],
            [['started_at', 'ended_at'], 'safe'],
            [['output'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'scheduled_task_id' => Yii::t('app', 'Scheduled Task ID'),
            'started_at' => Yii::t('app', 'Started At'),
            'ended_at' => Yii::t('app', 'Ended At'),
            'output' => Yii::t('app', 'Output'),
            'error' => Yii::t('app', 'Error'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheduledTask()
    {
        return $this->hasOne(\webtoolsnz\scheduler\models\SchedulerTask::className(), ['id' => 'scheduled_task_id']);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params = null)
    {
        $formName = $this->formName();
        $params = !$params ? Yii::$app->request->get($formName, array()) : $params;
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder'=>['id'=>SORT_DESC]],
        ]);

        if (!$this->load($params, $formName)) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'scheduled_task_id' => $this->scheduled_task_id,
            'error' => $this->error,
        ]);

        $query->andFilterWhere(['like', 'started_at', $this->started_at])
            ->andFilterWhere(['like', 'ended_at', $this->ended_at])
            ->andFilterWhere(['like', 'output', $this->output]);

        return $dataProvider;
    }
}

