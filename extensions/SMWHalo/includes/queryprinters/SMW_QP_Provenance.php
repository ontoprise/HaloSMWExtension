<?php
/**
 * Print query results in tables.
 * @author Kai K�hn
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
                        $text = $object->getLongText($outputmode,$this->getLinker($firstcol));
                        if (!$firstcol && strlen($text) > 0) {
                            $provURL = $object->getProvenance();
                            $text .= $this->createProvenanceLink($provURL);
                        }
                    } else {
                        $text = $object->getShortText($outputmode,$this->getLinker($firstcol));
                        if (!$firstcol && strlen($text) > 0) {
                            $provURL = $object->getProvenance();
                            $text .= $this->createProvenanceLink($provURL);
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
        if (defined('SMW_UP_RATING_VERSION')) {
            $url=str_replace('&amp;', '&', $url);
            return "UpRatingCell___".$url."___lleCgnitaRpU";
        }
    }
}
