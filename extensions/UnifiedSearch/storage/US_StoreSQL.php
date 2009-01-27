<?php

require_once('US_Store.php');
class USStoreSQL extends USStore {
	
	public function lookUpTitlesByText($terms, array $namespaces, $disjunctive = false, $limit=10, $offset=0) {
	   
	    // get titles containing all terms (case-insensitive)
        $requestoptions = new SMWAdvRequestOptions();
        $requestoptions->limit = $limit;
        $requestoptions->offset = $offset;
        $requestoptions->isCaseSensitive = false;
        $requestoptions->disjunctiveStrings = $disjunctive;
        foreach($terms as $term) {
            $requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
        }
        
		$result = "";
		$db =& wfGetDB( DB_SLAVE );
		$sql = "";
		if ($namespaces != NULL) {
			$sql .= '(';
			for ($i = 0, $n = count($namespaces); $i < $n; $i++) {
				if ($i > 0) $sql .= ' OR ';
				$sql .= 'page_namespace='.$db->addQuotes($namespaces[$i]);
			}
			if (count($namespaces) == 0) $sql .= 'true';
			$sql .= ') ';
		} else  {
			$sql = 'true';
		}

		$sql .= DBHelper::getSQLConditions($requestoptions,'page_title','page_title');
        
		
		$result = array();
		
		$length = 0;
		foreach($requestoptions->getStringConditions() as $cond) {
			$length += strlen($cond->string);
		}
        
		$page = $db->tableName('page');
		$query = '(SELECT page_title AS title, page_namespace AS ns, '.$length.'/LENGTH(page_title) AS score FROM '.$page.' WHERE '.$sql.' AND page_is_redirect = 0) ' .
                            'UNION DISTINCT ' .
                              '(SELECT rd_title AS title, rd_namespace AS ns, '.$length.'/LENGTH(page_title) AS score FROM '.$page.' JOIN redirect ON page_id = rd_from WHERE '.$sql.' AND page_is_redirect = 1) ORDER BY score DESC '.
        DBHelper::getSQLOptionsAsString($requestoptions,NULL);
        
		$res = $db->query($query );
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$title = Title::newFromText($row->title, $row->ns);
				$result[] = UnifiedSearchResult::newFromWikiTitleResult($title, $row->score);
			}
		}

		$db->freeResult($res);
		return $result;
	}
	
	public function lookupTitleBySKOS($terms, array $namespaces, $limit, $offset, $mode = 0) {
		
		 
		 $requestoptions = new SMWAdvRequestOptions();
		 $requestoptions->limit = $limit;
		 $requestoptions->offset = $offset;
		 $requestoptions->isCaseSensitive=false;
		 $requestoptions->disjunctiveStrings = true;
	 foreach($terms as $term) {
            $requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
        }
		 $results = array();
		 switch($mode) {
		 	case US_SYNOMYM_EXP:
		 		$results = $this->getPropertySubjectsByScore(array(SKOSVocabulary::$LABEL, SKOSVocabulary::$SYNONYM, SKOSVocabulary::$HIDDEN), $namespaces, $requestoptions);
		 	    break;
		 }
		 return $results;
		  
	}
	
	private function getPropertySubjectsByScore(array $properties, array $namespace, $requestoptions) {
		$db =& wfGetDB( DB_SLAVE );
		$smw_ids = $db->tableName('smw_ids');
		$smw_atts2 = $db->tableName('smw_atts2');
		
	    $namespaces = "";
        if ($namespace != NULL) {
            $namespaces .= '(';
            for ($i = 0, $n = count($namespace); $i < $n; $i++) {
                if ($i > 0) $namespaces .= ' OR ';
                $namespaces .= 's.smw_namespace='.$db->addQuotes($namespace[$i]);
            }
            if (count($namespace) == 0) $namespaces .= 'true';
            $namespaces .= ') ';
        } else  {
            $namespaces = 'true';
        }
		$propertyIDConstraint = "TRUE";
		foreach($properties as $p) {
			$p_id = smwfGetStore()->getSMWPropertyID($p);
			$propertyIDConstraint .= ' OR p_id = '.$p_id;
		}
       
	    $length = 0;
        foreach($requestoptions->getStringConditions() as $cond) {
            $length += strlen($cond->string);
        }
		$titleConstraint = DBHelper::getSQLConditions($requestoptions,'value_xsd','value_xsd');
		$query = 'SELECT s.smw_title AS title, s.smw_namespace AS ns, '.$length.'/LENGTH(value_xsd) AS score FROM '.
		$smw_ids.' s JOIN '.$smw_atts2.' ON s.smw_id = s_id WHERE '.$namespaces.' AND ('.$propertyIDConstraint.') '.$titleConstraint.' ' .
        DBHelper::getSQLOptionsAsString($requestoptions,NULL);
                         
        $res = $db->query($query );
        
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $title = Title::newFromText($row->title, $row->ns);
                $result[] = UnifiedSearchResult::newFromWikiTitleResult($title, $row->score);
            }
        }

        $db->freeResult($res);
        return $result;
	}
}
?>