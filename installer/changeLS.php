<?php
 	
	$variables = array();
	for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	 
		$keyvalue = explode("=", $arg);
	
		if (is_array($keyvalue) && (count($keyvalue) == 2)) {
			$variables[$keyvalue[0]] = $keyvalue[1];
		}
	}
	
	print "\nRead LocalSettings.php...";
	$content = readLocalSettings("LocalSettings.php.template");
	foreach($variables as $key => $value) {
		print "\nUpdate variable: $key";
		switch($key) {
			case 'importSMW': importSMW($content);break;
			case 'importSMWPlus': importSMWPlus($content);break;		
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
	   		$content .= "require_once( \"{$IP}/extensions/Cite.php\" );\n".
						"require_once( \"$IP/extensions/ParserFunctions/ParserFunctions.php\" );\n".
						"include_once(\"extensions/SMWHalo/includes/SMW_Initialize.php\");\n".
						"enableSMWHalo();\n"; 
	   
	   }
?>