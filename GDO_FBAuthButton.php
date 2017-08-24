<?php
namespace GDO\Facebook;

use GDO\Template\GDO_Template;
use GDO\UI\GDO_Button;
/**
 * Login with Facebook button.
 * @author gizmore
 */
final class GDO_FBAuthButton extends GDO_Button
{
	public function __construct()
	{
		$this->name('btn_facebook');
		$this->href($this->facebookURL());
	}
	
	public function facebookURL()
	{
		$module = Module_Facebook::instance();
		$fb = $module->getFacebook();
		$helper = $fb->getRedirectLoginHelper();
		$permissions = ['email']; // Optional permissions
		$redirectURL = url('Facebook', 'Auth', '&connectFB=1');
		return $helper->getLoginUrl($redirectURL, $permissions);
	}
	
	public function renderCell() { return GDO_Template::php('Facebook', 'cell/fbauthbutton.php', ['field' => $this]); }
}
