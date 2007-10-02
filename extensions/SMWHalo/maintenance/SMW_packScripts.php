<?php
/*
 * Created on 24.07.2007
 *
 * Author: kai
 *
 * Used to pack javascript files to one big file.
 */

 // license constants
 define('MIT_LICENSE_PROTOTYPE', 1);
 define('MIT_LICENSE_SCRIPTACULOUS', 2);
 define('GPL_LICENSE_ONTOPRISE', 3);
 define('GPL_LICENSE_XTREEVIEW', 4);
 define('WICK_LICENSE', 5);
 define('APACHE_LICENSE', 6);
 define('LGPL_LICENSE', 7);
 define('BSD_LICENSE', 8);
 define('LGPL_LICENSE_TOOLTIPS', 9);

 // license hints
 $licenses = array( MIT_LICENSE_PROTOTYPE => 'MIT-License; Copyright (c) 2005-2007 Sam Stephenson',
 					MIT_LICENSE_SCRIPTACULOUS => 'MIT-License; Copyright (c) 2005, 2006 Thomas Fuchs',
 					GPL_LICENSE_ONTOPRISE => 'GPL-License; Copyright (c) 2007 Ontoprise GmbH',
 					GPL_LICENSE_XTREEVIEW => 'GPL-License; (c) 2003-2004 Jean-Michel Garnier (garnierjm@yahoo.fr)',
 					WICK_LICENSE => 'WICK-License; Copyright (c) 2004, Christopher T. Holland',
 					APACHE_LICENSE => 'Apache-License',
 					LGPL_LICENSE => 'LGPL-License',
 					BSD_LICENSE => 'BSD-License',
 					LGPL_LICENSE_TOOLTIPS => 'LGPL-License; (c) 2002-2007 Walter Zorn (http://www.walterzorn.com)');

 // add script name as hint or not?
 $addScriptName = true;

 $mediaWikiLocation = dirname(__FILE__) . '/..';

 // directory where the scripts are located
 $sourcePath = 'c:/temp/halo_js_scripts/';

 $buildAll = count($argv) == 1; // build all if no parameter is set

 if ($argv[1] == 'smw' || $buildAll) {  // standard scripts which are loaded always (except special pages)
 	 // name of output file
 	 $outputFile = $mediaWikiLocation.'/scripts/deployGeneralScripts.js';

 	 // scripts which will be packed in one JS file (in this order!)
	 $scripts = array(
	 				  'slider.js' => MIT_LICENSE_SCRIPTACULOUS,
	 				  'STB_Framework.js' => GPL_LICENSE_ONTOPRISE,
	 				  'STB_Divcontainer.js' => GPL_LICENSE_ONTOPRISE,
	 				  'wick.js' => WICK_LICENSE,
	 				  'SMW_Help.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_Links.js' => GPL_LICENSE_ONTOPRISE,
	 				  'Annotation.js' => GPL_LICENSE_ONTOPRISE,
	 				  'WikiTextParser.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_Ontology.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_DataTypes.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_GenericToolbarFunctions.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_Container.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_Category.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_Relation.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_Properties.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_Refresh.js' => GPL_LICENSE_ONTOPRISE,
	 				  'SMW_FactboxType.js' => GPL_LICENSE_ONTOPRISE,
	 				  'CombinedSearch.js' => GPL_LICENSE_ONTOPRISE,
	 				  'obSemToolContribution.js' => GPL_LICENSE_ONTOPRISE /*,
	 				  'edit_area_loader.js',
	 				  'SMWEditInterface.js'*/
	 				  );
   	buildScripts($outputFile, $scripts);
 }

 if ($argv[1] == 'OntologyBrowser' || $buildAll) { // scripts which are only loaded on OntologyBrowser Special page
 	$outputFile = $mediaWikiLocation.'/scripts/OntologyBrowser/deployOntologyBrowser.js';

 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array(
 				     'effects.js' => MIT_LICENSE_SCRIPTACULOUS,
					 'treeview.js' => GPL_LICENSE_XTREEVIEW,
					 'treeviewActions.js' => GPL_LICENSE_ONTOPRISE,
					 'treeviewData.js' => GPL_LICENSE_ONTOPRISE);
 	buildScripts($outputFile, $scripts);
 }

 if ($argv[1] == 'Gardening' || $buildAll) { // scripts which are only loaded on OntologyBrowser Special page
 	$outputFile = $mediaWikiLocation.'/scripts/Gardening/deployGardening.js';

 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array(
 					 'effects.js' => MIT_LICENSE_SCRIPTACULOUS,
					 'gardening.js' => GPL_LICENSE_ONTOPRISE);
 	buildScripts($outputFile, $scripts);
 }

 if ($argv[1] == 'QueryInterface' || $buildAll) { // scripts which are only loaded on OntologyBrowser Special page
 	$outputFile = $mediaWikiLocation.'/scripts/QueryInterface/deployQueryInterface.js';

 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array(

 					 'treeviewQI.js' => GPL_LICENSE_XTREEVIEW,
					 'queryTree.js' => GPL_LICENSE_ONTOPRISE,
					 'Query.js' => GPL_LICENSE_ONTOPRISE,
					 'QIHelper.js' => GPL_LICENSE_ONTOPRISE,
					 'qi_tooltip.js' => LGPL_LICENSE_TOOLTIPS);
 	buildScripts($outputFile, $scripts);
 }

 if ($argv[1] == 'General' || $buildAll) { // scripts which are loaded always
 	$outputFile = $mediaWikiLocation.'/scripts/deployGeneralTools.js';

 	// scripts which will be packed in one JS file (in this order!)
 	$scripts = array(
 					 'generalTools.js' => GPL_LICENSE_ONTOPRISE,
 					 'smw_logger.js' => GPL_LICENSE_ONTOPRISE,
 					 'SMW_Language.js' => GPL_LICENSE_ONTOPRISE);

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
