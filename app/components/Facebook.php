<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 20.11.15
 */
namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\VarDumper;

class Facebook extends Component
{
	private $_facebookData = [];
	private $_relations = [
		//Our DB				=> FB DB
		'facebookId' 			=> 'id',
		'email' 		        => 'email',
		'birth_date' 			=> 'birthday',
		'first_name' 			=> 'first_name',
		'last_name' 			=> 'last_name',
		'facebook_user_name'	=> 'name'
	];

	/**
	 * @param $token
	 * @return array
	 */
	public function login($token)
	{
		$this->_facebookData['facebookToken'] = $token;
		$facebook = $this->_getFacebookInfo();
		$this->_createResponse($facebook);
		return $this->_facebookData;
	}

	/**
	 * @return mixed
	 */
	private function _getFacebookInfo()
	{
		$ch = curl_init();

		if(!$ch) {
//			Yii::info('Not cURL init.', 'error');
			return [];
		}

		$cookies = dirname(__DIR__) . '/runtime/cookies/facebook_cookies.txt';

		$header[] = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		$header[] = "Accept-Encoding:identity";
		$header[] = "Accept-Language:ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4";
		$header[] = "Cache-Control:max-age=0";
		$header[] = "Connection:keep-alive";

		curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me/?fields=id,name,email,first_name,last_name,picture&access_token=' . $this->_facebookData['facebookToken']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36");
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_REFERER, '');

		$response = curl_exec($ch);

		if(curl_errno($ch)) {
//			Yii::info(VarDumper::dumpAsString(curl_error($ch)), 'error');
//			Yii::info(VarDumper::dumpAsString(curl_errno($ch)), 'error');
		}
		else {
			/* return
		$aData = stdClass::__set_state(array(
			   'id' => '1007100706008885',
			   'birthday' => '01/26/1948',
			   'email' => 'fa8m@ua.fm',
			   'first_name' => 'Alexandr',
			   'gender' => 'male',
			   'last_name' => 'Rozenbaum',
			   'link' => 'https://www.facebook.com/app_scoped_user_id/1007100706008885/',
			   'locale' => 'ru_RU',
			   'name' => 'Alexandr Rozenbaum',
			   'timezone' => 4,
			   'updated_time' => '2014-12-07T07:44:31+0000',
			   'verified' => true,
			))
		*/
			 return json_decode($response);
		}
	}

	/**
	 * @param $facebook
	 * @return void
	 */
	private function _createResponse($facebook)
	{
		foreach($this->_relations as $keyOurDb => $keyFbDb) {
			if(isset($facebook->$keyFbDb)) {
				if($keyFbDb == 'birthday') {
					if(preg_match('#^\d{2}/\d{2}/\d{4}$#', $facebook->birthday)) {
						$birthday = \DateTime::createFromFormat('m/d/Y', $facebook->birthday);
						if(false !== $birthday) {
							$this->_facebookData[$keyOurDb]  = $birthday->format('Y-m-d');
						}
					}
				}
				else {
					$this->_facebookData[$keyOurDb] = $facebook->$keyFbDb;
				}
			}
		}
	}
}