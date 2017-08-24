<?php
namespace GDO\Facebook\Method;

use GDO\Core\GDO_Hook;
use GDO\Facebook\GDO_FBAuthButton;
use GDO\Facebook\Module_Facebook;
use GDO\Facebook\OAuthToken;
use GDO\Form\GDO_Form;
use GDO\Form\MethodForm;
use GDO\Login\Method\Form;
use GDO\User\User;
/**
 * Facebook OAuth connector.
 * @author gizmore
 * @since 4.0
 * @version 5.0
 */
final class Auth extends MethodForm
{
    public function isUserRequired() { return false; }
    
    public function getUserType() { return 'ghost'; }
	
	public function execute()
	{
		if (isset($_GET['connectFB']))
		{
			return $this->onConnectFB();
		}
		return parent::execute();
	}
	
	public function createForm(GDO_Form $form)
	{
		$form->addFields(array(
			GDO_FBAuthButton::make(),
		));
	}
	
	private function onConnectFB()
	{
		$fb = Module_Facebook::instance()->getFacebook();
		$helper = $fb->getRedirectLoginHelper();
		$accessToken = $helper->getAccessToken();
		if ($accessToken)
		{
		    $this->gotAccessToken($accessToken);
			return $this->message('msg_facebook_connected')->add($response);
		}
		return $this->error('err_facebook_connect');
	}
	
	public function gotAccessToken($accessToken)
	{
	    $fb = Module_Facebook::instance()->getFacebook();
	    $response = $fb->get('/me?fields=id,name,email', $accessToken);
	    $user = OAuthToken::refresh($accessToken->getValue(), $response->getGraphUser()->asArray());
	    
	    $activated = $user->tempGet('justActivated');
	    
	    # Temp is cleared here
	    $response = $this->authenticate(method('Login', 'Form'), $user);
	    
	    # Temp was in activation state?
	    if ($activated)
	    {
	        GDO_Hook::call('UserActivated', $user);
	        GDO_Hook::call('FBUserActivated', $user, substr($user->getVar('user_name'), 4));
	    }
	    
	    
	}
	
	private function authenticate(Form $method, User $user)
	{
		return $method->loginSuccess($user);
	}
}
