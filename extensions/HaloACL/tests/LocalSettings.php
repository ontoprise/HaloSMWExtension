/**
 * @file
 * @ingroup HaloACL_Tests
 */
//$smwgMessageBroker='localhost';
$smwgWebserviceEndpoint='localhost:8090';
$smwgEnableObjectLogicRules=true;
$smwgWebserviceProtocol="rest";

#Import SMW, SMWHalo and the Gardening extension
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);

include_once('extensions/ARCLibrary/ARCLibrary.php');
enableARCLibrary();
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo('SMWHaloStore2', "SMWTripleStore");

include_once('extensions/HaloACL/includes/HACL_Initialize.php');
$haclgBaseStore = HACL_STORE_LDAP;
enableHaloACL(); 

//--- LDAP ---
require_once( "$IP/extensions/LdapAuthentication/LdapAuthentication.php" );
$wgAuth = new LdapAuthenticationPlugin();
$wgLDAPDomainNames = array( "TestLDAP", "OntopriseAD" );
$wgLDAPServerNames = 
	array(	"TestLDAP"    => "localhost",
			"OntopriseAD" => '10.0.0.220' );
//			"OntopriseAD" => 'sg4-server' );
$wgLDAPSearchStrings = 
	array(	"TestLDAP"    => "cn=USER-NAME,ou=people,dc=ontoprise,dc=home",
			"OntopriseAD" => "USER-NAME@sg4.ontoprise.de");
$wgLDAPSearchAttributes = array(
			"TestLDAP" => "cn",
			"OntopriseAD"=>"sAMAccountName"  );
$wgLDAPGroupUseFullDN = array(
			"OntopriseAD"=>true  );

//User and password used for proxyagent access.
//Please use a user with limited access, NOT your directory manager!
$wgLDAPProxyAgent = array(
			"OntopriseAD"=>"CN=Wikisysop,OU=Users,OU=SMW,DC=sg4,DC=ontoprise,DC=de" );
$wgLDAPProxyAgentPassword = array(
			"OntopriseAD"=>"test"  );

 	
$wgLDAPEncryptionType = 
	array(	"TestLDAP"    => "clear",
			"OntopriseAD" => "clear");
$wgLDAPLowerCaseUsername = 
	array(	"TestLDAP"    => true,
			"OntopriseAD" => true);

$wgLDAPBaseDNs =
	array(	'TestLDAP' => 'dc=ontoprise,dc=home',
			"OntopriseAD" => 'OU=SMW,DC=sg4,DC=ontoprise,DC=de');
	
$wgLDAPUserBaseDNs =	
	array(	'TestLDAP'    => 'ou=people,dc=ontoprise,dc=home',
			"OntopriseAD" => 'OU=Users,OU=SMW,DC=sg4,DC=ontoprise,DC=de');
$wgLDAPGroupBaseDNs =	
	array(	'TestLDAP'    => 'ou=groups,dc=ontoprise,dc=home',
			"OntopriseAD" => 'OU=Groups,OU=SMW,DC=sg4,DC=ontoprise,DC=de');
	
//The objectclass of the groups we want to search for
$wgLDAPGroupObjectclass = 
	array( "TestLDAP"     => "groupOfNames" ,
			"OntopriseAD" => "group");
//The attribute used for group members
$wgLDAPGroupAttribute = 
	array(	"TestLDAP"=>"member" ,
			"OntopriseAD" => "member");
//The naming attribute of the group
$wgLDAPGroupNameAttribute = 
	array(	"TestLDAP"    => "cn" ,
			"OntopriseAD" =>  "cn");
$wgLDAPGroupSearchNestedGroups = 
	array(	"TestLDAP"    => true ,
			"OntopriseAD" => true);
$wgLDAPUseLDAPGroups = 
	array(	"TestLDAP"    => true ,
			"OntopriseAD" => true);
//$wgLDAPGroupsUseMemberOf = array( "TestLDAP" => true );
//The objectclass of the users we want to search for
$wgLDAPUserObjectclass = 
	array(	"TestLDAP"    => "inetOrgPerson" ,
			"OntopriseAD" => "person"); // only for HaloACL
$wgLDAPUserNameAttribute = 
	array(	"TestLDAP"    => "cn" ,
			"OntopriseAD" => "samaccountname"); // only for HaloACL
