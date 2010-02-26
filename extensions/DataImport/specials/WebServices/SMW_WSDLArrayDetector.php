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
 * @ingroup DIWebServices
 * 
 * @author Ingo Steinbauer
 */

/**
 * Class for detecting arrays that are defined in a wsdl by the maxOccurs-attribute
 *
 * @author Ingo Steinbauer
 *
 */
class WSDLArrayDetector {
	var $wsdl = ""; // the $wsdl
	var $xs = ""; // prefix for the xsd-namespace
	var $paths = array(); // the resulting flat paths that contain arrays

	/*
	 * @parameter string $uri : the uri of the wsdl
	 */
	function WSDLArrayDetector($uri){
		$this->wsdl = new SimpleXMLElement($uri, null, true);

		//register namespaces and detect XSD-namespace
		$namespaces = $this->wsdl->getNamespaces(true);
		foreach($namespaces as $prefix => $ns){
			$this->wsdl->registerXPathNamespace($prefix, $ns);
			if($ns == "http://www.w3.org/2001/XMLSchema"){
				$this->xs = $prefix;
			}
		}
	}

	/**
	 * This method returns all parameter-paths that contain
	 * arrays defined by the maxoccurs-attribute
	 *
	 * @param string $nodeType : type of the root node
	 * @param string_type $nodeName : name of the root node
	 * @return array<string> : the flat paths that contain arrays
	 */
	public function getArrayPaths($nodeType, $nodeName){
		$this->paths = array();
		$this->getChildren($nodeType, $nodeName, "","", false, true);
		return $this->paths;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $nodeType : type of the node to check
	 * @param string $nodeName : name of the node to check
	 * @param string $path : the path which is generated recursively
	 * @param string $xpath : the ypath used in the prior recursion step
	 * @param boolean $isArray
	 * @param boolean $firstOne : is this the first recursion step?
	 * @returns array<string> flat paths that contain arrays
	 */
	private function getChildren($nodeType, $nodeName, $path, $xpath, $isArray, $firstOne = false){
		if(strlen($nodeName) == 0){
			$nodeName = $nodeType;
		}
		//echo("start: " .$nodeType." : ".$nodeName." : ".$path."\n");
		if(strlen($path) == 0){
			$path = $nodeName;
		} else {
			if(!$firstOne){
				$path .= ".".$nodeName;
			}
		}
		$path .= $isArray ? "[]" : "";
		
		if(strpos($path, ".".$nodeName.".") > -1 || strpos($path, ".".$nodeName."[].") > -1){
			$this->paths[] = $path."##overflow##";
		} else {
			if(strlen($xpath) > 0){
				$xpathE = $xpath."[@name=\"".$nodeName."\"]/".$this->ws.":complexType/".$this->ws.":sequence/".$this->ws.":element";
			} else {
				$xpathE = "//".$this->xs.":element[@name=\"".$nodeType."\" and not(./parent::".$this->xs.":sequence)]/".$this->xs.":complexType/".$this->xs.":sequence/".$this->xs.":element";
				$xpathC = "//".$this->xs.":complexType[@name=\"".$nodeType."\" and not(./parent::".$this->xs.":sequence)]/".$this->xs.":sequence/".$this->xs.":element";
			}

			@$childNodesE = $this->wsdl->xpath($xpathE);
			@$childNodesC = $this->wsdl->xpath($xpathC);

			$childNodesE = !$childNodesE ? array() : $childNodesE;
			$childNodesC = !$childNodesC ? array() : $childNodesC;
			$childNodes = array_merge($childNodesC, $childNodesE);

			$insertPath = true;
			if($childNodes){
				foreach($childNodes as $node){
					$isArray = false;
					if($node["maxOccurs"] == "unbounded" || $node["maxOccurs"]*1 > 1){
						$isArray = true;
					}
					if(strlen($node["ref"]) > 0){
						$ref = $node["ref"];
						if(strpos($node["ref"], ":") > -1){
							$ref = substr($node["ref"], strpos($node["ref"], ":")+1);
						}
						$this->getChildren($ref, $ref, $path, "", $isArray);
						$insertPath = false;
					} else if(strlen($node["type"]) > 0){
						$type = $node["type"];
						if(substr($type, strpos($type, ":")) == $this->xs){
							$path .= ".".$node["name"]." (";
							continue;
						}
						$type = substr($node["type"], strpos($node["type"], ":")+1);
						$this->getChildren($type, $node["name"], $path, "", $isArray);
						$insertPath = false;
					} else if(strlen($node["name"]) > 0){
						$this->getChildren($node["name"], $node["name"], $path, $xpathE, $isArray);
						$insertPath = false;
					}
				}
			}
			if($insertPath){
				if(strpos($path, "[]") > -1){
					$this->paths[] = $path;
				}
			}
		}
	}

	public function cleanResultParts($dpaths){
		$this->paths = array();
		foreach($dpaths as $path){
			$this->paths[] = substr($path, strpos($path, ".")+1);

		}
		return $this->paths;
	}

	/**
	 * 	this methods merges the paths returned by the SoapClient
	 * with the paths detected by the WSDLArrayDetector
	 *
	 * @param array<string> $soapPaths
	 * @param array<string> $adPaths
	 * @return array<string>
	 */
	public function mergePaths($soapPaths, $adPaths){
		asort($soapPaths);
		asort($adPaths);
		$k=0;
		foreach($adPaths as $adPath){
			$tADPath = str_replace("[]", "", $adPath);
			for($m=$k; $m < count($soapPaths); $m++){
				$soapPath = $soapPaths[$m];
				if(strrpos($tADPath, "#")){
					$tADPath = substr($tADPath, 0, strrpos($tADPath, "#"));
				}
				if(strpos(substr($soapPath, 0, strrpos($soapPath, ".")), $tADPath) == 0
				|| substr($soapPath, $tADPath) == 0){
					$soapPathArray = explode(".", $soapPath);
					$adPathArray = explode(".", $adPath);
					$tADPathArray = explode(".",$tADPath);
					$i = 0;
					foreach($tADPathArray as $tADPathStep){
						if($tADPathStep == $soapPathArray[$i]){
							$soapPathArray[$i] = $adPathArray[$i];
						} else {
							break;
						}
						$i++;
					}
					$soapPaths[$m] = implode(".", $soapPathArray);
				} else {
					break;
					$k=m;
				}
			}
		}
		return $soapPaths;
	}

}

