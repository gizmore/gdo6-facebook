<?php
namespace GDO\Facebook\Websocket;

use GDO\Facebook\Module_Facebook;
use GDO\Facebook\Method\Auth;
use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;

final class GWS_Facebook extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$fbUID = $msg->readString();
		$fbExpire = time() + $msg->read32u();
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

// 		GDO_User::$CURRENT = $user = GDO_Session::instance()->getUser();
// 		GDO_Session::reset();

		$user = GDO_User::current();
		$msg->replyBinary($msg->cmd(), $this->userToBinary($user));
	}
}

GWS_Commands::register(0x0111, new GWS_Facebook());
