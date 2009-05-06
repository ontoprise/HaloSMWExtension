<?php

function smwfDoSpecialUSSearch() {
	global $wgOut;
	wfProfileIn('smwfDoSpecialUSSearch (SMW)');
	list( $limit, $offset ) = wfCheckLimits();
	$rep = new UnifiedSearchStatistics();
	$result = $rep->doQuery( $offset, $limit );

	wfProfileOut('smwfDoSpecialUSSearch (SMW)');
	return $result;
}

/**
 * UnifiedSearchStatistics displays statistical information about
 * search matches and tries.
 *
 * @author: Kai Kühn
 *
 */
class UnifiedSearchStatistics extends QueryPage {
	
	
	function getName() {
		return "UnifiedSearchStatistics";
	}
	function getPageHeader() {
		$html = '<p>' . wfMsg('us_statistics_docu') . "</p><br />\n";
		$specialAttPage = Title::newFromText("UnifiedSearchStatistics", NS_SPECIAL);
		global $wgRequest;
		$sort = $wgRequest->getVal("sort") == NULL ? 0 : $wgRequest->getVal("sort") + 0;
		$type = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;

		$sortOptions = array(wfMsg('us_search_asc'), wfMsg('us_search_desc'));
		$typeOptions = array(wfMsg('us_search_hits'), wfMsg('us_search_tries'));

		$html .= "<form action=\"".$specialAttPage->getFullURL()."\">";
		$html .= '<input type="hidden" name="title" value="' . $specialAttPage->getPrefixedText() . '"/>';
		// type of property
		$html .= "<table><tr><td style=\"text-align: right\">".wfMsg('us_sort_for')."</td><td>";
		$html .=    "<select name=\"type\">";
		$i = 0;
		foreach($typeOptions as $option) {
			if ($i == $type) {
				$html .= "<option value=\"$i\" selected=\"selected\">$option</option>";
			} else {
				$html .= "<option value=\"$i\">$option</option>";
			}
			$i++;
		}
		$html .=    "</select>";
		$html .= "</td><td></td><tr><td style=\"text-align: right\">".wfMsg('us_order_for')."</td>";
        $html .= "<td>";
		// sort options
		$html .=    "<select name=\"sort\">";
		$i = 0;
		foreach($sortOptions as $option) {
			if ($i == $sort) {
				$html .= "<option value=\"$i\" selected=\"selected\">$option</option>";
			} else {
				$html .= "<option value=\"$i\">$option</option>";
			}
			$i++;
		}
		$html .=    "</select>";
        $html .= "</td><td><input type=\"submit\" value=\" ".wfMsg('us_go_button')." \"></td></tr></table>";
		
		$html .= "</form>";
		return $html;
	}
	function isExpensive() {
		return false; /// disables caching for now
	}

	function isSyndicated() {
		return false; ///TODO: why not?
	}
	/**
	 * Implemented by subclasses to provide concrete functions.
	 */
	function getResults($limit, $offset, $minMax, $sortFor) {
		return USStore::getStore()->getSearchTries($limit, $offset, $minMax, $sortFor);
	}

	function openList($offset) {
		return "<div id=\"us_statistics_results\"><table border=\"1\" cellspacing=\"0\"><tr><th style=\"background-color: #CCC;\">".wfMsg('us_search_term')."</th><th>".wfMsg('us_search_tries')."</th><th>".wfMsg('us_search_hits')."</th></tr>";
	}

	function closeList() {
		return "</table></div>";
	}
	function formatResult($skin, $r) {
		list($searchterm, $tries, $hits) = $r;
		return "<tr><td>$searchterm</td><td>$tries</td><td>$hits</td></tr>";
		
	}
	

	/**
	 * Clear the cache and save new results
	 * @todo Implement caching for SMW query pages
	 */
	function recache( $limit, $ignoreErrors = true ) {
		///TODO
	}

	/**
	 * This is the actual workhorse. It does everything needed to make a
	 * real, honest-to-gosh query page.
	 * Alas, we need to overwrite the whole beast since we do not assume
	 * an SQL-based storage backend.
	 *
	 * @param $offset database query offset
	 * @param $limit database query limit
	 * @param $shownavigation show navigation like "next 200"?
	 */
	function doQuery( $offset, $limit, $shownavigation=true ) {
		global $wgUser, $wgOut, $wgLang, $wgContLang, $wgRequest;

		$minMax = $wgRequest->getVal("sort") == NULL ? 0 : $wgRequest->getVal("sort") + 0;
		$sortFor = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;
		$res = $this->getResults($limit, $offset, $minMax, $sortFor);
		$num = count($res);

		$sk = $wgUser->getSkin();
		$sname = $this->getName();

		if($shownavigation) {
			$wgOut->addHTML( $this->getPageHeader() );

			// if list is empty, show it
			if( $num == 0 ) {
				wfLoadExtensionMessages('SemanticMediaWiki');
				$wgOut->addHTML( '<p>' . wfMsgHTML('specialpage-empty') . '</p>' );
				return;
			}

			$top = wfShowingResults( $offset, $num);
			$wgOut->addHTML( "<p>{$top}\n" );

			// often disable 'next' link when we reach the end
			$atend = $num < $limit;

			$sl = wfViewPrevNext( $offset, $limit ,
			$wgContLang->specialPage( $sname ),
			wfArrayToCGI( $this->linkParameters() ), $atend );
			$wgOut->addHTML( "<br />{$sl}</p>\n" );
		}
		if ( $num > 0 ) {
			$s = array();
			if ( ! $this->listoutput )
			$s[] = $this->openList( $offset );

			foreach ($res as $r) {
				$format = $this->formatResult( $sk, $r );
				if ( $format ) {
					$s[] = $format;
				}
			}

			if ( ! $this->listoutput )
			$s[] = $this->closeList();
			$str = $this->listoutput ? $wgContLang->listToText( $s ) : implode( '', $s );
			$wgOut->addHTML( $str );
		}
		if($shownavigation) {
			$wgOut->addHTML( "<p>{$sl}</p>\n" );
		}
		return $num;
	}

}

?>