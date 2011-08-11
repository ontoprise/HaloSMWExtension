<?php

global $smwgHaloIP;
require_once($smwgHaloIP.'/specials/SMWQueryInterface/SMW_QIAjaxAccess.php');

/**
 *
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the ajax methods of the Query interface
 * @author Stephan Robotta
 */
class TestQIAjaxAccess extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

    /**
     * Test the getNumericTypes function
     * result looks like:
     * boolean,number,temperature,date
     */
	function testGetNumericTypes() {
        $exp_val = "boolean,number,temperature,date";
		$res = smwf_qi_QIAccess('getNumericTypes', 'dummy');
        $this->assertEquals($res, $exp_val, 'numeric types mismatch');
	}


    /**
     * Test a non existent property in the wiki. It must be handeled as if it
     * were a page property
     * XML result looks like:
     * <relationSchema name="Some_Property" arity="2"><param name="Page" type="_wpg"/></relationSchema>
     */
	function testGetNonexistentProperty() {
		$res = smwf_qi_QIAccess('getPropertyInformation', 'Some_Property');
        $doc = $this->getDomtree($res);
        $this->checkBasicNodeElements($doc, 'Some_Property', 2, 'Page', '_wpg');
	}

    /**
     * Test an existent page property in the wiki.
     * XML result looks like:
     * <relationSchema name="Part of" arity="2"><param name="Page" type="_wpg"/></relationSchema>
     */
  	function testGetExistentPageProperty() {
		$res = smwf_qi_QIAccess('getPropertyInformation', 'Part of');
        $doc = $this->getDomtree($res);
        $this->checkBasicNodeElements($doc, 'Part of', 2, 'Page', '_wpg');
	}

    /**
     * Test an existent numeric property in the wiki.
     * XML result looks like:
     * <relationSchema name="Has max cardinality" arity="2"><param name="Number" type="_num"/></relationSchema>
     */
  	function testGetNumericProperty() {
		$res = smwf_qi_QIAccess('getPropertyInformation', 'Has max cardinality');
        $doc = $this->getDomtree($res);
        $this->checkBasicNodeElements($doc, 'Has max cardinality', 2, 'Number', '_num');
	}

    /**
     * Test an existent custom type property in the wiki.
     * XML result looks like:
     * <relationSchema name="Torsional moment" arity="2"><param name="Force" type="Force"/></relationSchema>
     */
  	function testGetCustomTypeProperty() {
		$res = smwf_qi_QIAccess('getPropertyInformation', 'Torsional moment');
        $doc = $this->getDomtree($res);
        $this->checkBasicNodeElements($doc, 'Torsional moment', 2, 'Force', 'Force');
        $unit = $doc->getElementsByTagName('param')->item(0)->firstChild;
        $this->assertEquals($unit->nodeName, 'unit');
        $this->assertEquals($unit->getAttribute('label'), 'N');
        $unit = $unit->nextSibling;
        $this->assertEquals($unit->nodeName, 'unit');
        $this->assertEquals($unit->getAttribute('label'), 'kg*m/s²');

	}

    /**
     * Test an existent property that has a range defined.
     * XML result looks like:
     * <relationSchema name="Has Engine" arity="2"><param name="Page" type="_wpg" range="Category:Engine"/></relationSchema>
     */
  	function testGetPropertyWithRange() {
		$res = smwf_qi_QIAccess('getPropertyInformation', 'Has Engine');
        $doc = $this->getDomtree($res);
        $this->checkBasicNodeElements($doc, 'Has Engine', 2, 'Page', '_wpg', 'Category:Engine');
	}

    /**
     * Test an existent nary property in the wiki.
     * XML result looks like:
     * <relationSchema name="Has domain and range" arity="3"><param name="Page" type="_wpg"></param><param name="Page" type="_wpg"></param></relationSchema>
     */
  	function testGetNaryProperty() {
		$res = smwf_qi_QIAccess('getPropertyInformation', 'Has domain and range');
        $doc = $this->getDomtree($res);
        $this->checkBasicNodeElements($doc, 'Has domain and range', 3, 'Page', '_wpg');
	}

    /**
     * Test an existent nary property in the wiki.
     * XML result looks like:
     * <relationSchema name="Has domain and range" arity="3"><param name="Page" type="_wpg"></param><param name="Page" type="_wpg"></param></relationSchema>
     */
  	function testGetEnumerationProperty() {
		$res = smwf_qi_QIAccess('getPropertyInformation', 'Has rating');
        $doc = $this->getDomtree($res);
        /*
        $this->checkBasicNodeElements($doc, 'Has rating', 2, 'Number', '_num');
        $relationSchema = $doc->getElementsByTagName('relationSchema')->item(0);
        $allowedValue = $relationSchema->getElementsByTagName('allowedValue');
        $this->assertEquals($allowedValue->item(0)->getAttribute('value'), '-1');
        $this->assertEquals($allowedValue->item(1)->getAttribute('value'), '0');
        $this->assertEquals($allowedValue->item(2)->getAttribute('value'), '1');
         */
	}

    /**
     * getPropertyTypes encapsulates various getPropertyInformation calls within one
     * call. The <relationSchema> elements for each property are encapsulated into one
     * <propertyTypes> element.
     */
    function testGetPropertyTypes() {
		$res = smwf_qi_QIAccess('getPropertyTypes', 'Part of', 'Has domain and range', 'Has min cardinality');
        $doc = $this->getDomtree($res);
        // test the first child <relationSchema> inside the <propertyTypes>. remove it afterwards from the document
        $this->checkBasicNodeElements($doc, 'Part of', 2, 'Page', '_wpg');
        $partOf = $doc->getElementsByTagName('propertyTypes')->item(0)->firstChild;
        $doc->getElementsByTagName('propertyTypes')->item(0)->removeChild($partOf);
        // and test the next "first" child of the document
        $this->checkBasicNodeElements($doc, 'Has domain and range', 3, 'Page', '_wpg');
        $domAndRange = $doc->getElementsByTagName('propertyTypes')->item(0)->firstChild;
        $doc->getElementsByTagName('propertyTypes')->item(0)->removeChild($domAndRange);
        // and test the next "first" child of the document
        $this->checkBasicNodeElements($doc, 'Has min cardinality', 2, 'Number', '_num');
    }

    /**
     * Takes the XML and returns a DomDocument object
     * @param string $xml
     * @return DOMDocument $doc or null
     */
    private function getDomtree($xml) {
        $doc = new DOMDocument();
        if ($doc->loadXML($xml))
            return $doc;
        else
            $this->assertTrue(false, "no valid xml after function call");
    }
    /**
     * Does basic checks on the DomDocument. These are the name of the returned
     * property, the arity and that the parameter element contains the correct
     * type definitions. For nary properties (Records) the first field is tested
     * only. Finally check that the parameter nodes in the xml result correspond
     * to the arity of the property.
     *
     * @param DOMDocument $doc
     * @param string $propName
     * @param integer $arity
     * @param string $typeName
     * @param string $typeInt
     * @param string $range
     */
    private function checkBasicNodeElements($doc, $propName, $arity, $typeName, $typeInt, $range = "") {
        $relationSchema = $doc->getElementsByTagName('relationSchema')->item(0);
        $this->assertEquals($relationSchema->getAttribute('name'), $propName);
        $this->assertEquals($relationSchema->getAttribute('arity'), "".$arity);
        $this->assertEquals($relationSchema->getElementsByTagName('param')->item(0)->getAttribute('name'), $typeName);
        $this->assertEquals($relationSchema->getElementsByTagName('param')->item(0)->getAttribute('type'), $typeInt);
        $this->assertEquals($relationSchema->getElementsByTagName('param')->item(0)->getAttribute('range'), $range);
        $this->assertEquals($relationSchema->getElementsByTagName('param')->length, $arity - 1);
    }

}

?>
