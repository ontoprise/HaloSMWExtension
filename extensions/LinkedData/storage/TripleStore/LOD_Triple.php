<?php
/**
 * @file
 * @ingroup LinkedDataStorage
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
 * This file contains the class LODTriple which represents a triple consisting
 * of subject, predicate and object.
 * 
 * @author Thomas Schweitzer
 * Date: 30.04.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class defines a triple consisting of subject, predicate, object and the
 * type of the object.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  LODTriple  {
	
	//--- Constants ---
		
	//--- Private fields ---
	

	// string:
	// The name of the subject (with namespace prefix)
	private $mSubject;
	
	// string:
	// The name of the predicate (with namespace prefix)
	private $mPredicate;
	
	// string:
	// The name (with namespace prefix) or value of the object
	private $mObject;
	
	// string:
	// The type of the object e.g. xsd:string. The special value "__objectURI"
	// indicates that the object is the URI of another subject in the graph. 
	private $mType;
	
	/**
	 * Constructor for LODTriple
	 * Creates a new triple consisting of subject, predicate and object. 
	 * If one of the values $subject, $predicate or $object is invalid, an
	 * exception will be thrown.
	 *
	 * @param string $subject
	 * 		The name of the subject (with namespace prefix)
	 * @param string $predicate
	 * 		The name of the predicate (with namespace prefix)
	 * @param mixed $object
	 * 		The name (with namespace prefix) or value of the object
	 * @param string $type
	 * 		The type of the object. Xsd types should be prefixed with "xsd:".
	 * 		If the type is "xsd:string", special characters in the object (e.g.
	 * 		line breaks) are escaped. 
	 * 		The special value "__objectURI" indicates that the object is the URI
	 * 		of another subject in the graph. 
	 * 		If not type is given, the object is a simple literal
 	 */		
	public function __construct($subject, $predicate, $object, $type = null) {
		if (!isset($subject) || !isset($predicate) || !isset($object)) {
			// missing parameter values => throw exception
//TODO: Throw an exception
			return;	                           	
		}
		$this->mSubject      = $subject;
		$this->mPredicate    = $predicate;
		$this->mObject       = $object;
		$this->mType         = $type;
		
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	
	/**
	 * Creates a SPARUL command for this triple.
	 *
	 * @return string
	 * 		The SPARUL command for this triple. 
	 * 		
	 */
	public function toSPARUL() {
		if (!isset($this->mSubject) 
		    || !isset($this->mPredicate) 
		    || !isset($this->mObject)) {
		    return "";
		}
		
		$obj = ($this->type == '__objectURI') 
						? $this->mObject
						: self::makeLiteral($this->mObject, $this->mType);
		
		$SPARULCommand = "{$this->mSubject} {$this->mPredicate} $obj .";
		return $SPARULCommand;
	}

	//--- Private methods ---
	
	/**
	 * Creates a string literal for SPARUL.
	 * If $type is "xsd:string", the literal is escaped (double quotes, 
	 * backslash and line feeds).
	 *
	 * @param string $literal
	 * 		The base value of the literal that is transformed in a correct
	 * 		literal for SPARUL.
	 * @param string $type
	 * 		The type of the literal (e.g. xsd:string) or <null>.
	 * @return string
	 * 		The quoted literal with appended type, if a type is defined.
	 */
	private function makeLiteral($literal, $type) {
		if ($type == 'xsd:string') {
			// replace special characters in strings
			$literal = str_replace(array("\\",   "\"",   "\n",  "\r"), 
								   array("\\\\", "\\\"", "\\n", "\\r"), $literal);
		}
		$literal = '"'.$literal.'"';
		if ($type) {
			$literal .= "^^$type";
		}
		return $literal;
	}
		
}
