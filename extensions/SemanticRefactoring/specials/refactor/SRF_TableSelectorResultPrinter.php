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
