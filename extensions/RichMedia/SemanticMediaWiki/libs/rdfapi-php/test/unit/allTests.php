<?php

// ----------------------------------------------------------------------------------
// Script: allTest.php
// ----------------------------------------------------------------------------------

/**
 * Script for running the RAP unit tests.
 *
 * <BR><BR>History:<UL>
 * <LI>08-22-2004				 : Initial version of this class.
 *
 * For running the tests you have to 
 *
 * 1. Install the "Simple Test" testing framework
 *   into the document root of your web server. 
 *   Simple test can be downloaded from:
 *   http://sourceforge.net/projects/simpletest/ 
 *
 * 2. Now copy the "unit" folder to /rdfapi/test/
 *
 * 3. Make sure that "simple Test" and RAP is included correctly in 
 *   allTest.php and in
 *   showPasses.php 
 *
 * 4. To run the tests execute allTest.php
 *
 * @version  V0.9.1
 * @author Tobias Gauﬂ	<tobias.gauss@web.de>
 *
 * @package unittests
 * @access	public
 */


      define("SIMPLETEST_INCLUDE_DIR", "C:/!htdocs/simpletest/");
      define("RDFAPI_INCLUDE_DIR", "C:/!htdocs/rdfapi-php/api/");
      define("RDFAPI_TEST_INCLUDE_DIR", "C:/!htdocs/rdfapi-php/");
      define('RDFS_INF_TESTFILES',RDFAPI_TEST_INCLUDE_DIR.'test/unit/Infmodel/');

      require_once( SIMPLETEST_INCLUDE_DIR . 'unit_tester.php');
      require_once( SIMPLETEST_INCLUDE_DIR . 'reporter.php');
      require_once('show_passes.php');
	  include(RDFAPI_INCLUDE_DIR . "RdfAPI.php");
	  include_once( RDFAPI_INCLUDE_DIR . PACKAGE_INFMODEL);
	  include_once( RDFAPI_INCLUDE_DIR . PACKAGE_RESMODEL);
	  include_once( RDFAPI_INCLUDE_DIR . PACKAGE_ONTMODEL);
	  include_once( RDFAPI_INCLUDE_DIR . PACKAGE_SYNTAX_N3);
	  include_once( RDFAPI_INCLUDE_DIR . PACKAGE_SYNTAX_RDF);
	  include_once( RDFAPI_INCLUDE_DIR . PACKAGE_VOCABULARY);
      include(RDFAPI_INCLUDE_DIR.'vocabulary/ATOM_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/ATOM_RES.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/DC_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/DC_RES.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/FOAF_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/FOAF_RES.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/OWL_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/OWL_RES.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/RDF_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/RDF_RES.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/RDFS_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/RDFS_RES.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/RSS_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/RSS_RES.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/VCARD_C.php');
	  include(RDFAPI_INCLUDE_DIR.'vocabulary/VCARD_RES.php');


	$_SESSION['passes']=0;
	$_SESSION['fails']=0;
	define('LOG',FALSE);
	
	if(LOG){
		$file = fopen ("testlog.log", "a");
    	$time= strftime('%d.%m.%y %H:%M:%S' );
    	fputs($file,"\r\n".'-----'.$time.'-----'."\r\n");
	}

// =============================================================================
// *************************** package Model ***********************************
// =============================================================================

    $test1= &new GroupTest('Model MemModel Set Operations Test');
    $test1->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/Model/mm_SetOperations_tests.php');
    $test1->run(new ShowPasses());
    
    $test2= &new GroupTest('Model MemModel Basic Operations Test');
    $test2->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Model/mm_BasicOperations_tests.php');
    $test2->run(new ShowPasses());
 	
    $test3= &new GroupTest('Model MemModel Indextest');
    $test3->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Model/mm_index_tests.php');
    $test3->run(new ShowPasses());
    
    $test4= &new GroupTest('Model Literaltest');
    $test4->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Model/literals_tests.php');
    $test4->run(new ShowPasses());
    
    $test5= &new GroupTest('Model Blanknode test');
    $test5->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Model/blanknode_test.php');
    $test5->run(new ShowPasses()); 
    
    //$test6= &new GroupTest('Model DBModel test');
    //$test6->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Model/dBModel_test.php');
    //$test6->run(new ShowPasses()); 
 
    $test7= &new GroupTest('Model MemModel Search Test');
    $test7->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/Model/mm_search_tests.php');
    $test7->run(new ShowPasses()); 

	$test1_1= &new GroupTest('Get MemModel by rdql test');
    $test1_1->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/Model/getModelByRDQL_tests.php');
	$test1_1->run(new ShowPasses()); 

	

// =============================================================================
// *************************** package Syntax **********************************
// =============================================================================
   
    $test8= &new GroupTest('Syntax N3Parser test');
    $test8->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Syntax/n3Parser_test.php');
    $test8->run(new ShowPasses()); 
    
    $test9= &new GroupTest('Syntax Rdf Parser test');
    $test9->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Syntax/rdf_Parser_tests.php');
    $test9->run(new ShowPasses());
   
    $test10= &new GroupTest('Syntax Rdf Serializer test');
    $test10->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Syntax/rdf_Serializer_tests.php');
    $test10->run(new ShowPasses());  

   //$test11 = &new GroupTest('Syntax RDFParser tests (w3c)');
   //$test11->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/rdf/rdf_test_cases.php');
   //$test11->run(new ShowPasses());
 
// =============================================================================
// *************************** package Utility ***********************************
// =============================================================================    

  
    $test12= &new GroupTest('Utility FindIteratortest');
    $test12->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/utility/ut_FindIterator_tests.php');
    $test12->run(new ShowPasses()); 

    $test13= &new GroupTest('Utility StatementIterator test');
    $test13->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/utility/ut_it_tests.php');
    $test13->run(new ShowPasses()); 


// =============================================================================
// *************************** package InfModel ********************************
// =============================================================================

    $test1a= &new GroupTest('Model InfModelF Set Operations Test');
    $test1a->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelF_SetOperations_tests.php');
    $test1a->run(new ShowPasses());
   
    $test1b= &new GroupTest('Model InfModelB Set Operations Test');
    $test1b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelB_SetOperations_tests.php');
    $test1b->run(new ShowPasses());
   
    $test2a= &new GroupTest('Model InfModelF Basic Operations Test');
    $test2a->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/InfModel/InfModelF_BasicOperations_tests.php');
    $test2a->run(new ShowPasses());
	
    $test2b= &new GroupTest('Model InfModelB Basic Operations Test');
    $test2b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/InfModel/InfModelB_BasicOperations_tests.php');
    $test2b->run(new ShowPasses());
    
    $test3a= &new GroupTest('Model InfModelF Indextest');
    $test3a->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/InfModel/InfModelF_index_tests.php');
    $test3a->run(new ShowPasses());

    $test3b= &new GroupTest('Model InfModelB Indextest');
    $test3b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/InfModel/InfModelB_index_tests.php');
    $test3b->run(new ShowPasses());
    
    $test4a= &new GroupTest('Model InfModelF Search Test');
    $test4a->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelF_search_tests.php');
    $test4a->run(new ShowPasses()); 
  
    $test4b= &new GroupTest('Model InfModelB Search Test');
    $test4b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelB_search_tests.php');
    $test4b->run(new ShowPasses()); 

    $test5a= &new GroupTest('Model InfModelF : jena RDFS-tests ');
   	$test5a->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelF_jena_rdfs_test.php');
    $test5a->run(new ShowPasses());
    
    $test5b= &new GroupTest('Model InfModelB : jena RDFS-tests ');
    $test5b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelB_jena_rdfs_test.php');
    $test5b->run(new ShowPasses());
   
    $test6a= &new GroupTest('InfModelF entailment tests');
    $test6a->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelF_entailment_test.php');
    $test6a->run(new ShowPasses());
 
    $test6b= &new GroupTest('InfModelB entailment tests');
    $test6b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/InfModel/InfModelB_entailment_test.php');
    $test6b->run(new ShowPasses()); 


// =============================================================================
// *************************** package ResModel ********************************
// =============================================================================
	$test1b= &new GroupTest('ResModel basic operations tests');
    $test1b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/ResModel/ResModel_BasicOperations_tests.php');
    $test1b->run(new ShowPasses()); 
  
	$test2b= &new GroupTest('ResModel property tests');
    $test2b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/ResModel/ResModel_Property_tests.php');
    $test2b->run(new ShowPasses());     
  
    $test3b= &new GroupTest('ResModel search tests');
    $test3b->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/ResModel/ResModel_search_tests.php');
    $test3b->run(new ShowPasses());     
    

    
// =============================================================================
// *************************** package OntModel ********************************
// =============================================================================
	$test1c= &new GroupTest('OntModel basic operations tests');
    $test1c->addTestFile(RDFAPI_TEST_INCLUDE_DIR. '/test/unit/OntModel/OntModel_BasicOperations_tests.php');
    $test1c->run(new ShowPasses()); 


// =============================================================================
// *************************** package vocabulary ******************************
// =============================================================================

    $test14= &new GroupTest('Vocabulary tests');
    $test14->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/vocabulary/voc_tests.php');
    $test14->run(new ShowPasses()); 

// =============================================================================
// *************************** namespace handling ******************************
// =============================================================================    
    
    
    $test15= &new GroupTest('Namespace tests');
    $test15->addTestFile(RDFAPI_TEST_INCLUDE_DIR. 'test/unit/Namespaces/Namespace_handling_tests.php');
    $test15->run(new ShowPasses()); 


    if(LOG){
   		 $file = fopen ("testlog.log", "a");
   		 $time= strftime('%d.%m.%y %H:%M:%S' );
	     fputs($file,"\r\n".' Passes: '.$_SESSION['passes'].' Fails: '.$_SESSION['fails']."\r\n");
 	     fclose($file);
    }
     
?>
