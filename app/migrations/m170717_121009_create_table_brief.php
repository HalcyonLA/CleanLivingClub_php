<?php

use yii\db\Migration;

class m170717_121009_create_table_brief extends Migration
{
    public function safeUp()
    {
		$this->createTable('{{%brief}}', [
			'id' => $this->primaryKey(11),
			'userId' => 'int(11) null',
			'startMealDeliveryDate' => 'datetime not null',
			'mealDeliveryAddress' => 'text not null',
			'buildingEnterInstructions' => 'text not null',
			'foodAllergies' => 'text not null',
			'medications' => 'text not null',
			'healthGoals' => 'text not null',
			'weightLossGoal' => 'text not null',
			'height' => 'varchar(100) not null',
			'weight' => 'varchar(100) not null',
			'age' => 'varchar(100) not null',
			'energyGoals' => 'text not null',
			'beautyGoals' => 'text not null',
			'smthAbBeautyGoals' => 'text not null',
			'favoriteBreakfasts' => 'text not null',
			'favoriteLunches' => 'text not null',
			'favoriteSoups' => 'text not null',
			'favoriteSalads' => 'text not null',
			'favoriteSweetSnacks' => 'text not null',
			'favoriteSalSpSnacks' => 'text not null',
			'payDeposit' => 'int(1) default 0',
			'created' => 'timestamp not null default CURRENT_TIMESTAMP'
		]);
    }

    public function safeDown()
    {
	    $this->dropTable('{{%brief}}');
    }
}
