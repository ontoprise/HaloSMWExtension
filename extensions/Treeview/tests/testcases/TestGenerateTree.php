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
  		$res = $tg->generateTree($wgParser, 'property=Subsection of');
		$expected = '*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
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
		$this->assertEquals($expected, $res);
	}

	function testTreeStart() {
		global $wgParser;
		
		$tg = new TreeGenerator;
		$start = 'start=Märchen';
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', $start);
		$expected = '*[[Grimm]]
**[[Jacob Grimm]]
**[[Wilhelm Grimm]]
*[[Grimms Märchen]]
**[[Blaues Licht]]
**[[Der Wolf und die 7 Geißlein]]
**[[Die 3 Schlangenblätter]]
**[[Frau Holle]]
***[[Goldmarie]]
***[[Pechmarie]]
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
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'maxDepth=3');
		$expected = '*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
****[[Jacob Grimm]]
****[[Wilhelm Grimm]]
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
****[[Kleiner Muck]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeMaxdepthAndRedirect() {
		global $wgParser;
		
		$tg = new TreeGenerator;
		$property = "property=Subsection of";
		$maxDepth = "maxDepth=2";
		$redirect = "redirectPage=Märchen";
  		$res = $tg->generateTree($wgParser, $property, $maxDepth, $redirect);
		$expected = '*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
****[[Märchen|...]]
***[[Grimms Märchen]]
****[[Märchen|...]]
***[[Wilhelm Hauff]]
****[[Märchen|...]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeDisplayTitle() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'display=hasTitle');
		$expected = '*[[Help:Contents|About “information”]]
**[[Help:How_to_configure_the_tree|Configure Semantic Treeview]]
**[[Help:Glossary|Glossary]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|The \'\'Master\'\' of the \'\'\'Universe\'\'\']]
*[[Main Page]]
**[[Märchen|Märchen]]
***[[Grimm|Gebrüder Grimm]]
****[[Jacob_Grimm|Jacob Grimm]]
****[[Wilhelm_Grimm|Wilhelm Grimm]]
***[[Grimms_Märchen|Grimms Märchen]]
****[[Blaues_Licht|Das blaue Licht]]
****[[Waldhaus|Das Waldhaus]]
****[[Der_Wolf_und_die_7_Geißlein|Der Wolf und die 7 Geißlein]]
****[[Die_3_Schlangenblätter|Die 3 Schlangenblätter]]
****[[Frau_Holle|Frau Holle]]
*****[[Goldmarie|Goldmarie]]
*****[[Pechmarie|Pechmarie]]
****[[Hänsel_und_Gretel|Händel und Gretel]]
****[[Rapunzel|Rapunzel]]
****[[Schneewittchen|Schneewittchen]]
***[[Wilhelm_Hauff|Wilhelm Hauff]]
****[[Kleiner_Muck|Der kleine Muck]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeDisplayKHM() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'display=KHM');
		$expected = '*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
****[[Jacob Grimm]]
****[[Wilhelm Grimm]]
***[[Grimms Märchen]]
****[[Blaues_Licht|116]]
****[[Rapunzel|12]]
****[[Hänsel_und_Gretel|15]]
****[[Die_3_Schlangenblätter|16]]
****[[Waldhaus|169]]
****[[Frau_Holle|24]]
*****[[Goldmarie]]
*****[[Pechmarie]]
****[[Der_Wolf_und_die_7_Geißlein|5]]
****[[Schneewittchen|53]]
***[[Wilhelm Hauff]]
****[[Kleiner Muck]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeCategory() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'category=Content');
		$expected = '*[[Märchen]]
**[[Grimm]]
***[[Jacob Grimm]]
***[[Wilhelm Grimm]]
**[[Grimms Märchen]]
***[[Blaues Licht]]
***[[Der Wolf und die 7 Geißlein]]
***[[Die 3 Schlangenblätter]]
***[[Frau Holle]]
****[[Goldmarie]]
****[[Pechmarie]]
***[[Hänsel und Gretel]]
***[[Rapunzel]]
***[[Schneewittchen]]
***[[Waldhaus]]
**[[Wilhelm Hauff]]
***[[Kleiner Muck]]
*[[Help:Wikimaster|Wikimaster]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeSubcategory() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'category=Person');
		$expected = '*[[Jacob Grimm]]
*[[Help:Wikimaster|Wikimaster]]
*[[Wilhelm Grimm]]
*[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeSubcategoryAndLevel() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'category=Person', 'level=3');
		$expected = '***[[Jacob Grimm]]
***[[Help:Wikimaster|Wikimaster]]
***[[Wilhelm Grimm]]
***[[Wilhelm Hauff]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeConditionAnd() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'condition=[[KHM::+]][[StartsWith::Es war einmal]]');
		$expected = '*[[Blaues Licht]]
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
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'condition=[[KHM::+]]OR[[StartsWith::Es war einmal]]');
		$expected = '*[[Blaues Licht]]
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

	function testTreeConditionNSlink() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'condition=[[isWikimaster::+]]');
		$expected = '*[[Help:Wikimaster|Wikimaster]]
';
		$this->assertEquals($expected, $res);
	}
 
	function testTreeLevel() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'category=Content', 'level=2');
		$expected = '**[[Märchen]]
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
**[[Help:Wikimaster|Wikimaster]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeUrlparams() {
		global $wgParser;
		
		$tg = new TreeGenerator;
		$params = 'test%3DVog%25C3%25A4ssa';
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'category=Content', 'urlparams='.$params);
		$expected = "\x7f".'urlparams='.urlencode($params).'&'."\x7f".'*[[Märchen]]
**[[Grimm]]
***[[Jacob Grimm]]
***[[Wilhelm Grimm]]
**[[Grimms Märchen]]
***[[Blaues Licht]]
***[[Der Wolf und die 7 Geißlein]]
***[[Die 3 Schlangenblätter]]
***[[Frau Holle]]
****[[Goldmarie]]
****[[Pechmarie]]
***[[Hänsel und Gretel]]
***[[Rapunzel]]
***[[Schneewittchen]]
***[[Waldhaus]]
**[[Wilhelm Hauff]]
***[[Kleiner Muck]]
*[[Help:Wikimaster|Wikimaster]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeUrlparamsWithAmpersand() {
		global $wgParser;
		
		$tg = new TreeGenerator;
		$params = 'param1%3Dc%2526m%26param2%3Dcka';
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'start=Help:Contents', 'urlparams='.$params);
		$expected = "\x7f".'urlparams='.urlencode($params).'&'."\x7f".'*[[Help:Glossary|Glossary]]
*[[Help:How_to_configure_the_tree|How to configure the tree]]
*[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
*[[Help:Wikimaster|Wikimaster]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeInvalidStart() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'start=Contents');
		$expected = '*[[Contents]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeOpento() {
		global $wgParser;
		
		$opento = 'opento=Grimms Märchen';
		$opentoExp = 'opento='.urlencode('Grimms_Märchen');
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', $opento);
		$expected = "\x7f".$opentoExp.'&'."\x7f".'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
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
		$this->assertEquals($expected, $res);
	}

	function testTreeOpentoPageWithNsPrefix() {
		global $wgParser;
		
		$opento = 'opento=Help:How to configure the tree';
		$opentoExp = 'opento=Help%3AHow_to_configure_the_tree';
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', $opento);
		$expected = "\x7f".$opentoExp.'&'."\x7f".'*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
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
		$this->assertEquals($expected, $res);
	}

	function testTreeSortbyProperty() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'orderbyProperty=KHM');
		$expected = '*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
**[[Märchen]]
***[[Grimm]]
****[[Jacob Grimm]]
****[[Wilhelm Grimm]]
***[[Grimms Märchen]]
****[[Blaues Licht]]
****[[Rapunzel]]
****[[Hänsel und Gretel]]
****[[Die 3 Schlangenblätter]]
****[[Waldhaus]]
****[[Frau Holle]]
*****[[Goldmarie]]
*****[[Pechmarie]]
****[[Der Wolf und die 7 Geißlein]]
****[[Schneewittchen]]
***[[Wilhelm Hauff]]
****[[Kleiner Muck]]
';
		$this->assertEquals($expected, $res);
	}

	function testTreeCheckNode() {
		global $wgParser;
		
		$tg = new TreeGenerator;
  		$res = $tg->generateTree($wgParser, 'property=Subsection of', 'checkNode=1');
		$expected = '*[[Help:Contents|Contents]]
**[[Help:Glossary|Glossary]]
**[[Help:How_to_configure_the_tree|How to configure the tree]]
**[[Help:SMW+_1.4.3|SMW+ 1.4.3]]
**[[Help:Wikimaster|Wikimaster]]
*[[Main Page]]
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
		$this->assertEquals($expected, $res);
	}

}

?>
