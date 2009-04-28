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
	
	private function checkTreelist($treelist, $expected){
		for ($i = 0; $i < count($treelist); $i++) {
			$this->assertEquals($treelist[$i]->name, $expected[$i]['name']);
			$this->assertEquals($treelist[$i]->link, $expected[$i]['link']);
			$this->assertEquals($treelist[$i]->depth, $expected[$i]['depth']);
		}
	}
}
?>
