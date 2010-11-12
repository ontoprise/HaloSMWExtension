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
 * @ingroup DIWSResultPrinters
 * This file provides an class which represents a printer
 * for webservice usage results in the transposed format
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
class WebServiceTransposedResultPrinter extends WebServiceResultPrinter {

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
	public function getWikiText($template, $wsResult){
		$result = array();
		for($i = 1; $i<sizeof($wsResult);$i++){
			$k=0;
			foreach($wsResult[$i] as $wsR){
				if($i == 1){
					$result[$k] = array();
				}
				$result[$k][$i-1] = $wsR;
				$k++;
			}
		}

		$return = "";
		if($template != ""){
			
			$return .= "{{".$template."";
			
			$i=1;
			foreach($result as $res){
				$return.= "|".$i++."=";
				$k = 0;
				foreach($res as $r){
					if($r != ""){
						if($k>0){
							$return .= ",";
						}
						$return .= $r;
						$k++;
					}
				}
			}
			$return.= "}}";
		} else {
			foreach($result as $res){
				if($i > 0){
					$return .= ", ";
				}
				$k = 0;
				foreach($res as $r){
					if($r != ""){
						if($k>0){
							$return .= ",";
						}
						$return .= $r;
						$k++;
					}
				}
			}
		}
		return $return;
	}
}