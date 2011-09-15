<?php

/**
 * QMStore implementation
 * 
 * @author kuehn
 *
 */
class SMWQMStore {
	
	/**
	 * Wrapped store
	 * 
	 * @var SMWStore
	 */
	protected $smwstore;
	
	function __construct($basestore) {
		$this->smwstore = $basestore;
	}
	
	function getStore() {
		return $this->smwstore;
	}

	///// Reading methods /////
	// delegate to default implementation

	function getSemanticData(SMWDIWikiPage $subject, $filter = false) {
		return $this->smwstore->getSemanticData($subject, $filter);
	}


	function getPropertyValues($subject, SMWDIProperty $property, $requestoptions = NULL) {
		return $this->smwstore->getPropertyValues($subject, $property, $requestoptions);
	}

	function getPropertySubjects(SMWDIProperty $property, $value, $requestoptions = NULL) {
		return $this->smwstore->getPropertySubjects($property, $value, $requestoptions);
	}

	function getAllPropertySubjects(SMWDIProperty $property, $requestoptions = NULL) {
		return $this->smwstore->getAllPropertySubjects($property, $requestoptions);
	}

	function getProperties(SMWDIWikiPage $subject, $requestoptions = NULL) {
		return $this->smwstore->getProperties($subject, $requestoptions);
	}

	function getInProperties(SMWDataItem $object, $requestoptions = NULL) {
		return $this->smwstore->getInProperties($object, $requestoptions);
	}



	public function changeTitle( Title $oldtitle, Title $newtitle, $pageid, $redirid = 0 ) {
		return $this->smwstore->changeTitle( $oldtitle, $newtitle, $pageid, $redirid);
	}

	public function getPropertiesSpecial( $requestoptions = null ) {
		return $this->smwstore->getPropertiesSpecial($requestoptions);
	}

	public function getUnusedPropertiesSpecial( $requestoptions = null ) {
		return $this->smwstore->getUnusedPropertiesSpecial($requestoptions);
	}

	public function getWantedPropertiesSpecial( $requestoptions = null ) {
		return $this->smwstore->getWantedPropertiesSpecial($requestoptions);
	}

	public function getStatistics() {
		return $this->smwstore->getStatistics();
	}

	public function setup( $verbose = true ) {
		return $this->smwstore->setup($verbose);
	}

	public function drop( $verbose = true ) {
		return $this->smwstore->drop($verbose);
	}

	public function refreshData( &$index, $count, $namespaces = false, $usejobs = true ) {
		return $this->smwstore->refreshData($index, $count, $namespaces , $usejobs );
	}

	public function refreshConceptCache( Title $concept ) {
		return $this->smwstore->refreshConceptCache($concept);
	}

	public function deleteConceptCache( $concept ) {
		return $this->smwstore->deleteConceptCache($concept);
	}

	public function getConceptCacheStatus( $concept ) {
		return $this->smwstore->getConceptCacheStatus($concept);
	}

	public function reportProgress( $msg, $verbose = true ) {
		return $this->smwstore->reportProgress($msg, $verbose);
	}

	public function getSMWPageID( $title, $namespace, $iw, $subobjectName, $canonical = true ) {
		return $this->smwstore->getSMWPageID($title, $namespace, $iw, $subobjectName, $canonical);
	}

	public function getSMWPageIDandSort( $title, $namespace, $iw, $subobjectName, &$sort, $canonical ) {
		return $this->smwstore->getSMWPageIDandSort( $title, $namespace, $iw, $subobjectName, $sort, $canonical);
	}

	public function getRedirectId( $title, $namespace ) {
		return $this->smwstore->getRedirectId( $title, $namespace);
	}

	public function getSMWPropertyID( SMWDIProperty $property ) {
		return $this->smwstore->getSMWPropertyID($property);
	}

	public function cacheSMWPageID( $id, $title, $namespace, $iw, $subobjectName ) {
		return $this->smwstore->cacheSMWPageID($id, $title, $namespace, $iw, $subobjectName);
	}
	
	/*
	 * This method is overwritten in order to hook in
	 * the Query Results Cache and the Query Management
	 */
	public function getQueryResult(SMWQuery $query){

		SMWQMQueryManagementHandler::getInstance()->storeQueryMetadata($query);
		$qrc = new SMWQRCQueryResultsCache($this->smwstore);
		return $qrc->getQueryResult($query);

	}

	 

	function doDataUpdate(SMWSemanticData $data) {
		
		$qrc = new SMWQRCQueryResultsCache();
		$qrc->updateData($data, $this);
		$this->smwstore->doDataUpdate($data);

	}
}