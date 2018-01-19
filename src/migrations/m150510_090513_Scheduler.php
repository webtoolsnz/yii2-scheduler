<?php

use yii\db\Schema;
use yii\db\Migration;

class m150510_090513_Scheduler extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('scheduler_log', [
            'id' => $this->primaryKey(),
            'scheduler_task_id' => $this->integer()->notNull(),
            'started_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'ended_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'output' => $this->text()->notNull(),
            'error' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);

//        $this->createIndex('id_UNIQUE', 'scheduler_log', 'id', true);
        $this->createIndex('fk_table1_scheduler_task_idx', 'scheduler_log', 'scheduler_task_id', false);

        $this->createTable('scheduler_task', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'schedule' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'status_id' => $this->integer()->notNull(),
            'started_at' => $this->timestamp(),
            'last_run' => $this->timestamp()->defaultValue(null),
            'next_run' => $this->timestamp()->defaultValue(null),
            'active' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);

//        $this->createIndex('id_UNIQUE', 'scheduler_task', 'id', true);
        $this->createIndex('name_UNIQUE', 'scheduler_task', 'name', true);
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
