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
 * @ingroup SMWHaloTests
 *
 * Tests the external query interface.
 * @author Kai Kühn
 *
 */
abstract class TestEQI extends PHPUnit_Framework_TestCase {

	protected $params;

	protected function makeCall($query, $params = array()) {
		$res = "";
		$header = "";

		// Create a curl handle to a non-existing location
		global $wgServer, $wgScriptPath;

		$url = "$wgServer$wgScriptPath/index.php?action=ajax&rs=smwf_ws_callEQIXML&rsargs[]=".urlencode($query);
		foreach($params as $key => $value) {
			$url .= urlencode("|$key=$value");
		}
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,"");
		$httpHeader = array (
        "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
        "Expect: "
        );
        $httpHeader[] = "Accept: application/sparql-xml";
        curl_setopt($ch,CURLOPT_HTTPHEADER, $httpHeader);
        // if ($this->credentials != '') curl_setopt($ch,CURLOPT_USERPWD,trim($this->credentials));
        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $res = curl_exec($ch);
         
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $res;
	}

	function testASKCategory() {
		$res = $this->makeCall("[[Category:Person]]", $this->params);
		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
	}

	function testASKPropertyContraint() {
		$res = $this->makeCall("[[Category:Person]][[Height::+]]|?Height", $this->params);
		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
		$this->assertContains('175', $res);
		$this->assertContains('187', $res);
	}

	function testASKPropertyContraint2() {
		$res = $this->makeCall("[[Category:Sports car]][[Has Engine::<q>[[Has torsional moment::+]]</q>]]", $this->params);
		$this->assertContains('<uri>http://publicbuild/ob/a/Audi_TT</uri>', $res);
			
	}

	function testASKPropertyContraint3() {
		$res = $this->makeCall("[[Category:Person]]|?Has Engine", $this->params);
		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/3_cylinder</uri>', $res);
	}

	function testASKInstance() {
		$res = $this->makeCall("[[Kai]]|?Height");
		$this->assertContains('175', $res);
	}



}
