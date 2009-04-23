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
 * This file provides an class which represents a printer
 * for webservice usage results in the list format
 *
 * @author Ingo Steinbauer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_WebServiceResultPrinter.php");

/**
 * an abstract class which represents a printer for web service usage results
 * in the list format
 *
 */
class WebServiceListResultPrinter extends WebServiceResultPrinter {

	static private $instance = NULL;

	/**
	 * get an instance of this class
	 *
	 * @return WebServiceListResultPribter
	 */
	static public function getInstance(){
		if (self::$instance === NULL){
			self::$instance = new self;
		}
		return self::$instance;
	}


	/**
	 * get web service usage result as wikitext
	 *
	 * @param unknown_type $wsResult
	 * @return unknown
	 */
	public function getWikiText($template, $wsResult, $subst){
		$return = "";
		for($i = 1; $i<sizeof($wsResult);$i++){
			if($i != 1){
				$return.= ", ";
			}
			if($template != ""){
				// a template was defined when the ws was called
				
				//check if substitution is necessary
				if(!$subst){
					$return .= "{{".$template."";
				} else {
					$return .= "{{subst:".$template."";
				}
				$k = 1;
				foreach($wsResult[$i] as $wsR){
					$return .= "|".$k++."=".$wsR;
				}
				$return .= "}}";
			} else {
				$return.= implode(", ", $wsResult[$i]);
			}
		}
		return $return;
	}
}

?>