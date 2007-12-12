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
				  'valueint'		=>	'INTEGER',
				  'modified'		=>  'ENUM(\'y\', \'n\') DEFAULT \'n\' NOT NULL'), $db, $verbose);

			DBHelper::reportProgress("   ... done!\n",$verbose);
 	}
 	
 	public function clearGardeningIssues($bot_id = NULL, Title $t = NULL, $gi_type = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$sqlCond = ' WHERE TRUE ';
 		if ($t != NULL) {
 			$sqlCond = ' AND p1_id = '.$t->getArticleID();
 		}
 		if ($bot_id != NULL) {
 			$sqlCond .= ' AND bot_id = '.$db->addQuotes($bot_id);
 		}
 		if ($gi_type != NULL) {
 			$sqlCond .= ' AND gi_type = '.$gi_type;
 		}
 		$db->query('DELETE FROM '.$db->tableName('smw_gardeningissues').$sqlCond);
 	}
 	
 	public function existsGardeningIssue($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $title1 = NULL, $title2 = NULL, $value = NULL) {
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
 		if ($title1 != NULL) {
 			$sqlCond[] = 'p1_title = '.$db->addQuotes($title1->getDBkey()).' AND p1_namespace = '.$title1->getNamespace();
 		}
 		if ($title2 != NULL) {
 			$sqlCond[] = 'p2_title = '.$db->addQuotes($title2->getDBkey()).' AND p2_namespace = '.$title2->getNamespace();
 		}
 		if ($value != NULL && is_numeric($value)) {
 			$sqlCond[] = 'valueint = '.$value;
 		} else if ($value != NULL) {
 			$sqlCond[] = 'value = '.$db->addQuotes($value);
 		}
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('p1_id'), $sqlCond , 'SMWGardeningIssue::existsGardeningIssue');
 		$rowsExist = $db->numRows( $res ) > 0;
 		$db->freeResult($res);
 		return $rowsExist;
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
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('gi_type', 'p1_namespace', 'p1_title', 'p2_namespace', 'p2_title', 'value', 'valueint', 'modified'), $sqlCond , 'SMWGardeningIssue::getGardeningIssuesForPairs', $sqlOptions );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$result[] = GardeningIssue::createIssue($bot_id, $row->gi_type, $row->p1_namespace, $row->p1_title, $row->p2_namespace, $row->p2_title, $row->value != NULL ? $row->value : $row->valueint, $row->modified == 'y');
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
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('gi_type', 'p1_namespace', 'p1_title', 'p2_namespace', 'p2_title', 'value', 'valueint', 'modified'), $sqlCond , 'SMWGardeningIssue::getGardeningIssues', $sqlOptions );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$result[] = GardeningIssue::createIssue($bot_id, $row->gi_type, $row->p1_namespace, $row->p1_title, $row->p2_namespace, $row->p2_title, $row->value != NULL ? $row->value : $row->valueint, $row->modified == 'y');
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
 			'p2_id' => $t2->getArticleID(), 'p2_namespace' => $t2->getNamespace(), 'p2_title' => $t2->getDBkey(), 'value' => $numeric_value ? NULL : $value, 'valueint' => $numeric_value ? intval($value) : NULL, 'modified' => 'n'));
 						
 	}
 	
 	public function addGardeningIssueAboutArticle($bot_id, $gi_type, Title $t1) {
 		$db =& wfGetDB( DB_MASTER );
 		
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => -1 ,'p2_namespace' => -1, 'p2_title' => NULL, 'value' =>  NULL, 'valueint' => NULL, 'modified' => 'n'));
 		
 	}
 	
 	public function addGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value) {
 		$db =& wfGetDB( DB_MASTER );
 		$numeric_value = is_numeric($value);
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => -1, 'p2_namespace' => -1, 'p2_title' => NULL, 'value' => $numeric_value ? NULL : $value, 'valueint' => $numeric_value ? intval($value) : NULL, 'modified' => 'n'));
 	}
 	
 	public function setGardeningIssueToModified(Title $t) {
 		$db =& wfGetDB( DB_MASTER );
 		$db->update($db->tableName('smw_gardeningissues'), array('modified' => 'y'), array('p1_id' => $t->getArticleID()));
 	}
 	
 	public function generatePropagationIssuesForCategories($botID, $propagationType) {
 		$this->clearGardeningIssues($botID, NULL, $propagationType);
 		$db =& wfGetDB( DB_MASTER );
 		
 		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$smw_gardeningissues = $db->tableName('smw_gardeningissues');
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_relations = $db->tableName('smw_nary_relations');
				
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_prop_gardissues ( id INT(8) UNSIGNED NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableForPropagationIssues' );
		$db->query( 'CREATE TEMPORARY TABLE smw_prop_gardissues_to (id INT(8) UNSIGNED NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableForPropagationIssues' );
		$db->query( 'CREATE TEMPORARY TABLE smw_prop_gardissues_from ( id INT(8) UNSIGNED NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableForPropagationIssues' );
		
		// initialize with:
		// 1. All (super-/member-)categories of articles having issues with instances or categories
		// 2. All domain categories of property articles having issues. 
		$domainRangePropertyText = smwfGetSemanticStore()->domainRangeHintRelation->getDBkey();             
		$db->query('INSERT INTO smw_prop_gardissues (SELECT DISTINCT page_id AS id FROM '.$page.' ' .
						'JOIN '.$categorylinks.' ON page_title = cl_to ' .
						'JOIN '.$smw_gardeningissues.' ON p1_id = cl_from ' .
						'WHERE page_namespace = 14 AND (p1_namespace = 0 OR p1_namespace = 14) AND bot_id = '.$db->addQuotes($botID).')');
		$db->query('INSERT INTO smw_prop_gardissues (SELECT DISTINCT page_id AS id FROM '.$smw_nary.' n ' .
						'JOIN '.$smw_gardeningissues.' ON p1_title = subject_title AND p1_namespace = subject_namespace ' .
						'JOIN '.$page.' ON page_title = object_title ' .
						'JOIN '.$smw_nary_relations.' r  ' .
						'WHERE nary_pos = 0 AND attribute_title = '.$db->addQuotes($domainRangePropertyText).' AND p1_namespace = '.SMW_NS_PROPERTY.' ' .
								'AND object_namespace = '.NS_CATEGORY.' AND page_namespace = '.NS_CATEGORY.' AND bot_id = '.$db->addQuotes($botID).')');
		$db->query('INSERT INTO smw_prop_gardissues_from (SELECT * FROM smw_prop_gardissues)');
		
		// maximum iteration length is maximum category tree depth.
		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		do  {
			$maxDepth--;
			$db->query('INSERT INTO smw_prop_gardissues_to (SELECT DISTINCT page_id AS id FROM '.$categorylinks.' JOIN '.$page.' ON page_title = cl_to WHERE page_namespace = 14 AND cl_from IN (SELECT id FROM smw_prop_gardissues_from))');
			$db->query('INSERT INTO smw_prop_gardissues (SELECT * FROM smw_prop_gardissues_to)');
		
			$db->query('TRUNCATE TABLE smw_prop_gardissues_from');
			$db->query('INSERT INTO smw_prop_gardissues_from (SELECT * FROM smw_prop_gardissues_to)');
			
			// check if there is at least one more new ID. If not, all issues have been propagated to the root level.
			$res = $db->query('SELECT * FROM smw_prop_gardissues_to LIMIT 1');
			$nextLevelNotEmpty = $db->numRows( $res ) > 0;
			$db->freeResult($res);
			
			$db->query('TRUNCATE TABLE smw_prop_gardissues_to');
			
		} while ($nextLevelNotEmpty && $maxDepth > 0);
		
		// add propagated issues
		$res = $db->query('SELECT DISTINCT id FROM smw_prop_gardissues');
		$results = array();
		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$t = Title::newFromID($row->id);
				$this->addGardeningIssueAboutArticle($botID, $propagationType, $t);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		
		// drop virtual tables
		$db->query('DROP TABLE smw_prop_gardissues');
		$db->query('DROP TABLE smw_prop_gardissues_to');
		$db->query('DROP TABLE smw_prop_gardissues_from');
		return $results;
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
