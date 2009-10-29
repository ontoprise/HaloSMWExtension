<?php

/**
 * Derived version of SMWQueryResult to provide user-defined link
 * to Special:Ask page. Necessary for other storages. 
 *
 */
class SMWHaloQueryResult extends SMWQueryResult {
	
	public function SMWHaloQueryResult($printrequests, $query, $furtherres=false) {
		parent::__construct($printrequests, $query, $furtherres);
	}

	public function getQueryLink($caption = false) {
		
		$params = array(trim($this->m_querystring));
		foreach ($this->m_extraprintouts as $printout) {
			$params[] = $printout->getSerialisation();
		}
		if ( count($this->m_query->sortkeys)>0 ) {
			$psort  = '';
			$porder = '';
			$first = true;
			foreach ( $this->m_query->sortkeys as $sortkey => $order ) {
				if ( $first ) {
					$first = false;
				} else {
					$psort  .= ',';
					$porder .= ',';
				}
				$psort .= $sortkey;
				$porder .= $order;
			}
			if (($psort != '')||($porder != 'ASC')) { // do not mention default sort (main column, ascending)
				$params['sort'] = $psort;
				$params['order'] = $porder;
			}
		}
		if ($caption == false) {
			wfLoadExtensionMessages('SemanticMediaWiki');
			$caption = ' ' . wfMsgForContent('smw_iq_moreresults'); // the space is right here, not in the QPs!
		}
		$askPage = $this->m_query instanceof SMWSPARQLQuery ? "AskTSC" : "Ask";
		$result = SMWInfolink::newInternalLink($caption,':Special:'.$askPage, false, $params);
		// Note: the initial : prevents SMW from reparsing :: in the query string
		return $result;
	}
}
?>