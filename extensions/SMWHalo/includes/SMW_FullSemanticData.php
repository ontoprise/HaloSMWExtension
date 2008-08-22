<?php

/**
 * Extends SemanticData for semantic data needed for a triple store.
 *
 *  1. categories
 *  2. rules (optional)
 *  3. redirects
 *
 */
class SMWFullSemanticData extends SMWSemanticData {

    protected $categories;
    protected $rules = array();
    protected $redirects;
	private   $isDerived = false;
	private	  $derivedPropertiesAdded = false;
	
    public function __construct($dv) {
        parent::__construct($dv);
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
	    	if ($this->rules[$ruleid] === NULL) {
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
	 * Get the array of all properties that have stored values.
	 */
 	private function addDerivedProperties() {
 		
 		if ($this->derivedPropertiesAdded) {
 			return; 
 		}
 		$this->derivedPropertiesAdded = true;
 		
		global $smwgIP, $smwgHaloIP, $smwgNamespace;
		require_once($smwgIP . '/includes/SMW_QueryProcessor.php');
        require_once($smwgHaloIP . '/includes/storage/SMW_TripleStore.php');
        
        $subject = $this->subject->getDBkey();
        
		$inst = $smwgNamespace.SMWTripleStore::$INST_NS_SUFFIX;
//		$queryText = "PREFIX a:<$inst> SELECT ?pred ?obj WHERE { a:$subject ?pred ?obj . }";
		$queryText = "SELECT ?pred ?obj WHERE { a:$subject ?pred ?obj . }";
		
		$q = SMWSPARQLQueryProcessor::createQuery($queryText, new ParserOptions());
		$res = smwfGetStore()->getQueryResult($q);
		
		$propVal = array();
        while ( $row = $res->getNext() ) {
            foreach ($row as $field) {
                while ( ($object = $field->getNextObject()) !== false ) {
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
                }
            }
        }
		
        $this->isDerived = false;
        for ($i = 0; $i < count($propVal); $i += 2) {
        	if (!($propVal[$i] instanceof Title)) {
        		// The name of the property must be a title object
        		continue;
        	}
        	$propName = $propVal[$i]->getText();
        	$value = $propVal[$i+1];
        	$valueRep = ($value instanceof Title) ? $value->getText() : $value;
        	
        	// does the property already exist?
        	$values = $this->getPropertyValues($propName);
        	$this->isDerived = true;
        	foreach ($values as $v) {
        		$wv = $v->getWikiValue();
        		if ($wv == $valueRep) {
        			$this->isDerived = false;
        			break;
        		}
        	}
        	if ($this->isDerived) {
        		$this->hasprops = true;
        		SMWFactbox::addProperty($propName, $value, false, true);
        	}
        }
        $this->isDerived = false;
        
	}

	/**
	 * Return true if there are any properties.
	 */
	public function hasProperties() {
		if (!$this->hasprops) {
			//$this->addDerivedProperties(); //Due to a bug in SMW 
//              SMWFactbox::printFactbox() is called too often and fact boxes with
// 				derived properties are even generated in edit mode. So, showing
//				derives properties works by now only if there is at least on normal property.
		}
		return $this->hasprops;
	}
	
	public function getProperties($withDerived = false) {
		if ($withDerived) {
			$this->addDerivedProperties();
		}
		return parent::getProperties();
	}
	
	public function addPropertyObjectValue(Title $property, /*SMWDataValue*/ $value) {
		if ($this->isDerived) {
			$value->setDerived(true);
		}
		parent::addPropertyObjectValue($property, $value);
		
	}
	
	public function addPropertyValue($propertyname, /*SMWDataValue*/ $value) {
		if ($this->isDerived) {
			$value->setDerived(true);
		}
		parent::addPropertyValue($propertyname, $value);
	}
	
	

}

?>