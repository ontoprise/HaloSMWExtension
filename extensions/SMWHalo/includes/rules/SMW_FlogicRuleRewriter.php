<?php

include_once('SMW_RuleRewriter.php');

/**
 * FlogicRuleRewriter
 * 
 * Rewrites flogic rules to RDF predicate syntax.
 * 
 * Example: 
 * 
 * FORALL X,Y X[hasFather->Y] <- Y[hasChild->X] AND Y:Man.
 * 
 *  gets transformed into:
 * 
 * FORALL X,Y attr_(X, "http://wiki/property#"#HasFather, Y)@"http://wiki" <- attr_(Y, "http://wiki/property#"#HasChild, X)@"http://wiki" 
 *  AND isa_(Y, "http://wiki/category#"#Man.)@"http://wiki"
 *
 */
class FlogicRuleRewriter extends RuleRewriter {

	private $variables;
	
	//TODO: define these as constants somewhere central
    public static $CAT_NS_SUFFIX = "/category#";
    public static $PROP_NS_SUFFIX = "/property#";
    public static $INST_NS_SUFFIX = "/a#";
    
	public function rewrite($ruletext) {
		global $smwgNamespace;
		
		// replace namespace in order not to disturb rule rewriting
        $ruletext = str_replace($smwgNamespace, "{{wiki-name}}", $ruletext);
        
		$this->variables = $this->getVariables($ruletext);
        
		
		// replace property statments: X[P->Y]
		$ruletext = preg_replace_callback("/([^\\s[]+)\\[(.+?)->([^\\s\\]]+)\\]/", array('FlogicRuleRewriter', 'replacePropStmt'), $ruletext);
		
		// replace instance statements: X:C
		$ruletext = preg_replace_callback("/([^\\s:]+):([^\\s.]+)/", array('FlogicRuleRewriter', 'replaceCatStmt'), $ruletext);
		
		// replace placeholder for namespace
		$ruletext = str_replace("{{wiki-name}}", $smwgNamespace, $ruletext);
		$ruletext = str_replace("|", "", $ruletext);

		$ruletext = str_replace('"xsd:string"', '"http://www.w3.org/2001/XMLSchema#"#string', $ruletext);
		$ruletext = str_replace('"xsd:float"', '"http://www.w3.org/2001/XMLSchema#"#float', $ruletext);
		$ruletext = str_replace('"xsd:boolean"', '"http://www.w3.org/2001/XMLSchema#"#boolean', $ruletext);
		$ruletext = str_replace('"xsd:dateTime"', '"http://www.w3.org/2001/XMLSchema#"#string', $ruletext);
		$ruletext = str_replace('"xsd:unit"', '"http://www.w3.org/2001/XMLSchema#"#string', $ruletext);

		return $ruletext;
	}
    
	/**
	 * Parses variables
	 *
	 * @param String $ruletext
	 * @return arrayof String
	 */
	private function getVariables($ruletext) {
		$ruletext = substr(trim($ruletext),6); // remove FORALL
		$variables = array();
		preg_match_all("/(\\w+)\\s*[,\\|]/", $ruletext, $matches);
		foreach($matches[1] as $m) {
			$variables[] = trim($m);
		}
		return $variables;
	}
    
	/**
	 * Callback for property statement parser
	 *
	 * @param Array $match
	 * @return String
	 */
	private function replacePropStmt($match) {
		
		$subject = $match[1];
		$predicate = $match[2];
		$object = $match[3];
		$literalpredicate = false;

		$predicateDV = SMWPropertyValue::makeProperty("_TYPE");
		
		$prop = SMWPropertyValue::makeUserProperty($predicate);
		$typeID = $prop->getTypeID();
		$xsdType = WikiTypeToXSD::getXSDType($typeID);		
		
		$types = smwfGetStore()->getPropertyValues(Title::newFromText(ucfirst($predicate), SMW_NS_PROPERTY), $predicateDV);
		
		if (!in_array($subject, $this->variables)) {
			$subject = "\"{{wiki-name}}".self::$INST_NS_SUFFIX."\"#".ucfirst($subject);
		}

		if (!in_array($predicate, $this->variables)) {
			$predicate = "\"{{wiki-name}}".self::$PROP_NS_SUFFIX."\"#".ucfirst($predicate);
		}
		 
		if (count($types) == 0) {
			// object is instance or variable
				
			if (!in_array(trim($object), $this->variables)) {
				// instance
				$object = "\"{{wiki-name}}".self::$INST_NS_SUFFIX."\"#".ucfirst($object);
			}
		} else {
			$type = $types[0]; // ignore multiple types
			if ($type->getXSDValue() == '_wpg') {
				// object is instance or variable
				if (!in_array(trim($object), $this->variables)) {
					// instance
					$object = "\"{{wiki-name}}".self::$INST_NS_SUFFIX."\"#".ucfirst($object);
				}
			} else {
				
				// object is literal or variable
				$literalpredicate = true;
				if (!in_array(trim($object), $this->variables)) {
					// object is literal
					$parts = explode(" ", $object);
					if (count($parts) == 2 && is_numeric($parts[0])) {
						// probably a unit
						$object = $parts[0] . ", '" . $parts[1] . "'"; 
					} else {
					   $object = is_numeric($object) ? $object.",\"$xsdType\"" : "\"$object\",''";
					}
					
				} else {
					$object .= ",\"$xsdType\"";
				}
			}
		}
		$stmt = ($literalpredicate ? "attl_" : "attr_")."($subject, $predicate, $object)@\"{{wiki-name}}\""."\n";
		return ($literalpredicate ? "attl_" : "attr_")."($subject, $predicate, $object)@\"{{wiki-name}}\"";
	}

	/**
     * Callback for instance statement parser
     *
     * @param Array $match
     * @return String
     */
	private function replaceCatStmt($match) {
		
		$instance = $match[1];
		$category = $match[2];
		
		// Make sure an xsd-type is not recognized as category.
		$xsdPos = strrpos($instance,'xsd');
		if ($xsdPos == strlen($instance)-3) {
			return $match[0];
		}
      
		if (!in_array($instance, $this->variables)) {
			$instance = "\"{{wiki-name}}".self::$INST_NS_SUFFIX."\"#".ucfirst($instance);
		}
		
		if (!in_array($category, $this->variables)) {
			$category = "\"{{wiki-name}}".self::$CAT_NS_SUFFIX."\"#".ucfirst($category);
		}
		 

		return "isa_($instance, $category)@\"{{wiki-name}}\"";
	}
}
?>