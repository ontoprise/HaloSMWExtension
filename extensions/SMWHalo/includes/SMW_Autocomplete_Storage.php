<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once( $smwgHaloIP . "/includes/SMW_OntologyManipulator.php");

/**
 * @file
 * @ingroup SMWHaloAutocompletion
 * 
 * @author Kai K�hn
 *
 */
abstract class AutoCompletionStorage {

	/**
	 * Returns units which matches the types of the given property and the substring
	 *
	 * @param Title $property
	 * @param string $substring
	 *
	 * @return array of strings
	 */
	public abstract function getUnits(Title $property, $substring);

	/**
	 * Returns possible values for a given property.
	 *
	 * @param Title $property
	 * @return array of strings
	 */
	public abstract function getPossibleValues(Title $property);

	/**
	 * Retrieves pages matching the requestoptions and the given namespaces
	 *
	 * @param string match
	 * @param array of integer or NULL
	 *
	 * @return array of Title
	 */
	public abstract function getPages($match, $namespaces = NULL);

	/**
	 * Returns properties containing $match with unit $unit
	 *
	 * TODO: should be transferred to storage layer
	 *
	 * @param string $match substring
	 * @param string $typeLabel primitive type or unit
	 */
	public abstract function getPropertyWithType($match, $typeLabel);

	/**
	 * Returns (including inferred) properties which match a given $instance for domain or range category
	 * If $instance is not part of any category, it will return an empty result set.
	 *
	 * @param string $userInputToMatch substring must be part of property title
	 * @param Title $instance
	 * @param boolean $matchDomainOrRange True, if $instance must match domain, false for range
	 * 
	 * @return array (Title property, boolean inferred)
	 */
	public abstract function getPropertyForInstance($userInputToMatch, $instance, $matchDomainOrRange);
    
	/**
	 * Returns (including inferred) properties which have a given $category as domain.
     * 
	 * @param string $userInputToMatch substring must be part of property title
	 * @param Title $category
	 * 
	 * @return array (Title property, boolean inferred)
	 */
	public abstract function getPropertyForCategory($userInputToMatch, $category);
	
	/**
	 * Returns (including inferred) properties which are used on pages of the given $category.
	 *
	 * @param string $userInputToMatch
	 * @param Title $category
	 * 
	 * @return array (Title property, boolean inferred)
	 */
	public abstract function getPropertyForAnnotation($userInputToMatch, $category);
	
	/**
	 * Gets (including inferred) property values of $property
	 *
	 * @param string $userInputToMatch
	 * @param Title $property
	 */
	public abstract function getValueForAnnotation($userInputToMatch, $property);
	
	/**
	 * Returns instances which are member of the given range(s) and which match $userInputToMatch.
	 *
	 * @param string $userInputToMatch
	 * @param Array of SMWRecordValue $domainRangeAnnotations
	 *
	 * @return array of (Title instance)
	 */
	public abstract function getInstanceAsTarget($userInputToMatch, $domainRangeAnnotations);
}

/**
 * TODO: Document, including member functions
 */
class AutoCompletionStorageSQL2 extends AutoCompletionStorage {

	public function getUnits(Title $property, $substring) {
		$all_units = array();
		$substring = str_replace("_", " ",$substring);

		// get all types of a property (normally 1)
		$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
		$conversionFactorDV = SMWPropertyValue::makeProperty("_CONV");
		$conversionFactorSIDV = SMWPropertyValue::makeProperty("___cfsi");
		$types = smwfGetStore()->getPropertyValues($property, $hasTypeDV);
		foreach($types as $t) {

			$subtypes = explode(";", array_shift($t->getDBkeys()));

			foreach($subtypes as $st) {
				// get all units registered for a given type
				$typeTitle = Title::newFromText($st, SMW_NS_TYPE);
				$units = smwfGetStore()->getPropertyValues($typeTitle, $conversionFactorDV);
				$units_si = smwfGetStore()->getPropertyValues($typeTitle, $conversionFactorSIDV);
				$all_units = array_merge($all_units, $units, $units_si);
			}
		}
		$result = array();

		// regexp for a measure (=number + unit)
		$measure = "/(([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s+)?(.*)/";

		// extract unit substring and ignore the number (if existing)
		preg_match($measure, $substring, $matches);
		$substring = strtolower($matches[5]);

		// collect all units which match the substring (if non empty, otherwise all)
		foreach($all_units as $u) {
			$s_units = explode(",", array_shift($u->getDBkeys()));
			foreach($s_units as $su) {
				if ($substring != '') {
					if (strpos(strtolower($su), $substring) > 0) {
						preg_match($measure, $su, $matches);
						if (count($matches) >= 5) $result[] = $matches[5];// ^^^ 5th brackets
					}
				} else {
					preg_match($measure, $su, $matches);
					if (count($matches) >= 5) $result[] = $matches[5];// ^^^ 5th brackets
				}
			}
		}

		return array_unique($result);   // make sure all units appear only once.
	}

	public function getPossibleValues(Title $property) {
		$possibleValueDV = SMWPropertyValue::makeProperty("_PVAL");
		$poss_values = smwfGetStore()->getPropertyValues($property, $possibleValueDV);
		$result = array();
		foreach($poss_values as $v) {
			$result[] = array_shift($v->getDBkeys());
		}
		return $result;
	}

	public function getPages($match, $namespaces = NULL) {
		$result = "";
		$db =& wfGetDB( DB_SLAVE );
		$sql = "";
		$page = $db->tableName('page');
		$requestoptions = new SMWRequestOptions();
		$requestoptions->limit = SMW_AC_MAX_RESULTS;
		$options = DBHelper::getSQLOptionsAsString($requestoptions);
		if ($namespaces == NULL || count($namespaces) == 0) {

			$sql .= 'SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER('.DBHelper::convertColumn('page_title').') LIKE UPPER('.$db->addQuotes($match.'%').') ORDER BY page_title ';

		} else {

			for ($i = 0, $n = count($namespaces); $i < $n; $i++) {
				if ($i > 0) $sql .= ' UNION ';
				$sql .= '(SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER('.DBHelper::convertColumn('page_title').') LIKE UPPER('.$db->addQuotes($match.'%').') AND page_namespace='.$db->addQuotes($namespaces[$i]).' ORDER BY page_title) UNION ';
				$sql .= '(SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER('.DBHelper::convertColumn('page_title').') LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND page_namespace='.$db->addQuotes($namespaces[$i]).' ORDER BY page_title) ';
			}

		}

		$result = array();

		$res = $db->query($sql.$options);

		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				if (smwf_om_userCan($row->page_title, 'read', $row->page_namespace) == 'true') {
					if ($row->page_namespace == SMW_NS_PROPERTY) {
						$propertyTitle = Title::newFromText($row->page_title, SMW_NS_PROPERTY);
						$result[] = array($propertyTitle, false, NULL, $this->getPropertyData($propertyTitle));
					} else {
                    	$result[] = Title::newFromText($row->page_title, $row->page_namespace);
					}
				}
			}
		}
		$db->freeResult($res);
		return $result;
	}


	public function getPropertyWithType($match, $typeLabel) {
		$db =& wfGetDB( DB_SLAVE );
		$smw_spec2 = $db->tableName('smw_spec2');
		$smw_ids = $db->tableName('smw_ids');
		$page = $db->tableName('page');
		$result = array();
		$typeID = SMWDataValueFactory::findTypeID($typeLabel);
		$hasTypePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeProperty("_TYPE"));
		$res = $db->query('(SELECT i2.smw_title AS title FROM '.$smw_ids.' i2 '.
                               'JOIN '.$smw_spec2.' s1 ON i2.smw_id = s1.s_id AND s1.p_id = '.$hasTypePropertyID.' '.
                               'JOIN '.$smw_ids.' i ON s1.value_string = i.smw_title AND i.smw_namespace = '.SMW_NS_TYPE.' '.
                               'JOIN '.$smw_spec2.' s2 ON s2.s_id = i.smw_id AND s2.value_string REGEXP ' . $db->addQuotes("([0-9].?[0-9]*|,) $typeLabel(,|$)") .
                               'WHERE i2.smw_namespace = '.SMW_NS_PROPERTY.' AND UPPER('.DBHelper::convertColumn('i2.smw_title').') LIKE UPPER(' . $db->addQuotes("%$match%").'))'.
                            ' UNION (SELECT smw_title AS title FROM smw_ids i '.
                               'JOIN '.$smw_spec2.' s1 ON i.smw_id = s1.s_id AND s1.p_id = '.$hasTypePropertyID.' '.
                               'WHERE UPPER('.DBHelper::convertColumn('i.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$match.'%').') AND '.
                               'UPPER('.DBHelper::convertColumn('s1.value_string').') = UPPER('.$db->addQuotes($typeID).') AND smw_namespace = '.SMW_NS_PROPERTY.') '.
                            'ORDER BY title LIMIT '.SMW_AC_MAX_RESULTS);

		 
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				if (smwf_om_userCan($row->title, 'read', SMW_NS_PROPERTY) == 'true') {
					$result[] = Title::newFromText($row->title, SMW_NS_PROPERTY);
				}
			}
		}

		$db->freeResult($res);

		return $result;
	}


	public function getPropertyForInstance($userInputToMatch, $instance, $matchDomainOrRange) {
		global $smwgDefaultCollation;
		$db =& wfGetDB( DB_SLAVE );
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$smw_rels2 = $db->tableName('smw_rels2');
		$smw_ids = $db->tableName('smw_ids');
		 

		$nary_pos = $matchDomainOrRange ? '"_1"' : '"_2"';

		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties (id INT(8) NOT NULL, property VARCHAR(255), inferred ENUM(\'true\',\'false\') '.$collation.')
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );

		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_sub (category VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_super (category VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );

		$domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()) );
		if ($domainAndRange == NULL) {
			$domainAndRangeID = -1; // does never exist
		} else {
			$domainAndRangeID = $domainAndRange->smw_id;
		}

		$db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property, "false" AS inferred FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = '.$nary_pos.' AND r.smw_title IN (SELECT cl_to FROM '.$categorylinks.' WHERE cl_from = ' .$db->addQuotes($instance->getArticleID()).') AND r.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('q.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');


		$db->query('INSERT INTO smw_ob_properties_sub  (SELECT DISTINCT page_title AS category FROM '.$categorylinks.' JOIN '.$page.' ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' WHERE cl_from = ' .$instance->getArticleID().')');

		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum category tree depth.
		do  {
			$maxDepth--;

			// get next supercategory level
			$db->query('INSERT INTO smw_ob_properties_super (SELECT DISTINCT cl_to AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_id = cl_from WHERE page_namespace = '.NS_CATEGORY.' AND page_title IN (SELECT * FROM smw_ob_properties_sub))');

			// insert direct properties of current supercategory level
			$db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property, "true" AS inferred FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = '.$nary_pos.' AND r.smw_title IN (SELECT * FROM smw_ob_properties_super) AND r.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('q.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
			 
			// copy supercatgegories to subcategories of next iteration
			$db->query('DELETE FROM smw_ob_properties_sub');
			$db->query('INSERT INTO smw_ob_properties_sub (SELECT * FROM smw_ob_properties_super)');

			// check if there was least one more supercategory. If not, all properties were found.
			$res = $db->query('SELECT COUNT(category) AS numOfSuperCats FROM smw_ob_properties_sub');
			$numOfSuperCats = $db->fetchObject($res)->numOfSuperCats;
			$db->freeResult($res);

			$db->query('DELETE FROM smw_ob_properties_super');

		} while ($numOfSuperCats > 0 && $maxDepth > 0);

		$res = $db->query('SELECT DISTINCT property, inferred FROM smw_ob_properties ORDER BY inferred DESC, property LIMIT '.SMW_AC_MAX_RESULTS);
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				if (smwf_om_userCan($row->property, 'read', SMW_NS_PROPERTY) == 'true') {
					$propertyTitle = Title::newFromText($row->property, SMW_NS_PROPERTY);
					$result[] = array($propertyTitle, $row->inferred == "true", NULL, $this->getPropertyData($propertyTitle));
				}
			}
		}

		$db->freeResult($res);


		$db->query('DROP TEMPORARY TABLE smw_ob_properties');
		$db->query('DROP TEMPORARY TABLE smw_ob_properties_super');
		$db->query('DROP TEMPORARY TABLE smw_ob_properties_sub');
		return $result;
	}
	
    public function getPropertyForCategory($userInputToMatch, $category) {
        global $smwgDefaultCollation;
        $db =& wfGetDB( DB_SLAVE );
        $page = $db->tableName('page');
        $categorylinks = $db->tableName('categorylinks');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
         

        $nary_pos = '"_1"';

        if (!isset($smwgDefaultCollation)) {
            $collation = '';
        } else {
            $collation = 'COLLATE '.$smwgDefaultCollation;
        }
        // create virtual tables
        $db->query( 'CREATE TEMPORARY TABLE smw_ob_properties (id INT(8) NOT NULL, property VARCHAR(255), inferred ENUM(\'true\',\'false\') '.$collation.')
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );

        $db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_sub (category VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
        $db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_super (category VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );

        $domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()) );
        if ($domainAndRange == NULL) {
            $domainAndRangeID = -1; // does never exist
        } else {
            $domainAndRangeID = $domainAndRange->smw_id;
        }
   
        $db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property, "false" AS inferred FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = '.$nary_pos.' AND r.smw_title = '.$db->addQuotes($category->getDBkey()).' AND r.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('q.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');


        $db->query('INSERT INTO smw_ob_properties_sub VALUES (\''.$category->getDBkey().'\')');

        $maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
        // maximum iteration length is maximum category tree depth.
        do  {
            $maxDepth--;

            // get next supercategory level
            $db->query('INSERT INTO smw_ob_properties_super (SELECT DISTINCT cl_to AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_id = cl_from WHERE page_namespace = '.NS_CATEGORY.' AND page_title IN (SELECT * FROM smw_ob_properties_sub))');

            // insert direct properties of current supercategory level
            $db->query('INSERT INTO smw_ob_properties (SELECT q.smw_id AS id, q.smw_title AS property, "true" AS inferred FROM '.$smw_ids.' q JOIN '.$smw_rels2.' n ON q.smw_id = n.s_id JOIN '.$smw_rels2.' m ON n.o_id = m.s_id JOIN '.$smw_ids.' r ON m.o_id = r.smw_id JOIN '.$smw_ids.' s ON m.p_id = s.smw_id'.
                     ' WHERE n.p_id = '.$domainAndRangeID.' AND s.smw_sortkey = '.$nary_pos.' AND r.smw_title IN (SELECT * FROM smw_ob_properties_super) AND r.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('q.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
             
            // copy supercatgegories to subcategories of next iteration
            $db->query('DELETE FROM smw_ob_properties_sub');
            $db->query('INSERT INTO smw_ob_properties_sub (SELECT * FROM smw_ob_properties_super)');

            // check if there was least one more supercategory. If not, all properties were found.
            $res = $db->query('SELECT COUNT(category) AS numOfSuperCats FROM smw_ob_properties_sub');
            $numOfSuperCats = $db->fetchObject($res)->numOfSuperCats;
            $db->freeResult($res);

            $db->query('DELETE FROM smw_ob_properties_super');

        } while ($numOfSuperCats > 0 && $maxDepth > 0);

        $res = $db->query('SELECT DISTINCT property, inferred FROM smw_ob_properties ORDER BY inferred DESC, property LIMIT '.SMW_AC_MAX_RESULTS);
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
				if (smwf_om_userCan($row->property, 'read', SMW_NS_PROPERTY) == 'true') {
            		$propertyTitle = Title::newFromText($row->property, SMW_NS_PROPERTY);
                    $result[] = array($propertyTitle, $row->inferred == "true", NULL, $this->getPropertyData($propertyTitle));
				}
            }
        }

        $db->freeResult($res);


        $db->query('DROP TEMPORARY TABLE smw_ob_properties');
        $db->query('DROP TEMPORARY TABLE smw_ob_properties_super');
        $db->query('DROP TEMPORARY TABLE smw_ob_properties_sub');
        return $result;
    }
    
    public function getPropertyForAnnotation($userInputToMatch, $category) {
        global $smwgDefaultCollation;
        $db =& wfGetDB( DB_SLAVE );
        $page = $db->tableName('page');
        $categorylinks = $db->tableName('categorylinks');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_ids = $db->tableName('smw_ids');
         

        $nary_pos = 0;

        if (!isset($smwgDefaultCollation)) {
            $collation = '';
        } else {
            $collation = 'COLLATE '.$smwgDefaultCollation;
        }
        // create virtual tables
        $db->query( 'CREATE TEMPORARY TABLE smw_ob_properties (id INT(8) NOT NULL, property VARCHAR(255), inferred ENUM(\'true\',\'false\') '.$collation.')
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );

        $db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_sub (category VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );
        $db->query( 'CREATE TEMPORARY TABLE smw_ob_properties_super (category VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithPropertiesByCategory' );

        $domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()) );
        if ($domainAndRange == NULL) {
            $domainAndRangeID = -1; // does never exist
        } else {
            $domainAndRangeID = $domainAndRange->smw_id;
        }
   
        $db->query('INSERT INTO smw_ob_properties (SELECT p.smw_id AS id, p.smw_title AS property, "false" AS inferred FROM '.$smw_rels2.' rels JOIN '.$smw_ids.' s ON rels.s_id = s.smw_id JOIN '.$smw_ids.' p ON rels.p_id = p.smw_id JOIN smw_inst2 inst ON rels.s_id = inst.s_id JOIN smw_ids cats ON cats.smw_id = inst.o_id'.
                     ' WHERE cats.smw_title  = '.$db->addQuotes($category->getDBkey()).' AND cats.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('p.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
        $db->query('INSERT INTO smw_ob_properties (SELECT p.smw_id AS id, p.smw_title AS property, "false" AS inferred FROM '.$smw_atts2.' rels JOIN '.$smw_ids.' s ON rels.s_id = s.smw_id JOIN '.$smw_ids.' p ON rels.p_id = p.smw_id JOIN smw_inst2 inst ON rels.s_id = inst.s_id JOIN smw_ids cats ON cats.smw_id = inst.o_id'.
                     ' WHERE cats.smw_title  = '.$db->addQuotes($category->getDBkey()).' AND cats.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('p.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
        
       
        $db->query('INSERT INTO smw_ob_properties_sub VALUES (\''.$category->getDBkey().'\')');

        $maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
        // maximum iteration length is maximum category tree depth.
        do  {
            $maxDepth--;

            // get next supercategory level
            $db->query('INSERT INTO smw_ob_properties_super (SELECT DISTINCT cl_to AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_id = cl_from WHERE page_namespace = '.NS_CATEGORY.' AND page_title IN (SELECT * FROM smw_ob_properties_sub))');

            // insert direct properties of current supercategory level
            $db->query('INSERT INTO smw_ob_properties (SELECT p.smw_id AS id, p.smw_title AS property, "true" AS inferred FROM '.$smw_rels2.' rels JOIN '.$smw_ids.' s  ON rels.s_id = s.smw_id JOIN '.$smw_ids.' p ON rels.p_id = p.smw_id JOIN smw_inst2 inst ON rels.s_id = inst.s_id JOIN smw_ids cats ON cats.smw_id = inst.o_id'.
                     ' WHERE cats.smw_title IN (SELECT * FROM smw_ob_properties_super) AND cats.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('p.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
             $db->query('INSERT INTO smw_ob_properties (SELECT p.smw_id AS id, p.smw_title AS property, "true" AS inferred FROM '.$smw_atts2.' rels JOIN '.$smw_ids.' s  ON rels.s_id = s.smw_id JOIN '.$smw_ids.' p ON rels.p_id = p.smw_id JOIN smw_inst2 inst ON rels.s_id = inst.s_id JOIN smw_ids cats ON cats.smw_id = inst.o_id'.
                     ' WHERE cats.smw_title IN (SELECT * FROM smw_ob_properties_super) AND cats.smw_namespace = '.NS_CATEGORY.' AND UPPER('.DBHelper::convertColumn('p.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
                      
                     
           
            // copy supercatgegories to subcategories of next iteration
            $db->query('DELETE FROM smw_ob_properties_sub');
            $db->query('INSERT INTO smw_ob_properties_sub (SELECT * FROM smw_ob_properties_super)');

            // check if there was least one more supercategory. If not, all properties were found.
            $res = $db->query('SELECT COUNT(category) AS numOfSuperCats FROM smw_ob_properties_sub');
            $numOfSuperCats = $db->fetchObject($res)->numOfSuperCats;
            $db->freeResult($res);

            $db->query('DELETE FROM smw_ob_properties_super');

        } while ($numOfSuperCats > 0 && $maxDepth > 0);

        $res = $db->query('SELECT DISTINCT property, inferred FROM smw_ob_properties ORDER BY inferred DESC, property LIMIT '.SMW_AC_MAX_RESULTS);
        $result = array();
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
				if (smwf_om_userCan($row->property, 'read', SMW_NS_PROPERTY) == 'true') {
            		$propertyTitle = Title::newFromText($row->property, SMW_NS_PROPERTY);
                    $result[] = array($propertyTitle, $row->inferred == "true", NULL, $this->getPropertyData($propertyTitle));
				}
            }
        }

        $db->freeResult($res);


        $db->query('DROP TEMPORARY TABLE smw_ob_properties');
        $db->query('DROP TEMPORARY TABLE smw_ob_properties_super');
        $db->query('DROP TEMPORARY TABLE smw_ob_properties_sub');
        return $result;
    }
    
 public function getValueForAnnotation($userInputToMatch, $property) {
        global $smwgDefaultCollation;
        $db =& wfGetDB( DB_SLAVE );

        $smw_ids = $db->tableName('smw_ids');
        $smw_rels2 = $db->tableName('smw_rels2');
        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_subs2 = $db->tableName('smw_subp2');
        $smw_spec2 = $db->tableName('smw_spec2');
        
        if (!isset($smwgDefaultCollation)) {
            $collation = '';
        } else {
            $collation = 'COLLATE '.$smwgDefaultCollation;
        }
        // create virtual tables
        $db->query( 'CREATE TEMPORARY TABLE smw_cc_propertyinst (title VARCHAR(255), namespace INT(11), inferred ENUM(\'true\',\'false\'))
                    TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
      

        $db->query( 'CREATE TEMPORARY TABLE smw_cc_properties_sub (property VARCHAR(255) '.$collation.' NOT NULL)
                    TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
        $db->query( 'CREATE TEMPORARY TABLE smw_cc_properties_super (property VARCHAR(255) '.$collation.' NOT NULL)
                    TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );

        $db->query('INSERT INTO smw_cc_properties_super VALUES ('.$db->addQuotes($property->getDBkey()).')');

        
        $db->query('INSERT INTO smw_cc_propertyinst ' .
                '(SELECT value_xsd AS title, -1 AS namespace, "false" AS inferred FROM '.$smw_atts2.' JOIN '.$smw_ids.' p ON p_id = p.smw_id WHERE p.smw_title = '.$db->addQuotes($property->getDBkey()).' AND p.smw_namespace = '.SMW_NS_PROPERTY. ' AND UPPER('.DBHelper::convertColumn('value_xsd').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
        $db->query('INSERT INTO smw_cc_propertyinst ' .
                '(SELECT o.smw_title AS title, o.smw_namespace AS namespace, "false" AS inferred FROM '.$smw_rels2.' JOIN '.$smw_ids.' p ON p_id = p.smw_id JOIN '.$smw_ids.' o ON o_id = o.smw_id WHERE p.smw_title = '.$db->addQuotes($property->getDBkey()).' AND p.smw_namespace = '.SMW_NS_PROPERTY. ' AND UPPER('.DBHelper::convertColumn('o.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
        $db->query('INSERT INTO smw_cc_propertyinst ' .
                '(SELECT value_string AS title, -1 AS namespace, "false" AS inferred FROM '.$smw_spec2.' JOIN '.$smw_ids.' p ON p_id = p.smw_id WHERE p.smw_title = '.$db->addQuotes($property->getDBkey()).' AND p.smw_namespace = '.SMW_NS_PROPERTY. ' AND UPPER('.DBHelper::convertColumn('value_string').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
        
        $maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
        // maximum iteration length is maximum property tree depth.
        do  {
            $maxDepth--;

            // get next subproperty level
            $db->query('INSERT INTO smw_cc_properties_sub (SELECT DISTINCT i.smw_title AS property FROM '.$smw_subs2.' JOIN '.$smw_ids.' i ON s_id = i.smw_id JOIN '.$smw_ids.' i2 ON o_id = i2.smw_id WHERE i2.smw_title IN (SELECT * FROM smw_cc_properties_super))');

            $db->query('INSERT INTO smw_cc_propertyinst ' .
                '(SELECT value_xsd AS title, -1 AS namespace, "true" AS inferred FROM '.$smw_atts2.' JOIN '.$smw_ids.' p ON p_id = p.smw_id WHERE p.smw_title IN (SELECT * FROM smw_cc_properties_sub) AND p.smw_namespace = '.SMW_NS_PROPERTY. ' AND UPPER('.DBHelper::convertColumn('value_xsd').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
            $db->query('INSERT INTO smw_cc_propertyinst ' .
                '(SELECT o.smw_title AS title, o.smw_namespace AS namespace, "true" AS inferred FROM '.$smw_rels2.' JOIN '.$smw_ids.' p ON p_id = p.smw_id JOIN '.$smw_ids.' o ON o_id = o.smw_id WHERE p.smw_title IN (SELECT * FROM smw_cc_properties_sub) AND p.smw_namespace = '.SMW_NS_PROPERTY. ' AND UPPER('.DBHelper::convertColumn('o.smw_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');
            // special properties can not be inferred (?)

            // copy subcatgegories to supercategories of next iteration
            $db->query('DELETE FROM smw_cc_properties_super');
            $db->query('INSERT INTO smw_cc_properties_super (SELECT * FROM smw_cc_properties_sub)');

            // check if there was least one more subproperty. If not, all instances were found.
            $res = $db->query('SELECT COUNT(property) AS numOfSubProps FROM smw_cc_properties_super');
            $numOfSubProps = $db->fetchObject($res)->numOfSubProps;
            $db->freeResult($res);

            $db->query('DELETE FROM smw_cc_properties_sub');

        } while ($numOfSubProps > 0 && $maxDepth > 0);



        $res = $db->query('SELECT DISTINCT title, namespace, inferred FROM smw_cc_propertyinst ORDER BY inferred DESC, title LIMIT '.SMW_AC_MAX_RESULTS);

        $result = array();
        
        // deactivated code which considers users preferred date format
        $prop = SMWPropertyValue::makeUserProperty($property);
        
        if (array_shift($prop->getTypesValue()->getDBkeys()) == '_dat') {
            $dateformat = "dmy"; // set "25 April 1980 00:00:00" as default dateFormat (the time is optional)
            // This would consider user prefs for date format.
        	//global $wgUser;
        	//$dateformat = !is_null($wgUser) ? $wgUser->getOption('date') : "ISO 8601";
        }
        
       
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                if ($row->namespace == -1) {
                	$stringValue = $row->title;
                	$stringValue = isset($dateformat) ? ACStorageHelper::convertDate($stringValue, $dateformat) : $stringValue;
                    $result[] = array($stringValue, $row->inferred == 'true');
                } else {
					if (smwf_om_userCan($row->title, 'read', $row->namespace) == 'true') {
	                    $result[] = array(Title::newFromText($row->title, $row->namespace), $row->inferred == 'true');
					}
                }
            }
        }

        $db->freeResult($res);

        $db->query('DROP TEMPORARY TABLE smw_cc_properties_super');
        $db->query('DROP TEMPORARY TABLE smw_cc_properties_sub');
       
        $db->query('DROP TEMPORARY TABLE smw_cc_propertyinst');

        return $result;
 
    }
	 

	public function getInstanceAsTarget($userInputToMatch, $domainRangeAnnotations) {
		global $smwgDefaultCollation;
		$db =& wfGetDB( DB_SLAVE );
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');

		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_instances (instance VARCHAR(255) '.$collation.', namespace INTEGER(11))
                    TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );

		$db->query( 'CREATE TEMPORARY TABLE smw_ob_instances_sub (category VARCHAR(255) '.$collation.' NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );
		$db->query( 'CREATE TEMPORARY TABLE smw_ob_instances_super (category VARCHAR(255) '.$collation.' NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableWithInstances' );

		// initialize with direct instances
		foreach($domainRangeAnnotations as $dr) {
			$dvs = $dr->getDVs();
			if ($dvs[1] == NULL || !$dvs[1]->isValid()) continue;
			$db->query('INSERT INTO smw_ob_instances (SELECT page_title AS instance, page_namespace AS namespace FROM '.$page.' ' .
                        'JOIN '.$categorylinks.' ON page_id = cl_from ' .
                        'WHERE page_is_redirect = 0 AND cl_to = '.$db->addQuotes($dvs[1]->getTitle()->getDBkey()).' AND UPPER('.DBHelper::convertColumn('page_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');

			 
			$db->query('INSERT INTO smw_ob_instances_super VALUES ('.$db->addQuotes($dvs[1]->getTitle()->getDBkey()).')');

		}

		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum category tree depth.
		do  {
			$maxDepth--;

			// get next subcategory level
			$db->query('INSERT INTO smw_ob_instances_sub (SELECT DISTINCT page_title AS category FROM '.$categorylinks.' JOIN '.$page.' ON page_id = cl_from WHERE page_namespace = '.NS_CATEGORY.' AND cl_to IN (SELECT * FROM smw_ob_instances_super))');

			// insert direct instances of current subcategory level
			$db->query('INSERT INTO smw_ob_instances (SELECT page_title AS instance, page_namespace AS namespace  FROM '.$page.' ' .
                        'JOIN '.$categorylinks.' ON page_id = cl_from ' .
                        'WHERE page_is_redirect = 0 AND cl_to IN (SELECT * FROM smw_ob_instances_sub) AND UPPER('.DBHelper::convertColumn('page_title').') LIKE UPPER('.$db->addQuotes('%'.$userInputToMatch.'%').'))');

			// copy subcatgegories to supercategories of next iteration
			$db->query('DELETE FROM smw_ob_instances_super');
			$db->query('INSERT INTO smw_ob_instances_super (SELECT * FROM smw_ob_instances_sub)');

			// check if there was least one more subcategory. If not, all instances were found.
			$res = $db->query('SELECT COUNT(category) AS numOfSubCats FROM smw_ob_instances_super');
			$numOfSubCats = $db->fetchObject($res)->numOfSubCats;
			$db->freeResult($res);

			$db->query('DELETE FROM smw_ob_instances_sub');

		} while ($numOfSubCats > 0 && $maxDepth > 0);


		$db->query('DROP TEMPORARY TABLE smw_ob_instances_super');
		$db->query('DROP TEMPORARY TABLE smw_ob_instances_sub');

		 
		$res = $db->query('SELECT DISTINCT instance, namespace FROM smw_ob_instances ORDER BY instance LIMIT '.SMW_AC_MAX_RESULTS);

		$results = array();
		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			 
			while($row)
			{
				if (smwf_om_userCan($row->instance, 'read', $row->namespace) == 'true') {
					$instance = Title::newFromText($row->instance, $row->namespace);
					$results[] = $instance;
				}
				$row = $db->fetchObject($res);
			}

		}
		$db->freeResult($res);

		// drop virtual tables
		$db->query('DROP TEMPORARY TABLE smw_ob_instances');
		return $results;
	}
	
    private function getPropertyData(Title $property) {
        $ranges = array();
        $pv = SMWPropertyValue::makeUserProperty($property->getText());
        $typesValue = $pv->getTypesValue();
        $typeString = implode(',', $typesValue->getTypeLabels());
        $hasWikiPageType = false;
        $typeValues = $typesValue->getTypeValues();
        foreach($typeValues as $tv) {
            $hasWikiPageType |= array_shift($tv->getDBkeys()) == '_wpg';
        }
        
        
        $rangeString = NULL;
        if ($hasWikiPageType) {
            $domainRangeAnnotations = smwfGetStore()->getPropertyValues($property, smwfGetSemanticStore()->domainRangeHintProp);
            foreach($domainRangeAnnotations as $a) {
                $dvs = $a->getDVs();
                $domain = reset($dvs);
                $range = next($dvs);
                if (!is_null($range) && $range !== false) $ranges[] = $range->getTitle()->getText();
            }
             
            $rangeString = implode(',', array_unique($ranges));
        }
        return array($typeString, $rangeString);
    }
}

/*
 * Helper class
 */
class ACStorageHelper {
	public static function convertDate($date, $dateformat) {
		if ($dateformat == 'ISO 8601' || $dateformat == 'default') {
			return substr(trim($date),-1) == 'T' ? substr($date,0, strpos($date, 'T')) : $date; 
		}
		$dateTime = explode("T", $date);
		$datetime = date_create(str_replace("T", "", $date));
		if (!isset($dateTime[1]) || empty($dateTime[1]) || $dateTime[1] == '00:00:00') {
			switch($dateformat) {
	            case 'dmy': return $datetime->format('d F Y');
	            case 'mdy': return $datetime->format('F d, Y');
	            case 'ymd': return $datetime->format('Y F d');
	                    
	        }
		} else {
		  switch($dateformat) {
                case 'dmy': return $datetime->format('d F Y H:i:s');
                case 'mdy': return $datetime->format('F d, Y H:i:s');
                case 'ymd': return $datetime->format('Y F d H:i:s');
                        
            }
		}
		return substr(trim($date),-1) == 'T' ? substr($date,0, strpos($date, 'T')) : $date; 
	}
}


