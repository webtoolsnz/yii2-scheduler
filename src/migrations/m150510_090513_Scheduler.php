<?php

use yii\db\Schema;
use yii\db\Migration;

class m150510_090513_Scheduler extends Migration
{
    public function safeUp()
    {
        $this->createTable('scheduler_log', [
            'id'=> Schema::TYPE_PK.'',
            'scheduler_task_id'=> Schema::TYPE_INTEGER.'(11) NOT NULL',
            'started_at'=> Schema::TYPE_TIMESTAMP.' NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'ended_at'=> Schema::TYPE_TIMESTAMP.' NULL DEFAULT NULL',
            'output'=> Schema::TYPE_TEXT.' NOT NULL',
            'error'=> Schema::TYPE_BOOLEAN.'(1) NOT NULL DEFAULT "0"',
        ], 'ENGINE=InnoDB');

        $this->createIndex('id_UNIQUE', 'scheduler_log','id',1);
        $this->createIndex('fk_table1_scheduler_task_idx', 'scheduler_log','scheduler_task_id',0);

        $this->createTable('scheduler_task', [
            'id'=> Schema::TYPE_PK.'',
            'name'=> Schema::TYPE_STRING.'(45) NOT NULL',
            'schedule'=> Schema::TYPE_STRING.'(45) NOT NULL',
            'description'=> Schema::TYPE_TEXT.' NOT NULL',
            'status_id'=> Schema::TYPE_INTEGER.'(11) NOT NULL',
            'started_at'=> Schema::TYPE_TIMESTAMP.' NULL DEFAULT NULL',
            'last_run'=> Schema::TYPE_TIMESTAMP.' NULL DEFAULT NULL',
            'next_run'=> Schema::TYPE_TIMESTAMP.' NULL DEFAULT NULL',
            'active'=> Schema::TYPE_BOOLEAN.'(1) NOT NULL DEFAULT "0"',
        ], 'ENGINE=InnoDB');

        $this->createIndex('id_UNIQUE', 'scheduler_task','id',1);
        $this->createIndex('name_UNIQUE', 'scheduler_task','name',1);
        $this->addForeignKey('fk_scheduler_log_scheduler_task_id', 'scheduler_log', 'scheduler_task_id', 'scheduler_task', 'id');
    }

    public function safeDown()
    {
        $this->delete('scheduler_log');
        $this->delete('scheduler_task');

        $this->dropForeignKey('fk_scheduler_log_scheduler_task_id', 'scheduler_log');
        $this->dropTable('scheduler_log');
        $this->dropTable('scheduler_task');
    }
}
