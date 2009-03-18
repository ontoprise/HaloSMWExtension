<?php 
/**
 * Dummy for GardeningIssue access component for running the OntologyBrowser 
 * without gardening extension. 
 *
 */
class SMWGardeningIssuesAccess {
    
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
    		self::$gi_interface = new SMWGardeningIssuesAccess();
    	}
        return self::$gi_interface;
    }
 }
?>