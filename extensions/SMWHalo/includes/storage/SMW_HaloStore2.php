<?php

/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWSQLStore2 {

    /**
     * Modified to store ratings.
     */
    function updateData(SMWSemanticData $data, $newpage) {
        wfProfileIn("SMWHaloStore::updateData (SMW)");
        
        $annotations = smwfGetSemanticStore()->getRatedAnnotations($data->getSubject());
        parent::updateData($data, $newpage);
        if ($annotations !== NULL) {
            foreach($annotations as $pa) {
                smwfGetSemanticStore()->rateAnnotation($data->getSubject()->getDBkey(), $pa[0], $pa[1], $pa[2] );
            }
        }
        wfProfileOut("SMWHaloStore::updateData (SMW)");
    }
    
    function setup($verbose = true) {
        parent::setup($verbose);
        global $wgDBtype;
        $this->reportProgress("Altering tables for SMW+ if necessary ...\n\n",$verbose);
        if ($wgDBtype === 'postgres') {
            $this->reportProgress("Postgres is not supported by SMW+",$verbose);
            return;
        }
        $db =& wfGetDB( DB_MASTER );
        extract( $db->tableNames('smw_rels2', 'smw_atts2') );
        
        if (!$this->isColumnPresent($smw_rels2, 'rating')) {    
            $this->reportProgress("Altering $smw_rels2...\n",$verbose);
            $this->reportProgress("\t... adding column rating\n",$verbose);
            $db->query("ALTER TABLE $smw_rels2 ADD `rating` INT(8) FIRST", 'SMWHaloStore::setupTable');
            $this->reportProgress("done \n",$verbose);
        }
        
        if (!$this->isColumnPresent($smw_atts2, 'rating')) {  
            $this->reportProgress("Altering $smw_atts2...\n",$verbose);
            $this->reportProgress("\t... adding column rating\n",$verbose);
            $db->query("ALTER TABLE $smw_atts2 ADD `rating` INT(8) FIRST", 'SMWHaloStore::setupTable');
            $this->reportProgress("done \n",$verbose);
        }
        
        $this->reportProgress("Database initialised for SMW+ successfully.\n",$verbose);
        return true;
    }
    
    private function isColumnPresent($table, $column) {
       $db =& wfGetDB( DB_MASTER );
        
       $ratingPresent = false; 
       $res = $db->query( 'DESCRIBE ' . $table, 'SMWSQLStore::setupTable' );
      
       while ($row = $db->fetchObject($res)) {
           $field = $row->Field;
           if ($field == $column) {
                $ratingPresent = true;
           }
        }
        $db->freeResult($res);
        return $ratingPresent;
    }
}
?>