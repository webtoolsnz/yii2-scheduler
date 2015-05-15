<?php

use yii\db\Schema;
use yii\db\Migration;

class m150510_090513_Scheduler extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable('{{%scheduler_log}}', [
            'id'=> Schema::TYPE_PK.'',
            'scheduled_task_id'=> Schema::TYPE_INTEGER.'(11) NOT NULL',
            'started_at'=> Schema::TYPE_TIMESTAMP.' NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'ended_at'=> Schema::TYPE_TIMESTAMP.' NOT NULL DEFAULT "0000-00-00 00:00:00"',
            'output'=> Schema::TYPE_TEXT.' NOT NULL',
            'error'=> Schema::TYPE_BOOLEAN.'(1) NOT NULL DEFAULT "0"',
        ], $tableOptions);

        $this->createIndex('id_UNIQUE', '{{%scheduler_log}}','id',1);
        $this->createIndex('fk_table1_scheduled_task_idx', '{{%scheduler_log}}','scheduled_task_id',0);

        $this->createTable('{{%scheduler_task}}', [
            'id'=> Schema::TYPE_PK.'',
            'name'=> Schema::TYPE_STRING.'(45) NOT NULL',
            'schedule'=> Schema::TYPE_STRING.'(45) NOT NULL',
            'description'=> Schema::TYPE_TEXT.' NOT NULL',
            'status_id'=> Schema::TYPE_INTEGER.'(11) NOT NULL',
            'started_at'=> Schema::TYPE_TIMESTAMP.'',
            'last_run'=> Schema::TYPE_TIMESTAMP.'',
            'next_run'=> Schema::TYPE_TIMESTAMP.'',
            'active'=> Schema::TYPE_BOOLEAN.'(1) NOT NULL DEFAULT "0"',
        ], $tableOptions);

        $this->createIndex('id_UNIQUE', '{{%scheduler_task}}','id',1);
        $this->createIndex('name_UNIQUE', '{{%scheduler_task}}','name',1);
        $this->addForeignKey('fk_scheduler_log_scheduled_task_id', '{{%scheduler_log}}', 'scheduled_task_id', 'scheduler_task', 'id');
    }

    public function safeDown()
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $this->dropForeignKey('fk_scheduler_log_scheduled_task_id', '{{%scheduler_log}}');
            $this->dropTable('{{%scheduler_log}}');
            $this->dropTable('{{%scheduler_task}}');
            $transaction->commit();
        } catch (Exception $e) {
            echo 'Catch Exception '.$e->getMessage().' and rollBack this';
            $transaction->rollBack();
        }
    }
}
