<?php
/**
 * Print query results in tables.
 * @author Markus Kr�tzsch
 * @author Markus Nitsche (Halo modifications)
 */

/**
 * New implementation of SMW's printer for result tables.
 */
class SMWHaloTableResultPrinter extends SMWResultPrinter {
	
	protected function getHTML($res) {
		global $smwgIQRunningNumber;
		smwfRequireHeadItem(SMW_HEADER_SORTTABLE);
		
		$cols = array(); //Names of columns
		$gi_store = SMWGardening::getGardeningIssuesAccess();
		
		// print header
		if ('broadtable' == $this->mFormat)
			$widthpara = ' width="100%"';
		else $widthpara = '';
		$result = $this->mIntro .
		          "<table class=\"smwtable\"$widthpara id=\"querytable" . $smwgIQRunningNumber . "\">\n";
		
		if ($this->mShowHeaders) { // building headers
			$result .= "\n\t\t<tr>";
		}
		foreach ($res->getPrintRequests() as $pr) { //get column titles
			$title = $pr->getTitle();
			if($title instanceof Title)
				array_push($cols, $title);
			else
				array_push($cols, "");
				
			if ($this->mShowHeaders) {	
				$result .= "\t\t\t<th>" . $pr->getHTMLText($this->mLinker) . $this->addTooltip($title) . "</th>\n";
			}
		}
		if ($this->mShowHeaders) {
			$result .= "\n\t\t</tr>";
		}
		// print all result rows
		
		while ( $row = $res->getNext() ) { //ROW
			//FOR each row, save first column (article) to check for GIs, then check if col matches
			$result .= "\t\t<tr>\n";
			$firstcol = true;
			$act_column = 0;
			$gIssues = NULL;
			
			foreach ($row as $field) { //FIELDS
				$tt = '';
				if(($firstcol && $this->mLinkFirst) || $this->mLinkOthers){
					$cont = $field->getContent();
					
					if(is_array($cont) && count($cont)>0 && !is_null($cont[0]) && $cont[0] instanceof SMWWikiPageValue){ //for each link, add GI tooltip
						$tt = $this->addTooltip($cont[0]->getTitle());
						if($firstcol) //save gardening issues for the article of the current row
							$gIssues = $gi_store->getGardeningIssues("smw_consistencybot", NULL, SMW_CONSISTENCY_BOT_BASE + 3, $cont[0]->getTitle(), NULL, NULL);
					}
				}

				for($j = 0; $j<sizeof($gIssues); $j++){ //check if there's a GI for this property / article combination
					if($act_column > 0 && ($gIssues[$j]->getTitle2()->getArticleID() == $cols[$act_column]->getArticleID())){
						if($gIssues[$j]->getType() == SMW_GARDISSUE_TOO_LOW_CARD)
							$tt = '<a title="' . wfMsg("qbedit") . ' ' . $gIssues[$j]->getTitle1()->getText() . '" class="gardeningissue" href="' . $gIssues[$j]->getTitle1()->getEditURL() . '" target="_new">' . wfMsg('smw_iqgi_missing') . '</a>';
						else if($gIssues[$j]->getType() == SMW_GARDISSUE_WRONG_UNIT)
							$tt = '<a title="' . wfMsg("qbedit") . ' ' . $gIssues[$j]->getTitle1()->getText() . '" class="gardeningissue_notify" href="' . $gIssues[$j]->getTitle1()->getEditURL() . '" target="_new">' . wfMsg('smw_iqgi_wrongunit') . '</a>';
						else
							$tt = smwfEncodeMessages(array($gIssues[$j]->getRepresentation()));
					}
				}
				
				$result .= "<td>";
				$first = true;
				while ( ($text = $field->getNextHTMLText($this->getLinker($firstcol))) !== false ) {
					if ($first)
						$first = false;
					else $result .= '<br />';
					$result .= $text;
				}
				$result .= " $tt</td>";
				$firstcol = false;
				$act_column++;
			}
			$result .= "\n\t\t</tr>\n";
		}

		// print further results footer
		if ($this->mInline && $res->hasFurtherResults()) {
			$label = $this->mSearchlabel;
			if ($label === NULL) { //apply default
				$label = wfMsgForContent('smw_iq_moreresults');
			}
			if ($label != '') {
				$result .= "\n\t\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> <a href="' . $res->getQueryURL() . '">' . $label . '</a></td></tr>';
			}
		}
		$result .= "\t</table>"; // print footer
		$result .= $this->getErrorString($res); // just append error messages
		return $result;
	}
	
	protected function addTooltip($title){
		$gi_store = SMWGardening::getGardeningIssuesAccess();
		$tt = '';
		if($title instanceof Title){
			$gIssues = $gi_store->getGardeningIssues("smw_consistencybot", NULL, NULL, $title, NULL, NULL);
			$messages = array();
			for($j = 0; $j<sizeof($gIssues); $j++){
				array_push($messages, '<ul><li>' . $gIssues[$j]->getRepresentation() . '</li></ul>');
			}
			$tt = smwfEncodeMessages($messages);
		}
		return $tt;
	}
	
}
