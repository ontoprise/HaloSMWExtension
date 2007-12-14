<?php
/*
 * Created on 14.12.2007
 *
 * Author: kai
 */
 global $smwgHaloIP;
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");
 
 define('LINE_FEED', "\n");
 
 class ExportOntologyBot extends GardeningBot {
 	
 	// global log which contains wiki-markup
 	private $globalLog;
 	private $mapWikiTypeToXSD;
 	private $numOfCategories;
 	
 	function __construct() {
 		parent::GardeningBot("smw_exportontologybot");
 		$this->globalLog = "== Export date:  ==\n\n";
 		
 		$this->mapWikiTypeToXSD['_int'] = 'integer';
 		$this->mapWikiTypeToXSD['_str'] = 'string';
 		$this->mapWikiTypeToXSD['_flt'] = 'float';
 		$this->mapWikiTypeToXSD['_boo'] = 'boolean';
 	}
 	
 	public function getHelpText() {
 		return wfMsg('smw_gard_export_docu');
 	}
 	
 	public function getLabel() {
 		return wfMsg($this->id);
 	}
 	
 	public function allowedForUserGroups() {
 		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS);
 		//return array();
 	}
 	
 	/**
 	 * Returns an array mapping parameter IDs to parameter objects
 	 */
 	public function createParameters() {
 		$param1 = new GardeningParamString('GARD_EO_FILENAME', "Export file", SMW_GARD_PARAM_REQUIRED);
 		$param2 = new GardeningParamBoolean('GARD_EO_ONLYSCHEMA', "Export only schema", SMW_GARD_PARAM_OPTIONAL, false);
 		return array($param1, $param2);
 	}
 	
 	/**
 	 * Import ontology
 	 * Do not use echo when it is not running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		$this->globalLog = "";
 		// do not allow to start synchronously.
 		if (!$isAsync) {
 			return "Export ontology bot should not be done synchronously!";
 		}
 		echo "\nStart export...";
 		$outputFile = urldecode($paramArray['GARD_EO_FILENAME']);
 		
 		$handle = fopen($outputFile,"wb");
 		$this->writeHeader($handle);
 		
 		$db =& wfGetDB( DB_MASTER );
 		$this->numOfCategories = $db->selectField($db->tableName('page'), 'COUNT(page_id)', 'page_namespace = '.NS_CATEGORY);
 		print "\nExport Schema...\n";
 		$this->exportSchema($handle);
 		print "\nExport properties without domain...\n";
 		$this->exportPropertiesWithoutDomain($handle);
 		
 		if (!array_key_exists('GARD_EO_ONLYSCHEMA', $paramArray)) {
 			print "\nExport Instances...\n";
 			$this->exportInstances($handle);
 		}
 		$this->writeFooter($handle);
	 	fclose($handle); 
	 	print "\nExport successful!";
 	}
 	
 	private function writeHeader($filehandle) {
 		$header = '<!DOCTYPE owl ['.LINE_FEED;
   		$header .=	'<!ENTITY xsd  "http://www.w3.org/2001/XMLSchema#" > ]>'.LINE_FEED;
		$header .=	'<rdf:RDF'.LINE_FEED;
    	$header .=	'xmlns:a   ="http://www.halowiki.org#"'.LINE_FEED;
    	$header .=	'xmlns:cat ="http://www.halowiki.org/category#"'.LINE_FEED;
		$header .=	'xmlns:prop ="http://www.halowiki.org/property#"'.LINE_FEED;				
    	$header .=	'xmlns:owl ="http://www.w3.org/2002/07/owl#"'.LINE_FEED;
		$header .=	'xmlns:rdf ="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.LINE_FEED;
		$header .=	'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">'.LINE_FEED;
		$header .=	'<owl:Ontology rdf:about="www.halowiki.org">'.LINE_FEED;
		$header .=	'	<rdfs:comment>HaloWiki Export</rdfs:comment>'.LINE_FEED;
		$header .=	'	<rdfs:label>HaloWiki Ontology</rdfs:label>'.LINE_FEED;
		$header .=	'</owl:Ontology>'.LINE_FEED;
		fwrite($filehandle, $header);
 	}
 	
 	private function writeFooter($filehandle) {
 		$footer = '</rdf:RDF>'.LINE_FEED;
 		fwrite($filehandle, $footer);
 	}
 	
 	/**
 	 * Exports schema, i.e. categories with domain properties
 	 * 
 	 * @param $filehandle handle for a text file.
 	 */
 	private function exportSchema($filehandle) {
 		$rootCategories = smwfGetSemanticStore()->getRootCategories();
 		$counter = 0;
 		$owlCat = '<owl:Class rdf:about="http://www.halowiki.org/category#DefaultRootConcept">'.LINE_FEED;
		$owlCat .= '	<rdfs:label xml:lang="en">DefaultRootConcept</rdfs:label>'.LINE_FEED;
		$owlCat .= '</owl:Class>'.LINE_FEED;
		fwrite($filehandle, $owlCat);
 		foreach($rootCategories as $rc) {
 			$owlCat = '<owl:Class rdf:about="http://www.halowiki.org/category#'.$rc->getDBkey().'">'.LINE_FEED;
			$owlCat .= '	<rdfs:label xml:lang="en">'.$rc->getText().'</rdfs:label>'.LINE_FEED;
			$owlCat .= '	<rdfs:subClassOf rdf:resource="http://www.halowiki.org/category#DefaultRootConcept" />'.LINE_FEED;
			$owlCat .= '</owl:Class>'.LINE_FEED;
			fwrite($filehandle, $owlCat);
			$visitedNodes = array();
			
			$this->exportSubcategories($filehandle, $rc, $visitedNodes, $counter);
 		}
 	}
 	
 	/**
 	 * Exports all properties which do not have a domain category.
 	 * Those properties are added to 
 	 * 
 	 * @param $filehandle handle for a text file.
 	 */
 	private function exportPropertiesWithoutDomain($filehandle) {
 		$db =& wfGetDB( DB_MASTER );
 		$page = $db->tableName('page');
 		$smw_nary = $db->tableName('smw_nary');
 		$defaultRootCategory = Title::newFromText('DefaultRootConcept', NS_CATEGORY);
 		$res = $db->query('SELECT DISTINCT page_title FROM '.$page.' p LEFT JOIN '.$smw_nary.' ON p.page_id=subject_id ' .
 					'AND attribute_title = '.$db->addQuotes(smwfGetSemanticStore()->domainRangeHintRelation->getDBkey()).
					' WHERE subject_id IS NULL AND page_namespace = '.SMW_NS_PROPERTY);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$property = Title::newFromText($row->page_title, SMW_NS_PROPERTY);
				$this->exportProperty($filehandle, $defaultRootCategory, $property);
				
			}
		}
		$db->freeResult($res);	
 	}
 	
 	/**
 	 * Exports all instances which have at least one category.
 	 * 
 	 * @param $filehandle handle for a text file.
 	 */
 	private function exportInstances($filehandle) {
 		$instances = smwfGetSemanticStore()->getPages(array(NS_MAIN));
 		$counter = 0;
 		$numOfInstances = count($instances);
 		foreach($instances as $inst) {
	 		$categories = smwfGetSemanticStore()->getCategoriesForInstance($inst);
	 		if (count($categories) == 0) {
	 			$counter++;
	 			 continue;
	 		}
 			$owlInst = '<owl:Thing rdf:about="http://www.halowiki.org#'.smwfXMLContentEncode($inst->getDBkey()).'">'.LINE_FEED;
 			foreach($categories as $category) {
 				$owlInst .= '	<rdf:type rdf:resource="http://www.halowiki.org/category#'.smwfXMLContentEncode($category->getDBkey()).'"/>'.LINE_FEED;
 			}
 			$properties = smwfGetStore()->getProperties($inst);
 			foreach($properties as $p) {
 				$values = smwfGetStore()->getPropertyValues($inst, $p);
 				foreach($values as $smwValue) {
					if ($smwValue instanceof SMWWikiPageValue) {
						$target = $smwValue->getTitle();
						preg_match("/[\d\w_]+/", $p->getDBkey(), $matches);
						if ($matches[0] == $p->getDBkey()) {
							$propertyName = preg_replace("/\"/","&quot;", $p->getDBkey());
							
							if ($target!=NULL) $owlInst .= '	<prop:'.$propertyName.' rdf:resource="http://www.halowiki.org#'.$target->getDBkey().'"/>'.LINE_FEED;
						}
		 			} else {
		 				preg_match("/[\d\w_]+/", $p->getDBkey(), $matches);
						if ($matches[0] == $p->getDBkey()) {
							$propertyName = preg_replace("/\"/","&quot;", $p->getDBkey());
		 					$xsdType = $this->mapWikiTypeToXSD[$smwValue->getTypeID()] == NULL ? 'string' : $this->mapWikiTypeToXSD[$smwValue->getTypeID()];
		 					$content = preg_replace("/\x07/","", smwfXMLContentEncode($smwValue->getXSDValue()));
		 					$owlInst .= '	<prop:'.$propertyName.' rdf:datatype="http://www.w3.org/2001/XMLSchema#'.$xsdType.'">'.$content.'</prop:'.$propertyName.'>'.LINE_FEED;
						}	
		 			}
 				}
 			}
 			$owlInst .= '</owl:Thing>'.LINE_FEED;
 			fwrite($filehandle, $owlInst);
	 		$counter++;
 			if ($counter % 50 == 0 && $counter < $numOfInstances) {
 				print "\x08\x08\x08\x08\x08".number_format($counter/$numOfInstances*100, 0)."% ";
 			}
 		}
 		print "\x08\x08\x08\x08\x08".number_format($counter/$numOfInstances*100, 0)."% ";
 	}
 	
 	private function exportSubcategories($filehandle, $superCategory, array & $visitedNodes, & $counter) {
 		$directSubcategories = smwfGetSemanticStore()->getDirectSubCategories($superCategory);
 		array_push($visitedNodes, $superCategory->getArticleID());
 		foreach($directSubcategories as $c) {
 			if (in_array($c->getArticleID(), $visitedNodes)) {
 				return;
 			}
 			$directSuperCategories = smwfGetSemanticStore()->getDirectSuperCategories($c);
 			
 			$owlCat = '<owl:Class rdf:about="http://www.halowiki.org/category#'.$c->getDBkey().'">'.LINE_FEED;
			$owlCat .= '	<rdfs:label xml:lang="en">'.$c->getText().'</rdfs:label>'.LINE_FEED;
			foreach($directSuperCategories as $sc) {
				$owlCat .= '	<rdfs:subClassOf rdf:resource="http://www.halowiki.org/category#'.$sc->getDBkey().'" />'.LINE_FEED;
			}
			
			$owlCat .= '</owl:Class>'.LINE_FEED;
			
			$directProperties = smwfGetSemanticStore()->getDirectPropertiesByCategory($c);
			foreach($directProperties as $p) {	
				$this->exportProperty($filehandle, $c, $p);	
			}
			
			fwrite($filehandle, $owlCat);
			if ($counter < $this->numOfCategories) {
				$counter++;
				print "\x08\x08\x08\x08\x08".number_format($counter/$this->numOfCategories*100, 0)."% ";
			}
			
			$this->exportSubcategories($filehandle, $c, $visitedNodes, $counter);
 		}
 		array_pop($visitedNodes);
 	}
 	
 	private function exportProperty($filehandle, $domainCategory, $rp) {
 		
 		
 			$type = smwfGetStore()->getSpecialValues($rp, SMW_SP_HAS_TYPE);
 			if ($type == NULL || count($type) == 0) {
 				// default type: binary relation
 				$firstType = '_wpg';
 			} else {
 				$firstType = $type[0]->getID;
 			}
 			if ($firstType == '_wpg') {
 				$domain = $range = "";
 				$domainRange = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->domainRangeHintRelation);
 				if ($domainRange != NULL || count($domainRange) > 0) {
 					$dvs = $domainRange[0]->getDVs();
 					$range = $dvs[1] != NULL ? $dvs[1]->getTitle()->getDBkey() : "";
 				}
 				$owlCat = '<owl:ObjectProperty rdf:about="http://www.halowiki.org/property#'.$rp->getDBkey().'"/>'.LINE_FEED;
 				$owlCat .= '	<owl:Class rdf:about="http://www.halowiki.org/category#'.$domainCategory->getDBkey().'">'.LINE_FEED;
 				$owlCat .= '		<rdfs:subClassOf>'.LINE_FEED;
 				$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
				$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$rp->getDBkey().'" />'.LINE_FEED;
				if ($range != '') $owlCat .= '				<owl:allValuesFrom rdf:resource="http://www.halowiki.org/category#'.$range.'" />'.LINE_FEED;
				$owlCat .= '			</owl:Restriction>'.LINE_FEED;
				$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
				$owlCat .= '</owl:Class>'.LINE_FEED;
 			} else {
 				$xsdType = $this->mapWikiTypeToXSD[$firstType] == NULL ? 'string' : $this->mapWikiTypeToXSD[$firstType];
 				
				$owlCat = '<owl:DatatypeProperty rdf:about="http://www.halowiki.org/property#'.$rp->getDBkey().'"/>'.LINE_FEED;
 				$owlCat .= '	<owl:Class rdf:about="http://www.halowiki.org/category#'.$domainCategory->getDBkey().'">'.LINE_FEED;
 				$owlCat .= '		<rdfs:subClassOf>'.LINE_FEED;
 				$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
				$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$rp->getDBkey().'" />'.LINE_FEED;
				$owlCat .= '			<owl:allValuesFrom rdf:resource="http://www.w3.org/2001/XMLSchema#'.$xsdType.'" />'.LINE_FEED;
				$owlCat .= '		</owl:Restriction>'.LINE_FEED;
				$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
				$owlCat .= '	</owl:Class>'.LINE_FEED;
 			}
 			
 			fwrite($filehandle, $owlCat);
 		
 	}
 	
 	
 	
 }
 
 new ExportOntologyBot();
?>
