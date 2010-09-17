<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestLODSourceDefinition.php';
require_once 'testcases/TestTripleStoreAccess.php';
require_once 'testcases/TestMapping.php';
require_once 'testcases/TestSparqlDataspaceRewriter.php';
require_once 'testcases/TestOntologyBrowserSparql.php';
require_once 'testcases/TestMappingLanguageAPI.php';
require_once 'testcases/TestImporter.php';
require_once 'testcases/TestNonExistingPageHandler.php';

class LODTests
{ 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

// IMPORTANT!!
// Ontobroker Quad must be started with the following options:
//   msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 console 
//   run=D:\MediaWiki\SMWTripleStore\resources\lod_wiki_tests\OntologyBrowserSparql\initDebug.sparul 
//   reasoner=owl restfulws        

        $suite->addTestSuite("TestTripleStoreAccess");
        $suite->addTestSuite("TestLODSourceDefinition");
        $suite->addTestSuite("TestMapping");
        $suite->addTestSuite("TestSparqlDataspaceRewriter");
        $suite->addTestSuite("TestOntologyBrowserSparql");
        $suite->addTestSuite("TestMappingLanguageAPI");
        $suite->addTestSuite("TestImporter");
        $suite->addTestSuite("TestNonExistinPageSuite");
        
        return $suite;
    }
}