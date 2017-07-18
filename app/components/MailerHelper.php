<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 13.04.16
 *
*/
namespace app\components;

use Yii;
use yii\base\Component;
use yii\db\Exception;

/**
 * Class Mail
 * @package app\components
 *
 * @property string $fromMail
 * @property string $toMail
 * @property string $fromName
 * @property string $subject
 * @property string $body
 * @property string $type
 * @property array $data
 * @property string $typeLetter
 * @property string $templatePath
 * @property string $_viewPath
 */
class MailerHelper extends Component
{
	const TYPE_REPORT_IMAGE 	    = 'report-image';
	const TYPE_FORM_ECHO 	        = 'form-echo';

	public $fromMail;
	public $toMail;
	public $fromName;
	public $subject;
	public $body;
	public $type = 'text/html';
	public $data;
	public $typeLetter;
	public $templatePath;

	private $_viewPath;

	public static $defaultSubject = [
			self::TYPE_REPORT_IMAGE 	=> 'Report Image',
			self::TYPE_FORM_ECHO 	    => 'Your answers from CleanLivingClub',
	];

	public static $defaultTemplates = [
			self::TYPE_REPORT_IMAGE 	=> 'reportImage.php',
			self::TYPE_FORM_ECHO 	    => 'echoForm.php',
	];

    /**
     * @param $fromMail
     * @param $fromName
     * @param $toMail
     * @param $data
     * @param $typeLetter
     * @return $this
     */
    public function compose($fromMail, $fromName, $toMail, $data, $typeLetter)
	{
		$this->fromMail = $fromMail;
		$this->fromName = $fromName;
		$this->toMail = $toMail;
		$this->data = $data;
		$this->typeLetter = $typeLetter;
		$this->subject = self::$defaultSubject[$typeLetter];

		$this->_setViewPath();
//		$this->_setSubject();
		$this->_setBody();
		$this->_setType();

		return $this;
	}

	/**
	 * Sends an email to the specified email address using the information collected by this model
	 * @return bool
	 */
	public function send()
	{
		$mail = Yii::$app->mailer->compose()
				->setTo($this->toMail)
				->setFrom([$this->fromMail => $this->fromName])
				->setSubject($this->subject);

		if($this->type == 'text/html') {
			$mail->setHtmlBody($this->body);
		}
		else {
			$mail->setTextBody($this->body);
		}

		try {
			return $mail->send();
		} catch (\Swift_TransportException $e) {
			return $e;
		}


	}

	/**
	 *  @return null|string
	 */
	private function _getSubject()
	{
		return isset(self::$defaultSubject[$this->typeLetter]) ? self::$defaultSubject[$this->typeLetter] : null;
	}

	/**
	 * @return null|string
	 */
	private function _getTemplates()
	{
		return isset(self::$defaultTemplates[$this->typeLetter]) ? self::$defaultTemplates[$this->typeLetter] : null;
	}

	/**
	 * @return void
	 */
	private function _setViewPath()
	{
		$template = isset($this->data['template']) ? $this->data['template'] : $this->_getTemplates();
		$this->_viewPath = $this->templatePath . $template;
	}

	/**
	 * @return void
	 */
	private function _setSubject()
	{
		$this->subject = isset($this->data['subject']) ? $this->data['subject'] : $this->_getSubject();
	}

	/**
	 * @return void
	 */
	private function _setType()
	{
		if(isset($this->data['type'])) {
			$this->type = $this->data['type'];
		}
	}

	/**
	 * @return void
	 */
	private function _setBody()
	{
		$this->body = $this->_renderViewFile($this->_viewPath, $this->data, true);
	}

	/**
	 * Renders a view file.
	 * This method includes the view file as a PHP script
	 * and captures the display result if required.
	 * @param string $_viewFile_ view file
	 * @param array $_data_ data to be extracted and made available to the view file
	 * @param boolean $_return_ whether the rendering result should be returned as a string
	 * @return string the rendering result. Null if the rendering result is not required.
	 */
	private function _renderViewFile($_viewFile_, $_data_ = null, $_return_ = false)
	{
		// we use special variable names here to avoid conflict when extracting data
		if (is_array($_data_)) {
			extract($_data_, EXTR_PREFIX_SAME, 'data');
		}
		else {
			$data = $_data_;
		}
		$data = $_data_;
		$subject = $this->subject;

		if ($_return_) {
			ob_start();
			ob_implicit_flush(false);
			require $_viewFile_;

			return ob_get_clean();
		}
		else {
			require $_viewFile_;
		}
	}


}