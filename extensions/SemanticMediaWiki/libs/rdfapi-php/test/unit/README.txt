
----------------------------------------------------------
Using the RAP Unit Tests
----------------------------------------------------------

This package contains unit test for testing the RAP classes.
The test use the "Simple Test" testing framework, which is
similar to JUnit.

For running the tests you have to 

1. Install the "Simple Test" testing framework
   into the document root of your web server. 
   Simple test can be downloaded from:
   http://sourceforge.net/projects/simpletest/ 

2. Now copy the "unit" folder to /rdfapi/test/
 
3. Make sure that "simple Test" and RAP is included correctly in 
   allTest.php and in
   showPasses.php 

4. To run the tests execute allTest.php
   
In allTest.php you can also comment out all tests that you do not want to execute. 
