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
 define('SMW_GARDISSUES_DOMAINS_NOT_COVARIANT', 101);
 define('SMW_GARDISSUES_RANGES_NOT_COVARIANT', 102);
 define('SMW_GARDISSUES_TYPES_NOT_COVARIANT', 103);
 define('SMW_GARDISSUES_MINCARD_NOT_COVARIANT', 104);
 define('SMW_GARDISSUES_MAXCARD_NOT_COVARIANT', 105);
 define('SMW_GARDISSUES_SYMETRY_NOT_COVARIANT', 106);
 define('SMW_GARDISSUES_TRANSITIVITY_NOT_COVARIANT', 107);
 // ...
 // not defined issues
 define('SMW_GARDISSUES_DOMAINS_NOT_DEFINED', 201);
 define('SMW_GARDISSUES_DOMAINS_NOT_DEFINED', 202);
 define('SMW_GARDISSUES_TYPES_NOT_DEFINED', 203);
 // missing / doubles issues
 define('SMW_GARDISSUES_MORE_THAN_ONE_TYPE', 301);
 // wrong value / entity issues
 define('SMW_GARDISSUE_MAXCARD_NOT_NULL', 401);
 define('SMW_GARDISSUE_MINCARD_BELOW_NULL', 401);
 // incompatible entity issues
 
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
 	public abstract function clearGardeningIssues(Title $t1 = NULL, $gi_type = NULL);
 	
 	/**
 	 * Get Gardening issues for a given Title.
 	 * 
 	 * @param $t1 Title issue is about.
 	 * @param $gi_type type of issue.
 	 */
 	public abstract function getGardeningIssues(Title $t1 = NULL, $gi_type);
 	
 	/**
 	 * Add Gardening issue about articles.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $t2 Title 
 	 * @param $value optional value. Depends on $gi_type
 	 */
 	public abstract function addGardeningIssueAboutArticles($gi_type, Title $t1, Title $t2, $value = NULL);
 	
 	/**
 	 * Add Gardening issue about values.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $value Depends on $gi_type
 	 */
 	public abstract function addGardeningIssueAboutValue($gi_type, Title $t1, $value);
 }
?>
