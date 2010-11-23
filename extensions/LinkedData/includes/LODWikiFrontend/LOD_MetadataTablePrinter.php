<?php
/**
 * Print query results in tables.
 * @author Kai Kuehn
 * @file
 * @ingroup LODWikiFrontend
 */

/**
 * New implementation of SMW's printer for result tables.
 *
 * @ingroup SMWQuery
 */
class LODMetadataTablePrinter extends SMWResultPrinter {
    
	/**
	 * Colors for different sources
	 * @var hash array
	 */
    private static $bgColors = array(0 => "#d2d5ee", 1 => "#fff6c9", 2 => "#dbd9be", 3 => "#bfd8f6", 4 => "#dedede", 5 => "#ffe3b0",
	                          6 => "#f2d0cd", 7 => "#e6ddee", 8 => "#eff9cd", 9 => "#d4e7ec");
	
    public function getName() {
        smwfLoadExtensionMessages( 'SemanticMediaWiki' );
        return wfMsg( 'smw_printername_' . $this->mFormat );
    }

    protected function getResultText( $res, $outputmode ) {
        global $smwgIQRunningNumber;
        SMWOutputs::requireHeadItem( SMW_HEADER_SORTTABLE );

        // print header
        $result = '<table class="smwtable"' .
              ( $this->mFormat == 'broadtable' ? ' width="100%"' : '' ) .
                  " id=\"querytable$smwgIQRunningNumber\">\n";
              
        if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) { // building headers
            $result .= "\t<tr>\n";
            
            foreach ( $res->getPrintRequests() as $pr ) {
                $result .= "\t\t<th>" . $pr->getText( $outputmode, ( $this->mShowHeaders == SMW_HEADERS_PLAIN ? null:$this->mLinker ) ) . "</th>\n";
            }
            
            $result .= "\t</tr>\n";
        }

        // print all result rows
        while ( $row = $res->getNext() ) {
            $result .= "\t<tr>\n";
            $firstcol = true;
            $fieldcount = - 1;
            foreach ( $row as $field ) {
                $fieldcount = $fieldcount + 1;

                $result .= "\t\t<td";
                $alignment = trim( $field->getPrintRequest()->getParameter( 'align' ) );
                if ( ( $alignment == 'right' ) || ( $alignment == 'left' ) || ( $alignment == 'center' ) ) {
                    $result .= ' style="text-align:' . $alignment . ';"';
                }
                $currentColumn = current($field->getContent());
                
                if ($firstcol) {
                    $sourceID = $currentColumn->getMetadata('swp2_authority_id');
                }
                $result .= ' style="background-color: '.self::$bgColors[$this->hashtocolor(reset($sourceID))].'"';
                $result .= ">";
                
                $first = true;
                while ( ( $object = $field->getNextObject() ) !== false ) {
                    if ( $first ) {
                        if ( $object->isNumeric() ) { // additional hidden sortkey for numeric entries
                            $result .= '<span class="smwsortkey">' . $object->getValueKey() . '</span>';
                        }
                        $first = false;
                    } else {
                        $result .= '<br />';
                    }
                    // use shorter "LongText" for wikipage
                    $result .= ( ( $object->getTypeID() == '_wpg' ) || ( $object->getTypeID() == '__sin' ) ) ?
                           $object->getLongText( $outputmode, $this->getLinker( $firstcol ) ):
                           $object->getShortText( $outputmode, $this->getLinker( $firstcol ) );
                }
                $result .= "</td>\n";
                $firstcol = false;
            }
            $result .= "\t</tr>\n";
        }

        // print further results footer
        if ( $this->linkFurtherResults( $res ) ) {
            $link = $res->getQueryLink();
            if ( $this->getSearchLabel( $outputmode ) ) {
                $link->setCaption( $this->getSearchLabel( $outputmode ) );
            }
            $result .= "\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> ' . $link->getText( $outputmode, $this->mLinker ) . "</td></tr>\n";
        }
        $result .= "</table>\n"; // print footer
        $this->isHTML = ( $outputmode == SMW_OUTPUT_HTML ); // yes, our code can be viewed as HTML if requested, no more parsing needed
        return $result;
    }

    public function getParameters() {
        $params = parent::getParameters();
        $params = array_merge( $params, parent::textDisplayParameters() );
        return $params;
    }
    
    private function hashtocolor($sourceID) {
    	return md5($sourceID) % 10;
    }

}
