<?php

class TestGenerateTree extends PHPUnit_Framework_TestCase {

	function setUp() {
	}

	function tearDown() {
	}

	// argument list for generateTree is:
	// $wgParser, $property, $category, $start, $display, $maxDepth, $redirectPage, $level, $condition, $urlparams, $opento

	function testTreeWithoutParams() {
		global $wgParser;
		
		$tg = new TreeGenerator;
		$property = "Subsection of";
  		$res = $tg->generateTree($wgParser, 'property=Subsection of');
		$expected = '*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
****[[Jacob Grimm]]
****[[Wilhelm Grimm]]
***[[Grimms Märchen]]
****[[Blaues Licht]]
****[[Der Wolf und die 7 Geißlein]]
****[[Die 3 Schlangenblätter]]
****[[Frau Holle]]
*****[[Goldmarie]]
*****[[Pechmarie]]
****[[Hänsel und Gretel]]
****[[Rapunzel]]
****[[Schneewittchen]]
****[[Waldhaus]]
***[[Wilhelm Hauff]]
****[[Kleiner Muck]]
';
		$this->assertEquals($res, $expected);
	}

}
	 

?>
