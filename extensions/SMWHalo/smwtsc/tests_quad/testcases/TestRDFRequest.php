<?php
if (!defined("SMWH_FORCE_TS_UPDATE")) define("SMWH_FORCE_TS_UPDATE","1");
global $tscgIP;
require_once( "$tscgIP/includes/triplestore_client/TSC_RESTWebserviceConnector.php" );

/**
 * Tests the RDF request feature. 
 * 
 * Every page request with the MIME type application/rdf+xml returns the
 * RDF data about the page instead of HTML.
 * 
 * @author kuehn
 *
 */
class TestRDFRequest extends PHPUnit_Framework_TestCase {
	var $con;
	function setUp() {
		$this->con = new RESTWebserviceConnector("localhost", 80, "mediawiki/index.php");
	}

	function tearDown() {

	}

	function testRDFRequest1() {
		 
		list($header, $status, $data) = $this->con->send('', '/Berlin', 'application/rdf+xml' );
		 echo $data;
		$this->assertEquals(200, $status);
		
		$this->assertContains("http://mywiki/a/Berlin", $data);
		$this->assertContains("http://mywiki/a/Germany", $data);
		$this->assertContains("http://mywiki/category/City", $data);

	}

	function testRDFRequest2() {

		list($header, $status, $data) = $this->con->send('', '?title=Berlin', 'application/rdf+xml' );

		$this->assertEquals(200, $status);
		$this->assertContains("http://mywiki/a/Berlin", $data);
		$this->assertContains("http://mywiki/a/Germany", $data);
		$this->assertContains("http://mywiki/category/City", $data);

	}
}