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
	private static $bgColors = array(0 => "#cccccc", 1 => "#bbbbbb", 2 => "#aaaaaa", 3 => "#999999", 4 => "#888888", 5 => "#777777",
	                          6 => "#666666", 7 => "#555555", 8 => "#444444", 9 => "#333333");
	
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
                $firstColumn = current($field->getContent());
               
                $sourceID = $firstColumn->getMetadata('swp2_authority_id');
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
