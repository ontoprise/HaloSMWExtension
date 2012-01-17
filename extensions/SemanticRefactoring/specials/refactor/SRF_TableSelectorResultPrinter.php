<?php

/**
 * Print query results in tables for selecting instances
 * to refactor. Based on SMWTableResultPrinter
 *
 * @author Kai Kühn
 *
 * SMWTableResultPrinter:
 *
 * @author Markus Krötzsch
 * @author Jeroen De Dauw  < jeroendedauw@gmail.com >
 *
 * @file
 * @ingroup SREFSpecials
 */
class SRFTableSelectorResultPrinter extends SMWTableResultPrinter {

	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		global $smwgIQRunningNumber;
		SMWOutputs::requireHeadItem( SMW_HEADER_SORTTABLE );

		$tableRows = array();

		while ( $subject = $res->getNext() ) {
			$tableRows[] = $this->getRowForSubject( $subject, $outputmode );
		}
        
		$firstSlice = true;
		$result = "";
		
		$pageNum = intval(count($tableRows) / SREF_QUERY_PAGE_LIMIT);
		$pageNum += count($tableRows) % SREF_QUERY_PAGE_LIMIT === 0 ? 0 : 1;
		
		for($i = 0; $i < $pageNum; $i++) {
		
			// print header
			$visible = $firstSlice ? 'style="display: block" pageNum="'.$pageNum.'"' : 'style="display: none"';
			$firstSlice = false;
			$result .= '<div id="sref_slice'.$i.'" '.$visible.'>';
			$result .= '<table class="smwtable"' .
			( $this->mFormat == 'broadtable' ? ' width="100%"' : '' ) .
                  " id=\"querytable$smwgIQRunningNumber\">\n";

			if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) { // building headers
				$headers = array();

				foreach ( $res->getPrintRequests() as $pr ) {
					$attribs = array();

					if ( array_key_exists( $pr->getHash(), $this->columnsWithSortKey ) ) {
						$attribs['class'] = 'numericsort';
					}

					$headers[] = Html::rawElement(
                    'th',
					$attribs,
					$pr->getText( $outputmode, ( $this->mShowHeaders == SMW_HEADERS_PLAIN ? null:$this->mLinker ) )
					);
				}
                if ($firstSlice) {
				    array_unshift( $tableRows, '<tr>' . implode( "\n", $headers ) . '</tr>' );
                }
			}

			$result .= implode( "\n", array_slice($tableRows, $i*SREF_QUERY_PAGE_LIMIT, SREF_QUERY_PAGE_LIMIT) );

			$result .= "</table>\n"; // print footer
			$result .= "</div>\n";
		}
		$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML ); // yes, our code can be viewed as HTML if requested, no more parsing needed

		return $result;
	}
	/**
	 * Gets the contents for a table cell for all values of a property of a subject.
	 *
	 * @since 1.6.1
	 *
	 * @param SMWResultArray $resultArray
	 * @param $outputmode
	 *
	 * @return string
	 */
	protected function getCellContent( SMWResultArray $resultArray, $outputmode ) {
		$values = array();
		$isFirst = true;

		while ( ( $dv = $resultArray->getNextDataValue() ) !== false ) {
			$sortKey = '';
			$isSubject = $resultArray->getPrintRequest()->getMode() == SMWPrintRequest::PRINT_THIS;

			if ( $isFirst ) {
				$isFirst = false;
				$sortkey = $dv->getDataItem()->getSortKey();
				$enc_sortkey = $isSubject ? Sanitizer::encodeAttribute($dv->getDataItem()->getTitle()->getPrefixedDBkey()) : "";
				$checkbox = '<input class="sref_instance_selector" type="checkbox" checked="true" prefixedTitle="'.$enc_sortkey.'"></input>';
				if ( is_numeric( $sortkey ) ) { // additional hidden sortkey for numeric entries
					$this->columnsWithSortKey[$resultArray->getPrintRequest()->getHash()] = true;
					$sortKey .= '<span class="smwsortkey">' . $sortkey . '</span>';
				}
			}

			$value = ( ( $dv->getTypeID() == '_wpg' ) || ( $dv->getTypeID() == '__sin' ) ) ?
			$dv->getLongText( $outputmode, $this->getLinker( $isSubject ) ) :
			$dv->getShortText( $outputmode, $this->getLinker( $isSubject ) );

			$values[] = $isSubject ? $checkbox . $sortKey . $value : $sortKey . $value ;

		}

		return implode( '<br />', $values );
	}

	 
}
