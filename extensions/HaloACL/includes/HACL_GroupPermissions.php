<?php
/**
 * @file
 * @ingroup HaloACL
 */

/*  Copyright 2010, ontoprise GmbH
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
 * This file contains the class HACLGroupPermissions.
 * 
 * @author Thomas Schweitzer
 * Date: 18.08.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $haclgIP;
//require_once("$haclgIP/...");

/**
 * The class HACLGroupPermissions takes care that HaloACL groups and their 
 * members can be used together with $wgGroupPermissions i.e. that Mediawiki
 * permissions can be defined for HaloACL groups.
 * 
 * @author Thomas Schweitzer
 * 
 */
class HACLGroupPermissions  {
	
	//--- Constants ---
//	const XY= 0;		
		
	//--- Private fields ---
	private $mXY;    		//string: comment
	
	/**
	 * Constructor for  HACLGroupPermissions
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
//		$this->mXY = $xy;
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	/**
	 * This function is a callback for the hook 'UserEffectiveGroups' which is
	 * used in User::getEffectiveGroups(). The list of groups stored in the
	 * Mediawiki database is enhanced by the groups managed by HaloACL.
	 * 
	 * @param User $user
	 * 		The user whose groups are retrieved.
	 * @param array<string> $userGroups
	 * 		This list of groups will be modified by this method.
	 * 
	 * @return
	 * 		Returns true.
	 */
	public static function onUserEffectiveGroups(&$user, &$userGroups) {
		$groups = HACLGroup::getGroupsOfMember($user->getId(), HACLGroup::USER, true);
		foreach ($groups as $g) {
			$userGroups[] = $g['name'];
		}
		return true;
	}
	
	/**
	 * Description
	 *
	 * @param type $x
	 * 		...
	 * 
	 * @return type
	 * 		...
	 */
//	public  function ...($x) {
//		return ...
//	}

	//--- Private methods ---
}