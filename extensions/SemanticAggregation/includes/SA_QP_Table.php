<?php
/**
 * Print aggregate query results in tables.
 * @author Ning Hu
 * @file
 * @ingroup SMWQuery
 */

/**
 * New implementation of SMW's printer for result tables.
 *
 * @ingroup SMWQuery
 */
class SMWAggregateTableResultPrinter extends SMWAggregateResultPrinter {

	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber;
		SMWOutputs::requireHeadItem(SMW_HEADER_SORTTABLE);

		// print header
		if ('broadtable' == $this->mFormat)
			$widthpara = ' width="100%"';
		else $widthpara = '';
		$result = "<table class=\"smwtable\"$widthpara id=\"querytable" . $smwgIQRunningNumber . "\">\n";
		
		$act_column = 0;
		if ($this->mShowHeaders) { // building headers
			$result .= "\t<tr>\n";
			foreach ($res->getPrintRequests() as $pr) {
				if($this->m_aggregates[$act_column]->show())
					$result .= "\t\t<th>" . $pr->getText($outputmode, $this->mLinker) . "</th>\n";
				$act_column ++;
			}
			$result .= "\t</tr>\n";
		}

		$result_rows = "";
		// print all result rows
		while ( $row = $res->getNext() ) {
			$result_rows .= "\t<tr>\n";
			$firstcol = true;
			$act_column = 0;
			foreach ($row as $field) {
				if($this->m_aggregates[$act_column]->show())
					$result_rows .= "\t\t<td>";
				$first = true;
				while ( ($object = $field->getNextObject()) !== false ) {
					if ($object->getTypeID() == '_wpg') { // use shorter "LongText" for wikipage
						$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
					} else {
						$text = $object->getShortText($outputmode,$this->getLinker($firstcol));
					}
					if ($first) {
						if ($object->isNumeric()) { // use numeric sortkey
							if($this->m_aggregates[$act_column]->show())
								$result_rows .= '<span class="smwsortkey">' . $object->getWikiValue() . '</span>';
						}
						$first = false;
					} else {
						if($this->m_aggregates[$act_column]->show())
							$result_rows .= '<br />';
					}
					if($this->m_aggregates[$act_column]->show())
						$result_rows .= $text;
					// haven't think of nary, tbd
					if($this->m_hasAggregate) {
						$this->m_aggregates[$act_column]->appendValue($object);
						if($this->m_aggregates[$act_column]->isEmbedded())
							$embedAggregateRow = true;
						else
							$outAggregate = true;
					}
				}
				if($this->m_aggregates[$act_column]->show())
					$result_rows .= "</td>\n";
				$firstcol = false;
				$act_column ++;
			}
			$result_rows .= "\t</tr>\n";
		}

		if(strtolower($this->m_params['agg_pos']) != 'top') {
			$result .= $result_rows;
			if($embedAggregateRow) {
				$result .= "\t<tr class=\"smwfooter\">\n";
				foreach ($this->m_aggregates as $aggregate) {
					if($aggregate->show()) {
						$result .= "<td class=\"sortbottom\">";
						if($aggregate->isEmbedded())
							$result .= $aggregate->getResultPrefix($outputmode) . $aggregate->getResult($outputmode);
						$result .= "</td>\n";
					}
				}
				$result .= "\t</tr>\n";
			}
		} else {
			if($embedAggregateRow) {
				$result .= "\t<tr class=\"smwheader\">\n";
				foreach ($this->m_aggregates as $aggregate) {
					if($aggregate->show()) {
						$result .= "<td class=\"sortbottom\">";
						if($aggregate->isEmbedded())
							$result .= $aggregate->getResultPrefix($outputmode) . $aggregate->getResult($outputmode);
						$result .= "</td>\n";
					}
				}
				$result .= "\t</tr>\n";
			}
			$result .= $result_rows;
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

		if($outAggregate) {
			$result .= "<b>Aggregations</b><br/>\n";
			$result .= "<table class=\"smwtable\">\n";
			$act_column = 0;
			foreach ($res->getPrintRequests() as $pr) {
				if((!($this->m_aggregates[$act_column] instanceof SMWFakeQueryAggregate)) && (!$this->m_aggregates[$act_column]->isEmbedded()))
					$result .= "<tr><td>" . $pr->getText($outputmode, $this->mLinker) . "</td><td>". $this->m_aggregates[$act_column]->getResultPrefix($outputmode) . $this->m_aggregates[$act_column]->getResult($outputmode) . "</td></tr>\n";
				$act_column ++;
			}
			$result .= "</table>\n";
		}
		
		$this->isHTML = ($outputmode == SMW_OUTPUT_HTML); // yes, our code can be viewed as HTML if requested, no more parsing needed
		return $result;
	}

}
