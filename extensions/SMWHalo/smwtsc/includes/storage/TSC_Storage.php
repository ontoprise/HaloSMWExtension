<?php
/**
 * @file
 * @ingroup LinkedData_Storage
 */

/*  Copyright 2010, ontoprise GmbH
 *   This file is part of the LinkedData-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file contains the class TSCStorage that provides access to the database
 * tables that are used by the Linked Data extension.
 *
 * @author Thomas Schweitzer
 *
 */


/**
 * This class encapsulates all methods that care about the database tables of
 * the Linked Data extension. It is a singleton that contains an instance
 * of the actual database access object e.g. the Mediawiki SQL database.
 *
 */
class TSCStorage {

	//--- Private fields---

	private static $mInstance; // TSCStorage: the only instance of this singleton
	private static $mDatabase; // The actual database object

	//--- Constructor ---

	/**
	 * Constructor.
	 * Creates the object that handles the concrete database access.
	 *
	 */
	private function __construct() {
		global $tscgIP;
		if (self::$mDatabase == NULL) {
			 
			require_once("$tscgIP/includes/storage/TSC_StorageSQL.php");
			self::$mDatabase = new TSCStorageSQL();

		}

	}

	//--- Public methods ---

	/**
	 * Returns the single instance of this class.
	 *
	 * @return TSCStorage
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