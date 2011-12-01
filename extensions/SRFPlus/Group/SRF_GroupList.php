<?php

/**
 * Various mathematical functions - sum, average, min and max.
 *
 * @file
 * @ingroup SemanticResultFormats
 * @author Ning
 */

if (!defined('MEDIAWIKI')) die();

global $srfpgIP;
include_once($srfpgIP . '/Group/SRF_GroupResultPrinter.php');

class SRFGroupList extends SRFGroupResultPrinter {

	protected $mSep = '';
	protected $mTemplate = '';
	protected $mUserParam = '';
	protected $mColumns = 1;
	
	protected function readParameters($params,$outputmode) {
		SRFGroupResultPrinter::readParameters($params,$outputmode);

		if (array_key_exists('sep', $params)) {
			$this->mSep = str_replace('_',' ',$params['sep']);
		}
		if (array_key_exists('template', $params)) {
			$this->mTemplate = trim($params['template']);
		}
		if (array_key_exists('userparam', $params)) {
			$this->mUserParam = trim($params['userparam']);
		}
		if (array_key_exists('columns', $params)) {
			if ( ('group ul' == $this->mFormat) || ('group ol' == $this->mFormat) ) {
				$columns = trim($params['columns']);
				// allow a maximum of 10 columns
				if ($columns > 1 && $columns <= 10)
					$this->mColumns = (int)$columns;
			}
		}
	}

	public function getName() {
		wfLoadExtensionMessages('SemanticMediaWiki');
		return wfMsg('smw_printername_' . $this->mFormat);
	}

	protected function getResultText($res,$outputmode) {
		$result_rows = $this->getGroupResult($res, $outputmode, $headers);

		if($result_rows === NULL) {
			return 'Please verify "group by" parameter in query';
		}

		// Determine mark-up strings used around list items:
		if ( ('group ul' == $this->mFormat) || ('group ol' == $this->mFormat) ) {
			$header = '<' . substr($this->mFormat, 6) . '>';
			$footer = '</' . substr($this->mFormat, 6) . '>';
			$rowstart = '<li>';
			$rowend = '</li>';
			$plainlist = false;
		} else {
			if ($this->mSep != '') {
				$listsep = $this->mSep;
				$finallistsep = $listsep;
			} else {  // default list ", , , and "
				wfLoadExtensionMessages('SemanticMediaWiki');
				$listsep = ', ';
				$finallistsep = wfMsgForContent('smw_finallistconjunct') . ' ';
			}
			$header = '';
			$footer = '';
			$rowstart = '';
			$rowend = '';
			$plainlist = true;
		}
		// Print header
		$result = $header;

		// set up floating divs, if there's more than one column
		if ($this->mColumns > 1) {
			$column_width = floor(100 / $this->mColumns);
			$result .= '<div style="float: left; width: ' . $column_width . '%">' . "\n";
			$rows_per_column = ceil(count($result_rows) / $this->mColumns);
			$rows_in_cur_column = 0;
		}

		// now print each row
		$i = 0;
		foreach ($result_rows as $key => $row) {
			$data = $key;
			
			if ($this->mColumns > 1) {
				if ($rows_in_cur_column == $rows_per_column) {
					$result .= "\n</div>";
					$result .= '<div style="float: left; width: ' . $column_width . '%">' . "\n";
					$rows_in_cur_column = 0;
				}
				$rows_in_cur_column++;
			}
			if ( $i > 0 && $plainlist )  {
				$result .=  ($i <= count($rows)) ? $listsep : $finallistsep; // the comma between "rows" other than the last one
			} else {
				$result .= $rowstart;
			}

			if ($this->mTemplate != '') { // build template code
				$this->hasTemplates = true;
				$wikitext = ($this->mUserParam)?"|userparam=$this->mUserParam":'';
				$wikitext .= '|0=' . $data;
				for($j = 1;$j<count($row);++$j) {
					$wikitext .= '|' . $j . '=' . $row[$j]->getResult($outputmode);
				}
				$result .= '{{' . $this->mTemplate . $wikitext . '}}';
				//str_replace('|', '&#x007C;', // encode '|' for use in templates (templates fail otherwise) -- this is not the place for doing this, since even DV-Wikitexts contain proper "|"!
			} else {  // build simple list
				if ( ($this->mShowHeaders != SMW_HEADERS_HIDE) && ('' != $headers[0]) )
					$result .= $headers[0] . ' ';
				$result .= $data . ' (';
				for($j = 1;$j<count($row);++$j) {
					if ( ($this->mShowHeaders != SMW_HEADERS_HIDE) && ('' != $headers[$j]) )
						$result .= $headers[$j] . ' ';
					$result .= $row[$j]->getResult($outputmode);
					if($j<count($row)-1) $result .= ', ';
				}
				$result .= ')';

				// </li> tag is not necessary in MediaWiki
				//$result .= $rowend;
			
				$i++;
			}
		}

		// Make label for finding further results
		if ( $this->linkFurtherResults($res) && ( ('group ol' != $this->mFormat) || ($this->getSearchLabel(SMW_OUTPUT_WIKI)) ) ) {
			$link = $res->getQueryLink();
			if ($this->getSearchLabel(SMW_OUTPUT_WIKI)) {
				$link->setCaption($this->getSearchLabel(SMW_OUTPUT_WIKI));
			}
			/// NOTE: passing the parameter sep is not needed, since we use format=ul

			$link->setParameter('ul','format'); // always use ul, other formats hardly work as search page output
			if ($this->mTemplate != '') {
				$link->setParameter($this->mTemplate,'template');
				if (array_key_exists('link', $this->m_params)) { // linking may interfere with templates
					$link->setParameter($this->m_params['link'],'link');
				}
			}
			// </li> tag is not necessary in MediaWiki
			$result .= $rowstart . $link->getText(SMW_OUTPUT_WIKI,$this->mLinker);// . $rowend;
		}
		if ($this->mColumns > 1)
			$result .= '</div>' . "\n";

		// Print footer
		$result .= $footer;
		if ($this->mColumns > 1)
			$result .= '<br style="clear: both">' . "\n";
		return $result;
	}

}
