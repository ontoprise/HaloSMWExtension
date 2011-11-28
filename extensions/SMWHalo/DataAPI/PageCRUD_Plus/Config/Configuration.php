<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

require_once("UserCredentials.php");
require_once("WikiSystem.php");

/**
 * Creates a configuration for the MW system being accessed. 
 * Should be used with the client version of PCP.
 * @deprecated
 */
class PCPConfiguration{
	/**
	 * The user credentials used.
	 *
	 * @var PCPUserCredentials
	 */
	public $uc = NULL;
	/**
	 * The wiki system used.
	 *
	 * @var PCPWikiSystem
	 */
	public $ws = NULL;	
	
	/**
	 * Class constructor.
	 *
	 * @param PCPUsercredentials $userCredentials
	 * @param PCPWikiSystem $wikiSystem
	 */
	public function PCPConfiguration($userCredentials=NULL, $wikiSystem=NULL){
		$this->uc = $userCredentials;
		if($wikiSystem==NULL){
			die ("The wiki system must be configured.");			
		}else{
			$this->ws = $wikiSystem;
		}
	}
	/**
	 * TODO: Create a function that read a configuration file
	 */
}
