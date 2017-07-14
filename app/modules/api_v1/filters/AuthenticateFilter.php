<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 30.12.15
 */
namespace app\modules\api_v1\filters;

use Yii;
use app\components\ApiFilter;
use yii\base\Model;
use yii\helpers\VarDumper;

class AuthenticateFilter extends ApiFilter
{
	public function beforeAction($action)
	{
		if(false === Yii::$app->user->getIsGuest()) {
			return parent::beforeAction($action);
		}
		else {
			echo $this->_error($this->_jsonResponse['message'] = Yii::$app->errors->get(500004));
			return false;
		}
	}

	public function afterAction($action, $result)
	{
		return parent::afterAction($action, $result);
	}
}