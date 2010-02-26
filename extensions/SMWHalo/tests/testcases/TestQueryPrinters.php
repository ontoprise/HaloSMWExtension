<?php
/**
 * @file
 * @ingroup SMWHaloTests 
 * 
 * @author Kai Kühn
 *
 */
class TestQueryPrinters extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

	function testXMLQueryPrinter() {
		global $smwgResultFormats, $smwgHaloIP;
		require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";
		$smwgResultFormats['xml'] = 'SMWXMLResultPrinter';
		$params = array();
		$context = SMWQueryProcessor::INLINE_QUERY;
		$format = "xml";
		$extraprintouts = array();
		$querystring = "[[Category:Car]]";
		$query  = SMWQueryProcessor::createQuery($querystring, $params, $context, $format, $extraprintouts);
		$res = smwfGetStore()->getQueryResult($query);
		$result = SMWQueryProcessor::getResultFromQuery($query, $params, $extraprintouts, SMW_OUTPUT_FILE, $context, $format);
		$this->assertFileContentsIgnoringWhitespaces("testcases/resources/xml_qp_result.dat", $result);
		
	}

	function testExcelQueryPrinter() {
	
		$params = array();
		$context = SMWQueryProcessor::INLINE_QUERY;
		$format = "exceltable";
		$extraprintouts = array();
		$querystring = "[[Category:Car]]";
		$query  = SMWQueryProcessor::createQuery($querystring, $params, $context, $format, $extraprintouts);
		$res = smwfGetStore()->getQueryResult($query);
		$result = SMWQueryProcessor::getResultFromQuery($query, $params, $extraprintouts, SMW_OUTPUT_FILE, $context, $format);
		
		$this->assertFileContentsIgnoringWhitespaces("testcases/resources/excel_qp_result.dat", $result);
		
	}
	
	function assertFileContentsIgnoringWhitespaces($file, $act_result) {
		$contents = file_get_contents($file);
		$contents = preg_replace("/\\s+|\\n+|\\r+/", "", $contents);
		$act_result = preg_replace("/\\s+|\\n+|\\r+/", "", $act_result);
		$this->assertEquals($contents, $act_result);
	}
}
