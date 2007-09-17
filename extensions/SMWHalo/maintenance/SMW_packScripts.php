<?php
/*
 * Created on 24.07.2007
 *
 * Author: kai
 * 
 * Used to pack javascript files to one big file.
 */
 
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
	 $scripts = array('prototype.js',
	 				  'smw_logger.js',
	 				  'generalTools.js',
	 				  'SMW_Language.js',
	 				  'STB_Framework.js',
	 				  'STB_Divcontainer.js',
	 				  'wick.js',
	 				  'SMW_Help.js',
	 				  'SMW_Links.js',
	 				  'Annotation.js',
	 				  'WikiTextParser.js',
	 				  'SMW_Ontology.js',
	 				  'SMW_DataTypes.js',
	 				  'SMW_GenericToolbarFunctions.js',
	 				  'SMW_Container.js',
	 				  'SMW_Category.js',
	 				  'SMW_Relation.js',
	 				  'SMW_Properties.js',
	 				  'SMW_Refresh.js',
	 				  'SMW_FactboxType.js',
	 				  'CombinedSearch.js',
	 				  'obSemToolContribution.js' /*,
	 				  'edit_area_loader.js',
	 				  'SMWEditInterface.js'*/
	 				  );
   	buildScripts($outputFile, $scripts);			  
 } 
 
 if ($argv[1] == 'OntologyBrowser' || $buildAll) { // scripts which are only loaded on OntologyBrowser Special page
 	$outputFile = $mediaWikiLocation.'/scripts/OntologyBrowser/deployOB.js';
   	 
 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array('prototype.js', 'effects.js', 'generalTools.js', 'SMW_Language.js', 'treeview.js', 'treeviewActions.js', 'treeviewData.js');
 	buildScripts($outputFile, $scripts);
 } 
 
 if ($argv[1] == 'Gardening' || $buildAll) { // scripts which are only loaded on OntologyBrowser Special page
 	$outputFile = $mediaWikiLocation.'/scripts/Gardening/deployGardening.js';
   	 
 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array('prototype.js', 'effects.js', 'generalTools.js', 'SMW_Language.js', 'gardening.js');
 	buildScripts($outputFile, $scripts);
 } 
 
 /**
  * Build one script file consisting of all scripts given in $scripts array.
  */
 function buildScripts($outputFile, $scripts) { 
 	 global $sourcePath, $addScriptName;
	 $result = "";
	 echo "\n\nBilding scripts: $outputFile\n";
	 foreach($scripts as $s) {
	 	$filename = $sourcePath.$s;
	 	$handle = fopen($filename, "rb");
	 	$contents = fread ($handle, filesize ($filename));
	 	// FIXME: ugly hack to remove purchase hint in jasob TRIAL version
	 	//$contents = preg_replace("/\/\*([^\*]|\*[^\/])*\*\/\r\n/", "", $contents);
	 	echo 'Add '.$filename."...\n";
	 	if ($addScriptName) {
	 		$result .= '// '.basename($filename)."\n".$contents."\n\n";	
	 	} else {
	 		$result .= $contents."\n\n";
	 	}
	 	fclose($handle);
	 }
	 
	 $handle = fopen($outputFile,"wb");
	 echo "Write in output file: ".$outputFile."\n";
	 fwrite($handle, $result);
	 fclose($handle);
	 echo "Done!\n";
 }
 
?>
