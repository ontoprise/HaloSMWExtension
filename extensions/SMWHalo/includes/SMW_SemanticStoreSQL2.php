<?php

if ( !defined( 'MEDIAWIKI' ) ) die;

global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/SMW_SemanticStoreSQL.php" );

class SMWSemanticStoreSQL2 extends SMWSemanticStoreSQL {
    function getRootProperties($requestoptions = NULL) {
        
        $result = "";
        $db =& wfGetDB( DB_SLAVE );
        $smw_ids = $db->tableName('smw_ids');
        $smw_subs2 = $db->tableName('smw_subs2');
        $page = $db->tableName('page');
                
        $res = $db->query('SELECT page_title FROM '.$page.' JOIN '.$smw_ids.' ON page_title=smw_title AND page_namespace = '.SMW_NS_PROPERTY.
        ' AND page_is_redirect = 0 AND NOT EXISTS (SELECT s.s_id FROM '.$smw_subs2.' s WHERE s.s_id = smw_id)');

        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = Title::newFromText($row->page_title, SMW_NS_PROPERTY);
            }
        }
        $db->freeResult($res);
        return $result;
    }
    
    function getDirectSubProperties(Title $attribute, $requestoptions = NULL) {
        
        $result = "";
        $db =& wfGetDB( DB_SLAVE );
         $smw_ids = $db->tableName('smw_ids');
        $smw_subs2 = $db->tableName('smw_subs2');
        $page = $db->tableName('page');
        
            
        $res = $db->query('SELECT s.smw_title AS subject_title FROM '.$smw_ids.' o JOIN '.$smw_subs2.' sub ON o.smw_id = sub.s_id JOIN '.$smw_ids.' s ON s.smw_id = sub.o_id '.
        ' AND s.smw_namespace = '.SMW_NS_PROPERTY. ' AND o.smw_namespace = '.SMW_NS_PROPERTY. ' AND o.smw_title = ' . $db->addQuotes($attribute->getDBkey()));
         
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = Title::newFromText($row->subject_title, SMW_NS_PROPERTY);
                
            }
        }
        $db->freeResult($res);
        return $result;
    }
    
    function getDirectSuperProperties(Title $attribute, $requestoptions = NULL) {
            
        $result = "";
        $db =& wfGetDB( DB_SLAVE );
         $smw_ids = $db->tableName('smw_ids');
        $smw_subs2 = $db->tableName('smw_subs2');
        $page = $db->tableName('page');
        
            
        $res = $db->query('SELECT o.smw_title AS subject_title FROM '.$smw_ids.' o JOIN '.$smw_subs2.' sub ON o.smw_id = sub.s_id JOIN '.$smw_ids.' s ON s.smw_id = sub.o_id '.
        ' AND s.smw_namespace = '.SMW_NS_PROPERTY. ' AND o.smw_namespace = '.SMW_NS_PROPERTY. ' AND s.smw_title = ' . $db->addQuotes($attribute->getDBkey()));
         
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = Title::newFromText($row->subject_title, SMW_NS_PROPERTY);
                
            }
        }
        $db->freeResult($res);
        return $result;
    }
    
	protected function createVirtualTableWithPropertiesByCategory(Title $categoryTitle, & $db) {
		global $smwgDefaultCollation;

		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$smw_ids = $db->tableName('smw_ids');
		$smw_rels2 = $db->tableName('smw_rels2');

		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties (id INT(8) NOT NULL, property VARCHAR(255) '.$collation.')
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );

		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_sub (category INT(8) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_super (category INT(8) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
        
		$domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => $this->domainRangeHintRelation->getDBkey()) );
	    if ($domainAndRange == NULL) {
            $domainAndRangeID = -1; // does never exist
        } else {
            $domainAndRangeID = $domainAndRange->smw_id;
        }
		$category = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => $categoryTitle->getDBkey(), 'smw_namespace' => $categoryTitle->getNamespace() ) );
		if ($category == NULL) {
			$categoryID = -1; // does never exist
		} else {
			$categoryID = $category->smw_id;
		}
		$db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = 0 AND r.smw_id = '.$categoryID.')');
		
		
		$db->query('INSERT INTO smw_ob_properties_sub VALUES ('.$db->addQuotes($categoryID).')');

		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum category tree depth.
		do  {
			$maxDepth--;

			// get next supercategory level
			$db->query('INSERT INTO smw_ob_properties_super (SELECT DISTINCT page_id AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_title = cl_to WHERE page_namespace = '.NS_CATEGORY.' AND cl_from IN (SELECT * FROM smw_ob_properties_sub))');

			// insert direct properties of current supercategory level
            $db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = 0 AND r.smw_id IN (SELECT * FROM smw_ob_properties_super))');
			
            		
			// copy supercatgegories to subcategories of next iteration
			$db->query('DELETE FROM smw_ob_properties_sub');
			$db->query('INSERT INTO smw_ob_properties_sub (SELECT * FROM smw_ob_properties_super)');

			// check if there was least one more supercategory. If not, all properties were found.
			$res = $db->query('SELECT COUNT(category) AS numOfSuperCats FROM smw_ob_properties_sub');
			$numOfSuperCats = $db->fetchObject($res)->numOfSuperCats;
			$db->freeResult($res);

			$db->query('DELETE FROM smw_ob_properties_super');

		} while ($numOfSuperCats > 0 && $maxDepth > 0);
		 
		$db->query('DROP TEMPORARY TABLE smw_ob_properties_super');
		$db->query('DROP TEMPORARY TABLE smw_ob_properties_sub');
	}
	
	protected function getSchemaPropertyTuple(array & $properties, & $db) {
		$smw_atts2 = $db->tableName('smw_atts2');
		$smw_ids = $db->tableName('smw_ids');
		$smw_spec2 = $db->tableName('smw_spec2');
		
		$domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => $this->domainRangeHintRelation->getDBkey()) );
	    if ($domainAndRange == NULL) {
            $domainAndRangeID = -1; // does never exist
        } else {
            $domainAndRangeID = $domainAndRange->smw_id;
        }
        $resMinCard = $db->query('SELECT property, value_xsd AS minCard FROM smw_ob_properties JOIN '.$smw_ids.' ON smw_title = property AND smw_namespace = '.SMW_NS_PROPERTY.' JOIN '.$smw_atts2.' ON smw_id = s_id'. 
                             ' GROUP BY property ORDER BY property');
        $resMaxCard = $db->query('SELECT property, value_xsd AS maxCard FROM smw_ob_properties JOIN '.$smw_ids.' ON smw_title = property AND smw_namespace = '.SMW_NS_PROPERTY.' JOIN '.$smw_atts2.' ON smw_id = s_id'. 
                             ' GROUP BY property ORDER BY property');
        $resTypes = $db->query('SELECT property, value_string AS type FROM smw_ob_properties  JOIN '.$smw_ids.' ON smw_title = property AND smw_namespace = '.SMW_NS_PROPERTY.' JOIN '.$smw_spec2.' ON smw_id = s_id'. 
                             ' WHERE sp_id = '.SMW_SP_HAS_TYPE.' GROUP BY property ORDER BY property');
        $resSymCats = $db->query('SELECT property, cl_to AS minCard FROM smw_ob_properties  JOIN '.$db->tableName('categorylinks').
                             ' ON cl_from = id WHERE cl_to = '.$db->addQuotes($this->symetricalCat->getDBKey()). ' GROUP BY property ORDER BY property');
        $resTransCats = $db->query('SELECT property, cl_to AS minCard FROM smw_ob_properties  JOIN '.$db->tableName('categorylinks').
                             ' ON cl_from = id WHERE cl_to = '.$db->addQuotes($this->transitiveCat->getDBKey()). ' GROUP BY property ORDER BY property');
        $resRanges = $db->query('SELECT property, r.smw_title AS rangeinst FROM smw_ob_properties JOIN smw_rels2 n ON id = n.s_id JOIN smw_rels2 m ON n.o_id = m.s_id JOIN smw_ids r ON m.o_id = r.smw_id JOIN smw_ids s ON m.p_id = s.smw_id
                     WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = 1 GROUP BY property ORDER BY property');
                           
        // rewrite result as array
        $result = array();
        
        $rowMinCard = $db->fetchObject($resMinCard);
        $rowMaxCard = $db->fetchObject($resMaxCard);
        $rowType = $db->fetchObject($resTypes);
        $rowSymCat = $db->fetchObject($resSymCats);
        $rowTransCats = $db->fetchObject($resTransCats);
        $rowRanges = $db->fetchObject($resRanges);
        foreach($properties as $p) {
                $minCard = CARDINALITY_MIN;
                if ($rowMinCard != NULL && $rowMinCard->property == $p->getDBkey()) {
                    $minCard = $rowMinCard->minCard;
                    $rowMinCard = $db->fetchObject($resMinCard);
                }
                $maxCard = CARDINALITY_UNLIMITED;
                if ($rowMaxCard != NULL && $rowMaxCard->property == $p->getDBkey()) {
                    $maxCard = $rowMaxCard->maxCard;
                    $rowMaxCard = $db->fetchObject($resMaxCard);
                }
                $type = '_wpg';
                
                if ($rowType != NULL && $rowType->property == $p->getDBkey()) {
                    $type = $rowType->type; 
                    $rowType = $db->fetchObject($resTypes);
                }
                $symCat = false;
                if ($rowSymCat != NULL && $rowSymCat->property == $p->getDBkey()) {
                    $symCat = true;
                    $rowSymCat = $db->fetchObject($resSymCats);
                }
                $transCat = false;
                if ($rowTransCats != NULL && $rowTransCats->property == $p->getDBkey()) {
                    $transCat = true;
                    $rowTransCats = $db->fetchObject($resTransCats);
                }
                $range = NULL;
                if ($rowRanges != NULL && $rowRanges->property == $p->getDBkey()) {
                    $range = $rowRanges->rangeinst;
                    $rowRanges = $db->fetchObject($resRanges);
                }
                $result[] = array($p, $minCard, $maxCard, $type, $symCat, $transCat, $range);
            
        }
        $db->freeResult($resMinCard);
        $db->freeResult($resMaxCard);
        $db->freeResult($resTypes);
        $db->freeResult($resSymCats);
        $db->freeResult($resTransCats);
        $db->freeResult($resRanges);
        return $result;
    }   
    
    public function getNumberOfUsage(Title $title) {
        $num = 0;
        $db =& wfGetDB( DB_SLAVE );
        if ($title->getNamespace() == NS_TEMPLATE) {
            $templatelinks = $db->tableName('templatelinks');
            $res = $db->query('SELECT COUNT(tl_from) AS numOfSubjects FROM '.$templatelinks.' s WHERE tl_title = '.$db->addQuotes($title->getDBKey()).' GROUP BY tl_title ');
        } else if ($title->getNamespace() == SMW_NS_PROPERTY) {
            $smw_atts2 = $db->tableName('smw_atts2');
            $smw_rels2 = $db->tableName('smw_rels2');
            $smw_ids = $db->tableName('smw_ids');
            $res = $db->query('SELECT COUNT(s_id) AS numOfSubjects FROM '.$smw_atts2.' s JOIN '.$smw_ids.' ON p_id = smw_id WHERE smw_title = '.$db->addQuotes($title->getDBKey()).' GROUP BY smw_title ' .
                              ' UNION SELECT COUNT(s_id) AS numOfSubjects FROM '.$smw_rels2.' s JOIN '.$smw_ids.' ON p_id = smw_id WHERE smw_title = '.$db->addQuotes($title->getDBKey()).' GROUP BY smw_title');
                             
        }
        if($db->numRows( $res ) > 0) {
            $row = $db->fetchObject($res);
            $num = $row->numOfSubjects;
        } 
        $db->freeResult($res);
        return $num;
    }
    
    protected function getNarySubjects(Title $object, $pos) {
        $db =& wfGetDB( DB_SLAVE );
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
        $domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => $this->domainRangeHintRelation->getDBkey()) );
        if ($domainAndRange == NULL) {
            $domainAndRangeID = -1; // does never exist
        } else {
            $domainAndRangeID = $domainAndRange->smw_id;
        }
        $results = array();
              
        $res = $db->query('SELECT q.smw_title AS subject_title, q.smw_namespace AS subject_namespace FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = '.mysql_real_escape_string($pos).' AND r.smw_title = '.$db->addQuotes($object->getDBkey()).' AND r.smw_namespace = '.NS_CATEGORY);
        
        
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                
                $results[] = Title::newFromText($row->subject_title, $row->subject_namespace);
            }
        }
        $db->freeResult($res);
        return $results;
    }
    
    public function getNumberOfPropertiesForTarget(Title $target) {
        $db =& wfGetDB( DB_SLAVE ); 
        $result = 0;
        $res = $db->select( $db->tableName('smw_relations'), 
                            'COUNT(DISTINCT relation_title) AS numOfProperties',
                            array('object_title' => $target->getDBkey()), 'SMW::getNumberOfPropertiesForTarget', array() );
        
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
        $res = $db->query('SELECT COUNT(DISTINCT s_id) AS numOfProperties FROM '.$smw_rels2.' JOIN '.$smw_ids.' ON smw_id = o_id WHERE smw_title = '.$db->addQuotes($target->getDBkey()));
        
        if($db->numRows( $res ) > 0) {
              $row = $db->fetchObject($res);
              $result += $row->numOfProperties;
        }           
        $db->freeResult($res);
        
      
        return $result;
    }
    
public function getDistinctUnits(Title $type) {
        $db =& wfGetDB( DB_SLAVE );
        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
        $smw_spec2 = $db->tableName('smw_spec2');
        
        $res = $db->query(  'SELECT DISTINCT a.value_unit FROM '.$smw_atts2.' a JOIN '.$smw_spec2.' s ON a.p_id = s.s_id AND s.sp_id = '.SMW_SP_HAS_TYPE.' WHERE s.value_string = '.$db->addQuotes($type->getDBkey()));
                                    
        
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = $row->value_unit;
            }
        }
        $db->freeResult($res);
        return $result;
    }
    
    public function getAnnotationsWithUnit(Title $type, $unit) {
        
        $db =& wfGetDB( DB_SLAVE );
        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
        $smw_spec2 = $db->tableName('smw_spec2');
        
        $result = array();
        $res = $db->query('SELECT DISTINCT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS attribute_title FROM '.$smw_ids.' i JOIN '.$smw_atts2.' a ON i.smw_id = a.s_id JOIN '.$smw_spec2.' s ON a.p_id = s.s_id AND s.sp_id = '.SMW_SP_HAS_TYPE.' JOIN '.$smw_ids.' i2 ON i2.smw_id = a.p_id '.
                            ' WHERE s.value_string = '.$db->addQuotes($type->getDBkey()).' AND a.value_unit = '.$db->addQuotes($unit));
        
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array(Title::newFromText($row->subject_title, $row->subject_namespace), Title::newFromText($row->attribute_title, SMW_NS_PROPERTY));
            }
        }
        
        $db->freeResult($res);
        
        $res2 = $db->query('SELECT DISTINCT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS attribute_title FROM '.$smw_spec2.' s ' .
                                    'JOIN '.$smw_rels2.' r ON CONTAINS(s.value_string, '.$db->addQuotes($type->getDBkey()).') AND s.s_id = r.p_id ' .
                                    'JOIN '.$smw_ids.' i ON i.smw_id = r.s_id' .
                                    'JOIN '.$smw_ids.' i2 ON i.smw_id = r.p_id' .
                          'WHERE value_unit = '.$db->addQuotes($unit));
        
        
        if($db->numRows( $res2 ) > 0) {
            while($row = $db->fetchObject($res2)) {
                $result[] = array(Title::newFromText($row->subject_title, $row->subject_namespace), Title::newFromText($row->attribute_title, SMW_NS_PROPERTY));
            }
        }
        
        $db->freeResult($res2);
        
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
       
        
        $res = $db->selectRow($smw_atts2, 'rating', array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'value_xsd' => $object));
        if ($res !== false && $subject !== false && $predicate !== false) {
            $db->update($smw_atts2, array('rating' => (is_numeric($res->rating) ? $res->rating : 0) + $rating), array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'value_xsd' => $object));
        } else {
            $object = $db->selectRow($smw_ids, 'smw_id', array('smw_title' => $object));
            $res = $db->selectRow($smw_rels2, 'rating', array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'o_id' => $object->smw_id));
            
            if ($res !== false && $subject !== false && $predicate !== false && $object !== false) {
                $db->update($smw_rels2, array('rating' => (is_numeric($res->rating) ? $res->rating : 0) + $rating), array('s_id' => $subject->smw_id, 'p_id' => $predicate->smw_id, 'o_id' => $object->smw_id));
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
        
        $res = $db->query('(SELECT i.smw_title AS subject, i2.smw_title AS predicate, value_xsd AS object FROM '.$smw_atts2. ' JOIN '.$smw_ids.' i ON s_id=i.smw_id JOIN '.$smw_ids.' i2 ON p_id=i2.smw_id '. $where.') ' .
                            'UNION ' .
                           '(SELECT i.smw_title AS subject, i2.smw_title AS predicate, i3.smw_title AS object FROM '.$smw_rels2.' JOIN '.$smw_ids.' i ON s_id=i.smw_id JOIN '.$smw_ids.' i2 ON p_id=i2.smw_id JOIN '.$smw_ids.' i3 ON o_id=i3.smw_id '. $where.' AND i.smw_iw != ":smw") ORDER BY RAND() LIMIT '.$limit);
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array($row->subject, $row->predicate, $row->object);
            }
        }
        $db->freeResult($res);
        return $result;
    }
    
    public function replaceRedirectAnnotations($verbose = false) {
        
        
    }
}
?>