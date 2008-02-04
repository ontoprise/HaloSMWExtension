<?php
/*
 * Created on 16.03.2007
 *
 * Author: kai
 */
 global $smwgHaloIP;
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningIssues.php");
 
 define('SMW_GARD_RESULT_LIMIT_DEFAULT', 100);
 define('SMW_GARD_SIM_LIMIT_DEFAULT', 0);
 define('SMW_GARD_SIM_DEGREE_DEFAULT', 1);
 
 class SimilarityBot extends GardeningBot {
 	
 	// common prefixes and suffixes
 	private $commonSuffixes;
 	private $commonPrefixes;
 	
 	private $gi_store;
 	
 	function __construct() {
 		parent::GardeningBot("smw_similaritybot");
 		
 		$this->commonSuffixes = array('of');
 		$this->commonPrefixes = array('has');
 		
 	}
 	
 	public function getHelpText() {
 		return wfMsg('smw_gard_similaritybothelp');
 	}
 	
 	public function getLabel() {
 		return wfMsg($this->id);
 	}
 	
 	public function allowedForUserGroups() {
 		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
 	}
 	
 	/**
 	 * Returns an array of parameter objects
 	 */
 	public function createParameters() {
 		$params = array();
 		
 		$param2 = new GardeningParamListOfValues('SI_DEGREE', wfMsg('smw_gard_degreeofsimilarity'), SMW_GARD_PARAM_OPTIONAL, array('1','2','3'));
 		$param3 = new GardeningParamNumber('SI_RESULT_LIMIT', wfMsg('smw_gard_limitofresults'), SMW_GARD_PARAM_OPTIONAL, 0, 1000);
 		$param4 = new GardeningParamNumber('SI_SIM_LIMIT', wfMsg('smw_gard_limitofsim'), SMW_GARD_PARAM_OPTIONAL, 0, 3);
 		$param5 = new GardeningParamBoolean('SI_INC_ANNOT', "Include annotations (only global search)", SMW_GARD_PARAM_OPTIONAL, false);

 		
 		$params[] = $param2;
 		$params[] = $param3;
 		$params[] = $param4;
 		$params[] = $param5;
 		return $params;
 	}
 	
 	/**
 	 * Do similarity checks and return a log as wiki markup.
 	 * Do not use echo when it is not running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		$this->gi_store = SMWGardening::getGardeningIssuesAccess();
 		echo "...started!\n";
 		$result = "";	 		
 		
 		$similarityDegree = array_key_exists('SI_DEGREE', $paramArray) ? $paramArray['SI_DEGREE'] : SMW_GARD_SIM_DEGREE_DEFAULT;
 		$limitOfResults = $paramArray['SI_RESULT_LIMIT'] == '' ? SMW_GARD_RESULT_LIMIT_DEFAULT : $paramArray['SI_RESULT_LIMIT']+0;;
 		$limitOfSim = $paramArray['SI_SIM_LIMIT'] == '' ? SMW_GARD_SIM_LIMIT_DEFAULT : $paramArray['SI_SIM_LIMIT']+0;
 		$includeAnnotations = array_key_exists('SI_INC_ANNOT', $paramArray);
 		 
 		
 			// global search
 			echo "\nDo a global search for schema similarities of degree $similarityDegree...";
 			$similarities = $this->getAllSimilarTitles($similarityDegree, $limitOfResults);
 			echo "done!\n";
 			echo "Further investigations of found entities...";
 			foreach($similarities as $s) {
				$s->calcSimilarityFactor($this->commonPrefixes, $this->commonSuffixes); 			
 			} 
 			SimilarityBot::sortSimilarityArray($similarities);
 			foreach($similarities as $s) {
				$s->storeSchemaResults($this->gi_store, $this->id, $limitOfSim);
 			}
			echo "done!.\n";
			
			if ($includeAnnotations) {
				echo "\nSearching for similarities on annotation level...";
				$annotationSimilarities = $this->getAllSimilarAnnotations(NULL, $similarityDegree, $limitOfResults);
				foreach($annotationSimilarities as $as) {
					$as->storeAnnotationLevelResults($this->gi_store, $this->id);
				}
				echo "done!";
			}
			
 			return $result;
 		
	
 	}
 	 	
 	/**
 	 * Returns all titles matching any other 
 	 * with the given maximum edit distance.
 	 * 
 	 * Warning: may take some time!
 	 */
 	private function getAllSimilarTitles($similarityDegree, $limitOfResults) {
 		$dbr =& wfGetDB( DB_SLAVE );
 		$result = array();
 		if (!is_numeric($similarityDegree) || !is_numeric($limitOfResults)) return array();
 		$mw_page = $dbr->tableName('page');
		$cond = array();
 		foreach($this->commonPrefixes as $prefix) {
 			$cond[] = 'EDITDISTANCE(UPPER(p1.page_title),UPPER(CONCAT(\''.$prefix.'\',p2.page_title))) <= '.$similarityDegree.' OR ';
 		}
 		foreach($this->commonSuffixes as $suffix) {
 			$cond[] = 'EDITDISTANCE(UPPER(p1.page_title),UPPER(CONCAT(p2.page_title,\''.$suffix.'\'))) <= '.$similarityDegree.' OR ';
 		}
 		$cond[] = 'FALSE'; // end last OR condition
 		
 		// Calculate similar terms
 		// make sure that pages starting with 'Smw' are ignored because they are internal (such as logs).
 		$res = $dbr->query('SELECT p1.page_title AS page1, p2.page_title AS page2, p1.page_namespace AS pagenamespace1, p2.page_namespace AS pagenamespace2 FROM '.$mw_page.' p1, '.$mw_page.' p2 ' .
 							 'WHERE p1.page_is_redirect = 0 AND p2.page_is_redirect = 0 AND p1.page_title != p2.page_title  AND p1.page_namespace != '.NS_IMAGE.' AND p1.page_title NOT LIKE \'Smw%\' AND (EDITDISTANCE(UPPER(p1.page_title), UPPER(p2.page_title)) <= '.$similarityDegree.' ' .
 							 		'OR '.implode("",$cond).') LIMIT '.$limitOfResults);
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
					$title1 = Title::newFromText($row->page1, $row->pagenamespace1);
					$title2 = Title::newFromText($row->page2, $row->pagenamespace2);
					if (!is_object($title1) || !is_object($title2) || $title1 == NULL || $title2 == NULL) { 
						continue;
					} 
					// do not add doubles
					if (!SimilarityBot::containsSimilarity(new SchemaSimilarity($title2, $title1, $similarityDegree), $result)) { 
						$result[] = new SchemaSimilarity($title1, $title2, $similarityDegree);
					}
			}
			$dbr->freeResult( $res );
		}
		
 		 		
		return $result;
 	}
 	
 	/**
 	 * Returns similar property annotations which exist only as annotations, but not as pages. 
 	 */
 	private function getAllSimilarAnnotations($similarityTerm, $similarityDegree, $limitOfResults = NULL) {
 		$dbr =& wfGetDB( DB_SLAVE );
 		$result = array();
 		if (!is_numeric($similarityDegree)) return array();
 		if (!is_numeric($limitOfResults) && $limitOfResults != NULL) return array();
	 	$smw_attributes = $dbr->tableName('smw_attributes');
	 	$smw_relations = $dbr->tableName('smw_relations');
	 	$smw_nary = $dbr->tableName('smw_nary');		
	 	$mw_page = $dbr->tableName('page');
	 	$nameRestriction = "";
	 	if ($similarityTerm != NULL) {
	 		$similarityTerm = str_replace(" ", "_", $similarityTerm);
	 		$nameRestriction = "AND sa.relation_title LIKE '%".mysql_real_escape_string($similarityTerm)."%'";
	 	}
 		// Get similar attribute annotations which have no attribute page defined.
 		$res = $dbr->query('SELECT DISTINCT sa.attribute_title AS att1, sa.subject_title AS subject, sa.subject_namespace AS namespace, sa2.attribute_title AS att2, EDITDISTANCE(UPPER(sa.attribute_title), UPPER(sa2.attribute_title)) AS distance FROM '.$smw_attributes.' sa LEFT JOIN '.$mw_page.' p ON p.page_title = sa.attribute_title INNER JOIN '.$smw_attributes.' sa2 ' .
 				'WHERE sa.attribute_title != sa2.attribute_title '.$nameRestriction.' AND p.page_title IS NULL ' .
 				'AND EDITDISTANCE(UPPER(sa.attribute_title), UPPER(sa2.attribute_title)) <= '.$similarityDegree.(($limitOfResults != NULL) ? ' LIMIT '.$limitOfResults : ""));
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
					$title1 = Title::newFromText($row->att1, SMW_NS_PROPERTY);
					$title2 = Title::newFromText($row->att2, SMW_NS_PROPERTY);
					$article = Title::newFromText($row->subject, $row->namespace);
					$editdistance = $row->distance; 
					// do not add doubles
					$result[] = new AnnotationSimilarity($title1, $title2, $article, $editdistance);
					
			}
			$dbr->freeResult( $res );
		}
		$res = $dbr->query('SELECT DISTINCT sa.relation_title AS att1, sa.subject_title AS subject, sa.subject_namespace AS namespace, sa2.relation_title AS att2, EDITDISTANCE(UPPER(sa.relation_title), UPPER(sa2.relation_title)) AS distance FROM '.$smw_relations.' sa LEFT JOIN '.$mw_page.' p ON p.page_title = sa.relation_title INNER JOIN '.$smw_relations.' sa2 ' .
				'WHERE sa.relation_title != sa2.relation_title '.$nameRestriction.' AND p.page_title IS NULL ' .
				'AND EDITDISTANCE(UPPER(sa.relation_title), UPPER(sa2.relation_title)) <= '.$similarityDegree.(($limitOfResults != NULL) ? ' LIMIT '.$limitOfResults : ""));
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
					$title1 = Title::newFromText($row->att1, SMW_NS_PROPERTY);
					$title2 = Title::newFromText($row->att2, SMW_NS_PROPERTY);
					$article = Title::newFromText($row->subject, $row->namespace);
					$editdistance = $row->distance; 
					// do not add doubles
					 
					$result[] = new AnnotationSimilarity($title1, $title2, $article, $editdistance);
					
			}
			$dbr->freeResult( $res );
		}
		$res = $dbr->query('SELECT DISTINCT sa.attribute_title AS att1, sa.subject_title AS subject, sa.subject_namespace AS namespace , sa2.attribute_title AS att2, EDITDISTANCE(UPPER(sa.attribute_title), UPPER(sa2.attribute_title)) AS distance FROM '.$smw_nary.' sa LEFT JOIN '.$mw_page.' p ON p.page_title = sa.attribute_title INNER JOIN '.$smw_nary.' sa2 ' .
 				'WHERE sa.attribute_title != sa2.attribute_title '.$nameRestriction.' AND p.page_title IS NULL ' .
 				'AND EDITDISTANCE(UPPER(sa.attribute_title), UPPER(sa2.attribute_title)) <= '.$similarityDegree.(($limitOfResults != NULL) ? ' LIMIT '.$limitOfResults : ""));
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
					$title1 = Title::newFromText($row->att1, SMW_NS_PROPERTY);
					$title2 = Title::newFromText($row->att2, SMW_NS_PROPERTY);
					$article = Title::newFromText($row->subject, $row->namespace);
					$editdistance = $row->distance; 
					// do not add doubles
				 
					$result[] = new AnnotationSimilarity($title1, $title2, $article, $editdistance);
				
			}
			$dbr->freeResult( $res );
		}
		return $result;
 	}
 	
 	 	
 	/**
 	 * Helper function to determine if an array of Similarities contain
 	 * a certain similarity object.
 	 */
 	private static function containsSimilarity(SchemaSimilarity $s, array & $similarities) {
 		foreach($similarities as $sim) {
 			if ($sim != NULL && $s != NULL && $sim->equals($s)) {
 				return true;
 			}
 		}
 		return false;
 	}
 	
 	
 	
 	private static function sortSimilarityArray(array & $similarities) {
 		for ($i = 0, $n = count($similarities); $i < $n; $i++) {
 			for ($j = 0; $j < $n-1; $j++) {
 				if ($similarities[$j]->getSimilarityFactor() < $similarities[$j+1]->getSimilarityFactor()) {
 					$help = $similarities[$j];
 					$similarities[$j] = $similarities[$j+1];
 					$similarities[$j+1] = $help;
 				}
 			}
 		}
 	}
 }
 
 /**
  * Represents two lexical similar Titles. 
  * Allows also additional operations to investigate similarity further.
  */
 class SchemaSimilarity {
 	
 	// two titles to be compared
 	private $title1;
 	private $title2;
 	
 	// shared entity properties
 	private $sharedCategories;
 	private $sharedDomainCategories;
 	private $sharedRangeCategories;
 	private $sharedTypes;
 	private $distinctByCommonPrefixOrSuffix = false;
 	
 	// similarity factor is a measure for similarity between the two titles
 	private $similarityFactor;
 	
 	// lexical similarity (=edit distance) between the two titles
 	private $similarityDegree;
 	
  	
 	public function SchemaSimilarity(Title $t1, Title $t2, $similarityDegree) {
 		$this->title1 = $t1;
 		$this->title2 = $t2;
 		$this->similarityDegree = $similarityDegree;
 		
 		
 		
 	}
 	
 	public function getTitle1() {
 		return $this->title1;
 	}
 	
 	public function getTitle2() {
 		return $this->title2;
 	}
 	
 	public function getSimilarityFactor() {
 		return $this->similarityFactor;
 	}
 	
 	
 	
 	public function equals(SchemaSimilarity $s) {
 		return ($this->title1->equals($s->getTitle1()) && $this->title2->equals($s->getTitle2()));
 	}
 	 
 	
 	
 	public function calcSimilarityFactor(array & $commonPrefixes, array & $commonSuffixes) {
 		
 		if ($this->title1->getNamespace() == $this->title2->getNamespace()) {
 			switch($this->title1->getNamespace()) {
 				case SMW_NS_PROPERTY: {
 					$this->sharedDomainCategories = $this->getSharedDomainCategories();
 					$this->sharedTypes = $this->getSharedTypes();
 					$numOfSharedDomains = count($this->sharedDomainCategories);
 					$numOfSharedTypes = count($this->getSharedTypes());
 					$numOfSharedRanges = count($this->getSharedRangeCategories());
 					$this->similarityFactor = (1 / $this->similarityDegree) * (1+$numOfSharedDomains) * (1+$numOfSharedTypes) * (1+$numOfSharedRanges);
 					break;
 				}
 				case SMW_NS_RELATION: {
 					$this->sharedDomainCategories = $this->getSharedDomainCategories();
 					$numOfSharedDomains = count($this->sharedDomainCategories);
 					$numOfSharedRanges = count($this->getSharedRangeCategories());
 					$this->similarityFactor = (1 / $this->similarityDegree) * (1+$numOfSharedDomains) * (1+$numOfSharedRanges);
 					break;
 				}
 				case NS_CATEGORY: {
 					$this->sharedCategories = $this->getSharedMemberCategories();
 					$numOfSharedCats = count($this->sharedCategories);
 					$this->similarityFactor = (1 / $this->similarityDegree) * (1+$numOfSharedCats);
 					break;
 				}
 				case NS_MAIN: {
 					$this->sharedCategories = $this->getSharedMemberCategories();
 					$numOfSharedCats = count($this->sharedCategories);
 					$this->similarityFactor = (1 / $this->similarityDegree) * (1+$numOfSharedCats);
 					break;
 				}
 			}
 		} else {
 			$this->similarityFactor = 0;
 		}
 		if ($this->distinctByCommonPrefixOrSuffix($commonPrefixes, $commonSuffixes)) {
 			$this->similarityFactor++;
 			$this->distinctByCommonPrefixOrSuffix = true;
 		}
 		
 		
 	}
 	
 	public function storeSchemaResults(& $gi_store, $bot_id, $simlimit) {
 		if ($this->getSimilarityFactor() > $simlimit) { 
 				$gi_store->addGardeningIssueAboutArticles($bot_id, SMW_GARDISSUE_SIMILAR_SCHEMA_ENTITY, $this->getTitle1(), $this->getTitle2(), $this->getSimilarityFactor());
 				
 				
 				if ($this->isDistinctByCommonPrefixOrSuffix()) {
 					$gi_store->addGardeningIssueAboutArticles($bot_id, SMW_GARDISSUE_DISTINCTBY_PREFIX, $this->getTitle1(), $this->getTitle2());
 					
 				}
 				$this->storeSharedEntities($gi_store, $bot_id);
 			}
 		
 	}
 	
 	public function storeTermMatchingResults(& $gi_store, $bot_id, $similarityTerm) {
 		$gi_store->addGardeningIssueAboutValue($bot_id, SMW_GARDISSUE_SIMILAR_TERM, $t->getTitle1(), $similarityTerm);
 	}
 	
 	private function isDistinctByCommonPrefixOrSuffix() {
 		return $this->distinctByCommonPrefixOrSuffix;
 	}
 	
 	private function storeSharedEntities(& $gi_store, $bot_id) {
 		global $wgLang;
 		
 		if (count($this->sharedCategories) > 0) {
 				
 				$value = "";
 				foreach($this->sharedCategories as $cat) {
 					$value .= $wgLang->getNsText(NS_CATEGORY).":".$cat->getText().";";
 				}
 				$gi_store->addGardeningIssueAboutArticles($bot_id, SMW_GARDISSUE_SHARE_CATEGORIES, $this->getTitle1(), $this->getTitle2(), $value);
 				
 		}
 		if (count($this->sharedDomainCategories) > 0) {
 				$value = "";
 				foreach($this->sharedDomainCategories as $cat) {
 					$value .= $wgLang->getNsText(NS_CATEGORY).":".$cat->getText().";";
 					
 				}
 				$gi_store->addGardeningIssueAboutArticles($bot_id, SMW_GARDISSUE_SHARE_DOMAINS, $this->getTitle1(), $this->getTitle2(), $value);
 			
 		}
 		if (count($this->sharedRangeCategories) > 0) {
 				$value = "";
 				foreach($this->sharedRangeCategories as $cat) {
 					$value .= $wgLang->getNsText(NS_CATEGORY).":".$cat->getText().";";
 					
 				}
 				$gi_store->addGardeningIssueAboutArticles($bot_id, SMW_GARDISSUE_SHARE_RANGES, $this->getTitle1(), $this->getTitle2(), $value);
 				
 		}
 		if (count($this->sharedTypes) > 0) {
 				$value = "";
 				foreach($this->sharedTypes as $type) {
 						$value .= $wgLang->getNsText(SMW_NS_TYPE).":".$type.";";
 				}
 				$gi_store->addGardeningIssueAboutArticles($bot_id, SMW_GARDISSUE_SHARE_TYPES, $this->getTitle1(), $this->getTitle2(), $value);
 			
 		}
 		
 	}
 	
 	private function distinctByCommonPrefixOrSuffix(array & $commonPrefixes, array & $commonSuffixes) {
 		foreach($commonPrefixes as $prefix) {
 			if ((preg_match("/^$prefix\s*/i", $this->getTitle1()->getText()) > 0 && preg_match("/^$prefix\s*/i", $this->getTitle2()->getText()) == 0)
 				|| (preg_match("/^$prefix\s*/i", $this->getTitle2()->getText()) > 0 && preg_match("/^$prefix\s*/i", $this->getTitle1()->getText()) == 0)) {
 				return true;
 			}
 		}
 		foreach($commonSuffixes as $suffix) {
 			if ((preg_match("/\s*$suffix".'$'."/i", $this->getTitle1()->getText()) > 0 && preg_match("/\s*$suffix".'$'."/i", $this->getTitle2()->getText()) == 0)
 				|| (preg_match("/\s*$suffix".'$'."/i", $this->getTitle2()->getText()) > 0 && preg_match("/\s*$suffix".'$'."/i", $this->getTitle1()->getText()) == 0)) {
 				return true;
 			}
 		}
 		return false;
 	}
 	
 	private function getSharedMemberCategories() {
		
		$db =& wfGetDB( DB_MASTER );
		$smw_categorylinks = $db->tableName('categorylinks');			
		$res = $db->query('SELECT c1.cl_to FROM '.$smw_categorylinks.' c1, '.$smw_categorylinks.' c2 ' .
				'WHERE c1.cl_from = '.$this->title1->getArticleID(). ' AND c2.cl_from = '.$this->title2->getArticleID().' AND c1.cl_to = c2.cl_to');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->cl_to, NS_CATEGORY);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	private function getSharedDomainCategories() {
 		global $smwgHaloContLang;
 		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		
		$domainRangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT], SMW_NS_PROPERTY);
		
		
 		$db =& wfGetDB( DB_MASTER );
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_relations = $db->tableName('smw_nary_relations');
		$res = $db->query('SELECT DISTINCT r1.object_title AS cat FROM '.$smw_nary.' n1, '.$smw_nary.' n2, '.$smw_nary_relations.' r1, '.$smw_nary_relations.' r2 ' .
				'WHERE n1.subject_id = '.$this->title1->getArticleID(). ' AND n2.subject_id = '.$this->title2->getArticleID().' AND n1.subject_id = r1.subject_id AND n2.subject_id = r2.subject_id'.
				' AND n1.attribute_title = '.$db->addQuotes($domainRangeHintRelation->getDBkey()).' AND n2.attribute_title = '.$db->addQuotes($domainRangeHintRelation->getDBkey()).
				' AND r1.object_title = r2.object_title AND r1.nary_pos=0 AND r2.nary_pos=0');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->cat, NS_CATEGORY);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	private function getSharedRangeCategories() {
 		global $smwgHaloContLang;
 		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		
		$domainRangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT], SMW_NS_PROPERTY);
		
 		$db =& wfGetDB( DB_MASTER );
		$smw_nary = $db->tableName('smw_nary');
		$smw_nary_relations = $db->tableName('smw_nary_relations');	
		$res = $db->query('SELECT DISTINCT r1.object_title AS cat FROM '.$smw_nary.' n1, '.$smw_nary.' n2, '.$smw_nary_relations.' r1, '.$smw_nary_relations.' r2 ' .
				'WHERE n1.subject_id = '.$this->title1->getArticleID(). ' AND n2.subject_id = '.$this->title2->getArticleID().' AND n1.subject_id = r1.subject_id AND n2.subject_id = r2.subject_id'.
				' AND n1.attribute_title = '.$db->addQuotes($domainRangeHintRelation->getDBkey()).' AND n2.attribute_title = '.$db->addQuotes($domainRangeHintRelation->getDBkey()).
				' AND r1.object_title = r2.object_title AND r1.nary_pos=1 AND r2.nary_pos=1');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->cat, NS_CATEGORY);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	private function getSharedTypes() {
 		$db =& wfGetDB( DB_MASTER );
		global $smwgHaloContLang;
		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$smw_specialprops = $db->tableName('smw_specialprops');		
		$res = $db->query('SELECT s1.value_string AS type FROM '.$smw_specialprops.' s1, '.$smw_specialprops.' s2 ' .
				'WHERE s1.subject_id = '.$this->title1->getArticleID(). ' AND s2.subject_id = '.$this->title2->getArticleID().
				' AND s1.property_id = '.SMW_SP_HAS_TYPE.' AND s2.property_id = '.SMW_SP_HAS_TYPE.
				' AND s1.value_string = s2.value_string');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = SMWDataValueFactory::findTypeLabel($row->type);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 }
 
 class AnnotationSimilarity {
 	
 	private $title1;
 	private $title2;
 	
 	private $article;
 	private $editdistance;
 	
 	public function AnnotationSimilarity(Title $t1, Title $t2, Title $article) {
 		$this->title1 = $t1;
 		$this->title2 = $t2;
 		$this->article = $article;
 	}
 	
 	public function getTitle1() {
 		return $this->title1;
 	}
 	
 	public function getTitle2() {
 		return $this->title2;
 	}
 	
 	public function getArticle() {
 		return $this->article;
 	}
 	
 	public function getSimilarityFactor() {
 		return 3 - $this->editdistance;
 	}
 	
 	public function storeAnnotationLevelResults(& $gi_store, $bot_id) {
 		$gi_store->addGardeningIssueAboutArticles($bot_id, SMW_GARDISSUE_SIMILAR_ANNOTATION, $this->getTitle1(), $this->getTitle2(), $this->getArticle()->getNsText().':'.$this->getArticle()->getText(), $this->getSimilarityFactor());
 	}
 }
 // instantiate once (if editdistance function is supported).
 if (smwfDBSupportsFunction('halowiki')) {
 	new SimilarityBot();
 }
 
 define('SMW_SIMILARITY_BOT_BASE', 200);
 define('SMW_GARDISSUE_SIMILAR_SCHEMA_ENTITY', SMW_SIMILARITY_BOT_BASE * 100 + 1);
 define('SMW_GARDISSUE_SIMILAR_TERM', SMW_SIMILARITY_BOT_BASE * 100 + 2);
 define('SMW_GARDISSUE_DISTINCTBY_PREFIX', SMW_SIMILARITY_BOT_BASE * 100 + 3);
 define('SMW_GARDISSUE_SHARE_CATEGORIES', SMW_SIMILARITY_BOT_BASE * 100 + 4);
 define('SMW_GARDISSUE_SHARE_DOMAINS', SMW_SIMILARITY_BOT_BASE * 100 + 5);
 define('SMW_GARDISSUE_SHARE_RANGES', SMW_SIMILARITY_BOT_BASE * 100 + 6);
 define('SMW_GARDISSUE_SHARE_TYPES', SMW_SIMILARITY_BOT_BASE * 100 + 7);
 
 define('SMW_GARDISSUE_SIMILAR_ANNOTATION', (SMW_SIMILARITY_BOT_BASE+1) * 100 + 1);
        
 
 class SimilarityBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
 	}
 	
 	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
 			$text1 = $local ? wfMsg('smw_gard_issue_local') : $text1;
			switch($this->gi_type) {
				case SMW_GARDISSUE_SIMILAR_SCHEMA_ENTITY:
					return wfMsg('smw_gardissue_similar_schema_entity', $text1,  $this->t2->getText());
				case SMW_GARDISSUE_SIMILAR_ANNOTATION:
					$article = Title::newFromText($this->value);
					return wfMsg('smw_gardissue_similar_annotation',  $text1, $this->t2->getText(), $article != NULL ? $skin->makeLinkObj($article) : '');
				case SMW_GARDISSUE_SIMILAR_TERM:
					return wfMsg('smw_gardissue_similar_term',  $text1, $this->value);
				case SMW_GARDISSUE_SHARE_CATEGORIES:
					
					return wfMsg('smw_gardissue_share_categories',  $text1, $this->t2->getText(), $this->explodeTitlesToLinkObjs($skin, $this->value));
				case SMW_GARDISSUE_SHARE_DOMAINS:
					
					return wfMsg('smw_gardissue_share_domains',  $text1, $this->t2->getText(), $this->explodeTitlesToLinkObjs($skin, $this->value));
				case SMW_GARDISSUE_SHARE_RANGES:
					
					return wfMsg('smw_gardissue_share_ranges',  $text1, $this->t2->getText(), $this->explodeTitlesToLinkObjs($skin, $this->value));
				case SMW_GARDISSUE_SHARE_TYPES:
					
					return wfMsg('smw_gardissue_share_types',  $text1, $this->t2->getText(), $this->explodeTitlesToLinkObjs($skin, $this->value));
				case SMW_GARDISSUE_DISTINCTBY_PREFIX:
					return wfMsg('smw_gardissue_distinctby_prefix', $text1, $this->t2->getText());
				default: return NULL;
			}
 		
 	}
 	
 	
 	
 	
 }
 
 class SimilarityBotFilter extends GardeningIssueFilter {
 	 	
 	private $sortfor;
 	
 	public function __construct() {
 		parent::__construct(SMW_SIMILARITY_BOT_BASE);
 		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'), 
							wfMsg('smw_gardissue_class_similarschema'),
							wfMsg('smw_gardissue_class_similarannotations'));
							
		$this->sortfor = array('Alphabetically', 'Similarity score');
 	}
 	
 	public function getUserFilterControls($specialAttPage, $request) {
 		$sortfor = $request != NULL ? $request->getVal('sortfor') : 0;
		$html = " Sort by: <select name=\"sortfor\">";
		$i = 0;
		foreach($this->sortfor as $sortOption) {
			if ($i == $sortfor) {
		 		$html .= "<option value=\"$i\" selected=\"selected\">$sortOption</option>";
			} else {
				$html .= "<option value=\"$i\">$sortOption</option>";
			}
			$i++;		
		}
 		$html .= 	"</select>";
 		$matchString = $request != NULL && $request->getVal('matchString') != NULL ? $request->getVal('matchString') : "";
		return $html.' Contains:<input name="matchString" type="text" class="wickEnabled" value="'.$matchString.'"/>';
	}
	
	public function linkUserParameters(& $wgRequest) {
		return array('matchString' => $wgRequest->getVal('matchString'), 'sortfor' => $wgRequest->getVal('sortfor'));
	}
	
	public function getData($options, $request) {
		$matchString = $request->getVal('matchString');
		$sortfor = $request->getVal('sortfor');
		if ($matchString == NULL || $matchString == '') {
			return $this->getSortedData($options, $request, $sortfor == 0 ? SMW_GARDENINGLOG_SORTFORTITLE : SMW_GARDENINGLOG_SORTFORVALUE);
		} else {
			$options->addStringCondition($matchString, SMW_STRCOND_MID);
			return $this->getSortedData($options, $request, $sortfor == 0 ? SMW_GARDENINGLOG_SORTFORTITLE : SMW_GARDENINGLOG_SORTFORVALUE);
		}
	}
	
	private function getSortedData($options, $request, $sortfor) {
		$bot = $request->getVal('bot');
		if ($bot == NULL) return array(); 
		$gic = array();
		
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + SMW_SIMILARITY_BOT_BASE - 1;
		
		$gi_store = SMWGardening::getGardeningIssuesAccess();
		$titles = $gi_store->getDistinctTitlePairs($bot, NULL, $gi_class, $sortfor, $options);
		foreach($titles as $t) {
			$gis = $gi_store->getGardeningIssuesForPairs($bot, NULL, $gi_class, array($t), SMW_GARDENINGLOG_SORTFORTITLE, NULL);
			$gic[] = new GardeningIssueContainer($t, $gis);
		}
		return $gic;
	}
 }
?>
