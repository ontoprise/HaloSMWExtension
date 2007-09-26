<?php
/*
 * Created on 24.07.2007
 *
 * Author: kai
 * 
 * Used to pack javascript files to one big file.
 */
 
 // license constants
 define('MIT_LICENSE', 1);
 define('BSD_LICENSE', 2);
 define('GPL_LICENSE', 3);
 define('APACHE_LICENSE', 4);
 define('LGPL_LICENSE', 5);
 define('WICK_LICENSE', 6);
 
 // license hints
 $licenses = array( MIT_LICENSE => 'MIT-License',
 					BSD_LICENSE => 'BSD-License',
 					GPL_LICENSE => 'GPL-License',
 					APACHE_LICENSE => 'Apache-License',
 					LGPL_LICENSE => 'LGPL-License',
 					WICK_LICENSE => 'WICK-License');
 
 // add script name as hint or not?
 $addScriptName = true;
 
 $mediaWikiLocation = dirname(__FILE__) . '/..';

 // directory where the scripts are located
 $sourcePath = 'c:/temp/halo_js_scripts/';
 
 $buildAll = count($argv) == 1; // build all if no parameter is set
 
 if ($argv[1] == 'smw' || $buildAll) {  // standard scripts which are loaded always
 	 // name of output file
 	 $outputFile = $mediaWikiLocation.'/scripts/deployScripts.js';
 
 	 // scripts which will be packed in one JS file (in this order!)
	 $scripts = array('prototype.js' => MIT_LICENSE,
	 				  'slider.js' => MIT_LICENSE,
	 				  'smw_logger.js' => GPL_LICENSE,
	 				  'generalTools.js' => GPL_LICENSE,
	 				  'SMW_Language.js' => GPL_LICENSE,
	 				  'STB_Framework.js' => GPL_LICENSE,
	 				  'STB_Divcontainer.js' => GPL_LICENSE,
	 				  'wick.js' => WICK_LICENSE,
	 				  'SMW_Help.js' => GPL_LICENSE,
	 				  'SMW_Links.js' => GPL_LICENSE,
	 				  'Annotation.js' => GPL_LICENSE,
	 				  'WikiTextParser.js' => GPL_LICENSE,
	 				  'SMW_Ontology.js' => GPL_LICENSE,
	 				  'SMW_DataTypes.js' => GPL_LICENSE,
	 				  'SMW_GenericToolbarFunctions.js' => GPL_LICENSE,
	 				  'SMW_Container.js' => GPL_LICENSE,
	 				  'SMW_Category.js' => GPL_LICENSE,
	 				  'SMW_Relation.js' => GPL_LICENSE,
	 				  'SMW_Properties.js' => GPL_LICENSE,
	 				  'SMW_Refresh.js' => GPL_LICENSE,
	 				  'SMW_FactboxType.js' => GPL_LICENSE,
	 				  'CombinedSearch.js' => GPL_LICENSE,
	 				  'obSemToolContribution.js' => GPL_LICENSE /*,
	 				  'edit_area_loader.js',
	 				  'SMWEditInterface.js'*/
	 				  );
   	buildScripts($outputFile, $scripts);			  
 } 
 
 if ($argv[1] == 'OntologyBrowser' || $buildAll) { // scripts which are only loaded on OntologyBrowser Special page
 	$outputFile = $mediaWikiLocation.'/scripts/OntologyBrowser/deployOB.js';
   	 
 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array('prototype.js'  => MIT_LICENSE,
 				     'effects.js' => MIT_LICENSE,
 				     'smw_logger.js' => GPL_LICENSE,
					 'generalTools.js' => GPL_LICENSE, 
					 'SMW_Language.js' => GPL_LICENSE, 
					 'treeview.js' => GPL_LICENSE, 
					 'treeviewActions.js' => GPL_LICENSE, 
					 'treeviewData.js' => GPL_LICENSE);
 	buildScripts($outputFile, $scripts);
 } 
 
 if ($argv[1] == 'Gardening' || $buildAll) { // scripts which are only loaded on OntologyBrowser Special page
 	$outputFile = $mediaWikiLocation.'/scripts/Gardening/deployGardening.js';
   	 
 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array('prototype.js' => MIT_LICENSE,
 					 'effects.js' => MIT_LICENSE, 
 					 'smw_logger.js' => GPL_LICENSE,
					 'generalTools.js' => GPL_LICENSE, 
					 'SMW_Language.js' => GPL_LICENSE, 
					 'gardening.js' => GPL_LICENSE);
 	buildScripts($outputFile, $scripts);
 } 
 
 /**
  * Build one script file consisting of all scripts given in $scripts array.
  */
 function buildScripts($outputFile, $scripts) { 
 	 global $sourcePath, $addScriptName, $licenses;
	 $result = readLicenseFile()."\n";
	 echo "\n\nBilding scripts: $outputFile\n";
	 foreach($scripts as $s => $licenseNum) {
	 	$filename = $sourcePath.$s;
	 	$handle = fopen($filename, "rb");
	 	$contents = fread ($handle, filesize ($filename));
	 	// FIXME: ugly hack to remove purchase hint in jasob TRIAL version
	 	//$contents = preg_replace("/\/\*([^\*]|\*[^\/])*\*\/\r\n/", "", $contents);
	 	echo 'Add '.$filename."...\n";
	 	if ($addScriptName) {
	 		$result .= '// '.basename($filename)."\n";
	 	} else {
	 		$result .= $contents."\n\n";
	 	}
	 	$result .= "// under ".$licenses[$licenseNum]."\n";
	 	$result .= $contents."\n\n";	
	 	fclose($handle);
	 }
	 
	 $handle = fopen($outputFile,"wb");
	 echo "Write in output file: ".$outputFile."\n";
	 fwrite($handle, $result);
	 fclose($handle);
	 echo "Done!\n";
 }
 
 /**
  * Returns text of license file.
  */
 function readLicenseFile() {
 	global $mediaWikiLocation;
 	print "\nRead license file...";
 	$filename = $mediaWikiLocation."/maintenance/licenses.txt";
 	$handle = fopen($filename, "rb");
 	$contents = fread ($handle, filesize ($filename));
 	print "done!\n";
 	return $contents;	
 }
 
?>
