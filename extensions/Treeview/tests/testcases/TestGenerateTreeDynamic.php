<?php

class TestGenerateTreeDynamic extends PHPUnit_Framework_TestCase {
	
	// the header before the result tree looks similar each time, therefore define here
	// start and end part in variables
	private $retStart = "\x7fdynamic=1&";
	private $retEnd = "&\x7f";

	function setUp() {
	}

	function tearDown() {
	}

	// argument list for generateTree is:
	// $wgParser, $property, $category, $start, $display, $maxDepth, $redirectPage, $level, $condition, $urlparams, $opento

	function testTreeWithoutParams() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeStart() {
		global $wgParser;
		
		$tg = new TreeGenerator;
		$property = 'property=Subsection of';
		$start = utf8_encode('start=Märchen');
  		$res = $tg->generateTree($wgParser, $property, $start, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.utf8_decode($start).$this->retEnd.'*[[Grimm]]
**[[Jacob Grimm]]
**[[Wilhelm Grimm]]
*[[Grimms Märchen]]
**[[Blaues Licht]]
**[[Der Wolf und die 7 Geißlein]]
**[[Die 3 Schlangenblätter]]
**[[Frau Holle]]
**[[Hänsel und Gretel]]
**[[Rapunzel]]
**[[Schneewittchen]]
**[[Waldhaus]]
*[[Wilhelm Hauff]]
**[[Kleiner Muck]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeMaxdepth() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, 'dynamic=true', 'maxDepth=3');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&maxDepth=3'.$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeDisplayTitle() {
		global $wgParser;

		$property = 'property=Subsection of';
		$display = 'display=hasTitle';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $display, 'dynamic=3');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$display.$this->retEnd.'*[[Help:Contents|About “information”]]
**[[Help:How_to_configure_the_tree|Configure Semantic Treeview]]
**[[Help:Glossary|Glossary]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|The \'\'Master\'\' of the \'\'\'Universe\'\'\']]
*[[Main Page]]
**[[Märchen|Märchen]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeCategory() {
		global $wgParser;

		$property = 'property=Subsection of';
		$category = 'category=Content';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'dynamic=3443', $property, $category);
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$category.$this->retEnd.'*[[Märchen]]
**[[Grimm]]
**[[Grimms Märchen]]
**[[Wilhelm Hauff]]
*[[Help:Wikimaster|Wikimaster]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeSubcategory() {
		global $wgParser;

		$property = 'property=Subsection of';
		$category = 'category=Person';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $category, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$category.$this->retEnd.'*[[Jacob Grimm]]
*[[Help:Wikimaster|Wikimaster]]
*[[Wilhelm Grimm]]
*[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeSubcategoryAndLevel() {
		global $wgParser;

		$property = 'property=Subsection of';
		$category = 'category=Person';
		$level = 'level=3';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $category, $level, 'dynamic=2');
  		$res = utf8_decode($res);
  		$expected = $this->retStart.$property.'&'.$category.$this->retEnd.'***[[Jacob Grimm]]
***[[Help:Wikimaster|Wikimaster]]
***[[Wilhelm Grimm]]
***[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeConditionAnd() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$condition = 'condition=[[KHM::+]][[StartsWith::Es war einmal]]';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $condition, 'dynamic=1');
  		$res = utf8_decode($res);
  		$expected = $this->retStart.$property.'&'.$condition.$this->retEnd.'*[[Blaues Licht]]
*[[Der Wolf und die 7 Geißlein]]
*[[Die 3 Schlangenblätter]]
*[[Hänsel und Gretel]]
*[[Rapunzel]]
*[[Schneewittchen]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeConditionOr() {
		global $wgParser;

		$property = 'property=Subsection of';
		$condition = 'condition=[[KHM::+]]OR[[StartsWith::Es war einmal]]';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $condition, 'dynamic=1');
  		$res = utf8_decode($res);
  		$expected = $this->retStart.$property.'&'.$condition.$this->retEnd.'*[[Blaues Licht]]
*[[Der Wolf und die 7 Geißlein]]
*[[Die 3 Schlangenblätter]]
*[[Frau Holle]]
*[[Hänsel und Gretel]]
*[[Rapunzel]]
*[[Schneewittchen]]
*[[Waldhaus]]
';
		$this->assertEquals($expected, $res);
	}
	
	function testTreeConditionSeveralLevels() {
		global $wgParser;

		$property = 'property=Subsection of';
		$condition = 'condition=[[hasTitle::+]]';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $condition, 'dynamic=1');
  		$res = utf8_decode($res);
  		$expected = $this->retStart.$property.'&'.$condition.$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:Wikimaster|Wikimaster]]
*[[Märchen]]
**[[Grimm]]
**[[Grimms Märchen]]
**[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeUrlparams() {
		global $wgParser;
		
		$params = 'test%3DVog%25C3%25A4ssa';
		$property = 'property=Subsection of';
		$category = 'category=Content';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $category, 'urlparams='.$params, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$category.'&urlparams='.urlencode($params).$this->retEnd.'*[[Märchen]]
**[[Grimm]]
**[[Grimms Märchen]]
**[[Wilhelm Hauff]]
*[[Help:Wikimaster|Wikimaster]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeOpenTo() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$opento = utf8_encode('opento=Grimms Märchen');
		$opentoExp = 'opento='.urlencode(utf8_encode('Grimms_Märchen'));
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $opento, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$opentoExp.$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
***[[Grimms Märchen]]
***[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeOpenToDeeper() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$opento = 'opento=Frau Holle';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $opento, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.str_replace(' ', '_', $opento).$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
***[[Grimms Märchen]]
****[[Blaues Licht]]
****[[Der Wolf und die 7 Geißlein]]
****[[Die 3 Schlangenblätter]]
****[[Frau Holle]]
****[[Hänsel und Gretel]]
****[[Rapunzel]]
****[[Schneewittchen]]
****[[Waldhaus]]
***[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeSortbyProperty() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$orderby = 'orderbyProperty=KHM';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $orderby, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$orderby.$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeSortbyPropertyOpenTo() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$opento = 'opento=Frau Holle';
		$orderby = 'orderbyProperty=KHM';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, $opento, $orderby, 'dynamic=1');
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$orderby.'&'.str_replace(' ', '_', $opento).$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
***[[Grimms Märchen]]
****[[Blaues Licht]]
****[[Rapunzel]]
****[[Hänsel und Gretel]]
****[[Die 3 Schlangenblätter]]
****[[Waldhaus]]
****[[Frau Holle]]
****[[Der Wolf und die 7 Geißlein]]
****[[Schneewittchen]]
***[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeCheckNode() {
		global $wgParser;
		
		$property = 'property=Subsection of';
		$checkNode= 'checkNode=1';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, $property, 'dynamic=1', $checkNode);
  		$res = utf8_decode($res);
		$expected = $this->retStart.$property.'&'.$checkNode.$this->retEnd.'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]'."\x7f".'
**[[Help:How_to_configure_the_tree|How to configure the tree]]'."\x7f".'
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]'."\x7f".'
**[[Help:Wikimaster|Wikimaster]]'."\x7f".'
*[[Main Page]]
**[[Märchen]]
';
		$this->assertEquals($expected, $res);
	}


}

?>
