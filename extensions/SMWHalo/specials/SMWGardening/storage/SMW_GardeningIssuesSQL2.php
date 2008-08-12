<?php

class SMWGardeningIssuesAccessSQL2 extends SMWGardeningIssuesAccessSQL {
    public function generatePropagationIssuesForCategories($botID, $propagationType) {
        $this->clearGardeningIssues($botID, $propagationType);
        $db =& wfGetDB( DB_SLAVE );
        
        $page = $db->tableName('page');
        $categorylinks = $db->tableName('categorylinks');
        $smw_gardeningissues = $db->tableName('smw_gardeningissues');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
                
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
        
        $domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()) );
        if ($domainAndRange == NULL) {
            $domainAndRangeID = -1; // does never exist
        } else {
            $domainAndRangeID = $domainAndRange->smw_id;
        }
        $results = array();
              
        $db->query('INSERT INTO smw_prop_gardissues (SELECT DISTINCT page_id AS id FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id'. 
                                                    ' JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                                                    ' JOIN '.$page.' ON page_title = r.smw_title AND page_namespace = '.NS_CATEGORY.
                                                    ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = 0 '.
                                                        ' AND n.s_id IN (SELECT smw_id FROM '.$smw_ids.' JOIN '.$page.' ON page_title = smw_title AND page_namespace = smw_namespace JOIN '.$smw_gardeningissues.' ON page_id = p1_id AND page_namespace = '.SMW_NS_PROPERTY.
                                                                        ' WHERE bot_id = '.$db->addQuotes($botID).'))');
        
       
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
        $db->query('DROP TEMPORARY TABLE smw_prop_gardissues');
        $db->query('DROP TEMPORARY TABLE smw_prop_gardissues_to');
        $db->query('DROP TEMPORARY TABLE smw_prop_gardissues_from');
        return $results;
    }
}
?>