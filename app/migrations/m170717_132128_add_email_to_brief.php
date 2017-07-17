<?php

use yii\db\Migration;

class m170717_132128_add_email_to_brief extends Migration
{
    public function safeUp()
    {
		$this->addColumn('{{%brief}}', 'email', 'varchar(100) not null after id');
		$this->addColumn('{{%brief}}', 'phone', 'varchar(100) not null after email');
    }

    public function safeDown()
    {
	    $this->dropColumn('{{%brief}}', 'email');
	    $this->dropColumn('{{%brief}}', 'phone');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170717_132128_add_email_to_brief cannot be reverted.\n";

        return false;
    }
    */
}
