<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup HaloACL_Maintenance
 *
 * Maintenance script checking and finding the correct LDAP configuration.
 * 
 * @author Thomas Schweitzer
 * Date: 13.2.2012
 * 
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$haclgIP = "$dir/../../HaloACL";

require_once("$haclgIP/includes/HACL_Storage.php");
require_once("$haclgIP/includes/HACL_GlobalFunctions.php");
require_once("$haclgIP/storage/HACL_StorageLDAP.php");

if (!class_exists('LdapAuthenticationPlugin', false )) {
	printFailure("The class 'LdapAuthenticationPlugin' does not exist.");
	printSolution(
		"Add the following line to your LocalSettings.php:\n".
		'require_once( "$IP/extensions/LdapAuthentication/LdapAuthentication.php" );'."\n");
	die();
}

class CheckLdapAuthenticationPlugin extends LdapAuthenticationPlugin {
	/**
	 * Prints debugging information. $debugText is what you want to print, $debugVal
	 * is the level at which you want to print the information.
	 *
	 * @param string $debugText
	 * @param string $debugVal
	 * @access private
	 */
	function printDebug( $debugText, $debugVal, $debugArr = null ) {
		global $wgLDAPDebug;

		if ( isset( $debugArr ) ) {
			$text = $debugText . " " . implode( "::", $debugArr );
			printDebug($text);
		} else {
			printDebug($debugText);
		}
	}

}

printInfo(
	str_repeat('*', 80)."\n".
	"This script checks your LDAP settings for connecting HaloACL with an\n".
	"LDAP directory.\n\n".
	"This is a step-by-step process. If a step fails, the script stops and gives\n".
	"you hints of how to fix that issue.\n".
	"Accessing an LDAP directory is based on the MediaWiki extension LDAP Authentication.\n".
	"Please visit\n".
	"http://www.mediawiki.org/wiki/Extension:LDAP_Authentication\n".
	"for more information.\n".
	str_repeat('*', 80)."\n"
	, 0);

$configurationValid =
	checkStore() &&
	checkAuthenticationPlugin() &&
	checkLDAPInstalled() &&
	checkLDAPDomainNames() &&
	checkEachLDAPDomain();

if ($configurationValid) {
	printInfo(
		"\n++ Congratulation! Your configurations for all registered LDAP domains are correct.\n".
		"There are several other settings that may be of interest.\n".
		"Examples:\n".
		"\$wgLDAPSearchStrings = array(\"domainname\" => \"cn=USER-NAME,ou=people,dc=ontoprise,dc=home\")\n".
		"\$wgLDAPGroupUseFullDN = array(\"domainname\" => true  );\n".
		"\$wgLDAPLowerCaseUsername = array(\"domainname\" => true);\n".
		"\$wgLDAPGroupSearchNestedGroups = array(\"domainname\" => true);\n".
		"\$wgLDAPUseLDAPGroups = array(\"domainname\" => true);\n".
		"\$wgLDAPGroupsUseMemberOf = array(\"domainname\" => true);\n".
		"See for more information:\n".
		"  http://www.mediawiki.org/wiki/Extension:LDAP_Authentication/Configuration\n".
		"  http://ryandlane.com/blog/2009/03/23/using-the-ldap-authentication-plugin-for-mediawiki-the-basics-part-1\n"
		
		);
}

/**
 * 
 * Check the store. It must be 'HACL_STORE_LDAP'
 * 
 **/
function checkStore() {
	global $haclgBaseStore;
	printTestTitle("Checking store.");
	printInfo("The current store is: $haclgBaseStore");
	if ($haclgBaseStore === HACL_STORE_LDAP) {
		printSuccess();
		return true;
	} else {
		printFailure("Expected HACL_STORE_LDAP.");
		printSolution("Please initialize HaloACL like this in LocalSettings.php:\n".
		              "include_once('extensions/HaloACL/includes/HACL_Initialize.php');\n".
		              "\$haclgBaseStore = HACL_STORE_LDAP;\n".
		              "enableHaloACL();\n");
	}
	return false;
}

/**
*
* Checks if the PHP support for LDAP is enabled.
*/
function checkLDAPInstalled() {
	printTestTitle("Checking if LDAP is enabled for this PHP installation.");
	if (function_exists('ldap_connect')) {
		printSuccess();
		return true;
	}
	printFailure("No LDAP support found.");
	printSolution(
		"Please edit your php.ini and enable LDAP.\n".
		"Example:\n".
		"extension=php_ldap.dll\n");
	return false;
}


/**
 * 
 * Check if the extension LdapAuthentication is present and initialized.
 */
function checkAuthenticationPlugin() {
	global $wgAuth;
	printTestTitle("Checking if LdapAuthentication is installed.");
	if ($wgAuth && $wgAuth instanceof LdapAuthenticationPlugin) {
		printSuccess();
		return true;
	} else {
		printFailure(
			"Expected variable \$wgAuth to be an instance of LdapAuthenticationPlugin.");
		printSolution(
			"Please initialize LdapAuthentication like this in LocalSettings.php:\n".
			"require_once( \"\$IP/extensions/LdapAuthentication/LdapAuthentication.php\" );\n".
			"\$wgAuth = new LdapAuthenticationPlugin();\n");
	}
	return false;
	
}

/**
 * 
 * Check if the LDAP domains are defined.
 */
function checkLDAPDomainNames() {
	global $wgLDAPDomainNames;
	printTestTitle("Checking if LDAP domain names are defined.");
	if ($wgLDAPDomainNames && is_array($wgLDAPDomainNames) && count($wgLDAPDomainNames) > 0) {
		printSuccess();
		return true;
	} else {
		printFailure(
			"Expected variable \$wgLDAPDomainNames to be an array with at least one domain name.");
		printSolution(
			"Example:\n".
			"\$wgLDAPDomainNames = array( \"TestLDAP\", \"TestAD\" );\n");
	}
	return false;
	
}

/**
 * 
 * Check if each LDAP domain is defined and accessible.
 */
function checkEachLDAPDomain() {
	global $wgLDAPDomainNames;
	printTestTitle("Checking all LDAP domains.");
	foreach ($wgLDAPDomainNames as $domain) {
		$_SESSION['wsDomain'] = $domain;
		
		printTestTitle("Checking domain: $domain");
		$valid = checkLDAPServerName($domain) &&
				 (checkBindAnonymous($domain) || checkBindProxyAgent($domain)) &&
				 checkDefaultBaseDN($domain) &&
				 checkUserBaseDN($domain) &&
				 checkGroupBaseDN($domain) &&
				 checkGroupObjectclass($domain) &&
				 checkGroupMemberAttribute($domain) &&
				 checkGroupNameAttribute($domain) &&
				 checkUserObjectclass($domain) &&
				 checkUserNameAttribute($domain) &&
				 checkGetGroups($domain) &&
				 checkReadUserData($domain) &&
				 checkAuthenticate($domain);
		
		if (!$valid) {
			return false;
		}
		printInfo("\nThe configuration of domain $domain is correct.\n");
	}
	return true;
}

/**
 * Checks if the server for the given $domain is specified.
 * @param String $domain
 */
function checkLDAPServerName($domain) {
	global $wgLDAPServerNames;
	printTestTitle("Checking the server for LDAP domain: $domain");
	if ($wgLDAPServerNames 
		&& is_array($wgLDAPServerNames) 
		&& array_key_exists($domain, $wgLDAPServerNames)) {
		printInfo("The server is: $wgLDAPServerNames[$domain]");
		printSuccess();
	} else {
		printFailure(
			"Expected variable \$wgLDAPServerNames to be an array with the \n".
			"key '$domain' and a server name as value.\n");
		printSolution(
			"Example:\n".
			"\$wgLDAPServerNames = array(\"$domain\" => \"localhost\");\n");
		return false;
	}
	return true;
}

/**
 * 
 * Tries to bind anonymously to the LDAP server at the given $domain.
 * @param string $domain
 */
function checkBindAnonymous($domain) {
	global $wgLDAPServerNames;
	
	printTestTitle("Checking anonymous bind.");
	$_SESSION['wsDomain'] = $domain;
	$ldap = new CheckLdapAuthenticationPlugin();
	$ldap->connect();
	$bound = $ldap->bindAs();
	if (!$bound) {
		printFailure(
			"Binding failed. The LDAP server \n".
			"- may not be accessible or \n".
			"- may not support the specified encryption method (e.g. TLS) or \n".
			"- may not allow anonymous binds (see the result of the next test).\n".
			"LDAP error: ".ldap_error($ldap->ldapconn) ."\n");
		printSolution(
			"- Make sure that LDAP is running on the specified server:  $wgLDAPServerNames[$domain]\n".
			"- If starting TLS failed try another encryption method:\n".
			"    \$wgLDAPEncryptionType = array(\"$domain\" => \"clear\");\n". 
			"    or\n".
			"    \$wgLDAPEncryptionType = array(\"$domain\" => \"ssl\");\n".
			"- If the encryption (e.g. ssl) does not work, have a look at:\n".
			"    http://ryandlane.com/blog/2009/03/23/using-the-ldap-authentication-plugin-for-mediawiki-the-basics-part-1/#configuring-the-server\n".
			"- If anonymous binds are not allowed, see the next test.\n");
		return false;
	} else {
		printSuccess();
	}
	return true;
}

/**
 * 
 * Tries to bind the proxy agent to the LDAP server at the given $domain.
 * @param string $domain
 */
function checkBindProxyAgent($domain) {
	global $wgLDAPServerNames, $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword;
	
	printTestTitle("Checking bind with the proxy agent.");
	
	if ($wgLDAPProxyAgent && is_array($wgLDAPProxyAgent) && array_key_exists($domain, $wgLDAPProxyAgent)) {
		printInfo("The proxy agent is: $wgLDAPProxyAgent[$domain]");
	} else {
		printFailure(
			"Expected \$wgLDAPProxyAgent to be an array with the name of a proxy agent for\n".
			"domain $domain.\n");
		printSolution(
			"Example:\n".
			"\$wgLDAPProxyAgent = array(\"$domain\"=>\"cn=Manager,dc=acme,dc=home\");\n");
		return true;
	}
	
	if ($wgLDAPProxyAgentPassword && 
		is_array($wgLDAPProxyAgentPassword) && 
		array_key_exists($domain, $wgLDAPProxyAgentPassword)) {
		printInfo("The proxy agent password is: $wgLDAPProxyAgentPassword[$domain]");
	} else {
		printFailure(
			"Expected \$wgLDAPProxyAgentPassword to be an array with the password of a\n".
			"proxy agent for domain $domain.\n");
		printSolution(
			"Example:\n".
			"\$wgLDAPProxyAgentPassword = array(\"$domain\"=>\"secret password\");\n");
		return false;
	}
	$_SESSION['wsDomain'] = $domain;
	$ldap = new CheckLdapAuthenticationPlugin();
	$ldap->connect();
	$bound = $ldap->bindAs($wgLDAPProxyAgent[$domain], $wgLDAPProxyAgentPassword[$domain]);
	if (!$bound) {
		printFailure(
			"Binding failed as user $wgLDAPProxyAgent[$domain]\n".
			"with password $wgLDAPProxyAgentPassword[$domain].\n".
			"LDAP error: ".ldap_error($ldap->ldapconn) ."\n");
		printSolution(
			"Make sure to specify the correct user and password for binding to the LDAP server.\n");
		return false;
	} else {
		printInfo("Proxy agent was successfully bound.");
		printSuccess();
	}
	
	return true;
}

/**
 * Checks if the base DN (distinguished name) is set for the given $domain.
 * @param String $domain
 */
function checkDefaultBaseDN($domain) {
	global $wgLDAPBaseDNs;
	$baseDN = @$wgLDAPBaseDNs[$domain];
	return checkLDAPSchemaSetting($domain, 'default base DN', $wgLDAPBaseDNs, '$wgLDAPBaseDNs',
					"(!(cn=foobar))", NULL, $baseDN, '"dc=acme,dc=home"');
}

/**
 * Checks if the user base DN (distinguished name) is set for the given $domain 
 * and tries to retrieve user data.
 * @param String $domain
 */
function checkUserBaseDN($domain) {
	global $wgLDAPUserBaseDNs;
	$baseDN = @$wgLDAPUserBaseDNs[$domain];
	return checkLDAPSchemaSetting($domain, 'user base DN', $wgLDAPUserBaseDNs, '$wgLDAPUserBaseDNs',
	               "(!(cn=foobar))", NULL, $baseDN, '"ou=people,dc=acme,dc=home"');
}

/**
 * Checks if the group base DN (distinguished name) is set for the given $domain 
 * and tries to retrieve group data.
 * @param String $domain
 */
function checkGroupBaseDN($domain) {
	global $wgLDAPGroupBaseDNs;
	$baseDN = @$wgLDAPGroupBaseDNs[$domain];
	return checkLDAPSchemaSetting($domain, 'group base DN', $wgLDAPGroupBaseDNs, '$wgLDAPGroupBaseDNs',
	               "(!(cn=foobar))", NULL, $baseDN, '"ou=groups,dc=acme,dc=home"');
}

/**
 * 
 * Checks if the settings for the name of the object class of groups are correct
 * by querying all groups.
 * 
 * @param string $domain
 */
function checkGroupObjectclass($domain) {
	global $wgLDAPGroupObjectclass, $wgAuth;
	$groupClass = @$wgLDAPGroupObjectclass[$domain];
	return checkLDAPSchemaSetting($domain, 'object class for groups', $wgLDAPGroupObjectclass, 
				   '$wgLDAPGroupObjectclass',
	               "(objectclass=$groupClass)", NULL, $wgAuth->getBaseDN( GROUPDN ), 
				   '"groupOfNames"');
}

/**
 * 
 * Checks if the settings for the members of groups are correct
 * by querying all groups and their members.
 * 
 * @param string $domain
 */
function checkGroupMemberAttribute($domain) {
	global $wgLDAPGroupObjectclass, $wgLDAPGroupAttribute, $wgAuth;
	$groupClass = @$wgLDAPGroupObjectclass[$domain];
	$groupAttribute = @$wgLDAPGroupAttribute[$domain];
	return checkLDAPSchemaSetting($domain, 'member attribute for groups', $wgLDAPGroupAttribute, 
				   '$wgLDAPGroupAttribute',
	               "(objectclass=$groupClass)", $groupAttribute, $wgAuth->getBaseDN( GROUPDN ), 
				   '"member"');
}

/**
 * 
 * Checks if the settings for the names of groups are correct
 * by querying all groups and their names.
 * 
 * @param string $domain
 */
function checkGroupNameAttribute($domain) {
	global $wgLDAPGroupObjectclass, $wgLDAPGroupNameAttribute, $wgAuth;
	$groupClass = @$wgLDAPGroupObjectclass[$domain];
	$groupNameAttribute = @$wgLDAPGroupNameAttribute[$domain];
	return checkLDAPSchemaSetting($domain, 'group name attribute', $wgLDAPGroupNameAttribute, 
				   '$wgLDAPGroupNameAttribute',
	               "(objectclass=$groupClass)", $groupNameAttribute, $wgAuth->getBaseDN( GROUPDN ), 
				   '"cn"');
}

/**
 *
 * Checks if the settings for the name of the object class of users are correct
 * by querying all users.
 *
 * @param string $domain
 */
function checkUserObjectclass($domain) {
	global $wgLDAPUserObjectclass, $wgAuth;
	$userClass = @$wgLDAPUserObjectclass[$domain];
	return checkLDAPSchemaSetting($domain, 'object class for users', $wgLDAPUserObjectclass,
				   '$wgLDAPUserObjectclass',
	               "(objectclass=$userClass)", NULL, $wgAuth->getBaseDN( USERDN ), 
				   '"inetOrgPerson"');
}

/**
 *
 * Checks if the settings for the names of users are correct
 * by querying all users and their names.
 *
 * @param string $domain
 */
function checkUserNameAttribute($domain) {
	global $wgLDAPUserObjectclass, $wgLDAPUserNameAttribute, $wgAuth;
	$userClass = @$wgLDAPUserObjectclass[$domain];
	$userNameAttribute = @$wgLDAPUserNameAttribute[$domain];
	return checkLDAPSchemaSetting($domain, 'user name attribute', $wgLDAPUserNameAttribute,
				   '$wgLDAPUserNameAttribute',
	               "(objectclass=$userClass)", $userNameAttribute, $wgAuth->getBaseDN( USERDN ), 
				   '"cn"');
}

/**
 * Tries to retrieve data about a user from LDAP, given the user name that has to
 * be entered on the command line.
 * 
 * @param string $domain
 */
function checkReadUserData($domain) {
	global $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword;
	
	printTestTitle("Trying to retrieve the data for a user from domain $domain.");
	printInfo("Please enter the name of a user: ");
	$userName = trim(fgets(STDIN));
	
	$ldap = new CheckLdapAuthenticationPlugin();
	$ldap->connect();
	$bound = $ldap->bindAs($wgLDAPProxyAgent[$domain], $wgLDAPProxyAgentPassword[$domain]);
	if (!$bound) {
		$bound = $ldap->bindAs();
	}
	if (!$bound) {
		printFailure("Query to LDAP failed. Unable to bind server $wgLDAPServerNames[$domain].\n");
		return false;
	}

	$userDN = $ldap->getUserDN($userName);
	if (!$userDN) {
		printFailure("Failed to get a correct user DN.");
		printSolution(
			"Please check the variable \$wgLDAPSearchAttributes:\n".
			"Example:\n".
			"\$wgLDAPSearchAttributes = array(\"$domain\" => \"cn\")\n");
		return false;
	}
	printInfo("The Distinguished Name (DN) for this user is: $userDN");
	$userInfo = $ldap->getUserInfo();
	if ($userInfo && count($userInfo) > 1) {
		printInfo("Data for user $userName:");
		printLDAPResult($userInfo);
	} else {
		printFailure(
			"No user information found for $userName.\n".
			"This may indicate an error. Maybe you have to configure \$wgLDAPLowerCaseUsername.\n");
		printSolution(
			"Example:\n".
			"\t\$wgLDAPLowerCaseUsername = array(\"$domain\"=> true);\n");
	}
	return true;
	
}

/**
 * Tries to get all top level groups
 * @param unknown_type $domain
 */
function checkGetGroups($domain) {
	printTestTitle("Trying to retrieve all top level groups in domain $domain.");
	
	$haclStore = new HACLStorageLDAP();
	$groups = $haclStore->getGroups();
	
	$ldapGroupsFound = false;
	foreach ($groups as $g) {
		if ($g->getType() === HACLStorageLDAP::GROUP_TYPE_LDAP) {
			if (!$ldapGroupsFound) {
				printInfo("Found the following group(s):");
			}
			$ldapGroupsFound = true;
			printInfo($g->getGroupName());
			$members = $g->getUsers(HACLGroup::NAME);
			if (count($members) > 0) {
				printInfo("Users in this group:", 4);
				foreach ($members as $m) {
					printInfo($m, 6);
				}
			}
			$subgroups = $g->getGroups(HACLGroup::NAME);
			if (count($subgroups) > 0) {
				printInfo("Groups in this group:",4);
				foreach ($subgroups as $m) {
					printInfo($m, 6);
				}
			}
		}
	}
	if ($ldapGroupsFound) {
		printSuccess();
		return true;
	} else {
		echo "\tNo LDAP groups found for domain $domain.\n";
		return false;
	}
	
}

/**
 * Checks if its possible to log in as a user in the wiki using LDAP.
 * @param string $domain
 */
function checkAuthenticate($domain) {
	global $wgAuth;
	printTestTitle("Trying to authenticate user in domain $domain.");
	printInfo("Please enter the name of an LDAP user: ");
	$userName = trim(fgets(STDIN));
	printInfo("Please enter the password of this LDAP user: ");
	$passWD = trim(fgets(STDIN));
	
	$ldap = new CheckLdapAuthenticationPlugin();
	$success = $ldap->authenticate($userName, $passWD);
	if ($success) {
		printInfo("Authentication successful.\n");
		printSuccess();
		return true;
	}
	printFailure(
		"Authentication failed.\n".
		"Does this user with the given password exist in the LDAP system?\n");
	printSolution(
		"Authentication is incfluenced by the following settings:\n".
		"- \$wgLDAPAuthAttribute\n".
		"- \$wgLDAPAutoAuthUsername\n".
		"- \$wgLDAPLowerCaseUsername\n".
		"- \$wgLDAPSearchStrings\n".
		"See for more information:\n".
		"  http://www.mediawiki.org/wiki/Extension:LDAP_Authentication/Configuration\n".
		"  http://ryandlane.com/blog/2009/03/23/using-the-ldap-authentication-plugin-for-mediawiki-the-basics-part-1\n"
		);
	
	return false;
}



/**
 * Function for checking a schema setting of the LDAP domain
 * @param string $domain
 * @param string $schemaElementName
 * 		The name of the schema element is used in user messages.
 * @param array $setting
 * @param string $settingName
 * @param string $query
 * 		An LDAP query
 * @param string $attribute
 * 		An attribute to select in a result (can be NULL)
 * @param string $baseDN
 * 		The base DN for the query
 * @param string $example
 * @return boolean
 */
function checkLDAPSchemaSetting($domain, $schemaElementName, $setting, 
								$settingName, $query, $attribute, $baseDN, $example) {

	printTestTitle("Checking the $schemaElementName for domain: $domain");
	if ($setting
		&& is_array($setting)
		&& array_key_exists($domain, $setting)) {
			printInfo("The $schemaElementName is: $setting[$domain]");
			printSuccess();
	} else {
		printFailure(
			"Expected variable $settingName to be an array with the key '$domain'\n".
			"and the $schemaElementName as value.\n");
		printSolution(
			"Example:\n".
			"$settingName = array(\"$domain\" => $example);\n");
		return false;
	}
	
	printTestTitle("Trying to read data from the $schemaElementName for domain: $domain");
	$data = queryLDAP($domain, $query, $baseDN, $attribute);
	if (!$data) {
		printFailure(
			"The value of the $schemaElementName seems to be wrong in $settingName\n".
			"for the domain '$domain'.\n".
			"The current value is: $setting[$domain].\n");
		printSolution(
			"Please choose the correct $schemaElementName.\n".
			"Example:\n".
			"$settingName = array(\"$domain\" => $example);\n");
		
		return false;
	} else {
		printInfo("Read ${data['count']} entries from $schemaElementName.");
		printSuccess();
	}
	return true;
	
}

/**
 * 
 * Sends a $query to the given LDAP $domain. 
 * 
 * @param String $domain
 * @param String $query
 * @param array(string) $baseDN
 * 		The base DN for this query
 * @return boolean|array
 * 		false if connecting and binding the server failed
 * 		array of results otherwise
 */
function queryLDAP($domain, $query, $baseDN, $attributes = NULL) {
	global $wgLDAPProxyAgent, $wgLDAPProxyAgentPassword, $wgLDAPServerNames;
	$ldap = new CheckLdapAuthenticationPlugin();
	$ldap->connect();
	$bound = $ldap->bindAs($wgLDAPProxyAgent[$domain], $wgLDAPProxyAgentPassword[$domain]);
	if (!$bound) {
		$bound = $ldap->bindAs();
	}
	if (!$bound) {
		echo "\tQuery to LDAP failed. Unable to bind server $wgLDAPServerNames[$domain].\n";
		return false;
	}
	if ($attributes === NULL) {
		$attributes = array();
	} else if (!is_array($attributes)) {
		$attributes = array($attributes);
	}
	$info = @ldap_search($ldap->ldapconn, $baseDN, $query, $attributes);
	if (!$info) {
		echo "\tCould not query data from base DN $baseDN.\n";
		echo "\tThe query is: $query\n";
		echo "\tLDAP error: ". ldap_error($ldap->ldapconn)."\n";
		return false;
	}
	$data = @ldap_get_entries($ldap->ldapconn, $info);
	
	if (empty($attributes)) {
		return $data['count'] > 0 ? $data : false;
	}
	
	// Check if the result contains the expected attribute
	foreach ($attributes as $attr) {
		foreach ($data as $d) {
			if (is_array($d) && array_key_exists($attr, $d)) {
				return $data;
			}
		}
	}
	
	return false;
	
}

/**
 * Prints the title of a test.
 * @param string $title
 */
function printTestTitle($title) {
	
	$padding = 75 - strlen($title);
	if ($padding < 0) {
		$padding = 0;
	}
	echo "\n--- $title " . str_repeat('-', $padding) . "\n";
	
}

/**
 * Prints a message for success.
 */
function printSuccess() {
	echo "\n  ++ Configuration correct! ++\n";
}

/**
 * Prints an info message.
 * @param string $info
 * @param int $indent
 */
function printInfo($info, $indent = 2) {
	$lines = explode("\n", $info);
	foreach ($lines as $line) {
		echo str_repeat(' ', $indent).$line."\n";
	}
}

/**
 * Prints a debug message.
 * @param string $info
 * @param int $indent
 */
function printDebug($info, $indent = 2) {
	$lines = explode("\n", $info);
	foreach ($lines as $line) {
		echo str_repeat(' ', $indent)."! ".$line."\n";
	}
}

/**
 * Prints a failure.
 * @param string $info
 * @param int $indent
 */
function printFailure($info) {
	$lines = explode("\n", $info);
	foreach ($lines as $line) {
		echo "** ".$line."\n";
	}
}

/**
 * Prints a solution.
 * @param string $info
 * @param int $indent
 */
function printSolution($info) {
	echo str_repeat('*', 80);
	echo "\n".$info;
	echo str_repeat('*', 80);
}

/**
 * Prints an LDAP result
 * @param array $result
 */
function printLDAPResult($result) {
	array_shift($result);
	
	foreach ($result as $r) {
		foreach ($r as $key => $values) {
			if (!is_numeric($key)) {
				printInfo("$key:");
				if (is_array($values)) {
					foreach ($values as $vkey => $v) {
						if (is_numeric($vkey)) {
							printInfo("$v", 4);
						}
					}
				} else {
					printInfo($values, 4);
				}
			}
		}
	}
		
}

