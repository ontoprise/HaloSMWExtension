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
 * This class extends and replaces SMWASKPage, which implementents
 * the special page Special:ASK
 * 
 * Two things are modified:
 * - Source parameter is evaluated and the DIWSSMWStore is used if necessary
 * - links for editing the query are removed from the HTML output.
 */
class DISMWAskPageReplacement extends SMWAskPage {

	private $m_isWSCall = false;

	/**
	 * TODO: document
	 */
	protected function makeHTMLResult() {
		global $wgOut, $smwgAutocompleteInSpecialAsk;
		
		$this->checkIfThisIsAWSCALL();

		$delete_msg = wfMsg( 'delete' );

		// Javascript code for the dynamic parts of the page
		$javascript_text = <<<END
<script type="text/javascript">
function updateOtherOptions(strURL) {
	jQuery.ajax({ url: strURL, context: document.body, success: function(data){
		jQuery("#other_options").html(data);
	}});
}

// code for handling adding and removing the "sort" inputs
var num_elements = {$this->m_num_sort_values};

function addInstance(starter_div_id, main_div_id) {
	var starter_div = document.getElementById(starter_div_id);
	var main_div = document.getElementById(main_div_id);

	//Create the new instance
	var new_div = starter_div.cloneNode(true);
	var div_id = 'sort_div_' + num_elements;
	new_div.className = 'multipleTemplate';
	new_div.id = div_id;
	new_div.style.display = 'block';

	var children = new_div.getElementsByTagName('*');
	var x;
	for (x = 0; x < children.length; x++) {
		if (children[x].name)
			children[x].name = children[x].name.replace(/_num/, '[' + num_elements + ']');
	}

	//Create 'delete' link
	var remove_button = document.createElement('span');
	remove_button.innerHTML = '[<a href="javascript:removeInstance(\'sort_div_' + num_elements + '\')">{$delete_msg}</a>]';
	new_div.appendChild(remove_button);

	//Add the new instance
	main_div.appendChild(new_div);
	num_elements++;
}

function removeInstance(div_id) {
	var olddiv = document.getElementById(div_id);
	var parent = olddiv.parentNode;
	parent.removeChild(olddiv);
}
</script>

END;

		$wgOut->addScript( $javascript_text );

		if ( $smwgAutocompleteInSpecialAsk ) {
			self::addAutocompletionJavascriptAndCSS();
		}

		$result = '';
		$result_mime = false; // output in MW Special page as usual

		// build parameter strings for URLs, based on current settings
		$urlArgs['q'] = $this->m_querystring;

		$tmp_parray = array();
		foreach ( $this->m_params as $key => $value ) {
			if ( !in_array( $key, array( 'sort', 'order', 'limit', 'offset', 'title' ) ) ) {
				$tmp_parray[$key] = $value;
			}
		}

		$urlArgs['p'] = SMWInfolink::encodeParameters( $tmp_parray );
		$printoutstring = '';

		foreach ( $this->m_printouts as $printout ) {
			$printoutstring .= $printout->getSerialisation() . "\n";
		}

		if ( $printoutstring != '' ) $urlArgs['po'] = $printoutstring;
		if ( array_key_exists( 'sort', $this->m_params ) )  $urlArgs['sort'] = $this->m_params['sort'];
		if ( array_key_exists( 'order', $this->m_params ) ) $urlArgs['order'] = $this->m_params['order'];

		if ( $this->m_querystring != '' ) {
			$queryobj = SMWQueryProcessor::createQuery( $this->m_querystring, $this->m_params, SMWQueryProcessor::SPECIAL_PAGE , $this->m_params['format'], $this->m_printouts );
			$queryobj->params = $this->m_params;
			$store = $this->getStore();
			$res = $store->getQueryResult( $queryobj );
			
			// Try to be smart for rss/ical if no description/title is given and we have a concept query:
			if ( $this->m_params['format'] == 'rss' ) {
				$desckey = 'rssdescription';
				$titlekey = 'rsstitle';
			} elseif ( $this->m_params['format'] == 'icalendar' ) {
				$desckey = 'icalendardescription';
				$titlekey = 'icalendartitle';
			} else { $desckey = false; }

			if ( ( $desckey ) && ( $queryobj->getDescription() instanceof SMWConceptDescription ) &&
			     ( !isset( $this->m_params[$desckey] ) || !isset( $this->m_params[$titlekey] ) ) ) {
				$concept = $queryobj->getDescription()->getConcept();

				if ( !isset( $this->m_params[$titlekey] ) ) {
					$this->m_params[$titlekey] = $concept->getText();
				}

				if ( !isset( $this->m_params[$desckey] ) ) {
					// / @bug The current SMWStore will never return SMWConceptValue (an SMWDataValue) here; it might return SMWDIConcept (an SMWDataItem)
					$dv = end( smwfGetStore()->getPropertyValues( SMWWikiPageValue::makePageFromTitle( $concept ), new SMWDIProperty( '_CONC' ) ) );
					if ( $dv instanceof SMWConceptValue ) {
						$this->m_params[$desckey] = $dv->getDocu();
					}
				}
			}

			$printer = SMWQueryProcessor::getResultPrinter( $this->m_params['format'], SMWQueryProcessor::SPECIAL_PAGE );
			$result_mime = $printer->getMimeType( $res );

			global $wgRequest;

			$hidequery = $wgRequest->getVal( 'eq' ) == 'no';

			// if it's an export format (like CSV, JSON, etc.),
			// don't actually export the data if 'eq' is set to
			// either 'yes' or 'no' in the query string - just
			// show the link instead
			if ( $this->m_editquery || $hidequery ) $result_mime = false;

			if ( $result_mime == false ) {
				if ( $res->getCount() > 0 ) {
					if ( $this->m_editquery ) {
						$urlArgs['eq'] = 'yes';
					}
					else if ( $hidequery ) {
						$urlArgs['eq'] = 'no';
					}

					$navigation = $this->getNavigationBar( $res, $urlArgs );
					$result .= '<div style="text-align: center;">' . "\n" . $navigation . "\n</div>\n";
					$query_result = $printer->getResult( $res, $this->m_params, SMW_OUTPUT_HTML );

					if ( is_array( $query_result ) ) {
						$result .= $query_result[0];
					} else {
						$result .= $query_result;
					}

					$result .= '<div style="text-align: center;">' . "\n" . $navigation . "\n</div>\n";
				} else {
					$result = '<div style="text-align: center;">' . wfMsgHtml( 'smw_result_noresults' ) . '</div>';
				}
			} else { // make a stand-alone file
				$result = $printer->getResult( $res, $this->m_params, SMW_OUTPUT_FILE );
				$result_name = $printer->getFileName( $res ); // only fetch that after initialising the parameters
			}
		}

		if ( $result_mime == false ) {
			if ( $this->m_querystring ) {
				$wgOut->setHTMLtitle( $this->m_querystring );
			} else {
				$wgOut->setHTMLtitle( wfMsg( 'ask' ) );
			}

			$result = $this->getInputForm(
				$printoutstring,
				'offset=' . $this->m_params['offset']
					. '&limit=' . $this->m_params['limit']
					. wfArrayToCGI( $urlArgs )
			) . $result;
			
			$result = $this->postProcessHTML($result);
			
			$wgOut->addHTML( $result );
		} else {
			$wgOut->disable();

			header( "Content-type: $result_mime; charset=UTF-8" );

			if ( $result_name !== false ) {
				header( "content-disposition: attachment; filename=$result_name" );
			}

			echo $result;
		}
	}

	/*
	 * Checks if source=webservice
	 */
	private function checkIfThisIsAWSCALL(){
		if(array_key_exists('source', $this->m_params) && $this->m_params['source'] == 'webservice'){
			$this->m_isWSCall = true;	
		}
	}
	
	/*
	 * uses DIWSSMWStore if source = webservice
	*/
	private function getStore(){
		if($this->m_isWSCall){
			$store = new DIWSSMWStore();	
		} else {
			$store = smwfGetStore();
		}
		return $store;
	}
	
	
	/*
	 * Replaces links for editing the query if source=webservice
	 */
	private function postProcessHTML($result){
		if($this->m_isWSCall){
			$startPos = strpos($result, wfMsg('smw_ask_editquery'));
			$startPos = strrpos($result, '<a'.$startPos);
			$endPos = strpos($result, '</a>', $startPos);
			$endPos = strpos($result, '</a>', $endPos + strlen('</a>'));
			$result = substr($result, 0, $startPos).substr($result, $endPos + strlen('</a>'));
		}
		return $result;
	}
	

}
	