<?php
/*
 * Created on 26.11.2007
 *
 * Author: kai
 */
 global $smwgHaloIP;
 require_once($smwgHaloIP . '/specials/SMWFindWork/SMW_SuggestStatistics.php');
 
 class SMWSuggestStatisticsSQL extends SMWSuggestStatistics {
 	
 	public function getLastEditedPages($botID, $gi_class, $gi_type, $username, $requestoptions) {
 		$db =& wfGetDB( DB_MASTER );
 		$page = $db->tableName('page');
 		$revision = $db->tableName('revision');
 		$smw_gardeningissues = $db->tableName('smw_gardeningissues');
 		
 		$requestoptions->ascending = false;
 		$sql_options =  DBHelper::getSQLOptionsAsString($requestoptions,'lastrevision');
 		
 		$sqlCond = "";
		if ($botID != NULL) {
			$sqlCond .= 'bot_id = '.$db->addQuotes($botID).' AND ';
		}
		if ($gi_class != NULL) {
			$sqlCond .= 'gi_class = '.$gi_class.' AND ';
		}
		if ($gi_type != NULL) {
			$sqlCond .= 'gi_type = '.$gi_type.' AND ';
		}
		$sqlCond .= 'TRUE';
				
		if ($username == NULL) {
				$res = $db->query('SELECT DISTINCT p1_id AS id FROM '.$smw_gardeningissues.' WHERE '.$sqlCond.' ORDER BY RAND() LIMIT '.$requestoptions->limit);
		} else {
		
	 			$res = $db->query('SELECT rev_page AS id, MAX(rev_timestamp) AS lastrevision FROM '.$revision.' JOIN '.$smw_gardeningissues.
								' ON rev_page = p1_id LEFT JOIN '.$page.' ON page_id = p1_id ' .
								' WHERE gi_type != '.SMW_GARDISSUE_CONSISTENCY_PROPAGATION.' AND page_title IS NOT NULL AND '.$sqlCond.' AND rev_user_text = '.$db->addQuotes($username).' GROUP BY rev_page '.
								$sql_options);
			
		}
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromID($row->id);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getLastEditPagesOfUndefinedCategories($username, $requestoptions) {
 		$db =& wfGetDB( DB_MASTER );
 		$categorylinks = $db->tableName('categorylinks');
 		$smw_gardeningissues = $db->tableName('smw_gardeningissues');
 		
 		$sql_options =  DBHelper::getSQLOptionsAsString($requestoptions,'title');
			
			
 		$this->createVirtualTableForCategoriesOfLastEditedPages($username, $db);
 		$res = $db->query('SELECT DISTINCT p1_title AS title, p1_namespace AS namespace FROM '.$smw_gardeningissues.
 							' WHERE gi_type = '.SMW_GARDISSUE_CATEGORY_UNDEFINED.' AND p1_title IN (SELECT category FROM smw_fw_categories) '.$sql_options);
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->title, $row->namespace);
			}
		}
		$db->freeResult($res);
		$this->dropVirtualTableForCategoriesOfLastEditedPages($db);
		return $result;
 	}
 	
 	public function getLastEditPagesOfUndefinedProperties($username, $requestoptions) {
 		$db =& wfGetDB( DB_MASTER );
 		$page = $db->tableName('page');
 		$revision = $db->tableName('revision');
 		$smw_gardeningissues = $db->tableName('smw_gardeningissues');
 		$smw_attributes = $db->tableName('smw_attributes');		
		$smw_relations = $db->tableName('smw_relations');
		$smw_nary = $db->tableName('smw_nary');
		
		$sql_options =  DBHelper::getSQLOptionsAsString($requestoptions,'title');
		
	 	$res = $db->query(	'SELECT DISTINCT attribute_title AS title FROM '.$revision.' JOIN '.$smw_attributes.
							' ON rev_page = subject_id JOIN '.$smw_gardeningissues.' ON attribute_title = p1_title ' .
							' WHERE gi_type = '.SMW_GARDISSUE_PROPERTY_UNDEFINED.' AND rev_user_text = '.$db->addQuotes($username).
						' UNION ' .
							' SELECT DISTINCT relation_title AS title FROM '.$revision.' JOIN '.$smw_relations.
							' ON rev_page = subject_id JOIN '.$smw_gardeningissues.' ON relation_title = p1_title ' .
							' WHERE gi_type = '.SMW_GARDISSUE_PROPERTY_UNDEFINED.' AND rev_user_text = '.$db->addQuotes($username).
						' UNION ' .
							'SELECT DISTINCT attribute_title AS title FROM '.$revision.' JOIN '.$smw_nary.
							' ON rev_page = subject_id JOIN '.$smw_gardeningissues.' ON attribute_title = p1_title ' .
							' WHERE gi_type = '.SMW_GARDISSUE_PROPERTY_UNDEFINED.' AND rev_user_text = '.$db->addQuotes($username).' '.
						$sql_options);
			
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->title, SMW_NS_PROPERTY);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getLastEditedPagesOfSameCategory($botID, $gi_class, $gi_type, $username, $requestoptions) {
 		$db =& wfGetDB( DB_MASTER );
 		$categorylinks = $db->tableName('categorylinks');
 		$smw_gardeningissues = $db->tableName('smw_gardeningissues');
 		
 		$requestoptions->sort = false;
 		$sql_options =  DBHelper::getSQLOptionsAsString($requestoptions,'title');
			
		$sqlCond = "";
		if ($botID != NULL) {
			$sqlCond .= 'bot_id = '.$db->addQuotes($botID).' AND ';
		}
		if ($gi_class != NULL) {
			$sqlCond .= 'gi_class = '.$db->addQuotes($gi_class).' AND ';
		}
		if ($gi_type != NULL) {
			$sqlCond .= 'gi_type = '.$db->addQuotes($gi_type).' AND ';
		}
		$sqlCond .= 'TRUE';
		
 		$this->createVirtualTableForCategoriesOfLastEditedPages($username, $db);
 		$res = $db->query('SELECT DISTINCT p1_title AS title, p1_namespace AS namespace FROM '.$smw_gardeningissues.', '.$categorylinks.' LEFT JOIN smw_fw_categories ON cl_to = category' .
 							' WHERE '.$sqlCond.' AND gi_type != '.SMW_GARDISSUE_CONSISTENCY_PROPAGATION.' AND p1_id = cl_from AND category IS NOT NULL '.$sql_options);
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->title, $row->namespace);
			}
		}
		$db->freeResult($res);
		$this->dropVirtualTableForCategoriesOfLastEditedPages($db);
		return $result;
 	}
 	
 	
 	
 	public function getLowRatedAnnotations($username, $requestoptions) {
 		$db =& wfGetDB( DB_MASTER );
 		$smw_attributes = $db->tableName('smw_attributes');		
		$smw_relations = $db->tableName('smw_relations');
		$revision = $db->tableName('revision');
		$categorylinks = $db->tableName('categorylinks');
		$sql_options = DBHelper::getSQLOptionsAsString($requestoptions,'rt');
		global $smwgDefaultCollation;
		 if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		$db->query( 'CREATE TEMPORARY TABLE smw_fw_lowratedannotations (title VARCHAR(255) '.$collation.' NOT NULL, namespace INT(11) NOT NULL, property VARCHAR(255) '.$collation.', value VARCHAR(255) '.$collation.', type VARCHAR(255) '.$collation.', rating INT(8) )
		            TYPE=MEMORY', 'SMW::getLowRatedAnnotations' );
		if ($username == NULL) {
			// look for any low rated annotations
			$res = $db->query(	'INSERT INTO smw_fw_lowratedannotations (title, namespace, property, value, type, rating)  (SELECT subject_title AS title, subject_namespace AS namespace, attribute_title AS property, value_xsd AS value, \'string\' AS type, rating AS rt FROM '.$smw_attributes. ' WHERE rating < 0) ' .
 							'UNION ' .
 								'(SELECT subject_title AS title, subject_namespace AS namespace, relation_title AS property, object_title AS value, object_namespace AS type, rating AS rt FROM '.$smw_relations. ' WHERE rating < 0) ' .
 							'ORDER BY rt DESC LIMIT '.$requestoptions->limit);
		} else {
			// look for low rated annotations of articles in edit history
 			$db->query(	'INSERT INTO smw_fw_lowratedannotations (title, namespace, property, value, type, rating) (SELECT subject_title AS title, subject_namespace AS namespace, attribute_title AS property, value_xsd AS value, \'string\' AS type, rating AS rt FROM '.$smw_attributes. ' JOIN '.$revision.' ON subject_id = rev_page ' .
 								'WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ') ' .
 							'UNION ' .
 								'(SELECT subject_title AS title, subject_namespace AS namespace, relation_title AS property, object_title AS value, object_namespace AS type, rating AS rt FROM '.$smw_relations. ' JOIN '.$revision.' ON subject_id = rev_page ' .
 								'WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ') ' .
 							$sql_options);
 							
 			// check if there are already any results
 			$num = $db->query('SELECT COUNT(*) AS num FROM smw_fw_lowratedannotations');
 			
 			if($db->fetchObject($num)->num == 0) {
 				// if there are no results, consider low rated annotations of articles from same category as articles in edit history
 				$db->freeResult($num);
 				$requestoptions->limit /= 2;
 				
 				$this->createVirtualTableForCategoriesOfLastEditedPages($username, $db);
 				
 				$db->query(	'INSERT INTO smw_fw_lowratedannotations (title, namespace, property, value, type, rating) SELECT subject_title AS title, subject_namespace AS namespace, attribute_title AS property, value_xsd AS value, \'string\' AS type, rating AS rt FROM '.$smw_attributes.
 									 ' JOIN '.$revision.' ON subject_id = rev_page JOIN '.$categorylinks.' ON subject_id = cl_from ' .
 								'WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ' AND cl_to IN (SELECT category FROM smw_fw_categories) '.
 							$sql_options);
 							
 				$db->query(	'INSERT INTO smw_fw_lowratedannotations (title, namespace, property, value, type, rating) SELECT subject_title AS title, subject_namespace AS namespace, relation_title AS property, object_title AS value, object_namespace AS type, rating AS rt FROM '.$smw_relations.
 								 ' JOIN '.$revision.' ON subject_id = rev_page JOIN '.$categorylinks.' ON subject_id = cl_from ' .
 								'WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ' AND cl_to IN (SELECT category FROM smw_fw_categories) '.
 							$sql_options);
 				$this->dropVirtualTableForCategoriesOfLastEditedPages($db);
 				
 				
 				
 			}
		}
		$res = $db->query('SELECT * FROM smw_fw_lowratedannotations GROUP BY title, namespace');	
 		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(Title::newFromText($row->title, $row->namespace), Title::newFromText($row->property, SMW_NS_PROPERTY), $row->type == 'string' ? $row->value : Title::newFromText($row->value, $row->type));
			}
		}
		$db->freeResult($res);
		$db->query('DROP TABLE smw_fw_lowratedannotations');
		return $result;
 	}
 	
 	/**
 	 * Creates a temporary table called 'smw_fw_categories' which contains all categories of 
 	 * articles the user $username has edited.
 	 * 
 	 * @param $username
 	 * @param & $db
 	 */
 	private function createVirtualTableForCategoriesOfLastEditedPages($username, & $db) {
 		
 		$revision = $db->tableName('revision');
 		$pages = $db->tableName('page');
 		$categorylinks = $db->tableName('categorylinks');
 		
 		
 		global $smwgDefaultCollation;
 		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		$db->query( 'CREATE TEMPORARY TABLE smw_fw_categories (category VARCHAR(255) '.$collation.' NOT NULL, lastrevision VARCHAR(14) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::createVirtualTableForCategoriesOfLastEditedPages' );
		
		
 		
		$db->query('INSERT INTO smw_fw_categories (category, lastrevision) SELECT cl_to, MAX(rev_timestamp) AS lastrevision FROM '.$revision.' JOIN '.$categorylinks.
							' ON rev_page = cl_from WHERE rev_user_text = '.$db->addQuotes($username).' GROUP BY cl_to ORDER BY lastrevision DESC');
	
 	}
 	
 	/**
 	 * Drops the temporary table 'smw_fw_categories'.
 	 */
 	private function dropVirtualTableForCategoriesOfLastEditedPages(& $db) {
		$db->query('DROP TABLE smw_fw_categories');
 	}
 	
 	
 }
?>
