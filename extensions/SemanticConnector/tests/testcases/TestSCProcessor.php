<?php

class TestSCProcessor extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = false;
	
	function testGetTemplateField() {
		$ret = SCProcessor::getTemplateField ('Wiki Mail');
		$acceptable = array ( 'Wiki Mail' => array (
			'subject', 'from', 'to', 'cc', 'sent', 'attachment'
        ) );
        $this->assertEquals($ret, $acceptable);
	}

	function testGetAllFTFs() {
		// FIXME: TBD !!!
		$ret = SCProcessor::getAllFTFs ();
		$acceptable = $ret;
		$this->assertEquals($ret, $acceptable);
	}
}
?>