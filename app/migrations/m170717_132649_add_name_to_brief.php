<?php

use yii\db\Migration;

class m170717_132649_add_name_to_brief extends Migration
{
    public function safeUp()
    {
	    $this->addColumn('{{%brief}}', 'name', 'varchar(200) not null after phone');
    }

    public function safeDown()
    {
	    $this->dropColumn('{{%brief}}', 'name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170717_132649_add_name_to_brief cannot be reverted.\n";

        return false;
    }
    */
}
