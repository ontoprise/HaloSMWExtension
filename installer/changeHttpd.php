<?php
 	
	$variables = array();
	for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	 
		$keyvalue = explode("=", $arg);
	
		if (is_array($keyvalue) && (count($keyvalue) == 2)) {
			$variables[$keyvalue[0]] = str_replace('\\', "/", $keyvalue[1]);
		}
	}
	$content = readHttpd($variables['httpd']);
	$content .= getTemplate();
	foreach($variables as $key => $value) {
		print "\nUpdate variable: $key";
		$content = str_replace('{{'.$key.'}}', $value, $content);
	  
	}
	writeHttpd($variables['httpd'], $content);
	  function readHttpd($filename) {
	   	$handle = fopen($filename, "rb");
	 	$contents = fread ($handle, filesize ($filename));
	 	fclose($handle);
	 	$contents = str_replace("?>", "", $contents);
	 	return $contents;
	   }
	   
	   function writeHttpd($filename, $text) {
	   
	   	$handle = fopen($filename,"wb");
		fwrite($handle, $text);
	 	fclose($handle);
	   }
	   
	   function getTemplate() {
	   	return "\n<Directory \"{{fs-path}}\">".
			   	"\n\tOptions Indexes MultiViews".
			   	"\n\tAllowOverride None".
			   	"\n\tOrder allow,deny".
			   	"\n\tAllow from all".
			   	"\n</Directory>".
			   	"\nAlias /{{wiki-path}} \"{{fs-path}}\"\n";
	   }
?>