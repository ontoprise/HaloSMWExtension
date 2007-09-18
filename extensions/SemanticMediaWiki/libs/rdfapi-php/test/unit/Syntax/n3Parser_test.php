<?php

// ----------------------------------------------------------------------------------
// Class: testN3Parser
// ----------------------------------------------------------------------------------

/**
 * Tests the N3Parser
 *
 * @version  $Id$
 * @author Tobias Gauß	<tobias.gauss@web.de>
 *
 * @package unittests
 * @access	public
 */

    class testN3Parser extends UnitTestCase {
        function testN3Parser() {
            $this->UnitTestCase();
            
            $_SESSION['n3TestInput']='
        		@prefix p:  <http://www.example.org/personal_details#> .
				@prefix m:  <http://www.example.org/meeting_organization#> .

				<http://www.example.org/people#fred>
					p:GivenName  	"Fred";
					p:hasEmail 		<mailto:fred@example.com>;
					m:attending 	<http://meetings.example.com/cal#m1> .
				
				<http://meetings.example.com/cal#m1>
					m:homePage 		<http://meetings.example.com/m1/hp> .
			';


        }
        function testIsMemmodel() {
            
			// Import Package
			include_once(RDFAPI_INCLUDE_DIR.PACKAGE_SYNTAX_N3);
			$n3pars= new N3Parser();
			$model=$n3pars->parse2model($_SESSION['n3TestInput'],false);		
            $this->assertIsA($model, 'memmodel');
        }
        
        function testParsing() {
            
        	$n3pars= new N3Parser();
			$model=$n3pars->parse2model($_SESSION['n3TestInput'],false);	
        	
        	
        	$model2 = new MemModel();

			// Ceate new statements and add them to the model
			$statement1 = new Statement(new Resource("http://www.example.org/people#fred"),
							  		    new Resource("http://www.example.org/personal_details#hasEmail"),
								  		new Resource("mailto:fred@example.com"));
			$statement2 = new Statement(new Resource("http://www.example.org/people#fred"),
							  		    new Resource("http://www.example.org/meeting_organization#attending"),
								  		new Resource("http://meetings.example.com/cal#m1"));
			$statement3 = new Statement(new Resource("http://www.example.org/people#fred"),
							  		    new Resource("http://www.example.org/personal_details#GivenName"),
								  		new Literal("Fred"));
			$statement4 = new Statement(new Resource("http://meetings.example.com/cal#m1"),
							  		    new Resource("http://www.example.org/meeting_organization#homePage"),
								  		new Resource("http://meetings.example.com/m1/hp"));
								  		
			
			$model2->add($statement1);
			$model2->add($statement2);
			$model2->add($statement3);
			$model2->add($statement4);

	
            $this->assertTrue($model->containsAll($model2));
        }
        
        function testPrefixNotDeclared() {
            $rdfInput='
            @prefix m:  <http://www.example.org/meeting_organization#>.

			<http://www.example.org/people#fred>
				p:GivenName  	"Fred";
				p:hasEmail 		<mailto:fred@example.com>;
				m:attending 	<http://meetings.example.com/cal#m1> .
        	';

			$n3pars= new N3Parser();
			$model=$n3pars->parse2model($rdfInput,false);	
			 //var_dump($model);	
            $this->assertErrorPattern('[Prefix not declared: p:]');
        }
        
        function testLoneSemicolon() {
            $n3 = '<a> <b> <c> ; .';
            $parser = &new N3Parser();
            $model = &$parser->parse2model($n3, false);
            $this->assertEqual(1, $model->size());
            $this->assertNoErrors();
        }
    }
?>
