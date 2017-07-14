<?php
/**
 * Created by MI7
 * @author Aleksander Mokhonko
 * Date: 10.02.15 11:17
 *
 * @uses TConsoleRunner
 * Fill in /etc/crontab: * * * * * root php /path/to/domain/basic/yii cron/run
 */
namespace app\commands;

use yii\console\Controller;
use Yii;

class CronController extends Controller
{
	private $_always, $_min, $_hour, $_day, $_mon, $_year, $_dow, $_path;

	/**
	 * [commandName] => timeStart
	 * always: true/false (always run)
	 * min: 00-59
	 * hour: 00-23
	 * day: 1-31
	 * mon: 1-12
	 * year: 2015
	 * dow: 0-6 (0 - Sunday, 6 - Saturday)
	 *
	 * @var array
	 */
	private $_arrayCommands = [
		'notifications/push' => ['always' => true],
		/*
		 *
		 Example
		 'SendEmail' => array('always' => true), //run always
		'DeleteUsers' => array(
			'hour' 	=> '16',
			'min' => '12, 18, 25, 45, 46'
		),*/
	];

	/**
	 * Init time
	 * @param bool $always
	 */
	public function init($always = true)
	{
		$this->_always  = $always;
		$this->_min 	= date('i');
		$this->_hour 	= date('H');
		$this->_day 	= date('d');
		$this->_mon 	= date('m');
		$this->_year 	= date('Y');
		$this->_dow 	= date('w');
		$this->_path 	= Yii::getAlias('webroot');
	}

	/**
	 * Run commands
	 * @return void
	 */
	public function actionRun()
	{
		foreach($this->_arrayCommands as $command => $datetime) {
			if($this->_checkDatetime($datetime)) {
				$this->_runCommands($command);
			}
		}
	}

	/**
	 * Check start time
	 * @param $datetime
	 * @return bool
	 */
	private function _checkDatetime($datetime)
	{
		if(is_array($datetime) && !empty($datetime)) {
			foreach($datetime as $key => $value) {
				if(isset($this->{'_' . $key})) {
					if(!$this->_isArray($this->{'_' . $key}, $value)) {
						return false;
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Check data
	 * @param $now
	 * @param $value
	 * @return bool
	 */
	private function _isArray($now, $value)
	{
		if(stripos($value, ',') === false) {
			if($value != $now) {
				return false;
			}
		}
		elseif(!$this->_checkInPart($value, $now)) {
			return false;
		}

		return true;
	}

	/**
	 * If the predetermined time intervals separated by commas
	 * @param $string
	 * @param $value
	 * @return bool
	 */
	private function _checkInPart($string, $value)
	{
		$arrayItem = explode(',', $string);
		$arrayItem = $this->_trim($arrayItem);
		foreach($arrayItem as $partValue) {
			if($partValue == $value) {
				return true;
			}
		}
		return false;
	}

	/**
	 * trim()
	 * @param $array
	 * @return mixed
	 */
	private function _trim($array)
	{
		foreach($array as $k => $v) {
			$array[$k] = (int) trim($v);
		}
		return $array;
	}

	/**
	 * Run command
	 * @param $command
	 */
	private function _runCommands($command)
	{
		Yii::$app->console->run($command);
	}
}
