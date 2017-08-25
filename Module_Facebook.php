<?php
namespace GDO\Facebook;

use GDO\Avatar\UserAvatar;
use GDO\Core\Application;
use GDO\Core\Module;
use GDO\Form\GDO_Form;
use GDO\Template\Error;
use GDO\Template\Message;
use GDO\Type\GDO_Checkbox;
use GDO\Type\GDO_Secret;
use GDO\UI\GDO_Link;
use GDO\User\User;
use GDO\Util\HTTP;
/**
 * Facebook SDK Module and Authentication.
 * 
 * @author gizmore
 * @since 4.0
 * @version 5.0
 * 
 * @see OAuthToken
 * @see GDO_FBAuthButton
 */
final class Module_Facebook extends Module
{
	public $module_priority = 45;
	
	public function getClasses() { return ['GDO\Facebook\OAuthToken']; }
	public function onLoadLanguage() { $this->loadLanguage('lang/facebook'); }

	##############
	### Config ###
	##############
	public function getConfig()
	{
		return array(
			GDO_Checkbox::make('fb_auth')->initial('1'),
			GDO_Secret::make('fb_app_id')->ascii()->caseS()->max(32)->initial('224073134729877'),
			GDO_Secret::make('fb_secret')->ascii()->caseS()->max(64)->initial('f0e9ee41ea8d2dd2f9d5491dc81783e8'),
		);
	}
	public function cfgAuth() { return $this->getConfigValue('fb_auth'); }
	public function cfgAppID() { return $this->getConfigValue('fb_app_id'); }
	public function cfgSecret() { return $this->getConfigValue('fb_secret'); }
	
	############
	### Util ###
	############
	/**
	 * @return \Facebook\Facebook
	 */
	public function getFacebook()
	{
	    static $fb;
	    if (!isset($fb))
	    {
    		require_once $this->filePath('php-graph-sdk/src/Facebook/autoload.php');

	        $config = array(
	            'app_id' => $this->cfgAppID(),
	            'app_secret' => $this->cfgSecret(),
	            'cookie' => true,
	        );
    		
    		if (!Application::instance()->isCLI())
    		{
    			# lib requires normal php sessions.
    			if (!session_id()) { session_start(); }
    			$config['persistent_data_handler'] = 'session';
    		}
    		else
    		{
    		    $config['persistent_data_handler'] = 'memory';
    		}
    		
    		$fb = new \Facebook\Facebook($config);
	    }
	    return $fb;
	}
	
	#############
	### Hooks ###
	#############
	/**
	 * Hook into register and login form creation and add a link.
	 * @param GDO_Form $form
	 */
	public function hookLoginForm(GDO_Form $form) { $this->hookRegisterForm($form); }
	public function hookRegisterForm(GDO_Form $form)
	{
		$form->addField(GDO_Link::make('link_fb_auth')->href(href('Facebook', 'Auth')));
	}
	
	public function hookFBUserActivated(User $user, string $fbId)
	{
		if ($avatar = Application::instance()->getActiveModule('Avatar'))
		{
			$url = "http://graph.facebook.com/$fbId/picture";
			if ($contents = HTTP::getFromURL($url))
			{
				if (UserAvatar::createAvatarFromString($user, "FB-Avatar-$fbId.jpg", $contents))
				{
					echo Message::message('msg_fb_avatar_imported')->render();
					return;
				}
			}
		}
		echo Error::error('err_fb_avatar_not_imported')->render();
	}
}
