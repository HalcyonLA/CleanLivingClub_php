<?php

use yii\db\Migration;

class m170714_170437_add_status_to_users extends Migration
{
    public function safeUp()
    {
		$this->addColumn('{{%users}}', 'status', 'enum("ACTIVE", "DELETED") null default "ACTIVE" after photoThumbnail');
    }

    public function safeDown()
    {
	    $this->dropColumn('{{%users}}', 'status');
    }

}
