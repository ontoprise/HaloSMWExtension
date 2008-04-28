<?php
/**
 * Print query results in tables.
 * @author Markus Krötzsch
 */

/**
 * New implementation of SMW's printer for result tables.
 *
 * @note AUTOLOADED
 */
class SMWTableResultPrinter extends SMWResultPrinter {

	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber;
		smwfRequireHeadItem(SMW_HEADER_SORTTABLE);
		
		$cols = array(); //Names of columns
		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();

		// print header
		if ('broadtable' == $this->mFormat)
			$widthpara = ' width="100%"';
		else $widthpara = '';
		$result = $this->mIntro .
		          "<table class=\"smwtable\"$widthpara id=\"querytable" . $smwgIQRunningNumber . "\">\n";
		if ($this->mShowHeaders) { // building headers
			$result .= "\t<tr>\n";
		}
		foreach ($res->getPrintRequests() as $pr) {
			$title = $pr->getTitle();
			if($title instanceof Title)
				array_push($cols, $title);
			else
				array_push($cols, "");
			
			if ($this->mShowHeaders) {
				$result .= "\t\t<th>" . $pr->getText($outputmode, $this->mLinker) . $this->addTooltip($title) . "</th>\n";
			}
		}
		if ($this->mShowHeaders) {
			$result .= "\t</tr>\n";
		}
		// print all result rows
		while ( $row = $res->getNext() ) {
			$result .= "\t<tr>\n";
			$firstcol = true;
			$act_column = 0;
			foreach ($row as $field) {
				$result .= "\t\t<td>";
				$first = true;	
				$gIssues = null;
				$gi_hash = null;
				while ( ($object = $field->getNextObject()) !== false ) {
					if($firstcol){ //save gardening issues for the article of the current row
						$gIssues = $gi_store->getGardeningIssues("smw_consistencybot", NULL, SMW_CONSISTENCY_BOT_BASE + 3, $object->getTitle(), NULL, NULL);
					}
					if ($object->getTypeID() == '_wpg') { // use shorter "LongText" for wikipage
						$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
					} else {
						$text = $object->getShortText($outputmode,$this->getLinker($firstcol));
					}
					$tt = '';
					for($j = 0; $j<sizeof($gIssues); $j++){ //check if there's a GI for this property / article combination
						/*echo "Col: " . $act_column . "\n";
						if($cols[$act_column] instanceof Title){
							echo "GI ID: " . $gIssues[$j]->getTitle2()->getArticleID() . "\n";
							echo "Col ID: " . $cols[$act_column]->getArticleID() . "\n\n";
						}*/
						
						if($act_column > 0 && $cols[$act_column] instanceof Title && ($gIssues[$j]->getTitle2()->getArticleID() == $cols[$act_column]->getArticleID())){
							if($gIssues[$j]->getType() == SMW_GARDISSUE_TOO_LOW_CARD)
								$tt = '<a title="' . wfMsg("qbedit") . ' ' . $gIssues[$j]->getTitle1()->getText() . '" class="gardeningissue" href="' . $gIssues[$j]->getTitle1()->getEditURL() . '" target="_new">' . wfMsg('smw_iqgi_missing') . '</a>';
							else if($gIssues[$j]->getType() == SMW_GARDISSUE_WRONG_UNIT)
								$tt = '<a title="' . wfMsg("qbedit") . ' ' . $gIssues[$j]->getTitle1()->getText() . '" class="gardeningissue_notify" href="' . $gIssues[$j]->getTitle1()->getEditURL() . '" target="_new">' . wfMsg('smw_iqgi_wrongunit') . '</a>';
							else
								$tt = smwfEncodeMessages(array($gIssues[$j]->getRepresentation()));
						}
					}
					$text .= $tt;
					
					if ($object instanceof SMWWikiPageValue){
						$text .= $this->addTooltip($object->getTitle());
					}
					if ($first) {
						if ($object->isNumeric()) { // use numeric sortkey
							$result .= '<span class="smwsortkey">' . $object->getNumericValue() . '</span>';
						}
						$first = false;
					} else {
						$result .= '<br />';
					}
					$result .= $text;
				}
				$result .= "</td>\n";
				$firstcol = false;
				$act_column++;
			}
			$result .= "\t</tr>\n";
		}

		// print further results footer
		if ( $this->mInline && $res->hasFurtherResults() ) {
			$label = $this->mSearchlabel;
			if ($label === NULL) { //apply default
				$label = wfMsgForContent('smw_iq_moreresults');
			}
			if ($label != '') {
				$result .= "\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> ' . $this->getFurtherResultsLink($outputmode,$res,$label) . "</td></tr>\n";
			}
		}
		$result .= "</table>\n"; // print footer
		return $result;
	}
	
	protected function addTooltip($title){
		$tt = '';
		if($title instanceof Title){
			$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
			$gIssues = $gi_store->getGardeningIssues("smw_consistencybot", NULL, NULL, $title, NULL, NULL);
			$messages = array();
			for($j = 0; $j<sizeof($gIssues); $j++){
				if($gIssues[$j]->getRepresentation() != wfMsg('smw_gard_issue_contains_further_problems'))
					array_push($messages, '<ul><li>' . $gIssues[$j]->getRepresentation() . '</li></ul>');
			}
			if(count($messages)>0){
				$messages = array_unique($messages);
				$tt = smwfEncodeMessages($messages);
			}
		}
		return $tt;
	}

}
