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
	
    private $derivedProperties;

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
    
    public function getDerivedProperties() {
    	return $this->derivedProperties;
    }
	
	/**
	 * Get the array of all properties that have stored values.
	 */
 	public function addDerivedProperties(SMWSemanticData $semData) {
 		
 	
 		
		global $smwgIP, $smwgHaloIP, $smwgNamespace;
		require_once($smwgIP . '/includes/SMW_QueryProcessor.php');
        require_once($smwgHaloIP . '/includes/storage/SMW_TripleStore.php');
        
        $subject = $semData->getSubject()->getDBkey();
        
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
		
       
        for ($i = 0; $i < count($propVal); $i += 2) {
        	if (!($propVal[$i] instanceof Title)) {
        		// The name of the property must be a title object
        		continue;
        	}
        	$propName = $propVal[$i]->getText();
        	$value = $propVal[$i+1];
        	$valueRep = ($value instanceof Title) ? $value->getText() : $value;
        	
        	// does the property already exist?
        	$values = $semData->getPropertyValues($propName);
        	$isDerived = true;
        	foreach ($values as $v) {
        		$wv = $v->getWikiValue();
        		if ($wv == $valueRep) {
        			$isDerived = false;
        			break;
        		}
        	}
        	if ($isDerived) {
        		$this->hasprops = true;
        		$derivedProperties[] = array($propName, $value, false, true);
        	}
        }
        
        
	}


	
	

}

?>