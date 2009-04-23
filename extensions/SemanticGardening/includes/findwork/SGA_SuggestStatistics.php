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
				
			}
		}
		return self::$store;
	}
}
?>
