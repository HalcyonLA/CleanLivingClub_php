<?php

use yii\db\Migration;

class m170714_165020_add_columns_to_users extends Migration
{
    public function safeUp()
    {
		$this->addColumn('{{%users}}', 'firstName', 'varchar(255) null after facebookId');
		$this->addColumn('{{%users}}', 'lastName', 'varchar(255) null after firstName');
		$this->addColumn('{{%users}}', 'photo', 'varchar(500) null after lastName');
		$this->addColumn('{{%users}}', 'photoThumbnail', 'varchar(500) null after photo');
    }

    public function safeDown()
    {
	    $this->dropColumn('{{%users}}', 'firstName');
	    $this->dropColumn('{{%users}}', 'lastName');
	    $this->dropColumn('{{%users}}', 'photo');
	    $this->dropColumn('{{%users}}', 'photoThumbnail');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170714_165020_add_columns_to_users cannot be reverted.\n";

        return false;
    }
    */
}
