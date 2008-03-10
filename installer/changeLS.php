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
	$variables = array();
	for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	 
		$keyvalue = explode("=", $arg);
	
		if (is_array($keyvalue) && (count($keyvalue) == 2)) {
			$variables[$keyvalue[0]] = str_replace('\\', "/", $keyvalue[1]);
		}
	}
	
	print "\nRead ".$variables['ls']."...";
	$content = readLocalSettings($variables['ls']);
	foreach($variables as $key => $value) {
		if ($value == '**notset**') continue;
		print "\nUpdate variable: $key";
		switch($key) {
			case 'importSMW': importSMW($content);break;
			case 'importSMWPlus': importSMWPlus($content);break;
			case 'ls': break;		
			default: setVariable($content, $key, $value);
		}
	  
	}
	print "\nWrite LocalSettings.php...";
	writeLocalSettings("LocalSettings.php", $content);
	
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
	   
	  function setVariable(& $content, $name, $value) {
	   		if ($value == '') {
	   			// remove it
	   			$content = preg_replace('/\$'.$name.'\s*=.*/', "", $content);
	   		
	   		}
	   		$value = is_numeric($value) || $value == 'true' || $value == 'false' ? $value : "\"".$value."\"";
	   		if (preg_match('/\$'.$name.'\s*=.*/', $content) > 0) {
	   			$content = preg_replace('/\$'.$name.'\s*=.*/', "\$$name=$value;", $content);
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
?>