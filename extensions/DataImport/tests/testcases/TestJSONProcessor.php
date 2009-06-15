<?php

global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_JSONProcessor.php");

/*
 * tests for JSON processing
 */
class TestJSONProcessor extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;

	/*
	*/
	function testJSONProcessor() {
		$jsonProcessor = new JSONProcessor();
		$jsonString = '{'
			.'"object1":"value1",'
			.'"object2":"value2",'
			.'"object3":["arrayValue1", "arrayValue2","arrayValue3"],'
			.'"object4":[{"arrayObject1a":"arrayObjectValue1a","arrayObject1b":"arrayObjectValue1b"},'
			.'{"arrayObject2a":"arrayObjectValue2a","arrayObject2b":"arrayObjectValue2b"},'
			.'{"arrayObject3": ["arrayValue1", "arrayValue2","arrayValue3"]}]}';
		
		$comparisonString = '<JSONRoot>'
							.'<object1><![CDATA[value1]]></object1>'
							.'<object2><![CDATA[value2]]></object2>'
							.'<object3><![CDATA[arrayValue1]]></object3>'
							.'<object3><![CDATA[arrayValue2]]></object3>'
							.'<object3><![CDATA[arrayValue3]]></object3>'
							.'<object4>'
								.'<arrayObject1a><![CDATA[arrayObjectValue1a]]></arrayObject1a>'
								.'<arrayObject1b><![CDATA[arrayObjectValue1b]]></arrayObject1b>'
							.'</object4><object4>'
								.'<arrayObject2a><![CDATA[arrayObjectValue2a]]></arrayObject2a>'
								.'<arrayObject2b><![CDATA[arrayObjectValue2b]]></arrayObject2b>'
							.'</object4><object4>'
								.'<arrayObject3><![CDATA[arrayValue1]]></arrayObject3>'
								.'<arrayObject3><![CDATA[arrayValue2]]></arrayObject3>'
								.'<arrayObject3><![CDATA[arrayValue3]]></arrayObject3>'
							.'</object4>'
						.'</JSONRoot>';
						
		$convertedResult = $jsonProcessor->convertJSON2XML($jsonString);

		$this->assertEquals($convertedResult, $comparisonString);
	}

	}
?>