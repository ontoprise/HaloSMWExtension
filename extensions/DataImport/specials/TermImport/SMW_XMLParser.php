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
 * @ingroup DITermImport
 * 
 * @author Thomas Schweitzer
 */

/**
 * A class to process XML structures comfortably.
 * 
 * @author Thomas Schweitzer
 */

class XMLParser {
	//--- Fields ---
	
	// String representation of the XML structure
	private $xmlString;
	
	// An array with the parsed structure of the XML string
	private $xmlStructure;

	// An array with indices into the parsed structure of the XML string
	private $xmlIndex;
	
	//--- Constructor methods ---
	
	/**
	 * Creates an instance of the XML parser. The string is not parsed in the
	 * constructor.
	 * 
	 * @param string $xmlString
	 * 		A string that contains XML code.
	 */
	public function __construct(&$xmlString) {
		$this->xmlString = $xmlString;
	}
	
	//--- Public methods ---
	
	/**
	 * Parses the internal XML string and stores the structure internally. 
	 * 
	 * @return 
	 * 		TRUE, if the string was parsed correctly or an
	 * 		error message otherwise.
	 *
	 */
	public function parse() {
		$parser = xml_parser_create();
		$this->xmlStructure = array();
		$this->xmlIndex = array();
		$msg = null;
		if (!xml_parse_into_struct($parser, $this->xmlString, 
		                           $this->xmlStructure, $this->xmlIndex)) {
			$msg = wfMsg('smw_ti_xml_error', 
						 xml_error_string(xml_get_error_code($parser)),
						 xml_get_current_line_number($parser));
			$msg = htmlspecialchars($msg);
		}
		xml_parser_free($parser);
		
		// augment the xmlStructure with references to the parent elements
		$this->addParentRefs();
		
		return isset($msg) ? $msg : TRUE;
		
	}
	
	/**
	 * Checks if the root node of the XML tree has the name $rootname. This 
	 * function can be called after <parse()> has been called.
	 *
	 * @param string $rootname
	 * 		The name of the root node to be checked
	 * 
	 * @return bool
	 * 		<true>, if the root node has the expected name
	 * 		<false>, if not or if the structure is not parsed yet.
	 * 
	 */
	public function rootIs($rootname) {
		if (!isset($this->xmlStructure)) {
			return false;
		}
		return $this->xmlStructure[0]['tag'] == strtoupper($rootname);
	}
	
	/**
	 * Serializes the current $xmlStructure into an XML string.
	 * 
	 * @param int $startIdx
	 * 		This optional index is the start of an XML element. If this value
	 * 		is given, only this element is serialized.
	 * @param bool $addXmlHeader 
	 * 	 	If <true>, the XML header is added at the beginning.
	 *  
	 * @return string
	 * 		An XML string
	 *
	 */
	public function serialize($startIdx = 0, $addXmlHeader = true) {
		$xml = $addXmlHeader ? '<?xml version="1.0"?>'."\n" : '';
		$len = count($this->xmlStructure);
		
		$elem = $this->xmlStructure[$startIdx];
		if (!$elem || ($elem['type'] != 'open' && $elem['type'] != 'complete')) {
			// invalid element index
			return "";
		}
		
		$endTag = $elem['tag'];
		$endLevel = $elem['level'];
		
		for ($i = $startIdx; $i < $len; ++$i) {
			$elem = $this->xmlStructure[$i];
			if (!$elem) {
				continue;
			}
			switch ($elem['type']) {
				case 'open':
					$xml .= '<' . $elem['tag'];
					$attr = &$elem['attributes'];
					if ($attr) {
						foreach($attr as $key => $val) {
							$xml .= ' ' . $key . '="'.$val.'"';
						}
					}
					$xml .= '>'. $elem['value'];
					break;
				case 'cdata':
					$xml .= $elem['value'];
					break;
				case 'complete':
					$xml .= '<' . $elem['tag'];
					$attr = &$elem['attributes'];
					if ($attr) {
						foreach($attr as $key => $val) {
							$xml .= ' ' . $key . '="'.$val.'"' ;
						}
					}
					$xml .= '>'. $elem['value'] . '</'.$elem['tag'].'>';
					break;
				case 'close':
					$xml .= '</'.$elem['tag'].'>';
					break;
			}
			if ($endTag == $elem['tag'] && $endLevel == $elem['level']
			    && ($elem['type'] == 'close' || $elem['type'] == 'complete')) {
				// closing element found
			    break;
			}
		}
		return $xml;
	}
	
	/**
	 * Returns the XML string of the XML element with the path <$elementPath>.
	 *
	 * @param array<string> $elementPath
	 * 		The values in this array build a path that starts somewhere in the
	 * 		hierarchy and ends at a leaf.
	 * @param int $elemIndex
	 * 		Several elements may match the given path. This parameter specifies
	 * 		which of the matching elements is serialized.
	 * @param bool $addXmlHeader 
	 * 	 	If <true>, the XML header is added at the beginning.
	 * @return string
	 * 		The serialized XML structure of the element. Can be empty.
	 */
	public function serializeElement($elementPath, $elemIndex = 0, $addXmlHeader = true) {
		$result = '';
		$pi = count($elementPath)-1;
		if ($pi < 0) {
			return $values;
		}
		
		$indices = $this->xmlIndex[strtoupper($elementPath[$pi])];
		if (!$indices) {
			return $result;
		}
		
		$ei = -1;
		$indicesIndex = 0;
		while (true) {
			$elem = &$this->xmlStructure[$indices[$indicesIndex]];
			// candidate found => check if the path is correct
			$i = $pi-1;
			$found = true;
			$parent = &$elem;
			while ($i >= 0) {
				$parent = &$this->xmlStructure[$parent['parent']];
				if ($parent['tag'] != strtoupper($elementPath[$i])) {
					$found = false;
					break;
				}
				--$i;
			}
			if ($found) {
				++$ei;
				if ($ei == $elemIndex) {
					break;
				}
			}
			++$indicesIndex;
			if ($indicesIndex >= count($indices)) {
				$found = false;
				break;
			}
		}
		if ($found) {
			// ok => serialize the element 
			$result = $this->serialize($indices[$indicesIndex], $addXmlHeader);
		}
		return $result;
	}
	
	
	/**
	 * Tries to find an XML element with the name $element and the content $content.
	 * All elements in the parent element that are on the same level as $element
	 * are returned in an array
	 * - Untagged content of the parent element is not returned
	 * - All tags within the parent must be leafs. 
	 *
	 * @param string $element
	 * 		Name of the XML element
	 * @param string $content
	 * 		Content of the XML element
	 * @return array
	 * 		An array of all elements within the parent of $element or
	 * 		<null>, if the element was not found.
	 */
	public function findElementWithContent($element, $content) {
		// find the xml element with the specified content
		$idIndices = $this->xmlIndex[$element];
		foreach ($idIndices as $idx) {
			$val = $this->xmlStructure[$idx]['value'];
			if ($val == $content) {
				// store all XML elements on the same level.
				$element = array();
				$level = $this->xmlStructure[$idx]['level'];
				$i = $this->xmlStructure[$idx]['parent'];
	
				// search forwards till the closing of the parent element
				for (++$i; ; ++$i) {
					if ($this->xmlStructure[$i]['level'] == $level) {
						$element[$this->xmlStructure[$i]['tag']] = $this->xmlStructure[$i];
					} else {
						if ($this->xmlStructure[$i]['type'] == 'close') {
							break;
						}
					}
				}
				return $element;
			}
		}
		return null;
	}
	
	/**
	 * Returns an array of strings with the values of the XML elements with the
	 * path <$elementPath>. The element must not have child elements.
	 *
	 * @param array<string> $elementPath
	 * 		The values in this array build a path that starts somewhere in the
	 * 		hierarchy and ends at a leaf.
	 * @return array<string>
	 * 		Array of element values. The array can be empty.
	 */
	public function getValuesOfElement($elementPath) {
		$values = array();
		$pi = count($elementPath)-1;
		if ($pi < 0) {
			return $values;
		}
		if (array_key_exists(strtoupper($elementPath[$pi]), $this->xmlIndex)){
			$indices = $this->xmlIndex[strtoupper($elementPath[$pi])];
		}
		else {
			return $values;
		}
		foreach ($indices as $idx) {
			$elem = &$this->xmlStructure[$idx];
			if ($elem['type'] == 'complete') {
				// candidate found => check if the path is correct
				$i = $pi-1;
				$found = true;
				$parent = &$elem;
				while ($i >= 0) {
					$parent = &$this->xmlStructure[$parent['parent']];
					if ($parent['tag'] != strtoupper($elementPath[$i])) {
						$found = false;
						break;
					}
					--$i;
				}
				
				if ($found) {
					// ok => add the value
					if ( array_key_exists('value',$elem) ) {
						$values[] = $elem['value'];
					}
					else {
						$values[]='';
					}
				}
			}
		}
		return $values;
	}
	
	/**
	 * This method tries to find the XML element that is specified by the 
	 * $elementPath. For the element an array that contains the properties is
	 * generated. 
	 *
	 * @param array<string> $elementPath 
	 * 		Each element in the array is a part of the path to the XML element e.g.
	 * 		array('a','b'). In this example, the XML element 'b' is returned, if 
	 * 		it is a child of the element 'a'. 
	 * @param int $nextElem
	 * 		This is an optional input/output parameter. It defines the starting 
	 * 		point of the search in the XML tree and is set to the next search 
	 * 		position after an element has been found. By simply passing a
	 * 		variable with value 0, all elements with a matching path in the XML
	 *      tree can be traversed.
	 * @return array
	 * 		The XML element is represented as array. Child elements are placed in
	 * 		sub-arrays. CDATA sections in the XML tree are ignored. The names of
	 * 		child elements are the keys in the array. Child elements can occur
	 * 		several times.
	 */
	public function getElement($elementPath, &$nextElem = 0) {
		$result = null;
		$pathIdx = count($elementPath)-1;
		if ($pathIdx < 0) {
			return null;
		}
		
		$indices = &$this->xmlIndex[strtoupper($elementPath[$pathIdx])];
		if (!$indices) {
			return null;
		}
		
		// Find the next opening or complete element that matches the path
		$len = count($indices);
		for ($i = $nextElem; $i < $len; ++$i) {
			$idx = $indices[$i];
			$elem = &$this->xmlStructure[$idx];
			if ($elem['type'] == 'complete' || $elem['type'] == 'open') {
				// candidate found => check if the path is correct
				$pi = $pathIdx-1;
				$found = true;
				$parent = &$elem;
				while ($pi >= 0) {
					$parent = &$this->xmlStructure[$parent['parent']];
					if ($parent['tag'] != strtoupper($elementPath[$pi])) {
						$found = false;
						break;
					}
					--$pi;
				}
				
				if ($found) {
					// ok => return the value
					$nextElem = $i+1;
					list($result, $lastIdx) = $this->createElement($idx);
					
					return $result;
				}
					
			}
			
		}
		return $result;		
	}
	
	/**
	 * Tries to find an XML element with the name $element and the content $content.
	 * The complete parent elements of elements that do not match the content
	 * are removed from the structure.
	 *
	 * @param string $element
	 * 		Name of the XML element
	 * @param string $content
	 * 		Content of the XML element
	 */
	public function removeAllParentElements($element, $content) {
		// find the xml element with the specified content
		$idIndices = $this->xmlIndex[$element];
		foreach ($idIndices as $idx) {
			$val = $this->xmlStructure[$idx]['value'];
			if ($val != $content) {
				// remove the complete parent element
				$i = $this->xmlStructure[$idx]['parent'];
	
				// search forwards till the closing of the parent element
				$finish = false;
				for (;!$finish ; ++$i) {
					if ($this->xmlStructure[$i]['type'] == 'close') {
						$finish = true;
					}
					$this->xmlStructure[$i] = null;
				}
			}
		}
		$this->xmlString = $this->serialize();
		$this->parse();
		
	}
	
	//--- Private methods ---
	
	/**
	 * The XML structure generated by <xml_parse_into_struct> contains no references
	 * to parent nodes. However, this is very valueable if paths in the XML 
	 * structure are needed. This function adds a key <parent> to the elements
	 * in <$xmlStructure> that contains the index of opening parent tag. 
	 * This method is called recursively as it moves through the XML structure.
	 * 
	 * @param int $startIdx
	 * 		Current index in $xmlStructure
	 * @param int $parentIdx
	 * 		Current index of the parent
	 * @return int
	 * 		Traversing the array continues at this index when the method returns 
	 *
	 */
	private function addParentRefs($startIdx = 0, $parentIdx = -1) {
		while (true) {
			$elem = &$this->xmlStructure[$startIdx];
			if ($elem == null) {
				return;
			}
			$elem['parent'] = $parentIdx;
			if ($elem['type'] == 'open') {
				// step down into child elements
				$startIdx = $this->addParentRefs($startIdx+1, $startIdx);
			} else if ($elem['type'] == 'close') {
				return $startIdx+1;
			} else {
				++$startIdx;
			}
		}		
	}
	
	/**
	 * Creates an array for the XML that starts at the index <$startIdx> in the
	 * parsed XML-structure. The elements within the XML-structure are the keys 
	 * in the array. CDATA sections are ignored.
	 *
	 * @param int $startIdx
	 */
	private function createElement($startIdx) {
		$len = count($this->xmlStructure);
//		für ein toplevel open element wird kein array erzeugt
//		für open elemente werden keine attribute gespeichert
		// skip all elements until an opening or complete element is found
		$isComplete = false;
		for ($i = $startIdx; $i < $len; ++$i) {
			$elem = &$this->xmlStructure[$i];
			if ($elem && ($elem['type'] == 'open' || $elem['type'] == 'complete')) {
				$startIdx = $i;
				break;
			}				
		}
		
		
		$endTag = $elem['tag'];
		$attr = '';
		if( array_key_exists('attributes',$elem) ) {
			$attr = $elem['attributes'];	
		}
		if ($elem['type'] == 'complete') {
			$result = array('value' => $elem['value'], 'attributes' => $attr);
			$result = array($endTag => array($result));
			return array($result, $i+1);
		}
		$result = array('value' => array(), 'attributes' => $attr);
		$endLevel = $elem['level'];
		
		for ($i = $startIdx+1; $i < $len; ++$i) {
			$elem = &$this->xmlStructure[$i];
			if (!$elem) {
				continue;
			}
			switch ($elem['type']) {
				case 'open':
				case 'complete':
					list($val, $i) = $this->createElement($i);
					$result['value'][$elem['tag']][] = $val[$elem['tag']][0];
			}
			if (($endTag == $elem['tag'] 
			        && $endLevel == $elem['level']
			        && $elem['type'] == 'close')) {
				// closing element found
			    break;
			}
		}
		
		$result = array($endTag => array($result));
		return array($result, $i);
		
	}
/*
	private function createElement($startIdx) {
		$len = count($this->xmlStructure);
//		für ein toplevel open element wird kein array erzeugt
//		für open elemente werden keine attribute gespeichert
		// skip all elements until an opening or complete element is found
		$isComplete = false;
		for ($i = $startIdx; $i < $len; ++$i) {
			$elem = &$this->xmlStructure[$i];
			if ($elem && ($elem['type'] == 'open' || $elem['type'] == 'complete')) {
				$startIdx = $i;
				$isComplete = ($elem['type'] == 'complete');
				break;
			}				
		}
		
		$result = array();
		
		$endTag = $elem['tag'];
		$endLevel = $elem['level'];
		
		for ($i = $startIdx; $i < $len; ++$i) {
			$elem = &$this->xmlStructure[$i];
			if (!$elem) {
				continue;
			}
			if ($elem['type'] == 'open' || $elem['type'] == 'complete') {
				$tag = $elem['tag'];
				$tagArray = &$result[$tag];
				if (!$tagArray) {
					$result[$tag] = array();
					$tagArray = &$result[$tag];
				}
			}
			switch ($elem['type']) {
				case 'open':
					$attr = $elem['attributes'];
					list($val, $i) = $this->createElement($i+1);
					$cont = array('value' => $val, 
					              'attributes' => $attr);
					$tagArray[] = $cont;
					break;
				case 'complete':
					$cont = array('value' => $elem['value'], 
					              'attributes' => $elem['attributes']);
					$tagArray[] = $cont;
					break;
			}
			if ($isComplete 
			    || ($endTag == $elem['tag'] 
			        && $endLevel == $elem['level']
			        && $elem['type'] == 'close')) {
				// closing element found
			    break;
			}
		}
		return array($result, $i);
		
	}
 
 */	
}