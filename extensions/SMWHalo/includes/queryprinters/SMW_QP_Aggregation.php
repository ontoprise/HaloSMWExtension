<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup SMWHaloQueryPrinters
 * 
 * @defgroup SMWHaloQueryPrinters SMWHalo QueryPrinters
 * @ingroup SMWHalo
 * 
 * This file provides a class which represents a 
 * a query result printer for aggregating query results
 * @author Ingo Steinbauer
 */


/**
 */
class SMWAggregationResultPrinter extends SMWResultPrinter {

	protected $mSep = ", ";
	protected $mTemplate = '';
	protected $functions = array("sum"=>"", "max"=>"","min"=>"","avg"=>"","median"=>"");

    protected function setSupportedParameters() {
        $sep = new SMWQPParameter('sep', 'Separator', '<string>', NULL, "Separator used");
        $template = new SMWQPParameter('template', 'Template', '<string>', NULL, "Template used to display");
        
        $this->mParameters[] = $sep;
        $this->mParameters[] = $template;
        

    }
	protected function readParameters($params,$outputmode) {
		SMWResultPrinter::readParameters($params,$outputmode);

		if (array_key_exists('sep', $params)) {
			$this->mSep = str_replace('_',' ',$params['sep']);
		}
		if (array_key_exists('template', $params)) {
			$this->mTemplate = trim($params['template']);
		}
	}

	protected function getResultText($res,$outputmode) {
		$values = array();
		$dvs = array();

		$i = 0;
		foreach ($res->getPrintRequests() as $pr) {
			$label = $pr->getText($outputmode, null);
			$labels = explode(",", $label);
			for($k=0; $k<count($labels); $k++){
				$label = $labels[$k];
				if($label != ""){
					if($bp = strpos($label, "(")){
						$dvs[$i]["nary"] = trim(substr($label,$bp+1, strpos($label,")")-$bp-1))*1;
						$label = substr($label, 0, $bp);
					} else {
						$dvs[$i]["nary"] = 0;
					}
					$dvs[$i]["values"] = array();
					if(!array_key_exists(trim(strtolower($label)), $this->functions)){
						return $label;
						$label = "sum";
					}
					$dvs[$i]["function"] = trim(strtolower($label));
					$dvs[$i]["dv"] = null;
					if($k == count($labels)-1){
						$dvs[$i]["next"] = true;
					} else {
						$dvs[$i]["next"] = false;
					}
					$i+=1;
				}
			}
		}

		$row = $res->getNext();
		while ( $row !== false ) {
			$first_col = true;

			$colCount = 0;
			for($i=0; $i<count($row); $i++) {
				if (!$first_col) {
					$nextField = false;
					while(!$nextField){
						$field = clone $row[$i];
						$nextField = $dvs[$colCount]["next"];
						while($nextDV = $field->getNextObject()){
							$dv = $nextDV;
							if($dv instanceof SMWRecordValue){
								$dv = $dv->getDVs();
								if(array_key_exists($dvs[$colCount]["nary"], $dv)){
									$dv = $dv[$dvs[$colCount]["nary"]];
								} else {
									$dv = null;
								}
							}
							if($dv == null){
								continue;
							}
							else if ($dv->isNumeric()){
								$dvs[$colCount]["values"][] = $dv->getNumericValue();
							} else if ($numValue = $this->isNumeric($dv->getShortWikiText(SMW_OUTPUT_WIKI))){
								$dvs[$colCount]["values"][] = $numValue;
							}
							$dvs[$colCount]["dv"] = $dv;
						}
						$colCount+=1;
					}
				} else {
					$first_col = false;
				}

			}
			$row = $res->getNext();
		}

		$result = '';
		if($this->mTemplate != ""){
			$result .= '{{'.$this->mTemplate;
			$this->hasTemplates = true;
		}
		for($i=0; $i<count($dvs); $i++){
			if($this->mTemplate != ""){
				$result .= '|'.($i+1).'=';
			} else if ($i != 0){
				$result .= $this->mSep;
			}
			if($dvs[$i]["dv"] == null){
				$result .= "null";
				continue;
			}
			$sres = 0;
			if($dvs[$i]["function"] == "sum"){
				foreach($dvs[$i]["values"] as $v){
					if($v != ""){
						$sres += $v;
					}
				}
			} else if ($dvs[$i]["function"] == "avg"){
				foreach($dvs[$i]["values"] as $v){
					if($v != ""){
						$sres += $v;
					}
				}
				$sres = $sres / count($dvs[$i]["values"]);
			} else if ($dvs[$i]["function"] == "max"){
				$sres = @max($dvs[$i]["values"]);
			} else if ($dvs[$i]["function"] == "count"){
				$sres = count($dvs[$i]["values"]);
			} else if ($dvs[$i]["function"] == "min"){
				$sres = @min($dvs[$i]["values"]);
			} else if ($dvs[$i]["function"] == "median"){
				sort($dvs[$i]["values"]);
				$numOfItems = count($dvs[$i]["values"]);
				if($numOfItems%2){
					$sres = $dvs[$i]["values"][$numOfItems/2-0.5];
				} else {
					$sres = ($dvs[$i]["values"][$numOfItems/2]+$dvs[$i]["values"][$numOfItems/2-1])/2;
				}
			} else {
				$result .= print_r($dvs[$i]["values"], true)."\r\n";
				continue;
			}

			$dvs[$i]["dv"]->setXSDValue($sres, $dvs[$i]["dv"]->getUnit());
			$result .= $dvs[$i]["dv"]->getWikiValue(SMW_OUTPUT_WIKI, null);
		}
		if($this->mTemplate != ""){
			$result .= '}}';
		}
		return $result;
	}

	private function isNumeric($candidate){
		$decseparator = wfMsgForContent('smw_decseparator');
		$kiloseparator = wfMsgForContent('smw_kiloseparator');

		$parts = preg_split('/([-+]?\s*\d+(?:\\' . $kiloseparator . '\d\d\d)*' .
		                      '(?:\\' . $decseparator . '\d+)?\s*(?:[eE][-+]?\d+)?)/u',
		trim(str_replace(array('&nbsp;','&thinsp;', ' '), '', $candidate)),
		2, PREG_SPLIT_DELIM_CAPTURE);

		if (count($parts) >= 2) {
			$numstring = str_replace($kiloseparator, '', preg_replace('/\s*/u', '', $parts[1])); // simplify
			if ($decseparator != '.') {
				$numstring = str_replace($decseparator, '.', $numstring);
			}
			return $numstring;
		}
		return false;
	}
}

