<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
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
 * This file defines the class LOD_RatingTripleInfo
 * 
 * @author Thomas Schweitzer
 * Date: 20.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * 
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODRatingTripleInfo  {
	
	//--- Constants ---
	// Constants for the position of a variable in a triple
	const SUBJECT	= 0;
	const PREDICATE	= 1;
	const OBJECT	= 2;
		
	//--- Private fields ---
	private $mVariable;    		//string: 
	private $mPosition;
	private $mTriple;
	private $mBound;
	
	/**
	 * Constructor for  LODRatingTripleInfo
	 *
	 * @param string $variable
	 * 		The name of the variable
	 * @param int $position
	 * 		Position of the variable in the triple
	 * @param bool $bound
	 * 		<true> if a value is bound to the variable
	 * @param LODTriple $triple
	 * 		The triple that contains the variable
	 */
	function __construct($variable, $position, $bound, LODTriple $triple) {
		$this->mVariable = $variable;
		$this->mPosition = $position;
		$this->mBound = $bound;
		$this->mTriple = $triple;
	}

	//--- getter/setter ---
	public function getVariable()	{ return $this->mVariable; }
	public function getPosition()	{ return $this->mPosition; }
	public function	isBound()		{ return $this->mBound; }
	public function getTriple()		{ return $this->mTriple; }
	
//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	

	//--- Private methods ---
}