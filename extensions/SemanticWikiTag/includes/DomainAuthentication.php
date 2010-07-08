<?php
require_once( 'AuthPlugin.php' );

class DomainAuthenticationPlugin extends AuthPlugin {
	/**
	 * Check whether there exists a user account with the given name.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param $username String: username.
	 * @return bool
	 * @public
	 */
	function userExists( $username ) {
		return true;
	}

	/**
	 * Check if a username+password pair is a valid login.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param $username String: username.
	 * @param $password String: user password.
	 * @return bool
	 * @public
	 */
	function authenticate( $username, $password ) {
		return true;
	}

	/**
	 * Return true if the wiki should create a new local account automatically
	 * when asked to login a user who doesn't exist locally but does in the
	 * external auth database.
	 *
	 * If you don't automatically create accounts, you must still create
	 * accounts in some way. It's not possible to authenticate without
	 * a local account.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return bool
	 * @public
	 */
	function autoCreate() {
		return true;
	}

	/**
	 * Return true to prevent logins that don't authenticate here from being
	 * checked against the local database's password fields.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return bool
	 * @public
	 */
	function strict() {
		return true;
	}

	/**
	 * When creating a user account, optionally fill in preferences and such.
	 * For instance, you might pull the email address or real name from the
	 * external user database.
	 *
	 * The User object is passed by reference so it can be modified; don't
	 * forget the & on your function declaration.
	 *
	 * @param $user User object.
	 * @param $autocreate bool True if user is being autocreated on login
	 * @public
	 */
	function initUser( &$user, $autocreate=false ) {
		global $wgDomainAuthEmail;
		$user->setPassword( null );
		$user->setEmail( $user->mName.$wgDomainAuthEmail );
		$user->saveSettings();

		// copy from includes/specials/SpecialUserlogin.php, LoginForm::mailPasswordInternal
		global $wgServer, $wgScript;

		$np = $user->randomPassword();
		$user->setNewpassword( $np, false );
		$user->saveSettings();

		$ip = wfGetIP();
		if ( '' == $ip ) { $ip = '(Unknown)'; }

		$m = wfMsg( 'createaccount-text', $ip, $user->getName(), $np, $wgServer . $wgScript );
		$result = $user->sendMail( wfMsg( 'createaccount-title' ), $m );
	}
}

/**
 * Add extension information to Special:Version
 */
$wgExtensionCredits['other'][] = array(
	'name' => 'Vulcan internal Wiki Authentication Plugin',
	'version' => '1.0',
	'author' => 'Ning Hu',
	'description' => 'Domain Authentication plugin',
	'url' => 'http://wiking.vulcan.com/ngt',
);

/**
 * Sets up the SSL authentication piece of the LDAP plugin.
 *
 * @access public
 */
function DomainAuthSetup() {
	global $wgHooks;
	global $wgAuth;

	$wgAuth = new DomainAuthenticationPlugin();
	$wgHooks['PersonalUrls'][] = 'NoLogout'; /* Disallow logout link */
}

/* No logout link in MW */
function NoLogout( &$personal_urls, $title ) {
	$personal_urls['logout'] = null;
	return true;
}

global $wgAuthDomains, $wgDomainAuthEmail, $wgDomainAutoLogin;
$wgDomainAutoLogin = true;
$wgDomainAuthEmail = '@vulcan.com';
$wgAuthDomains = array( 'vulcan' );