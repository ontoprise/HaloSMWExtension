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
 * This file contains the class TSCTriple which represents a triple consisting
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
class TSCTriple  {
	
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
	 * Constructor for TSCTriple
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
	 * 		The special value "__blankNode" indicates that the object is a blank 
	 * 		node. 
	 * 		If no type is given, the object is a simple literal
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
	public function getSubject()	{return $this->mSubject;}
	public function getPredicate()	{return $this->mPredicate;}
	public function getObject()		{return $this->mObject;}
	public function getType()		{return $this->mType;}
	
//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---

	/**
	 * Returns <true> if the object of this triple is a literal and <false> if it 
	 * is an URI.
	 * 
	 * @return boolean
	 * 		true - Object is a literal
	 * 		false - Object is an URI
	 */
	public function isObjectLiteral() {
		return $this->mType !== "__objectURI" && $this->mType !== "__blankNode";
	}
	
	/**
	 * Returns all prefixes of subject, predicate, object and type in this order.
	 * 
	 * @return array(string)
	 * 		All prefixes used in this triple. If an element has no prefix, the 
	 * 		empty string is returned for that element.
	 */
	public function getPrefixes() {
		return array(
			$this->getPrefix($this->mSubject),
			$this->getPrefix($this->mPredicate),
			$this->isObjectLiteral() ? "" : $this->getPrefix($this->mObject),
			$this->getPrefix($this->mType),
			
		);
	}
	
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
		
		$subj = $this->mSubject;
        if (strpos($subj, "http://") === 0 || strpos($subj, "obl:") === 0) {
            $subj = "<$subj>";
        }
        
        $pred = $this->mPredicate;
        if (strpos($pred, "http://") === 0) {
            $pred = "<$pred>";
        }
        
        switch ($this->mType) {
        case '__objectURI':
            $obj = (preg_match("~^http://~i", $this->mObject) ||
                    strpos($this->mObject,'obl:') === 0)
                        ? '<' . $this->mObject . '>' 
                        : $this->mObject;
            break;
		case '__blankNode':
			$obj = $this->mObject;
			break;
		default:
			$obj = self::makeLiteral($this->mObject, $this->mType);
		}
		
		$SPARULCommand = "$subj $pred $obj .";
		return $SPARULCommand;
	}
	
	/**
	 * Returns an associative array representation of this triple.
	 */
	public function toArray() {
		return array(
			"subject"   => $this->mSubject,
			"predicate" => $this->mPredicate,
			"object"    => $this->mObject,
			"type"      => $this->mType,
		);
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
	
	/**
	 * Returns the prefix of a URI, if it contains a colon and is not an absolute
	 * URI.
	 * 
	 * @param string $uri
	 * 		A (possible) URI. If it contains a colon, the part before the colon
	 * 		is returned otherwise an empty string.
	 */
	private function getPrefix($uri) {
		if (strpos($uri, "http://") === 0) {
			// An absolute URI has no prefix.
			return "";
		}
		
		$pos = strpos($uri, ":");
		return $pos === false ? "" : substr($uri, 0, $pos);
	}
}
