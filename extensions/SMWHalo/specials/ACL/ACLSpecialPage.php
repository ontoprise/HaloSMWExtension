<?php
/*
 * Created on 12.03.2007
 *
 * Author: kai
 */
 if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( "$IP/includes/SpecialPage.php" );

global $wgAjaxExportList;

 
 // register ajax calls
 $wgAjaxExportList[] = 'smwf_al_updateACLs';
 
/*
 * Called when gardening request in sent in wiki
 */

class ACLSpecialPage extends SpecialPage {
	
	
	public function __construct() {
		parent::__construct('ACL', 'delete');
	}
	
	public function execute() {
		global $wgRequest, $wgOut, $wgPermissionACL, $wgContLang, $wgLang, $wgWhitelistRead, $wgPermissionACL_Superuser, $wgExtensionCredits;
		$wgOut->setPageTitle(wfMsg('acl'));
		if (!self::hasExtension("other", "PermissionACL")) {
			$wgOut->addHTML("PermissionACL extension not detected! Please insert: <pre>require_once('extensions/PermissionACL.php');\n".
							"if (file_exists('ACLs.php')) require_once('ACLs.php');</pre>in your LocalSettings.php!");
			return;
		}
		$html = "<div style=\"margin-bottom:10px;\">".wfMsg('acl_welcome')."</div>";
		$html .= "<form id=\"permissions\"><table class=\"smwtable\">";
		$html .= "<tr><th width=\"30\"><input type=\"button\" name=\"up\" value=\"".wfMsg('smw_acl_up')."\" onclick=\"acl.up()\"/>".
		"<input type=\"button\" name=\"down\" value=\"".wfMsg('smw_acl_down')."\" onclick=\"acl.down()\"/>".
		"</th><th>".wfMsg('smw_acl_groups')."</th><th>".wfMsg('smw_acl_namespaces')."</th><th>".wfMsg('smw_acl_actions')."</th><th>".wfMsg('smw_acl_permission')."</th></tr>";
		$i = 0;
		if (isset($wgPermissionACL)) {
			foreach($wgPermissionACL as $pm) {
				$group = $pm['group'];
				$groupText = is_array($group) ? implode(",", $group) : $group;
				
				$namespaces = $pm['namespace'];
				$namespacesText = is_array($namespaces) ? $this->implodeNS($namespaces) : ($namespaces == "*" ? "*" : $wgLang->getNsText($namespaces));
				
				$action = is_array($pm['action']) ? implode(",", $pm['action'] ) : $pm['action'];
				$actionText = is_array($action) ? implode(",", $this->translateActions($action)) : wfMsg('smw_acl_'.$action);
				
				$operation = $pm['operation'];
				
				$html .= "<tr>";
				$html .= "<td><input type=\"radio\" name=\"select\" value=\"\"/></td>";
				$html .= "<td>".$groupText."</td>";
				$html .= "<td>".$namespacesText."</td>";
				$html .= "<td value=\"".$action."\">".$actionText."</td>";
				$html .= "<td value=\"$operation\">".wfMsg('smw_acl_'.$operation)."</td>";
				$html .= "</tr>";
				$i++;
			}
		}
		$html .= "</table></form>";
		$whitelistText = isset($wgWhitelistRead) && is_array($wgWhitelistRead) ? implode(",",array_slice($wgWhitelistRead, 5)) : "";
		$superusersText = isset($wgPermissionACL_Superuser)  && is_array($wgPermissionACL_Superuser) ? implode(",",$wgPermissionACL_Superuser) : "";
		$html .= "<p>".wfMsg('smw_acl_whitelist').":<br><input id=\"whitelist\" type=\"text\" size=\"50\" name=\"whitelist\" value=\"".$whitelistText."\"/></p>";
		$html .= "<p>".wfMsg('smw_acl_superusers').":<br> <input id=\"superusers\" type=\"text\" size=\"50\" name=\"superusers\" value=\"".$superusersText."\"/></p>";
		$html .= "<br><input type=\"button\" name=\"update\" value=\"".wfMsg('smw_acl_update')."\" onclick=\"acl.update()\"/>";
		$html .= "<input type=\"button\" name=\"delete_$i\" value=\"".wfMsg('smw_acl_remove')."\" onclick=\"acl.removeRule()\"/>";
		
		$allgroups = User::getAllGroups();
		$allnamespaces = $wgContLang->getNamespaces();
		$html .= "<h2>".wfMsg('smw_acl_newrule')."</h2>";
		$html .= "<form id=\"newRule\">";
		$html .= "<table border=\"0\">";
		$html .= "<tr>";
			$html .= "<td>".wfMsg('smw_acl_groups')."</td>";
			$html .= "<td><select name=\"group\" id=\"group\">";
		
			
			$html .= "<option selected=\"selected\">*</option>";
			foreach($allgroups as $group) {
				
				$html .= "<option>".$group."</option>";
				
				
			}
 			$html .= "</select></td>";
			
		$html .= "</tr>";
		$html .= "<tr>";
			$html .= "<td>".wfMsg('smw_acl_namespaces')."</td>";
			$html .= "<td><select name=\"namespaces\" id=\"namespaces\">";
		
			
			$html .= "<option selected=\"selected\">*</option>";
			foreach($allnamespaces as $ns) {
				if ($ns == '') $ns = 'Main';
				$html .= "<option>".$ns."</option>";
				
				
			}
 			$html .= "</select></td>";
		$html .= "</tr>";
		$html .= "<tr>";
			$html .= "<td>".wfMsg('smw_acl_actions')."</td>";
			$html .= "<td><select name=\"action\" id=\"action\">";
			$html .= "<option value=\"*\" selected=\"selected\">*</option>";
			$html .= "<option value=\"read\">".wfMsg('smw_acl_read')."</option>";
			$html .= "<option value=\"edit\">".wfMsg('smw_acl_edit')."</option>";
			$html .= "<option value=\"create\">".wfMsg('smw_acl_create')."</option>";
			$html .= "<option value=\"move\">".wfMsg('smw_acl_move')."</option>";
 			$html .= "</select></td>";
		$html .= "</tr>";
		$html .= "<tr>";
			$html .= "<td>".wfMsg('smw_acl_permission')."</td>";
			$html .= "<td><select name=\"operation\" id=\"operation\">";
			$html .= "<option value=\"permit\" selected=\"selected\">".wfMsg('smw_acl_permit')."</option>";
			$html .= "<option value=\"deny\">".wfMsg('smw_acl_deny')."</option>";
			
 			$html .= "</select></td>";
		$html .= "</tr>";
		$html .= "</table></form>";
		$html .= "<input type=\"button\" name=\"addRule\" value=\"".wfMsg('smw_acl_addrule')."\" onclick=\"acl.addRule()\"/>";
		$wgOut->addHTML($html);
	}
	
	private function implodeNS($namespaces) {
		global $wgLang;
		$result = "";
		for($i = 0; $i < count($namespaces)-1; $i++) {
			$result .= $namespaces[$i] == NS_MAIN ? "Main, " : $wgLang->getNsText($namespaces[$i]).", ";
		}
		$result .= $namespaces[$i] == NS_MAIN ? "Main" : $wgLang->getNsText($namespaces[count($namespaces)-1]);
		return $result;
	}
	
	private function translateActions(& $tarray) {
		foreach($tarray as $t) {
			$t = wfMsg('smw_acl_'.$t);
		}
		return $tarray;
	}
	
	private static function hasExtension($ext, $name) {
		global $wgExtensionCredits;
		if (isset($wgExtensionCredits[$ext])) {
			foreach($wgExtensionCredits[$ext] as $e) {
				if ($e['name']==$name) return true;
			}
		}
		return false;
	}
}

class ACLManager {
	
	private static $instance = NULL;
	
	public static function SINGLETON() {
		if (ACLManager::$instance == NULL) {
			$instance = new ACLManager();
		}
		return $instance;
	}
	
	public function writeConfig($acl_list, $whitelist, $superusers) {
		$serializedData = $this->serialize($acl_list, $whitelist, $superusers);
		$handle = fopen('ACLs.php','wb');
		fwrite($handle, $serializedData);
	 	fclose($handle);
	}
	
	private function serialize($acl_list, $whitelist, $superusers) {
		global $wgWhitelistRead, $wgPermissionACL_Superuser, $wgLang;
		
		$data = "<?php\nglobal \$wgPermissionACL, \$wgWhitelistRead, \$wgPermissionACL_Superuser;\n\n";
		
		// serialize permissions
		foreach($acl_list as $pm) {
			$group = $pm->group;
			$namespaces = $pm->namespaces;
			$action = $pm->action;
			$operation = $pm->operation;
			$groupText = ACLManager::implodeQuoted(",", $group);
			$namespacesText = count($namespaces) > 1 ? 
					"array(".implode(",", ACLManager::convertNSToIndex($namespaces)).")" :
					implode(",", ACLManager::convertNSToIndex($namespaces));
			$actionText = count($action) > 1 ? "array(".ACLManager::implodeQuoted(",", $action).")" : "\"".$action[0]."\"";
			$data .= "\$wgPermissionACL[] = array( 'group' => array(".$groupText."),\n". 
						"\t'namespace' => ".$namespacesText.",\n".
						"\t'action' => ".$actionText.",\n".
						"\t'operation' =>\"".$operation."\"\n".
						");\n\n";
		}
		
		// serialize whitelist
		$whitelist = explode(",", $whitelist);
		global $wgContLang;
		$spns_text = $wgContLang->getNsText(NS_SPECIAL);
		$sp_aliases = $wgContLang->getSpecialPageAliases();
		$data .= "\$wgWhitelistRead = array('$spns_text:ACL', '$spns_text:".$sp_aliases['Userlogin'][0]."',". 
		"'$spns_text:".$sp_aliases['Userlogout'][0]."', '$spns_text:".$sp_aliases['Resetpass'][0]."', ".ACLManager::implodeQuoted(",", $whitelist).");\n\n";
				
		// serialize superusers
		$superusers = explode(",", $superusers);
		$data .= "\$wgPermissionACL_Superuser = array(".ACLManager::implodeQuoted(",", $superusers).");\n\n";
		
		$data .= "?>";
		return $data;
	}
	
	private static function implodeQuoted($delimeter, $tarray) {
		$result = array();
		foreach($tarray as $t) {
			$result[] = '"'.trim($t).'"';
		}
		return implode($delimeter, $result);
	}
	
	private static function convertNSToIndex($namespaces) {
		global $wgLang;
		$result = array();
		if (!is_array($namespaces)) $namespaces = array($namespaces);
		foreach($namespaces as $ns) {
			switch(trim($ns)) {
				case '*' : $result[] = '"*"';break;
				case 'Main' :  $result[] = 0;break;
				default: $result[] = $wgLang->getNsIndex(trim($ns));
			}
					
		}
		return $result;
	}
}

function smwf_al_updateACLs($acl_list_json, $whitelist, $superusers) {
	$acls = json_decode($acl_list_json);
	$acl_manager = ACLManager::SINGLETON();
	$acl_manager->writeConfig($acls, $whitelist, $superusers);
	return "true";
}
?>
