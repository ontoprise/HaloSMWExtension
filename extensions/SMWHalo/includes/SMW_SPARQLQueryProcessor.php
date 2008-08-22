<?php
class SMWSPARQLQueryProcessor extends SMWQueryProcessor {
	
/**
     * Preprocess a query as given by an array of parameters as is  typically
     * produced by the #ask parser function. The parsing results in a querystring,
     * an array of additional parameters, and an array of additional SMWPrintRequest
     * objects, which are filled into call-by-ref parameters.
     * $showmode is true if the input should be treated as if given by #show
     */
    static public function processFunctionParams($rawparams, &$querystring, &$params, &$printouts, $showmode=false) {
        global $wgContLang;
        $querystring = '';
        $printouts = array();
        $params = array();
        foreach ($rawparams as $name => $param) {
            if ( is_string($name) && ($name != '') ) { // accept 'name' => 'value' just as '' => 'name=value'
                $param = $name . '=' . $param;
            }
            if ($param == '') {
            } elseif ($param{0} == '?') { // print statement
                $param = substr($param,1);
                $parts = explode('=',$param,2);
                $propparts = explode('#',$parts[0],2);
                if (trim($propparts[0]) == '') { // print "this"
                    $printmode = SMWPrintRequest::PRINT_THIS;
                    if (count($parts) == 1) { // no label found, use empty label
                        $parts[] = '';
                    }
                    $title = NULL;
                } elseif ($wgContLang->getNsText(NS_CATEGORY) == ucfirst(trim($propparts[0]))) { // print category
                    $title = NULL;
                    $printmode = SMWPrintRequest::PRINT_CATS;
                    if (count($parts) == 1) { // no label found, use category label
                        $parts[] = $showmode?'':$wgContLang->getNSText(NS_CATEGORY);
                    }
                } else { // print property or check category
                    $title = Title::newFromText(trim($propparts[0]), SMW_NS_PROPERTY); // trim needed for \n
                    if ($title === NULL) { // too bad, this is no legal property name, ignore
                        continue;
                    }
                    if ($title->getNamespace() == SMW_NS_PROPERTY) {
                        $printmode = SMWPrintRequest::PRINT_PROP;
                    } elseif ($title->getNamespace() == NS_CATEGORY) {
                        $printmode = SMWPrintRequest::PRINT_CCAT;
                    } //else?
                    if (count($parts) == 1) { // no label found, use property/category name
                        $parts[] = $showmode?'':$title->getText();
                    }
                }
                if (count($propparts) == 1) { // no outputformat found, leave empty
                    $propparts[] = '';
                }
                $printouts[] = new SMWPrintRequest($printmode, trim($parts[1]), $title, trim($propparts[1]));
            } else { // parameter or query
                // FIX:KK special handling for SPARQL queries here 
                if (strpos($param, "SELECT ") !== false) {
                    $querystring .= $param;
                } else {
                    $parts = explode('=',$param,2);
                    if (count($parts) >= 2) {
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
    
    static public function getResultFromFunctionParams($rawparams, $outputmode, $context = SMWQueryProcessor::INLINE_QUERY, $showmode = false) {
        SMWSPARQLQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts,$showmode);
        return SMWSPARQLQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_WIKI, $context);
    }
    static public function getResultFromQueryString($querystring, $params, $extraprintouts, $outputmode, $context = SMWQueryProcessor::INLINE_QUERY) {
        wfProfileIn('SMWQueryProcessor::getResultFromQueryString (SMW)');
        $format = SMWQueryProcessor::getResultFormat($params);
        $query  = SMWSPARQLQueryProcessor::createQuery($querystring, $params, $context, $format, $extraprintouts);
        $result = SMWQueryProcessor::getResultFromQuery($query, $params, $extraprintouts, $outputmode, $context, $format);
        wfProfileOut('SMWQueryProcessor::getResultFromQueryString (SMW)');
        return $result;
    }
    
    static public function createQuery($querystring, $params, $context = SMWQueryProcessor::INLINE_QUERY, $format = '', $extraprintouts = array()) {
        global $smwgQDefaultNamespaces, $smwgQFeatures, $smwgQConceptFeatures;

        // parse query:
        if ($context == SMWQueryProcessor::CONCEPT_DESC) {
            $queryfeatures = $smwgQConceptFeatures;
        } else {
            $queryfeatures = $smwgQFeatures;
        }
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
       // parse query:
         $qp = new SMWSPARQLQueryParser();
	    $qp->setDefaultNamespaces($smwgQDefaultNamespaces);
	    $desc = $qp->getQueryDescription($querystring);
	
	    if (array_key_exists('mainlabel', $params)) {
	        $mainlabel = $params['mainlabel'] . $qp->getLabel();
	    } else {
	        $mainlabel = $qp->getLabel();
	    }
	    if ( ( !$desc->isSingleton() || (count($desc->getPrintRequests()) + count($extraprintouts) == 0) ) && ($mainlabel != '-') ) {
	        $desc->prependPrintRequest(new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, $mainlabel));
	    }
	
	    $query = new SMWSPARQLQuery($desc, true);
	    $query->fromASK = strpos($querystring, 'SELECT') === false;
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
                if ( ('descending' != $order) && ('reverse' != $order) && ('desc' != $order) ) {
                    $orders[$key] = 'ASC';
                } else {
                    $orders[$key] = 'DESC';
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
        } else { // sort by page title (main column) by default
            $query->sortkeys[''] = (current($orders) != false)?current($orders):'ASC';
        } // TODO: check and report if there are further order statements?

        return $query;
    }
}
?>