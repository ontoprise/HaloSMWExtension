<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


/*
 * Query printer which displays always the most current query result via Ajax calls 
 */
class SMWLiveQueryPrinter extends SMWResultPrinter {

	/*
	 * Returns printer name
	 */
	public function getName() {
		return 'Ajax';
	}

	public function getMimeType( $res ) {
		//This is a trick to force SMW to also show TF if no results exist
		if($res->getCount() == 0 ){
			return true;
		} else {
			return false;
		}
	}


	/*
	 * Also called by Halo Initialize
	 */
	public function getScripts() {
		return array();
	}

	/*
	 * Also called by Halo Initialize
	 */
	function getStylesheets() {
		return array();//
	}


	/*
	 * Returns the HTML output of this query printer
	 */
	protected function getResultText(SMWQueryResult $queryResult, $outputMode ) {
		$this->isHTML = true;

		$params = array($queryResult->getQueryString());
		$updateFrequency = 60;
		foreach($this->m_params as $label => $value){
			if($label == 'format'){
			} else if($label == 'subformat'){
				$params[] = 'format='.$value;
			} else if($label == 'update frequency'){
				$updateFrequency = $value;
			}else {
				$params[] = $label.'='.$value;
			}
		}
		
		$queryType = 'ask';
		if($queryResult instanceof SMWHaloQueryResult 
					&&$queryResult->getQuery() instanceof SMWSPARQLQuery 
					&& !$queryResult->getQuery()->fromASK){
			$queryType = 'sparql';
		}
		
		$params = array_merge($params, $this->getPrintRequests($queryResult, $queryType));
		 
		$query = '{{#'.$queryType.':'.implode('|', $params).'}}';
		$query = '<pre>'.str_replace(array('<','>'), array('&lt;', '&gt;'), $query).'</pre>';
		
		global $smwgIQRunningNumber, $smwgHaloScriptPath;
		$id = 'live-query-' . $smwgIQRunningNumber;
		$html = '<div class="lq-container" id="'.$id.'" lq-frequency="'.$updateFrequency.'">';
		$html .= '<img src="' . $smwgHaloScriptPath . '/skins/ajax-loader.gif">';
		$html .= '<span class="lq-query" style="display: none">';
		$html .= $query;
		$html .= '</span>';
		$html .= '</div>';
		
		SMWOutputs::requireResource('ext.smwhalo.livequeries');
		
		 return $html;
	}

	public function getParameters() {
		$params = array_merge(parent::getParameters(), parent::textDisplayParameters());
		$params[] = array( 'name' => 'enable add', 'type' => 'subformat',
			'description' => wfMsg( 'smw_tf_paramdesc_add' ),
			'values' => array( 'ul', 'ol', 'table', 'template'));
		
		return $params;
	}
	
	
	private function getPrintRequests($queryResult, $queryType){
		$printRequests = array();
		foreach($queryResult->getPrintRequests() as $printRequest){

			if(is_null($printRequest->getData())){
				//deal with category print requests
				if(strpos($printRequest->getHash(), '0:') === 0){
					$label = $printRequest->getWikiText(false);
					$printRequests[] = '?Category='.$label;
				}

				continue;
			}
			
			$label = $printRequest->getWikiText(false);
			//$labelIntro = substr($label, 0, strpos($label, '>') + 1);
			//$label = substr($label, strpos($label, '>') + 1);
			//$labelOutro = substr($label, strpos($label, '<'));
			//$label = substr($label, 0, strpos($label, '<'));
			//$label = explode('=', $label);
			//$preload = count($label) > 1 ? $label[1] : '';
			//$label = $labelIntro.$label[0].$labelOutro;

			$printRequests[] = '?'.$printRequest->getData()->getText().'='.$label;
		}
		
		return $printRequests;
	}

}
