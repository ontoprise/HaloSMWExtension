<?php

require_once('US_Store.php');

/**
 * @author: Kai Kühn
 *
 * Created on: 27.01.2009
 *
 */
class USStoreSQL extends USStore {

	





	public function getPropertySubjects(array $properties, array $namespace, $requestoptions) {
		$db =& wfGetDB( DB_SLAVE );
		$smw_ids = $db->tableName('smw_ids');
		$smw_atts2 = $db->tableName('smw_atts2');

		$namespaces = "";
		if ($namespace != NULL) {
			$namespaces .= '(';
			for ($i = 0, $n = count($namespace); $i < $n; $i++) {
				if ($i > 0) $namespaces .= ' OR ';
				$namespaces .= 's.smw_namespace='.$db->addQuotes($namespace[$i]);
			}
			if (count($namespace) == 0) $namespaces .= 'true';
			$namespaces .= ') ';
		} else  {
			$namespaces = 'true';
		}
		$propertyIDConstraint = "FALSE";
		foreach($properties as $p) {
			$p_id = smwfGetStore()->getSMWPropertyID($p);
			$propertyIDConstraint .= ' OR r.p_id = '.$p_id;
		}

	
		$titleConstraint1 = DBHelper::getSQLConditions($requestoptions,'r.value_xsd','r.value_xsd');
		$titleConstraint2 = DBHelper::getSQLConditions($requestoptions,'o.smw_title','o.smw_title');
		$query = '(SELECT s.smw_title AS title, s.smw_namespace AS ns FROM '.
		$smw_ids.' s JOIN '.$smw_atts2.' r ON s.smw_id = s_id WHERE '.$namespaces.' AND ('.$propertyIDConstraint.') '.$titleConstraint1.')'.
		'UNION '.
		'(SELECT s.smw_title AS title, s.smw_namespace AS ns FROM smw_rels2 r '.
		      'JOIN smw_ids s ON r.s_id = s.smw_id JOIN smw_ids o ON r.o_id = o.smw_id WHERE ('.$propertyIDConstraint.')  '.$titleConstraint2.') LIMIT 5';

		$res = $db->query($query );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->title, $row->ns);
			}
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * Returns a title if it matches the given term as single title.
	 * Case-insensitive
	 *
	 * @param string $term
	 * @return Title
	 */
	public function getSingleTitle($term) {
		$db =& wfGetDB( DB_SLAVE );
		$page = $db->tableName('page');
		$term = mysql_real_escape_string(strtoupper(str_replace(" ", "_", $term)));
		$res = $db->query('SELECT page_title, page_namespace FROM '.$page.' WHERE UPPER(page_title) = '.$db->addQuotes($term));
		$numRows = $db->numRows($res);
		if ($numRows > 1) {
			$db->freeResult($res);
			return NULL;
		}
		if ($numRows == 1) {
			$row = $db->fetchObject($res);
			$title = Title::newFromText($row->page_title, $row->page_namespace);
			$db->freeResult($res);
			return $title;
		}
		$db->freeResult($res);
		return NULL;
	}

	/**
	 * Gets all categories the given title is member of.
	 *
	 * @param Title $title
	 * @return array of Title
	 */
	public function getCategories($title) {
		$db =& wfGetDB( DB_SLAVE );
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');

		$res = $db->query('SELECT cl_to FROM '.$page.' JOIN '.$categorylinks.' WHERE cl_from = page_id AND page_title = '.$db->addQuotes($title->getDBkey()). ' AND page_namespace = '.$title->getNamespace());
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->cl_to, NS_CATEGORY);
			}
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * Gets all redirects which point to the given title.
	 *
	 * @param Title $title
	 * @return array of Title
	 */
	public function getRedirects($title) {
		$db =& wfGetDB( DB_SLAVE );
		$page = $db->tableName('page');
		$redirects = $db->tableName('redirect');

		$res = $db->query('SELECT rd_title, rd_namespace FROM '.$page.' JOIN '.$redirects.' WHERE rd_from = page_id AND page_title = '.$db->addQuotes($title->getDBkey()). ' AND page_namespace = '.$title->getNamespace());
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->rd_title, $row->rd_namespace);
			}
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * Adds (or updates) a new search statistic with given hits.
	 *
	 * @param string $searchTerm
	 * @param int $hits
	 */
	public function addSearchTry($searchTerm, $hits) {
		$db =& wfGetDB( DB_MASTER );
		$smw_searchmatch = $db->tableName('smw_searchmatches');
		$res = $db->selectRow($smw_searchmatch, array('tries'), array('searchterm'=>$searchTerm));
		if ($res !== false) {
			$db->query('UPDATE '.$smw_searchmatch.' SET tries='.($res->tries+1).', hits='.$hits.' WHERE searchterm='.$db->addQuotes(mysql_real_escape_string($searchTerm)));
		} else {
			$db->query('INSERT INTO '.$smw_searchmatch.' VALUES ('.$db->addQuotes($searchTerm).',1,'.$hits.')');
		}
	}

	/**
	 * Returns search statistics
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param 0 or 1 $ascOrDesc
	 * @param 0 or 1 $sortFor where 0 = hits, 1 = tries
	 * @return array($row->searchterm, $row->tries, $row->hits);
	 */
	public function getSearchTries($limit, $offset, $ascOrDesc, $sortFor) {
		$db =& wfGetDB( DB_SLAVE );
		$smw_searchmatch = $db->tableName('smw_searchmatches');

		switch($ascOrDesc) {
			case 0: $ascOrDesc = "ASC";break;
			case 1: $ascOrDesc = "DESC";break;
			default: $ascOrDesc = "ASC";break;
		}

		switch($sortFor) {
			case 0: $sortFor = "hits $ascOrDesc, tries DESC";break;
			case 1: $sortFor = "tries $ascOrDesc, hits DESC";break;
			default: $sortFor = "hits $ascOrDesc, tries DESC";break;
		}

		$res = $db->select($smw_searchmatch, array('searchterm', 'tries', 'hits'), array(), '', array('LIMIT'=>$limit, 'OFFSET'=>$offset, 'ORDER BY'=>$sortFor));
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array($row->searchterm, $row->tries, $row->hits);
			}
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * Setups database for UnifiedSearch extension
	 *
	 * @param boolean $verbose
	 */
	public function setup($verbose) {
		if ($verbose) print ("Creating tables for Unified search...\n");
		$db =& wfGetDB( DB_MASTER );
		$smw_searchmatch = $db->tableName('smw_searchmatches');
		$db->query('CREATE TABLE IF NOT EXISTS '.$smw_searchmatch.' (searchterm VARCHAR(255), tries INTEGER, hits INTEGER)');
		if ($verbose) print("..done\n");
	}
}
?>