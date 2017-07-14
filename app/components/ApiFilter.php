<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 19.11.15
 */
namespace app\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

class ApiFilter extends ActionFilter
{
	protected $_jsonRequest = null;
	protected $_jsonResponse = ['status' => '', 'data' => '', 'message' => ''];
	protected $_errorMessage;

	/**
	 * @param $errors
	 * @return mixed
	 */
	protected function _error($errors)
	{
		$this->_errorMessage = $errors;
		$this->_getJsonRequest();
		$this->_createErrorResponse();
		$this->_log();
		return $this->_afterAction();
	}

	/**
	 * @return void
	 */
	private function _getJsonRequest()
	{
		$this->_jsonRequest = Yii::$app->request->getBodyParam(ApiController::OBJECT_PARAMS, []);
		if(empty($this->_jsonRequest)) {
			foreach($_GET as $key => $param) {
				$this->_jsonRequest[$key] = $param;
			}
		}
	}

	/**
	 * Create error response
	 * @return void
	 */
	private function _createErrorResponse()
	{
		$this->_jsonResponse['status'] = 'error';
		$this->_jsonResponse['message'] = $this->_errorMessage;
	}

	/**
	 * Returns response after actions
	 */
	private function _afterAction()
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		return json_encode($this->_jsonResponse);
	}

	/**
	 * Record of requests and responses in the table 'logdb'
	 */
	private function _log()
	{
		$log =[
			'url'			=> Yii::$app->request->getUrl(),
			'ip'			=> Yii::$app->request->getUserIP(),
			'_jsonRequest' 	=> $this->_jsonRequest,
			'_jsonResponse' => $this->_jsonResponse,
		];

		Yii::info($log, 'db_log');
	}
}