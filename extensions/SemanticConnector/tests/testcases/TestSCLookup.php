<?php

class TestSCLookup extends PHPUnit_Framework_TestCase {
    protected $backupGlobals = false;
    
    function testGetPossibleForms() {
		$ret = SCProcessor::getPossibleForms (array('Wiki Mail'));
		$acceptable = array('Wiki Mail', 'Project Bug', 'Project Task');
		$this->assertEquals(sort($ret), sort($acceptable));
    }

	function testGetMappingData_1() {
		$ret = SCProcessor::getMappingData ('Wiki Mail');
		$acceptable = '{
        source:{
                form:"Wiki Mail",
                templates: [
                {id:"Wiki Mail", fields:[{id:"subject"},{id:"from"},{id:"to"},{id:"cc"},{id:"sent"},{id:"attachment"},]},
                ],
                mappedData: [{
                                src:"Wiki Mail.Wiki Mail.to",
                                map:"Project Bug.Project Bug.debugger"
                        },{
                                src:"Wiki Mail.Wiki Mail.to",
                                map:"Project Task.Project Task.owner"
                        },{
                                src:"Wiki Mail.Wiki Mail.sent",
                                map:"Project Bug.Project Bug.report date"
                        },{
                                src:"Wiki Mail.Wiki Mail.sent",
                                map:"Project Task.Project Task.start date"
                        },
                ]
        },
        mapping:{
                forms: [
                	{id:"Project Bug", templates:[{id:"Project Bug", fields:[{id:"story"},{id:"reporter"},{id:"report date"},{id:"sprint output"},{id:"priority"},{id:"debugger"},{id:"fix date"},{id:"status"},{id:"attachment"},]}, ]}, 
                	{id:"Project Task", templates:[{id:"Project Task", fields:[{id:"story"},{id:"owner"},{id:"start date"},{id:"end date"},{id:"status"},{id:"description"},]}, ]}, 
                	{id:"Wiki Mail", templates:[{id:"Wiki Mail", fields:[{id:"subject"},{id:"from"},{id:"to"},{id:"cc"},{id:"sent"},{id:"attachment"},]}, ]}, 
                ]
        }
}';
		
		// mapping returns all templates in server, may cause error in other test cases
		$ps = explode("mapping:{", $ret, 2);
		$ret = preg_replace('/\s*\n\s*/', '', $ps[0]);
		$ps = explode("mapping:{", $acceptable, 2);
		$acceptable = preg_replace('/\s*\n\s*/', '', $ps[0]);
		
		$this->assertEquals($ret, $acceptable);
    }
    
    function testGetMappingData_2() {
		$ret = SCProcessor::getMappingData ('Project Bug');
		$acceptable = '{
        source:{
                form:"Project Bug",
                templates: [
                {id:"Project Bug", fields:[{id:"story"},{id:"reporter"},{id:"report date"},{id:"sprint output"},{id:"priority"},{id:"debugger"},{id:"fix date"},{id:"status"},{id:"attachment"},]},
                ],
                mappedData: [{
                                src:"Project Bug.Project Bug.debugger",
                                map:"Wiki Mail.Wiki Mail.to"
                        },{
                                src:"Project Bug.Project Bug.report date",
                                map:"Wiki Mail.Wiki Mail.sent"
                        },
                ]
        },
        mapping:{
                forms: [
                	{id:"Project Bug", templates:[{id:"Project Bug", fields:[{id:"story"},{id:"reporter"},{id:"report date"},{id:"sprint output"},{id:"priority"},{id:"debugger"},{id:"fix date"},{id:"status"},{id:"attachment"},]}, ]}, 
                	{id:"Project Task", templates:[{id:"Project Task", fields:[{id:"story"},{id:"owner"},{id:"start date"},{id:"end date"},{id:"status"},{id:"description"},]}, ]}, 
                	{id:"Wiki Mail", templates:[{id:"Wiki Mail", fields:[{id:"subject"},{id:"from"},{id:"to"},{id:"cc"},{id:"sent"},{id:"attachment"},]}, ]}, 
                ]
        }
}';
		
		// mapping returns all templates in server, may cause error in other test cases
		$ps = explode("mapping:{", $ret, 2);
		$ret = preg_replace('/\s*\n\s*/', '', $ps[0]);
		$ps = explode("mapping:{", $acceptable, 2);
		$acceptable = preg_replace('/\s*\n\s*/', '', $ps[0]);
		
		$this->assertEquals($ret, $acceptable);
    }
}
?>