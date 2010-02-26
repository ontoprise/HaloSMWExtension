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
 * for webservice usage results in the table format
 *
 * @author Ingo Steinbauer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_WebServiceResultPrinter.php");

/**
 * an abstract class which represents a printer for web service usage results
 * in the table format
 *
 */
class WebServiceTableResultPrinter extends WebServiceResultPrinter {

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
		$return = "<table width=\"100%\" class=\"smwtable\">";
		for($i = 0; $i < sizeof($wsResult) ;$i++){
			$return.= "<tr>";
			for($k=0; $k < sizeof($wsResult[$i]); $k++){
				if($i == 0){
					$return.= "<th>".$wsResult[$i][$k]."</th>";
				} else {
					$return.= "<td>".$wsResult[$i][$k]."</td>";
				}
			}
			$return.= "</tr>";
		}


			
		//			for($k=0; $k < sizeof($values); $k++){
		//				if($i==0){
		//					$return.= "<th>".$values[$k]."</th>";
		//				} else {
		//
		//				}
		//			}
		//			$i++;
		//			$return.= "</tr>";
		//		}
		$return.="</table>";
		return $return;
	}
}