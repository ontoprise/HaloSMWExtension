<?php

require_once('US_Store.php');
class USStoreSQL extends USStore {
    
	public function lookUpTitles($terms, array $namespaces, $disjunctive = false, $limit=10, $offset=0, $tolerance = 0) {
		$db =& wfGetDB( DB_SLAVE );
		// create virtual tables
        $db->query( 'CREATE TEMPORARY TABLE title_matches (page_title VARCHAR(255), page_namespace INTEGER, score DOUBLE)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
        
        $query = $this->lookUpTitlesByText($terms, $namespaces, false); // add all matches with all terms matching
        $db->query('INSERT INTO title_matches ('.$query.')');
        
        if ($tolerance == US_HIGH_TOLERANCE) { // add all matches with at least one term matching
            $query = $this->lookUpTitlesByText($terms, $namespaces, true);       
            $db->query('INSERT INTO title_matches ('.$query.')');
        }
        
        if ($tolerance <= US_LOWTOLERANCE) { // check SKOS properties in case of low or high tolerance
            $query = $this->lookupTitleBySKOS($terms, $namespaces, $tolerance);
            $db->query('INSERT INTO title_matches ('.$query.')');
        }
        
        $totalNum = 0;
        $query = 'SELECT COUNT(DISTINCT page_title, page_namespace) AS num FROM title_matches';
        $res = $db->query($query );
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $totalNum += $row->num;
            }
        }
        $db->freeResult($res);
        
        $query = 'SELECT page_title, page_namespace, SUM(score) AS totalscore FROM title_matches GROUP BY page_title, page_namespace ORDER BY totalscore DESC LIMIT '.$limit.' OFFSET '.$offset;
        $result = array();
        $res = $db->query($query );
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $title = Title::newFromText($row->page_title, $row->page_namespace);
                $result[] = UnifiedSearchResult::newFromWikiTitleResult($title, $row->totalscore);
            }
        }
        $db->freeResult($res);
        
        $db->query('DROP TEMPORARY TABLE title_matches');
        
        return array($result, $totalNum);
	}
	private function lookUpTitlesByText($terms, array $namespaces, $disjunctive = false) {

		// get titles containing all terms (case-insensitive)
		$requestoptions = new SMWAdvRequestOptions();
		
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
		$query = 'SELECT page_title, page_namespace, '.$length.'/LENGTH(page_title) AS score FROM '.$page.' WHERE '.$sql.' ORDER BY score DESC  ';
                          //  'UNION DISTINCT ' .
                           //   '(SELECT rd_title AS page_title, rd_namespace AS page_namespace, '.$length.'/LENGTH(page_title) AS score FROM '.$page.' JOIN redirect ON page_id = rd_from WHERE '.$sql.' AND page_is_redirect = 1) ORDER BY score DESC ';

		return $query;		
		
		
	}

	private function lookupTitleBySKOS($terms, array $namespaces, $mode = 0) {

			
		$requestoptions = new SMWAdvRequestOptions();
		$requestoptions->isCaseSensitive=false;
		$requestoptions->disjunctiveStrings = true;
		foreach($terms as $term) {
			$requestoptions->addStringCondition($term, SMWStringCondition::STRCOND_MID);
		}
		$results = "";
		switch($mode) {
			case US_LOWTOLERANCE:
				$results = $this->getPropertySubjectsByScore(array(SKOSVocabulary::$LABEL, SKOSVocabulary::$SYNONYM, SKOSVocabulary::$HIDDEN), $namespaces, $requestoptions);
				break;
			case US_HIGH_TOLERANCE:
                $results = $this->getPropertySubjectsByScore(array(SKOSVocabulary::$LABEL, SKOSVocabulary::$SYNONYM, SKOSVocabulary::$HIDDEN, SKOSVocabulary::$BROADER, SKOSVocabulary::$NARROWER), $namespaces, $requestoptions);
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
		$smw_ids.' s JOIN '.$smw_atts2.' ON s.smw_id = s_id WHERE '.$namespaces.' AND ('.$propertyIDConstraint.') '.$titleConstraint;
			
		return $query;
	}
}
?>