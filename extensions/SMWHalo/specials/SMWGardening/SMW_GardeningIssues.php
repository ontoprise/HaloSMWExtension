<?php
/*
 * Created on 18.10.2007
 *
 * Author: kai
 * 
 * Provide access to Gardening issue table. Gardening issues can be categorized:
 * 
 * 	- covariance
 * 	- not defined
 * 	- missing / doubles
 * 	- wrong value / entity
 * 	- incompatible entity
 * 
 */
 
 // covariance issues
 define('SMW_GARDISSUE_DOMAINS_NOT_COVARIANT', 101);
 define('SMW_GARDISSUE_RANGES_NOT_COVARIANT', 102);
 define('SMW_GARDISSUE_TYPES_NOT_COVARIANT', 103);
 define('SMW_GARDISSUE_MINCARD_NOT_COVARIANT', 104);
 define('SMW_GARDISSUE_MAXCARD_NOT_COVARIANT', 105);
 define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT', 106);
 define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT', 107);
 // ...
 // not defined issues
 define('SMW_GARDISSUE_DOMAINS_NOT_DEFINED', 201);
 define('SMW_GARDISSUE_DOMAINS_NOT_DEFINED', 202);
 define('SMW_GARDISSUE_TYPES_NOT_DEFINED', 203);
 
 define('SMW_GARDISSUE_CATEGORY_NOT_DEFINED', 204);
 define('SMW_GARDISSUE_PROPERTY_NOT_DEFINED', 205);
 define('SMW_GARDISSUE_TARGET_NOT_DEFINED', 206);
 
 // missing / doubles issues
 define('SMW_GARDISSUE_DOUBLE_TYPE', 301);
 define('SMW_GARDISSUE_DOUBLE_MAX_CARD', 302);
 define('SMW_GARDISSUE_DOUBLE_MIN_CARD', 303);
 define('SMW_GARD_ISSUE_MISSING_PARAM', 304);
 define('SMW_GARDISSUE_INSTANCE_WITHOUT_CAT', 305);
 
 // wrong value / entity issues
 define('SMW_GARDISSUE_MAXCARD_NOT_NULL', 401);
 define('SMW_GARDISSUE_MINCARD_BELOW_NULL', 402);
 define('SMW_GARDISSUE_WRONG_CARD_VALUE', 403);
 define('SMW_GARDISSUE_WRONG_TARGET_VALUE', 404);
 define('SMW_GARDISSUE_WRONG_DOMAIN_VALUE', 405);
 define('SMW_GARDISSUE_WRONG_CARD', 406);
 
 // incompatible entity issues
 define('SMW_GARD_ISSUE_DOMAIN_NOT_RANGE', 501);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY', 502);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_TYPE', 503);
 
 // others
define('SMW_GARD_ISSUE_PART_OF_CYCLE', 601);
 
 abstract class SMWGardeningIssues {
 	
 	
 	/**
 	 * Setups GardeningIssues table(s).
 	 */
 	public abstract function setup($verbose);
 	
 	/**
 	 * Clear all Gardening issues
 	 * 
 	 * @param if not NULL, clear only this type of issue. Otherwise all.
 	 */
 	public abstract function clearGardeningIssues($bot_id = NULL, Title $t1 = NULL);
 	
 	/**
 	 * Get Gardening issues for a given Title.
 	 * 
 	 * @param $t1 Title issue is about.
 	 * @param $gi_type type of issue.
 	 */
 	public abstract function getGardeningIssues($bot_id, Title $t1 = NULL);
 	
 	/**
 	 * Add Gardening issue about articles.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $t2 Title 
 	 * @param $value optional value. Depends on $gi_type
 	 */
 	public abstract function addGardeningIssueAboutArticles($bot_id, $gi_type, Title $t1, Title $t2, $value = NULL);
 	
 	/**
 	 * Add Gardening issue about values.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $value Depends on $gi_type
 	 */
 	public abstract function addGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value);
 }

/**
 * Simple record class to store a Gardening issue.
 * 
 * @author kai
 */
class GardeningIssue {
	
	private $gi_type;
	private $t1;
	private $t2;
	private $value;
	
	public function __construct($gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
		$this->gi_type = $gi_type;
		if ($t1_ns != NULL && $t1 != NULL && $t1 != '') {
			$this->t1 = Title::newFromText($t1_ns, $t1);
		}
		if ($t2_ns != NULL && $t2 != NULL && $t2 != '') {
			$this->t1 = Title::newFromText($t2_ns, $t2);
		}
		$this->value = $value;
	}
	public static function createIssueAboutArticles($gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
		$this->gi_type = $gi_type;
		if ($t1_ns != NULL && $t1 != NULL && $t1 != '') {
			$this->t1 = Title::newFromText($t1_ns, $t1);
		}
		if ($t2_ns != NULL && $t2 != NULL && $t2 != '') {
			$this->t1 = Title::newFromText($t2_ns, $t2);
		}
		$this->value = $value;
	}
	
	public static function createIssueAboutValue($gi_type, $t1_ns, $t1, $value) {
		$this->gi_type = $gi_type;
		if ($t1_ns != NULL && $t1 != NULL && $t1 != '') {
			$this->t1 = Title::newFromText($t1_ns, $t1);
		}
		
		$this->value = $value;
	}
}
?>
