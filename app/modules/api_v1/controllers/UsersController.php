<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 04.02.16
 */

namespace app\modules\api_v1\controllers;

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


class UsersController extends ApiController
{
	private $_updateColumn;

	public function behaviors()
	{
		return [
			'authenticate' => [
				'class' => 'app\modules\api_v1\filters\AuthenticateFilter',
			    'only' => [
						'logout',
						'update',
						'delete-me',
						'user-info',
						'push-settings',
						'set-device-token',
						'related',
						'add-phone-number',
				],
			]
		];
	}

    public function loginAsAttributes()
    {
        return [
            self::OBJECT_PARAMS => [
                'id' 	    => ['14', true],
            ]
        ];
    }

    public function actionLoginAs()
    {
        $allowedHosts = [
            'dev.mytimebot.com',
            'timebot.loc'
        ];

//        if (!in_array($_SERVER['HTTP_HOST'], $allowedHosts)) {
//            $this->_jsonResponse['status'] = 'error';
//            $this->_jsonResponse['message'] = 'The feature only for dev server!';
//            return;
//        }

        $user = Users::findOne(['id' => $this->_jsonRequest['id']]);
        if (!is_null($user)) {
            Yii::$app->user->login($user, Users::SESSION_TIME);
            $this->_jsonResponse['status'] = 'ok';
            $this->_jsonResponse['data'] = Users::renderOne($user);
            return;
        }
    }


	public function deleteMeAttributes()
	{
		return [
			self::OBJECT_PARAMS => []
		];
	}

	public function actionDeleteMe()
	{
		/** @var Users $user */
		$user = Yii::$app->user->getIdentity();
		$user->selfRemove();
		if ($user->hasErrors()) {
			$this->_handlerErrors($user);
			return;
		}
		$this->_jsonResponse['status'] = 'ok';
	}


	public function registrationAttributes()
	{
		return [
			self::OBJECT_PARAMS => [
				'email' 	        	=> ['halcyon.third@gmail.com', true],
				'password' 			=> ['123456789', true],
				'firstName' 	    	=> ['Test', true],
				'lastName' 	        => ['User1', true],
				'photo'				=> [''],
				'photoThumbnail'	=> [''],
			]
		];
	}

	/**
	 * Registration users
	 * @return void
	 */
	public function actionRegistration()
	{
		$users = new Users();
		$users->setScenario('create');
		$users->attributes = $this->_jsonRequest;
		if($users->validate()) {
			$users->password = $users->hashPassword($users->password);
			$users->save('');
			$users->createAt = date("Y-m-d H:i:s");

			$loginForm = new LoginForm();
			$loginForm->attributes = [
				'email' =>  $users->email,
				'password' =>  $this->_jsonRequest['password'],
			];

			if($loginForm->validate() && $loginForm->login('email')) {
				$this->_jsonResponse['status'] = 'ok';
				$this->_jsonResponse['data'] = Yii::$app->pathTransformer->transform($loginForm->userInfo->getAttributes(), ['photo', 'photo_thumbnail']);
				return;
			}
			else {
				$this->_handlerErrors($loginForm);
			}
		}
		else {
			$this->_handlerErrors($users);
		}
	}

	public function userInfoAttributes()
	{
		return [
			self::OBJECT_PARAMS => [
				'id' 	        => ['1', false],
			]
		];
	}

	public function actionUserInfo()
	{
		if (isset($this->_jsonRequest['id']) && !empty($this->_jsonRequest['id'])) {
			$user = Users::find()->where(['id' => $this->_jsonRequest['id']])->one();
		} else {
			$user = Yii::$app->user->getIdentity();
		}

		if (is_null($user)) {
			$this->_jsonResponse['status'] = 'error';
			$this->_jsonResponse['message'] = Yii::$app->errors->get(500000);
			return;
		}

		$this->_jsonResponse['status'] = 'ok';
		$this->_jsonResponse['data'] = Users::renderOne($user);
	}



	public function updateAttributes()
	{
		if (is_null($this->getUser())) return;

		return [
			self::OBJECT_PARAMS => [
				'email' 	        => [$this->getUser()->email],
				'password' 			=> [''],
				'firstName' 	    => [$this->getUser()->firstname],
				'lastName' 	        => [$this->getUser()->lastname],
			],
			self::OBJECT_FILES => [
				'photo' => ['', true]
			]
		];
	}

	/**
	 * @return void
	 */
	public function actionUpdate()
	{
		/** @var Users $user */
		$user 			= Yii::$app->user->getIdentity();

		$user->setScenario('update');
		if (!empty($user->facebookId) && intval($user->facebookId) > 0) {
			$user->setScenario('facebookUpdate');
		}

		if (isset($this->_jsonRequest['email'])) {
			$user->email 	= $this->_empty('email', $user->email);
		}

		if (isset($this->_jsonRequest['password']) && $this->_jsonRequest['password'] !== '') {
			if($this->_empty($this->_jsonRequest['password'])) {
				$user->password = $user->hashPassword($this->_empty('password', $user->password));

			}
		}

		if (isset($this->_jsonRequest['firstName']) && !empty($this->_jsonRequest['firstName'])) {
			$user->firstname = $this->_jsonRequest['firstName'];
			$this->_updateColumn[] = 'firstName';
		}
		if (isset($this->_jsonRequest['lastName']) && !empty($this->_jsonRequest['lastName'])) {
			$user->lastname = $this->_jsonRequest['lastName'];
			$this->_updateColumn[] = 'lastName';
		}


		$rFile = false;
		if (isset($_FILES['photo']['tmp_name'])) {
			$rFile = $_FILES['photo']['tmp_name'];
		}

		if ($rFile) {
			$user->photo = $_FILES['photo']['tmp_name'];


			if ($user->validate(['photo'])) {
				$bigImage = Yii::$app->imgProcessor->fitByWidth($this->getUser()->getId(), $user->photo, 3000);
				$smallImage = Yii::$app->imgProcessor->fitByWidth($this->getUser()->getId(), $bigImage, 650);

				$user->photo = Yii::$app->pathTransformer->absToLegasy($bigImage);
				$user->photo_thumbnail = Yii::$app->pathTransformer->absToLegasy($smallImage);
				$this->_updateColumn[] = 'photo';
				$this->_updateColumn[] = 'photoThumbnail';

			} else {
				unset($this->_updateColumn['photo']);
			}
		}



		if($user->validate($this->_updateColumn)) {

			if (!$user->update($this->_updateColumn)) {
				if (empty($user->getErrors())) {
					$this->_jsonResponse['status'] = 'ok';
					return;
				}
				$this->_handlerErrors($user);
				return;
			} else {
				$this->_jsonResponse['status'] = 'ok';
				$this->_jsonResponse['data'] = Yii::$app->pathTransformer->transform($user->getAttributes(), ['photo', 'photoThumbnail']);
				return;
			}

		}
		else {

			$this->_handlerErrors($user);
		}

	}

	public function loginAttributes()
	{
		return [
			self::OBJECT_PARAMS => [
				'email' 	=> ['halcyon.third@gmail.com', true],
				'password' 	=> ['123456789', true]
			]
		];
	}

	public function actionLogin()
	{
		$loginForm = new LoginForm();
		$loginForm->attributes = $this->_jsonRequest;
		if($loginForm->login()) {
			/** @var Users $user */
			$user = Yii::$app->user->getIdentity();
			$this->_jsonResponse['status'] = 'ok';
			$this->_jsonResponse['data'] = Yii::$app->pathTransformer->transform($user->getAttributes(), ['photo', 'photo_thumbnail']);
		}
		else {
			$this->_handlerErrors($loginForm);
		}
	}



	public function facebookLoginAttributes()
	{
		return [
			self::OBJECT_PARAMS => [
				'facebookToken'	=> ['CAANCOFCf7moBAD3FJLvdEZCT9AfHdH9uw0RZB05wPoRkjVnkOZApKfRwz6far9oX2FQIJeg4ZCtzHbmqjxtihaca2DpxNLVLro8SCxjbfosq0iMAZBfppgXe0HT9dPQpMkbGXs1KLqZCZCmFwxxkA31PBEANt0CHcV5Cy65lX4D3ixGyjJW9wcIKnn0ZBOME9H2z16a514S6VL8aAxVTGuvCv9vqMWYbbSRZBVQe0TD91EgZDZD', true],
			]
		];
	}

	/**
	 * Authenticate user
	 */
	public function actionFacebookLogin()
	{

		$users = new Users();
		$users->setScenario('facebookLogin');
		$users->attributes = $this->_jsonRequest;
		if($users->validate()) {
			$user = $users->facebookLogin();
			if(false === $user) {
				$this->_handlerErrors($users);
			}
			else {
				$this->_jsonResponse['status'] = 'ok';
				$this->_jsonResponse['data'] = Yii::$app->pathTransformer->transform($user, ['photo', 'photo_thumbnail']);
			}
		}
		else {
			$this->_handlerErrors($users);
		}
	}

	public function logoutAttributes()
	{
		return [];
	}

	/**
	 * Logout
	 * @return void
	 */
	public function actionLogout()
	{
		Yii::$app->user->logout();
		$this->_jsonResponse['status'] = 'ok';
	}

	/**
	 * @param $attribute
	 * @param null $default
	 * @return null
	 */
	private function _empty($attribute, $default = null)
	{
		if(empty($this->_jsonRequest[$attribute]) || !isset($this->_jsonRequest[$attribute])) {
			return $default;
		}
		else {
			$this->_updateColumn[] = $attribute;
			return $this->_jsonRequest[$attribute];
		}
	}
}
