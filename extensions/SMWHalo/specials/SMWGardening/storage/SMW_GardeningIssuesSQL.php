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
 
 class SMWGardeningIssuesSQL extends SMWGardeningIssues {
 	
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
 			$sqlCond .= ' AND bot_id = '.$bot_id;
 		}
 		$db->query('DELETE FROM '.$db->tableName('smw_gardeningissues').$sqlCond);
 	}
 	
 	public function getGardeningIssues($bot_id, Title $t1 = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		if ($t1 != NULL) {
 			$sqlCond = array('bot_id = '.$db->addQuotes($bot_id), 'p1_namespace = '.$t1->getNamespace(), 'p1_title = '.$db->addQuotes($t1->getDBkey()));
 		} else {
 			$sqlCond = array('bot_id = '.$db->addQuotes($bot_id));
 		}
 		$res = $db->select($db->tableName('smw_gardeningissues'), array('gi_type', 'p1_namespace', 'p1_title', 'p2_namespace', 'p2_title', 'value'), $sqlCond , 'SMWGardeningIssue::getGardeningIssues' );
 		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{
				$result[] = new GardeningIssue($row->gi_type, $row->p1_namespace, $row->p1_title, $row->p2_namespace, $row->p2_title, $row->value);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function addGardeningIssueAboutArticles($bot_id, $gi_type, Title $t1, Title $t2, $value = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type, 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			'p2_namespace' => $t1->getNamespace(), 'p2_title' => $t1->getDBkey(), 'value' => $value));
 						
 	}
 	
 	public function addGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value) {
 		$db->insert($db->tableName('smw_gardeningissues'), array('bot_id' => $bot_id, 'gi_type' => $gi_type, 'p1_namespace' => $t1->getNamespace(), 'p1_title' => $t1->getDBkey(),
 			NULL, NULL, 'value' => $value));
 	}
 	
 	
 }
?>
