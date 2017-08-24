<?php
namespace GDO\Facebook\Websocket;

use GDO\Facebook\Module_Facebook;
use GDO\Facebook\Method\Auth;
use GDO\User\Session;
use GDO\User\User;
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
        
        $fb = Module_Facebook::instance()->getFacebook();
        $fb->setDefaultAccessToken($fbAccessToken);
        $helper = $fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();
        $this->onAccess($accessToken, method('Facebook', 'Auth'));
    }
    
    public function onAccess($accessToken, Auth $method)
    {
        $method->gotAccessToken($accessToken);
        
        User::$CURRENT = $user = Session::instance()->getUser();
        Session::reset();
        $msg->replyBinary($msg->cmd(), $this->userToBinary($user));
    }
}

GWS_Commands::register(0x0111, new GWS_Facebook());
