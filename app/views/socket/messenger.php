<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 30.05.16
 */

use yii\helpers\Html;
use \yii\widgets\ListView;
use \yii\bootstrap\ActiveForm;
use \app\models\main\Messages;

/* @var yii\web\View $this */
/* @var app\models\main\Chats $chat */
/* @var app\models\main\Users $user */
/* @var app\models\main\Messages $message */
/* @var app\models\main\Messages[] $messages */

$this->title = 'Chat';
?>
<script src="/js/socket.io-1.3.5.js"></script>
<div class="site-index">

</div>
<script type="text/javascript">
	$(document).ready(function() {

		var HandlerChat = {
			socket: null,
			host: '<?php echo DOMAIN; ?>',
			port: '8890',
			modeAjax: 'ajax',
			modeWebSocket: 'socket',
			mode: this.modeAjax,
			eventSocket: false,
			lastId: null,
			chatHash: 'pW4G6pFKC5RTkj7ovKVN60yTNfVcNW-8bE2z6wwvOOeIkRCPXZtyx2o_4xoGxQ1N',
			init: function() {
				this.socket = io.connect( 'https://' + this.host + ':' + this.port );
				this.lastId = $('#lastId-field').val();
				this.checkMode();

				console.log(this.socket);
			},
			submitInfo: function() {
				var _this = this;
				var form = $('#chat-form');
				var messageField = $( '#message-field' );
				var data = form.serialize();
				messageField.val('');
				_this.ajaxRequest(form.attr('action'), data);
			},
			ajaxRequest: function(url, data) {
				var _this = this;
				// Ajax call for saving datas
				$.ajax({
					url: url,
					type: "POST",
					dataType: 'json',
					data: data,
					success: function(json) {
						if(_this.mode == _this.modeAjax) {
							var data = $.parseJSON(json);
							data.forEach(function(item, i, arr) {
								_this.addMessage(item)
							});
						}
					}
				});
			},
			checkMode: function () {
				var _this = this;

				var connected = _this.socket.connected == true ? _this.modeWebSocket : _this.modeAjax;

				if(connected != _this.mode || _this.mode == 'undefined') {
					_this.mode = connected;
					$('#mode-field').val(_this.mode);
					if(_this.mode == _this.modeAjax) {
						_this.getNewMessages();
					}
				}

				_this.messenger();
			},
			messenger: function() {
				var _this = this;
				if(_this.mode == _this.modeAjax) {
					_this.getNewMessages();
				}
				else {
					if(_this.eventSocket == false) {
						_this.socket.on( _this.chatHash, function(data) {
							_this.eventSocket = true;
//							_this.addMessage(data);
							console.log(data);
						});
					}
				}
			},
			getNewMessages: function () {
//				this.ajaxRequest(
//						$('#chat-form').attr('action'),
//						{Messages: {mode: '<?php //echo Messages::MODE_AJAX ?>//', notSave: true, lastId: this.lastId}})
//				;
			},
			addMessage: function (data) {
				var _this = this;

//				if(_this.lastId < data.lastId) {
//					_this.lastId = data.lastId;
//					$('#lastId-field').val(_this.lastId);
//
//					var string = '<div><span style="font-size: 10px;">[' + HandlerChat.timenow() + ']</span><strong> ';
//					if(<?php //echo $user->id?>// == data.userId) {
//						string += '<span style="color: green;">' + data.name + '</span>';
//					}
//					else {
//						string += '<span style="color: blue;">' + data.name + '</span>';
//					}
//
//					string += '</strong>: ' + data.message + '</div>';
//
//					$( "#notifications" ).append( string );
//				}
			},
			timenow: function () {
				var now = new Date();
				var ampm = 'am';
				var year = now.getFullYear();
				var month = this.prependZero(now.getMonth() + 1);
				var date = this.prependZero(now.getDate());
				var hours = now.getHours();
				var minutes = this.prependZero(now.getMinutes());

				if(hours >= 12) {
					if(hours > 12) {
						hours -= 12;
					}
					ampm = 'pm';
				}

				hours = this.prependZero(hours);

				return date + '/' + month + '/' + year + ' ' + hours + ':' + minutes + ' ' + ampm;
			},
			prependZero: function(number) {
				if(number < 10) {
					number = '0' + number;
				}

				return number;
			}

		};

		HandlerChat.init();
		setInterval(function() {HandlerChat.checkMode();}, 10000);

		$('#chat-form').submit( function(event) {
			event.preventDefault();
			HandlerChat.submitInfo();
		});

		document.onkeyup = function (event) {
			event = event || window.event;
			if (event.keyCode === 13) {
				HandlerChat.submitInfo();
			}
			return false;
		};
	});
</script>