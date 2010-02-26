<?php 
/**
 * Dummy for GardeningIssue access component for running the OntologyBrowser 
 * without gardening extension. 
 * 
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloOntologyBrowser
 * 
 * @author Kai Kühn
 */

// define the consistency bot constants, because the ontology browser uses them.

define('SMW_CONSISTENCY_BOT_BASE', 100);
 // covariance issues
 define('SMW_GARDISSUE_DOMAINS_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 1);
 define('SMW_GARDISSUE_RANGES_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 2);
 define('SMW_GARDISSUE_TYPES_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 3);
 define('SMW_GARDISSUE_MINCARD_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 4);
 define('SMW_GARDISSUE_MAXCARD_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 5);
 define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1', SMW_CONSISTENCY_BOT_BASE * 100 + 6);
 define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1', SMW_CONSISTENCY_BOT_BASE * 100 + 7);
 define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2', SMW_CONSISTENCY_BOT_BASE * 100 + 8);
 define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2', SMW_CONSISTENCY_BOT_BASE * 100 + 9);
 // ...
 // not defined issues
 define('SMW_GARDISSUE_DOMAINS_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 1);
 define('SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 2);
 define('SMW_GARDISSUE_RANGES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 4);
 define('SMW_GARDISSUE_TYPES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 5);
 
 
 // doubles issues
 define('SMW_GARDISSUE_DOUBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 1);
 define('SMW_GARDISSUE_DOUBLE_MAX_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 2);
 define('SMW_GARDISSUE_DOUBLE_MIN_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 3);

 
 // wrong/missing values / entity issues
 define('SMW_GARDISSUE_MAXCARD_NOT_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 1);
 define('SMW_GARDISSUE_MINCARD_BELOW_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 2);
 define('SMW_GARDISSUE_WRONG_MINCARD_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 3);
 define('SMW_GARDISSUE_WRONG_MAXCARD_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 4);
 define('SMW_GARDISSUE_WRONG_TARGET_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 5);
 define('SMW_GARDISSUE_WRONG_DOMAIN_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 6);
 define('SMW_GARDISSUE_TOO_LOW_CARD', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 7);
 define('SMW_GARDISSUE_TOO_HIGH_CARD', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 8);
 define('SMW_GARDISSUE_WRONG_UNIT', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 9);
 define('SMW_GARD_ISSUE_MISSING_PARAM', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 10);
 define('SMW_GARDISSUE_MISSING_ANNOTATIONS', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 11);
 
 // incompatible entity issues
 define('SMW_GARD_ISSUE_DOMAIN_NOT_RANGE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 1);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 2);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 3);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_SUPERTYPES', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 4 );
 
 // others
define('SMW_GARD_ISSUE_CYCLE', (SMW_CONSISTENCY_BOT_BASE+5) * 100 + 1);

class SGAGardeningIssuesAccess {
    
    static $gi_interface;
   
    /**
     * Get Gardening issues. Every parameter (except $bot_id) may be NULL!
     * 
     * @param $bot_id Bot-ID
     * @param $gi_type type of issue. (Can be an array!)
     * @param $gi_class type of class of issue. (Can be an array!)
     * @param $titles Title1 issue is about. (Can be an array)
     * @param $sortfor column to sort for. Default by title.
     *              One of the constants: SMW_GARDENINGLOG_SORTFORTITLE, SMW_GARDENINGLOG_SORTFORVALUE 
     * @param $options instance of SMWRequestOptions
     * 
     * @return array of GardeningIssue objects
     */
    public function getGardeningIssues($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $titles = NULL,  $sortfor = NULL, $options = NULL) {
    	return array();
    }
    
    
    /**
     * Get Gardening issues for a pair of titles. Every parameter (except $bot_id) may be NULL!
     * 
     * @param $bot_id Bot-ID
     * @param $gi_type type of issue. (Can be an array!)
     * @param $gi_class type of class of issue.  (Can be an array!)
     * @param $titles Pair (2-tuple) of Title objects the issue is about. (Must be an array of tuples)
     * @param $sortfor column to sort for. Default by title.
     *              One of the constants: SMW_GARDENINGLOG_SORTFORTITLE, SMW_GARDENINGLOG_SORTFORVALUE 
     * @param $options instance of SMWRequestOptions
     * 
     * @return array of GardeningIssue objects
     */
    public function getGardeningIssuesForPairs($bot_id = NULL, $gi_type = NULL, $gi_class = NULL, $titles = NULL,  $sortfor = NULL, $options = NULL) {
    	return array();
    }
    
    

    public static function getGardeningIssuesAccess() {
    	if (self::$gi_interface == NULL) {
    		self::$gi_interface = new SGAGardeningIssuesAccess();
    	}
        return self::$gi_interface;
    }
 }
