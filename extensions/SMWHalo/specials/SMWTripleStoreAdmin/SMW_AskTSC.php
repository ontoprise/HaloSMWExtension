<?php
/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloTriplestore
 *
 * @author Kai Kühn
 *
 * This special page for MediaWiki implements a customisable form for
 * executing queries to TSC outside of articles.
 *
 */
class SMWAskTSCPage extends SpecialPage {

	protected $m_querystring = '';
    protected $m_params = array();
    protected $m_printouts = array();
    protected $m_editquery = false;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('AskTSC');
		wfLoadExtensionMessages('SemanticMediaWiki');
	}

	function execute( $p ) {
		global $wgOut, $wgRequest, $smwgQEnabled, $smwgRSSEnabled, $smwgMW_1_14;
		wfProfileIn('doSpecialAskTSC (SMWHalo)');

		$this->extractQueryParameters($p);
        
		$format = $this->getResultFormat($this->m_params);
		$query  = SMWSPARQLQueryProcessor::createQuery($this->m_querystring, $this->m_params, SMWQueryProcessor::SPECIAL_PAGE, $format, $this->m_printouts);
		$res = smwfGetStore()->getQueryResult($query);
		$printer = SMWQueryProcessor::getResultPrinter($format, SMWQueryProcessor::SPECIAL_PAGE, $res);

		$result_mime = $printer->getMimeType($res);
				
		if ($result_mime == false) {
			if ($res->getCount() > 0) {
			
				$result = '<div style="text-align: center;">';
				$result .= '</div>' . $printer->getResult($res, $this->m_params,SMW_OUTPUT_HTML);
				$result .= '<div style="text-align: center;"></div>';
			} else {
				$result = '<div style="text-align: center;">' . wfMsg('smw_result_noresults') . '</div>';
			}
		} else { // make a stand-alone file

			$result = $printer->getResult($res, $this->m_params,SMW_OUTPUT_FILE);
			$result_name = $printer->getFileName($res); // only fetch that after initialising the parameters
		}
		if ($result_mime == false) {
			if ($this->m_querystring) {
				$wgOut->setHTMLtitle($this->m_querystring);
			} else {
				$wgOut->setHTMLtitle(wfMsg('ask'));
			}
			
			$wgOut->addHTML($result);
		} else {
			$wgOut->disable();
			header( "Content-type: $result_mime; charset=UTF-8" );
			if ($result_name !== false) {
				header( "Content-Disposition: attachment; filename=$result_name");
			}
			print $result;
		}

		wfProfileOut('doSpecialAskTSC (SMWHalo)');
	}

	protected function getResultFormat($params) {
		$format = 'auto';
		if (array_key_exists('format', $params)) {
			$format = strtolower(trim($params['format']));
			global $smwgResultFormats;
			if ( !array_key_exists($format, $smwgResultFormats) ) {
				$format = 'auto'; // If it is an unknown format, defaults to list/table again
			}
		}
		return $format;
	}

	protected function extractQueryParameters($p) {
		// This code rather hacky since there are many ways to call that special page, the most involved of
		// which is the way that this page calls itself when data is submitted via the form (since the shape
		// of the parameters then is governed by the UI structure, as opposed to being governed by reason).
		global $wgRequest, $smwgQMaxInlineLimit;

		// First make all inputs into a simple parameter list that can again be parsed into components later.

		if ($wgRequest->getCheck('q')) { // called by own Special, ignore full param string in that case
			$rawparams = SMWInfolink::decodeParameters($wgRequest->getVal( 'p' ), false); // p is used for any additional parameters in certain links
		} else { // called from wiki, get all parameters
			$rawparams = SMWInfolink::decodeParameters($p, true);
		}
		// Check for q= query string, used whenever this special page calls itself (via submit or plain link):
		$this->m_querystring = $wgRequest->getText( 'q' );
		if ($this->m_querystring != '') {
			$rawparams[] = $this->m_querystring;
		}
		// Check for param strings in po (printouts), appears in some links and in submits:
		$paramstring = $wgRequest->getText( 'po' );
		if ($paramstring != '') { // parameters from HTML input fields
			$ps = explode("\n", $paramstring); // params separated by newlines here (compatible with text-input for printouts)
			foreach ($ps as $param) { // add initial ? if omitted (all params considered as printouts)
				$param = trim($param);
				if ( ($param != '') && ($param{0} != '?') ) {
					$param = '?' . $param;
				}
				$rawparams[] = $param;
			}
		}
		
		// Now parse parameters and rebuilt the param strings for URLs
        SMWSPARQLQueryProcessor::processFunctionParams($rawparams,$this->m_querystring,$this->m_params,$this->m_printouts);
        // Try to complete undefined parameter values from dedicated URL params
        if ( !array_key_exists('format',$this->m_params) ) {
            if (array_key_exists('rss', $this->m_params)) { // backwards compatibility (SMW<=1.1 used this)
                $this->m_params['format'] = 'rss';
            } else { // default
                $this->m_params['format'] = 'broadtable';
            }
        }
        $sortcount = $wgRequest->getVal( 'sc' );
        if (!is_numeric($sortcount)) {
            $sortcount = 0;
        }
        
        // commented out because TSC does not accept empty sort/order parameters
	   /*if ( !array_key_exists('order',$this->m_params) ) {
            $this->m_params['order'] = $wgRequest->getVal( 'order' ); // basic ordering parameter (, separated)
            for ($i=0; $i<$sortcount; $i++) {
                if ($this->m_params['order'] != '') {
                    $this->m_params['order'] .= ',';
                }
                $value = $wgRequest->getVal( 'order' . $i );
                $value = ($value == '')?'ASC':$value;
                $this->m_params['order'] .= $value;
            }
        }
        if ( !array_key_exists('sort',$this->m_params) ) {
            $this->m_params['sort'] = $wgRequest->getText( 'sort' ); // basic sorting parameter (, separated)
            for ($i=0; $i<$sortcount; $i++) {
                if ( ($this->m_params['sort'] != '') || ($i>0) ) { // admit empty sort strings here
                    $this->m_params['sort'] .= ',';
                }
                $this->m_params['sort'] .= $wgRequest->getText( 'sort' . $i );
            }
        }*/
       
        // Find implicit ordering for RSS -- needed for downwards compatibility with SMW <=1.1
        if ( ($this->m_params['format'] == 'rss') && ($this->m_params['sort'] == '') && ($sortcount==0)) {
            foreach ($this->m_printouts as $printout) {
                if ((strtolower($printout->getLabel()) == "date") && ($printout->getTypeID() == "_dat")) {
                    $this->m_params['sort'] = $printout->getTitle()->getText();
                    $this->m_params['order'] = 'DESC';
                }
            }
        }
        if ( !array_key_exists('offset',$this->m_params) ) {
            $this->m_params['offset'] = $wgRequest->getVal( 'offset' );
            if ($this->m_params['offset'] == '')  $this->m_params['offset'] = 0;
        }
        if ( !array_key_exists('limit',$this->m_params) ) {
            $this->m_params['limit'] = $wgRequest->getVal( 'limit' );
            if ($this->m_params['limit'] == '') {
                 $this->m_params['limit'] = ($this->m_params['format'] == 'rss')?10:20; // standard limit for RSS
            }
        }
        $this->m_params['limit'] = min($this->m_params['limit'], $smwgQMaxInlineLimit);
        
        if ( !array_key_exists('merge',$this->m_params) ) {
            $this->m_params['merge'] = $wgRequest->getVal( 'merge' );
            if ($this->m_params['merge'] == '') {
                $this->m_params['merge'] = true; // merge per default
            }
         }
        
        $this->m_editquery = ( $wgRequest->getVal( 'eq' ) != '' ) || ('' == $this->m_querystring );
	}

}

