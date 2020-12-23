<?php
namespace GDO\Facebook;

use GDO\Core\GDT_Template;
use GDO\UI\GDT_Button;
/**
 * Login with Facebook button.
 * @author gizmore
 */
final class GDT_FBAuthButton extends GDT_Button
{
	protected function __construct()
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
	
	public function renderCell() { return GDT_Template::php('Facebook', 'cell/fbauthbutton.php', ['field' => $this]); }
}
