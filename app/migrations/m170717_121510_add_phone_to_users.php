<?php

use yii\db\Migration;

class m170717_121510_add_phone_to_users extends Migration
{
    public function safeUp()
    {
		$this->addColumn('{{%users}}', 'phone', 'varchar(15) not null after facebookId');
    }

    public function safeDown()
    {
	    $this->dropColumn('{{%users}}', 'phone');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170717_121510_add_phone_to_users cannot be reverted.\n";

        return false;
    }
    */
}
