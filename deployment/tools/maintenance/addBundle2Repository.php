<?php 
/**
 * @file
 * @ingroup DFMaintenance
 * 
 * Adds a bundle to a repository XML file. 
 * 
 * Usage: TODO 
 *          
 * @author: Kai Kühn / ontoprise / 2011
 * 
 */

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");


$latest = false;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

    //-r => repository directory
    if ($arg == '-r') {
        $repositoryDir = next($argv);
        continue;
    }
    
	// -b => bundle file or directory containing bundles
    if ($arg == '-r') {
        $bundlePath = next($argv);
        continue;
    }
    
    // --url => base URL for downloads
	if ($arg == '--url') {
        $repositoryURL = next($argv);
        continue;
    }
}

// read bundles and extract the deploy descriptors

// load existing repository

// make according changes
// 1. create extensions substructure
// 2. Add to repository.xml

// save repository.xml


function loadRepository($filePath) {
	$xml = file_get_contents($filePath);
	return DOMDocument::loadXML($xml);
}

function extractDeployDescriptors($bundlePath) {
	$tmpFolder = Tools::isWindows() ? 'c:\temp\mw_deploy_tool' : '/tmp/mw_deploy_tool';
	if (is_dir($bundlePath)) {
				
	} else {
		$dd = Tools::unzipDeployDescriptor($bundlePath, $tmpFolder);
		return array($dd);
	}
}



