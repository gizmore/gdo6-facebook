<?php
namespace GDO\Facebook;

use GDO\DB\GDO;
use GDO\Net\GDO_IP;
use GDO\Type\GDO_Char;
use GDO\Type\GDO_String;
use GDO\Type\GDO_Text;
use GDO\User\GDO_User;
use GDO\User\User;
/**
 * Mapping of ProviderID to userid.
 * Mapping is only possible via username field.
 * Realname is used for users realname.
 * 
 * @author gizmore
 * @since 4.0
 * @version 5.0
 *
 */
final class OAuthToken extends GDO
{
	public function gdoColumns()
	{
		return array(
			GDO_Char::make('oauth_provider')->ascii()->caseS()->size(2)->primary(),
			GDO_String::make('oauth_id')->ascii()->caseS()->max(32)->primary(),
			GDO_User::make('oauth_user')->notNull(),
			GDO_Text::make('oauth_token')->utf8()->caseS()->max(4096),
		);
	}
	
	/**
	 * @return User
	 */
	public function getUser() { return $this->getValue('oauth_user'); }
	public function getUserID() { return $this->getVar('oauth_user'); }
	public function getToken() { return $this->getVar('oauth_token'); }
	
	/**
	 * Refresh login tokens and user association.
	 * @param string $token
	 * @param array $fbVars
	 * @return User
	 */
	public static function refresh($token, array $fbVars, $provider='FB')
	{
		# Provider data
		$id = $fbVars['id'];
		$email = $fbVars['email'];
		$displayName = $fbVars['name'];
		
		$name = "-$provider-$id"; # Build ProviderUsername
		if (!($user = User::getByName($name))) # And get by name
		{
			# Not found => Create with fb data 
			$user = User::blank(array(
				'user_type' => User::MEMBER,
				'user_email' => $email,
				'user_name' => $name,
				'user_real_name' => $displayName,
				'user_password' => $provider,
				'user_register_ip' => GDO_IP::current(),
			))->insert();
			$user->tempSet('justActivated', true);
		}
		
		# Update mapping
		self::blank(array(
			'oauth_id' => $id,
			'oauth_provider' => $provider,
			'oauth_user' => $user->getID(),
			'oauth_token' => $token,
		))->replace();
		
		return $user;
	}
}