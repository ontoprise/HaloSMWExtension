<?php
/**
 * This file provides the access to the database tables that are
 * used by the Semantic Connector extension.
 *
 * @author Ning Hu
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgConnectorIP;
require_once $smwgConnectorIP . '/includes/SC_DBHelper.php';



/**
 * This class encapsulates all methods that care about the database tables of
 * the web service extension. It is a singleton that contains an instance
 * of the actual database access object e.g. the Mediawiki SQL database.
 *
 */
class SCStorage {

	//--- Private fields---

	private static $mInstance; // WSStorage: the only instance of this singleton
	private static $mDatabase; // The actual database object

	//--- Constructor ---

	/**
	 * Constructor.
	 * Creates the object that handles the concrete database access.
	 *
	 */
	private function __construct() {
		if (self::$mDatabase == NULL) {
			global $smwgConnectorIP;
			require_once($smwgConnectorIP . '/includes/storage/SC_StorageSQL.php');
			self::$mDatabase = new ConnectorStorageSQL();
		}
	}

}

//--- Public methods ---

/**
 * Returns the single instance of this class.
 *
 * @return WSStorage
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