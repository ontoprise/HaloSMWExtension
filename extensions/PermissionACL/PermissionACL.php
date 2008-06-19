<?php
 
if( !defined( 'MEDIAWIKI' ) ) {
    echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
    die(1);
}

if (file_exists('ACLs.php')) require_once('ACLs.php');

if (!class_exists('Services_JSON')) {
    require_once ('JSON.php');
}

global $wgAjaxExportList;

 
 // register ajax calls
 $wgAjaxExportList[] = 'smwf_al_updateACLs';
 
$wgExtensionCredits['other'][] = array(
        'name' => 'PermissionACL',
        'author' => 'Jan Vavricek',
        'url' => 'http://mediawiki.org/wiki/Extension:PermissionACL',
        'description' => 'permissions access control list',
);

global $wgExtensionFunctions, $wgHooks;
$wgHooks['userCan'][] = 'PermissionACL_userCan';

// use SMW_AddScripts hook from SMWHalo to make sure that Prototype is available.
$wgHooks['SMW_AddScripts'][]='wfACLAddHeader';
$wgExtensionFunctions[] = 'wfACLSetupExtension';

/**
 * Add javascripts and css files
 *
 * @param unknown_type $out
 * @return unknown
 */
function wfACLAddHeader(& $out) {
	global $wgScriptPath;
	$out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $wgScriptPath . '/extensions/PermissionACL/acl.css'
                ));
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/PermissionACL/acl.js"></script>');
	return true;
}

/**
 * Initializes PermissionACL extension 
 *
 * @return unknown
 */
function wfACLSetupExtension() {
	global $wgAutoloadClasses, $wgSpecialPages, $wgScriptPath;
	wfACLInitMessages();
	$wgAutoloadClasses['ACLSpecialPage'] = 'extensions/PermissionACL/ACLSpecialPage.php';
    $wgSpecialPages['ACL'] = array('ACLSpecialPage');
    return true;
}

/**
 * Registers ACL messages.
 */
function wfACLInitMessages() {
        global $wgMessageCache, $wgLang;

        $aclLangClass = 'ACL_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );
    
        if (file_exists('extensions/PermissionACL/languages/'. $aclLangClass . '.php')) {
            include_once('extensions/PermissionACL/languages/'. $aclLangClass . '.php' );
        }
        // fallback if language not supported
       if ( !class_exists($aclLangClass)) {
            include_once('extensions/PermissionACL/languages/ACL_LanguageEn.php' );
            $aclgHaloLang = new ACL_LanguageEn();
        } else {
            $aclgHaloLang = new $aclLangClass();
        }
       
        $wgMessageCache->addMessages($aclgHaloLang->acl_userMessages, $wgLang->getCode());

}

function smwf_al_updateACLs($acl_list_json, $whitelist, $superusers) {
    $jsonservice = new Services_JSON();
    $acls = $jsonservice->decode($acl_list_json);
    $acl_manager = ACLManager::SINGLETON();
    $acl_manager->writeConfig($acls, $whitelist, $superusers);
    return "true";
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
            $user = $pm->user;
            $namespaces = $pm->namespaces;
            $action = $pm->action;
            $operation = $pm->operation;
            $namespacesText = ACLManager::convertNSToIndex($namespaces);
            $category = $pm->category;
            $page = $pm->page;
            
            $data .= "\$wgPermissionACL[] = array(";
            if ($group != null) {
                 $data .= "'group' => \"".$group[0]."\",\n";
            } else {
                 $data .= "'user' => \"".$user[0]."\",\n";
            }
            
            if ($namespaces != NULL) {
                $data .= "\t'namespace' => ".$namespacesText[0].",\n";
            } else if ($category != NULL) {
                $data .= "\t'category' => \"".str_replace(" ","_",trim($category[0]))."\",\n";
            } else {
                $data .= "\t'page' => \"".str_replace(" ","_",trim($page[0]))."\",\n";
            }
                        
            $data .= "\t'action' => \"".$action[0]."\",\n";
            $data .= "\t'operation' =>\"".$operation."\"\n";
            $data .= ");\n\n";
        }
        
        // serialize whitelist
        $whitelist = explode(",", $whitelist);
        global $wgContLang;
        $spns_text = $wgContLang->getNsText(NS_SPECIAL);
        $sp_aliases = $wgContLang->getSpecialPageAliases();
        $data .= "\$wgWhitelistRead = array('$spns_text:".$sp_aliases['Userlogin'][0]."',". 
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
// old extension Code

function PermissionACL_userCan($title, $user, $action, &$result) {
	
    global $wgPermissionACL, $wgPermissionACL_Superuser, $wgWhitelistRead;
 
    $result = NULL;
 
    if(!isset($wgPermissionACL)) return TRUE; //if not set - grant access
 
    if($title->isCssJsSubpage()) return TRUE;
 
    if($action == 'read' && is_array($wgWhitelistRead)) { //don't continue if - read action on whitelisted pages
        if(in_array($title->getPrefixedText(), @$wgWhitelistRead)) {
            $result = TRUE;
            return TRUE;
        }//if-in_array
    }//if-whitelist
 
    if(isset($wgPermissionACL_Superuser)) { //Superuser can do everything - no need to read ACLs
        if(is_array($wgPermissionACL_Superuser)) {
            if(in_array(strtolower($user->getName()), ArrayToLower($wgPermissionACL_Superuser))) return TRUE;
        }//if-superusers array
        else if(strtolower($user->getName()) == strtolower($wgPermissionACL_Superuser)) return TRUE;
    }//if-superuser
 
    foreach($wgPermissionACL as $rule) { //process ACLs
        if(!PermissionACL_isRuleValid($rule)) continue; //syntax checking
 
        if(PermissionACL_isRuleApplicable($rule, $title, $user, $action)) {
            if(PermissionACL_Permit($rule)) {
                $result = TRUE;
                return TRUE;
            }
            else {
                $result = FALSE;
                return FALSE;
            }
        }//if-applicable
    }//foreach
 
    //implicit end rule - DENY ALL
    $result = FALSE;
    return FALSE;
}//PermissionACL_userCan
 
function PermissionACL_isRuleValid($rule) {
    /* rule parts:
        'group' || 'user'
        'namespace' || 'page' || 'category'
        'action' = (read, edit, create, move, *)
        'operation' = (permit, deny)
    */
    $tmp_actions    = array('read', 'edit', 'create', 'move', '*');
    $tmp_operations = array('permit', 'deny');
 
    if( ( isset($rule['group']) && !isset($rule['user'])) ||
        (!isset($rule['group']) &&  isset($rule['user'])) ) {
 
        if( ( isset($rule['namespace']) && !isset($rule['page']) && !isset($rule['category'])) ||
            (!isset($rule['namespace']) &&  isset($rule['page']) && !isset($rule['category'])) ||
            (!isset($rule['namespace']) && !isset($rule['page']) &&  isset($rule['category'])) ) {
 
            if( isset($rule['action'])  && ((is_string($rule['action']) && in_array($rule['action'], $tmp_actions)) ||
                is_array($rule['action']) )) {
 
                if( isset($rule['operation']) && in_array($rule['operation'], $tmp_operations)) {
                    return TRUE;
                }//if-operation test
            }//if-action test
        }//if-namespace, page, category test
    }//if-user, group test
 
    return FALSE;
}//function PermissionACL_isRuleValid
 
function PermissionACL_isRuleApplicable($rule, $title, $user, $action) {
    //group|user rule
    if(isset($rule['group'])) { //group rule
        if(is_array($rule['group']))
            $tmp = ArrayToLower($rule['group']);
        else
            $tmp = strtolower($rule['group']);
 
        $groups = ArrayToLower($user->getEffectiveGroups());
        if(!( (is_string($tmp) && in_array($tmp, $groups)) ||
              (is_array($tmp) && count(array_intersect($tmp, $groups))>0)
            )) return FALSE;
    }
    else { // user rule
        if(is_array($rule['user']))
            $tmp = ArrayToLower($rule['user']);
        else
            $tmp = strtolower($rule['user']);
        $tmp2 = strtolower($user->getName());
 
        if(!( (is_string($tmp) && $tmp=='*') ||
              (is_string($tmp) && $tmp==$tmp2) ||
              (is_array($tmp) && in_array($tmp2, $tmp))
            )) return FALSE;
    }
 
    //namespace|page|category rule
    if(isset($rule['namespace'])) { //namespace rule
        $tmp = $rule['namespace'];
        $tmp2 = $title->getNamespace();
 
        if(!( (is_int($tmp) &&  $tmp==$tmp2) ||
              (is_string($tmp) && $tmp=='*') ||
              (is_array($tmp) && in_array($tmp2, $tmp))
            )) return FALSE;
    }
    else if(isset($rule['page'])){ //page rule
        $tmp = $rule['page'];
        $tmp2 = $title->getPrefixedText();
 
        if(!( (is_string($tmp) && $tmp==$tmp2) ||
              (is_string($tmp) && $tmp=='*') ||
              (is_array($tmp) && in_array($tmp2, $tmp))
            )) return FALSE;
    }
    else { //category rule
        $tmp = $rule['category'];
        $tmp2 = $title->getParentCategories();
        $categs = array();
 
        if(is_array($tmp2)) {
            global $wgContLang;
            $tmp_pos = strrpos($wgContLang->getNSText(NS_CATEGORY), ':');
 
            foreach($tmp2 as $cat => $page) {
                if($tmp_pos === FALSE) {
                    $categs[] = substr($cat, strpos($cat, ':')+1);
                }
                else {
                    $tmp_categ = substr($cat, $tmp_pos+1);
                    $categs[] = substr($tmp_categ, strpos($tmp_categ, ':')+1);
                }
            }
        }
 
        if(!( (is_string($tmp) && is_array($tmp2) && in_array($tmp, $categs)) ||
              (is_string($tmp) && $tmp=='*') ||
              (is_array($tmp)  && is_array($tmp2) && count(array_intersect($tmp, $categs))>0)
            )) return FALSE;
    }
 
    //action rule
    if(is_array($rule['action']))
        $tmp = ArrayToLower($rule['action']);
    else
        $tmp = strtolower($rule['action']);
 
    if(!( ($tmp == $action) ||
          (is_string($tmp) && $tmp=='*') ||
          (is_array($tmp) && in_array($action, $tmp))
        )) return FALSE;
 
    return TRUE;
}//function PermissionACL_isRuleApplicable
 
function PermissionACL_Permit($rule) {
    if($rule['operation'] == 'permit') return TRUE;
    return FALSE;
}//function PermissionACL_Permit
 
function ArrayToLower($ar) {
    $tmp = array();
    foreach($ar as $index => $value)
        $tmp[$index] = strtolower($value);
    return $tmp;
}
?>