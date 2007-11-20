<?php
/*
 * Created on 18.10.2007
 *
 * GardeningIssues interface implementation for SQL.
 * 
 * Author: kai
 */
 
 global $smwgHaloIP;
 require_once $smwgHaloIP . '/specials/SMWGardening/SMW_GardeningIssues.php';
 require_once $smwgHaloIP . '/includes/SMW_DBHelper.php';
 
 class SMWGardeningIssuesAccessSQL extends SMWGardeningIssuesAccess {
 	
 	public function setup($verbose) {
 		global $smwgDefaultCollation;
			$db =& wfGetDB( DB_MASTER );

			// create GardeningIssues table
			$smw_gardening_issues = $db->tableName('smw_gardeningissues');
						
			if (!isset($smwgDefaultCollation)) {
				$collation = '';
			} else {
				$collation = 'COLLATE '.$smwgDefaultCollation;
			}
		
			// create relation table
			DBHelper::setupTable($smw_gardening_issues, array(
				  'bot_id'			=>  'VARCHAR(32) NOT NULL '.$collation,
				  'gi_type'      	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'gi_class'      	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'p1_id'			=>  'INT(8) UNSIGNED',
				  'p1_namespace'	=>	'INTEGER' ,
				  'p1_title'  		=> 	'VARCHAR(255) '.$collation,
				  'p2_id'			=>  'INT(8) UNSIGNED',
				  'p2_namespace'	=>	'INTEGER' ,
				  'p2_title'  		=> 	'VARCHAR(255) '.$collation,
				  'value'			=>	'VARCHAR(255) '.$collation,
				  'valueint'		=>	'INTEGER'), $db, $verbose);

			DBHelper::reportProgress("   ... done!\n",$verbose);
 	}
 	
 	public function clearGardeningIssues($bot_id = NULL, Title $t = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$sqlCond = ' WHERE TRUE ';
 		if ($t != NULL) {
 			$sqlCond = ' AND p1_id = '.$t->getArticleID();
 		}
 		if ($bot_id != NULL) {
 			$sqlCond .= ' AND bot_id = '.$db->addQuotes($bot_id);
 		}
 		$db->query('DELETE FROM '.$db->tableName('smw_gardeningissues').$sqlCond);
 	}
 	
 	public function existsGardeningIssue($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $title = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$sqlCond = array();
 		if ($bot_id != NULL) { 
 			$sqlCond[] = 'bot_id = '.$db->addQuotes($bot_id);
 		}
 		if ($gi_class != NULL) {
 			if (is_array($gi_class)) {
 				$cond = "";
 				foreach($gi_class as $c) {
 					$cond .= 'gi_class = '.$c.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else {
 				$sqlCond[] = 'gi_class = '.$gi_class;
 			}
 		}
 		if ($gi_type != NULL) {
 			if (is_array($gi_type)) {
 				$cond = "";
 				foreach($gi_type as $t) {
 					$cond .= 'gi_type = '.$t.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else { 
 				$sqlCond[] = 'gi_type = '.$gi_type;
 			}
 		}
 		if ($title != NULL) {
 			$sqlCond[] = 'p1_title = '.$db->addQuotes($titles->getDBkey()).' AND p1_namespace = '.$titles->getNamespace();
 		}
 		$row = $db->selectRow($db->tableName('smw_gardeningissues'), array('p1_id'), $sqlCond , 'SMWGardeningIssue::existsGardeningIssue');
 		return $row !== false;
 	}
 	
 	public function getGardeningIssuesForPairs($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $titles = NULL, $sortfor = NULL, $options = NULL) {
 		global $registeredBots;
 		$db =& wfGetDB( DB_MASTER );
 		
 		$sqlOptions = array();
 		if ($options != NULL) {
 			$sqlOptions['LIMIT'] = $options->limit;
 			$sqlOptions['OFFSET'] = $options->offset;
 		}
 		
 		if ($sortfor != NULL) {
 			switch($sortfor) {
 				case SMW_GARDENINGLOG_SORTFORTITLE: 
 					$sqlOptions['ORDER BY'] = 'p1_title';
 					break;
 				case SMW_GARDENINGLOG_SORTFORVALUE:
 					$sqlOptions['ORDER BY'] = 'valueint';
 					break;
 			}
 		} else { // sort by title by default
 			$sqlOptions['ORDER BY'] = 'p1_title';
 		}
		
 		$sqlCond = array();
 		if ($bot_id != NULL) { 
 			$sqlCond[] = 'bot_id = '.$db->addQuotes($bot_id);
 		}
 		if ($titles != NULL && is_array($titles)) {
 			
 			$cond = "";
 			if (count($titles) > 0 && is_array($titles[0])) {
	 			foreach($titles as $t) {
	 				$cond .= '(p1_title = '.$db->addQuotes($t[0]->getDBkey()).' AND p1_namespace = '.$t[0]->getNamespace().' AND p2_title = '.$db->addQuotes($t[1]->getDBkey()).' AND p2_namespace = '.$t[1]->getNamespace().') OR ';
	 			}
	 			$sqlCond[] = '('.$cond.' FALSE)';
 			} else {
 				$sqlCond[] = $cond .= '(p1_title = '.$db->addQuotes($titles[0]->getDBkey()).' AND p1_namespace = '.$titles[0]->getNamespace().' AND p2_title = '.$db->addQuotes($titles[1]->getDBkey()).' AND p2_namespace = '.$titles[1]->getNamespace().')';
 			}
 			
 		} 
 		if ($gi_class != NULL) {
 			if (is_array($gi_class)) {
 				$cond = "";
 				foreach($gi_class as $c) {
 					$cond .= 'gi_class = '.$c.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else {
 				$sqlCond[] = 'gi_class = '.$gi_class;
 			}
 		}
 		if ($gi_type != NULL) {
 			if (is_array($gi_type)) {
 				$cond = "";
 				foreach($gi_type as $t) {
 					$cond .= 'gi_type = '.$t.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else { 
 				$sqlCond[] = 'gi_type = '.$gi_type;
 			}
 		}
 		if ($options != NULL) { 
 			$sqlCond = array_merge($sqlCond, $this->getSQLValueConditions($options, NULL, 'p1_title'));
 		}
 		$result = array();
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('gi_type', 'p1_namespace', 'p1_title', 'p2_namespace', 'p2_title', 'value', 'valueint'), $sqlCond , 'SMWGardeningIssue::getGardeningIssuesForPairs', $sqlOptions );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$result[] = GardeningIssue::createIssue($bot_id, $row->gi_type, $row->p1_namespace, $row->p1_title, $row->p2_namespace, $row->p2_title, $row->value != NULL ? $row->value : $row->valueint);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getGardeningIssues($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $titles = NULL, $sortfor = NULL, $options = NULL) {
 		global $registeredBots;
 		$db =& wfGetDB( DB_MASTER );
 		
 		$sqlOptions = array();
 		if ($options != NULL) {
 			$sqlOptions['LIMIT'] = $options->limit;
 			$sqlOptions['OFFSET'] = $options->offset;
 		}
 		
 		if ($sortfor != NULL) {
 			switch($sortfor) {
 				case SMW_GARDENINGLOG_SORTFORTITLE: 
 					$sqlOptions['ORDER BY'] = 'p1_title';
 					break;
 				case SMW_GARDENINGLOG_SORTFORVALUE:
 					$sqlOptions['ORDER BY'] = 'valueint';
 					break;
 			}
 		} else { // sort by title by default
 			$sqlOptions['ORDER BY'] = 'p1_title';
 		}
		
 		$sqlCond = array();
 		if ($bot_id != NULL) { 
 			$sqlCond[] = 'bot_id = '.$db->addQuotes($bot_id);
 		}
 		if ($titles != NULL) {
 			if (is_array($titles)) {
 				$cond = "";
 				foreach($titles as $t) {
 					$cond .= '(p1_title = '.$db->addQuotes($t->getDBkey()).' AND p1_namespace = '.$t->getNamespace().') OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else { 
 				$sqlCond[] = 'p1_title = '.$db->addQuotes($titles->getDBkey()).' AND p1_namespace = '.$titles->getNamespace();
 			}
 		} 
 		if ($gi_class != NULL) {
 			if (is_array($gi_class)) {
 				$cond = "";
 				foreach($gi_class as $c) {
 					$cond .= 'gi_class = '.$c.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else {
 				$sqlCond[] = 'gi_class = '.$gi_class;
 			}
 		}
 		if ($gi_type != NULL) {
 			if (is_array($gi_type)) {
 				$cond = "";
 				foreach($gi_type as $t) {
 					$cond .= 'gi_type = '.$t.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else { 
 				$sqlCond[] = 'gi_type = '.$gi_type;
 			}
 		}
 		if ($options != NULL) { 
 			$sqlCond = array_merge($sqlCond, $this->getSQLValueConditions($options, NULL, 'p1_title'));
 		}
 	   
 		$result = array();
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('gi_type', 'p1_namespace', 'p1_title', 'p2_namespace', 'p2_title', 'value', 'valueint'), $sqlCond , 'SMWGardeningIssue::getGardeningIssues', $sqlOptions );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$result[] = GardeningIssue::createIssue($bot_id, $row->gi_type, $row->p1_namespace, $row->p1_title, $row->p2_namespace, $row->p2_title, $row->value != NULL ? $row->value : $row->valueint);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	
 	
 	public function getDistinctTitles($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $sortfor = NULL, $options = NULL) {
 		global $registeredBots;
 		$db =& wfGetDB( DB_MASTER );
 		
 		$sqlOptions = array();
 		$sqlOptions = array('GROUP BY' => 'p1_title, p1_namespace');
 		if ($options != NULL) {
 			$sqlOptions['LIMIT'] = $options->limit;
 			$sqlOptions['OFFSET'] = $options->offset;
 		}
 		
 		if ($sortfor != NULL) {
 			switch($sortfor) {
 				case SMW_GARDENINGLOG_SORTFORTITLE: 
 					$sqlOptions['ORDER BY'] = 'p1_title';
 					break;
 				case SMW_GARDENINGLOG_SORTFORVALUE:
 					$sqlOptions['ORDER BY'] = 'valueint DESC';
 					break;
 			}
 		} else { // sort by title by default
 			$sqlOptions['ORDER BY'] = 'p1_title';
 		}
 		
		
 		$sqlCond = array();
 		if ($bot_id != NULL) { 
 			$sqlCond[] = 'bot_id = '.$db->addQuotes($bot_id);
 		}
 		
 		if ($gi_class != NULL) {
 			$sqlCond[] = 'gi_class = '.$gi_class;
 		}
 		if ($gi_type != NULL) {
 			if (is_array($gi_type)) {
 				$cond = "";
 				foreach($gi_type as $t) {
 					$cond .= 'gi_type = '.$t.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else { 
 				$sqlCond[] = 'gi_type = '.$gi_type;
 			}
 		}
 		if ($options != NULL) { 
 			$sqlCond = array_merge($sqlCond, $this->getSQLValueConditions($options, NULL, 'p1_title'));
 		}
 		$result = array();
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('p1_title', 'p1_namespace'), $sqlCond , 'SMWGardeningIssue::getDistinctTitles', $sqlOptions );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$t = Title::newFromText($row->p1_title, $row->p1_namespace);
				if ($t != NULL) $result[] = $t;
				
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		
		return $result;
 	}
 	
 	public function getDistinctTitlePairs($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $sortfor = NULL, $options = NULL) {
 		global $registeredBots;
 		$db =& wfGetDB( DB_MASTER );
 		
 		$sqlOptions = array('GROUP BY' => 'p1_id, p2_id');
 		if ($options != NULL) {
 			$sqlOptions['LIMIT'] = $options->limit;
 			$sqlOptions['OFFSET'] = $options->offset;
 		}
 		
 		if ($sortfor != NULL) {
 			switch($sortfor) {
 				case SMW_GARDENINGLOG_SORTFORTITLE: 
 					$sqlOptions['ORDER BY'] = 'p1_title';
 					break;
 				case SMW_GARDENINGLOG_SORTFORVALUE:
 					$sqlOptions['ORDER BY'] = 'valueint DESC';
 					break;
 			}
 		} else { // sort by title by default
 			$sqlOptions['ORDER BY'] = 'p1_title';
 		}
 		
		
 		$sqlCond = array();
 		if ($bot_id != NULL) { 
 			$sqlCond[] = 'bot_id = '.$db->addQuotes($bot_id);
 		}
 		
 		if ($gi_class != NULL) {
 			$sqlCond[] = 'gi_class = '.$gi_class;
 		}
 		if ($gi_type != NULL) {
 			if (is_array($gi_type)) {
 				$cond = "";
 				foreach($gi_type as $t) {
 					$cond .= 'gi_type = '.$t.' OR ';
 				}
 				$sqlCond[] = '('.$cond.' FALSE)';
 			} else { 
 				$sqlCond[] = 'gi_type = '.$gi_type;
 			}
 		}
 		if ($options != NULL) { 
 			$sqlCond = array_merge($sqlCond, $this->getSQLValueConditions($options, NULL, 'p1_title'));
 		}
 		$result = array();
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('p1_title', 'p1_namespace', 'p2_title', 'p2_namespace'), $sqlCond , 'SMWGardeningIssue::getDistinctTitlePairs', $sqlOptions );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$t1 = Title::newFromText($row->p1_title, $row->p1_namespace);
				$t2 = Title::newFromText($row->p2_title, $row->p2_namespace);
				if ($t1 != NULL && $t2 != NULL) { 
					$result[] = array($t1, $t2);
				}
				
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		
		return $result;
 	}
 	
 	public function addGardeningIssueAboutArticles($bot_id, $gi_type, Title $t1, Title $t2, $value = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$numeric_value = is_numeric($value);
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type, 'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => $t2->getArticleID(), 'p2_namespace' => $t2->getNamespace(), 'p2_title' => $t2->getDBkey(), 'value' => $numeric_value ? NULL : $value, 'valueint' => $numeric_value ? intval($value) : NULL));
 						
 	}
 	
 	public function addGardeningIssueAboutArticle($bot_id, $gi_type, Title $t1) {
 		$db =& wfGetDB( DB_MASTER );
 		
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => -1 ,'p2_namespace' => -1, 'p2_title' => NULL, 'value' =>  NULL, 'valueint' => NULL));
 		
 	}
 	
 	public function addGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value) {
 		$db =& wfGetDB( DB_MASTER );
 		$numeric_value = is_numeric($value);
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => -1, 'p2_namespace' => -1, 'p2_title' => NULL, 'value' => $numeric_value ? NULL : $value, 'valueint' => $numeric_value ? intval($value) : NULL));
 	}
 	
 	
 	
 	protected function getSQLValueConditions($requestoptions, $valuecol, $labelcol = NULL) {
		$sql_conds = array();
		if ($requestoptions !== NULL) {
			$db =& wfGetDB( DB_SLAVE );
			if ($requestoptions->boundary !== NULL) { // apply value boundary
				if ($requestoptions->ascending) {
					if ($requestoptions->include_boundary) {
						$op = ' >= ';
					} else {
						$op = ' > ';
					}
				} else {
					if ($requestoptions->include_boundary) {
						$op = ' <= ';
					} else {
						$op = ' < ';
					}
				}
				$sql_conds[] =  $valuecol . $op . $db->addQuotes($requestoptions->boundary);
			}
			if ($labelcol !== NULL) { // apply string conditions
				foreach ($requestoptions->getStringConditions() as $strcond) {
					$string = str_replace(array('_', ' '), array('\_', '\_'), $strcond->string);
					switch ($strcond->condition) {
						case SMW_STRCOND_PRE:
							$string .= '%';
							break;
						case SMW_STRCOND_POST:
							$string = '%' . $string;
							break;
						case SMW_STRCOND_MID:
							$string = '%' . $string . '%';
							break;
					}
					$sql_conds[] = 'UPPER('.$labelcol . ') LIKE UPPER(' . $db->addQuotes($string).')';
				}
			}
		}
		return $sql_conds;
	}
 }
?>
