<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 20.11.15
 */
namespace app\components;

use Yii;
use yii\base\Component;

class Errors extends Component
{
	/**
	 * CODE GROUPS
	 * 1XXXXX - ChatController
	 * 2XXXXX - FriendshipController
	 * 3XXXXX - MessagesController
	 * 4XXXXX - TimeSyncController
	 * 5XXXXX - UsersController
	 */



	private $_errors = [
		100000 => 'You should to add participants to chat',
		100001 => 'Chat successfully deleted',
		100002 => 'Error when deleting chat',
		100003 => 'Deleted successfully',
		100004 => 'Request not found',
		100005 => 'Chat not found',
		100006 => 'Chat participants not found',
		100007 => 'Participants info not found',

		200000 => 'Relation already exist',
		200001 => 'Request not found',
		200002 => 'Request accepted',
		200003 => 'Relationship not found',
		200004 => 'Friend deleted successfully',

		300000 => 'Message not found',

		400000 => 'A TimeSync for your group is already in progress',
		400001 => 'You can not create Timesync in chat with one person',
		400002 => 'Chat not found',
		400003 => 'Unable to upload TimeSync photo',
		400004 => 'Timesync not found',
		400005 => 'Timesync is full',
		400006 => 'This TimeSync has already Started',

		500000 => 'User not found',
		500001 => 'Token saved',
		500002 => 'Auth limits exceeded, wait 15 minutes',
		500003 => 'You need to register before login',
		500004 => 'Authorization required',
	];

	public function get($code)
	{
		return (empty($this->_errors[$code])) ? '' : $this->_errors[$code];
	}
}