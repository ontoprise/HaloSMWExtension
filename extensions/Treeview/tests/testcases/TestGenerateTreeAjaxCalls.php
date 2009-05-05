<?php

class TestGenerateTreeAjaxCalls extends PHPUnit_Framework_TestCase {
	private $wikiurl; 

	function setUp() {
		global $wgServer, $wgScript;

		if (strpos($wgServer, '/localhost.localdomain') !== false)
			$this->wikiurl = str_replace('.localdomain', '', $wgServer);
		$this->wikiurl .= $wgScript.'?action=ajax&rs=smw_treeview_getTree&rsargs[]=';
	}

	function tearDown() {
	}

	function testCallChildren1() {
		$params = array(
			'p' => 'Subsection of',
			's' => 'Märchen',
		);
		$expected = array(
			array('name' => "Grimm", 'link' => "Grimm", 'depth' => 1),
			array('name' => "Grimms Märchen", 'link' => "Grimms_Märchen", 'depth' => 1),
			array('name' => "Wilhelm Hauff", 'link' => "Wilhelm_Hauff", 'depth' => 1),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->checkTreelist($res->treelist, $expected);
	}
	
	function testCallChildrenWithUrlparams() {
		$params = array(
			'p' => 'Subsection of',
			's' => 'Grimm',
			'u' => 'param1=c%26a&param2=cka'
		);
		$expected = array(
			array('name' => "Jacob Grimm", 'link' => "Jacob_Grimm", 'depth' => 1),
			array('name' => "Wilhelm Grimm", 'link' => "Wilhelm_Grimm", 'depth' => 1),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->checkTreelist($res->treelist, $expected);
		
	}

	function testFetchLeaf() {
		$token = uniqid();
		$params = array(
			'p' => 'Subsection of',
			's' => 'Wilhelm Grimm',
			't' => $token,
		);
		$expected = array(
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->assertEquals($res->token, $token);
		$this->assertEquals($res->treelist, null);
	}

	function testFetchNonexistantLeaf() {
		$token = uniqid();
		$params = array(
			'p' => 'Subsection of',
			's' => 'Someother Grimm',
			't' => $token,
		);
		$expected = array(
			array('name' => "Someother Grimm", 'link' => "Someother_Grimm", 'depth' => 1),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->assertEquals($res->token, $token);
		$this->checkTreelist($res->treelist, $expected);
	}

	function testInitOnload() {
		$token = uniqid();
		$params = array(
			'p' => 'Subsection of',
			'i' => 1,
			'z' => 1,
			't' => $token,
		);
		$expected = array(
			array('name' => "Contents", 'link' => "Help:Contents", 'depth' => 1),
			array('name' => "Glossary", 'link' => "Help:Glossary", 'depth' => 2),
			array('name' => "How to configure the tree", 'link' => "Help:How_to_configure_the_tree", 'depth' => 2),
			array('name' => "SMW+ 1.4.3", 'link' => "Help:SMW+_1.4.3", 'depth' => 2),
			array('name' => "Wikimaster", 'link' => "Help:Wikimaster", 'depth' => 2),
			array('name' => "Main Page", 'link' => "Main_Page", 'depth' => 1),
			array('name' => "Märchen", 'link' => "Märchen", 'depth' => 2),
			array('name' => "Grimm", 'link' => "Grimm", 'depth' => 3),
			array('name' => "Jacob Grimm", 'link' => "Jacob_Grimm", 'depth' => 4),
			array('name' => "Wilhelm Grimm", 'link' => "Wilhelm_Grimm", 'depth' => 4),
			array('name' => "Grimms Märchen", 'link' => "Grimms_Märchen", 'depth' => 3),
			array('name' => "Blaues Licht", 'link' => "Blaues_Licht", 'depth' => 4),
			array('name' => "Der Wolf und die 7 Geißlein", 'link' => "Der_Wolf_und_die_7_Geißlein", 'depth' => 4),
			array('name' => "Die 3 Schlangenblätter", 'link' => "Die_3_Schlangenblätter", 'depth' => 4),
			array('name' => "Frau Holle", 'link' => "Frau_Holle", 'depth' => 4),
			array('name' => "Goldmarie", 'link' => "Goldmarie", 'depth' => 5),
			array('name' => "Pechmarie", 'link' => "Pechmarie", 'depth' => 5),
			array('name' => "Hänsel und Gretel", 'link' => "Hänsel_und_Gretel", 'depth' => 4),
			array('name' => "Rapunzel", 'link' => "Rapunzel", 'depth' => 4),
			array('name' => "Schneewittchen", 'link' => "Schneewittchen", 'depth' => 4),
			array('name' => "Waldhaus", 'link' => "Waldhaus", 'depth' => 4),
			array('name' => "Wilhelm Hauff", 'link' => "Wilhelm_Hauff", 'depth' => 3),
			array('name' => "Kleiner Muck", 'link' => "Kleiner_Muck", 'depth' => 4),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->assertEquals($res->token, $token);
		$this->checkTreelist($res->treelist, $expected);
	}


	function testInitOnloadDynamic() {
		$token = uniqid();
		$params = array(
			'p' => 'Subsection of',
			'i' => 1,
			't' => $token,
		);
		$expected = array(
			array('name' => "Contents", 'link' => "Help:Contents", 'depth' => 1),
			array('name' => "Glossary", 'link' => "Help:Glossary", 'depth' => 2),
			array('name' => "How to configure the tree", 'link' => "Help:How_to_configure_the_tree", 'depth' => 2),
			array('name' => "SMW+ 1.4.3", 'link' => "Help:SMW+_1.4.3", 'depth' => 2),
			array('name' => "Wikimaster", 'link' => "Help:Wikimaster", 'depth' => 2),
			array('name' => "Main Page", 'link' => "Main_Page", 'depth' => 1),
			array('name' => "Märchen", 'link' => "Märchen", 'depth' => 2),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->assertEquals($res->token, $token);
		$this->checkTreelist($res->treelist, $expected);
	}

	function testInitOnloadStartAndMaxdepth() {
		$token = uniqid();
		$params = array(
			'p' => 'Subsection of',
			'i' => 1,
			'z' => 1,
			's' => 'Main Page',
			'm' => 2,
			't' => $token,
		);
		$expected = array(
			array('name' => "Märchen", 'link' => "Märchen", 'depth' => 1),
			array('name' => "Grimm", 'link' => "Grimm", 'depth' => 2),
			array('name' => "Jacob Grimm", 'link' => "Jacob_Grimm", 'depth' => 3),
			array('name' => "Wilhelm Grimm", 'link' => "Wilhelm_Grimm", 'depth' => 3),
			array('name' => "Grimms Märchen", 'link' => "Grimms_Märchen", 'depth' => 2),
			array('name' => "Blaues Licht", 'link' => "Blaues_Licht", 'depth' => 3),
			array('name' => "Der Wolf und die 7 Geißlein", 'link' => "Der_Wolf_und_die_7_Geißlein", 'depth' => 3),
			array('name' => "Die 3 Schlangenblätter", 'link' => "Die_3_Schlangenblätter", 'depth' => 3),
			array('name' => "Frau Holle", 'link' => "Frau_Holle", 'depth' => 3),
			array('name' => "Hänsel und Gretel", 'link' => "Hänsel_und_Gretel", 'depth' => 3),
			array('name' => "Rapunzel", 'link' => "Rapunzel", 'depth' => 3),
			array('name' => "Schneewittchen", 'link' => "Schneewittchen", 'depth' => 3),
			array('name' => "Waldhaus", 'link' => "Waldhaus", 'depth' => 3),
			array('name' => "Wilhelm Hauff", 'link' => "Wilhelm_Hauff", 'depth' => 2),
			array('name' => "Kleiner Muck", 'link' => "Kleiner_Muck", 'depth' => 3),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->assertEquals($res->token, $token);
		$this->checkTreelist($res->treelist, $expected);
	}
	
	function testInitOnloadDynamicStart() {
		$token = uniqid();
		$params = array(
			'p' => 'Subsection of',
			'i' => 1,
			's' => 'Märchen',
			't' => $token,
		);
		$expected = array(
			array('name' => "Grimm", 'link' => "Grimm", 'depth' => 1),
			array('name' => "Jacob Grimm", 'link' => "Jacob_Grimm", 'depth' => 2),
			array('name' => "Wilhelm Grimm", 'link' => "Wilhelm_Grimm", 'depth' => 2),
			array('name' => "Grimms Märchen", 'link' => "Grimms_Märchen", 'depth' => 1),
			array('name' => "Blaues Licht", 'link' => "Blaues_Licht", 'depth' => 2),
			array('name' => "Der Wolf und die 7 Geißlein", 'link' => "Der_Wolf_und_die_7_Geißlein", 'depth' => 2),
			array('name' => "Die 3 Schlangenblätter", 'link' => "Die_3_Schlangenblätter", 'depth' => 2),
			array('name' => "Frau Holle", 'link' => "Frau_Holle", 'depth' => 2),
			array('name' => "Hänsel und Gretel", 'link' => "Hänsel_und_Gretel", 'depth' => 2),
			array('name' => "Rapunzel", 'link' => "Rapunzel", 'depth' => 2),
			array('name' => "Schneewittchen", 'link' => "Schneewittchen", 'depth' => 2),
			array('name' => "Waldhaus", 'link' => "Waldhaus", 'depth' => 2),
			array('name' => "Wilhelm Hauff", 'link' => "Wilhelm_Hauff", 'depth' => 1),
			array('name' => "Kleiner Muck", 'link' => "Kleiner_Muck", 'depth' => 2),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->assertEquals($res->token, $token);
		$this->checkTreelist($res->treelist, $expected);
		
	}

	function testInitOnloadSortbyPropertyStartDisplayDynamic() {
		$token = uniqid();
		$params = array(
			'p' => 'Subsection of',
			'i' => 1,
			'd' => 'hasTitle',
			'b' => 'KHM',
			's' => 'Märchen',
			't' => $token,
		);
		$expected = array(
			array('name' => "Gebrüder Grimm", 'link' => "Grimm", 'depth' => 1),
			array('name' => "Jacob Grimm", 'link' => "Jacob_Grimm", 'depth' => 2),
			array('name' => "Wilhelm Grimm", 'link' => "Wilhelm_Grimm", 'depth' => 2),
			array('name' => "Grimms Märchen", 'link' => "Grimms_Märchen", 'depth' => 1),
			array('name' => "Das blaue Licht", 'link' => "Blaues_Licht", 'depth' => 2),
			array('name' => "Rapunzel", 'link' => "Rapunzel", 'depth' => 2),
			array('name' => "Händel und Gretel", 'link' => "Hänsel_und_Gretel", 'depth' => 2),
			array('name' => "Die 3 Schlangenblätter", 'link' => "Die_3_Schlangenblätter", 'depth' => 2),
			array('name' => "Das Waldhaus", 'link' => "Waldhaus", 'depth' => 2),
			array('name' => "Frau Holle", 'link' => "Frau_Holle", 'depth' => 2),
			array('name' => "Der Wolf und die 7 Geißlein", 'link' => "Der_Wolf_und_die_7_Geißlein", 'depth' => 2),
			array('name' => "Schneewittchen", 'link' => "Schneewittchen", 'depth' => 2),
			array('name' => "Wilhelm Hauff", 'link' => "Wilhelm_Hauff", 'depth' => 1),
			array('name' => "Der kleine Muck", 'link' => "Kleiner_Muck", 'depth' => 2),
		);
		$res= $this->callWiki($params);
		$this->assertEquals($res->result, "success");
		$this->assertEquals($res->token, $token);
		$this->checkTreelist($res->treelist, $expected);
	}
	
	/**
	 * This is the function ot for testing but for calling the wiki url and returning
	 * the result. The result is plain text, but in a json format that Javascript and
	 * a php function can turn into a simple object. This is returned.
	 * 
	 * @access private
	 * @param  string $params url parameters
	 * @return object $res standard object with result data
	 */
	private function callWiki($params) {
		$url = $this->wikiurl;
		foreach ($params as $k => $v)
			$url .= $k."%3d".urlencode($v)."%26";
		$fp = fopen($url, "rb");
		if (!$fp) return;
		$res = '';
		while ($r = fgets($fp, 4096))
			$res .= $r;
		fclose($fp);
		return json_decode($res);
	}
	
	/**
	 * Check the result of the returned object treelist variable. This is an
	 * array which elements are simple value objects, derived from a json string.
	 * The same way an associative array must be submited, who's elements are in
	 * the same sequence, so that for each value a string compare can be done.
	 * 
	 * @access private
	 * @param  array(object) $treelist
	 * @param  array(array) $expected
	 */
	private function checkTreelist($treelist, $expected){
		// check, based on treelist array
		for ($i = 0; $i < count($treelist); $i++) {
			$this->assertEquals($treelist[$i]->name, $expected[$i]['name']);
			$this->assertEquals($treelist[$i]->link, $expected[$i]['link']);
			$this->assertEquals($treelist[$i]->depth, $expected[$i]['depth']);
		}
		// reverse check, based on expected
		for ($i = 0; $i < count($expected); $i++) {
			$this->assertEquals($treelist[$i]->name, $expected[$i]['name']);
			$this->assertEquals($treelist[$i]->link, $expected[$i]['link']);
			$this->assertEquals($treelist[$i]->depth, $expected[$i]['depth']);
		}

	}
}
?>
