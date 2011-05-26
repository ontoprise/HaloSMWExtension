<?php
require_once( 'AuthPlugin.php' );

class LDAPUtils {
	static function syncGroups( $user, $winLDAPGroupMembership ) {
		global $wgWinLDAPGroupMapExternal, $wgWinLDAPGroupMapInternal;
		if(!(is_array($winLDAPGroupMembership) &&
		is_array($wgWinLDAPGroupMapExternal) &&
		is_array($wgWinLDAPGroupMapInternal))) {
			return;
		}

		// Remove user from all non auto groups
		$oldGroups = $user->getEffectiveGroups();
		foreach ($oldGroups as $group) {
			if ($group <> "*" or $group <> "user" or $group <> "autoconfirmed") {
				$user->removeGroup($group);
			}
		}

		// Add user to security groups
		foreach ($winLDAPGroupMembership as $userGroup) {
			$i = 0;
			foreach ($wgWinLDAPGroupMapExternal as $externalGroup) {
				if (strtolower($userGroup) == strtolower($externalGroup)) {
					$user->addGroup($wgWinLDAPGroupMapInternal[$i]);
				}
				$i = $i + 1;
			}
		}
	}

	// handles the debug output to a debug file
	static function debugme($input)
	{
		global $wgWinLDAPDebug, $wgWinLDAPDebugLogFile;

		if ($wgWinLDAPDebug) {
			$f = fopen($wgWinLDAPDebugLogFile, "a+");
			fputs($f, "Debug :  " . $input . "\r\n");
			fclose($f);
		}
	}

	static function fetchLDAPdata( $NTLMdomain, $NTLMusername )
	{
		$ldap = array();

		global $wgWinLDAPGCServer, $wgWinLDAPUseTLS;
		global $wgWinLDAPBindUser, $wgWinLDAPBindPassword, $wgWinLDAPForestRoot, $wgWinLDAPGroupNested;
		global $wgWinLDAPGroupMapExternal, $wgWinLDAPGroupMapInternal;

		if(!isset($wgWinLDAPGCServer)) return false;

		LDAPUtils::debugme("Connecting to GC $wgWinLDAPGCServer");

		// Connect to Windows Domain GC (LDAP) Server
		$ldapconn = ldap_connect($wgWinLDAPGCServer, 3268);

		if (isset($ldapconn)) {
			LDAPUtils::debugme("Succesfully connected");

			if (!ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
				LDAPUtils::debugme("Protocol option not set");
			}
			if (!ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0)) {
				LDAPUtils::debugme("Referrals option not set");
			}
			if ($wgWinLDAPUseTLS) {
				if (ldap_start_tls($ldapconn) == false) {
					LDAPUtils::debugme("TLS enabled and could not start");
					return false;
				}
			}

			if (strlen($wgWinLDAPBindUser) & strlen($wgWinLDAPBindPassword)) {
				LDAPUtils::debugme("Binding as $wgWinLDAPBindUser");
				if (ldap_bind($ldapconn, $wgWinLDAPBindUser, $wgWinLDAPBindPassword)) {
					LDAPUtils::debugme("Binding as $wgWinLDAPBindUser suceeded");

					// Search LDAP for user
					$filter = "(&(|(mail=" . $NTLMusername . "*)(anr=" . $NTLMusername . "))(mailnickname=*)(objectCategory=person)(objectClass=user))";
					$search = ldap_search($ldapconn, $wgWinLDAPForestRoot, $filter, array("givenname", "sn", "mail", "memberof"));
					$records = ldap_get_entries($ldapconn, $search);
					LDAPUtils::debugme("Base for filter search [$wgWinLDAPForestRoot]");
					LDAPUtils::debugme("Searching LDAP for user using filter [$filter]");

					// Only allow matching if one LDAP account found.
					if ($records["count"] == 1) {
						LDAPUtils::debugme("One match found for $NTLMusername");
						$i = 0;

						// User prefs
						$ldap['realname'] = $records[$i]["givenname"][0] . " " . $records[$i]["sn"][0];
						$ldap['mail'] = $records[$i]["mail"][0];
						LDAPUtils::debugme("User pref retrieved [" . $ldap['realname'] . "]");
						LDAPUtils::debugme("User pref retrieved [" . $ldap['mail'] . "]");

						// Get group membership
						$ldap['membership'] = $records[$i]["memberof"];
						foreach ($ldap['membership'] as $membershipOfGroup) {
							LDAPUtils::debugme("Group membership [$membershipOfGroup]");
						}

						$securityFlag = true;

						if(isset($wgWinLDAPGroupMapExternal)) {
							// Expand nested groups
							if ($wgWinLDAPGroupNested) {
								LDAPUtils::debugme("Expanding groups looking for nested groups");
								$j = 0;
								foreach ($wgWinLDAPGroupMapExternal as $externalGroup) {
									LDAPUtils::getGroupMembers($externalGroup, $wgWinLDAPGroupMapInternal[$j], $ldapconn);
									$j ++;
								}
							}

							// Check user is in securty group
							$securityFlag = false;
							$j = 0;
							foreach ($wgWinLDAPGroupMapExternal as $securityGroup) {
								foreach ($ldap['membership'] as $userGroup) {
									if ($userGroup == $securityGroup) {
										$securityFlag = true;
										LDAPUtils::debugme("Security group check passed added to group [" . $wgWinLDAPGroupMapInternal[$j] . "]");
									}
								}
								$j ++;
							}
						}

						// Don't login if not in group
						if ($securityFlag) {
							return $ldap;
						} else {
							LDAPUtils::debugme("Security group check failed");
						}
					} else {
						LDAPUtils::debugme("More than one match found for $NTLMusername");
					}
				} else {
					LDAPUtils::debugme("Binding as $wgWinLDAPBindUser failed");
				}
			} else {
				LDAPUtils::debugme("Anonymous connections are not allowed");
			}
		} else {
			LDAPUtils::debugme("Connection to GC failed");
		}
		return false;
	}

	static function getGroupMembers($group, $groupmap, $ldapconn) {
		if (strtolower(substr($group, 0, 2)) == "cn") {
			$filter = str_replace("cn=", "", substr($group, 0, strpos($group, ","))) ;
			$base = substr(substr($group, strpos($group, ",")), 1);
			$search = ldap_search($ldapconn, $base, $filter, array("dn", "cn", "member", "objectclass"));
			$records = ldap_get_entries($ldapconn, $search);
			if ($records["count"] == 1) {

				$DNclass = $records[0]["objectclass"];
				foreach ($DNclass as $groupClass) {
					if (strtolower($groupClass) == "group") {

						// Add group mapping
						LDAPUtils::insertGroupArray($group, $groupmap);
						if (isset($records[0]["member"])) {
							$groupMembers = $records[0]["member"];
							foreach ($groupMembers as $groupMember) {
								LDAPUtils::getGroupMembers($groupMember, $groupmap, $ldapconn);
							}
						}
					}
				}
			}
		}
	}

	static function insertGroupArray($group, $groupmap)
	{
		global $wgWinLDAPGroupMapExternal, $wgWinLDAPGroupMapInternal;

		$exists = false;

		foreach ($wgWinLDAPGroupMapExternal as $externalGroup) {
			if ($group == $externalGroup) {
				$exists = true;
			}
		}

		if (!$exists) {

			// Group does not exist insert into $wgWinLDAPGroupMapExternal
			LDAPUtils::debugme("Found nested group adding to mappings [$group] [$groupmap]");
			array_push($wgWinLDAPGroupMapExternal, $group);
			array_push($wgWinLDAPGroupMapInternal, $groupmap);
		}
	}
}

class DomainAuthenticationPlugin extends AuthPlugin {
	var $m_ldap;

	function syncGroups( $user ) {
		if( !isset($this->m_ldap) ) {
			global $wgDomainAuthDomain, $wgDomainAuthUser;
			$this->m_ldap = LDAPUtils::fetchLDAPdata( $wgDomainAuthDomain, $wgDomainAuthUser );
		}
		if( $this->m_ldap ) LDAPUtils::syncGroups( $user, $this->m_ldap['membership'] );
	}
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

	function modifyUITemplate(&$template) {
		$template->set('create', false);
		$template->set('usedomain', false);
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

		if( !isset($this->m_ldap) ) {
			global $wgDomainAuthDomain, $wgDomainAuthUser;
			$this->m_ldap = LDAPUtils::fetchLDAPdata( $wgDomainAuthDomain, $wgDomainAuthUser );
		}

		if( $this->m_ldap ) {
			if(isset($this->m_ldap['mail'])) {
				$user->setEmail( $this->m_ldap['mail'] );
			} else {
				$user->setEmail( $user->mName . $wgDomainAuthEmail );
			}
			if(isset($this->m_ldap['realname'])) {
				$user->setRealName($this->m_ldap['realname']);
			}
		} else {
			$user->setEmail( $user->mName . $wgDomainAuthEmail );
		}

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

	/* No logout link in MW */
	static function NoLogout( &$personal_urls, $title ) {
		global $wgDomainAutoLogin;
		if($wgDomainAutoLogin) {
			$personal_urls['logout'] = null;
		}
		return true;
	}

	static function DomainAuthenAutoLogin( $user ) {
		global $wgDomainAutoLogin, $wgDomainAuthDomain, $wgDomainAuthUser;
		if($wgDomainAutoLogin) {
			// add session
			if( session_id() == '' ) {
				wfSetupSession();
			}
			// login with OS account
			$nt = Title::newFromText( $wgDomainAuthUser );
			if( is_null( $nt ) ) {
				# Illegal name
				return true;
			}
			$user->mName = $nt->getText();
			$user->mId = User::idFromName( $user->mName );
			global $wgAuth;
			if ( !$user->mId ) {
				// create account
				$user->addToDatabase();
				$user->setToken();
				$wgAuth->initUser( $user, true );
				$user->saveSettings();
				# Update user count
				$ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
				$ssUpdate->doUpdate();
				wfRunHooks( 'AuthPluginAutoCreate', array( $user ) );
			} else {
				$user->loadFromId();
			}
			$wgAuth->syncGroups( $user );
			// log in
			$user->invalidateCache();
			$user->setCookies();
			$injected_html = '';
			wfRunHooks('UserLoginComplete', array(&$user, &$injected_html));
		}
		return true;
	}

	static function NoSpecialLoginOut()
	{
		global $wgRequest;
		$title = $wgRequest->getVal('title');
		if (($title == "Special:UserLogout" || ($title == "Special:UserLogin"))) {
			global $wgScript;
			header("Location: $wgScript");
			return;
		}
		return true;
	}
}

/**
 * Add extension information to Special:Version
 */
$wgExtensionCredits['other'][] = array(
	'name' => 'Internal Wiki Authentication Plugin',
	'version' => '1.0',
	'author' => 'Ning Hu',
	'description' => 'Domain Authentication plugin',
	'url' => 'http://wiking.vulcan.com/ngt',
);

function DomainAuthSetup() {
	global $wgHooks, $wgAuth, $wgExtensionFunctions, $wgVersion;
	$wgExtensionFunctions[] = array( 'DomainAuthenticationPlugin', 'NoSpecialLoginOut' );

	global $wgDomainAuthDomain, $wgDomainAuthUser;
	list($wgDomainAuthDomain, $wgDomainAuthUser) = split('\\\\', $_SERVER["REMOTE_USER"], 2);

	$wgAuth = new DomainAuthenticationPlugin();

	if ( version_compare( $wgVersion, '1.14.0', '<' ) ) {
		$wgHooks['UserLoadFromSession'][] = 'DomainAuthenticationPlugin::DomainAuthenAutoLogin';
	} else {
		$wgHooks['UserLoadAfterLoadFromSession'][] = 'DomainAuthenticationPlugin::DomainAuthenAutoLogin';
	}
	$wgHooks['PersonalUrls'][] = 'DomainAuthenticationPlugin::NoLogout'; /* Disallow logout link */
}

global $wgAuthDomains, $wgDomainAuthEmail, $wgDomainAutoLogin;

$wgDomainAutoLogin = true;
$wgDomainAuthEmail = '@';
$wgAuthDomains = array();