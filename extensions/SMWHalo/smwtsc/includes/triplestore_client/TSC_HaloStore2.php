<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWStoreAdapter {

	var $mURIMappings;

	function __construct($basestore) {
		$this->smwstore = $basestore;
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
		if ($id === false) return; // something is wrong. stop here
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

		if (is_null($id) || $id === false) return; // something is wrong. stop here

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
				
				if (!($uriValue instanceof SMWDIUri)) continue;
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
			$namespaceMapping = TSCMappingStore::getAllNamespaceMappings();
			$prefixMappings=array();
			foreach($namespaceMapping as $prefix => $uri) {
				$prefixMappings[strtolower($prefix)] = $prefix;
			}
			$parts = explode("/", $subjectTitle->getDBkey());
			$prefix = strtolower($parts[0]);
			if (array_key_exists($prefix, $prefixMappings)) {
				$local = substr($subjectTitle->getDBkey(), strlen($prefix) + 1);
				$tscURI = $namespaceMapping[$prefixMappings[$prefix]] . $local;
				$db->insert($smw_urimapping, array('smw_id' => $id->smw_id, 'page_id' => $subjectTitle->getArticleID(), 'smw_uri'=>$tscURI));
				$wikiURI = TSNamespaces::getInstance()->getFullURI($subjectTitle);
				$this->mURIMappings = array($wikiURI, $tscURI);
			}
		}
	}

	public function getMapping() {
		return $this->mURIMappings;
	}

	
}


