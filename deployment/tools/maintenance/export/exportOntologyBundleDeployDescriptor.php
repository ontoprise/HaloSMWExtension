<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup DFMaintenance
 *
 * Exports the deploy descriptor for an ontology bundle.
 *
 * @author: Kai K�hn
 */
// termination constants
define('DF_TERMINATION_WITH_FINALIZE', 0);
define('DF_TERMINATION_ERROR', 1);
define('DF_TERMINATION_WITHOUT_FINALIZE', 2);

// get root dir of DF
global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../../");

global $mwrootDir;
$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $rootDir);
$mwrootDir = realpath($rootDir."/..");

require_once($rootDir. '/../maintenance/commandLine.inc' );
require_once($rootDir.'/tools/smwadmin/DF_Tools.php');
require_once( $rootDir.'/io/DF_BundleTools.php');
require_once( $rootDir.'/io/DF_PrintoutStream.php');
require_once($rootDir.'/io/export/DF_DeployUploadExporter.php');
require_once( $rootDir.'/languages/DF_Language.php');
dffInitLanguage();

$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
$dfgOut->start(DF_OUTPUT_TARGET_STDOUT);


for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-b => Bundle to export
	if ($arg == '-b') {
		$bundleToExport = next($argv);
		if ($bundleToExport === false) Tools::exitOnFatalError("No bundle given.");
		$bundleToExport = ucfirst($bundleToExport);
		continue;
	} else if ($arg == '-o') {
		$output = next($argv);
		if ($output === false) Tools::exitOnFatalError("No output file given");
			
		continue;
	} else if ($arg == '-d') {
		$dumpFile = next($argv);
		if ($dumpFile === false) Tools::exitOnFatalError("No dump file given");
			
		continue;
	} else if ($arg == '--stripname') {
        $stripBundlename = next($argv);
        if ($stripBundlename === false) Tools::exitOnFatalError("No bundle name given");
            
        continue;
    } else if (strpos($arg, '--includeInstances') === 0) {
		list($option, $value) = explode("=", $arg);
		if (!isset($value))  $value = next($argv);
		$includeInstances = ($value == 'true' || $value == '1' || $value == 'yes');
		continue;
	} else if (strpos($arg, '--includeImages') === 0) {

		list($option, $value) = explode("=", $arg);
		if (!isset($value)) $value = next($argv);
		$includeImages = ($value == 'true' || $value == '1' || $value == 'yes');
			
		continue;
	}
}


if (isset($stripBundlename)) {
	
	$title = preg_replace("/[^A-Z_a-z\\-\\.0-9]/", " ", $stripBundlename);
	$title = trim($title);
	if(strlen($title) == 0 || preg_match("/\w/", $title[0]) == 0) {
		// illegal start character for XML prefixes
		$title = "_".$title;
	}
	$title = preg_replace("/\s/", "_", $title);
	echo $title;
	die();
}

if (!isset($bundleToExport)) {
	echo "\nUsage: php exportOntologyBundleDeployDescriptor.php -b <bundlename> -o <outputdir> -d <dumpfile>\n";
	die();
}

// check bundle page
$bundlePage = Title::newFromText($bundleToExport, NS_MAIN);
if (!$bundlePage->exists()) {
	Tools::exitOnFatalError("\n\n".$bundlePage->getText()." does not exist. Please create first.\n");
}

// check if relevant package properties exist
if (DFBundleTools::checkBundleProperties($dfgOut) === false) {
	Tools::exitOnFatalError("\n\nCorrect the errors and try again!\n");
}

dumpDescriptor($bundleToExport, $output, $dumpFile);

function dumpDescriptor($bundeID, $output = "deploy.xml", $dumpFile = "dump.xml") {
	global $dfgLang, $includeInstances, $includeImages;
	$dependencies_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_dependencies'));

	$instdir_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_instdir'));
	$ontologyversion_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_ontologyversion'));
	$patchlevel_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_patchlevel'));
	$rationale_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_rationale'));
	$maintainer_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_maintainer'));
	$vendor_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_vendor'));
	$helpURL_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_helpurl'));
	$license_p = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_license'));

	$bundleTitle = Title::newFromText($bundeID);
	$bundlePageDi = SMWDIWikiPage::newFromTitle($bundleTitle);
	$dependencies = smwfGetStore()->getPropertyValues($bundlePageDi, $dependencies_p);
	$version = smwfGetStore()->getPropertyValues($bundlePageDi, $ontologyversion_p);
	$patchlevel = smwfGetStore()->getPropertyValues($bundlePageDi, $patchlevel_p);
	$instdir = smwfGetStore()->getPropertyValues($bundlePageDi, $instdir_p);
	$rationale = smwfGetStore()->getPropertyValues($bundlePageDi, $rationale_p);
	$maintainer = smwfGetStore()->getPropertyValues($bundlePageDi, $maintainer_p);
	$vendor = smwfGetStore()->getPropertyValues($bundlePageDi, $vendor_p);
	$helpurl = smwfGetStore()->getPropertyValues($bundlePageDi, $helpURL_p);
	$license = smwfGetStore()->getPropertyValues($bundlePageDi, $license_p);

	if ( count($version) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_ontologyversion')."]] annotation on $bundeID" . "\n" );
	}
	if ( count($version) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_patchlevel')."]] annotation on $bundeID" . "\n" );
	}
	if ( count($vendor) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_vendor')."]] annotation on $bundeID" . "\n" );
	}
	if ( count($instdir) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_instdir')."]] annotation on $bundeID" . "\n" );
	}
	if ( count($rationale) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_rationale')."]] annotation on $bundeID" . "\n" );
	}
	if ( count($maintainer) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_maintainer')."]] annotation on $bundeID" . "\n" );
	}
	if ( count($helpurl) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_helpurl')."]] annotation on $bundeID" . "\n" );
	}
	if ( count($license) == 0) {
		fwrite( STDERR , "No [[".$dfgLang->getLanguageString('df_license')."]] annotation on $bundeID" . "\n" );
	}


	$versionText = count($version) > 0 ? reset($version)->getString() : "1.0.0";
	$patchlevelText = count($patchlevel) > 0 ? reset($patchlevel)->getNumber() : "0";
	$vendorText = count($vendor) > 0 ? reset($vendor)->getString() : "no vendor";
	$instdirText = count($instdir) > 0 ? reset($instdir)->getString() : "extensions/$bundeID";
	$rationaleText = count($rationale) > 0 ? reset($rationale)->getString() : "no description";
	$maintainerText = count($maintainer) > 0 ? reset($maintainer)->getString() : "no maintainer";
	$helpurlText = count($helpurl) > 0 ? reset($helpurl)->getURI() : "no help url";
	$licenseText = count($license) > 0 ? reset($license)->getTitle()->getPrefixedText() : "no license specified";

	$handle = fopen("$output", "w");
	$src = dirname(__FILE__)."/../../../";
	$dest = dirname($output);
	$options['used'] = true;
	$options['shared'] = true;
	$options['includeInstances'] = $includeInstances;
	$options['includeImages'] = $includeImages;

	$uploadExporter = new DeployUploadExporter( $options, $bundeID, $handle, $src, $dest );

	$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
	$xml .= '<deploydescriptor>'."\n";
	$xml .= "\t".'<global>'."\n";
	$xml .= "\t\t".'<version>'.$versionText.'</version>'."\n";
	$xml .= "\t\t".'<patchlevel>'.$patchlevelText.'</patchlevel>'."\n";
	$xml .= "\t\t".'<id>'.$bundeID.'</id>'."\n";
	$xml .= "\t\t".'<instdir>'.$instdirText.'</instdir>'."\n";
	$xml .= "\t\t".'<vendor>'.$vendorText.'</vendor>'."\n";
	$xml .= "\t\t".'<description>'.$rationaleText.'</description>'."\n";
	$xml .= "\t\t".'<maintainer>'.$maintainerText.'</maintainer>'."\n";
	$xml .= "\t\t".'<helpurl>'.$helpurlText.'</helpurl>'."\n";
	$xml .= "\t\t".'<license>'.$licenseText.'</license>'."\n";
	$xml .= "\t\t".'<dependencies>'."\n";
	foreach($dependencies as $dep) {
		$sd = $dep->getSemanticData();

		// id must be there
		$bundleID = NULL;
		$minversion = false;
		$maxversion = false;
		$properties = $sd->getProperties();
		$isoptional = false;
		foreach($properties as $p) {
			switch($p->getKey()) {
				case $dfgLang->getLanguageString('df_mwextension'):
					$bundleIDDi = $sd->getPropertyValues(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_mwextension')));
					$bundleID = reset($bundleIDDi)->getString();
					break;
				case $dfgLang->getLanguageString('df_minversion'):
					$minversionDi = $sd->getPropertyValues(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_minversion')));
					$minversion = reset($minversionDi)->getString();
					break;
				case $dfgLang->getLanguageString('df_maxversion'):
					$maxversionDi = $sd->getPropertyValues(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_maxversion')));
					$maxversion = reset($maxversionDi)->getString();
					break;
				case $dfgLang->getLanguageString('df_isoptional'):
                    $isoptionalDi = $sd->getPropertyValues(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_isoptional')));
                    $isoptional = reset($isoptionalDi)->getBoolean();
                    break;
			}
		}
		if (is_null($bundleID)) {
			Tools::exitOnFatalError("\n\nDependency annotation lacks bundle ID. It is mandatory.\n");
		}

		$minversion = $minversion !== false ? 'from="'.$minversion.'"' : "";
		$maxversion = $maxversion !== false ? 'to="'.$maxversion.'"' : "";
		$isoptional = $isoptional !== false ? 'optional="true"' : "";
		
		$bundleID = str_replace(",","|", $bundleID);
		$attr = trim("$minversion $maxversion $isoptional");
		$xml .= "\t\t\t<dependency $attr>$bundleID</dependency>\n";
	}
	$xml .= "\t".'</dependencies>'."\n";
	
	$xml .= "\t".'</global>'."\n";
	$xml .= "\t".'<wikidumps>'."\n";
	$xml .= "\t\t".'<file loc="'.$dumpFile.'"/>'."\n";
	$xml .= "\t".'</wikidumps>'."\n";
	$xml .= "\t".'<resources>'."\n";
	fwrite($handle, $xml);
	$uploadExporter->run();
	$xml = "\t".'</resources>'."\n";
	$xml .= '</deploydescriptor>'."\n";
	fwrite($handle, $xml);
	fclose($handle);
}


