<?php

class TestOb {
	
	public function testfunc($test1, & $test2) {
		$test2 = "Test2";
	}
}

$o = new TestOb();
$userValues="";
$o->testfunc("Test1",$userValues );


print $userValues;
 

?>