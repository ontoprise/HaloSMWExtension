<?php
global $smwgHaloIP;
require_once($smwgHaloIP.'/includes/SMW_Autocomplete.php');
require_once('TestAutocompletionStore.php');
/**
 *
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the auto-completion storage layer
 * @author Kai Kühn
 */
class TestAutocompletionTSCStore extends TestAutocompletionStore {

	function setUp() {
		global $smwgDefaultStore;
		$smwgDefaultStore='SMWTripleStore';		
	}
}