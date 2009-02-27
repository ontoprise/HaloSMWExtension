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
	
	
        
    public static function &getStore() {
        global $IP, $smwgDefaultStore;
        if (self::$STORE == NULL) {
            if ($smwgDefaultStore == 'SMWSQLStore2' || 'SMWHaloStore2') {
            	// may use SMW or SMWHalo store
                require_once($IP . '/extensions/UnifiedSearch/storage/US_StoreSQL.php');
                self::$STORE = new USStoreSQL();
            } else {
                trigger_error("The store '$smwgDefaultStore' is not implemented for the UnifiedSearch extension. Please use 'SMWSQLStore2'.");
            } 
        }
        return self::$STORE;
    }
    
    /**
     * Returns direct subcategories.
     *
     * @param string $term
     * @return Title
     */
    public abstract function getDirectSubCategories(Title $categoryTitle, $requestoptions = NULL);
    
    /**
     * Returns direct subproperties.
     *
     * @param string $term
     * @return Title
     */
    public abstract function getDirectSubProperties(Title $attribute, $requestoptions = NULL);
    
    /**
     * Returns property subjects whose objects or literals matches the given restrictions.
     *
     * @param array $properties 
     * @param array $namespace Only subjects from that namespace
     * @param SMWRequestOptions $requestoptions
     */
    public abstract function getPropertySubjects(array $properties, array $namespace, $requestoptions);

    /**
     * Returns a title if it matches the given term as single title.
     * Case-insensitive
     *
     * @param string $term
     * @return Title
     */
    public abstract function getSingleTitle($term);

    /**
     * Gets all categories the given title is member of.
     *
     * @param Title $title
     * @return array of Title
     */
    public abstract function getCategories($title);

    /**
     * Gets all redirects which point to the given title.
     *
     * @param Title $title
     * @return array of Title
     */
    public abstract function getRedirects($title);

    /**
     * Adds (or updates) a new search statistic with given hits.
     *
     * @param string $searchTerm
     * @param int $hits
     */
    public abstract function addSearchTry($searchTerm, $hits);

    /**
     * Returns search statistics
     *
     * @param int $limit
     * @param int $offset
     * @param 0 or 1 $ascOrDesc
     * @param 0 or 1 $sortFor where 0 = hits, 1 = tries
     * @return array($row->searchterm, $row->tries, $row->hits);
     */
    public abstract function getSearchTries($limit, $offset, $ascOrDesc, $sortFor);

    /**
     * Setups database for UnifiedSearch extension
     *
     * @param boolean $verbose
     */
    public abstract function setup($verbose);
}
?>