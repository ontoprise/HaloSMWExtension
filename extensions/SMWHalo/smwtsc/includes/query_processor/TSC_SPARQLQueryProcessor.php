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
 * @file
 * @ingroup SMWHaloSMWDeviations 
 * @author kai
 *
 */
class SMWSPARQLQueryProcessor extends SMWQueryProcessor {

	/**
	 * Preprocess a query as given by an array of parameters as is  typically
	 * produced by the #ask parser function. The parsing results in a querystring,
	 * an array of additional parameters, and an array of additional SMWPrintRequest
	 * objects, which are filled into call-by-ref parameters.
	 * $showmode is true if the input should be treated as if given by #show
	 */
	/**
	 * Preprocess a query as given by an array of parameters as is typically
	 * produced by the #ask parser function. The parsing results in a querystring,
	 * an array of additional parameters, and an array of additional SMWPrintRequest
	 * objects, which are filled into call-by-ref parameters.
	 * $showmode is true if the input should be treated as if given by #show
	 */
	static public function processFunctionParams(array $rawparams, &$querystring, &$params, &$printouts, $showmode=false) {
		global $wgContLang;
		$querystring = '';
		$printouts = array();
		$params = array();
		$doublePipe=false;
		foreach ($rawparams as $name => $param) {
			if ($doublePipe) {
				$querystring .= " || ". $param;
				$doublePipe = false;
				continue;
			}
			if ( is_string($name) && ($name != '') ) { // accept 'name' => 'value' just as '' => 'name=value'
				$param = $name . '=' . $param;
			}
			if ($param == '') {
				$doublePipe = true;
				continue;
			} elseif ($param{0} == '?') { // print statement
				$param = substr($param,1);
				$parts = explode('=',$param,2);
				$propparts = explode('#',$parts[0],2);
				if (trim($propparts[0]) == '') { // print "this"
					$printmode = SMWPrintRequest::PRINT_THIS;
					$label = ''; // default
                    $title = NULL;
                    $data = NULL;
				} elseif ($wgContLang->getNsText(NS_CATEGORY) == ucfirst(trim($propparts[0]))) { // print categories
					$title = NULL;
					$printmode = SMWPrintRequest::PRINT_CATS;
					if (count($parts) == 1) { // no label found, use category label
						$parts[] = $showmode?'':$wgContLang->getNSText(NS_CATEGORY);
					}
				} else { // print property or check category
					$title = Title::newFromText(trim($propparts[0]), SMW_NS_PROPERTY); // trim needed for \n
					if ($title === NULL) {
						continue;
					}
					
					if ($title->getNamespace() == SMW_NS_PROPERTY) {
						$printmode = SMWPrintRequest::PRINT_PROP;
						$property = SMWPropertyValue::makeProperty($title->getDBKey());
						$data = $property;
						$label = $showmode?'':$property->getWikiValue();
					} elseif ($title->getNamespace() == NS_CATEGORY) {
						$printmode = SMWPrintRequest::PRINT_CCAT;
						$data = $title;
						$label = $showmode?'':$title->getText();
					} elseif ($title->getNamespace() == NS_MAIN) {
                        $printmode = SMWPrintRequest::PRINT_THIS;
                        $data = $title;
                        $label = $showmode?'':$title->getText();
                    }//else?
					if (count($parts) > 1) { // no label found, use property/category name
						$label = trim($parts[1]);
					}
				}
				if (count($propparts) == 1) { // no outputformat found, leave empty
					$propparts[] = false;
				} elseif ( trim( $propparts[1] ) == '' ) { // "plain printout", avoid empty string to avoid confusions with "false"
                    $propparts[1] = '-';
                }
			if (count($parts) > 1) { // label found, use this instead of default
                    $label = trim($parts[1]);
                }
				$printouts[] = new SMWPrintRequest($printmode, $label, $data, trim($propparts[1]));
			} else if($param{0} == '#') { //param of the tabular forms result printer 
				$param = explode('=', $param, 2);
				if(count($param) == 1) $param[1] = '';
				$params[trim($param[0])] = trim($param[1]);
			} else { // parameter or query
				// FIX:KK special handling for SPARQL queries here
				if (TSHelper::isSPARQL($param)) {
					$querystring .= $param;
				} else {
					$parts = explode('=',$param,2);
					$knownOption = in_array($parts[0], array('merge','template', 'mainlabel', 'sort', 'order', 'default', 'format', 'offset', 'limit', 'headers', 'link', 'intro', 'searchlabel', 'enable add', 'enable delete', 'use silent annotations template', 'write protected annotations', 'instance name preload value', 'subformat', 'update frequency'));
					$probablyOption = preg_match('/^\s*[\w-]+\s*$/', $parts[0]) > 0 && strlen($parts[0]) < 20; // probably an option if alphanumeric and less than 20 chars.
					if (count($parts) == 2 && ($knownOption || $probablyOption)) {
						$params[strtolower(trim($parts[0]))] = $parts[1]; // don't trim here, some params care for " "
					} else {
						$querystring .= $param;
					}
				}
			}
		}
		$querystring = str_replace(array('&lt;','&gt;'), array('<','>'), $querystring);
		if ($showmode) $querystring = "[[:$querystring]]";
	}


	static public function getResultFromFunctionParams(array $rawparams, $outputmode, $context = SMWQueryProcessor::INLINE_QUERY, $showmode = false) {
		SMWSPARQLQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts,$showmode);

		return SMWSPARQLQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_WIKI, $context);
	}

	static public function getResultFromQueryString($querystring, array $params, $extraprintouts, $outputmode, $context = SMWQueryProcessor::INLINE_QUERY) {

		$format = SMWQueryProcessor::getResultFormat($params);
		$query  = SMWSPARQLQueryProcessor::createQuery($querystring, $params, $context, $format, $extraprintouts);
		$result = SMWQueryProcessor::getResultFromQuery($query, $params, $extraprintouts, $outputmode, $context, $format);

		return $result;
	}

	static public function createQuery($querystring, array $params, $context = SMWQueryProcessor::INLINE_QUERY, $format = '', $extraprintouts = array()) {
		global $smwgQDefaultNamespaces, $smwgQFeatures, $smwgQConceptFeatures;
		
		// check anomaly: happens when | is first character
		if (substr(trim($querystring),0,2) == '||') {
			$querystring = "";
		}
		// parse query:
		if ($context == SMWQueryProcessor::CONCEPT_DESC) {
			$queryfeatures = $smwgQConceptFeatures;
		} else {
			$queryfeatures = $smwgQFeatures;
		}
		$qp = new SMWSPARQLQueryParser($queryfeatures);
		$qp->setDefaultNamespaces($smwgQDefaultNamespaces);
		$desc = $qp->getQueryDescription($querystring);

		if ($format == '') {
			$format = SMWQueryProcessor::getResultFormat($params);
		}
		if ($format == 'count') {
			$querymode = SMWQuery::MODE_COUNT;
		} elseif ($format == 'debug') {
			$querymode = SMWQuery::MODE_DEBUG;
		} elseif (in_array($format, array('rss','icalendar','vcard','csv'))) {
			$querymode = SMWQuery::MODE_NONE;
		} else {
			$querymode = SMWQuery::MODE_INSTANCES;
		}

		if (array_key_exists('mainlabel', $params)) {
			$mainlabel = $params['mainlabel'];
		} else {
			$mainlabel = '';
		}
			
		if ( ($querymode == SMWQuery::MODE_NONE) ||
		( ( !$desc->isSingleton() ||
		(count($desc->getPrintRequests()) + count($extraprintouts) == 0)
		) && ($mainlabel != '-')
		)
		) {
           
			$desc->prependPrintRequest(new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, $mainlabel));
		}
        
				
		$query = new SMWSPARQLQuery($desc, true);
		$query->params = $params; 
		$query->fromASK = strpos($querystring, 'SELECT') === false;
		$query->mainLabelMissing = $mainlabel == '-';
		$query->setQueryString($querystring);
		$query->setExtraPrintouts($extraprintouts);
		$query->addErrors($qp->getErrors());

		// set query parameters:
		$query->querymode = $querymode;
		if ( (array_key_exists('offset',$params)) && (is_int($params['offset'] + 0)) ) {
			$query->setOffset(max(0,trim($params['offset']) + 0));
		}
		if ($query->querymode == SMWQuery::MODE_COUNT) { // largest possible limit for "count", even inline
			global $smwgQMaxLimit;
			$query->setOffset(0);
			$query->setLimit($smwgQMaxLimit, false);
		} else {
			if ( (array_key_exists('limit',$params)) && (is_int(trim($params['limit']) + 0)) ) {
				$query->setLimit(max(0,trim($params['limit']) + 0));
				if ( (trim($params['limit']) + 0) < 0 ) { // limit < 0: always show further results link only
					$query->querymode = SMWQuery::MODE_NONE;
				}
			} else {
				global $smwgQDefaultLimit;
				$query->setLimit($smwgQDefaultLimit);
			}
		}
		// determine sortkeys and ascendings:
		if ( array_key_exists('order', $params) ) {
			$orders = explode( ',', $params['order'] );
			foreach ($orders as $key => $order) { // normalise
				$order = strtolower(trim($order));
				if ( ('descending' == $order) || ('reverse' == $order) || ('desc' == $order) ) {
					$orders[$key] = 'DESC';
				} elseif ( ('random' == $order) || ('rand' == $order) ) {
					$orders[$key] = 'RAND()';
				} else {
					$orders[$key] = 'ASC';
				}
			}
		} else {
			$orders = Array();
		}
		reset($orders);
		if ( array_key_exists('sort', $params) ) {
			$query->sort = true;
			$query->sortkeys = Array();
			foreach ( explode( ',', trim($params['sort']) ) as $sort ) {
				$sort = smwfNormalTitleDBKey( trim($sort) ); // slight normalisation
				$order = current($orders);
				if ($order === false) { // default
					$order = 'ASC';
				}
				if (array_key_exists($sort, $query->sortkeys) ) {
					// maybe throw an error here?
				} else {
					$query->sortkeys[$sort] = $order;
				}
				next($orders);
			}
			if (current($orders) !== false) { // sort key remaining, apply to page name
				$query->sortkeys[''] = current($orders);
			}
		} elseif ($format == 'rss') { // unsorted RSS: use *descending* default order
			///TODO: the default sort field should be "modification date" (now it is the title, but
			///likely to be overwritten by printouts with label "date").
			$query->sortkeys[''] = (current($orders) != false)?current($orders):'DESC';
		} else { // sort by page title (main column) by default
			$query->sortkeys[''] = (current($orders) != false)?current($orders):'ASC';
		} // TODO: check and report if there are further order statements?

		return $query;
	}


}
