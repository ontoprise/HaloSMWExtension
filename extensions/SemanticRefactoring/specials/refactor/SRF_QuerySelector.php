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

/**
 * Created on 19.12.2011
 *
 * @file
 * @ingroup SREFSpecials
 * @ingroup SREFRefactor
 *
 * @author Kai Kühn
 */


class SRFQuerySelector {

	protected $m_querystring = '';
	protected $m_params = array();
	protected $m_printouts = array();
	protected $m_editquery = false;


	public function __construct($query) {
		$this->m_querystring = $query;

	}
	public function getQueryResult() {

		$this->extractQueryParameters();
		
		SMWQueryProcessor::addThisPrintout(  $this->m_printouts, $this->m_params );
		$this->m_params = SMWQueryProcessor::getProcessedParams($this->m_params, $this->m_printouts);
		$queryobj = SMWQueryProcessor::createQuery( $this->m_querystring, $this->m_params, SMWQueryProcessor::SPECIAL_PAGE , '_srftable', $this->m_printouts );
		$res = smwfGetStore()->getQueryResult( $queryobj );
		$printer = SMWQueryProcessor::getResultPrinter('_srftable', SMWQueryProcessor::SPECIAL_PAGE );
		$query_result = $printer->getResult( $res, $this->m_params, SMW_OUTPUT_HTML );

		if ( is_array( $query_result ) ) {
			$result = $query_result[0];
		} else {
			$result = $query_result;
		}
		$html = $result;

		return array('html' => $html, 'result'=>$res);
	}
	/**
	 * This code rather hacky since there are many ways to call that special page, the most involved of
	 * which is the way that this page calls itself when data is submitted via the form (since the shape
	 * of the parameters then is governed by the UI structure, as opposed to being governed by reason).
	 *
	 * @param string $p
	 */
	protected function extractQueryParameters(  ) {
		global $wgRequest, $smwgQMaxInlineLimit;
        $oldQMaxLimit = $smwgQMaxInlineLimit;
        $smwgQMaxInlineLimit = 500;
		// Check for q= query string, used whenever this special page calls itself (via submit or plain link):
		list($queryText, $printouts) = self::splitASKQuery($this->m_querystring);
		$this->m_querystring = $queryText;
		$paramstring = $printouts;

		$rawparams=array();
		if ( $this->m_querystring != '' ) {
			$rawparams[] = $this->m_querystring;
		}

		// Check for param strings in po (printouts), appears in some links and in submits:

		if ( $paramstring != '' ) { // parameters from HTML input fields
			$ps = explode( "|", $paramstring ); // params separated by newlines here (compatible with text-input for printouts)

			foreach ( $ps as $param ) { // add initial ? if omitted (all params considered as printouts)
				$param = trim( $param );
				$rawparams[] = $param;
			}
		}

		// Now parse parameters and rebuilt the param strings for URLs.
		SMWQueryProcessor::processFunctionParams( $rawparams, $this->m_querystring, $this->m_params, $this->m_printouts );

		// Try to complete undefined parameter values from dedicated URL params.
		if ( !array_key_exists( 'format', $this->m_params ) ) {
			$this->m_params['format'] = 'broadtable';
		}

		if ( !array_key_exists( 'order', $this->m_params ) ) {
			$order_values = $wgRequest->getArray( 'order' );

			if ( is_array( $order_values ) ) {
				$this->m_params['order'] = '';

				foreach ( $order_values as $order_value ) {
					if ( $order_value == '' ) $order_value = 'ASC';
					$this->m_params['order'] .= ( $this->m_params['order'] != '' ? ',' : '' ) . $order_value;
				}
			}
		}

		$this->m_num_sort_values = 0;

		if  ( !array_key_exists( 'sort', $this->m_params ) ) {
			$sort_values = $wgRequest->getArray( 'sort' );
			if ( is_array( $sort_values ) ) {
				$this->m_params['sort'] = implode( ',', $sort_values );
				$this->m_num_sort_values = count( $sort_values );
			}
		}

		if ( !array_key_exists( 'offset', $this->m_params ) ) {
			$this->m_params['offset'] = $wgRequest->getVal( 'offset' );
			if ( $this->m_params['offset'] == '' )  $this->m_params['offset'] = 0;
		}

		/*if ( !array_key_exists( 'limit', $this->m_params ) ) {
			$this->m_params['limit'] = $wgRequest->getVal( 'limit' );

			if ( $this->m_params['limit'] == '' ) {
				$this->m_params['limit'] = ( $this->m_params['format'] == 'rss' ) ? 10 : 20; // Standard limit for RSS.
			}
		}*/

		$this->m_params['limit'] = 500; //min( array($this->m_params['limit'], SREF_QUERY_PAGE_LIMIT) );

		$this->m_editquery = ( $wgRequest->getVal( 'eq' ) == 'yes' ) || ( $this->m_querystring == '' );
		
		$smwgQMaxInlineLimit = $oldQMaxLimit;
	}

	/**
	 * Get a localised Title object for a specified special page name
	 *
	 * @return Title object
	 */
	static function getTitleFor( $name, $subpage = false ) {
		$name = self::getLocalNameFor( $name, $subpage );
		if ( $name ) {
			return Title::makeTitle( NS_SPECIAL, $name );
		} else {
			throw new MWException( "Invalid special page name \"$name\"" );
		}
	}

	private static function splitASKQuery($query) {
		$result = array();
		$result[0] = trim($query);
		$result[1] = "";
		$i = 0;
		$index = -1;
		do {
			$index = strpos($query, "|");
			if ($index > -1 && strlen($query) > ($index + 1)) {
				if ($query[$index + 1] != '|') {
					$result[0] = trim(substr($query, 0, $index));
					$result[1] = trim(substr($query, $index + 1));
					break;
				} else {
					$i = $index + 2;
					continue;
				}
			}
			$i = $index + 1;
		} while ($index > -1);
		if (strlen($result[0])-1 >= 0 && $result[0][strlen($result[0])-1] == "|") {
			$result[0] = substr($result[0], 0, strlen($result[0]) - 1);
		}
		return $result;
	}
}