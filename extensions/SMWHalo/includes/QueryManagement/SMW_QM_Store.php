<?php

/**
 * QMStore implementation
 * 
 * @author kuehn
 *
 */
class SMWQMStore extends SMWStore {
	
	/**
	 * Wrapped store
	 * 
	 * @var SMWStore
	 */
	protected $smwstore;
	
	function __construct($basestore) {
		$this->wrappedStore = $basestore;
	}
	
	function getStore() {
		return $this->smwstore;
	}

	///// Reading methods /////
	// delegate to default implementation

	function getSemanticData(SMWDIWikiPage $subject, $filter = false) {
		return $this->wrappedStore->getSemanticData($subject, $filter);
	}


	function getPropertyValues($subject, SMWDIProperty $property, $requestoptions = NULL) {
		return $this->wrappedStore->getPropertyValues($subject, $property, $requestoptions);
	}

	function getPropertySubjects(SMWDIProperty $property, $value, $requestoptions = NULL) {
		return $this->wrappedStore->getPropertySubjects($property, $value, $requestoptions);
	}

	function getAllPropertySubjects(SMWDIProperty $property, $requestoptions = NULL) {
		return $this->wrappedStore->getAllPropertySubjects($property, $requestoptions);
	}

	function getProperties(SMWDIWikiPage $subject, $requestoptions = NULL) {
		return $this->wrappedStore->getProperties($subject, $requestoptions);
	}

	function getInProperties(SMWDataItem $object, $requestoptions = NULL) {
		return $this->wrappedStore->getInProperties($object, $requestoptions);
	}

	public function changeTitle( Title $oldtitle, Title $newtitle, $pageid, $redirid = 0 ) {
		return $this->wrappedStore->changeTitle( $oldtitle, $newtitle, $pageid, $redirid);
	}

	public function getPropertiesSpecial( $requestoptions = null ) {
		return $this->wrappedStore->getPropertiesSpecial($requestoptions);
	}

	public function getUnusedPropertiesSpecial( $requestoptions = null ) {
		return $this->wrappedStore->getUnusedPropertiesSpecial($requestoptions);
	}

	public function getWantedPropertiesSpecial( $requestoptions = null ) {
		return $this->wrappedStore->getWantedPropertiesSpecial($requestoptions);
	}

	public function getStatistics() {
		return $this->wrappedStore->getStatistics();
	}

	public function setup( $verbose = true ) {
		return $this->wrappedStore->setup($verbose);
	}

	public function drop( $verbose = true ) {
		return $this->wrappedStore->drop($verbose);
	}

	public function refreshData( &$index, $count, $namespaces = false, $usejobs = true ) {
		return $this->wrappedStore->refreshData($index, $count, $namespaces , $usejobs );
	}

	public function refreshConceptCache( Title $concept ) {
		return $this->wrappedStore->refreshConceptCache($concept);
	}

	public function deleteConceptCache( $concept ) {
		return $this->wrappedStore->deleteConceptCache($concept);
	}

	public function getConceptCacheStatus( $concept ) {
		return $this->wrappedStore->getConceptCacheStatus($concept);
	}

	public function reportProgress( $msg, $verbose = true ) {
		return $this->wrappedStore->reportProgress($msg, $verbose);
	}

	public function getSMWPageID( $title, $namespace, $iw, $subobjectName, $canonical = true ) {
		return $this->wrappedStore->getSMWPageID($title, $namespace, $iw, $subobjectName, $canonical);
	}

	public function getSMWPageIDandSort( $title, $namespace, $iw, $subobjectName, &$sort, $canonical ) {
		return $this->wrappedStore->getSMWPageIDandSort( $title, $namespace, $iw, $subobjectName, $sort, $canonical);
	}

	public function getRedirectId( $title, $namespace ) {
		return $this->wrappedStore->getRedirectId( $title, $namespace);
	}

	public function getSMWPropertyID( SMWDIProperty $property ) {
		return $this->wrappedStore->getSMWPropertyID($property);
	}

	public function cacheSMWPageID( $id, $title, $namespace, $iw, $subobjectName ) {
		return $this->wrappedStore->cacheSMWPageID($id, $title, $namespace, $iw, $subobjectName);
	}
	
	public function doDataUpdate(SMWSemanticData $data) {
		return $this->wrappedStore->doDataUpdate($data);
	}
	
	public function deleteSubject( Title $subject ){
		return $this->wrappedStore->deleteSubject($subject); 
	}
	
	
	/*
	 * Stores query metadata for QueryManagement
	 */
	public function getQueryResult(SMWQuery $query){
		SMWQMQueryManagementHandler::getInstance()->storeQueryMetadata($query);
		return $this->wrappedStore->getQueryResult($query);
	}
	
	public function getWikiPageSortKey( SMWDIWikiPage $wikiPage ) {
		return $this->wrappedStore->getWikiPageSortKey($wikiPage);
	}
	
	public function getRedirectTarget( SMWDataItem $dataItem ) {
		return $this->wrappedStore->getRedirectTarget($dataItem);
	}
	
	public function updateData( SMWSemanticData $data ) {
		return $this->wrappedStore->updateData($data);
	}
	
	public function clearData( SMWDIWikiPage $di ) {
		return $this->wrappedStore->clearData($di);
	}
	
}



