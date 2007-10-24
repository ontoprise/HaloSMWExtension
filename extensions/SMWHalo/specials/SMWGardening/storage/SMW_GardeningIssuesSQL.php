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
				  'id'				=>	'INT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
				  'bot_id'			=>  'VARCHAR(32) NOT NULL '.$collation,
				  'gi_type'      	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'gi_class'      	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'p1_id'			=>  'INT(8) UNSIGNED',
				  'p1_namespace'	=>	'INTEGER' ,
				  'p1_title'  		=> 	'VARCHAR(255) '.$collation,
				  'p2_id'			=>  'INT(8) UNSIGNED',
				  'p2_namespace'	=>	'INTEGER' ,
				  'p2_title'  		=> 	'VARCHAR(255) '.$collation,
				  'value'			=>	'VARCHAR(255) '.$collation), $db, $verbose);

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
 	
 	public function getGardeningIssues($bot_id, $options, $gi_class = NULL, Title $t1 = NULL) {
 		global $registeredBots;
 		$db =& wfGetDB( DB_MASTER );
 		
		$sqlOptions = array('LIMIT' => $options->limit, 'OFFSET' => $options->offset );
 		$sqlCond = array();
 		$sqlCond[] = 'bot_id = '.$db->addQuotes($bot_id);
 		if ($t1 != NULL) {
 			$sqlCond[] = 'p1_namespace = '.$t1->getNamespace();
 			$sqlCond[] = 'p1_title = '.$db->addQuotes($t1->getDBkey());
 		} else {
 			$sqlCond[] = ('bot_id = '.$db->addQuotes($bot_id));
 		}
 		if ($gi_class != NULL) {
 			$sqlCond[] = 'gi_class = '.$gi_class;
 		}
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('gi_type', 'p1_namespace', 'p1_title', 'p2_namespace', 'p2_title', 'value'), $sqlCond , 'SMWGardeningIssue::getGardeningIssues', $sqlOptions );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{	
				$issueClassName = get_class($registeredBots[$bot_id])."Issue";
				$result[] = new $issueClassName($bot_id, $row->gi_type, $row->p1_namespace, $row->p1_title, $row->p2_namespace, $row->p2_title, $row->value);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function addGardeningIssueAboutArticles($bot_id, $gi_type, Title $t1, Title $t2, $value = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type, 'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => $t2->getArticleID(), 'p2_namespace' => $t2->getNamespace(), 'p2_title' => $t2->getDBkey(), 'value' => $value));
 						
 	}
 	
 	public function addGardeningIssueAboutArticle($bot_id, $gi_type, Title $t1) {
 		$db =& wfGetDB( DB_MASTER );
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => -1 ,'p2_namespace' => -1, 'p2_title' => NULL, 'value' => NULL));
 		
 	}
 	
 	public function addGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value) {
 		$db =& wfGetDB( DB_MASTER );
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p1_id' => -1, 'p2_namespace' => -1, 'p2_title' => NULL, 'value' => $value));
 	}
 	
 	public function updateGardeningIssueAboutArticles($bot_id, $gi_type, Title $t1, Title $t2, $value = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$success = $db->update($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_id' => $t2->getArticleID(), 'p2_namespace' => $t2->getNamespace(), 'p2_title' => $t2->getDBkey(), 'value' => $value), array('p1_title' => $t1->getDBkey(), 'p2_title' => $t2->getDBkey()));
 		if (!$success) $this->addGardeningIssueAboutArticles($bot_id, $gi_type, $t1, $t2, $value);			
 	}
 	
 	public function updateGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value) {
 		$db =& wfGetDB( DB_MASTER );
 		$success = $db->update($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type,  'gi_class' => intval($gi_type / 100), 'p1_id' => $t1->getArticleID(), 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p1_id' => -1, 'p2_namespace' => -1, 'p2_title' => NULL, 'value' => $value), array('p1_title' => $t1->getDBkey()));
 		if (!$success) $this->addGardeningIssueAboutValue($bot_id, $gi_type, $t1, $value);		
 	}
 }
?>
