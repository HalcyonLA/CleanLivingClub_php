<?php

use yii\db\Schema;
use yii\db\Migration;

class m160203_204347_create_table_users extends Migration
{
    public function up()
    {
        $this->createTable('{{%users}}', [
            'id' => 'pk',
            'email' => 'VARCHAR(100) NOT NULL',
            'password' => 'VARCHAR(64) NOT NULL',
            'facebookId' => 'VARCHAR(30) NOT NULL',
            'createAt' => 'TIMESTAMP NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%users}}');
    }
}
