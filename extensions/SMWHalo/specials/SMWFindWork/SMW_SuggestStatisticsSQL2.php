<?php
if ( !defined( 'MEDIAWIKI' ) ) die;

 global $smwgHaloIP;
 require_once($smwgHaloIP . '/specials/SMWFindWork/SMW_SuggestStatisticsSQL.php');
   
 class SMWSuggestStatisticsSQL2 extends SMWSuggestStatisticsSQL {
 	
 public function getLowRatedAnnotations($username, $requestoptions) {
        $db =& wfGetDB( DB_SLAVE );
        
        $smw_atts2 = $db->tableName('smw_atts2');     
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
        $revision = $db->tableName('revision');
        $categorylinks = $db->tableName('categorylinks');
        $page = $db->tableName('page');
        
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
           
            $res = $db->query(  'INSERT INTO smw_fw_lowratedannotations (title, namespace, property, value, type, rating) '.
					             '(SELECT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS property, value_xsd AS value, \'string\' AS type, rating AS rt '.
					             ' FROM '.smw_atts2. 
					             ' JOIN '.$smw_ids.' i ON i.smw_id = s_id '.
					             ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id '.
					             ' WHERE rating < 0) ' .
                                'UNION ' .
                                 '(SELECT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS property, i3.smw_title AS value, i3.smw_namespace AS type, rating AS rt '.
                                 ' FROM '.smw_rels2. 
                                 ' JOIN '.$smw_ids.' i ON i.smw_id = s_id '.
                                 ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id ' .
                                 ' JOIN '.$smw_ids.' i3 ON i3.smw_id = o_id '.
                                 ' WHERE rating < 0) ' .
                                'ORDER BY rt DESC LIMIT '.$requestoptions->limit);
        } else {
            // look for low rated annotations of articles in edit history
            $res = $db->query(  'INSERT INTO smw_fw_lowratedannotations (title, namespace, property, value, type, rating) '.
                                 '(SELECT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS property, value_xsd AS value, \'string\' AS type, rating AS rt '.
                                 ' FROM '.smw_atts2. 
                                 ' JOIN '.$smw_ids.' i ON i.smw_id = s_id '.
                                 ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id '.
                                 ' JOIN '.$page.' ON i.smw_title = page_title AND i.smw_namespace = page_namespace '.
                                 ' JOIN '.$revision.' ON page_id = rev_page '.
                                 ' WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ') ' .
                                'UNION ' .
                                 '(SELECT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS property, i3.smw_title AS value, i3.smw_namespace AS type, rating AS rt '.
                                 ' FROM '.smw_rels2. 
                                 ' JOIN '.$smw_ids.' i ON i.smw_id = s_id '.
                                 ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id ' .
                                 ' JOIN '.$smw_ids.' i3 ON i3.smw_id = o_id '.
                                 ' JOIN '.$page.' ON i.smw_title = page_title AND i.smw_namespace = page_namespace '.
                                 ' JOIN '.$revision.' ON page_id = rev_page '.
                                 ' WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ') ' .
                                $sql_options);
                                    
            // check if there are already any results
            $num = $db->query('SELECT COUNT(*) AS num FROM smw_fw_lowratedannotations');
            
            if($db->fetchObject($num)->num == 0) {
                // if there are no results, consider low rated annotations of articles from same category as articles in edit history
                $db->freeResult($num);
                $requestoptions->limit /= 2;
                
                $this->createVirtualTableForCategoriesOfLastEditedPages($username, $db);
                
                $res = $db->query(  'INSERT INTO smw_fw_lowratedannotations (title, namespace, property, value, type, rating) '.
                                 '(SELECT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS property, value_xsd AS value, \'string\' AS type, rating AS rt '.
                                 ' FROM '.smw_atts2. 
                                 ' JOIN '.$smw_ids.' i ON i.smw_id = s_id '.
                                 ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id '.
                                 ' JOIN '.$page.' ON i.smw_title = page_title AND i.smw_namespace = page_namespace '.
                                 ' JOIN '.$revision.' ON page_id = rev_page '.
                                 ' JOIN '.$categorylinks.' ON page_id = cl_from ' .
                                 ' WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ' AND cl_to IN (SELECT category FROM smw_fw_categories)) ' .
                                'UNION ' .
                                 '(SELECT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS property, i3.smw_title AS value, i3.smw_namespace AS type, rating AS rt '.
                                 ' FROM '.smw_rels2. 
                                 ' JOIN '.$smw_ids.' i ON i.smw_id = s_id '.
                                 ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id ' .
                                 ' JOIN '.$smw_ids.' i3 ON i3.smw_id = o_id '.
                                 ' JOIN '.$page.' ON i.smw_title = page_title AND i.smw_namespace = page_namespace '.
                                 ' JOIN '.$revision.' ON page_id = rev_page '.
                                 ' JOIN '.$categorylinks.' ON page_id = cl_from ' .
                                 ' WHERE rating < 0 AND rev_user_text = '.$db->addQuotes($username). ' AND cl_to IN (SELECT category FROM smw_fw_categories)) ' .
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
        $db->query('DROP TEMPORARY TABLE smw_fw_lowratedannotations');
        return $result;
    }
 }
?>