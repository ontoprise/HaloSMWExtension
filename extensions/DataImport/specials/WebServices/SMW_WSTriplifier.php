<?php

/**
 * @file
 * @ingroup DIWebServices
 *
 * @author Ingo Steinbauer
 *
 */

define("DI_PROVENANCE_GRAPH", "ProvenanceGraph");
define("DI_SWP_ASSERTED_BY", "swp:assertedBy");
define("DI_SWP_AUTHORITY", "swp:authority");
define("DI_DC_DATE", "dc:date");
define("DI_DC_DATE_FORMAT", "xsd:dateTime");
define("DI_XSD_PREFIX", "xsd");
define("DI_XSD_IRI", "<http://www.w3.org/2001/XMLSchema#>");
define("DI_SWP_PREFIX", "swp");
define("DI_SWP_IRI", "<http://www.w3.org/2004/03/trix/swp-2/>");
define("DI_DC_PREFIX", "dc");
define("DI_DC_IRI", "<http://purl.org/dc/elements/1.1/>");

define('DI_DATASOURCE_GRAPH', 'DataSourceInformationGraph');
define('DI_RDF_PREFIX', 'rdf');
define('DI_RDF_IRI', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#>');
define('DI_RDFS_PREFIX', 'rdfs');
define('DI_RDFS_IRI', '<http://www.w3.org/2000/01/rdf-schema#>');
define('DI_SHK_PREFIX', 'shk');
//todo: get shk namespace uri
define('DI_SHK_IRI', '<http://smw-house-keeping/>');



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
	public function triplify($wsResult, $subjectCreationPattern, $wsId, $triplify, $articleId, $createGraph){
		//preprocess triples and subjects
		list($tripleData, $subjects) = $this->createTriples($wsResult, $subjectCreationPattern, $wsId);
		
		if($triplify && defined( 'LOD_LINKEDDATA_VERSION')){
			global $IP;
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_TripleStoreAccess.php");
			
			//create triples
			$triples = array();
			foreach($tripleData as $td){
				$td['subject'] = $this->getSubjectIRI($td['subject']);
				$td['predicate'] = $this->getPropertyIRI($td['predicate']);
				$triple = new LODTriple($td['subject'], $td['predicate'], $td['object'], $td['type']);
				$triples[] = $triple;
			}
			
			$tsA = new LODTripleStoreAccess();
			$tsA->addPrefixes("PREFIX ".DI_XSD_PREFIX.":".DI_XSD_IRI);
			if($createGraph){
				//new graph only needs to be created if this is the first usage
				//of this ws in this article 
				$tsA->dropGraph($this->getGraphName($wsId, $articleId));
				$tsA->createGraph($this->getGraphName($wsId, $articleId));
				$this->dropProvenanceData($wsId, $articleId);
				$this->addProvenanceData($wsId, $articleId);	
			}
			
			$tsA->insertTriples($this->getGraphName($wsId, $articleId), $triples);
			$tsA->flushCommands();
		}
		
		return $subjects;		
	}
	
	/*
	 * Get the name of a ws usage graph
	 */
	private function getGraphName($wsId, $articleId){
		return $this->getWikiNS()."WS_".$wsId."_".$articleId;
	}
	
	private function getSubjectIRI($subject){
		return "<".$this->getWikiNS()."a#".$subject.">";
	}
	
	private function getPropertyIRI($property){
		return "<".$this->getWikiNS()."property#".$property.">";
	}
	
	/*
	 * Get the base URI of the SMW schema
	 */
	private function getWikiNS(){
		global $IP, $smwgTripleStoreGraph;
		$wikiNS = $smwgTripleStoreGraph;
		$wikiNS .= ($wikiNS[strlen($wikiNS)-1] == "/") ? '' : '/';
		return $wikiNS;
	}
	
	/*
	 * Creates the preprocessed triples as well as the corresponding subjects
	 */
	private function createTriples($wsResult, $subjectCreationPattern, $wsId){
		global $wgParser, $IP;
		require_once($IP."/extensions/SMWHalo/includes/storage/SMW_TS_Helper.php");
		
		//get number of rows and predicate types
		$lineCount = 0;
		$types = array();
		foreach($wsResult as $predicate => $resultPart){
			$lineCount = max($lineCount, count($resultPart));
			$title = Title::newFromText($predicate, SMW_NS_PROPERTY);
			$semData = smwfGetStore()->getSemanticData(SMWWikiPageValue::makePageFromTitle($title));
			$property = SMWPropertyValue::makeProperty('Has type');
			$value = $semData->getPropertyValues($property);
			$types[$predicate] = (count($value) > 0) ? 
				SMWDataValueFactory::findTypeID($value[0]->getShortWikiText()) : '';
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
			foreach($wsResult as $predicate => $objects){
				if(array_key_exists($i, $objects) && strlen($objects[$i]) > 0){
					if (array_key_exists($predicate, $subjectCreationPatternParts)){
						$subject = str_replace("?".$subjectCreationPatternParts[$predicate]."?", $objects[$i], $subject);
					}
					$triple = array();
					$triple['predicate'] = $predicate;
					$triple['object'] = $objects[$i];
					if(strlen($types[$predicate]) == 0){
						$triple['type'] = null;
					} else {
						$typeDataValue = SMWDataValueFactory::newTypeIDValue($types[$predicate], $objects[$i]);
						if($typeDataValue->isValid()){
							$triple['type'] = WikiTypeToXSD::getXSDType($types[$predicate]);
						} else {
							$triple['type'] = null;
						}
					}
					
					$tempTriples[] = $triple;
				} else if (array_key_exists($predicate, $subjectCreationPatternParts)){
					$subject = str_replace("?".$subjectCreationPatternParts[$predicate]."?", '', $subject);
				}
			}
			
			$subject = urlencode(trim($wgParser->replaceVariables($subject)));
			
			if(strlen($subject) > 0){
				foreach($tempTriples as $triple){
					$triple['subject'] = $subject;
					$triples[] = $triple;
				}
			}
			
			$subjects[] = "[[".$subject."]]";
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
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_TripleStoreAccess.php");
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
		
			$this->dropProvenanceData($wsId, $articleId);
			
			$tsA = new LODTripleStoreAccess();
			$tsA->dropGraph($this->getGraphName($wsId, $articleId));
			$tsA->flushCommands();
		}		
	}
	
	/*
	 * Adds provenance data for a WS Usage
	 */
	private function addProvenanceData($wsId, $articleId){
		$tsA = new LODTripleStoreAccess();
		$tsA->addPrefixes("PREFIX ".DI_SWP_PREFIX.":".DI_SWP_IRI);
		$tsA->addPrefixes("PREFIX ".DI_DC_PREFIX.":".DI_DC_IRI);
		$tsA->addPrefixes("PREFIX ".DI_XSD_PREFIX.":".DI_XSD_IRI);
			
		$tsA->createGraph($this->getWikiNS().DI_PROVENANCE_GRAPH);
		
		$dateTime = new DateTime();
		$dateTime = $dateTime->format('Y-m-d')."T".$dateTime->format('H:i:s');
		
		$triples = array();
		$triples[] = new LODTriple(
			"<".$this->getGraphName($wsId, $articleId).">", DI_SWP_ASSERTED_BY, "<".$this->getGraphName($wsId, $articleId)."Warrant>", "__objectURI");
		$triples[] = new LODTriple(
			"<".$this->getGraphName($wsId, $articleId)."Warrant>", DI_SWP_AUTHORITY, "<".$this->getWikiNS()."WS_".$wsId.">", "__objectURI");
		$triples[] = new LODTriple(
			"<".$this->getGraphName($wsId, $articleId)."Warrant>", DI_DC_DATE, $dateTime, "xsd:dateTime");	
		 
		$tsA->insertTriples($this->getWikiNS().DI_PROVENANCE_GRAPH, $triples);
		
		$tsA->flushCommands();	
	}
	
	/*
	 * Drop provenance data related to a WS usage
	 */
	private function dropProvenanceData($wsId, $articleId){
		$tsA = new LODTripleStoreAccess();
		
		$tsA->deleteTriples($this->getWikiNS().DI_PROVENANCE_GRAPH, 
			"<".$this->getGraphName($wsId, $articleId)."> ?p ?o", "<".$this->getGraphName($wsId, $articleId)."> ?p ?o");
		$tsA->deleteTriples($this->getWikiNS().DI_PROVENANCE_GRAPH, 
			"<".$this->getGraphName($wsId, $articleId)."Warrant> ?p ?o", "<".$this->getGraphName($wsId, $articleId)."Warrant> ?p ?o");
		
		$tsA->flushCommands();
	}
	
	/*
	 * This method removes a WS from the DataSourceDefinitionGraph
	 */
	private function removeWSFromDataSourceInformationGraph($wsId){
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			global $IP;
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_TripleStoreAccess.php");
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
			$tsA = new LODTripleStoreAccess();
			
			$tsA->deleteTriples($this->getWikiNS().DI_DATASOURCE_GRAPH, 
				$this->getSubjectIRI("WS_".$wsId)." ?p ?o", $this->getSubjectIRI("WS_".$wsId)." ?p ?o");
			
			$tsA->flushCommands();
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
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_TripleStoreAccess.php");
			require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_Triple.php");
		
			$tsA = new LODTripleStoreAccess();
			
			$tsA->createGraph($this->getWikiNS().DI_DATASOURCE_GRAPH);
			
			$tsA->addPrefixes("PREFIX ".DI_RDF_PREFIX.":".DI_RDF_IRI);
			$tsA->addPrefixes("PREFIX ".DI_RDFS_PREFIX.":".DI_RDFS_IRI);
			$tsA->addPrefixes("PREFIX ".DI_SHK_PREFIX.":".DI_SHK_IRI);
			$tsA->addPrefixes("PREFIX ".DI_XSD_PREFIX.":".DI_XSD_IRI);
			
			$triples = array();
			$triples[] = new LODTriple(
				$this->getSubjectIRI("WS_".$wsId), "rdf:type", "shk:DataSource", "__objectURI");
			$triples[] = new LODTriple(
				$this->getSubjectIRI("WS_".$wsId), "rdfs:label", Title::newFromID($wsId)->getFullText(), "xsd:string");
			
			$tsA->insertTriples($this->getWikiNS().DI_DATASOURCE_GRAPH, $triples);
			
			$tsA->flushCommands();
		}
	}
}