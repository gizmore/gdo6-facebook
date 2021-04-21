<?php
namespace GDO\Facebook;

use GDO\Avatar\GDO_UserAvatar;
use GDO\Core\Application;
use GDO\Core\GDO_Module;
use GDO\Form\GDT_Form;
use GDO\DB\GDT_Checkbox;
use GDO\Core\GDT_Secret;
use GDO\User\GDO_User;
use GDO\Net\HTTP;
use GDO\Core\GDT_Success;
use GDO\Core\GDT_Error;
use GDO\UI\GDT_Button;
use GDO\Core\GDT_Array;

/**
 * Facebook SDK Module and Authentication.
 * 
 * @author gizmore
 * @version 6.10.1
 * @since 4.0.0
 * 
 * @see OAuthToken
 * @see GDT_FBAuthButton
 */
final class Module_Facebook extends GDO_Module
{
	public $module_priority = 45;
	
	public function getClasses() { return ['GDO\Facebook\GDO_OAuthToken']; }
	public function onLoadLanguage() { $this->loadLanguage('lang/facebook'); }
	public function thirdPartyFolders() { return ['/php-']; }
	
	##############
	### Config ###
	##############
	public function getConfig()
	{
		return [
			GDT_Checkbox::make('fb_auth')->initial('1'),
			GDT_Secret::make('fb_app_id')->ascii()->caseS()->max(32)->initial('224073134729877'),
			GDT_Secret::make('fb_secret')->ascii()->caseS()->max(64)->initial('f0e9ee41ea8d2dd2f9d5491dc81783e8'),
		];
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
		if (!$fb)
		{
			require_once $this->filePath('php-graph-sdk/src/Facebook/autoload.php');

			$config = [
				'app_id' => $this->cfgAppID(),
				'app_secret' => $this->cfgSecret(),
				'cookie' => true,
			];
			
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
	 * @param GDT_Form $form
	 */
	public function hookLoginForm(GDT_Form $form) { $this->hookRegisterForm($form); }
	public function hookRegisterForm(GDT_Form $form)
	{
	    if ($this->cfgAuth())
	    {
    	    $form->actions()->addField(
    	        GDT_Button::make('link_fb_auth')->secondary()->href(
    	            href('Facebook', 'Auth')));
	    }
	}
	
	public function hookFBUserActivated(GDO_User $user, $fbId)
	{
		if (module_enabled('Avatar'))
		{
			$url = "http://graph.facebook.com/$fbId/picture";
			if ($contents = HTTP::getFromURL($url))
			{
				if (GDO_UserAvatar::createAvatarFromString($user, "FB-Avatar-$fbId.jpg", $contents))
				{
					echo GDT_Success::with('msg_fb_avatar_imported')->render();
					return;
				}
			}
		}
		echo GDT_Error::with('fb_avatar_not_imported')->render();
	}
	
	public function hookIgnoreDocsFiles(GDT_Array $ignore)
	{
	    $ignore->data[] = 'GDO/Facebook/php-graph-sdk/**/*';
	}
	
}
