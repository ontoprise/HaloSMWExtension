<?php
 class SMWSPARQLTransformer {

    private $sparqlAST;
    
    public function __construct($sparqlAST) {
            
        $this->sparqlAST = $sparqlAST;
           
    }
    
    /**
     * Transforms query to ASK syntax
     *
     * @return string ASK query
     */
    public function transform() {
        
        $res = $this->checkQuery();
        if ($res !== true) {
            throw new Exception($res);
        }
        
        $resultPart = $this->sparqlAST->getResultPart();
        $graphPattern = $resultPart[0]->getTriplePattern();
        
        $rootTriples = SPARQLTransformHelper::getRootTriples($graphPattern);
        $resultVars = $this->sparqlAST->getResultVars();
        
        $propertyRestr = "";
        $categoryRestr = "";
        foreach($rootTriples as $triple) {
        	if (in_array($triple->getObject(), $resultVars)) {
        		$propertyRestr .= "[[".$triple->getPredicate()->getURI()."::*]]";
        		continue;
        	}
        	if ($triple->getPredicate()->getLocalName() == 'type') {
        		$categoryRestr .= "[[Category:".$triple->getObject()->getURI()."]]";
        		continue;
        	}
        	$subQuery = $this->_transform($triple, $graphPattern);
        	if (strlen($subQuery) > 0) {
        	   $propertyRestr .= "[[".$triple->getPredicate()->getURI()."::<q>".$subQuery."</q>]]";
        	} else {
        		$propertyRestr .= "[[".$triple->getPredicate()->getURI()."::*]]";
        	}
        }
        
        return $categoryRestr.$propertyRestr; //print_r($this->sparqlAST, true);
    }
    
    private function _transform($triple, & $graphPatterns) {
        $propertyRestr = "";
        $categoryRestr = "";
        if (SPARQLTransformHelper::isVariable($triple->getObject())) {
            $triples = SPARQLTransformHelper::getSubjectsToVar($triple->getObject(), $graphPatterns);
            foreach($triples as $triple) {
	            
	            if ($triple->getPredicate()->getURI() == 'rdf:type') {
	                $categoryRestr .= "[[Category:".$triple->getObject()->getURI()."]]";
	                continue;
	            }
              
	            $subQuery = $this->_transform($triple, $graphPattern);
	            if (strlen($subQuery) > 0) {
	               $propertyRestr .= "[[".$triple->getPredicate()->getURI()."::<q>".$subQuery."</q>]]";
	            } else {
	                $propertyRestr .= "[[".$triple->getPredicate()->getURI()."::*]]";
	            }
            }
        }
       
        return $categoryRestr.$propertyRestr;
    }
    /**
     * Checks if query is transformable to ASK.
     * 
     * @return mixed: true or string with reason
     */
    private function checkQuery() {
        $cond1 = $this->checkCond1();
        $cond2 = $this->checkCond2();
        $cond3 = $this->checkCond3();
        $cond4 = $this->checkCond4();
        
        $res = $cond1 && $cond2 && $cond3 && $cond4;
        
        $reason = "";
        if ($res === false) {
        	if ($cond1 === false) {
        		$reason .= "All selected variables must appear on first level ".
        		           "(subject or object position). Every variable on object ".
        		           "position must appear only once.\n";
        	}
	        if ($cond2 === false) {
	                $reason .= "Exactly one of the selected variables must appear ".
	                           "on subject position on first level.\n";
	                           
	            }
	        if ($cond3 === false) {
	                $reason .= "There must be no cycles in the query graph.\n";
	                           
	            }
	        if ($cond4 === false) {
	                $reason .= "All predicates and objects of rdf:type must be ground.\n";
	                           
	        }
        }
        return $res === true ? true : $reason;
    }
    
    /**
     * Checks if all selected variables appear on first level 
     * (subject or object position). Every variable on object position 
     * must appear only once.
     * 
     * @return bool
     */
    private function checkCond1() {
        $resultPart = $this->sparqlAST->getResultPart();
        $graphPattern = $resultPart[0]->getTriplePattern();
        
        // get root triples
        $rootTriples = SPARQLTransformHelper::getRootTriples($graphPattern);
        
        // checks if result vars appear in subject or object of root triples 
        $resultVars = $this->sparqlAST->getResultVars();
        $ok = true;
        foreach($resultVars as $var) {
            $ok = $ok && (SPARQLTransformHelper::inSubjectOrObject($var, $rootTriples));
        }
        if (!$ok) return false;
        
        // check if all object variables appear only once in root triples
        $objectVariables = array();
        foreach($rootTriples as $triple) {
            if (SPARQLTransformHelper::isVariable($triple->getObject())) {
                if (array_key_exists($triple->getObject(), $objectVariables)) return false;
                $objectVariables[$triple->getObject()] = true;
            }
        }
        return true;
    }
    
    /**
     * Checks if exactly one of these variables
     * appears on subject position on first level.
     *
     */
    private function checkCond2() {
    	$resultVars = $this->sparqlAST->getResultVars();
    	$resultPart = $this->sparqlAST->getResultPart();
        $graphPattern = $resultPart[0]->getTriplePattern();
        
        // get root triples
        $rootTriples = SPARQLTransformHelper::getRootTriples($graphPattern);
        
	    $foundSubject = false;
        foreach($resultVars as $var) {
	        foreach($rootTriples as $triple) {
	        	if ($triple->getSubject() == $var) {
	        		if ($foundSubject) {
	        			return false;
	        		}
	        		$foundSubject = true;
	        		break;
	        	}
	        }
        }
        return $foundSubject;
    }
    
    /**
     * Checks if the query contains cycles.
     *
     * @return bool
     */
    private function checkCond3() {
    	$resultPart = $this->sparqlAST->getResultPart();
        $graphPattern = $resultPart[0]->getTriplePattern();
        $rootTriples = SPARQLTransformHelper::getRootTriples($graphPattern);
        if (empty($rootTriples)) return false;
        $no_cycle = true;
        foreach($rootTriples as $triple) {
	        $visitedTriples = array($triple);
	        $no_cycle = $no_cycle && $this->_checkCond3($triple, $graphPattern, $visitedTriples);
        }
        return $no_cycle;
    }
    
    private function _checkCond3($triple, & $graphPatterns, & $visitedTriples) {
    	$no_cycle = true;
    	if (SPARQLTransformHelper::isVariable($triple->getObject())) {
    		$triples = SPARQLTransformHelper::getSubjectsToVar($triple->getObject(), $graphPatterns);
    		foreach($triples as $triple) {
    			if (SPARQLTransformHelper::containsTriple($triple, $visitedTriples)) {
    				return false;
    			}
    			$visitedTriples[] = $triple;
    		    $no_cycle = $no_cycle && $this->_checkCond3($triple, $graphPatterns, $visitedTriples);
    		}
    	}
    	array_pop($visitedTriples);
    	return $no_cycle;
    }
    
    /**
     * Checks if all predicates and objects of rdf:type are ground.
     *
     * @return bool
     */
    private function checkCond4() {
        $resultPart = $this->sparqlAST->getResultPart();
        $graphPattern = $resultPart[0]->getTriplePattern();
        
        // check properties and objects of rdf:type for groundness
        foreach($graphPattern as $triple) {
        	if (SPARQLTransformHelper::isVariable($triple->getPredicate())) {
        		return false;
        	}
        	if ($triple->getPredicate() == 'rdf:type') {
        		if (SPARQLTransformHelper::isVariable($triple->getObject())) {
        			return false;
        		}
        	}
        }
        return true;
       
    }
 }
 
 class SPARQLTransformHelper {
 	
 	public static function isVariable($term) {
 		return substr($term,0,1) == '?';
 	}
    public static function inSubjectOrObject($var, $rootTriples) {
        foreach($rootTriples as $triple) {
            if ($triple->getSubject() == $var || $triple->getObject() == $var) return true;
        }
        return false;
    }
    
    public static function getRootTriples($graphPattern) {
        $rootTriples = array();
        foreach($graphPattern as $triple1) {
            $isRoot = true;
            foreach($graphPattern as $triple2) {
                
                if ($triple1->getSubject() == $triple2->getObject()) {
                    $isRoot = false;
                    break;
                }
            }
            if ($isRoot) $rootTriples[] = $triple1;
        }
        return $rootTriples;
    }
    
    public static function getSubjectsToVar($var, $graphPatterns) {
    	$triples = array();
        foreach($graphPatterns as $triple) {
        	if ($triple->getSubject() == $var) {
        		$triples[] = $triple;
        	}
        }
        return $triples;
    }
    
    public static function containsTriple($triple, $visitedTriples) {
    	foreach($visitedTriples as $t) {
    		if ($triple->getSubject() == $t->getSubject() 
	    		&& $triple->getPredicate() == $t->getPredicate() 
	    		&& $triple->getObject() == $t->getObject()) {
    			return true;
    		}
    	}
    	return false;
    }
 }
 
	 
?>