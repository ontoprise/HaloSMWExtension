<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * Print query results as XML for semantic notifications. The format depends on
 * the size of the generated XML. Normally all values are stored. If the XML
 * becomes too large, only hash values for each row of the result are stored.
 * If even this is too large, the element <result> remains empty.
 *  
 * @author Thomas
 * 
 * @note AUTOLOADED
 */
class SMW_SN_XMLResultPrinter extends SMWResultPrinter {

	private $mMaxSize = 1000;	// The maximum size of the printed result
	
	/**
	 * Constructor. 
	 * Sets the limits for the current user.
	 *
	 */
	public function __construct() {
		$limits = SemanticNotificationManager::getUserLimitations();
		$this->mMaxSize = $limits['size'];		
	}
	
	/**
	 * Returns the result as XML structure with the following format:
	 * <?xml version="1.0"?>
	 * <QueryResult>
	 *     <table>
	 *         <rows>number of rows</rows>
	 *         <columns>number of columns</columns>
	 *         <hash>a hashvalue over all results</hash>
	 *     </table>
	 *     <result>
	 *        <row>
	 *           <cell>content of a cell in the table</cell>
	 *          ...further cells...
	 *        </row>
	 *        ...further rows with their cells...
	 *     </result>
	 * </QueryResult>
	 * 
	 * If the XML serialization becomes too large, the rows are reduced to a
	 * hash value e.g.<row hash="jfads098q34hpq09zfga43" />.
	 * If even this becomes too large, the element <result> remains empty.
	 *
	 * @param SMWQueryResult $res
	 * @param int $outputmode
	 * @return string
	 * 		The query result as XML structure.
	 */
    protected function getResultText($res, $outputmode) {
       $variables = array();
       $result = $this->printHeader();
       $meta   = $this->printMetaData($res);
       $r      = $this->printResults($res, $hash);
       $meta   = str_replace('<hash></hash>', '<hash>'.$hash.'</hash>', $meta);
       $result .= $meta .  $r . $this->printFooter();
       return $result;
    }
    
    /**
     * Creates the element <table> as described in method getResultText.
     *
     * @param SMWQueryResult $res
     * 		The query result
     * @return string
     * 		The result's meta data i.e. number of rows and columns and a hash value.
     */
    private function printMetaData(&$res) {
    	
        $result = "\t<table>\n";
        $result .= "\t\t<rows>".$res->getCount()."</rows>\n";
        $result .= "\t\t<columns>".$res->getColumnCount()."</columns>\n";
        $result .= "\t\t<hash></hash>\n";
        $result .= "\t</table>\n";
        return $result;
    }
    
    /**
     * Creates the content of the element <result> as described in method 
     * getResultText.
     *
     * @param SMWQueryResult $res
     * 		The query result.
     * @param string $hash
     * 		The hash value for all results is returned in this parameter.
     * @return string
     * 		The XML serialization of the result.
     */
    private function printResults($res, &$hash) {
		$hash = hash_init('md5');
    	
    	$result = "\t<result>\n";
    	$rowsLong = "";  // Long form of the result with detailed cells
    	$rowsShort = ""; // Short form of the result where each row contains only a hash value
        while ( $row = $res->getNext() ) {
            $rowsLong .= "\t\t<row>\n";
			$rowsShort .= "\t\t<row hash=\"";
			$rowhash = hash_init('md5');
			           
            foreach ($row as $field) {
                $rowsLong .= "\t\t\t<cell>";
                $first = true;
                              
                while ( ($object = $field->getNextObject()) !== false ) {
                    if ($object->getTypeID() == '_wpg') {  // print whole title with prefix in this case
                        $text = $object->getTitle()->getPrefixedText();
                    } else {
                        if ($object->isNumeric()) { // does this have any effect?
                            $text = $object->getNumericValue();
                        } else {
                            $text = $object->getXSDValue();
                        }
                    }
                    hash_update($hash, $text);
					hash_update($rowhash, $text);
                    $text = htmlspecialchars($text);
                    $rowsLong .= $text;
                }
                $rowsLong .= "</cell>\n";
            }
            $rowsLong .= "\t\t</row>\n";
            $rowhash = hash_final($rowhash);
            $rowsShort .= $rowhash . "\" />\n";
        }
        
		$hash = hash_final($hash);
		if (strlen($rowsLong) <= $this->mMaxSize) {
			// return the long result
	        $result .= $rowsLong."\t</result>\n";
		} else if (strlen($rowsShort) <= $this->mMaxSize) {
			// return the short result
	        $result .= $rowsShort."\t</result>\n";
		} else {
			// return nothing
	        $result .= "\t</result>\n";
		}
        return $result;    
    }
    
    /**
     * Returns the XML header
     *
     * @return string
     * 		The XML header
     */
    private function printHeader() {
    	return "<?xml version=\"1.0\"?>\n<QueryResult>\n";
    }
    
    /**
     * Returns the XML footer
     *
     * @return string
     * 		The XML footer
     */
    private function printFooter() {
    	return '</QueryResult>';
    }
}
?>