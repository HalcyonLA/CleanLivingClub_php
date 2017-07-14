<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 29.12.15
 */
namespace app\modules\api_v1;

use \yii\base;

class Api extends base\Module
{
	public $defaultController = 'help';
	public $controllerNamespace = 'app\modules\api_v1\controllers';

	public function init()
	{
		parent::init();
		// custom initialization code goes here
	}
}