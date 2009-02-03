<?php
/**
 * @author: Kai Kühn
 * 
 * Created on: 27.01.2009
 *
 */
abstract class USStore {
	
    private static $STORE;
    private static $SMW_STORE;
	/**
	 * Lookup page titles in $namespaces
	 *
	 */
	public abstract function lookUpTitles($terms, array $namespaces, $disjunctive = false, $limit=10, $offset=0, $mode = 0);

	
        
    public static function &getStore() {
        global $IP, $smwgBaseStore;
        if (self::$STORE == NULL) {
            if ($smwgBaseStore != 'SMWHaloStore' && $smwgBaseStore != 'SMWHaloStore2') {
                trigger_error("The store '$smwgBaseStore' is not implemented for the HALO extension. Please use 'SMWHaloStore2'.");
            } elseif ($smwgBaseStore == 'SMWHaloStore2') {
                require_once($IP . '/extensions/UnifiedSearch/storage/US_StoreSQL.php');
                self::$STORE = new USStoreSQL();
            }  else {
                trigger_error("The store '$smwgBaseStore' is deprecated. You must use 'SMWHaloStore2'.");
            }
        }
        return self::$STORE;
    }
    
    public static function &getSMWStore() {
        global $IP, $smwgBaseStore;
        if (self::$SMW_STORE == NULL) {
            if ($smwgBaseStore != 'SMWHaloStore' && $smwgBaseStore != 'SMWHaloStore2') {
                trigger_error("The store '$smwgBaseStore' is not implemented for the HALO extension. Please use 'SMWHaloStore2'.");
            } elseif ($smwgBaseStore == 'SMWHaloStore2') {
                
                self::$SMW_STORE = new SMWStore2Adv();
            }  else {
                trigger_error("The store '$smwgBaseStore' is deprecated. You must use 'SMWHaloStore2'.");
            }
        }
        return self::$SMW_STORE;
    }
}
?>