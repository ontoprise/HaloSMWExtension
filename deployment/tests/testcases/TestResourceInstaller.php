<?php 

define('LOD_NS_MAPPING', 250);

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once $rootDir.'/descriptor/DF_DeployDescriptor.php';
require_once $rootDir.'/tools/smwadmin/DF_ResourceInstaller.php';
require_once ($rootDir.'/io/DF_PrintoutStream.php');

/**
 * Tests the resource installer
 *
 */
class TestResourceInstaller extends PHPUnit_Framework_TestCase {
	var $ddp;
	var $ri;
	
	
    function setUp() {
    	   global $dfgOut;
        $dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
        $xml = file_get_contents('testcases/resources/test_deploy_variables.xml');
        $this->ddp = new DeployDescriptor($xml);
        $path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer/" : "testcases/resources/installer/";
        $this->ri = ResourceInstaller::getInstance(realpath($path));
    }
    
    public function testInstallMappings() {
    	$importedMappings = $this->ri->installOrUpdateMappings($this->ddp, true);
    
    	list($source, $target, $content) = $importedMappings[0];
    	$this->assertEquals("dbpedia", $source);
    	list($source, $target, $content) = $importedMappings[1];
        $this->assertEquals("freebase", $source);
    }
    
    //TODO: add tests for other functionality
}