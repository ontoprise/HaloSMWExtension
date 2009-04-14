<?php

include_once('SMW_RuleRewriter.php');

/**
 * FlogicRuleRewriter
 * 
 * Rewrites flogic rules to object logic syntax.
 * 
 * Example: 
 * 
 * FORALL X,Y X[hasFather->Y] <- Y[hasChild->X] AND Y:Man.
 * 
 *  gets transformed into:
 * 
 * attg_(?X, _"http://wiki/property#HasFather", ?Y) :- attg_(?Y, _"http://wiki/property#HasChild", ?X) 
 *  AND isa_(?Y, _"http://wiki/category#Man".)"
 *
 */
class FlogicRuleRewriter extends RuleRewriter {

	private $variables;
	
	//TODO: define these as constants somewhere central
    public static $CAT_NS_SUFFIX = "/category#";
    public static $PROP_NS_SUFFIX = "/property#";
    public static $INST_NS_SUFFIX = "/a#";
    
	public function rewrite($ruletext) {
		global $smwgTripleStoreGraph;
		
		// replace namespace in order not to disturb rule rewriting
        $ruletext = str_replace($smwgTripleStoreGraph, "{{wiki-name}}", $ruletext);
        
		$this->variables = $this->getVariables($ruletext);
        
		
		// replace property statments: X[P->Y]
		$ruletext = preg_replace_callback("/([^\\s[]+)\\[(.+?)->([^\\s\\]]+)\\]/", array('FlogicRuleRewriter', 'replacePropStmt'), $ruletext);
		
		// replace instance statements: X:C
		$ruletext = preg_replace_callback("/([^\\s:]+):([^\\s.]+)/", array('FlogicRuleRewriter', 'replaceCatStmt'), $ruletext);
		
		// replace placeholder for namespace
		$ruletext = str_replace("{{wiki-name}}", $smwgTripleStoreGraph, $ruletext);
		$ruletext = substr($ruletext, strpos($ruletext, "|")+1);
        $ruletext = str_replace("<-", ":-", $ruletext);
		

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
		

		$predicateDV = SMWPropertyValue::makeProperty("_TYPE");
		
		$prop = SMWPropertyValue::makeUserProperty($predicate);
		$typeID = $prop->getPropertyTypeID();
		$xsdType = substr(WikiTypeToXSD::getXSDType($typeID), 4); // get the parts after xsd:...		
		
		$types = smwfGetStore()->getPropertyValues(Title::newFromText(ucfirst($predicate), SMW_NS_PROPERTY), $predicateDV);
		
		if (!in_array($subject, $this->variables)) {
			$subject = "_\"{{wiki-name}}".self::$INST_NS_SUFFIX.ucfirst($subject)."\"";
		} else {
			$subject = "?".$subject;
		}

		if (!in_array($predicate, $this->variables)) {
			$predicate = "_\"{{wiki-name}}".self::$PROP_NS_SUFFIX.ucfirst($predicate)."\"";
		} else {
            $predicate = "?".$predicate;
        }
		 
		if (count($types) == 0) {
			// object is instance or variable
				
			if (!in_array(trim($object), $this->variables)) {
				// instance
				$object = "_\"{{wiki-name}}".self::$INST_NS_SUFFIX.ucfirst($object)."\"";
			} else {
				$object = "?".$object;
			}
		} else {
			$type = $types[0]; // ignore multiple types
			if ($type->getXSDValue() == '_wpg') {
				// object is instance or variable
				if (!in_array(trim($object), $this->variables)) {
					// instance
					$object = "_\"{{wiki-name}}".self::$INST_NS_SUFFIX.ucfirst($object)."\"";
				}
			} else {
				
				// object is literal or variable
				
				if (!in_array(trim($object), $this->variables)) {
					// object is literal
					$parts = explode(" ", $object);
					if (count($parts) == 2 && is_numeric($parts[0])) {
						// FIXME: probably a unit. Can not be expressed in ObjectLogic at the moment
						$object = "\"".$parts[0] . " " . $parts[1]."\"^^_$xsdType";
						
					} else {
					   $object = "\"".$object."\"^^_$xsdType";
					}
					
				} else {
					$object = "?".$object;
				}
			}
		}
		$stmt = "attg_($subject, $predicate, $object)";
		return  $stmt;
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
			$instance = "_\"{{wiki-name}}".self::$INST_NS_SUFFIX.ucfirst($instance)."\"";
		} else {
			$instance = "?$instance";
		}
		
		if (!in_array($category, $this->variables)) {
			$category = "_\"{{wiki-name}}".self::$CAT_NS_SUFFIX.ucfirst($category)."\"";
		} else {
            $category = "?$category";
        }
		 

		return "isa_($instance, $category)";
	}
}
?>