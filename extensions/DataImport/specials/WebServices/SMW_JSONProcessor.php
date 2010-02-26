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
 * Thsi file contains a JSON to XML converter.
 * @ingroup DIWebServices
 * @author Ingo Steinbauer
 */

/**
 * This class allows to convert json to xml so that it can be evaluated
 * via XPath
 *
 * @author Ingo Steinbauer
 *
 */
class JSONProcessor {
	
	/*
	 * Converts json to xml so that it can be evaluated via xpath
	 * 
	 * @param jsonString<string> ; the json string to convert
	 * 
	 * @return <string> the xml string
	 */
	public function convertJSON2XML($jsonString){
		$jsonObject = json_decode($jsonString, false);
		return "<JSONRoot>".$this->doConvertJSON2XML($jsonObject)."</JSONRoot>";
	}
	
	/*
	 * Helper method for convertig json to xml
	 */
	private function doConvertJSON2XML($jsonObject, $tagName = null){
		$result = "";
		$isArray = false;
		if(is_array($jsonObject)){
			$isArray = true;
		}
		
		if(is_object($jsonObject) || is_array($jsonObject)){
			foreach($jsonObject as $jK => $jV){
				if(!$isArray){
					$tagName = $jK;
				}
				if(!is_array($jV) || $isArray){
					$result .= "<".$tagName.">";
				}
				if(is_array($jV) || is_object($jV)){
					$result .= $this->doConvertJSON2XML($jV, $tagName);
				} else {
					$result .= "<![CDATA[".$jV."]]>";
				}
				if(!is_array($jV) || $isArray){
					$result .= "</".$tagName.">";
				}
			}
		} else {
			return "";
		}
		
		return $result;
	}
}