<html>
<!-- Copyright 2007, ontoprise GmbH
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
*	Configuration page for SMW and SMWHalo
*
-->

<head>
	<script type="text/javascript" src="index.js"></script>
</head>
<body style="background-color: #FBFBBA">
	<?php
		 if ($_GET["update"] == NULL) {
		 	
		 	// page is loaded. Read parameters: 
		 	$content = readLocalSettings('../../../LocalSettings.php');
		 	
		 	$phpInterpreterText = str_replace("\\", "/", getVariable($content, 'phpInterpreter'));
		 	$phpInterpreter = substr($phpInterpreterText,1,strlen($phpInterpreterText)-2);
		 	
		 	$gardeningBotDelayText = getVariable($content, 'smwgGardeningBotDelay');
		 	$gardeningBotDelay =  $gardeningBotDelayText == '' ? 100 : $gardeningBotDelayText;
		 	
		 	$semanticAC = isTrue($content, 'smwgSemanticAC');
		 	$enableDeployVersion = isTrue($content, 'smwgDeployVersion');
		 	$enabledLogging = isTrue($content, 'smwgEnableLogging');
		 	$keepGardeningConsole = isTrue($content, 'smwgKeepGardeningConsole');
		 	
		 	$collationText = getVariable($content, 'smwgDefaultCollation');
		 	$collation = $collationText == NULL ? NULL : substr($collationText, 1, strlen($collationText) -2);
		 } else {
		 	
		 	// update button was pressed.
		 	$phpInterpreter = $_GET["phpInterpreter"];
	   		$gardeningBotDelay = is_numeric($_GET["gdb"]) ? intval($_GET["gdb"]) : 100; 
		 	$semanticAC = $_GET["ac"] != '';  
	  		$enableDeployVersion = $_GET["ed"] != '';  
	  		$enabledLogging = $_GET["el"] != '';  
	  		$collation = $_GET["collation"] == NULL ? NULL : $_GET["collation"] ; 
	  		$keepGardeningConsole = $_GET["kgc"] != '';  
		 }
	?>
	<h1>SMW Halo configuration page</h1>
	This config page helps you to configure the SMW and SMWHalo extension without changing the LocalSettings.php file manually. You must have a running MediaWiki installation beforehand.
	Please make sure that this page is not available from outside. It is best to delete the config directory after configuration. 
	<form name="opt_params">
	<h2>Required configurations</h2>
	<p style="margin-left: 20px">
		Location of PHP-Interpreter: <input name="phpInterpreter" type="text" size="70" value="<?php echo $phpInterpreter ?>"/>
	</p>
	<h2>Optional configurations</h2>
	<p style="margin-left: 20px">
	
		Gardening Bot delay: <input name= "gdb" id="gbd" type="text" size="10" value="<?php echo $gardeningBotDelay ?>"/> in ms<br><br>
		Enable semantic Auto-completion: <input name="ac" id="ac" type="checkbox" <?php echo $semanticAC ? "checked" : "" ?>/><br><br>
		Enable deploy version: <input name="ed" id="ed" type="checkbox" <?php echo $enableDeployVersion ? "checked" : "" ?>/> (recommended)<br><br>
		Enable logging: <input name="el" id="el" type="checkbox" <?php echo $enabledLogging ? "checked" : "" ?>/> (not recommended)<br><br>
		Use standard collation:  <input id="sc" type="checkbox" <?php echo $collation == NULL ? "checked" : "" ?>/> (recommended) <input name="collation" id="collation" type="text" size="15" value="<?php echo $collation ?>" <?php echo $collation == NULL ? "disabled" : "" ?>/><br><br>
		Keep gardening console:  <input name="kgc" id="kgc" type="checkbox" <?php echo $keepGardeningConsole ? "checked" : "" ?>/> (not recommended) <br><br>
		<input name="update" id="update" type="submit" value="Update LocalSettings"/> 
	
	</p>
	</form>
	<?php
		define ('VARIABLE_INSERT_MARKER', '//SMWHALO Variable insertion marker. DO NOT REMOVE!!');
	   if ($_GET["update"] == NULL) {
	   		
	   		return;
	   }
	   
	   // read and validate variables
	  
	   if ($phpInterpreter == '') {
	   		echo "<p style=\"background-color:#F00;font-weight:bold; \">".'At least PHP-Interpreter path must be set!'."</p>";
		    return;
	   }
	   if (!file_exists($phpInterpreter)) { 
	   		echo "<p style=\"background-color:#F00;font-weight:bold; \">".'PHP interpreter at "'.$phpInterpreter.'" does not exist. (Forgot file extension .exe?)'."</p>";
		    return;
	   }
	   if (!is_numeric($_GET["gdb"])) {
	   		echo "<p style=\"background-color:#F00;font-weight:bold; \">".'GardeningBot delay must be an integer.'."</p>";
		    return;
	   }
	  
	   
	  
	   $content = readLocalSettings('../../../LocalSettings.php');
	  
	   
	   // check if SMW extension is already included. If not, add it at the end of LocalSettings.php
	   if (strpos($content, "include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');") === false) {
	   	 	$first = substr($content, 0, strpos($content, "?>") - 1);
	   		$first .= "\n" .
	   				  VARIABLE_INSERT_MARKER."\n" .
	   				  "\ninclude_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');\n" .
	   				  "enableSemantics('localhost:8080');\n";
			$content = $first."\n?>";
	   } else {
	   		// If it is already included, set a variable insert position marker BEFORE SMW is included.
	   		if (strpos($content, VARIABLE_INSERT_MARKER) === false) {
	   			$insertAt = strpos($content, "include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');");
	   			$content = substr($content, 0, $insertAt)."\n".VARIABLE_INSERT_MARKER."\n".substr($content, $insertAt); 
	   		}
	   }	
	   
	   // check if SMWHalo extension is already included. If not, add it at the end of LocalSettings.php
	   if (strpos($content, "include_once('extensions/SMWHalo/includes/SMW_Initialize.php');") === false) {
	   	 	$first = substr($content, 0, strpos($content, "?>") - 1);
	   		$first .= "\ninclude_once('extensions/SMWHalo/includes/SMW_Initialize.php');\n" .
	   				  "enableSMWHalo();\n";
			$content = $first."\n?>";
	   } 
	   
	    // set/remove/change LocalSettings.php
	    $content = setVariable($content, "phpInterpreter", str_replace("\\\\", "/", $phpInterpreter));
	    $content = setVariable($content, "smwgSemanticAC", $semanticAC ? "true" : "false");
	    $content = setVariable($content, "smwgGardeningBotDelay", $gardeningBotDelay);
	    $content = setVariable($content, "smwgDeployVersion", $enableDeployVersion ? "true" : "false");
	    $content = setVariable($content, "smwgEnableLogging", $enabledLogging ? "true" : "false");
	    $content = setVariable($content, "smwgDefaultCollation", $collation);
	    $content = setVariable($content, "smwgKeepGardeningConsole", $keepGardeningConsole ? "true" : "false");
	   	
	   	// always add
	   	$content = setVariable($content, "smwgAllowNewHelpQuestions", "true");
	   	$content = setVariable($content, "wgUseAjax", "true");
	   	$content = setVariable($content, "wgEnableUploads", "true");
	   	$content = setVariable($content, "smwgIQEnabled", "true");
	   	 
	   	    
	   writeLocalSettings('LocalSettings.php', $content);
	   
	   // success!
	   echo "<p style=\"background-color:#0F0;\">LocalSettings updated! Please copy it from SMWHalo/config to MediaWiki root directory.</p>";
	   
	   
	   // Functions
	   
	   function readLocalSettings($filename) {
	   	$handle = fopen($filename, "rb");
	 	$contents = fread ($handle, filesize ($filename));
	 	fclose($handle);
	 	return $contents;
	   }
	   
	   function writeLocalSettings($filename, $text) {
	   	$handle = fopen($filename,"wb");
		fwrite($handle, $text);
	 	fclose($handle);
	   }
	   
	   /**
	    * Sets a variable. 3 cases:
	    * 	(1) It does not exist -> It is added.
	    *   (2) It exists -> value is changed.
	    * 	(3) Value is empty -> variable is removed.
	    */
	   function setVariable($content, $name, $value) {
	   		if ($value == '') {
	   			// remove it
	   			$new = preg_replace('/\$'.$name.'\s*=.*/', "", $content);
	   			return $new;
	   		}
	   		$value = is_numeric($value) || $value == 'true' || $value == 'false' ? $value : "\"".$value."\"";
	   		if (preg_match('/\$'.$name.'\s*=.*/', $content) > 0) {
	   			$new = preg_replace('/\$'.$name.'\s*=.*/', "\$$name=$value;", $content);
	   		} else {
	   			$new = insertVariable($content, "\$$name=$value;");
	   		}
	   		return $new;
	   }
	   
	   function getVariable($content, $name) {
	   		$matches = array();
	   		preg_match('/\$'.$name.'\s*=([^;]*)/', $content, $matches);
	   		return trim($matches[1]);
	   }
	   
	   function isTrue($content, $name) {
	   		$matches = array();
	   		preg_match('/\$'.$name.'\s*=([^;]*)/', $content, $matches);
	   		return trim($matches[1]) == 'true';
	   }
	   
	   /**
	    * Insert Varibale at VARIABLE_INSERT_MARKER.
	    */
	   function insertVariable($content, $text) {
	   	$insertAt = strpos($content, VARIABLE_INSERT_MARKER) + strlen(VARIABLE_INSERT_MARKER);
	   	return substr($content, 0, $insertAt)."\n".$text."\n".substr($content, $insertAt);
	   }
	?>
</body>

</html>