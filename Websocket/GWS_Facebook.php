<?php
namespace GDO\Facebook\Websocket;

use GDO\Core\Application;
use GDO\Core\Module_Core;
use GDO\Facebook\Module_Facebook;
use GDO\Facebook\Method\Auth;
use GDO\User\GDO_User;
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;
use GDO\Session\GDO_Session;

final class GWS_Facebook extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$fbUID = $msg->readString();
		$fbExpire = Application::$TIME + $msg->read32u();
		$fbAccessToken = $msg->readString();
		$fbCookie = $msg->readString();
		$_COOKIE['fbsr_'.Module_Facebook::instance()->cfgAppID()] = $fbCookie;
		
		$fb = Module_Facebook::instance()->getFacebook();
		$fb->setDefaultAccessToken($fbAccessToken);
		$helper = $fb->getJavaScriptHelper();
		
		$accessToken = $helper->getAccessToken();
		
		$this->onAccess($msg, $accessToken, method('Facebook', 'Auth'));
	}
	
	public function onAccess(GWS_Message $msg, $accessToken, Auth $method)
	{
		$method->gotAccessToken($accessToken);

		$user = GDO_User::current();
		$user->tempSet('sess_id', GDO_Session::instance()->getID());
		$msg->conn()->setUser($user);
		
		$msg->replyText($msg->cmd(), json_encode(Module_Core::instance()->gdoUserJSON()));
	}
}

GWS_Commands::register(0x0111, new GWS_Facebook());
