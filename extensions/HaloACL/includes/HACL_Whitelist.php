<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class HACLWhitelist for managing the whitelist in the
 * database.
 * 
 * @author Thomas Schweitzer
 * Date: 11.05.2009
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $haclgIP;
//require_once("$haclgIP/...");

/**
 * The class HACLWhitelist manages the whitelist in the database. The whitelist
 * is a set of pages that can be read by everyone.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  HACLWhitelist  {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	private $mPages = array();    		//array(string): The names of all pages
										//  (with namespace) that define the
										//  whitelist
	
	/**
	 * Constructor for HACLWhitelist. The new object has to be saved to store the
	 * whitelist in the database.
	 *
	 * @param array(string) $pages
	 * 		An array of pagenames (with namespace) that define the whitelist.
	 * 		For an empty whitelist, the array may be empty or the parameter 
	 * 		may be completely missing.
	 */		
	function __construct($pages = array()) {
		$this->mPages = $pages;
	}
	

	//--- getter/setter ---
	public function getPages()           {return $this->mPages;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	
	/**
	 * Creates a HACLWhitelist-object based on the content of the database.
	 *
	 * @return HACLWhitelist
	 * 		A whitelist object that contains all whitelist pages that are stored
	 * 		in the database.
	 */
	public static function newFromDB() {
		// Read the IDS of all pages that are part of the whitelist
		$pageIDs = HACLStorage::getDatabase()->getWhitelist();
		$pages = array();
		// Transform page-IDs to page names
		foreach ($pageIDs as $pid) {
			$t = Title::newFromID($pid);
			if ($t) {
				$pages[] = $t->getFullText();
			}
		}
		
		return new HACLWhitelist($pages);
	}
	
	/**
	 * Saves the pages of this object as whitelist in the database. It is not
	 * possible to add names of pages that do no exist. In this case an 
	 * exception is thrown. However, all existing articles are stored in the
	 * database.
	 *
	 * @throws HACLWhitelistException
	 * 		HACLWhitelistException(HACLWhitelistException::PAGE_DOES_NOT_EXIST)
	 * 		... if an article given in the whitelist does not exist.
	 */
	public function save() {
		$nonExistent = array();
		$ids = array();
		// Get the IDs of all pages
		foreach ($this->mPages as $name) {
			$t = Title::newFromText($name);
			$id = $t->getArticleID();
			if ($id == 0) {
				$nonExistent[] = $name;
			} else {
				$ids[] = $id;
			}
		}
		HACLStorage::getDatabase()->saveWhitelist($ids);
		if (!empty($nonExistent)) {
			throw new HACLWhitelistException(HACLWhitelistException::PAGE_DOES_NOT_EXIST,
											 $nonExistent);		
		}
	}

	/**
	 * Checks if the article with the ID or name $page is a member of the 
	 * whitelist.
	 *
	 * @param mixed int|string $page
	 * 		ID or name of the page
	 * 
	 * @return bool
	 * 		true, if the article is part of the whitelist
	 * 		false, otherwise
	 */
	public static function isInWhitelist($page) {
		if (!is_int($page)) {
			$t = Title::newFromText($page);
			$page = $t->getArticleID();
		}
		return HACLStorage::getDatabase()->isInWhitelist($page);
	}
	//--- Private methods ---
}