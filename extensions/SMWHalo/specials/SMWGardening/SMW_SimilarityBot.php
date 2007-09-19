<?php
/*
 * Created on 16.03.2007
 *
 * Author: kai
 */
 require_once("SMW_GardeningBot.php");
 require_once("SMW_ParameterObjects.php");
 
 define('SMW_GARD_RESULT_LIMIT_DEFAULT', 100);
 define('SMW_GARD_SIM_LIMIT_DEFAULT', 0);
 define('SMW_GARD_SIM_DEGREE_DEFAULT', 1);
 
 class SimilarityBot extends GardeningBot {
 	
 	// common prefixes and suffixes
 	private $commonSuffixes;
 	private $commonPrefixes;
 	
 	function SimilarityBot() {
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
 		$param1 = new GardeningParamTitle('SI_TERM', wfMsg('smw_gard_similarityterm'), SMW_GARD_PARAM_OPTIONAL);
 		$param2 = new GardeningParamListOfValues('SI_DEGREE', wfMsg('smw_gard_degreeofsimilarity'), SMW_GARD_PARAM_OPTIONAL, array('1','2','3'));
 		$param3 = new GardeningParamNumber('SI_RESULT_LIMIT', wfMsg('smw_gard_limitofresults'), SMW_GARD_PARAM_OPTIONAL, 0, 1000);
 		$param4 = new GardeningParamNumber('SI_SIM_LIMIT', wfMsg('smw_gard_limitofsim'), SMW_GARD_PARAM_OPTIONAL, 0, 3);
 		$param5 = new GardeningParamBoolean('SI_INC_ANNOT', "Include annotations (only global search)", SMW_GARD_PARAM_OPTIONAL, false);
 		$param1->setAutoCompletion(true);
 		$params[] = $param1;
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
 		
 		echo "...started!\n";
 		$result = "";	 		
 		$similarityTerm = str_replace(" ","_", urldecode($paramArray['SI_TERM']));
 		$similarityDegree = array_key_exists('SI_DEGREE', $paramArray) ? $paramArray['SI_DEGREE'] : SMW_GARD_SIM_DEGREE_DEFAULT;
 		$limitOfResults = $paramArray['SI_RESULT_LIMIT'] == '' ? SMW_GARD_RESULT_LIMIT_DEFAULT : $paramArray['SI_RESULT_LIMIT']+0;;
 		$limitOfSim = $paramArray['SI_SIM_LIMIT'] == '' ? SMW_GARD_SIM_LIMIT_DEFAULT : $paramArray['SI_SIM_LIMIT']+0;
 		$includeAnnotations = array_key_exists('SI_INC_ANNOT', $paramArray);
 		 
 		if ($similarityTerm == '') {
 			// global search
 			echo "\nDo a global search for schema similarities of degree $similarityDegree...";
 			$similarities = $this->getAllSimilarTitles(NULL, $similarityDegree, $limitOfResults);
 			echo "done!\n";
 			echo "Further investigations of found entities...";
			$result = $this->formatSchemaResults($similarityTerm, $similarities, $limitOfSim);
			echo "done!.\n";
			
			if ($includeAnnotations) {
				echo "\nSearching for similarities on annotation level...";
				$annotationSimilarities = $this->getAllSimilarAnnotations(NULL, $similarityDegree, $limitOfResults);
				$result .= $this->formatAnnotationLevelResults($annotationSimilarities);
				echo "done!";
			}
			
 			return $result;
 		} else {
 			// local search
 			echo "\nSearch for similar entities of '".$similarityTerm."'...";
 			$similarities = $this->getSimilarTitlesForTerm($similarityTerm, $similarityDegree);
 			echo "done!\n"; 			
 			$result .= $this->formatTermMatchingResults($similarityTerm, $similarities, $limitOfSim);
 			
 			if ($includeAnnotations) {
				echo "\nSearching for similarities on annotation level...";
				$annotationSimilarities = $this->getAllSimilarAnnotations($similarityTerm, $similarityDegree, $limitOfResults);
				$result .= $this->formatAnnotationLevelResults($annotationSimilarities);
				echo "done!";
			}
			return $result;
 		}
	
 	}
 	
 	private function getSimilarTitlesForTerm($similarityTerm, $similarityDegree) {
 		$similarityTermWithoutSpaces = preg_replace("/\s+/", "", $similarityTerm);
 		$t1 = $this->getSimilarTitles($similarityTerm, $similarityDegree);
 		$t2 = $this->getSimilarTitlesWithCommonMods($similarityTerm, $similarityDegree);
 			
 		// without spaces
 		$t3 = $this->getSimilarTitles($similarityTermWithoutSpaces, $similarityDegree);
 		$t4 = $this->getSimilarTitlesWithCommonMods($similarityTermWithoutSpaces, $similarityDegree);
 		
 		
 		
 		$uniqueTitles = array();
 		// eliminate duplicates
 		foreach($t1 as $t) {
 			if (!SimilarityBot::containsTitle($t, $uniqueTitles)) {
 				$uniqueTitles[] = $t;
 			}
 		}
 		foreach($t2 as $t) {
 			if (!SimilarityBot::containsTitle($t,$uniqueTitles)) {
 				$uniqueTitles[] = $t;
 			}
 		}
 		foreach($t3 as $t) {
 			if (!SimilarityBot::containsTitle($t, $uniqueTitles)) {
 				$uniqueTitles[] = $t;
 			}
 		}
 		foreach($t4 as $t) {
 			if (!SimilarityBot::containsTitle($t, $uniqueTitles)) {
 				$uniqueTitles[] = $t;
 			}
 		}
 		
 		return $uniqueTitles;
 	}
 	
 	
 	
 	
 	private function formatSchemaResults($similarityTerm = NULL, $similarities, $simlimit) {
 		global $smwgContLang;
 		$namespaces = $smwgContLang->getNamespaceArray();
 		$markup = $similarityTerm != NULL ? "'''$similarityTerm''' is similar to the following entities: \n" : "";
 		foreach($similarities as $t) {
			$t->calcSimilarityFactor($this->commonPrefixes, $this->commonSuffixes); 			
 		} 
 		SimilarityBot::sortSimilarityArray($similarities);
 		foreach($similarities as $t) {
 			if ($t->getSimilarityFactor() > $simlimit) { 
 				$markup .= "* ".SimilarityBot::getWikiLink($t->getTitle1())." ".wfMsg('smw_gard_issimilarto')." ".SimilarityBot::getWikiLink($t->getTitle2())."\n";
 				$markup .= "**".wfMsg('smw_gard_similarityscore').": ".$t->getSimilarityFactor()."\n";
 				if ($t->isDistinctByCommonPrefixOrSuffix()) {
 					$markup .= "**".wfMsg('smw_gard_disticntbycommonfix')."\n";
 				}
 				$markup .= $t->formatSharedEntities();
 			}
 		}
 		return $markup;
 	}
 	
 	private function formatTermMatchingResults($similarityTerm, $similarities, $limitOfSim) {
 		$markup = "\n== Similar entities to $similarityTerm ==\n";
 		foreach($similarities as $t) {
 			$markup .= "* ".SimilarityBot::getWikiLink($t);
 		}
 		return $markup;
 	}
 	
 	private function formatAnnotationLevelResults($similarities) {
 		$markup = "\n== Annotation similarities ==\n";
 		foreach($similarities as $t) {
 			$markup .= "* ".wfMsg('smw_gard_similarannotation',SimilarityBot::getWikiLink($t->getTitle1()), SimilarityBot::getWikiLink($t->getArticle()), SimilarityBot::getWikiLink($t->getTitle2()));
 		}
 		return $markup;
 	}
 	
 	
 	
 		
 	/**
 	 * Returns titles matching the given $similarity term 
 	 * with the given maximum edit distance.
 	 */
 	private function getSimilarTitles($similarityTerm, $similarityDegree) {
 		$dbr =& wfGetDB( DB_MASTER );
 		$result = array();
 		// Calculate terms similar to $similarityTerm
 		$res = $dbr->query('SELECT DISTINCT(page_title), page_namespace FROM page p WHERE EDITDISTANCE(UPPER(p.page_title),UPPER(\''.$similarityTerm.'\')) <= '.$similarityDegree.";");
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$dbr->freeResult( $res );
		return $result;
 	}
 	
 	private function getSimilarTitlesWithCommonMods($similarityTerm, $similarityDegree) {
 		$dbr =& wfGetDB( DB_SLAVE );
 		$cond = array();
 		foreach($this->commonPrefixes as $prefix) {
 			$cond[] = 'EDITDISTANCE(UPPER(page_title),UPPER(\''.$prefix.$similarityTerm.'\')) <= '.$similarityDegree.' OR ';
 		}
 		foreach($this->commonSuffixes as $suffix) {
 			$cond[] = 'EDITDISTANCE(UPPER(page_title),UPPER(\''.$similarityTerm.$suffix.'\')) <= '.$similarityDegree.' OR ';
 		}
 		$cond[] = 'FALSE'; // end last OR condition
 		
 		$result = array();
 			// Calculate terms similar to $similarityTerm
 			$res = $dbr->query('SELECT DISTINCT(page_title), page_namespace FROM page WHERE '.implode("",$cond));
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
			}
		}
		$dbr->freeResult( $res );
			return $result;
 	}
 	
 	
 	/**
 	 * Returns all titles matching any other 
 	 * with the given maximum edit distance.
 	 * 
 	 * Warning: may take some time!
 	 */
 	private function getAllSimilarTitles($similarityTerm, $similarityDegree, $limitOfResults) {
 		$dbr =& wfGetDB( DB_SLAVE );
 		$result = array();
 		
		$cond = array();
 		foreach($this->commonPrefixes as $prefix) {
 			$cond[] = 'EDITDISTANCE(UPPER(p1.page_title),UPPER(CONCAT(\''.$prefix.'\',p2.page_title))) <= '.$similarityDegree.' OR ';
 		}
 		foreach($this->commonSuffixes as $suffix) {
 			$cond[] = 'EDITDISTANCE(UPPER(p1.page_title),UPPER(CONCAT(p2.page_title,\''.$suffix.'\'))) <= '.$similarityDegree.' OR ';
 		}
 		$cond[] = 'FALSE'; // end last OR condition
 		
 		// Calculate similar terms
 		$res = $dbr->query('SELECT p1.page_title AS page1, p2.page_title AS page2, p1.page_namespace AS pagenamespace1, p2.page_namespace AS pagenamespace2 FROM page p1, page p2 WHERE p1.page_title != p2.page_title AND (EDITDISTANCE(UPPER(p1.page_title), UPPER(p2.page_title)) <= '.$similarityDegree.' OR '.implode("",$cond).') LIMIT '.$limitOfResults);
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
	 	
	 	$nameRestriction = "";
	 	if ($similarityTerm != NULL) {
	 		$similarityTerm = str_replace(" ", "_", $similarityTerm);
	 		$nameRestriction = "AND sa.relation_title LIKE '%$similarityTerm%'";
	 	}
 		// Get similar attribute annotations which have no attribute page defined.
 		$res = $dbr->query('SELECT DISTINCT sa.attribute_title AS att1, sa.subject_title AS subject, sa.subject_namespace AS namespace, sa2.attribute_title AS att2 FROM smw_attributes sa LEFT JOIN page p ON p.page_title = sa.attribute_title INNER JOIN smw_attributes sa2 ' .
 				'WHERE sa.attribute_title != sa2.attribute_title '.$nameRestriction.' AND p.page_title IS NULL ' .
 				'AND EDITDISTANCE(UPPER(sa.attribute_title), UPPER(sa2.attribute_title)) <= '.$similarityDegree.(($limitOfResults != NULL) ? ' LIMIT '.$limitOfResults : ""));
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
					$title1 = Title::newFromText($row->att1, SMW_NS_PROPERTY);
					$title2 = Title::newFromText($row->att2, SMW_NS_PROPERTY);
					$article = Title::newFromText($row->subject, $row->namespace);
					// do not add doubles
					$result[] = new AnnotationSimilarity($title1, $title2, $article);
					
			}
			$dbr->freeResult( $res );
		}
		$res = $dbr->query('SELECT DISTINCT sa.relation_title AS att1, sa.subject_title AS subject, sa.subject_namespace AS namespace, sa2.relation_title AS att2 FROM smw_relations sa LEFT JOIN page p ON p.page_title = sa.relation_title INNER JOIN smw_relations sa2 ' .
				'WHERE sa.relation_title != sa2.relation_title '.$nameRestriction.' AND p.page_title IS NULL ' .
				'AND EDITDISTANCE(UPPER(sa.relation_title), UPPER(sa2.relation_title)) <= '.$similarityDegree.(($limitOfResults != NULL) ? ' LIMIT '.$limitOfResults : ""));
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
					$title1 = Title::newFromText($row->att1, SMW_NS_PROPERTY);
					$title2 = Title::newFromText($row->att2, SMW_NS_PROPERTY);
					$article = Title::newFromText($row->subject, $row->namespace);
					// do not add doubles
					 
					$result[] = new AnnotationSimilarity($title1, $title2, $article);
					
			}
			$dbr->freeResult( $res );
		}
		$res = $dbr->query('SELECT DISTINCT sa.attribute_title AS att1, sa.subject_title AS subject, sa.subject_namespace AS namespace , sa2.attribute_title AS att2 FROM smw_nary sa LEFT JOIN page p ON p.page_title = sa.attribute_title INNER JOIN smw_nary sa2 ' .
 				'WHERE sa.attribute_title != sa2.attribute_title '.$nameRestriction.' AND p.page_title IS NULL ' .
 				'AND EDITDISTANCE(UPPER(sa.attribute_title), UPPER(sa2.attribute_title)) <= '.$similarityDegree.(($limitOfResults != NULL) ? ' LIMIT '.$limitOfResults : ""));
		if($dbr->numRows( $res ) > 0) {
			while($row = $dbr->fetchObject($res)) {
					$title1 = Title::newFromText($row->att1, SMW_NS_PROPERTY);
					$title2 = Title::newFromText($row->att2, SMW_NS_PROPERTY);
					$article = Title::newFromText($row->subject, $row->namespace);
					// do not add doubles
				 
					$result[] = new AnnotationSimilarity($title1, $title2, $article);
				
			}
			$dbr->freeResult( $res );
		}
		return $result;
 	}
 	
 	/**
 	 * Helper function to determine if an array of Titles contain
 	 * a certain title object.
 	 */
 	private static function containsTitle($title, array & $uniqueTitles) {
 		foreach($uniqueTitles as $t) {
 			if ($t->equals($title)) {
 				return true;
 			}
 		}
 		return false;
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
 	
 	private static function getWikiLink($title) {
 		global $smwgContLang, $wgLang;
 		$nsArray = $smwgContLang->getNamespaceArray();
 		switch($title->getNamespace()) {
 				case NS_CATEGORY: {
 					$markup = "[[:".$wgLang->getNsText(NS_CATEGORY).":".$title->getText()."]]";
 					break;
 				}
 				case SMW_NS_PROPERTY: {
 					$markup = "[[".$nsArray[SMW_NS_PROPERTY].":".$title->getText()."]]";
 					break;
 				}
 				case SMW_NS_RELATION: {
 					$markup = "[[".$nsArray[SMW_NS_RELATION].":".$title->getText()."]]";
 					break;
 				}
 				case SMW_NS_QUERY: {
 					$markup = "[[".$nsArray[SMW_NS_QUERY].":".$title->getText()."]]";
 					break;
 				}
 				case NS_MAIN: {
 					$markup = "[[".$title->getText()."]]";
 					break;
 				}
 				case NS_TEMPLATE: {
 					$markup = "[[".$wgLang->getNsText(NS_TEMPLATE).":".$title->getText()."]]";
 					break;
 				}
 				default: {
 					$markup = "unknown type";
 				}
 			}
 			return $markup;
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
 	
 	private $domainHintRelation;
 	private $rangeHintRelation;
 	
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
 	
 	public function isDistinctByCommonPrefixOrSuffix() {
 		return $this->distinctByCommonPrefixOrSuffix;
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
 	
 	public function formatSharedEntities() {
 		global $wgLang;
 		$markup = "";
 		if (count($this->sharedCategories) > 0) {
 				$markup .= "** ".wfMsg('smw_gard_sharecategories').": \n";
 				foreach($this->sharedCategories as $cat) {
 					$markup .= "***[[:".$wgLang->getNsText(NS_CATEGORY).":".$cat->getText()."]]";
 				}
 				$markup .= "\n";
 		}
 		if (count($this->sharedDomainCategories) > 0) {
 				$markup .= "** ".wfMsg('smw_gard_sharedomains').": \n";
 				foreach($this->sharedDomainCategories as $cat) {
 					$markup .= "***[[:".$wgLang->getNsText(NS_CATEGORY).":".$cat->getText()."]]";
 				}
 				$markup .= "\n";
 		}
 		if (count($this->sharedRangeCategories) > 0) {
 				$markup .= "** ".wfMsg('smw_gard_shareranges').": \n";
 				foreach($this->sharedRangeCategories as $cat) {
 					$markup .= "***[[:".$wgLang->getNsText(NS_CATEGORY).":".$cat->getText()."]]";
 				}
 				$markup .= "\n";
 		}
 		if (count($this->sharedTypes) > 0) {
 			$markup .= "** ".wfMsg('smw_gard_sharetypes').": \n";
 				foreach($this->sharedTypes as $type) {
 					$markup .= "***".$type;
 				}
 				$markup .= "\n";
 		}
 		return $markup;
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
			
		$res = $db->query('SELECT c1.cl_to FROM categorylinks c1, categorylinks c2 ' .
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
 		global $smwgContLang, $smwgHaloContLang;
 		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		
		$domainHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_HINT], SMW_NS_PROPERTY);
		
		
 		$db =& wfGetDB( DB_MASTER );
			
		$res = $db->query('SELECT r1.object_title AS cat FROM smw_relations r1, smw_relations r2 ' .
				'WHERE r1.subject_id = '.$this->title1->getArticleID(). ' AND r2.subject_id = '.$this->title2->getArticleID().
				' AND r1.relation_title = '.$db->addQuotes($domainHintRelation->getDBkey()).' AND r2.relation_title = '.$db->addQuotes($domainHintRelation->getDBkey()).
				' AND r1.object_title = r2.object_title');
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
 		global $smwgContLang, $smwgHaloContLang;
 		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		
		$rangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_RANGE_HINT], SMW_NS_PROPERTY);
		
 		$db =& wfGetDB( DB_MASTER );
			
		$res = $db->query('SELECT r1.object_title AS cat FROM smw_relations r1, smw_relations r2 ' .
				'WHERE r1.subject_id = '.$this->title1->getArticleID(). ' AND r2.subject_id = '.$this->title2->getArticleID().
				' AND r1.relation_title = '.$db->addQuotes($rangeHintRelation->getDBkey()).' AND r2.relation_title = '.$db->addQuotes($rangeHintRelation->getDBkey()).
				' AND r1.object_title = r2.object_title');
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
		global $smwgContLang, $smwgHaloContLang;
		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
			
		$res = $db->query('SELECT s1.value_string AS type FROM smw_specialprops s1, smw_specialprops s2 ' .
				'WHERE s1.subject_id = '.$this->title1->getArticleID(). ' AND s2.subject_id = '.$this->title2->getArticleID().
				' AND s1.property_id = '.SMW_SP_HAS_TYPE.' AND s2.property_id = '.SMW_SP_HAS_TYPE.
				' AND s1.value_string = s2.value_string');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = $row->type;
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
 }
 // instantiate once (if editdistance function is supported).
 if (smwfDBSupportsFunction('editdistance')) {
 	new SimilarityBot();
 }
?>
