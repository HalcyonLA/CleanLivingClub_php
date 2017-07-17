<?php

use yii\db\Migration;

class m170717_120526_alter_tb_users extends Migration
{
    public function safeUp()
    {
		$this->alterColumn('{{%users}}', 'facebookId', 'varchar(30) null');
    }

    public function safeDown()
    {
	    $this->alterColumn('{{%users}}', 'facebookId', 'varchar(30)');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170717_120526_alter_tb_users cannot be reverted.\n";

        return false;
    }
    */
}
