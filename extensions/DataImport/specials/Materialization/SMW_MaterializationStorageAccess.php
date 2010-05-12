<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the Data Import-Extension.
*
*   The Data Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
  * @ingroup DIWSMaterialization
  * This file provides the access to the database tables that are
 * used by the materialization parser function.
 * 
 * @author Ingo Steinbauer
 * 
 */

global $smwgHaloIP;
require_once $smwgHaloIP . '/includes/SMW_DBHelper.php';



/**
 * This class returns an IMaterializationStorage instance
 *
 */
class SMWMaterializationStorageAccess {

	//--- Private fields---
	private static $mInstance; // WSStorage: the only instance of this singleton
	private static $mDatabase; // The actual database object
	
	/**
	 * Constructor.
	 * Creates the object that handles the concrete database access.
	 *
	 */
	private function __construct() {
        global $smwgHaloIP;
        if (self::$mDatabase == NULL) {
            global $smwgBaseStore;
            global $smwgDIIP;
            require_once($smwgDIIP . '/specials/Materialization/storage/SMW_MaterializationStorageSQL.php');
            self::$mDatabase = new SMWMaterializationStorageSQL();
        }
		
	}
	
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