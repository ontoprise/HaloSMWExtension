<?php

// ----------------------------------------------------------------------------------
// Class: rdfParser_test
// ----------------------------------------------------------------------------------

/**
 * Tests the RDF/XML Parser
 *
 * <BR><BR>History:<UL>
 * <LI>08-22-2004				 : Initial version of this class.
 *
 * @version  V0.9.1
 * @author Tobias Gau�	<tobias.gauss@web.de>
 *
 * @package unittests
 * @access	public
 */

 class rdfParser_test extends UnitTestCase {

 	function testRdfParser(){
 		$_SESSION['test']='Rdf- Parser Empty Resource test';
 		$mod1= new MemModel();
 		$mod2= new MemModel();
 		
 		$mod1->load('emptyResource.rdf');
 		$mod2->load('emptyResource_serialized.rdf');

 		if($mod1->equals($mod2)){
 			$pass=true;
 		}else{
 			$pass=false;
 		};
 		$this->assertTrue($pass);
 	}
 	
 	
 	
 }
?>