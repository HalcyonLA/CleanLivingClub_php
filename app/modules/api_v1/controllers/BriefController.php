<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 04.02.16
 */

namespace app\modules\api_v1\controllers;

use app\models\Brief;
use app\models\DeviceTokens;
use app\models\LoginForm;
use app\models\Messages;
use app\models\ParticipantsChats;
use app\models\query\FriendsRelations;
use app\models\FriendsRelations as FriendsModel;
use app\models\UsersPushSettings;
use Yii;
use app\components\ApiController;
use yii\base\Exception;
use yii\helpers\Url;
use app\controllers;
use app\models\Users;
use app\models\query\Users as UsersQuery;
use yii\helpers\VarDumper;

use Jose\Factory\JWKFactory;
use Jose\Loader;
use \Firebase\JWT\JWT;


class BriefController extends ApiController
{
	private $_updateColumn;

	public function behaviors()
	{
		return [
			'authenticate' => [
				'class' => 'app\modules\api_v1\filters\AuthenticateFilter',
			    'only' => [
						'fill',
				],
			]
		];
	}

    public function fillAttributes()
    {
        return [
            self::OBJECT_PARAMS => [
	            'email' => ['', true],
	            'phone' => ['', true],
	            'startMealDeliveryDate' => ['2017-08-16 00:00:00', true],
	            'mealDeliveryAddress' => ['14', true],
	            'buildingEnterInstructions' => ['14', true],
	            'foodAllergies' => ['14', true],
	            'medications' => ['14', true],
	            'healthGoals' => ['14', true],
	            'weightLossGoal' => ['14', true],
	            'height' => ['14', true],
	            'weight' => ['14', true],
	            'age' => ['14', true],
	            'energyGoals' => ['14', true],
	            'beautyGoals' => ['14', true],
	            'smthAbBeautyGoals' => ['14', true],
	            'favoriteBreakfasts' => ['14', true],
	            'favoriteLunches' => ['14', true],
	            'favoriteSoups' => ['14', true],
	            'favoriteSalads' => ['14', true],
	            'favoriteSweetSnacks' => ['14', true],
	            'favoriteSalSpSnacks' => ['14', true],
	            'payDeposit' => ['0', true, '0 or 1'],
            ]
        ];
    }

    public function actionFill()
    {
		$item = new Brief();
		$item->setAttributes($this->_jsonRequest);
		$item->userId = Yii::$app->user->getIdentity()->getId();

		if (!$item->validate()) {
			$this->_handlerErrors($item);
			return;
		}
		$item->save();

		$this->_jsonResponse['status'] = 'ok';
		$this->_jsonResponse['data'] = $item->getAttributes();
    }

}
