<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

require_once 'testcases/TestLODSourceDefinition.php';
require_once 'testcases/TestTripleStoreAccess.php';
require_once 'testcases/TestMapping.php';
require_once 'testcases/TestSparqlDataspaceRewriter.php';
require_once 'testcases/TestOntologyBrowserSparql.php';
require_once 'testcases/TestMappingLanguageAPI.php';
require_once 'testcases/TestImporter.php';
require_once 'testcases/TestMetaDataQueryPrinter.php';
require_once 'testcases/TestLODRating.php';
require_once 'testcases/TestSparqlParser.php';
require_once 'testcases/TestLODPolicy.php';

class LODTests
{ 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

// IMPORTANT!!
// Ontobroker Quad must be started with the following options:
//   msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 console 
//   reasoner=owl restfulws        
        
        $suite->addTestSuite("TestTripleStoreAccessSuite");
        $suite->addTestSuite("TestLODSourceDefinitionSuite");
        $suite->addTestSuite("TestMapping");
        $suite->addTestSuite("TestSparqlDataspaceRewriter");
        $suite->addTestSuite("TestOntologyBrowserSparql");
        $suite->addTestSuite("TestMappingLanguageAPI");
        $suite->addTestSuite("TestImporter");
        $suite->addTestSuite("TestMetaDataQueryPrinterSuite");
        $suite->addTestSuite("TestSparqlParserSuite");
        $suite->addTestSuite("TestLODPolicySuite");
        $suite->addTestSuite("TestLODRatingSuite");
        
        return $suite;
    }
}