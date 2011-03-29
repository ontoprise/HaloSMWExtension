<?php

/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWSQLStore2 {

	var $mapping;
	/*
	 * This method is overwritten in order to hook in
	 * the Query Results Cache and the Query Management
	 */
	public function getQueryResult(SMWQuery $query){
		SMWQMQueryManagementHandler::getInstance()->storeQueryMetadata($query);

		global $smwgQRCEnabled;
		if($smwgQRCEnabled){
			$qrc = new SMWQRCQueryResultsCache();
			return $qrc->getQueryResult($query);
		} else {
			return $this->doGetQueryResult($query);
		}
	}

	public function doGetQueryResult(SMWQuery $query){
		return parent::getQueryResult($query);
	}

	function doDataUpdate(SMWSemanticData $data) {
		global $smwgQRCEnabled;
		if($smwgQRCEnabled){
			$qrc = new SMWQRCQueryResultsCache();
			$updateData = $qrc->updateData($data, $this);
			$this->mapping = NULL;
			$this->handleURIMappings($data);
			return $updateData;
				
		} else {
			$updateData = parent::doDataUpdate($data);
			$this->mapping = NULL;
			$this->handleURIMappings($data);
			return $updateData;
				
		}
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
		$ontologyURIProperty = smwfGetSemanticStore()->ontologyURIProp->getDBkey();

		if (!isset($id)) {
			$id = $db->selectRow($smw_ids, array('smw_id'), array('smw_title'=>$subjectTitle->getDBkey(), 'smw_namespace'=>$subjectTitle->getNamespace()));
		}

		if (is_null($id)) return; // something is wrong. stop here

		// delete old mappings
		$db->delete($smw_urimapping, array('smw_id' => $id->smw_id));

		foreach($data->getProperties() as $key => $property) {
			if ($ontologyURIProperty == $property->getDBkey()) {

				$propertyValueArray = $data->getPropertyValues($property);

				if (count($propertyValueArray) == 0) continue;
				// should be only one, otherwise out of spec)
				$uriValue = reset($propertyValueArray);
				$uriDBkeys = $uriValue->getDBkeys();
				$tscURI = array_shift($uriDBkeys);
				 
				// make sure to decode "(", ")", ",". Normally they are encoded in SMW URIs
				// This is crucial for OBL functional terms!
				$tscURI = str_replace("%28", "(", $tscURI);
				$tscURI = str_replace("%29", ")", $tscURI);
				$tscURI = str_replace("%2C", ",", $tscURI);

				$db->insert($smw_urimapping, array('smw_id' => $id->smw_id, 'page_id' => $subjectTitle->getArticleID(), 'smw_uri'=>$tscURI));

				$wikiURI = TSNamespaces::getInstance()->getFullURI($subjectTitle);
				$this->mapping = array($wikiURI, $tscURI);
			}
		}
	}

	public function getMapping() {
		return $this->mapping;
	}
}


