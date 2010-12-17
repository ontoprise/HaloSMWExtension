<?php
require_once("SMW_XPathProcessor.php");

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
 * This class allows process subparameters of parameter templates
 *
 * @author Ingo Steinbauer
 *
 */
class SMWSubParameterProcessor {

	private $parameterDefinition = "";

	private $unavailableSubParameters = array();
	private $defaultSubParameters = array();
	private $missingSubParameters = array();
	private $optionalSubParameters = array();
	private $passedSubParameters = array();

	public function getUnavailableSubParameters(){return $this->unavailableSubParameters;}
	public function getDefaultSubParameters(){return $this->defaultSubParameters;}
	public function getMissingSubParameters(){return $this->missingSubParameters;}
	public function getOptionalSubParameters(){return $this->optionalSubParameters;}
	public function getPassedSubParameters(){return $this->passedSubParameters;}

	/*
	 * constructor for SMWSubParameterProcessor
	 * 
	 * @parame $parameterDefinition string : the WWSD parameter definition
	 * @param $subParameters <string, string> : array of subparameters with name
	 * 										as key and value 
	 */
	public function __construct($parameterDefinition, $subParameters){
		$this->parameterDefinition = $parameterDefinition;

		$xpathProcessor = new XPathProcessor($parameterDefinition);
		$availableSubParameters = $xpathProcessor->evaluateQuery("//subparameter/@name");
		$availableSubParameters = array_flip($availableSubParameters);
		
		$availableSubParametersTMP = $availableSubParameters;
		$availableSubParameters = array();
		foreach($availableSubParametersTMP as $key => $value){
			$availableSubParameters[strtolower($key)] = $value;
		}
		
		foreach($subParameters as $key => $value){
			if(!array_key_exists($key, $availableSubParameters)){
				$this->unavailableSubParameters[$key] = null;
			} else {
				$this->passedSubParameters[$key] = $value;
				unset($availableSubParameters[$key]);
			}
		}

		$this->optionalSubParameters = $availableSubParameters;

		$availableSubParameters = $xpathProcessor->evaluateQuery("//subparameter[not(@optional='true')]/@name");

		$availableSubParameters = array_flip($availableSubParameters);

		foreach($subParameters as $key => $value){
			if(array_key_exists($key, $availableSubParameters)){
				unset($availableSubParameters[$key]);
			}
		}

		foreach($availableSubParameters as $key => $value){
			unset($this->optionalSubParameters[$key]);
			$defValue = $xpathProcessor->evaluateQuery("//subparameter[@name='".$key."']/@defaultValue");
			if(count($defValue) == 1){
				$this->defaultSubParameters[$key] = $defValue[0];
			} else {
				$this->missingSubParameters[$key] = null;
			}
		}
	}

	/**
	 * Creates the value for the parameter when calling the web service
	 * 
	 * @return string : the value for the parameter
	 */
	public function createParameterValue(){
		$xpathProcessor = new XPathProcessor($this->parameterDefinition);
		$availableSubParameters = $xpathProcessor->evaluateQuery("//subparameter/@name");
		$availableSubParameters = array_flip($availableSubParameters);

		foreach($availableSubParameters as $key => $value){
			if(array_key_exists($key, $this->passedSubParameters)){
				$availableSubParameters[$key] = $this->passedSubParameters[$key];
			} else if(array_key_exists($key, $this->defaultSubParameters)){
				$availableSubParameters[$key] = $this->defaultSubParameters[$key];
			} else if(array_key_exists($key, $this->optionalSubParameters)){
				$availableSubParameters[$key] = "";
			}
		}
		$subPathStartPos = 0;
		foreach($availableSubParameters as $key => $value){
			$subPathStartPos = strpos($this->parameterDefinition, "<subparameter", $subPathStartPos);
			$subPathStart= subStr($this->parameterDefinition, 0, $subPathStartPos);
				
			$subPathEnd = subStr($this->parameterDefinition,
			strpos($this->parameterDefinition, ">", $subPathStartPos)+1);
				
			$this->parameterDefinition = $subPathStart.$value.$subPathEnd;
		}

		$xpathProcessor = new XPathProcessor($this->parameterDefinition);
		$textNodes = $xpathProcessor->evaluateQuery("//parameter/text()");
		$response = "";
		foreach($textNodes as $textNode){
			$response .= trim($textNode);
		}

		return $response;
	}
}
