<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 12.01.16
 */
namespace app\modules\main\controllers;

use app\models\Users;



use Yii;
use \app\components\MainController;
use yii\data\ActiveDataProvider;
use \app\models\Chats as BaseChats;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\VarDumper;
use yii\web\Response;

class ChatsController extends MainController
{
	public $defaultAction = 'room';
	public $layout = '@app/views/layouts/main';

	public function behaviors()
	{
		return [
			'authenticate' => [
					'class' => 'app\filters\main\AuthenticateFilter',
					'except' => ['check-online']
			],
		];
	}

	public function beforeAction($action)
	{
		// ...set `$this->enableCsrfValidation` here based on some conditions...
		// call parent method that will check CSRF if such property is true.
		if ($action->id === 'access-node-js') {
			# code...
			$this->enableCsrfValidation = false;
		}
		return parent::beforeAction($action);
	}

	/**
	 * Creates a new Users model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
//	public function actionAdd()
//	{
//		$model = new Users();
//		if ($model->saveInfo(Yii::$app->request->post())) {
//			$forgotPasswordForm = new ForgotPasswordForm();
//			$forgotPasswordForm->setScenario('getEmail');
//			$forgotPasswordForm->setEmail($model->primaryEmail);
//			$forgotPasswordForm->forgot(MailerHelper::TYPE_ADMIN_ADD_USER);
//			return $this->redirect(['view', 'id' => $model->id]);
//		}
//		else {
//			return $this->render('add', ['model' => $model]);
//		}
//	}

	/**
	 * Creates a new Users model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
//	public function actionList()
//	{
//		$model = new Users();
//		$model->setScenario('signUp');
//		$post = Yii::$app->request->post('Users');
//		if($post && $model->saveInfo($post)) {
//			Yii::$app->session->setFlash('successRegistration', 'Success Registration. Sign In, Please!');
//			return $this->redirect(['/users/sign-in']);
//		}
//		else {
//			return $this->render('signUp', ['model' => $model]);
//		}
//	}

	/**
	 * @return string
	 */
	public function actionCheckOnline()
	{
		return Yii::$app->mainAccount->getId() ? 'ok' : '';
	}

	/**
	 * @return string
	 */
//	public function actionSearch()
//	{
//		$searchModel = new UsersSearch();
//		$userId = Yii::$app->mainAccount->getId();
//		$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $userId);
//
//		return $this->render('search', [
//				'searchModel' => $searchModel,
//				'dataProvider' => $dataProvider,
//		        'userId' => $userId
//		]);
//	}

	/**
	 * @param null $id
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionRoom($id = null)
	{
		$this->layout = '@app/views/layouts/messenger';
		/** @var Chats $chat */
		$chat = Chats::find()->byCHash($id);
		/** @var Users $user */
		$user = Yii::$app->mainAccount->identity;

		if(null !== $chat) {
			if(ParticipantsChats::find()->isParticipant($chat->id, $user->id)) {
				$message = new Messages();
				$message->setScenario('create');
				if($message->load(Yii::$app->request->post())) {
					$message->userId = $user->id;
					$message->chatId = $chat->id;
					if($message->validate()) {
						if($message->notSave === false) {
							$message->saveInfo();
						}


						Yii::$app->redis->executeCommand('PUBLISH', [
								'channel' => $id,
								'message' => Json::encode([
										'name' => $user->username,
										'message' => $message->content,
										'userId' => $user->id,
								        'lastId' => $message->id
								])
						]);
					}

					if($message->mode == Messages::MODE_AJAX) {
						Yii::$app->response->format = Response::FORMAT_JSON;
						return Json::encode($message->allForChat(), 0);
					}
				}

				$messages = Messages::find()->byChatId($chat->id, false, 20);

				return $this->render('messenger',
						[
							'chat' => $chat,
							'user' => $user,
							'messages' => array_reverse($messages),
						    'message' => $message
						]
				);
			}
		}

		throw new NotFoundHttpException('Forbidden.');
	}

	/**
	 * @param null $id
	 * @return string
	 * @throws NotFoundHttpException
	 */
//	public function actionRoomOld($id = null)
//	{
//		$this->layout = '@app/views/layouts/messenger';
//		/** @var Chats $chat */
//		$chat = Chats::find()->byCHash($id);
//		/** @var Users $user */
//		$user = Yii::$app->mainAccount->identity;
//
//		if(null !== $chat) {
//			if(ParticipantsChats::find()->isParticipant($chat->id, $user->id)) {
//				$message = new Messages();
//				$message->setScenario('create');
//				if($message->load(Yii::$app->request->post())) {
//					$message->userId = $user->id;
//					$message->chatId = $chat->id;
//					if($message->validate()) {
//						$message->saveInfo();
//					}
//				}
//
//
//				$messages = Messages::find()->byChatId($chat->id, false, 20);
//
//				return $this->render('messenger',
//						[
//								'chat' => $chat,
//								'user' => $user,
//								'messages' => array_reverse($messages),
//								'message' => $message
//						]
//				);
//			}
//		}
//
//		throw new NotFoundHttpException('Forbidden.');
//	}

	/**
	 * @param $uid
	 * @return \yii\web\Response
	 * @throws ForbiddenHttpException
	 */
	public function actionFind($uid)
	{
		/** @var Users $user */
		$user = Yii::$app->mainAccount->identity;
		$friend = (new Contacts())->isFriends($user->id, $uid);
		$blocked = (new BlackList())->isBlocked($user->id, $uid);

		/** @var Chats $chat */
		$chat = (false === $friend || true === $blocked)
				? false
				: Chats::find()->byInterlocutorId($uid, $user->id);
		if(false === $chat) {
			throw new ForbiddenHttpException('Forbidden.');
		}
		else {
			if(null === $chat) {
				$chat = new Chats();
				$chat->create($uid, $user->id, false);
			}

			Yii::$app->session->set(BaseChats::DEFAULT_CHAT_NAME, ['type' => BaseChats::CHAT_TYPE_MAIN, 'uid' => $uid]);

			$interlocutor = Users::find()->byUserId($uid);

			return $this->renderPartial('partial/_messenger', ['chat' => $chat, 'user' => $user, 'interlocutor' => $interlocutor]);

			//return $this->redirect(['/main/chats/room', 'id' => $chat->cHash]);
		}
	}

	/**
	 * @return array
	 */
	public function actionAccessNodeJs()
	{
		$cHash = Yii::$app->request->post('cHash');
		/** @var Users $user */
		$user = Yii::$app->user;

		if ($user->web)


		$userId = Yii::$app->mainAccount->getId();
		$chat = Chats::find()->byCHash($cHash);
		Yii::$app->response->format = Response::FORMAT_JSON;
		if(null != $chat) {
			if(ParticipantsChats::find()->isParticipant($chat->id, $userId)) {
				return ['status' => 'ok'];
			}
		}

		return ['status' => 'error'];
	}

	/**
	 * @return string
	 */
//	public function actionFriendList()
//	{
//		$searchModel = new UsersSearch();
//		$userId = Yii::$app->mainAccount->getId();
//		$params = Yii::$app->request->queryParams;
//		$params['Users']['contactsStatus'] = Contacts::STATUS_SUCCESS;
//		$dataProvider = $searchModel->search($params, $userId);
//
//		return $this->render('search', [
//				'searchModel' => $searchModel,
//				'dataProvider' => $dataProvider,
//				'userId' => $userId
//		]);
//	}
//
//	/**
//	 * Updates an existing Users model.
//	 * If update is successful, the browser will be redirected to the 'profile' page.
//	 * @param integer $id
//	 * @return string|\yii\web\Response
//	 * @throws ForbiddenHttpException
//	 * @throws NotFoundHttpException
//	 */
//	public function actionUpdate($id)
//	{
//		if(Yii::$app->mainAccount->getId() == $id) {
//			$model = $this->findModel($id);
//			$post = Yii::$app->request->post();
//			$this->_convertFiles('Users');
//			if($model->load($post) && $model->saveInfo($model->getAttributes(), $this->_filesRequest)) {
//				Yii::$app->session->set('logoMain', Yii::$app->mainAccount->identity->getUrlLogo(Files::CATEGORY_SMAll));
//
//				return $this->redirect(['profile', 'id' => $model->id]);
//			}
//			else {
//				return $this->render('update', [
//						'model' => $model,
//				]);
//			}
//		}
//		else {
//			throw new ForbiddenHttpException('Forbidden.');
//		}
//	}
//
//	/**
//	 * Displays a single Users model.
//	 * @param $id
//	 * @return string
//	 * @throws ForbiddenHttpException
//	 */
//	public function actionProfile($id)
//	{
//		$userId = Yii::$app->mainAccount->getId();
//		$mainContacts = new Contacts();
//		if($userId == $id || $mainContacts->isFriends($userId, $id)) {
//			return $this->render('profile', [
//					'model' => $this->findModel($id),
//					'userId' => $userId,
//			]);
//		}
//		else {
//			throw new ForbiddenHttpException('Forbidden.');
//		}
//	}
//
//	/**
//	 * @return string|\yii\web\Response
//	 */
//	public function actionSignIn()
//	{
//		$loginForm = new Login();
//		$post = Yii::$app->request->post('Login');
//		if(null !== $post) {
//			$loginForm->attributes = $post;
//			if($loginForm->login()) {
//				return $this->redirect(['/users/profile', 'id' => Yii::$app->mainAccount->getId()]);
//			}
//		}
//
//		return $this->render('login', ['model' => $loginForm]);
//	}
//
//	/**
//	 * @return \yii\web\Response
//	 */
//	public function actionLogout()
//	{
//		Yii::$app->mainAccount->logout(false);
//		return $this->redirect(Url::toRoute(['/users/sign-in']));
//	}
//
//	/**
//	 * @return string
//	 */
//	public function actionActivities()
//	{
//		$searchModel = new ActivitiesSearch();
//		$queryParams = Yii::$app->request->queryParams;
//		$queryParams[$searchModel->formName()]['userId'] = Yii::$app->mainAccount->getId();
//		$dataProvider = $searchModel->search($queryParams);
//		return $this->render('activities', ['dataProvider' => $dataProvider]);
//	}
//
//	/**
//	 * Input mail to reset your password
//	 * @return string
//	 */
//	public function actionForgotPassword()
//	{
//		$post = Yii::$app->request->post('ForgotPassword');
//		$forgotPasswordForm = new ForgotPasswordForm();
//		$forgotPasswordForm->setScenario('getEmail');
//		if(null !== $post) {
//			$forgotPasswordForm->attributes = $post;
//			if($forgotPasswordForm->validate() && $forgotPasswordForm->forgot()) {
//				return $this->render('successForgotPassword', ['primaryEmail' => $forgotPasswordForm->primaryEmail]);
//			}
//		}
//
//		return $this->render('forgotPassword', array('forgotPasswordForm' => $forgotPasswordForm));
//	}
//
//	/**
//	 * Reset user password
//	 * @param $hash
//	 * @return string
//	 */
//	public function actionResetPassword($hash)
//	{
//		$forgotPasswordForm = new ForgotPasswordForm();
//		$forgotPasswordForm->setScenario('checkHash');
//		$forgotPasswordForm->hash = $hash;
//		$post = Yii::$app->request->post('ForgotPassword');
//		if($forgotPasswordForm->validate()) {
//			if(null !== $post) {
//				$forgotPasswordForm->setScenario('resetPassword');
//				$forgotPasswordForm->attributes = $post;
//				if($forgotPasswordForm->validate() && $forgotPasswordForm->reset()) {
//					return $this->render('successResetPassword');
//				}
//			}
//		}
//
//		return $this->render('resetPassword', ['forgotPasswordForm' => $forgotPasswordForm]);
//	}
//
//	/**
//	 * Finds the Users model based on its primary key value.
//	 * If the model is not found, a 404 HTTP exception will be thrown.
//	 * @param integer $id
//	 * @return Users the loaded model
//	 * @throws NotFoundHttpException if the model cannot be found
//	 */
//	protected function findModel($id)
//	{
//		if (($model = Users::find()->byUserId($id)) !== null) {
//			return $model;
//		}
//		else {
//			throw new NotFoundHttpException('The requested page does not exist.');
//		}
//	}
}