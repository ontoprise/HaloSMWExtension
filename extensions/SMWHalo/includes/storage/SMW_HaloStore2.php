<?php


/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWSQLStore2 {

    /**
     * Modified to store ratings.
     */
    function updateData(SMWSemanticData $data) {
        wfProfileIn("SMWHaloStore::updateData (SMW)");
        
        wfRunHooks('smwhaloBeforeUpdateData', array($data));
        parent::updateData($data);
        wfRunHooks('smwhaloAfterUpdateData', array($data));
        
        wfProfileOut("SMWHaloStore::updateData (SMW)");
    }
   
}
?>