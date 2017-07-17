<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%brief}}".
 *
 * @property integer $id
 * @property string $email
 * @property string $phone
 * @property string $name
 * @property integer $userId
 * @property string $startMealDeliveryDate
 * @property string $mealDeliveryAddress
 * @property string $buildingEnterInstructions
 * @property string $foodAllergies
 * @property string $medications
 * @property string $healthGoals
 * @property string $weightLossGoal
 * @property string $height
 * @property string $weight
 * @property string $age
 * @property string $energyGoals
 * @property string $beautyGoals
 * @property string $smthAbEnergyGoals
 * @property string $smthAbBeautyGoals
 * @property string $favoriteBreakfasts
 * @property string $favoriteLunches
 * @property string $favoriteSoups
 * @property string $favoriteSalads
 * @property string $favoriteSweetSnacks
 * @property string $favoriteSalSpSnacks
 * @property integer $payDeposit
 * @property integer $sendToEmail
 * @property string $created
 */
class Brief extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%brief}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'payDeposit'], 'integer'],
            [['startMealDeliveryDate', 'mealDeliveryAddress', 'buildingEnterInstructions', 'foodAllergies',
	            'medications', 'healthGoals', 'weightLossGoal', 'height', 'weight', 'age', 'energyGoals',
	            'beautyGoals', 'smthAbBeautyGoals', 'favoriteBreakfasts', 'favoriteLunches', 'favoriteSoups',
	            'favoriteSalads', 'favoriteSweetSnacks', 'favoriteSalSpSnacks', 'email', 'phone', 'name', 'smthAbEnergyGoals'], 'required'],
            [['startMealDeliveryDate', 'created'], 'safe'],
            [['email'], 'email'],
            [['sendToEmail', 'payDeposit'], 'in', 'range' => [0, 1]],
            [['sendToEmail', 'payDeposit'], 'default', 'value' => 0],
            [['phone'], 'phone'],
            [['mealDeliveryAddress', 'buildingEnterInstructions', 'foodAllergies', 'medications', 'healthGoals',
	            'weightLossGoal', 'energyGoals', 'beautyGoals', 'smthAbBeautyGoals', 'favoriteBreakfasts',
	            'favoriteLunches', 'favoriteSoups', 'favoriteSalads', 'favoriteSweetSnacks',
	            'favoriteSalSpSnacks', 'name', 'smthAbEnergyGoals'], 'string'],
            [['height', 'weight', 'age'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'phone' => 'Phone',
            'name' => 'Name',
            'userId' => 'User ID',
            'startMealDeliveryDate' => 'Start Meal Delivery Date',
            'mealDeliveryAddress' => 'Meal Delivery Address',
            'buildingEnterInstructions' => 'Building Enter Instructions',
            'foodAllergies' => 'Food Allergies',
            'medications' => 'Medications',
            'healthGoals' => 'Health Goals',
            'weightLossGoal' => 'Weight Loss Goal',
            'height' => 'Height',
            'weight' => 'Weight',
            'age' => 'Age',
            'energyGoals' => 'Energy Goals',
            'beautyGoals' => 'Beauty Goals',
            'smthAbEnergyGoals' => 'Smth Ab Energy Goals',
            'smthAbBeautyGoals' => 'Smth Ab Beauty Goals',
            'favoriteBreakfasts' => 'Favorite Breakfasts',
            'favoriteLunches' => 'Favorite Lunches',
            'favoriteSoups' => 'Favorite Soups',
            'favoriteSalads' => 'Favorite Salads',
            'favoriteSweetSnacks' => 'Favorite Sweet Snacks',
            'favoriteSalSpSnacks' => 'Favorite Sal Sp Snacks',
            'payDeposit' => 'Pay Deposit',
            'created' => 'Created',
            'sendToEmail' => 'Send To Email',
        ];
    }
}
