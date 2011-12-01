<?php
/**
 * Print query results in tables.
 * @author Ning
 * @file
 * @ingroup SMWQuery
 */

/**
 * New implementation of SMW's printer for result tables.
 *
 * @ingroup SMWQuery
 */
global $srfpgIP;
include_once($srfpgIP . '/Group/SRF_GroupResultPrinter.php');

class SRFGroupTable extends SRFGroupResultPrinter {

	public function getName() {
		wfLoadExtensionMessages('SemanticMediaWiki');
		return wfMsg('smw_printername_' . $this->mFormat);
	}

	protected function readParameters($params,$outputmode) {
		SRFGroupResultPrinter::readParameters($params,$outputmode);
	}
	
	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber;
		SMWOutputs::requireHeadItem(SMW_HEADER_SORTTABLE);
		
		$result_rows = $this->getGroupResult($res, $outputmode, $headers);

		if($result_rows === NULL) {
			return 'Please verify "group by" parameter in query';
		}
		

		// print header
		if ('group broadtable' == $this->mFormat)
			$widthpara = ' width="100%"';
		else $widthpara = '';
		$result = "<table class=\"smwtable\"$widthpara id=\"querytable" . $smwgIQRunningNumber . "\">\n";
		
		$first_row = true;
		if ($this->mShowHeaders && $first_row) { 
			// building headers
			$result .= "\t<tr>\n";
			foreach ($headers as $h) {
				$result .= "\t\t<th>" . $h . "</th>\n";
			}
			$result .= "\t</tr>\n";
		}

		foreach($result_rows as $key=>$row_data) {
			$result .= "\t<tr>\n";
			$result .= "\t\t<td>$key</td>\n";
			for($j = 1;$j<count($row_data);++$j) {
				$field = $row_data[$j];
				$result .= "\t\t<td>" . $field->getResult($outputmode) . "</td>\n";
			}
			$result .= "\t</tr>\n";
		}

		// print further results footer
		if ( $this->linkFurtherResults($res) ) {
			$link = $res->getQueryLink();
			if ($this->getSearchLabel($outputmode)) {
				$link->setCaption($this->getSearchLabel($outputmode));
			}
			$result .= "\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> ' . $link->getText($outputmode,$this->mLinker) . "</td></tr>\n";
		}
		$result .= "</table>\n"; // print footer

		$this->isHTML = ($outputmode == SMW_OUTPUT_HTML); // yes, our code can be viewed as HTML if requested, no more parsing needed
		return $result;
	}

}
