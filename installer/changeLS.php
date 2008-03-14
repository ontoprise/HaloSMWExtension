<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
* 
* 
* 	Updates LocalSettings.php file
* 	
* 	Takes Key/Value Pairs as parameters and set/change them in LocalSettings.php. Additionally
* 	some include statements are added if specified.
*/ 	
    
    // Stores all variable bindings:
    //
    //  1. simple key/value pairs, e.g. $var1="test"
    //  2. arrays, e.g. $var2=array("test",test2")
    //  3. hash arrays, e.g. $var3=array("key1" => "value1");
    $variables = array();
	
    // Parses cmdline arguments
	for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	 
		$keyvalue = explode("=", $arg);
			
		if (is_array($keyvalue) && (count($keyvalue) == 2)) {
			if (preg_match('/^\[[^]]*\]$/', $keyvalue[1])) {
			    // arrays
			 $variables[$keyvalue[0]] = explode(",", substr(str_replace('\\', "/", $keyvalue[1]), 1, -1));	
			} else if (count(explode("~", $keyvalue[1])) == 2) {
				// hash arrays
				 $mapkeys = explode("~",  str_replace('\\', "/", $keyvalue[1]));
				 $variables[$keyvalue[0]] = new Mapping($mapkeys[0], $mapkeys[1]);
			} else {
				// normal variables
			 $variables[$keyvalue[0]] = str_replace('\\', "/", $keyvalue[1]);
			}
		}
	}
	
	// Reads file specified with parameter: ls
	print "\nRead ".$variables['ls']."...";
	$content = readLocalSettings($variables['ls']);
	
	// applies changes
	foreach($variables as $key => $value) {
		if ($value == '**notset**') continue;
		switch($key) {
			// imports, just adding some text
			case 'importSMW': print "\n *import SMW"; importSMW($content);break;
			case 'importSMWPlus': print "\n *import SMW+"; importSMWPlus($content);break;
			case 'importACL': print "\n *import ACL"; importACL($content);break;
			case 'importLDAP': print "\n *import LDAP"; importLDAP($content);break;
			case 'ls': break;
			
			// variable exchanges
			default:		
			    
				if (is_array($value)) {
					 $arrayValue = "array(";
					 for($i = 0; $i < count($value)-1; $i++) {
					 	if (is_numeric($value[$i])) $arrayValue .= $value[$i].", "; else $arrayValue .= "'".$value[$i]."', ";
					 }
					 if (is_numeric($value[count($value)-1])) $arrayValue .= $value[count($value)-1]." "; else $arrayValue .= "'".$value[count($value)-1]."' ";
					 $arrayValue .= ")";
					 setVariable($content, $key, $arrayValue, true);
					 print "\n *update variable: $key with value: $arrayValue";
				} else if ($value instanceof Mapping) {
					setVariable($content, $key, 'array('.$value->key.' => '.$value->value.')', true);
					print "\n *update variable: $key with value: ".'array('.$value->key.' => '.$value->value.')';
				} else {
					print "\n *update variable: $key with value: $value";
				    setVariable($content, $key, $value);
				}
		}
	  
	}
	
	// Write changed file
	print "\nWrite LocalSettings.php...";
	writeLocalSettings("LocalSettings.php", $content);
	
	// Helper functions
	  function readLocalSettings($filename) {
	   	$handle = fopen($filename, "rb");
	 	$contents = fread ($handle, filesize ($filename));
	 	fclose($handle);
	 	$contents = str_replace("?>", "", $contents);
	 	return $contents;
	   }
	   
	   function writeLocalSettings($filename, $text) {
	   	$text .= "\n?>";
	   	$handle = fopen($filename,"wb");
		fwrite($handle, $text);
	 	fclose($handle);
	   }
	   
	  function setVariable(& $content, $name, $value, $notquote=false) {
	   		if ($value == '') {
	   			// remove it
	   			$content = preg_replace('/\$'.preg_quote($name).'\s*=.*/', "", $content);
	   		
	   		}
	   		$value = is_numeric($value) || $value == 'true' || $value == 'false' || $notquote ? $value : "\"".$value."\"";
	   		if (preg_match('/\$'.preg_quote($name).'\s*=.*/', $content) > 0) {
	   			$content = preg_replace('/\$'.preg_quote($name).'\s*=.*/', "\$$name=$value;", $content);
	   		} else {
	   			$content = $content."\n\$$name=$value;";
	   		}
	   		
	   }
	   
	   function getVariable($content, $name) {
	   		$matches = array();
	   		preg_match('/\$'.$name.'\s*=([^;]*)/', $content, $matches);
	   		return trim($matches[1]);
	   }
	   
	   function importSMW(& $content) {
	    	$content .= "include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');\n".
	    		   "enableSemantics('localhost:8080');";
	   }
	   
	   function importSMWPlus(& $content) {
	   		$content .= "require_once( \"\$IP/extensions/Cite.php\" );\n".
						"require_once( \"\$IP/extensions/ParserFunctions/ParserFunctions.php\" );\n".
						"include_once(\"extensions/SMWHalo/includes/SMW_Initialize.php\");\n".
						"enableSMWHalo();\n"; 
	   
	   }
	   
	   function importACL(& $content) {
	   		$content .= "require_once('extensions/PermissionACL.php');\n".
	   		"if (file_exists('ACLs.php')) require_once('ACLs.php');";	
	   }
	   
	   function importLDAP(& $content) {
	   		$content .= "require_once('extensions/LdapAuthentication.php');\n".
						"\$wgAuth = new LdapAuthenticationPlugin();\n";
	   }
	   
	   class Mapping {
	   	 public $key;
	   	 public $value;
	   	 
	   	 public function __construct($key, $value) {
	   	 	$this->key = "'".$key."'";
	   	 	$this->value = is_numeric($value) ? $value : "'".$value."'";
	   	 }
	   }
?>