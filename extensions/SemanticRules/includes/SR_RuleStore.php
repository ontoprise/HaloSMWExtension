<?php
/**
 * @file
 * @ingroup SemanticRules
 *
 * Provides access to local rule store.
 *
 * @author: Kai K�hn / ontoprise / 2009
 *
 */


class SRRuleStore extends SMWRuleStore {


	/**
	 * Returns rule from local rule store for a given page id.
	 *
	 * @param int $page_id
	 * @return array of rule_id
	 */
	public function getRules($page_id) {
		$db =& wfGetDB( DB_SLAVE );

		$ruleTableName = $db->tableName('smw_rules');
		$res = $db->select($ruleTableName, array('rule_id'), array('subject_id' => $page_id));
		$results = array();

		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$results[] = $row->rule_id;
			}
		}
		$db->freeResult($res);
		return $results;
	}
	
	public function getAllRulePages() {
		$db =& wfGetDB( DB_SLAVE );

        $ruleTableName = $db->tableName('smw_rules');
        $res = $db->select($ruleTableName, array('subject_id'), array());
        $results = array();

        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $results[] = $row->subject_id;
            }
        }
        $db->freeResult($res);
        return $results;
	}
	
     /**
     * Returns true if the rule already exists, false otherwise.
     * Returns also date of last change.
     *
     * @param tuple $rule ($ruleID, $ruletext, $native, $active, $type)
     * @return tuple (true/false, last_changeDate)
     */
    public function existsRule($rule) {
    	list($ruleID, $ruletext, $native, $active, $type) = $rule;
    	$native = $native ? "true" : "false";
    	$active = $active ? "true" : "false";
    	
        $db =& wfGetDB( DB_SLAVE );

        $ruleTableName = $db->tableName('smw_rules');
        $res = $db->select($ruleTableName, array('rule_id', 'rule_text', 'last_changed'), array('rule_id' => $ruleID, 'is_native'=>$native, 'is_active'=>$active, 'type'=>$type));
        $results = array();

        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
            	// should be only one, otherwise something is wrong
                if ($row->rule_text == $ruletext) return array(true, $row->last_changed);
            }
        }
        $db->freeResult($res);
        return array(false, NULL);
    }

	/**
	 * Adds new rules to the local rule store.
	 *
	 * @param int $article_id
	 * @param array $new_rules (ruleID => ruleText)
	 */
	public function addRules($article_id, $new_rules) {

		$db =& wfGetDB( DB_MASTER );
		$smw_rules = $db->tableName('smw_rules');
		foreach($new_rules as $rule) {
			list($rule_id, $ruleText, $native, $active, $type, $changeDate, $tsc_uri) = $rule;
			$currentDate = getDate();
			if (is_null($changeDate)) {
			$dateAsSQLString = $currentDate["year"].
					"-".$currentDate["mon"].
					"-".$currentDate["mday"].
					" ".$currentDate["hours"].
					":".$currentDate["minutes"].
					":".$currentDate["seconds"];
			} else {
				$dateAsSQLString = $changeDate;
			}
			$db->insert($smw_rules, array('subject_id' => $article_id,
										  'rule_id' => $rule_id, 
			                              'tsc_uri' => $tsc_uri, 
										  'rule_text' => $ruleText, 
										  'is_native' => $native ? "true" : "false", 
										  'is_active' => $active ? "true" : "false", 
										  'type' => $type, 
										  'last_changed' => $dateAsSQLString));
		}
	}

	/**
	 * Removes rule from given article
	 *
	 * @param int $article_id
	 */
	public function clearRules($article_id) {

		$db =& wfGetDB( DB_MASTER );
		$smw_rules = $db->tableName('smw_rules');
		$db->delete($smw_rules, array('subject_id' => $article_id));
	}

	/**
	 * Updates article IDs and rule IDs (=URIs) . In case of a renaming operation.
	 *
	 * @param int $new_article_id
	 * @param int $old_article_id
	 * @param Title $newtitle
	 * 
	 * @return tuple(old rule URI, new rule URI)
	 */
	public function updateRules($new_article_id, $old_article_id, $newTitle) {
		$db =& wfGetDB( DB_MASTER );
		$smw_rules = $db->tableName('smw_rules');
		$modifiedRules = array();
		$rules = $this->getRules($old_article_id);
	
		// update page id
		$db->update($smw_rules, array('subject_id' => $new_article_id), array('subject_id' => $old_article_id));
		
		// update rule IDs
		$uri = TSNamespaces::getInstance()->getFullURI($newTitle);
		foreach($rules as $old_ruleID) {
			list($containedPage, $localname) = explode("$$",$old_ruleID);
			$new_ruleID = $uri."$$".$localname;
			$db->update($smw_rules, array('rule_id' => $new_ruleID), array('rule_id' => $old_ruleID));
			$modifiedRules[] = array($old_ruleID, $new_ruleID);
		}
		return $modifiedRules;
	}

	public function setup($verbose) {
		// add rule storage
		global $smwgHaloIP;
		if (!class_exists("DBHelper")) {
			require_once($smwgHaloIP.'/includes/SMW_DBHelper.php');
		}
		DBHelper::reportProgress("   ... Creating SemanticRules tables \n",$verbose);
		$db =& wfGetDB( DB_MASTER );

		$ruleTableName = $db->tableName('smw_rules');
		// create rule table
		DBHelper::setupTable($ruleTableName,
		array('subject_id'    => 'INT(8) UNSIGNED NOT NULL',
                            'rule_id'       => 'VARCHAR(255) binary NOT NULL',
		                    'tsc_uri'       => 'VARCHAR(255) binary',
                            'rule_text'      => 'TEXT NOT NULL',
		                    'is_native'      => 'enum(\'false\', \'true\')',
							'is_active'      => 'enum(\'false\', \'true\')',
							'type'       => 'VARCHAR(255) binary',
							'last_changed'      => 'DATETIME'), $db, $verbose);
	}

	public function drop($verbose) {
		global $wgDBtype;
		global $smwgHaloIP;
		if (!class_exists("DBHelper")) {
			require_once($smwgHaloIP.'/includes/SMW_DBHelper.php');
		}
		DBHelper::reportProgress("Deleting all database content and tables generated by SemanticRules ...\n\n",$verbose);
		$db =& wfGetDB( DB_MASTER );
		$tables = array('smw_rules');
		foreach ($tables as $table) {
			$name = $db->tableName($table);
			$db->query('DROP TABLE' . ($wgDBtype=='postgres'?'':' IF EXISTS'). $name, 'SMWSemanticStoreSQL2::drop');
			DBHelper::reportProgress(" ... dropped table $name.\n", $verbose);
		}
	}
}

