<?php

/**
 * @file
 * @ingroup DIWebServices
 *
 * @author Ingo Steinbauer
 *
 */
if(defined( 'LOD_LINKEDDATA_VERSION')){
	global $lodgIP;
	require_once("$lodgIP/includes/LODAdministration/LOD_AdministrationStore.php");
}

/*
 * This class provides the Connection between the Web Service component
 * of the Data Import Extension and the Triple Store, respectively the Linked
 * Data Extension
 */
class WSTriplifier {
	
	static private $instance;
	
	/**
	 * singleton
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/*
	 * This method is called if one uses a WS in an article and if the _triplify option was chosen
	 * or if one has chosen the special result part for investigating triple subjects.
	 * 
	 * This method returns the subjects which have been or would have been created.
	 */
	public function triplify($wsResult, $subjectCreationPattern, $wsId, $triplify, $articleId, $createGraph, $subjectCreationPatternParts, $previewTitle){
		//preprocess triples and subjects
		list($tripleData, $subjects) = $this->createTriples($wsResult, $subjectCreationPattern, $wsId, $subjectCreationPatternParts, $previewTitle);
		
		if($triplify && defined( 'LOD_LINKEDDATA_VERSION') && $articleId != 0){
			global $IP;
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_PersistentTripleStoreAccess.php");
			
			//create triples
			$triples = array();
			foreach($tripleData as $td){
				$td['subject'] = $this->getSubjectIRI($td['subject']);
				$td['property'] = $this->getPropertyIRI($td['property']);
				if($td['type'] == '__objectURI'){
					$td['object'] = $this->getSubjectIRI($td['object']);
				} else {
					$td['object'] = str_replace('"', '\\"', $td['object']);
				}
				$triple = new LODTriple($td['subject'], $td['property'], $td['object'], $td['type']);
				$triples[] = $triple;
			}
			
			$tsA = new LODPersistentTripleStoreAccess(true);
			
			$tsA->addPrefixes(LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes());
			
			if($createGraph){
				//new graph only needs to be created if this is the first usage
				//of this ws in this article 
				$tsA->dropGraph($this->getGraphName($wsId, $articleId));
				$tsA->deletePersistentTriples('di_ws',$wsId.'_'.$articleId.'_use');
				
				$tsA->createGraph($this->getGraphName($wsId, $articleId));
				
				$this->dropProvenanceData($wsId, $articleId);
				$this->addProvenanceData($wsId, $articleId);	
			}
			
			//echo('<pre>'.urldecode(print_r($triples, true)).'</pre>');
			
			$tsA = new LODPersistentTripleStoreAccess(true);
			$tsA->addPrefixes(TSNamespaces::getAllPrefixes());
			
			//todo: id = wsid + articleid
			$tsA->insertTriples($this->getGraphName($wsId, $articleId), $triples);
			$tsA->flushCommands('di_ws',$wsId.'_'.$articleId.'_use');
			
		}	
		
		return $subjects;		
	}
	
	/*
	 * Get the name of a ws usage graph
	 */
	private function getGraphName($wsId, $articleId){
		$lAS = LODAdministrationStore::getInstance();
		return $lAS->getSMWGraphsURI()."WS_".$wsId."_".$articleId;
	}
	
	private function getSubjectIRI($subject){
		$subject = str_replace('[', '', $subject);
		$subject = str_replace(']', '', $subject);
		$subject = str_replace('|', '', $subject);
		$subject = str_replace('<', '', $subject);
		$subject = str_replace('>', '', $subject);
		$subject = str_replace(':', '', $subject);
		$subject = urlencode($subject);
		
		$tsN = new TSNamespaces();
		$uri = $tsN->getAllNamespaces();
		$uri = $uri[NS_MAIN];
		
		return '<'.$uri.$subject.'>';
	}
	
	private function getDataSourceURI($source){
		return "smwDatasources:".$source;
	}
	
	private function getPropertyIRI($property){
		$tsN = new TSNamespaces();
		$uri = $tsN->getAllNamespaces();
		$uri = $uri[SMW_NS_PROPERTY];
		return '<'.$uri.$property.'>';
	}
	
	/*
	 * Get the base URI of the SMW schema
	 */
	public function getWikiNS(){
		global $IP, $smwgTripleStoreGraph;
		$wikiNS = $smwgTripleStoreGraph;
		$wikiNS .= ($wikiNS[strlen($wikiNS)-1] == "/") ? '' : '/';
		return $wikiNS;
	}
	
	/*
	 * Creates the preprocessed triples as well as the corresponding subjects
	 */
	private function createTriples($wsResult, $subjectCreationPattern, $wsId, $unwantedPropertys, $previewTitle){
		$unwantedPropertys = array_flip($unwantedPropertys);
		
		global $wgParser, $IP;
		require_once($IP."/extensions/SMWHalo/includes/storage/SMW_TS_Helper.php");
		
		$subjects = array();
		
		//get number of rows and property types
		$lineCount = 0;
		$types = array();
		
		foreach($wsResult as $propertyName => $resultPart){
			$lineCount = max($lineCount, count($resultPart));
			$title = Title::newFromText($propertyName, SMW_NS_PROPERTY);
			$semData = smwfGetStore()->getSemanticData(SMWWikiPageValue::makePageFromTitle($title));
			$property = SMWPropertyValue::makeProperty('Has_type');
			$value = $semData->getPropertyValues($property);
			 if(count($value) > 0) { 
				$fK = array_keys($value);
			 	$fK = $fK[0];
				@ $types[$propertyName] = ''.$value[$fK]->getShortWikiText();
				$types[$propertyName] = str_replace('http://www.w3.org/2001/XMLSchema#','xsd:', $types[$propertyName]);
				 //@ $types[$propertyName] = SMWDataValueFactory::findTypeID($value[$fK]->getShortWikiText()); 
			} else {
				$types[$propertyName] = '';
			} 
		}
		
		$triples = array();
		$allAliases = WebService::newFromId($wsId)->getAllResultPartAliases();
		$subjectCreationPatternParts = array();
		foreach($allAliases as $alias => $dc){
			if(strpos($subjectCreationPattern, "?".$alias."?") !== false){
				$alias = explode(".", $alias);
				$subjectCreationPatternParts[$alias[1]] = $alias[0].".".$alias[1];
			}
		}
		 
		for($i=0; $i < $lineCount; $i++){
			$tempTriples = array();
			$subject = $subjectCreationPattern;
			foreach($wsResult as $property => $objects){
				if(array_key_exists($i, $objects) && strlen($objects[$i]) > 0){
					if (array_key_exists($property, $subjectCreationPatternParts)){
						$subject = str_replace("?".$subjectCreationPatternParts[$property]."?", $objects[$i], $subject);
					}
					$triple = array();
					$triple['property'] = $property;
					$triple['object'] = $objects[$i];
					if(!array_key_exists($property, $types) || strlen($types[$property]) == 0){
						$triple['type'] = '__objectURI';
						$triple['object'] = trim($triple['object']);
					} else {
						//$typeDataValue = SMWDataValueFactory::newTypeIDValue($types[$property], $triple['object']);
						//if($typeDataValue->isValid()){
						//	$triple['type'] = WikiTypeToXSD::getXSDType($types[$property]);
						//} else {
						//	$triple['type'] = null;
						//}
						$triple['type'] = $types[$property];
					}
					
					if(!array_key_exists($property, $unwantedPropertys)){
						$tempTriples[] = $triple;
					}
				} else if (array_key_exists($property, $subjectCreationPatternParts)){
					$subject = str_replace("?".$subjectCreationPatternParts[$property]."?", '', $subject);
				}
			}
			
			
			
			if(is_string($previewTitle)){
				//we are in preview mode
				$t = Title::makeTitleSafe(0, $previewTitle);
				$popts = new ParserOptions();
				$wgParser->startExternalParse($t, $popts, Parser::OT_HTML);
	
				$subject = $wgParser->internalParse($subject);
				//$subject = $wgParser->doBlockLevels($subject, true);
				$subject = trim($subject);	
			} else {
					$subject = trim($wgParser->replaceVariables($subject));
			}
			
			if(strlen($subject) > 0){
				foreach($tempTriples as $triple){
					$triple['subject'] = $subject;
					$triples[] = $triple;
				}
			}
			if(strlen($subject)>0 && !is_string($previewTitle)) $subject = "[[".$subject."]]";
			$subjects[] = $subject;
		}
		
		return array($triples, $subjects);
	}
	
	/*
	 * This method is called if one removes a WS usage from an article. 
	 * The corresponding graph as well as provenance data is removed 
	 * from the Triple Store.
	 */
	public function removeWSUsage($wsId, $articleId){
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			global $IP;
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_PersistentTripleStoreAccess.php");
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
		
			$this->dropProvenanceData($wsId, $articleId);
			
			$tsA = new LODPersistentTripleStoreAccess(true);
			
			$tsA->addPrefixes(LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes());
			
			
			$tsA->dropGraph($this->getGraphName($wsId, $articleId));
			$tsA->flushCommands('di_ws',$wsId.'_'.$articleId.'_use');
			$tsA->deletePersistentTriples('di_ws',$wsId.'_'.$articleId.'_use');
		}		
	}
	
	/*
	 * Adds provenance data for a WS Usage
	 */
	private function addProvenanceData($wsId, $articleId){
		$tsA = new LODPersistentTripleStoreAccess(true);
		
		$tsA->addPrefixes(LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes());
		$tsA->addPrefixes(LODAdministrationStore::getInstance()->getProvenanceGraphPrefixes());
		$tsA->addPrefixes(TSNamespaces::getAllPrefixes());
		
		$lAS = LODAdministrationStore::getInstance();;
		$tsA->createGraph($lAS->getSMWGraphsURI().'ProvenanceGraph');
		
		$dateTime = new DateTime();
		$dateTime = $dateTime->format('Y-m-d')."T".$dateTime->format('H:i:s');
		
		$triples = array();
		$triples[] = new LODTriple(
			'<'.$this->getGraphName($wsId, $articleId).'>', 'rdf:type', "smw-lde:ImportGraph", "__objectURI");
		$triples[] = new LODTriple(
			'<'.$this->getGraphName($wsId, $articleId).'>', 'swp:assertedBy', "_:1", "__objectURI");
		$triples[] = new LODTriple(
			"_:1", 'rdf:type', "swp:Warrant", "__objectURI");
		$triples[] = new LODTriple(
			"_:1", 'swp:authority', $this->getDataSourceURI("WS_".$wsId), "__objectURI");
		$triples[] = new LODTriple(
			'<'.$this->getGraphName($wsId, $articleId).'>', 'smw-lde:created', $dateTime, "xsd:dateTime");	
		 
		$tsA->insertTriples($lAS->getSMWGraphsURI().'ProvenanceGraph', $triples);
		
		$tsA->flushCommands('di_ws',$wsId.'_'.$articleId.'_prov');	
	}
	
	/*
	 * Drop provenance data related to a WS usage
	 */
	private function dropProvenanceData($wsId, $articleId){
		$tsA = new LODPersistentTripleStoreAccess(true);
		
		$tsA->addPrefixes(LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes());
		
		$lAS = LODAdministrationStore::getInstance();;
		
		$dataSourceURI = LODAdministrationStore::getInstance()->getDataSourcesURI();
		
		$tsA->deleteTriples($lAS->getSMWGraphsURI().'ProvenanceGraph', 
			'<'.$this->getGraphName($wsId, $articleId).'> ?p ?o. ?sw ?x <'.$dataSourceURI.'WS_'.$wsId.'>. ?sw ?y ?z .'
			, '<'.$this->getGraphName($wsId, $articleId).'> ?p ?o. ?sw ?y ?z.');
		
		$tsA->flushCommands('di_ws',$wsId.'_'.$articleId.'_prov');
		$tsA->deletePersistentTriples('di_ws',$wsId.'_'.$articleId.'_prov');
	}
	
	/*
	 * This method removes a WS from the DataSourceDefinitionGraph
	 */
	private function removeWSFromDataSourceInformationGraph($wsId){
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			global $IP;
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_PersistentTripleStoreAccess.php");
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
			
			$tsA = new LODPersistentTripleStoreAccess(true);
			
			$tsA->addPrefixes(LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes());
			
			$lAS = LODAdministrationStore::getInstance();;
			
			//todo:deal with persistency id=wsid 
			$tsA->deleteTriples($lAS->getSMWGraphsURI().'DataSourceInformationGraph', 
				$this->getDataSourceURI("WS_".$wsId)." ?p ?o", $this->getDataSourceURI("WS_".$wsId)." ?p ?o");
			
			$tsA->flushCommands('di_ws',$wsId.'_dsinfo');
			$tsA->deletePersistentTriples('di_ws',$wsId.'_dsinfo');
		}
	}
	
	/*
	 * This method is called if one edits or deletes a WWSD.
	 * All graphs, provenance data and data in the 
	 * DataSourceInformation graph is removed. This methhod is 
	 * called by the WebServiceManager.
	 */
	public function removeWS($wsId, $articleIds){
		foreach($articleIds as $articleId){
			$this->removeWSUsage($wsId, $articleId);
		}
		
		$this->removeWSFromDataSourceInformationGraph($wsId);
	}
	
	/*
	 * This method is called if one edits or creates a new WWSD.
	 * The WS is added to the DataSourceInformationGraph. This methhod is 
	 * called by the WebServiceManager.
	 */
	public function addWSAsDataSource($wsId){
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			global $IP;
			
			$this->removeWSFromDataSourceInformationGraph($wsId);
			
			//require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_PersistentTripleStoreAccess.php");
			//require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
		
			$tsA = new LODPersistentTripleStoreAccess(true);
			
			$lAS = LODAdministrationStore::getInstance();
			
			$tsA->createGraph($lAS->getSMWGraphsURI().'DataSourceInformationGraph');
			
			$tsA->addPrefixes(TSNamespaces::getAllPrefixes());
			
			$tsA->addPrefixes(LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes());
			
			$triples = array();
			$triples[] = new LODTriple(
				$this->getDataSourceURI("WS_".$wsId), "rdf:type", "smw-lde:Datasource", "__objectURI");
			$triples[] = new LODTriple(
				$this->getDataSourceURI("WS_".$wsId), "smw-lde:label", Title::newFromID($wsId)->getFullText(), "xsd:string");
			$triples[] = new LODTriple(
				$this->getDataSourceURI("WS_".$wsId), "smw-lde:ID", Title::newFromID($wsId)->getFullText(), "xsd:string");
			
			//todo: deal with persistency id=wsid
			$tsA->insertTriples($lAS->getSMWGraphsURI().'DataSourceInformationGraph', $triples);
			
			$tsA->flushCommands('di_ws',$wsId.'_dsinfo');
		}
	}
}