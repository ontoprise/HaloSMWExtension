<?php

/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWStore {

	var $smwstore;
	var $mURIMappings;

	function __construct($basestore) {
		$this->smwstore = $basestore;
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
    	return $this->smwstore->getQueryResult($query);
	}
	

	function doDataUpdate(SMWSemanticData $data) {

		$updateData = $this->smwstore->doDataUpdate($data);
		$this->mURIMappings = NULL;
		$this->handleURIMappings($data);
		return $updateData;

	}

	function deleteSubject(Title $subjectTitle) {
		$this->smwstore->deleteSubject($subjectTitle);

		// remove
		$db =& wfGetDB( DB_MASTER );
		$smw_ids =  $db->tableName('smw_ids');
		$smw_urimapping = $db->tableName('smw_urimapping');
		$id = $db->selectRow($smw_ids, array('smw_id'), array('smw_title'=>$subjectTitle->getDBkey(), 'smw_namespace'=>$subjectTitle->getNamespace()));
		if (is_null($id)) return; // something is wrong. stop here
		// delete mappings
		$db->delete($smw_urimapping, array('smw_id' => $id->smw_id));
	}

	/**
	 * Creates URI mapping table. Maps the SMW ids to URIs.
	 *
	 * @param SMWSemanticData $data
	 *
	 */
	private function handleURIMappings(SMWSemanticData $data) {

		$db =& wfGetDB( DB_MASTER );
		$smw_ids =  $db->tableName('smw_ids');
		$smw_urimapping = $db->tableName('smw_urimapping');
		$subjectTitle = $data->getSubject()->getTitle();
		$ontologyURIProperty = SMWHaloPredefinedPages::$ONTOLOGY_URI->getDBkey();


		if (!isset($id)) {
			$id = $db->selectRow($smw_ids, array('smw_id'), array('smw_title'=>$subjectTitle->getDBkey(), 'smw_namespace'=>$subjectTitle->getNamespace()));
		}

		if (is_null($id)) return; // something is wrong. stop here

		// delete old mappings
		$db->delete($smw_urimapping, array('smw_id' => $id->smw_id));

		// addOntologyURI mappings, if any
		$ontologyURIMappingAdded = false;
		foreach($data->getProperties() as $property) {

			// only if OntologyURI property
			if ($ontologyURIProperty == $property->getKey()) {

				$propertyValueArray = $data->getPropertyValues($property);

				if (count($propertyValueArray) == 0) continue;
				// should be only one, otherwise out of spec)
				$uriValue = reset($propertyValueArray);
				$tscURI = $uriValue->getURI();
					
				// make sure to decode "(", ")", ",". Normally they are encoded in SMW URIs
				// This is crucial for OBL functional terms!
				$tscURI = str_replace("%28", "(", $tscURI);
				$tscURI = str_replace("%29", ")", $tscURI);
				$tscURI = str_replace("%2C", ",", $tscURI);

				$db->insert($smw_urimapping, array('smw_id' => $id->smw_id, 'page_id' => $subjectTitle->getArticleID(), 'smw_uri'=>$tscURI));

				$wikiURI = TSNamespaces::getInstance()->getFullURI($subjectTitle);
				$this->mURIMappings = array($wikiURI, $tscURI);
				$ontologyURIMappingAdded = true;
			}
		}
		if (!$ontologyURIMappingAdded) {
			// that means ontology URL might be implicitly defined by a prefix in the title: Category:Foaf/Person
			$namespaceMapping = smwfGetSemanticStore()->getAllNamespaceMappings();
			$parts = explode("/", $subjectTitle->getText());
			$prefix = strtolower($parts[0]);
			if (array_key_exists($prefix, $namespaceMapping)) {
				$local = substr($subjectTitle->getText(), strlen($prefix) + 1);
				$tscURI = $namespaceMapping[$prefix] . $local;
				$db->insert($smw_urimapping, array('smw_id' => $id->smw_id, 'page_id' => $subjectTitle->getArticleID(), 'smw_uri'=>$tscURI));
				$wikiURI = TSNamespaces::getInstance()->getFullURI($subjectTitle);
				$this->mURIMappings = array($wikiURI, $tscURI);
			}
		}
	}

	public function getMapping() {
		return $this->mURIMappings;
	}

	public function setLocalRequest($local) {
		// dummy
	}
}


