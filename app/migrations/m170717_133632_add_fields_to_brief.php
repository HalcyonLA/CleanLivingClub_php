<?php

use yii\db\Migration;

class m170717_133632_add_fields_to_brief extends Migration
{
    public function safeUp()
    {
		$this->addColumn('{{%brief}}', 'smthAbEnergyGoals', 'text not null after beautyGoals');
		$this->addColumn('{{%brief}}', 'sendToEmail', 'int(1) default 0');

    }

    public function safeDown()
    {
	    $this->dropColumn('{{%brief}}', 'smthAbEnergyGoals');
	    $this->dropColumn('{{%brief}}', 'sendToEmail');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170717_133632_add_fields_to_brief cannot be reverted.\n";

        return false;
    }
    */
}
