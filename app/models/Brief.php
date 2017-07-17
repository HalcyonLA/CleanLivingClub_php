<?php

namespace app\models;
use app\components\MailerHelper;
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
            [['phone'], 'string'],
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
            'phone' => 'What is your phone #?',
            'name' => 'What is your name?',
            'userId' => 'User ID',
            'startMealDeliveryDate' => 'What date would you like to start your meal delivery?',
            'mealDeliveryAddress' => 'Freshly prepared organic meals are delivered Monday to Friday before 6AM, what is your meal delivery address?',
            'buildingEnterInstructions' => 'Are there any special codes, keys needed to enter your building to do delivery? Is so what are the special instructions?',
            'foodAllergies' => 'Do you have any food allergies? If so what foods you do not eat? ',
            'medications' => 'Are you on any medications if so what are they?',
            'healthGoals' => 'What are your health goals?',
            'weightLossGoal' => 'If weight loss is your health goal, how much weight you would like to lose and by when?',
            'height' => 'What is your height?',
            'weight' => 'What is your weight?',
            'age' => 'What is your age?',
            'energyGoals' => 'What are your energy goals?',
            'beautyGoals' => 'What are your beauty goals?',
            'smthAbEnergyGoals' => 'If improving energy is your goal, when do you feel low energy?',
            'smthAbBeautyGoals' => 'If you have any beauty goals, what have you been using so far for your beauty goals and how are those solutions failing to help you?',
            'favoriteBreakfasts' => 'What are your favorite breakfasts?',
            'favoriteLunches' => 'What are your favorite lunches?',
            'favoriteSoups' => 'What are your favorite soups?',
            'favoriteSalads' => 'What are your favorite salads?',
            'favoriteSweetSnacks' => 'What are your favorite sweet snacks?',
            'favoriteSalSpSnacks' => 'What are your favorite salty & spicy snacks?',
            'payDeposit' => 'The food and drinks are delivered in recyclable glass containers. Glass containers are used to protect any chemicals seeping into your food that causes diseases. The holding deposit for the class containers is $50 (following containers are glass containers : breakfast, lunch, soup, salad, cold pressed juice, probiotic, tea, 6 water bottles- total 13 containers in a day along with 2 bags. We allocated 2 sets for each customer) The holding deposit is returned at the end of your meal deliver service. If there is any damage for the glassware or the bags the amount for those damaged items will be deducted.Below is the link to pay the container deposit. Once paid mark the check box for paid.',
            'created' => 'Created',
            'sendToEmail' => 'Send To Email',
        ];
    }

    public function prepareToEmail()
    {
    	$data = [];
    	$attributes = $this->getAttributes(null, ['id', 'created', 'sendToEmail']);

    	foreach ($this->attributeLabels() as $key=>$name) {
    		if (!array_key_exists($key, $attributes)) continue;

    		if ($key == 'startMealDeliveryDate') {
    			$data[] = [
    				'q' => $name,
				    'a' => date("m/d/Y", strtotime($attributes[$key]))
			    ];
		    } else {
			    $data[] = [
				    'q' => $name,
				    'a' => $attributes[$key]
			    ];
		    }
	    }

	    return $data;
    }

    public function sendToEmail()
    {
	    /** @var \app\components\MailerHelper $mailerHelper */
	    $mailerHelper = Yii::$app->mailerHelper;
	    $fromMail = Yii::$app->params['emails']['service']['no-reply']['email'];
	    $fromName = Yii::$app->params['emails']['service']['no-reply']['name'];
	    $toMail = $this->email;
	    $data = $this->prepareToEmail();

	    $mailerHelper->compose($fromMail, $fromName, $toMail, $data, MailerHelper::TYPE_FORM_ECHO)->send();
    }
}
