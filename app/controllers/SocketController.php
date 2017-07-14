<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 12.01.16
 */
namespace app\controllers;

use app\models\Users;
use yii\web\Controller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\VarDumper;
use yii\web\Response;

class SocketController extends Controller
{
	public $defaultAction = 'room';
	public $layout = '@app/views/layouts/main';

	public function behaviors()
	{
		return [
			'authenticate' => [
					'class' => 'app\modules\api_v1\filters\AuthenticateFilter',
					'except' => ['check-online']
			],
		];
	}

	public function beforeAction($action)
	{
		// ...set `$this->enableCsrfValidation` here based on some conditions...
		// call parent method that will check CSRF if such property is true.
		if ($action->id === 'access-node-js' || $action->id === 'on-demand') {
			# code...
			$this->enableCsrfValidation = false;
		}
		return parent::beforeAction($action);
	}

    public function actionOnDemand()
    {
        $command = 'timesync/on-demand ' . Yii::$app->request->post('userId');
        Yii::$app->console->run($command);

        $command = 'socket/update-user-info ' . Yii::$app->request->post('userId');
        Yii::$app->console->run($command);
        return 'ok';
    }

	/**
	 * @return string
	 */
	public function actionCheckOnline()
	{
		return Yii::$app->user->id ? 'ok' : '';
	}

	/**
	 * @param null $id
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionRoom($id = null)
	{
		$this->layout = '@app/views/layouts/main';
		/** @var Chats $chat */
//		$chat = Chats::find()->byCHash($id);
		/** @var Users $user */
		$user = Yii::$app->user->getIdentity();
		echo $id;

		Yii::$app->redis->executeCommand('PUBLISH', [
				'channel' => $id,
				'message' => Json::encode([
					'name' => $user->email,
					'userId' => $user->id,
				])
		]);



		return $this->render('messenger',
				[
						'chat' => '',
						'user' => '',
						'messages' => '',
						'message' => ''
				]
		);
	}


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
		Yii::$app->response->format = Response::FORMAT_JSON;

		/** @var Users $user */
		$user = Yii::$app->user->getIdentity();
		if ($user->websocketHash == $cHash) {
			return ['status' => 'ok', 'user' => $user->getAttributes()];
		}
		return ['status' => 'error'];
	}

}