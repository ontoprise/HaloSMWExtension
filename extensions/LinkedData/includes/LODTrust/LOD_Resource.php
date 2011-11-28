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
 * @ingroup LinkedDataAdministration
 */
/**
 * This file contains the class LODResource.
 * 
 * @author Magnus Niemann
 * Date: 06.01.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;

/**
 * This class describes a common URI-based resource.
 * 
 * @author Magnus Niemann
 * 
 */
class LODResource  {
	
	//--- Constants ---

	//--- Private fields ---
	
	// string: 	
	// The URI.
	private $mURI;
	
	/**
	 * Constructor for LODResource.
	 *
	 * @param string $URI
	 * 		URI of the resource.
	 */		
	function __construct($URI) {
		$this->mURI = $URI;
	}
	

	//--- getter/setter ---
	public function getURI()						{ return $this->mURI; }

    public function setURI($val)						{ $this->mURI = $val; }

    //--- Public methods ---
	
	//--- Private methods ---
}
