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
 	
 	
 	private $mapWikiTypeToXSD;
 	
 	private $numOfCategories;
 	private $numOfInstances;
 	private $numOfProperties;
 	
 	function __construct() {
 		parent::GardeningBot("smw_exportontologybot");
 		 		
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
 	}
 	
 	/**
 	 * Returns an array of GardeningParamObjects
 	 */
 	public function createParameters() {
 		$param1 = new GardeningParamString('GARD_EO_FILENAME', "Export file", SMW_GARD_PARAM_REQUIRED);
 		$param2 = new GardeningParamBoolean('GARD_EO_ONLYSCHEMA', "Export only schema", SMW_GARD_PARAM_OPTIONAL, false);
 		return array($param1, $param2);
 	}
 	
 	/**
 	 * Export ontology
 	 * DO NOT use echo when it is not running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		
 		// do not allow to start synchronously.
 		if (!$isAsync) {
 			return "Export ontology bot should not be done synchronously!";
 		}
 		echo "\nStart export...";
 		$outputFile = urldecode($paramArray['GARD_EO_FILENAME']);
 		
 		$handle = fopen($outputFile,"wb");
 		$this->writeHeader($handle);
 		
 		$db =& wfGetDB( DB_MASTER );
 		$this->numOfCategories = $db->selectField($db->tableName('page'), 'COUNT(page_id)', 'page_namespace = '.NS_CATEGORY) - 2; // 2 builtin categories
 		
 		print "\n\nExport Categories...\n";
 		$this->exportCategories($handle);
 		print "\n\nExport properties...\n";
 		$this->exportProperties($handle);
 		
 		
 		if (!array_key_exists('GARD_EO_ONLYSCHEMA', $paramArray)) {
 			print "\n\nExport Instances...\n";
 			$this->exportInstances($handle);
 		}
 		
 		$this->writeFooter($handle);
	 	fclose($handle);
	 	 
	 	$successMessage = "\n\n --- Export to '$outputFile' was successful! ---\n\n";
	 	
	 	return $successMessage;
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
 	 * Exports categories
 	 * 
 	 * @param $filehandle handle for a text file.
 	 */
 	private function exportCategories($filehandle) {
 		$rootCategories = smwfGetSemanticStore()->getRootCategories();
 		$counter = 0;
 		$owlCat = '<owl:Class rdf:about="http://www.halowiki.org/category#DefaultRootConcept">'.LINE_FEED;
		$owlCat .= '	<rdfs:label xml:lang="en">DefaultRootConcept</rdfs:label>'.LINE_FEED;
		$owlCat .= '</owl:Class>'.LINE_FEED;
		fwrite($filehandle, $owlCat);
 		foreach($rootCategories as $rc) {
 			
 			if (smwfGetSemanticStore()->transitiveCat->equals($rc) 
 					|| smwfGetSemanticStore()->symetricalCat->equals($rc)) {
 						// ignore builtin categories
 						continue;
 			}
 			
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
 	 /*
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
 	}*/
 	
 	/**
 	 * Exports all instances. 
 	 * Instances without categories will be added to DefaultRootConcept
 	 * 
 	 * @param $filehandle handle for a text file.
 	 */
 	private function exportInstances($filehandle) {
 		$instances = smwfGetSemanticStore()->getPages(array(NS_MAIN));
 		$counter = 0;
 		$this->numOfInstances = count($instances);
 		foreach($instances as $inst) {
 			if ($counter % 10 == 0) {
 				$this->printProgress($counter, $this->numOfInstances);
 			}
	 		$categories = smwfGetSemanticStore()->getCategoriesForInstance($inst);
 			$owlInst = '<owl:Thing rdf:about="http://www.halowiki.org#'.smwfXMLContentEncode($inst->getDBkey()).'">'.LINE_FEED;
	 		if (count($categories) == 0) {
	 			$owlInst .= '	<rdf:type rdf:resource="http://www.halowiki.org/category#DefaultRootConcept"/>'.LINE_FEED;
	 		} else {
	 			foreach($categories as $category) {
	 				$owlInst .= '	<rdf:type rdf:resource="http://www.halowiki.org/category#'.smwfXMLContentEncode($category->getDBkey()).'"/>'.LINE_FEED;
	 			}
	 		}
 			$properties = smwfGetStore()->getProperties($inst);
 			
 			foreach($properties as $p) {
 				$propertyLocal = ExportOntologyBot::makeXMLExportId($p->getDBkey());
 				if ($propertyLocal == NULL) continue;
 				$values = smwfGetStore()->getPropertyValues($inst, $p);
 				foreach($values as $smwValue) {
					if ($smwValue instanceof SMWWikiPageValue) {
						$target = $smwValue->getTitle();
						
							$targetLocal = preg_replace("/\"/", "", $target->getDBkey());
							
							if ($target!=NULL) $owlInst .= '	<prop:'.$propertyLocal.' rdf:resource="http://www.halowiki.org#'.$targetLocal.'"/>'.LINE_FEED;
						
		 			} else {
		 				
							
							if ($smwValue->getUnit() != NULL && $smwValue->getUnit() != '') {
								$owlInst .= $this->exportSI($p, $smwValue);
							} else {
			 					$xsdType = $this->mapWikiTypeToXSD[$smwValue->getTypeID()] == NULL ? 'string' : $this->mapWikiTypeToXSD[$smwValue->getTypeID()];
			 					$content = preg_replace("/\x07/","", smwfXMLContentEncode($smwValue->getXSDValue()));
			 					$owlInst .= '	<prop:'.$propertyLocal.' rdf:datatype="http://www.w3.org/2001/XMLSchema#'.$xsdType.'">'.$content.'</prop:'.$propertyLocal.'>'.LINE_FEED;
							}
						
		 			}
 				}
 			}
 			$owlInst .= '</owl:Thing>'.LINE_FEED;
 			fwrite($filehandle, $owlInst);
	 		$counter++;
 			
 		}
 		$this->printProgress($counter, $this->numOfInstances);
 	}
 	
 	/**
 	 * Exports all properties with their
 	 *  1. Domain and Type/Range (also multiple domains/ranges)
 	 *  2. Cardinality (min/max)
 	 *  3. Symmetry and Transitivity
 	 *  4. Inverse relations
 	 *  5. Super properties
 	 */
 	private function exportProperties($filehandle) {
 		
 		$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		$counter = 0;
 		$this->numOfProperties = count($properties);
 		foreach($properties as $rp) {
 			$counter++;
 			
 			if (smwfGetSemanticStore()->domainRangeHintRelation->equals($rp) 
 					|| smwfGetSemanticStore()->minCard->equals($rp) 
 					|| smwfGetSemanticStore()->maxCard->equals($rp)
 					|| smwfGetSemanticStore()->inverseOf->equals($rp)) {
 						// ignore builtin properties
 						continue;
 			}
 			
 			$maxCards = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->maxCard);
 			if ($maxCards != NULL || count($maxCards) > 0) {
 				$maxCard = intval($maxCards[0]->getXSDValue());
 				
 			} else {
 				$maxCard = NULL;
 			}
 			
 			$minCards = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->minCard);
 			if ($minCards != NULL || count($minCards) > 0) {
 				$minCard = intval($minCards[0]->getXSDValue());
 				
 			} else {
 				$minCard = NULL;
 			}
 			
 			$directSuperProperties = smwfGetSemanticStore()->getDirectSuperProperties($rp);
 			
 			$type = smwfGetStore()->getSpecialValues($rp, SMW_SP_HAS_TYPE);
 			if ($type == NULL || count($type) == 0) {
 				// default type: binary relation
 				$firstType = '_wpg';
 			} else {
 				$firstType = $type[0]->getID;
 			}
 			if ($firstType == '_wpg') {
 				$owlCat = $this->exportObjectProperty($rp, $directSuperProperties, $maxCard, $minCard);
 			} else { //TODO: how to handle n-aries? for the moment export them as string attributes
 				$owlCat = $this->exportDatatypeProperty($rp, $firstType, $directSuperProperties, $maxCard, $minCard);
 			}
 			
 			fwrite($filehandle, $owlCat);
 			
 			$this->printProgress($counter, $this->numOfProperties);
 		}
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
			
			
			fwrite($filehandle, $owlCat);
			if ($counter < $this->numOfCategories) {
				$counter++;
				$this->printProgress($counter, $this->numOfCategories);
			}
			
			$this->exportSubcategories($filehandle, $c, $visitedNodes, $counter);
 		}
 		array_pop($visitedNodes);
 	}
 	
 	
 	
 	private function exportDatatypeProperty($rp, $firstType, $directSuperProperties, $maxCard, $minCard) {
 		$xsdType = $this->mapWikiTypeToXSD[$firstType] == NULL ? 'string' : $this->mapWikiTypeToXSD[$firstType];
 				
		$owlCat = '<owl:DatatypeProperty rdf:about="http://www.halowiki.org/property#'.$rp->getDBkey().'">'.LINE_FEED;
		foreach($directSuperProperties as $dsp) {
 			$owlCat .= '	<rdfs:subPropertyOf rdf:resource="http://www.halowiki.org/property#'.$dsp->getDBkey().'"/>'.LINE_FEED;
 		}
 		$owlCat .= '</owl:DatatypeProperty>'.LINE_FEED;
 		$domainRange = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->domainRangeHintRelation);
 		if ($domainRange == NULL || count($domainRange) == 0) {
 					
			$owlCat .= '	<owl:Class rdf:about="http://www.halowiki.org/category#DefaultRootConcept">'.LINE_FEED;
			$owlCat .= '		<rdfs:subClassOf>'.LINE_FEED;
			$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
			$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$rp->getDBkey().'" />'.LINE_FEED;
			$owlCat .= '				<owl:allValuesFrom rdf:resource="http://www.w3.org/2001/XMLSchema#'.$xsdType.'" />'.LINE_FEED;
			$owlCat .= '			</owl:Restriction>'.LINE_FEED;
			$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
			if ($maxCard != NULL) {
				$owlCat .= $this->exportMaxCard($rp, $maxCard);
			}
			if ($minCard != NULL) {
				$owlCat .= $this->exportMinCard($rp, $minCard);
			}
			$owlCat .= '</owl:Class>'.LINE_FEED;
 		} else {
	 			
	 		foreach($domainRange as $dr) {
		 		$dvs = $dr->getDVs();
		 		$domain = $dvs[0] != NULL ? $dvs[0]->getTitle()->getDBkey() : "";
		 		if ($domain == NULL) continue;
		 		$range = $dvs[1] != NULL ? $dvs[1]->getTitle()->getDBkey() : "";
			
				$owlCat .= '	<owl:Class rdf:about="http://www.halowiki.org/category#'.$domain.'">'.LINE_FEED;
				$owlCat .= '		<rdfs:subClassOf>'.LINE_FEED;
				$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
				$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$rp->getDBkey().'" />'.LINE_FEED;
				$owlCat .= '				<owl:allValuesFrom rdf:resource="http://www.w3.org/2001/XMLSchema#'.$xsdType.'" />'.LINE_FEED;
				$owlCat .= '			</owl:Restriction>'.LINE_FEED;
				$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
				if ($maxCard != NULL) {
					$owlCat .= $this->exportMaxCard($rp, $maxCard);
				}
				if ($minCard != NULL) {
					$owlCat .= $this->exportMinCard($rp, $minCard);
				}
				$owlCat .= '</owl:Class>'.LINE_FEED;
	 		}
	 				
 		}
		return $owlCat;
 	}
 	
 	private function exportObjectProperty($rp, $directSuperProperties, $maxCard, $minCard) {
 				$inverseRelations = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->inverseOf);
 				
 				$owlCat = '<owl:ObjectProperty rdf:about="http://www.halowiki.org/property#'.$rp->getDBkey().'">'.LINE_FEED;
 				if ($this->checkIfMemberOfCategory($rp, smwfGetSemanticStore()->symetricalCat)) {
 					$owlCat .= '	<rdf:type rdf:resource="http://www.w3.org/2002/07/owl#SymmetricProperty"/>'.LINE_FEED;
 				}
 				if ($this->checkIfMemberOfCategory($rp, smwfGetSemanticStore()->transitiveCat)) {
 					$owlCat .= '	<rdf:type rdf:resource="http://www.w3.org/2002/07/owl#TransitiveProperty"/>'.LINE_FEED;
 				}
 				
 				foreach($directSuperProperties as $dsp) {
 					$owlCat .= '	<rdfs:subPropertyOf rdf:resource="http://www.halowiki.org/property#'.$dsp->getDBkey().'"/>'.LINE_FEED;
 				}
 				foreach($inverseRelations as $inv) {
 					if (!($inv instanceof SMWWikiPageValue)) continue;
 					$owlCat .= '	<owl:inverseOf rdf:resource="http://www.halowiki.org/property#'.$inv->getTitle()->getDBkey().'"/>'.LINE_FEED;
 				}
 				$owlCat .= '</owl:ObjectProperty>'.LINE_FEED;
 				$domainRange = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->domainRangeHintRelation);
 				if ($domainRange == NULL || count($domainRange) == 0) {
			 				$owlCat .= '	<owl:Class rdf:about="http://www.halowiki.org/category#DefaultRootConcept">'.LINE_FEED;
			 				$owlCat .= '		<rdfs:subClassOf>'.LINE_FEED;
			 				$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
							$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$rp->getDBkey().'" />'.LINE_FEED;
							$owlCat .= '			</owl:Restriction>'.LINE_FEED;
							$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
							if ($maxCard != NULL) {
								$owlCat .= $this->exportMaxCard($rp, $maxCard);
							}
							if ($minCard != NULL) {
								$owlCat .= $this->exportMinCard($rp, $minCard);
							}
							$owlCat .= '</owl:Class>'.LINE_FEED;
 				} else {
	 				
	 				
	 					foreach($domainRange as $dr) {
		 					$dvs = $dr->getDVs();
		 					$domain = $dvs[0] != NULL ? $dvs[0]->getTitle()->getDBkey() : "";
		 					if ($domain == NULL) continue;
		 					$range = $dvs[1] != NULL ? $dvs[1]->getTitle()->getDBkey() : "";
		 				
			 				$owlCat .= '	<owl:Class rdf:about="http://www.halowiki.org/category#'.$domain.'">'.LINE_FEED;
			 				$owlCat .= '		<rdfs:subClassOf>'.LINE_FEED;
			 				$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
							$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$rp->getDBkey().'" />'.LINE_FEED;
							if ($range != '') $owlCat .= '				<owl:allValuesFrom rdf:resource="http://www.halowiki.org/category#'.$range.'" />'.LINE_FEED;
							$owlCat .= '			</owl:Restriction>'.LINE_FEED;
							$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
							if ($maxCard != NULL) {
								$owlCat .= $this->exportMaxCard($rp, $maxCard);
							}
							if ($minCard != NULL) {
								$owlCat .= $this->exportMinCard($rp, $minCard);
							}
							$owlCat .= '</owl:Class>'.LINE_FEED;
	 					}
	 				
 				}
				return $owlCat;
 	}
 	
 	private function exportMinCard($property, $minCard) {
 		$owlCat = '		<rdfs:subClassOf>'.LINE_FEED;
		$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
		$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$property->getDBkey().'" />'.LINE_FEED;
		$owlCat .= '				 <owl:minCardinality rdf:datatype="http://www.w3.org/2001/XMLSchema#nonNegativeInteger">'.$minCard.'</owl:minCardinality>'.LINE_FEED;
		$owlCat .= '			</owl:Restriction>'.LINE_FEED;
		$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
		return $owlCat;
 	}
 	
 	private function exportMaxCard($property, $maxCard) {
 		$owlCat = '		<rdfs:subClassOf>'.LINE_FEED;
		$owlCat .= '			<owl:Restriction>'.LINE_FEED; 
		$owlCat .= '				<owl:onProperty rdf:resource="http://www.halowiki.org/property#'.$property->getDBkey().'" />'.LINE_FEED;
		$owlCat .= '				 <owl:maxCardinality rdf:datatype="http://www.w3.org/2001/XMLSchema#nonNegativeInteger">'.$maxCard.'</owl:maxCardinality>'.LINE_FEED;
		$owlCat .= '			</owl:Restriction>'.LINE_FEED;
		$owlCat .= '		</rdfs:subClassOf>'.LINE_FEED;
		return $owlCat;
 	}
 	
 	/**
 	 * Checks if $title is member of $category
 	 */
 	private function checkIfMemberOfCategory($title, $category) {
 		$db =& wfGetDB( DB_MASTER );
 		$res = $db->selectRow($db->tableName('categorylinks'), 'cl_to', array('cl_from'=>$title->getArticleID(), 'cl_to'=>$category->getDBkey()));
 		return $res !== false;
 	}
 	
 	private function exportSI($pt, $value) {
 		if ( $value->isNumeric() ) {
			$dtid = &smwfGetStore()->getSpecialValues($pt, SMW_SP_HAS_TYPE);
			$dttitle = Title::newFromText($dtid[0]->getWikiValue(), SMW_NS_TYPE);
			$conv = array();
			if ($dttitle !== NULL)
				$conv = &smwfGetStore()->getSpecialValues($dttitle, SMW_SP_CONVERSION_FACTOR_SI);
			if ( !empty($conv) ) {
				$dv = SMWDataValueFactory::newPropertyValue($pt->getPrefixedText(), $value->getXSDValue() . " " . $value->getUnit());
					list($sivalue, $siunit) = $this->convertToSI($dv->getNumericValue(), $conv[0]);
					$dv->setUserValue($sivalue . " " . $dv->getUnit()); // in order to translate to XSD
					if ($dv->getXSDValue() != null && $dv->getXSDValue() != '') {
						return "\t\t<" . $pt->getDBkey() . ' rdf:datatype="http://www.w3.org/2001/XMLSchema#float">' . smwfXMLContentEncode($dv->getXSDValue()) . '</' . $pt->getDBkey() . ">\n";
					}

			}
		}
		return '';
 	}
 	
 	// Converts the given value to the SI unit value based, and also
	// returns the name of the unit. Inputs are the value in the standard
	// unit (a float) and the conversion spec string from the corresponds
	// to SI special attribute.
	// This function is only needed for the SI unit export.
	private function convertToSI ($stdvalue, $conversionSpec) {
		$preNum = '';
		$num = null;  // This indicates error.
		$unit = '';
		$decseparator = wfMsgForContent('smw_decseparator');
		$kiloseparator = wfMsgForContent('smw_kiloseparator');

		// First, split off number from the rest.
		// Number is, e.g. -12,347,421.55e6
		// Note the separators might be a magic regexp value like '.', so have to escape them with backslash.
		// This rejects .1 , it needs a leading 0.
		// This rejects - 3, there can't be spaces in the number.
		$arr = preg_split('/([-+]?\d+(?:\\' . $kiloseparator . '\d+)*\\' . $decseparator . '?[\d]*(?:\s*[eE][-+]?\d+)?)[ ]*/', trim($conversionSpec), 2, PREG_SPLIT_DELIM_CAPTURE);

		$arrSiz = count($arr);
		if ($arrSiz >= 1) $preNum = $arr[0];
		if ($arrSiz >= 2) $num = $arr[1];
		if ($arrSiz >= 3) $unit = $arr[2];

		if ($num !== null) {
			// sscanf doesn't like commas or other than '.' for decimal point.
			$num = str_replace($kiloseparator, '', $num);
			if ($decseparator != '.') {
				$num = str_replace($decseparator, '.', $num);
			}
			// sscanf doesn't like space between number and exponent.
			// TODO: couldn't we just delete all ' '? -- mak
			$num = preg_replace('/\s*([eE][-+]?\d+)/', '$1', $num, 1);

			$extra = ''; // required, a failed sscanf leaves it untouched.
			// Run sscanf to convert the number string to an actual float.
			// This also strips any leading + (relevant for LIKE search).
			list($num, $extra) = sscanf($num, "%f%s");

			// junk after the number after parsing indicates syntax error
			// TODO: can this happen? Isn't all junk thrown into $unit anyway? -- mak
			if ($extra != '') {
				$num = null;	// back to error state
			}

			// Clean up leading space from unit, which should be common
			$unit = preg_replace('/^(?:&nbsp;|&thinsp;|\s)+/','', $unit);

			if (is_infinite($num)) {
				return array(0, $unit);
			}
			return array($stdvalue*$num, $unit);
		} else {
			return array(0, '');
		}
	}
	
	private function printProgress($currentWorkDone, $completeWork) {
		print "\x08\x08\x08\x08\x08".number_format($currentWorkDone/$completeWork*100, 0)."% ";
	}
	
	/** This function transforms a valid url-encoded URI into a string
	 *  that can be used as an XML-ID. The mapping should be injective.
	 */
	static function makeXMLExportId($uri) {
		$uri = str_replace( '-', '-2D', $uri);
		//$uri = str_replace( ':', '-3A', $uri); //already done by PHP
		//$uri = str_replace( '_', '-5F', $uri); //not necessary
		$uri = str_replace( array('"','#','&',"'",'+','%',')','('),
		                    array('-22','-23','-26','-27','-2B','-','-29','-28'),
		                    $uri);
		return preg_match('/^[\d\w_-]+$/', $uri) > 0 ? $uri : NULL;
	}

	/** This function transforms an XML-ID string into a valid
	 *  url-encoded URI. This is the inverse to makeXMLExportID.
	 */
	static function makeURIfromXMLExportId($id) {
		$id = str_replace( array('-22','-23','-26','-27','-2B','-','-29','-28'),
		                   array('"','#','&',"'",'+','%',')','('),
		                   $id);
		$id = str_replace( '-2D', '-', $id);
		return $id;
	}
 	
 }
 
 new ExportOntologyBot();
?>
