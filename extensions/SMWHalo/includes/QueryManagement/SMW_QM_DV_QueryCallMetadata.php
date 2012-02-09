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
 *
  * @ingroup SMWHaloQueryResultsCache
 *
 * @author Ingo Steinbauer
 *
 */

/*
 * A SMWDataValue type for storing query calls related metadata
 */
class SMWQueryCallMetadataValue extends SMWDataValue {
	
	protected $m_data;
	
	public function __construct($typeid) {
		parent::__construct($typeid);
		global $smwg_qm_metadata_subsubject_names, $wgTitle;
		if(is_null($smwg_qm_metadata_subsubject_names)){
			$smwg_qm_metadata_subsubject_names = 1;
		}
		$smwg_qm_metadata_subsubject_names += 1;
		$subject = new SMWDIWikiPage( $wgTitle->getDBkey(),
			$wgTitle->getNamespace(), $wgTitle->getInterwiki(),
			'_qm_'.$smwg_qm_metadata_subsubject_names );
		$this->m_data = new SMWContainerSemanticData($subject);
	}
	
	/*
	 * Sets the id of this query call
	 */
	public function setQueryId($queryId){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQID_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryId);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	/*
	 * Sets the query string of this query call
	 */
	public function setQueryString($queryString){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQS_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryString);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
/*
	 * Sets the limit of this query call
	 */
	public function setQueryLimit($queryLimit){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQL_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryLimit);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
/*
	 * Sets the offset of this query call
	 */
	public function setQueryOffset($queryOffset){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQO_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryOffset);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	/*
	 * Add dependency to a property, which is used by the query
	 */
	public function addPropertyDependency($propertyName){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_DOP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $propertyName);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	/*
	 * Add dependency to a category, which is used by the query
	 */
	public function addCategoryDependency($categoryName){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_DOC_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $categoryName);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	/*
	 * Add dependency to an instance, which is used by the query
	 */
	public function addInstanceDependency($instanceName){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_DOI_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $instanceName);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function addExtraPropertyPrintouts($epp){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HEPP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $epp);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setExtraCategoryPrintouts($hasECP){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HECP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $hasECP);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setIsSPQRQLQuery($isSPARQL){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_ISQ_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $isSPARQL);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setusesASKSyntax($isASK){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_UAS_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $isASK);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setUsedInArticle($uia){
		$propertyValue = SMWPropertyValue::makeUserProperty(QM_UIA_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $uia);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setUsedQueryPrinter($uqp){
		$propertyValue = SMWPropertyValue::makeUserProperty(QM_UQP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $uqp);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setQueryName($hqn){
		$propertyValue = SMWPropertyValue::makeUserProperty(QM_HQN_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $hqn);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function parseUserValue($value){
		return true;
	} 
	
	public function getShortWikiText($linked = null){
	}
	
	
	public function getShortHTMLText($linked = null){
	}
	
	public function  getLongWikiText($linker = null){
	}
	
	public function getLongHTMLText($linker = null){
	}
	
	public function getWikiValue(){
	}
	
	public function isValid(){
		return true;
	}
	
	public function getSignature() {
		return 'c';
	}
	
	protected function loadDataItem( SMWDataItem $dataItem ){
		$this->m_data = $dataItem->getSemanticData();
	}
	
	public function getDataItem(){
		return new SMWDIContainer($this->m_data);
	}
	
/**
	 * Containers have one DB key, so the value of this function should be an array with one
	 * element. This one DB key should consist of an array of arbitrary length where each
	 * entry encodes one property-value pair. The pairs are encoded as arrays of size two
	 * that correspond to the input arguments of SMWSemanticData::addPropertyStubValue():
	 * a property DB key (string), and a value DB key array (array).
	 */
	protected function parseDBkeys( $args ) {
		$this->m_data->clear();
		if ( count( $args ) > 0 ) {
			foreach ( reset( $args ) as $value ) {
				if ( is_array( $value ) && ( count( $value ) == 2 ) ) {
					$this->m_data->addPropertyStubValue( reset( $value ), end( $value ) );
				}
			}
		}
	}

	/**
	 * Serialize data in the format described for parseDBkeys(). However, it is usually
	 * expected that callers are aware of containers (this is the main purpose of this
	 * abstract class) so they can use specific methods for accessing the data in a more
	 * convenient form that contains the (probably available) information about property
	 * and data *objects* (not just their plain strings).
	 */
	public function getDBkeys() {
		$data = array();
		foreach ( $this->m_data->getProperties() as $property ) {
			foreach ( $this->m_data->getPropertyValues( $property ) as $dv ) {
				$data[] = array( $property->getDBkey(), $dv->getDBkeys() );
			}
		}
		return array( $data );
	}

	public function getHash() {
		if ( $this->isValid() ) {
			return $this->m_data->getHash();
		} else {
			return implode( "\t", $this->getErrors() );
		}
	}

	// Methods for parsing, serialisation, and display are not defined in this abstract class:
		// public function getShortWikiText($linked = null);
		// public function getShortHTMLText($linker = null);
		// public function getLongWikiText($linked = null);
		// public function getLongHTMLText($linker = null);
		// protected function parseUserValue($value);
		// public function getWikiValue();

	/**
	 * Return the stored data as a SMWSemanticData object. This is more conveniently to access than
	 * what getDBkeys() gives, but intended only for reading. It may not be safe to write to the returned
	 * object.
	 */
	public function getData() {
		return $this->m_data;
	}
	
}
