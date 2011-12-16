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
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

/**
 * @file
 * @ingroup LinkedData_Tests
 */

require_once 'testcases/TestTSCSourceDefinition.php';
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
        
       
        
        $suite->addTestSuite("TestMapping");
        $suite->addTestSuite("TestSparqlDataspaceRewriter");
        $suite->addTestSuite("TestOntologyBrowserSparql");
        $suite->addTestSuite("TestMappingLanguageAPI");
        $suite->addTestSuite("TestImporter");
        $suite->addTestSuite("TestMetaDataQueryPrinterSuite");
       
        $suite->addTestSuite("TestLODPolicySuite");
        $suite->addTestSuite("TestLODRatingSuite");
        
        return $suite;
    }
}
