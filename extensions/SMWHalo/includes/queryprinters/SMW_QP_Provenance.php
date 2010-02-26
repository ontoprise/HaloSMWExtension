<?php
/**
 * @file
 * @ingroup SMWHaloQueryPrinters
 * 
 * Print query results in tables.
 * @author Kai Kühn
 * @file
 * @ingroup SMWQuery
 */

/**
 * New implementation of SMW's printer for result tables.
 *
 * @ingroup SMWQuery
 */
class SMWProvenanceResultPrinter extends SMWResultPrinter {

	public function getName() {
		return "SMWProvenanceResultPrinter";
	}

	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber;
		SMWOutputs::requireHeadItem(SMW_HEADER_SORTTABLE);

		// print header
		if ('broadtable' == $this->mFormat)
		$widthpara = ' width="100%"';
		else $widthpara = '';
		$result="";
		if (defined('SMW_UP_RATING_VERSION'))
		$result .= "UpRatingTable___".$smwgIQRunningNumber."___elbaTgnitaRpU";
		$result .= "<table class=\"smwtable\"$widthpara id=\"querytable" . $smwgIQRunningNumber . "\">\n";
		if ($this->mShowHeaders != SMW_HEADERS_HIDE) { // building headers
			$result .= "\t<tr>\n";
			foreach ($res->getPrintRequests() as $pr) {
				$result .= "\t\t<th>" . $pr->getText($outputmode, ($this->mShowHeaders == SMW_HEADERS_PLAIN?NULL:$this->mLinker) ) . "</th>\n";
			}
			$result .= "\t</tr>\n";
		}

		// print all result rows
		while ( $row = $res->getNext() ) {
			$result .= "\t<tr>\n";
			$firstcol = true;
			foreach ($row as $field) {
				$result .= "\t\t<td>";
				$first = true;
				while ( ($object = $field->getNextObject()) !== false ) {
					if ($object->getTypeID() == '_wpg') { // use shorter "LongText" for wikipage
						
						$provURL = $object->getProvenance();
						if ($firstcol && !is_null($provURL)) {
							
							//$text = $this->createArticleLinkFromProvenance($provURL, $this->getLinker($firstcol));
							$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
						} else {
							$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
							if (strlen($text) > 0 && !is_null($provURL)) {
								$text .= $this->createProvenanceLink($provURL);
							}
						}
						
					} else {
						$text = $object->getShortText($outputmode,$this->getLinker($firstcol));
						if (strlen($text) > 0) {
							$provURL = $object->getProvenance();
							if (!is_null($provURL)) $text .= $this->createProvenanceLink($provURL);
						}
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

	private function createProvenanceLink($url) {
		global $uprgWikipediaAPI;
        // no provenance link, if there is "novalue" placeholder from OB and if UP rating is not installed
		if ($url != 'http://novalue#0' && defined('SMW_UP_RATING_VERSION')) {
			$url=str_replace('&amp;', '&', $url);
			// hack for Wikipedia clone
			if (strpos($uprgWikipediaAPI, 'vulcan.com')) {
				$url = str_replace('en.wikipedia.org/wiki/', 'wiking.vulcan.com/wp/index.php,title=', $url);
				$url = preg_replace('/relative-line=\d+/', '', $url);
				$url = preg_replace('/absolute-line=(\d+)/', 'line=$1', $url);
				$url = preg_replace('/#[^\?&]*(\?|&){1}/', '', $url);
				$url = str_replace('#', '&', $url);
				$url = str_replace('?', '&', $url);
				$url = str_replace('index.php,title=', 'index.php?title=', $url);
			}
			return "UpRatingCell___".$url."___lleCgnitaRpU";
		}
	}
    
	/**
	 * Creates article link for instances of first column by using provenance data.
	 *
	 * @param string $url Provenance URL
	 * @param Linker $linker
	 * @return string (wiki markup)
	 */
	private function createArticleLinkFromProvenance($url, $linker) {
		$url_parts = parse_url($url);
		$desturl = $url_parts['path'];
		$title = substr($desturl, strrpos($desturl, "/")+1);
		$title = str_replace("_", " ", $title);
        $wpurl = str_replace('en.wikipedia.org/wiki/', 'wiking.vulcan.com/wp/index.php?title=', $url);
		return '['.$wpurl.' '.$title.']';
		
	}
}
