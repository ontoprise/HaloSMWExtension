
/*
* The DeployDescriptorProcessor applies the changes specified in a deploy descritor 
* to this file. 
*/
$testvar2 = "Halo rockt";
$testvar3 = "Halo is cool";
$testvar4 = 10;
$testvar5 = false;


/*start-smwhalo*/
$innertestvar1 = "Halo";
testfunc2(/*param-start-testfunc2*/'http://localhost:8080', array('1', '2')/*param-end-testfunc2*/);
require('testcases/resources/testinclude2.php');
/*php-start-phptest2*/ $testphp2 = 2; /*php-end-phptest2*/
/*end-smwhalo*/
 
$testvar6 = "Halo";


