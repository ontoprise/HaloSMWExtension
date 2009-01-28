<?php
abstract class USStore {
	
    private static $STORE;
	/**
	 * Lookup page titles in $namespaces
	 *
	 */
	public abstract function lookUpTitlesByText($termString, array $namespaces, $disjunctive = false, $limit=10, $offset=0);

	
        
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
}
?>