<?php
global $smwgHaloIP;


class WSDLArrayDetector {
	var $wsdl = "";
	var $xs;
	var $paths =array();

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

	public function getArrayPaths($nodeType, $nodeName){
		$this->paths = array();
		$this->getChildren($nodeType, $nodeName, "", false, true);
		return $this->paths;
	}

	private function getChildren($nodeName, $path, $xpath, $isArray, $firstOne = false){
		if(strlen($path) == 0){
			$path = $nodeName;
		} else {
			if(!$firstOne){
				$path .= ".".$nodeName;
			}
		}

		if($isArray){
			$path .= "[]";
		}

		if(strpos($path, ".".$nodeName.".") > -1 || strpos($path, ".".$nodeName."[].") > -1){
			$this->paths[] = $path."##overflow##";
		} else {
			if(strlen($xpath) > 0){
				$xpath = $xpath."[@name=\"".$nodeName."\"]/".$this->ws.":complexType/".$this->ws.":sequence/".$this->ws.":element";
			} else {
				$xpath = "//".$this->xs.":element[@name=\"".$nodeName."\" and not(./parent::".$this->xs.":sequence)]/".$this->xs.":complexType/".$this->xs.":sequence/".$this->xs.":element";
			}
			//			$this->paths[] = $xpath;
			//			return;
			@$childNodes = $this->wsdl->xpath($xpath);

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
						$this->getChildren($ref, $path, "", $isArray);
						$insertPath = false;
					} else if(strlen($node["name"]) > 0){
						$this->getChildren($node["name"], $path, $xpath, $isArray);
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

	public function mergePaths($soapPaths, $adPaths){
		foreach($adPaths as $adPath){
			$tADPath = str_replace("[]", "", $adPath);
			$k = 0;
			foreach($soapPaths as $soapPath){
				if(strpos($soapPath, $tADPath) > -1){
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
					$soapPaths[$k] = implode(".", $soapPathArray);
				}
				$k++;
			}
		}
		return $soapPaths;
	}

}

?>