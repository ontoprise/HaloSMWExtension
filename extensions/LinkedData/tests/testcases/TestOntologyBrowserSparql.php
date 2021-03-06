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

require_once 'extensions/SMWHalo/specials/SMWOntologyBrowser/SMW_OntologyBrowserAjaxAccess.php';

/**
 * This class tests the Ajax functions of the Ontology Browser for retrieving schema
 * information via SPARQL.
 * The triple store must be running for this test.
 *
 * @author thsc
 *
 */
class TestOntologyBrowserSparql extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	protected static $mBaseURI = 'http://www.example.org/smw-lde/';
	protected $mGraph1 = "http://www.example.org/smw-lde/smwGraphs/ToyotaGraph";
	protected $mGraph2 = "http://www.example.org/smw-lde/smwGraphs/VolkswagenGraph";
	protected $mProvGraph;
	protected $mDSIGraph;
    
	// this file is located in the TSC resources directory
	protected $mFilePath = "file://resources/lod_wiki_tests/OntologyBrowserSparql/";
	protected $mGraph1N3 = "ToyotaGraph.n3";
	protected $mGraph2N3 = "VolkswagenGraph.n3";
	protected $mProvGraphN3 = "ProvenanceGraph.n3";
	protected $mDSIGraphN3 = "DataSourceInformationGraph.n3";

	function setUp() {
		$this->mProvGraph = self::$mBaseURI."smwGraphs/ProvenanceGraph";
		$this->mDSIGraph = self::$mBaseURI."smwGraphs/DataSourceInformationGraph";

		$tsa = new TSCTripleStoreAccess();
		$tsa->createGraph($this->mGraph1);
		$tsa->createGraph($this->mGraph2);
		$tsa->createGraph($this->mProvGraph);
		$tsa->createGraph($this->mDSIGraph);
		$tsa->loadFileIntoGraph("{$this->mFilePath}ToyotaGraph.n3", $this->mGraph1, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}VolkswagenGraph.n3", $this->mGraph2, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}ProvenanceGraph.n3", $this->mProvGraph, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}DataSourceInformationGraph.n3", $this->mDSIGraph, "n3");
		$tsa->flushCommands();
			
	}

	function tearDown() {
		$tsa = new TSCTripleStoreAccess();
		$tsa->dropGraph($this->mGraph1);
		$tsa->dropGraph($this->mGraph2);
		$tsa->dropGraph($this->mProvGraph);
		$tsa->dropGraph($this->mDSIGraph);
		$tsa->flushCommands();
			
	}

	/**
	 * Tests retrieving all instances of category "Automobile".
	 */
	function testGetInstances() {
		$wiki = wfMsg("smw_ob_source_wiki");
		$r = smwf_ob_OntologyBrowserAccess("getInstance", "Automobile##80##0", $wiki);
		
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') !== false);
		$this->assertTrue(strpos($r,'superCat="Hybrid"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') === false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') === false);
			
		$r = smwf_ob_OntologyBrowserAccess("getInstance", "Automobile##80##0", "Toyota");
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') !== false);
		$this->assertTrue(strpos($r,'superCat="Hybrid"') !== false);
	//	$this->assertTrue(strpos($r,'superCat="Automobile"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') === false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') === false);
			

		$r = smwf_ob_OntologyBrowserAccess("getInstance", "Automobile##80##0", "Volkswagen");
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') !== false);
		$this->assertTrue(strpos($r,'superCat="Hybrid"') === false);
		//$this->assertTrue(strpos($r,'superCat="Automobile"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') === false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') === false);

		$r = smwf_ob_OntologyBrowserAccess("getInstance", "Automobile##80##0", "$wiki,Volkswagen,Toyota");
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') !== false);
		$this->assertTrue(strpos($r,'superCat="Hybrid"') !== false);
		//$this->assertTrue(strpos($r,'superCat="Automobile"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') !== false);

	}

	/**
	 * Tests retrieving all annotations of instances.
	 */
	function testGetAnnotations() {
		$wiki = wfMsg("smw_ob_source_wiki");
		$prefix = "http://mywiki/a/";

		// Check the content of the wiki
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Prius##80##0", $wiki);
	
		$this->assertTrue(strpos($r,'title_url="HasPower" title="HasPower"') !== false);
		$this->assertTrue(strpos($r,'<![CDATA[136]]>') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Prius-II##80##0", $wiki);
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Prius-III##80##0", $wiki);
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);
			
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Golf-I##80##0", $wiki);
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);
			
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Golf-VI##80##0", $wiki);
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);
			
		// Check the content of source "Toyota"
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius##80##0", "Toyota");
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius-II##80##0", "Toyota");
		$this->assertTrue(strpos($r,'title_url="HasPower" title="HasPower"') !== false);
		$this->assertTrue(strpos($r,'<![CDATA[113]]>') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius-III##80##0", "Toyota");
		$this->assertTrue(strpos($r,'title_url="HasPower" title="HasPower"') !== false);
		$this->assertTrue(strpos($r,'<![CDATA[136]]>') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Golf-I##80##0", "Toyota");
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);
			
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Golf-VI##80##0", "Toyota");
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);
			
		// Check the content of source "Volkswagen"
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius##80##0", "Volkswagen");
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius-II##80##0", "Volkswagen");
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);
			
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius-III##80##0", "Volkswagen");
		$this->assertTrue(strpos($r,'<annotationsList isEmpty="true"') !== false);
			
		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Golf-I##80##0", "Volkswagen");
		$this->assertTrue(strpos($r,'title_url="HasPower" title="HasPower"') !== false);
		$this->assertTrue(strpos($r,'<![CDATA[50]]>') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Golf-VI##80##0", "Volkswagen");
		$this->assertTrue(strpos($r,'title_url="HasPower" title="HasPower"') !== false);
		$this->assertTrue(strpos($r,'<![CDATA[105]]>') !== false);

	}

	/**
	 * This function tests retrieving all instance with a certain property.
	 */
	function testGetInstancesUsingProperty() {
		$wiki = wfMsg("smw_ob_source_wiki");
		$r = smwf_ob_OntologyBrowserAccess("getInstancesUsingProperty", "HasPower##80##0", $wiki);
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') === false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') === false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') === false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') === false);

		$r = smwf_ob_OntologyBrowserAccess("getInstancesUsingProperty", "HasPower##80##0", "Toyota");
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') === false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') === false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') === false);
			
		$r = smwf_ob_OntologyBrowserAccess("getInstancesUsingProperty", "HasPower##80##0", "Volkswagen");
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') === false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') === false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') === false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') !== false);
			
		$r = smwf_ob_OntologyBrowserAccess("getInstancesUsingProperty", "HasPower##80##0", "$wiki,Volkswagen,Toyota");
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') !== false);

	}

	/**
	 * This function tests retrieving all categories for a certain instance.
	 */
	function testGetCategoryForInstance() {
		$prefix = "http://mywiki/a/";
		
		$source = wfMsg("smw_ob_source_wiki");
		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", ":Prius##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') !== false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", ":Prius-II##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') === false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') === false);

		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", ":Golf-I##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') === false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') === false);

		$source = "Toyota";
		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", "{$prefix}Prius##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') === false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') === false);

		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", "{$prefix}Prius-II##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') !== false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", "{$prefix}Golf-I##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') === false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') === false);

		$source = "Volkswagen";
		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", "{$prefix}Prius##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') === false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') === false);

		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", "{$prefix}Prius-II##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') === false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') === false);

		$r = smwf_ob_OntologyBrowserAccess("getCategoryForInstance", "{$prefix}Golf-I##80##0", $source);
		$this->assertTrue(strpos($r,'title_url="Automobile" title="Automobile"') !== false);
		$this->assertTrue(strpos($r,'title_url="Hybrid" title="Hybrid"') === false);

	}

	/**
	 * This function tests retrieving all instances that match a given filter.
	 */
	function testFilterBrowse() {
		$source = wfMsg("smw_ob_source_wiki");
		$r = smwf_ob_OntologyBrowserAccess("filterBrowse", "instance##Pri", $source);
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("filterBrowse", "instance##Pri", "Toyota");
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("filterBrowse", "instance##Go", "Volkswagen");
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') !== false);

		$r = smwf_ob_OntologyBrowserAccess("filterBrowse", "instance##-I", "$source,Toyota,Volkswagen");
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') === false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') === false);

		$r = smwf_ob_OntologyBrowserAccess("filterBrowse", "instance##Pri", "$source,Toyota,Volkswagen");
		$this->assertTrue(strpos($r,'title_url="Prius" title="Prius"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-II" title="Prius-II"') !== false);
		$this->assertTrue(strpos($r,'title_url="Prius-III" title="Prius-III"') !== false);
		$this->assertTrue(strpos($r,'title_url="Golf-I" title="Golf-I"') === false);
		$this->assertTrue(strpos($r,'title_url="Golf-VI" title="Golf-VI"') === false);

	}


	function testMetadata() {
		$prefix = "http://mywiki/a/";
		$wiki = wfMsg("smw_ob_source_wiki");

        // Check the content of the wiki
        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Prius-II##80##0", $wiki);
        $this->assertTrue(strpos($r,'property name="_META_SWP2_AUTHORITY"') === false);

        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Prius-II##80##0", $wiki);
        $this->assertTrue(strpos($r,'property name="_META_SWP2_AUTHORITY"') === false);

        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Prius-III##80##0", $wiki);
        $this->assertTrue(strpos($r,'property name="_META_SWP2_AUTHORITY"') === false);
            
        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Golf-I##80##0", $wiki);
        $this->assertTrue(strpos($r,'property name="_META_SWP2_AUTHORITY"') === false);
            
        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "Golf-VI##80##0", $wiki);
        $this->assertTrue(strpos($r,'property name="_META_SWP2_AUTHORITY"') === false);
            
        // Check the content of source "Toyota". It must contain metadata
        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius-II##80##0##(SWP2_AUTHORITY)", "Toyota");
       
        $this->assertTrue(strpos($r,'<property name="SWP2_AUTHORITY">') !== false);
      
        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Prius-III##80##0##(SWP2_AUTHORITY)", "Toyota");
        $this->assertTrue(strpos($r,'<property name="SWP2_AUTHORITY">') !== false);
            
        // Check the content of source "Volkswagen". It must contain metadata
        $r = smwf_ob_OntologyBrowserAccess("getAnnotations", "{$prefix}Golf-VI##80##0##(SWP2_AUTHORITY)", "Volkswagen");
        $this->assertTrue(strpos($r,'<property name="SWP2_AUTHORITY">') !== false);

	}


}
