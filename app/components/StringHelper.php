<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 30.12.15
 */
namespace app\components;

use yii\base\Component;
use Yii;
use yii\helpers\VarDumper;

class StringHelper extends Component
{
	/**
	 * Convert controller|action name to route
	 * @param $name
	 * @return string
	 */
	public static function route($name)
	{
		preg_match_all('/((?:^|[A-Z])[a-z]+)/', $name, $matches);
		$result = [];
		array_walk($matches[0], function($item) use (&$result) {
			$result[] = strtolower($item);
		});

		return implode('-', $result);
	}
}