<?php
/*
 * Created on 26.11.2007
 *
 * Author: kai
 */

abstract class SMWSuggestStatistics {

	private static $store;

	/**
	 * Returns pages edited by $username, which have gardening issues of the given type.
	 *
	 * @param $bot_id
	 * @param $gi_class
	 * @param $gi_type
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditedPages($botID, $gi_class, $gi_type, $username, $requestoptions);

	/**
	 * Returns pages which are member of the same category as articles edited by $username
	 * and which have gardening issues of the given type.
	 *
	 * @param $bot_id
	 * @param $gi_class
	 * @param $gi_type
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditedPagesOfSameCategory($botID, $gi_class, $gi_type, $username, $requestoptions) ;

	/**
	 * Returns undefined categories which are used on articles edited by $username
	 *
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditPagesOfUndefinedCategories($username, $requestoptions) ;

	/**
	 * Returns undefined properties which are used on articles edited by $username
	 *
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditPagesOfUndefinedProperties($username, $requestoptions) ;
    
	/**
     * Returns all annotations tuples ($property, $value, $rating) of $subject.
     * 
     * @param $subject (DBkey) 
     */
    public abstract function getRatedAnnotations($subject);
    
    /**
     * Returns an array of $limit annotations (non-nary) which are not yet rated.
     * 
     * @param $limit integer
     * @return array of titles (subject, predicate, objectOrValue)
     */
    public abstract function getAnnotationsForRating($limit);
    // Methods which modifies the database
    
    /**
     * Rate annotation specified by $subject, $predicate, $object
     * 
     * @param $subject (DBkey)
     * @param $predicate (DBkey)
     * @param $object (DBkey)
     * @param $rating Integer (positive or negative)
     * 
     */
    public abstract function rateAnnotation($subject, $predicate, $object, $rating);
    
	/**
	 * Setups tables
	 *
	 */
	public abstract function setup($verbose = true);

	public static function getStore() {
		global $smwgBaseStore, $sgagIP, $wgUser;
		if (self::$store == NULL) {
			switch ($smwgBaseStore) {
				case (SMW_STORE_TESTING):
					self::$store = null; // not implemented yet
					trigger_error('Testing stores not implemented for HALO extension.');
					break;
				case ('SMWHaloStore2'): default:
					require_once($sgagIP . '/includes/findwork/SGA_SuggestStatisticsSQL2.php');
					self::$store = new SMWSuggestStatisticsSQL2();
					break;
				case ('SMWHaloStore'): default:
					require_once($sgagIP . '/includes/findwork/SGA_SuggestStatisticsSQL.php');
					self::$store = new SMWSuggestStatisticsSQL();
					break;
			}
		}
		return self::$store;
	}
}
?>
