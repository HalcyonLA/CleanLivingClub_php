<?php
namespace app\components;

use Yii;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use app\models;

/**
 * @author Aleksandr Mokhonko
 * Date: 17.11.15
 * Class ApiController
 * @package app\components
 *
 * @property array $_jsonRequest
 * @property array $_filesRequest
 * @property array $_jsonResponse
 * @property array $_errorMessage
 */
class ApiController extends Controller
{
	const OBJECT_PARAMS = 'json';
	const OBJECT_FILES 	= 'files';
	protected $_jsonRequest = [];
	protected $_filesRequest = [];
	protected $_jsonResponse = ['status' => '', 'data' => '', 'message' => ''];
	protected $_errorMessage;

	/**
	 * Get data 'json' from device
	 * @return void
	 */
	public function init()
	{
		$json = Yii::$app->request->getBodyParam(self::OBJECT_PARAMS, []);
		if(empty($json)) {
			$this->_jsonRequest = Yii::$app->request->getIsPost() === true ? $_POST : $_GET;
		}
		else {
			$this->_jsonRequest = json_decode($json, true);
		}

		$this->_handlerFiles();
		parent::init();
	}

	/**
	 * Get $_FILES
	 * @return void
	 */
	private function _handlerFiles()
	{
		$files = isset($_FILES[self::OBJECT_FILES]) ? $_FILES[self::OBJECT_FILES] : null;
		if($files !== null) {
			foreach($files['name'] as $key => $array) {
				$countFiles = count($files['name'][$key]);
				for($i = 0; $i < $countFiles; ++$i) {
					$this->_filesRequest[$key][$i] = [
							'name' 		=> $files['name'][$key][$i],
							'type' 		=> $files['type'][$key][$i],
							'tmp_name'	=> $files['tmp_name'][$key][$i],
							'error' 	=> $files['error'][$key][$i],
							'size' 		=> $files['size'][$key][$i],
					];
				}
			}
		}
	}

	/**
	 * @param $model
	 */
	protected function _handlerErrors($model)
	{
		$this->_createErrorMessage($model->getErrors());
		$this->_jsonResponse['status'] = 'error';
		$this->_jsonResponse['data'] = '';
		$this->_jsonResponse['message'] = $this->_errorMessage;

		return $this->_jsonResponse;
	}

	protected function _fetchErrors($model)
	{
		$this->_createErrorMessage($model->getErrors());
		return $this->_errorMessage;
	}

	/**
	 * Create error message
	 * @param $errors
	 * @return void
	 */
	protected function _createErrorMessage($errors, $clean = true)
	{
		if ($clean) $this->_errorMessage = '';

		if(!empty($errors)) {
			foreach($errors as $error) {
				$this->_errorMessage .=  $error[0] . ' ';
			}
		}
	}

	/**
	 * @param \yii\base\Action $action
	 * @return bool
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		return parent::beforeAction($action);
	}

	protected function _nullFilter($modelAttributes)
	{
		foreach ($modelAttributes as &$attr) {
			if ($attr === null) {
				$attr = '';
				continue;
			}
			if ($attr === false || $attr == 'NO') {
				$attr = 0;
				continue;
			}
			if ($attr === true || $attr == 'YES') {
				$attr = 1;
				continue;
			}
		}

		return $modelAttributes;
	}

	protected function getUser()
	{
		return Yii::$app->user->getIdentity();
	}

	/**
	 * Returns response after actions
	 * @param \yii\base\Action $action
	 * @param mixed $result
	 * @return array
	 */
	public function afterAction($action, $result)
	{
		$result = parent::afterAction($action, $result);
		if($action->id !== 'main' && $action->id !== 'testIndex') {
			$this->_log();

//			if (!empty($this->_jsonResponse['data']) &&(is_array($this->_jsonResponse['data']))) {
//				$this->_jsonResponse['data'] = $this->_jsonResponse['data'];
//			}

			if(Yii::$app->request->getBodyParam('testApi')) {
				echo '<pre>';
				echo print_r($this->_jsonResponse);
				echo '</pre>';
			}
			else {
				Yii::$app->response->format = Response::FORMAT_JSON;
//				$this->_jsonResponse['status'] = isset($this->_jsonResponse['status'])
//						? $this->_jsonResponse['status']
//						: '';
//				$this->_jsonResponse['data'] = isset($this->_jsonResponse['data'])
//						? $this->_jsonResponse['data']
//						: '';
//				$this->_jsonResponse['message'] = isset($this->_jsonResponse['message'])
//						? $this->_jsonResponse['message']
//						: '';

				return $this->_jsonResponse;
			}
		}

		return $result;
	}

	/**
	 * Record of requests and responses in the table 'logdb'
	 */
	private function _log()
	{
		$log = [
			'url'			=> Yii::$app->request->getUrl(),
			'ip'			=> Yii::$app->request->getUserIP(),
			'_jsonRequest' 	=> [
				self::OBJECT_PARAMS	=> $this->_jsonRequest,
				self::OBJECT_FILES => $this->_filesRequest,
			],
			'_jsonResponse' => $this->_jsonResponse,
		];

		Yii::info($log, 'db_log');
	}
}