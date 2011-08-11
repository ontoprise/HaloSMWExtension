<?php
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
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
	}

	function testASKPropertyContraint() {
		$res = $this->makeCall("[[Category:Person]][[Height::+]]|?Height", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
		$this->assertContains('175', $res);
		$this->assertContains('cm', $res);
		$this->assertContains('187', $res);
	}

	function testASKPropertyContraint2() {
		$res = $this->makeCall("[[Category:Sports car]][[Has Engine::<q>[[Has torsional moment::+]]</q>]]", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Audi_TT</uri>', $res);
		 
	}

	function testASKPropertyContraint3() {
		$res = $this->makeCall("[[Category:Person]]|?Has Engine", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/3_cylinder</uri>', $res);
	}

	function testASKInstance() {
		$res = $this->makeCall("[[Kai]]|?Height");
		echo $res;
		$this->assertContains('175 cm', $res);
	}

	function testASKInstance2() {
		$res = $this->makeCall("[[Type:Height]]|?Corresponds to", $this->params);
		echo $res;
		$this->assertContains('1 cm', $res);
		$this->assertContains('Zentimeter', $res);
		$this->assertContains('0.01 m', $res);
		$this->assertContains('Meter', $res);
		$this->assertContains('Zoll', $res);
		$this->assertContains('0.3937 inch', $res);
	}

}