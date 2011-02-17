<?php

/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWSQLStore2 {

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

	function updateData(SMWSemanticData $data) {
		global $smwgQRCEnabled;
		if($smwgQRCEnabled){
			$qrc = new SMWQRCQueryResultsCache();
			return $qrc->updateData($data, $this);
		} else {
			return $this->doUpdateData($data);
		}
	}

	function doUpdateData(SMWSemanticData $data) {
		$updateData = parent::updateData($data);
		$this->handleURIMappings($data);
		return $updateData;
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

		foreach($data->getProperties() as $key => $property) {
			if ($ontologyURIProperty == $property->getDBkey()) {
				
				if (!isset($id)) {
					$id = $db->selectRow($smw_ids, array('smw_id'), array('smw_title'=>$subjectTitle->getDBkey(), 'smw_namespace'=>$subjectTitle->getNamespace()));
				}
				
				if (is_null($id)) continue;
				
				$propertyValueArray = $data->getPropertyValues($property);

				// should be only one, otherwise out of spec)
				$uriValue = reset($propertyValueArray);
				$uriDBkeys = $uriValue->getDBkeys();
				$db->delete($smw_urimapping, array('smw_id' => $id->smw_id));
				$db->insert($smw_urimapping, array('smw_id' => $id->smw_id, 'page_id' => $subjectTitle->getArticleID(), 'smw_uri'=>array_shift($uriDBkeys)));
			}
		}
	}
}


