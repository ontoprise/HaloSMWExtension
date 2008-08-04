<?php
/**
 * Print query results in tables.
 * @author Markus Krötzsch
 * @author Ontoprise
 */

/**
 * New implementation of SMW's printer for result tables, extended with
 * certain gardening features supplied by the Halo extension.
 *
 * @note AUTOLOADED
 */
class SMWGardeningTableResultPrinter extends SMWResultPrinter {

	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber;
		smwfRequireHeadItem(SMW_HEADER_SORTTABLE);

		global $smwgHaloIP;
		require_once( $smwgHaloIP . "/specials/SMWGardening/SMW_GardeningIssues.php");
		require_once( $smwgHaloIP . "/specials/SMWGardening/ConsistencyBot/SMW_ConsistencyBot.php");

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
			foreach ($res->getPrintRequests() as $pr) {
				$title = $pr->getTitle();
				if($title instanceof Title)
					array_push($cols, $title);
				else
					array_push($cols, "");
				$result .= "\t\t<th>" . $pr->getText($outputmode, $this->mLinker) . "</th>\n";
			}
			$result .= "\t</tr>\n";
		} else {
			foreach ($res->getPrintRequests() as $pr) {
				$title = $pr->getTitle();
				if($title instanceof Title)
					array_push($cols, $title);
				else
					array_push($cols, "");
			}
		}

		// print all result rows
		while ( $row = $res->getNext() ) {
			$result .= "\t<tr>\n";
			$firstcol = true;
			$gIssues = null;
			$act_column = 0;
			foreach ($row as $field) {
				$result .= "\t\t<td>";
				$first = true;
				$tt = '';
				if($gIssues != null && $outputmode == SMW_OUTPUT_HTML){
					for($j = 0; $j<sizeof($gIssues); $j++){ //check if there's a GI for this property / article combination
						if($act_column > 0 && $cols[$act_column] instanceof Title && ($gIssues[$j]->getTitle2()->getArticleID() == $cols[$act_column]->getArticleID())){
							if($gIssues[$j]->getType() == SMW_GARDISSUE_TOO_LOW_CARD)
								$tt = '<a title="' . wfMsg("qbedit") . ' ' . $gIssues[$j]->getTitle1()->getText() . '" class="gardeningissue" href="' . $gIssues[$j]->getTitle1()->getEditURL() . '" target="_new">' . wfMsg('smw_iqgi_missing') . '</a>';
							else if($gIssues[$j]->getType() == SMW_GARDISSUE_WRONG_UNIT)
								$tt = '&nbsp;<a title="' . wfMsg("qbedit") . ' ' . $gIssues[$j]->getTitle1()->getText() . '" class="gardeningissue_notify" href="' . $gIssues[$j]->getTitle1()->getEditURL() . '" target="_new">(' . wfMsg('smw_iqgi_wrongunit') . ')</a>';
							else
								$tt = smwfEncodeMessages(array($gIssues[$j]->getRepresentation()));
						}
					}
				}
				while ( ($object = $field->getNextObject()) !== false ) {
					if($firstcol){ //save gardening issues for the article of the current row
						$gIssues = $gi_store->getGardeningIssues("smw_consistencybot", NULL, SMW_CONSISTENCY_BOT_BASE + 3, $object->getTitle(), NULL, NULL);
					}
					if ($object->getTypeID() == '_wpg') { // use shorter "LongText" for wikipage
						$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
					} else {
						$text = $object->getShortText($outputmode,$this->getLinker($firstcol));
					}
					if ($first) {
						if ($object->isNumeric()) { // use numeric sortkey
							$result .= '<span class="smwsortkey">' . $object->getNumericValue() . '</span>';
						}
						$first = false;
					} else {
						$result .= '<br />';
					}
					if ($object instanceof SMWWikiPageValue){
						$text .= $this->addTooltip($object->getTitle());
					}
					$result .= $text;
				}
				$result .= $tt;
				$result .= "</td>\n";
				$firstcol = false;
				$act_column ++;
			}
			$result .= "\t</tr>\n";
		}

		// print further results footer
		if ( $this->mInline && $res->hasFurtherResults() && $this->mSearchlabel !== '') {
			$link = $res->getQueryLink();
			if ($this->mSearchlabel) {
				$link->setCaption($this->mSearchlabel);
			}
			$result .= "\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> ' . $link->getText($outputmode,$this->getLinker()) . "</td></tr>\n";
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
