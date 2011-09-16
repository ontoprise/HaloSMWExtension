<?php
/**
 * @file
 * @ingroup LinkedData
 */
global $smwgHaloIP;
require_once($smwgHaloIP."/includes/storage/SMW_RESTWebserviceConnector.php");

/**
 * This is the implementation for accessing the mapping endpoint at the TSC.
 *
 * Note: There is no SOAP implementation available, because the TSC does not
 * support SOAP any more.
 *
 * @author Kai, ingo Steinbauer
 * Date: 28.5.2010
 *
 */
class LODMappingTripleStore implements ILODMappingStore {

	/**
	 * Deletes all mappings that are stored in the article with the name 
	 * $articleName. Also calls remove all Mappings in order to delte
	 * mappings from TSC
	 * 
	 * @param string $articleName
	 * 		Fully qualified name of an article
	 */
	public function removeAllMappingsFromPage($articleName) {
		$db = LODStorage::getDatabase();

		$sourceTargetPairs = $db->getMappingsInArticle($articleName);
		if (isset($sourceTargetPairs)) {
			foreach ($sourceTargetPairs as $stp) {
				$source = $stp[0];
				$target = $stp[1];
				$this->removeAllMappings($source, $target, $articleName);
			}
			
			$db->removeAllMappingsFromPage($articleName);
		}
	}
	
	
	/**
	 * Deletes all mappings having source and range $source and $target.
	 *
	 * @param string source
	 * 		ID of the source. If <null>, all mappings with the to the given target
	 * 		are deleted.
	 * @param string target
	 * 		ID of the target. If <null>, all mappings from the given source are 
	 * 		deleted.
	 * @param string $persistencyLayerId
	 * 		Must be the same Id that was used when storing the triples.	
	 * 
	 */
	public function removeAllMappings($source, $target, $persistencyLayerId) {
		$tripleStoreAccess = new TSCPersistentTripleStoreAccess(true);
		$pm = TSCPrefixManager::getInstance();
		
		$property = 'smw-lde:linksFrom';
		$property = $pm->makeAbsoluteURI($property);
		$source = 'smwDatasources:'.$source; 
		$source = $pm->makeAbsoluteURI($source);
		$where = '?mapping '.$property.' '.$source.'. ';
		
		$property = 'smw-lde:linksTo';
		$property = $pm->makeAbsoluteURI($property);
		$target = 'smwDatasources:'.$target; 
		$target = $pm->makeAbsoluteURI($target);
		$where .= '?mapping '.$property.' '.$target.'. ';
				
		$graph = 'smwGraphs:MappingRepository';
		$graph = $pm->makeAbsoluteURI($graph, false);
		
		$tripleStoreAccess->deleteTriples($graph, $where, $where);
		$tripleStoreAccess->flushCommands();
		
		$tripleStoreAccess->deletePersistentTriples('MappingStore', $persistencyLayerId);
	}

	/**
	 * Deletes a mapping.
	 *
	 * @param string $uri
	 * 		The uri of the mapping to be removed
	 */
    public function removeMapping($uri) {
        $tripleStoreAccess = new TSCPersistentTripleStoreAccess(true);
		$pm = TSCPrefixManager::getInstance();

        $graph = 'smwGraphs:MappingRepository';
		$graph = $pm->makeAbsoluteURI($graph, false);
		$where = "?s ?p ?o";
		$template = "<$uri> ?p ?o";

		$tripleStoreAccess->deleteTriples($graph, $where, $template);
		return $tripleStoreAccess->flushCommands();
    }
	
	
	/**
	 * Adds the given mapping to the store. Already existing mappings with the
	 * same source and target are not replaced but enhanced.
	 * 
	 * @param LODMapping $mapping
	 * 		This object defines a mapping for a linked data source.
	 * 
	 * @return bool 
	 * 		<true> if the mapping was stored successfully or
	 * 		<false> otherwise
	 * 	
	 * @param string persistencyLayerId
	 * 		An id, that adresses the tripples of this mapping in the 
	 * 		persistency layer. Normally the article name is used.
	 */
	public function addMapping(LODMapping $mapping, $persistencyLayerId) {
		$triples = $mapping->getTriples();
		
		$tripleStoreAccess = new TSCPersistentTripleStoreAccess(true);
		$pm = TSCPrefixManager::getInstance();
		
		$graph = 'smwGraphs:MappingRepository';
		$graph = $pm->makeAbsoluteURI($graph, false);
		
		$tripleStoreAccess->addPrefixes($pm->getSPARQLPrefixes(array('xsd')));
		$tripleStoreAccess->insertTriples($graph, $triples);
		
		$result = $tripleStoreAccess->flushCommands('MappingStore', $persistencyLayerId);
		
		return $result;
	}
	
	
	/**
	 * As mappings are stored in articles the system must know which mappings
	 * (i.e. source-target pairs) are stored in an article.
	 * This function stores a source-target pairs for an article.
	 * @param string $articleName
	 * 		Fully qualified name of an article
	 * @param string $source
	 * 		Name of the mapping source
	 * @param string $target
	 * 		Name of the mapping target
	 */
	public function addMappingToPage($articleName, $source, $target) {
		$db = LODStorage::getDatabase();	
		
		$db->addMappingToPage($articleName, $source, $target);
	}
	
	
	/**
	 * Returns an array of source-target pairs of mappings that are stored in the
	 * article with the name $articleName
	 * @param string $articleName
	 * 		Fully qualified name of an article
	 * @return array(array(string source, string $target))
	 */
	public function getMappingsInArticle($articleName, $askTSC = false) {
		$db = LODStorage::getDatabase();
		$sourceTargetPairs = $db->getMappingsInArticle($articleName);
		 
		if(!$askTSC){
			return $sourceTargetPairs;
		}  else {
			$pairs = array();
			foreach($sourceTargetPairs as $pair){
				$pairs[$pair[0]][] = $pair[1];
			}
			
			$mappings = array();
			foreach($pairs as $source => $targets){
				foreach($targets as $target){
					$mappings = array_merge($mappings,
						$this->getAllMappings($source, $target));		
				}
			}
			
			return $mappings;
		}
	}
	
	
	/**
	 * Loads all definitions of mappings between $source and $target.
	 *
	 * @param string source
	 * 		ID of the source. If <null>, all mappings with the to the given target
	 * 		are returned.
	 * @param string target
	 * 		ID of the target. If <null>, all mappings from the given source are 
	 * 		returned.
	 * If both parameters are <null>, all existing mappings are returned.
	 * 
	 * @return array<LODMapping>
	 * 		The definitions of matching mappings or an empty array, if there are 
	 * 		no such mappings.
	 */
	public function getAllMappings($source = null, $target = null, $typeId = null) {
		$tripleStoreAccess = new TSCPersistentTripleStoreAccess(true);
		$pm = TSCPrefixManager::getInstance();
		
		$graph = 'smwGraphs:MappingRepository';
		$graph = $pm->makeAbsoluteURI($graph, false);
				
		$query = LODMapping::getQueryString($source, $target);
		
		$queryResult = $tripleStoreAccess->queryTripleStore($query, $graph);
		
		$mappings = array();
		if($queryResult instanceof TSCSparqlQueryResult){
			foreach($queryResult -> getRows() as $row){
				$mappings[$row->getResult('mapping')->getValue()][$row->getResult('p')->getValue()][] = 
					$row->getResult('o')->getValue();
			}
		}
		
		foreach($mappings as $subjectURI => $mappingData){
			$mappings[$subjectURI] = 
				LODMapping::createMappingFromSPARQLResult($mappingData, $subjectURI);
		}
		
		return $mappings;
	}
	
	
	/**
	 * Loads a mapping definition based on the mapping URI
	 *
	 * @param string mappingUri
	 * 
	 * @return LODMapping
	 * 		The definition of matching mappings or null
	 */
	public function getMapping($mappingUri) {
		$tripleStoreAccess = new TSCPersistentTripleStoreAccess(true);
		$pm = TSCPrefixManager::getInstance();
		
		$graph = 'smwGraphs:MappingRepository';
		$graph = $pm->makeAbsoluteURI($graph, false);
				
		$query = "SELECT ?p ?o WHERE { <$mappingUri> ?p ?o }";
		
		$queryResult = $tripleStoreAccess->queryTripleStore($query, $graph);
		
		$mappingData = array();
		if($queryResult instanceof TSCSparqlQueryResult){
			foreach($queryResult -> getRows() as $row){
				$mappingData[$row->getResult('p')->getValue()][] = 
					$row->getResult('o')->getValue();
			}
			return LODMapping::createMappingFromSPARQLResult($mappingData, $mappingUri);
		} else {
			return null;
		}
	}
	
	/**
	 * Checks if a mapping exists in the Mapping Store
	 *
	 * @param LODMapping mapping
	 * 		The mapping to check for
	 * 
	 * @return bool
	 * 	<true>, if the mapping exists
	 * 	<false> otherwise
	 * 
	 */
	public function existsMapping($mapping){
		if($mapping instanceof LODSILKMapping){
			$mappingType = 'SILK';
		} else {
			$mappingType = 'R2R';
		}
		
		$mappings = $this->getAllMappings($mapping->getSource(), $mapping->getTarget(), $mappingType);
		
		foreach($mappings as $existsCandidate){
			if($mapping->equals($existsCandidate)){
				return true;
			}
		}
		return false;
		
		
	}

}






