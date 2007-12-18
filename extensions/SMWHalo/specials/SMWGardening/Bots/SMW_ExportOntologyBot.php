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
 define('DEFAULT_EXPORT_NS', 'http://www.halowiki.org');
 
 class ExportOntologyBot extends GardeningBot {
 	
 	
 	private $mapWikiTypeToXSD;
 	private $namespace;
 	
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
 		$param1 = new GardeningParamString('GARD_EO_NAMESPACE', wfMsg('smw_gard_export_ns'), SMW_GARD_PARAM_REQUIRED, DEFAULT_EXPORT_NS);
 		$param2 = new GardeningParamBoolean('GARD_EO_ONLYSCHEMA', wfMsg('smw_gard_export_onlyschema'), SMW_GARD_PARAM_OPTIONAL, false);
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
 		
 		// create output directory and generate output filename
 		$wikiexportDir = __FILE__."/../../../../wikiexport";
 		if (!file_exists($wikiexportDir)) mkdir($wikiexportDir);
 		$outputFile = "wikiexport_".uniqid(rand()).".owl";
 		
 		// get bot parameters
 		$this->namespace = urldecode($paramArray['GARD_EO_NAMESPACE']);
 		$exportOnlySchema = array_key_exists('GARD_EO_ONLYSCHEMA', $paramArray);
 		
 		// validate and correct the parameters if necessary
 		if ($this->namespace == '') { // should not happen because it is required
 			$this->namespace = DEFAULT_EXPORT_NS;
 		}
 		 		
 		// open file and write headers
 		$handle = fopen($wikiexportDir."/".$outputFile,"wb");
 		$this->writeHeader($handle);
 		
 		// set number of subtasks for progress indication 		
 		$this->setNumberOfTasks($exportOnlySchema ? 2 : 3); 
 		
 		// start to export the whole shit
 		print "\n\nExport Categories...\n";
 		$this->exportCategories($handle);
 		print "\n\nExport properties...\n";
 		$this->exportProperties($handle);
 		 		
 		if (!$exportOnlySchema) {
 			print "\n\nExport Instances...\n";
 			$this->exportInstances($handle);
 		}
 		
 		// write footer and close
 		$this->writeFooter($handle);
	 	fclose($handle);
	 	 
	 	$successMessage = "\n\nExport was successful!";
	 	
	 	// create download link
	 	global $wgServer, $wgScriptPath;
	 	$downloadLink = "\nClick [".$wgServer.$wgScriptPath."/extensions/SMWHalo/wikiexport/$outputFile here] to download wiki export as OWL file.\n";
	 	
	 	return $successMessage.$downloadLink;
 	}
 	
 	private function writeHeader($filehandle) {
 		$header = '<!DOCTYPE owl ['.LINE_FEED;
   		$header .=	'<!ENTITY xsd  "http://www.w3.org/2001/XMLSchema#" >'.LINE_FEED;
   		$header .=	'<!ENTITY a  "'.$this->namespace.'#" >'.LINE_FEED;
   		$header .=	'<!ENTITY prop  "'.$this->namespace.'/property#" >'.LINE_FEED;
   		$header .=	'<!ENTITY cat  "'.$this->namespace.'/category#" > ]>'.LINE_FEED;
		$header .=	'<rdf:RDF'.LINE_FEED;
    	$header .=	'xmlns:a   ="&a;"'.LINE_FEED;
    	$header .=	'xmlns:cat ="&cat;"'.LINE_FEED;
		$header .=	'xmlns:prop ="&prop;"'.LINE_FEED;				
    	$header .=	'xmlns:owl ="http://www.w3.org/2002/07/owl#"'.LINE_FEED;
		$header .=	'xmlns:rdf ="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.LINE_FEED;
		$header .=	'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">'.LINE_FEED;
		$header .=	'<owl:Ontology rdf:about="'.$this->namespace.'">'.LINE_FEED;
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
 		// obtain complete number of categories
 		$db =& wfGetDB( DB_MASTER );
 		$this->numOfCategories = $db->selectField($db->tableName('page'), 'COUNT(page_id)', 'page_namespace = '.NS_CATEGORY) - 2; // 2 builtin categories
 		
 		$this->addSubTask($this->numOfCategories);
 		$rootCategories = smwfGetSemanticStore()->getRootCategories();
 		$counter = 0;
 		
 		// generate default root concept
 		$owl = '<owl:Class rdf:about="&cat;DefaultRootConcept">'.LINE_FEED;
		$owl .= '	<rdfs:label xml:lang="en">DefaultRootConcept</rdfs:label>'.LINE_FEED;
		$owl .= '</owl:Class>'.LINE_FEED;
		fwrite($filehandle, $owl);
 		foreach($rootCategories as $rc) {
 			
 			if (smwfGetSemanticStore()->transitiveCat->equals($rc) 
 					|| smwfGetSemanticStore()->symetricalCat->equals($rc)) {
 						// ignore builtin categories
 						continue;
 			}
 			
 			// export root categories
 			$owl = '<owl:Class rdf:about="&cat;'.$rc->getDBkey().'">'.LINE_FEED;
			$owl .= '	<rdfs:label xml:lang="en">'.$rc->getText().'</rdfs:label>'.LINE_FEED;
			$owl .= '	<rdfs:subClassOf rdf:resource="&cat;DefaultRootConcept" />'.LINE_FEED;
			$owl .= '</owl:Class>'.LINE_FEED;
			fwrite($filehandle, $owl);
			$visitedNodes = array();
			
			$this->exportSubcategories($filehandle, $rc, $visitedNodes, $counter);
 		}
 	}
 	
 	  	
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
 		$this->addSubTask($this->numOfInstances);
 		foreach($instances as $inst) {
 			if ($counter % 10 == 0) {
 				$this->printProgress($counter, $this->numOfInstances);
 			}
 			
 			// define member categories. If there is no, put it to DefaultRootConcept by default
	 		$categories = smwfGetSemanticStore()->getCategoriesForInstance($inst);
 			$owl = '<owl:Thing rdf:about="&a;'.smwfXMLContentEncode($inst->getDBkey()).'">'.LINE_FEED;
	 		if (count($categories) == 0) {
	 			$owl .= '	<rdf:type rdf:resource="&cat;DefaultRootConcept"/>'.LINE_FEED;
	 		} else {
	 			foreach($categories as $category) {
	 				$owl .= '	<rdf:type rdf:resource="&cat;'.smwfXMLContentEncode($category->getDBkey()).'"/>'.LINE_FEED;
	 			}
	 		}
 			$properties = smwfGetStore()->getProperties($inst);
 			
 			// export properties
 			foreach($properties as $p) {
 				// create valid xml export ID for property. If no exists, skip it.
 				$propertyLocal = ExportOntologyBot::makeXMLExportId($p->getDBkey());
 				if ($propertyLocal == NULL) continue;
 				$values = smwfGetStore()->getPropertyValues($inst, $p);
 				foreach($values as $smwValue) {
 					// export WikiPage value as ObjectProperty
					if ($smwValue instanceof SMWWikiPageValue) {
						$target = $smwValue->getTitle();
						
							$targetLocal = preg_replace("/\"/", "", $target->getDBkey());
							
							if ($target!=NULL) $owl .= '	<prop:'.$propertyLocal.' rdf:resource="&a;'.$targetLocal.'"/>'.LINE_FEED;
						
		 			} else { // and all others as datatype properties (including n-aries)
		 										
							if ($smwValue->getUnit() != NULL && $smwValue->getUnit() != '') {
								// special handling for units
								$owl .= $this->exportSI($p, $smwValue);
							} else {
			 					$xsdType = $this->mapWikiTypeToXSD[$smwValue->getTypeID()] == NULL ? 'string' : $this->mapWikiTypeToXSD[$smwValue->getTypeID()];
			 					$content = preg_replace("/\x07/","", smwfXMLContentEncode($smwValue->getXSDValue()));
			 					$owl .= '	<prop:'.$propertyLocal.' rdf:datatype="&xsd;'.$xsdType.'">'.$content.'</prop:'.$propertyLocal.'>'.LINE_FEED;
							}
						
		 			}
 				}
 			}
 			$owl .= '</owl:Thing>'.LINE_FEED;
 			fwrite($filehandle, $owl);
	 		$counter++;
 			$this->worked(1);
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
 		$this->addSubTask($this->numOfProperties);
 		foreach($properties as $rp) {
 			$counter++;
 			$this->worked(1);
 			if (smwfGetSemanticStore()->domainRangeHintRelation->equals($rp) 
 					|| smwfGetSemanticStore()->minCard->equals($rp) 
 					|| smwfGetSemanticStore()->maxCard->equals($rp)
 					|| smwfGetSemanticStore()->inverseOf->equals($rp)) {
 						// ignore builtin properties
 						continue;
 			}
 			
 			// obtain cardinalities
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
 			
 			// obtain direct super properties
 			$directSuperProperties = smwfGetSemanticStore()->getDirectSuperProperties($rp);
 			
 			// decide what to export by reading property type
 			$type = smwfGetStore()->getSpecialValues($rp, SMW_SP_HAS_TYPE);
 			if ($type == NULL || count($type) == 0) {
 				// default type: binary relation
 				$firstType = '_wpg';
 			} else {
 				$firstType = $type[0]->getID;
 			}
 			if ($firstType == '_wpg') {
 				// wikipage properties will be exported as ObjectProperties
 				$owl = $this->exportObjectProperty($rp, $directSuperProperties, $maxCard, $minCard);
 			} else { //TODO: how to handle n-aries? for the moment export them as string attributes
 				$owl = $this->exportDatatypeProperty($rp, $firstType, $directSuperProperties, $maxCard, $minCard);
 			}
 			
 			fwrite($filehandle, $owl);
 			
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
 			
 			$owl = '<owl:Class rdf:about="&cat;'.$c->getDBkey().'">'.LINE_FEED;
			$owl .= '	<rdfs:label xml:lang="en">'.$c->getText().'</rdfs:label>'.LINE_FEED;
			foreach($directSuperCategories as $sc) {
				$owl .= '	<rdfs:subClassOf rdf:resource="&cat;'.$sc->getDBkey().'" />'.LINE_FEED;
			}
			
			$owl .= '</owl:Class>'.LINE_FEED;
			
			
			fwrite($filehandle, $owl);
			if ($counter < $this->numOfCategories) {
				$counter++;
				$this->worked(1);
				$this->printProgress($counter, $this->numOfCategories);
			}
			
			// depth-first in category tree
			$this->exportSubcategories($filehandle, $c, $visitedNodes, $counter);
 		}
 		array_pop($visitedNodes);
 	}
 	
 	
 	
 	private function exportDatatypeProperty($rp, $firstType, $directSuperProperties, $maxCard, $minCard) {
 		$xsdType = $this->mapWikiTypeToXSD[$firstType] == NULL ? 'string' : $this->mapWikiTypeToXSD[$firstType];
 		
 		// export as subproperty 	
		$owl = '<owl:DatatypeProperty rdf:about="&prop;'.$rp->getDBkey().'">'.LINE_FEED;
		foreach($directSuperProperties as $dsp) {
 			$owl .= '	<rdfs:subPropertyOf rdf:resource="&prop;'.$dsp->getDBkey().'"/>'.LINE_FEED;
 		}
 		$owl .= '</owl:DatatypeProperty>'.LINE_FEED;
 		
 		// read all domains/ranges
 		$domainRange = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->domainRangeHintRelation);
 		if ($domainRange == NULL || count($domainRange) == 0) {
 			// if no domainRange annotation exists, export as property of DefaultRootConcept
			$owl .= '	<owl:Class rdf:about="&cat;DefaultRootConcept">'.LINE_FEED;
			$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
			$owl .= '			<owl:Restriction>'.LINE_FEED; 
			$owl .= '				<owl:onProperty rdf:resource="&prop;'.$rp->getDBkey().'" />'.LINE_FEED;
			$owl .= '				<owl:allValuesFrom rdf:resource="&xsd;'.$xsdType.'" />'.LINE_FEED;
			$owl .= '			</owl:Restriction>'.LINE_FEED;
			$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
			if ($maxCard != NULL) {
				$owl .= $this->exportMaxCard($rp, $maxCard);
			}
			if ($minCard != NULL) {
				$owl .= $this->exportMinCard($rp, $minCard);
			}
			$owl .= '</owl:Class>'.LINE_FEED;
 		} else {
	 			
	 		foreach($domainRange as $dr) {
		 		$dvs = $dr->getDVs();
		 		$domain = $dvs[0] != NULL ? $dvs[0]->getTitle()->getDBkey() : "";
		 		if ($domain == NULL) continue;
		 		$range = $dvs[1] != NULL ? $dvs[1]->getTitle()->getDBkey() : "";
			
				$owl .= '	<owl:Class rdf:about="&cat;'.$domain.'">'.LINE_FEED;
				$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
				$owl .= '			<owl:Restriction>'.LINE_FEED; 
				$owl .= '				<owl:onProperty rdf:resource="&prop;'.$rp->getDBkey().'" />'.LINE_FEED;
				$owl .= '				<owl:allValuesFrom rdf:resource="&xsd;'.$xsdType.'" />'.LINE_FEED;
				$owl .= '			</owl:Restriction>'.LINE_FEED;
				$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
				if ($maxCard != NULL) {
					$owl .= $this->exportMaxCard($rp, $maxCard);
				}
				if ($minCard != NULL) {
					$owl .= $this->exportMinCard($rp, $minCard);
				}
				$owl .= '</owl:Class>'.LINE_FEED;
	 		}
	 				
 		}
		return $owl;
 	}
 	
 	private function exportObjectProperty($rp, $directSuperProperties, $maxCard, $minCard) {
 				$inverseRelations = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->inverseOf);
 				
 				// export as symmetrical property
 				$owl = '<owl:ObjectProperty rdf:about="&prop;'.$rp->getDBkey().'">'.LINE_FEED;
 				if ($this->checkIfMemberOfCategory($rp, smwfGetSemanticStore()->symetricalCat)) {
 					$owl .= '	<rdf:type rdf:resource="http://www.w3.org/2002/07/owl#SymmetricProperty"/>'.LINE_FEED;
 				}
 				// export as transitive property
 				if ($this->checkIfMemberOfCategory($rp, smwfGetSemanticStore()->transitiveCat)) {
 					$owl .= '	<rdf:type rdf:resource="http://www.w3.org/2002/07/owl#TransitiveProperty"/>'.LINE_FEED;
 				}
 				
 				// export as subproperty
 				foreach($directSuperProperties as $dsp) {
 					$owl .= '	<rdfs:subPropertyOf rdf:resource="&prop;'.$dsp->getDBkey().'"/>'.LINE_FEED;
 				}
 				
 				// export as inverse property
 				foreach($inverseRelations as $inv) {
 					if (!($inv instanceof SMWWikiPageValue)) continue;
 					$owl .= '	<owl:inverseOf rdf:resource="&prop;'.$inv->getTitle()->getDBkey().'"/>'.LINE_FEED;
 				}
 				$owl .= '</owl:ObjectProperty>'.LINE_FEED;
 				$domainRange = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->domainRangeHintRelation);
 				if ($domainRange == NULL || count($domainRange) == 0) {
 					// if no domainRange annotation exists, export as property of DefaultRootConcept
			 				$owl .= '	<owl:Class rdf:about="&cat;DefaultRootConcept">'.LINE_FEED;
			 				$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
			 				$owl .= '			<owl:Restriction>'.LINE_FEED; 
							$owl .= '				<owl:onProperty rdf:resource="&prop;'.$rp->getDBkey().'" />'.LINE_FEED;
							$owl .= '			</owl:Restriction>'.LINE_FEED;
							$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
							if ($maxCard != NULL) {
								$owl .= $this->exportMaxCard($rp, $maxCard);
							}
							if ($minCard != NULL) {
								$owl .= $this->exportMinCard($rp, $minCard);
							}
							$owl .= '</owl:Class>'.LINE_FEED;
 				} else {
	 				
	 				
	 					foreach($domainRange as $dr) {
		 					$dvs = $dr->getDVs();
		 					$domain = $dvs[0] != NULL ? $dvs[0]->getTitle()->getDBkey() : "";
		 					if ($domain == NULL) continue;
		 					$range = $dvs[1] != NULL ? $dvs[1]->getTitle()->getDBkey() : "";
		 				
			 				$owl .= '	<owl:Class rdf:about="&cat;'.$domain.'">'.LINE_FEED;
			 				$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
			 				$owl .= '			<owl:Restriction>'.LINE_FEED; 
							$owl .= '				<owl:onProperty rdf:resource="&prop;'.$rp->getDBkey().'" />'.LINE_FEED;
							if ($range != '') $owl .= '				<owl:allValuesFrom rdf:resource="&cat;'.$range.'" />'.LINE_FEED;
							$owl .= '			</owl:Restriction>'.LINE_FEED;
							$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
							if ($maxCard != NULL) {
								$owl .= $this->exportMaxCard($rp, $maxCard);
							}
							if ($minCard != NULL) {
								$owl .= $this->exportMinCard($rp, $minCard);
							}
							$owl .= '</owl:Class>'.LINE_FEED;
	 					}
	 				
 				}
				return $owl;
 	}
 	
 	private function exportMinCard($property, $minCard) {
 		$owl = '		<rdfs:subClassOf>'.LINE_FEED;
		$owl .= '			<owl:Restriction>'.LINE_FEED; 
		$owl .= '				<owl:onProperty rdf:resource="&prop;'.$property->getDBkey().'" />'.LINE_FEED;
		$owl .= '				 <owl:minCardinality rdf:datatype="&xsd;nonNegativeInteger">'.$minCard.'</owl:minCardinality>'.LINE_FEED;
		$owl .= '			</owl:Restriction>'.LINE_FEED;
		$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
		return $owl;
 	}
 	
 	private function exportMaxCard($property, $maxCard) {
 		$owl = '		<rdfs:subClassOf>'.LINE_FEED;
		$owl .= '			<owl:Restriction>'.LINE_FEED; 
		$owl .= '				<owl:onProperty rdf:resource="&prop;'.$property->getDBkey().'" />'.LINE_FEED;
		$owl .= '				 <owl:maxCardinality rdf:datatype="&xsd;nonNegativeInteger">'.$maxCard.'</owl:maxCardinality>'.LINE_FEED;
		$owl .= '			</owl:Restriction>'.LINE_FEED;
		$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
		return $owl;
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
						return "\t\t<" . $pt->getDBkey() . ' rdf:datatype="&xsd;float">' . smwfXMLContentEncode($dv->getXSDValue()) . '</' . $pt->getDBkey() . ">\n";
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
