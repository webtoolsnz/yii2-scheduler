<?php

namespace webtoolsnz\scheduler\models;

use Yii;

/**
 * This is the model class for table "scheduler_log".
 */
class SchedulerLog extends \webtoolsnz\scheduler\models\base\SchedulerLog
{
    public function __toString()
    {
        return Yii::$app->formatter->asDatetime($this->started_at);
    }

    public function getDuration()
    {
        $start = new \DateTime($this->started_at);
        $end = new \DateTime($this->ended_at);
        $diff = $start->diff($end);

        return $diff->format('%hh %im %Ss');
    }

}
