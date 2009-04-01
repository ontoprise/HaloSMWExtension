<?php
if ( !defined( 'MEDIAWIKI' ) ) die;

global $sgagIP;
require_once($sgagIP . '/includes/findwork/SGA_SuggestStatisticsSQL.php');

class SMWSuggestStatisticsSQL2 extends SMWSuggestStatisticsSQL {

	public function getLowRatedAnnotations($username, $requestoptions) {
		$db =& wfGetDB( DB_SLAVE );

		$smw_atts2 = $db->tableName('smw_atts2');
		$smw_rels2 = $db->tableName('smw_rels2');
		$smw_ids = $db->tableName('smw_ids');
		$revision = $db->tableName('revision');
		$categorylinks = $db->tableName('categorylinks');
		$page = $db->tableName('page');

		$sql_options = SGADBHelper::getSQLOptionsAsString($requestoptions,'rt');
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
	
// Annotations

    public function rateAnnotation($subject, $predicate, $object, $rating) {
        $db =& wfGetDB( DB_MASTER );

        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');

        $subject = $db->selectRow($smw_ids, 'smw_id', array('smw_title' => $subject, 'smw_namespace' => NS_MAIN));
        $predicate = $db->selectRow($smw_ids, 'smw_id', array('smw_title' => $predicate, 'smw_namespace' => SMW_NS_PROPERTY));
        if ($subject !== false && $predicate !== false) {
            $res = $db->selectRow($smw_atts2, 'rating', array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'value_xsd' => $object));
            if ($res !== false) {
                $db->update($smw_atts2, array('rating' => (is_numeric($res->rating) ? $res->rating : 0) + $rating), array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'value_xsd' => $object));
            } else {
                $object = $db->selectRow($smw_ids, 'smw_id', array('smw_title' => $object));
                if ($object !== false) {
                    $res = $db->selectRow($smw_rels2, 'rating', array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'o_id' => $object->smw_id));

                    if ($res !== false && $subject !== false && $predicate !== false && $object !== false) {
                        $db->update($smw_rels2, array('rating' => (is_numeric($res->rating) ? $res->rating : 0) + $rating), array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'o_id' => $object->smw_id));
                    }
                }
            }
        }
    }

    public function getRatedAnnotations($subject) {
        $db =& wfGetDB( DB_SLAVE );

        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
            

        $res = $db->query('SELECT i.smw_title AS attribute_title, value_xsd, rating FROM '.$smw_atts2.' JOIN '.$smw_ids.' i ON p_id = i.smw_id JOIN '.$smw_ids.' i2 ON s_id = i2.smw_id WHERE i2.smw_title = '.$db->addQuotes($subject->getDBkey()).' AND i2.smw_namespace = '.$subject->getNamespace());
        $res2 = $db->query('SELECT i.smw_title AS relation_title, i2.smw_title AS object_title, rating FROM '.$smw_rels2.' JOIN '.$smw_ids.' i ON p_id = i.smw_id JOIN '.$smw_ids.' i2 ON o_id = i2.smw_id WHERE i2.smw_title = '.$db->addQuotes($subject->getDBkey()).' AND i2.smw_namespace = '.$subject->getNamespace());

        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {

                $result[] = array($row->attribute_title, $row->value_xsd, $row->rating);
            }
        }
        if($db->numRows( $res2 ) > 0) {
            while($row = $db->fetchObject($res2)) {
                $result[] = array($row->relation_title, $row->object_title, $row->rating);
            }
        }
        $db->freeResult($res);
        $db->freeResult($res2);
        return $result;
    }

    public function getAnnotationsForRating($limit, $unrated = true) {
        $db =& wfGetDB( DB_SLAVE );

        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');

        if ($unrated) $where = 'WHERE rating IS NULL'; else $where = 'WHERE rating IS NOT NULL';

        // get random offsets
        $offset_result = $db->query( " SELECT FLOOR(RAND() * COUNT(*)) AS offset FROM $smw_atts2 ");
        $offset_row = $db->fetchObject( $offset_result );
        $offsetAtt = $offset_row->offset;
        $db->freeResult($offset_result);

        $offset_result = $db->query( " SELECT FLOOR(RAND() * COUNT(*)) AS offset FROM $smw_rels2 ");
        $offset_row = $db->fetchObject( $offset_result );
        $offsetRel = $offset_row->offset;
        $db->freeResult($offset_result);
            
        $res = $db->query('(SELECT i.smw_title AS subject, i2.smw_title AS predicate, value_xsd AS object FROM '.$smw_atts2. ' JOIN '.$smw_ids.' i ON s_id=i.smw_id JOIN '.$smw_ids.' i2 ON p_id=i2.smw_id '. $where.') ' .
                            'UNION ' .
                           '(SELECT i.smw_title AS subject, i2.smw_title AS predicate, i3.smw_title AS object FROM '.$smw_rels2.' JOIN '.$smw_ids.' i ON s_id=i.smw_id JOIN '.$smw_ids.' i2 ON p_id=i2.smw_id JOIN '.$smw_ids.' i3 ON o_id=i3.smw_id '. $where.' AND i.smw_iw != ":smw") LIMIT '.$limit.' OFFSET '.($offsetAtt+$offsetRel));
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array($row->subject, $row->predicate, $row->object);
            }
        }
        $db->freeResult($res);
        return $result;
    }

	function setup($verbose = true) {
		global $wgDBtype;
		SGADBHelper::reportProgress("Altering tables for Semantic Gardening if necessary ...\n\n",$verbose);
		if ($wgDBtype === 'postgres') {
			$this->reportProgress("Postgres is not supported by SMW+",$verbose);
			return;
		}
		$db =& wfGetDB( DB_MASTER );
		$smw_relations = $db->tableName('smw_rels2');
		$smw_attributes = $db->tableName('smw_atts2');

		if (!$this->isColumnPresent($smw_relations, 'rating')) {
			SGADBHelper::reportProgress("Altering $smw_relations...\n",$verbose);
			SGADBHelper::reportProgress("\t... adding column rating\n",$verbose);
			$db->query("ALTER TABLE $smw_relations ADD `rating` INT(8) FIRST", 'SMWHaloStore::setupTable');
			SGADBHelper::reportProgress("done \n",$verbose);
		}

		if (!$this->isColumnPresent($smw_attributes, 'rating')) {
			SGADBHelper::reportProgress("Altering $smw_attributes...\n",$verbose);
			SGADBHelper::reportProgress("\t... adding column rating\n",$verbose);
			$db->query("ALTER TABLE $smw_attributes ADD `rating` INT(8) FIRST", 'SMWHaloStore::setupTable');
			SGADBHelper::reportProgress("done \n",$verbose);
		}

		SGADBHelper::reportProgress("Database initialised for Semantic Gardening successfully.\n",$verbose);

	}

	function isColumnPresent($table, $column) {
		$db =& wfGetDB( DB_MASTER );

		$ratingPresent = false;
		$res = $db->query( 'DESCRIBE ' . $table, 'SMWSQLStore::setupTable' );

		while ($row = $db->fetchObject($res)) {
			$field = $row->Field;
			if ($field == $column) {
				$ratingPresent = true;
			}
		}
		$db->freeResult($res);
		return $ratingPresent;
	}
}
?>