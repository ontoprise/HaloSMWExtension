<?php

class TestSCMapping extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = false;
	
	public static function setUpBeforeClass() {
		SCStorage::getDatabase()->deleteDatabaseTables();
		SCStorage::getDatabase()->setup(true);
    }

	function testSaveMappingData() {
		$ret = SCProcessor::saveMappingData ('Wiki Mail', array(
			'Wiki Mail.Wiki Mail.to|Project Bug.Project Bug.debugger',
			'Wiki Mail.Wiki Mail.to|Project Task.Project Task.owner',
			'Wiki Mail.Wiki Mail.sent|Project Bug.Project Bug.report date',
			'Wiki Mail.Wiki Mail.sent|Project Task.Project Task.start date',
		));
		$this->assertEquals($ret, '{success:true, msg:"Your schema mapping is saved successfully."}');
	}
}