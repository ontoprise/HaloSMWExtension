<?php

/**
 * This file provides the access to the database tables that are
 * used by the NotifyMe extension.
 * 
 * @author dch
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $srfpgIP;
require_once $srfpgIP . '/includes/SRF_DBHelper.php';



/**
 * This class encapsulates all methods that care about the database tables of 
 * the NotifyMe extension. It is a singleton that contains an instance 
 * of the actual database access object e.g. the Mediawiki SQL database.
 *
 */
class SRFStorage {

	//--- Private fields---
	
	private static $mInstance; // SRFStorage: the only instance of this singleton
	private static $mDatabase; // The actual database object
	
	//--- Constructor ---
	
	/**
	 * Constructor.
	 * Creates the object that handles the concrete database access.
	 *
	 */
	private function __construct() {
        if (self::$mDatabase == NULL) {
                    global $srfpgIP;
                	require_once($srfpgIP . '/includes/SRF_StorageSQL.php');
                    self::$mDatabase = new SRFStorageSQL();
        }
		
	}
	
	//--- Public methods ---
	
	/**
	 * Returns the single instance of this class.
	 *
	 * @return SRFStorage
	 * 		The single instance of this class.
	 */
	public static function getInstance() {
        if (!isset(self::$mInstance)) {
            $c = __CLASS__;
            self::$mInstance = new $c;
        }

        return self::$mInstance;
	}
	
	/**
	 * Returns the actual database. 
	 *
	 * @return object
	 * 		The object to access the database.
	 */
	public static function getDatabase() {
        self::getInstance(); // Make sure, singleton is initialized
        return self::$mDatabase;
	}
	 
}

?>
