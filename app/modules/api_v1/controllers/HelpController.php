<?php
/**
 * @version 1.0
 * @author Aleksandr Mokhonko
 * Date: 29.12.15
 */
namespace app\modules\api_v1\controllers;

use Yii;
use app\components\ApiController;
use app\components\StringHelper;
use yii\helpers\Url;
use app\controllers;
use yii\helpers\VarDumper;

class HelpController extends ApiController
{
	public $defaultAction		= 'main';
	public $layout				= 'test';
	private $_controller 		= '';
	private $_method 			= '';
	private $_allActions 		= [];
	private $_methodAttributes 	= null;
	private $_methodFiles 		= null;
	private $_excludeClasses 	= ['help', 'test'];
	private $_excludeMethods 	= ['actions'];

	/**
	 * @param string $c Controller name
	 * @param string $m Method name
	 * @return string
	 */
	public function actionMain($c = null, $m = null)
	{
		$this->_controller = $c;
		$this->_method = $m;
		$this->_getClasses();
		return $this->render('main', [
				'data' 				=> $this->_allActions,
				'methodAttributes' 	=> $this->_methodAttributes,
				'methodFiles' 		=> $this->_methodFiles,
				'controllerName' 	=> $this->_controller,
				'methodName'		=> $this->_method,
				'nameAttributes'	=> self::OBJECT_PARAMS,
				'nameFiles'			=> self::OBJECT_FILES
		]);
//		echo '<pre>';
//		var_export($this->_methodAttributes);
//		echo '</pre>';
//		echo '<pre>';
//		var_export($this->_allActions);
//		echo '</pre>';
	}

	private function _getClasses()
	{
		foreach(glob(__DIR__ . "/*.php") as $file) {
			$id = lcfirst(str_replace('Controller.php', '', basename($file)));
			if(!in_array($id, $this->_excludeClasses)) {
				$this->_allActions[] = [
						'id' 		=> $id,
						'filePath' 	=> $file,
						'fileName' 	=> basename($file),
						'className' => str_replace('.php', '', basename($file)),

				];
			}
		}

		$this->_getMethods();
	}

	private function _getMethods()
	{
		foreach($this->_allActions as $key => $classInfo) {
			require_once $classInfo['filePath'];
			$name = 'app\modules\api_v1\controllers\\' . $classInfo['className'];
			$classObject = new $name($classInfo['id'], 'api');
			$methods = get_class_methods($classObject);
			sort($methods);
			foreach($methods as $method) {
				if(0 === strpos($method, 'action') && !in_array($method, $this->_excludeMethods)) {
					$methodName = lcfirst(str_replace('action', '', $method));
					if($classInfo['id'] == $this->_controller && $methodName == $this->_method) {
						$activeMethodName = $this->_method . "Attributes";
						if(true === method_exists($classObject, $activeMethodName)) {
							$methodAttributes = $classObject->$activeMethodName();
							$this->_methodAttributes = empty($methodAttributes[self::OBJECT_PARAMS]) ? [] : $methodAttributes[self::OBJECT_PARAMS];
							$this->_methodFiles = empty($methodAttributes[self::OBJECT_FILES]) ? [] : $methodAttributes[self::OBJECT_FILES];
						}
						else {
							$this->_methodAttributes = null;
							$this->_methodFiles = null;
						}
					}

					$this->_allActions[$key]['methods'][] = [
							'methodName' 	=> $methodName,
							'url'			=> Url::toRoute(
									[StringHelper::route($this->_allActions[$key]['id']) . '/' . StringHelper::route($methodName)]
							)
					];
				}
			}
		}
	}
}