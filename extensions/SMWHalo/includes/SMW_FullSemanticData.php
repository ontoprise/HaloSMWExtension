<?php

/**
 * Extends SemanticData for semantic data needed for a triple store.
 *
 *  1. categories
 *  2. rules (optional)
 *  3. redirects
 *
 */
class SMWFullSemanticData {

    protected $categories;
    protected $rules = array();
    protected $redirects;
	
    public function __construct() {
        $this->categories = array();
        $this->rules = array();
        $this->redirects = array();
    }

    public function setCategories($categories) {
    	$this->categories = $categories;
    }

	// set rules (array)
    public function setRules($rules) {
    	foreach ($rules as $ruleid => $ruletext) {
    		// check if ruleId already exists - if so, do not add rule again to parsed array
	    	if (!array_key_exists($ruleid, $this->rules)) {
	    		$this->rules[$ruleid] = $ruletext;
	    	}
    	}
    }

    public function setRedirects($redirects) {
         $this->redirects = $redirects;
    }

    public function getCategories() {
    	return $this->categories;
    }

    public function getRules() {
         return $this->rules;
    }

    public function getRedirects() {
         return $this->redirects;
    }
    	
	/**
	 * Get derived properties.
	 * @param SMWSemanticData $semData
	 * 		Annotated facts of an article
	 * @return SMWSemanticData
	 * 		Derived facts of the article
	 */
 	public static function getDerivedProperties(SMWSemanticData $semData) {
 		
		global $smwgIP, $smwgHaloIP, $smwgTripleStoreGraph;
		require_once($smwgIP . '/includes/SMW_QueryProcessor.php');
        require_once($smwgHaloIP . '/includes/storage/SMW_TripleStore.php');

        $derivedProperties = new SMWSemanticData($semData->getSubject());
        
        $subject = $semData->getSubject()->getDBkey();
        
		$inst = $smwgTripleStoreGraph.SMWTripleStore::$INST_NS_SUFFIX;
//		$queryText = "PREFIX a:<$inst> SELECT ?pred ?obj WHERE { a:$subject ?pred ?obj . }";
		$queryText = "SELECT ?pred ?obj WHERE { a:$subject ?pred ?obj . }";
		
		// Ask for all properties of the subject (derived and ground facts)
		$q = SMWSPARQLQueryProcessor::createQuery($queryText, new ParserOptions());
		$res = smwfGetStore()->getQueryResult($q);
		
		$propVal = array();
        while ( $row = $res->getNext() ) {
        	$i = 0;
            foreach ($row as $field) {
                while ( ($object = $field->getNextObject()) !== false ) {
                	$propVal[] = $object;
/*
                    if ($object->getTypeID() == '_wpg') {  // print whole title with prefix in this case
                        $text = $object->getTitle()->getText();
                        $propVal[] = $object->getTitle();
                    } else {
                        if ($object->isNumeric()) { // does this have any effect?
                            $text = $object->getNumericValue();
                        	$propVal[] = $text;
                        } else {
                            $text = $object->getXSDValue();
                        	$propVal[] = $text;
                        }
                    }
*/
                }
            }
        }
		
       
        for ($i = 0; $i < count($propVal); $i += 2) {
        	if ($propVal[$i]->getTypeID() != '_wpg') {
        		// The name of the property must be a title object
        		continue;
        	}
        	$propName = $propVal[$i]->getTitle()->getText();
        	$value = $propVal[$i+1];
//        	$valueRep = ($value instanceof Title) ? $value->getText() : $value;
        	
        	// does the property already exist?
        	$prop = SMWPropertyValue::makeUserProperty($propName);
        	$values = $semData->getPropertyValues($prop);
        	$isDerived = true;
        	$val = null;
        	foreach ($values as $v) {
        		if ($value->getTypeID() == '_wpg' && $v->getTypeID() == '_wpg') {
        			$vt1 = $value->getTitle();
        			$vt2 = $v->getTitle();
        			if (isset($vt1) 
        			    && isset($vt2)
        			    && $vt1->getText() == $vt2->getText()) {
	        			$isDerived = false;
	        			break;
        			}
        		} else if ($value->getTypeID() == '_wpg' && $v->getTypeID() != '_wpg') {
        			// how can this happen?
        			$isDerived = false;
        			break;
        		} else {
					if ($value->isNumeric()) {
		        		if ($value->getNumericValue() == $v->getNumericValue()) {
		        			$isDerived = false;
		        			break;
		        		}
		        	} else {
		        		if ($value->getXSDValue() == $v->getXSDValue()) {
		        			$isDerived = false;
		        			break;
		        		}
		        	}
        		}
        	}
        	if ($isDerived) {
				$property = SMWPropertyValue::makeUserProperty($propName);
        		$derivedProperties->addPropertyObjectValue($property, $value);
        	}
        }
        return $derivedProperties;
	}

}

?>