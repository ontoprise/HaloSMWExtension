<?php
class USStoreSQL extends USStore {
	
	public function lookUpTitles(array $namespace, $requestOptions) {
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

		$res = $db->query( '(SELECT page_title AS title, page_namespace AS ns, SUM('.$length.'/LENGTH(page_title)) AS score FROM page WHERE '.$sql.' AND page_is_redirect = 0) ' .
                            'UNION DISTINCT ' .
                              '(SELECT rd_title AS title, rd_namespace AS ns, SUM('.$length.'/LENGTH(page_title)) AS score FROM page JOIN redirect ON page_id = rd_from WHERE '.$sql.' AND page_is_redirect = 1) GROUP BY title ORDER BY score DESC '.

		DBHelper::getSQLOptionsAsString($requestoptions,'ns'));
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->title, $row->ns);
			}
		}

		$db->freeResult($res);
		return $result;
	}
}
?>