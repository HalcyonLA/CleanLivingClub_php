<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 10.02.16
 *
 * @property string $apnsHostProd
 * @property string $apnsHostDev
 * @property int $apnsPort
 * @property string $apnsCertProd
 * @property string $apnsCertDev
 * @property string $apnsPassphrase
 * @property string $timeout
 * @property string $errorResponse
 *
 */

namespace app\components;

use yii\base\Component;
use Yii;
use yii\helpers\VarDumper;


class ApplePushNotification extends Component
{
	const TYPE_DEV  = 'dev';
	const TYPE_PROD = 'prod';

    public $apnsHostProd;
	public $apnsHostDev;
	public $apnsPort;
	public $apnsCertProd;
	public $apnsCertDev;
	public $apnsPassphrase;
	public $timeout;
	public $errorResponse;

	private $_type;
	private $_socketClient;
	private $_context;
	private $_error;
	private $_errorString;
    private $_apnsHost;
    private $_apnsCert;

	/**
	 * @param $alert ['message' = > '', 'type' => '', 'objectId' => '']
	 * @param $token
	 * @param string $type
	 * @param $sound
	 */
	public function sendNotification($alert, $token, $type, $sound = null)
	{
		$this->_init($type);
	    $this->_setContext();
		$this->_createStreamSocket();

		// Create the payload body
		$body['aps'] = array(
				'badge' => +1,
				'alert' => $alert['message'],
				'type' => $alert['type'],
				'object_id' => $alert['objectId'],
		);

		if ($alert['type'] == 'new-timesync') {
			if (!is_null($sound)) {
				$body['aps']['sound'] = $sound;
			}
		} elseif ($alert['type'] == 'new-world-timesync') {
			if (!is_null($sound)) {
				$body['aps']['sound'] = $sound;
			}
		} elseif ($alert['type'] == 'new-message') {
			if (!is_null($sound)) {
				$body['aps']['sound'] = $sound;
			}
		} else {
			$body['aps']['sound'] = 'default';
		}



		$payload = json_encode($body);
		// Build the binary notification
		$message = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		stream_set_blocking($this->_socketClient, 0);
		fwrite($this->_socketClient, $message, strlen($message));
		usleep($this->timeout);
		$errorAppleResponse = fread($this->_socketClient, 6);
		if (!empty($errorAppleResponse)) {
			// unpack the error response (first byte 'command" should always be 8)
			/**
			    'command' => 8
				'status_code' => 7
				'identifier' => 0
			 */
			$this->errorResponse = unpack('Ccommand/Cstatus_code/Nidentifier', $errorAppleResponse);
			$this->_createErrorMessage();
		}

		//return (bool) $result;
	}

	private function _init($type)
    {
        if($type == self::TYPE_PROD) {
            $this->_apnsHost = $this->apnsHostProd;
            $this->_apnsCert = $this->apnsCertProd;
        }
        elseif($type == self::TYPE_DEV) {
            $this->_apnsHost = $this->apnsHostDev;
            $this->_apnsCert = $this->apnsCertDev;
        }
    }

	/**
	 * @return void
	 */
	public function closeConnect()
	{
		// Close the connection to the server
		@fclose($this->_socketClient);
	}

	/**
	 * @return void
	 */
	private function _setContext()
	{
		$this->_context = stream_context_create();
		stream_context_set_option($this->_context, 'ssl', 'local_cert', $this->_apnsCert);
		//stream_context_set_option($this->_context, 'ssl', 'passphrase', $this->apnsPassphrase);
	}

	/**
	 * @return bool
	 */
	private function _createStreamSocket()
	{
	    $this->_socketClient = stream_socket_client(
				'ssl://' . $this->_apnsHost . ':' . $this->apnsPort,
				$this->_error,
				$this->_errorString,
				60,
				STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
				$this->_context);

		if(!$this->_socketClient) {
			$this->errorResponse['message'] = 'CHECK SOCKET';
			Yii::info(VarDumper::dumpAsString($this->_error), 'show');
			Yii::info(VarDumper::dumpAsString($this->_errorString), 'show');
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * @return void
	 */
	private function _createErrorMessage()
	{
		if(isset($this->errorResponse['status_code'])) {
			switch($this->errorResponse['status_code']) {
				case 0:
					$this->errorResponse['message'] = '0-No errors encountered';
					break;
				case 1:
					$this->errorResponse['message'] = '1-Processing error';
					break;
				case 2:
					$this->errorResponse['message'] = '2-Missing device token';
					break;
				case 3:
					$this->errorResponse['message'] = '3-Missing topic';
					break;
				case 4:
					$this->errorResponse['message'] = '4-Missing payload';
					break;
				case 5:
					$this->errorResponse['message'] = '5-Invalid token size';
					break;
				case 6:
					$this->errorResponse['message'] = '6-Invalid topic size';
					break;
				case 7:
					$this->errorResponse['message'] = '7-Invalid payload size';
					break;
				case 8:
					$this->errorResponse['message'] = '8-Invalid token';
					break;
				case 255:
					$this->errorResponse['message'] = '255-None (unknown)';
					break;
				default:
					$this->errorResponse['message'] = $this->errorResponse['status_code'] . '-Not listed';
					break;
			}
//			Yii::info($this->errorResponse['message'], 'show');
		}
		else {
			$this->errorResponse['message'] = 'CHECK';
		}
	}
}

