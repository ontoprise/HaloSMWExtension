<?php
/*
 * Created on 14.12.2007
 *
 * Author: kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");

define('LINE_FEED', "\n");
define('DEFAULT_EXPORT_NS', 'http://www.halowiki.org');

class ExportOntologyBot extends GardeningBot {

	// maps wiki types (e.g. Number) to XSD types
	private $mapWikiTypeToXSD;

	// user defined namespace for exported ontology
	private $namespace;

	// number of pages in different namespaces
	private $numOfCategories;
	private $numOfInstances;
	private $numOfProperties;

	private $delay;
	private $limit;

	function __construct() {
		parent::GardeningBot("smw_exportontologybot");
			
		// initialize type map
		$this->mapWikiTypeToXSD['_str'] = 'string';
		$this->mapWikiTypeToXSD['_num'] = 'float';
		$this->mapWikiTypeToXSD['_boo'] = 'boolean';
			
		$this->mapWikiTypeToXSD['_int'] = 'integer'; // deprecated
		$this->mapWikiTypeToXSD['_flt'] = 'float'; // deprecated
			
		global $smwgGardeningBotDelay;
		$this->delay = isset($smwgGardeningBotDelay) && is_numeric($smwgGardeningBotDelay) ? $smwgGardeningBotDelay : 0;
		$this->limit = 100;
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
			return "Export ontology bot should not be executed synchronously!";
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
			
		// escape user defined namespace
		$this->namespace = ExportOntologyBot::makeXMLAttributeContent($this->namespace);
			
		// open temporary file for export and write headers
		$handle = fopen($wikiexportDir."/latestExport.temp","wb");
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
			
		if ($this->isAborted()) {
			// remove temporary file
			unlink($wikiexportDir."/latestExport.temp");
			return "";
		}
			
		// copy to normal output file as well as to latestExport file
		copy($wikiexportDir."/latestExport.temp", $wikiexportDir."/".$outputFile);
		copy($wikiexportDir."/latestExport.temp", $wikiexportDir."/latestExport.owl");
			
		// remove temporary file
		unlink($wikiexportDir."/latestExport.temp");

		// create download link
		global $wgServer, $wgScriptPath;
		$downloadLink = wfMsg('smw_gard_export_download', "[".$wgServer.$wgScriptPath."/extensions/SMWHalo/wikiexport/$outputFile ".wfMsg('smw_gard_export_here')."]");
			
		return "\n\n".$downloadLink."\n\n";
	}

	private function writeHeader($filehandle) {
		$header = '<?xml version="1.0" encoding="UTF-8"?>'.LINE_FEED;
		$header .= '<!DOCTYPE owl ['.LINE_FEED;
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
		$db =& wfGetDB( DB_SLAVE );
		$this->numOfCategories = smwfGetSemanticStore()->getNumber(NS_CATEGORY) - 2; // 2 builtin categories
			
		$this->addSubTask($this->numOfCategories);
		GardeningBot::printProgress(0);
			
		$rootCategories = smwfGetSemanticStore()->getRootCategories();
			
		// generate default root concept
		$owl = '<owl:Class rdf:about="&cat;DefaultRootConcept">'.LINE_FEED;
		$owl .= '	<rdfs:label xml:lang="en">DefaultRootConcept</rdfs:label>'.LINE_FEED;
		$owl .= '</owl:Class>'.LINE_FEED;
		fwrite($filehandle, $owl);
		foreach($rootCategories as $rc) {
			if ($this->isAborted()) break;
			if (smwfGetSemanticStore()->transitiveCat->equals($rc)
			|| smwfGetSemanticStore()->symetricalCat->equals($rc)) {
				// ignore builtin categories
				continue;
			}

			// export root categories
			$owl = '<owl:Class rdf:about="&cat;'.ExportOntologyBot::makeXMLAttributeContent($rc->getPartialURL()).'">'.LINE_FEED;
			$owl .= '	<rdfs:label xml:lang="en">'.smwfXMLContentEncode($rc->getText()).'</rdfs:label>'.LINE_FEED;
			$owl .= '	<rdfs:subClassOf rdf:resource="&cat;DefaultRootConcept" />'.LINE_FEED;
			// export redirects
			$redirects = smwfGetSemanticStore()->getRedirectPages($rc);
			foreach($redirects as $r) {
				$owl .= "\t".'<owl:equivalentClass rdf:resource="&cat;'.ExportOntologyBot::makeXMLAttributeContent($r->getPartialURL()).'"/>'.LINE_FEED;
			}
			$owl .= '</owl:Class>'.LINE_FEED;
			fwrite($filehandle, $owl);
			$visitedNodes = array();

			$this->exportSubcategories($filehandle, $rc, $visitedNodes);
		}
		GardeningBot::printProgress(1);
	}


	/**
	 * Exports all instances.
	 * Instances without categories will be added to DefaultRootConcept
	 *
	 * @param $filehandle handle for a text file.
	 */
	private function exportInstances($filehandle) {
			
		$requestoptions = new SMWRequestOptions();
		$requestoptions->limit = $this->limit;
		$requestoptions->offset = 0;

		$this->numOfInstances = smwfGetSemanticStore()->getNumber(NS_MAIN);
		$this->addSubTask($this->numOfInstances);
			
		GardeningBot::printProgress(0);
		do {
			$instances = smwfGetSemanticStore()->getPages(array(NS_MAIN), $requestoptions);

			foreach($instances as $inst) {

				$workDone = $this->getCurrentWorkDone();
				if ($workDone % 10 == 0) {
					usleep($this->delay);
					if ($this->isAborted()) break;
					GardeningBot::printProgress($workDone/$this->numOfInstances);
				}

				// define member categories. If there is no, put it to DefaultRootConcept by default
	 		$categories = smwfGetSemanticStore()->getCategoriesForInstance($inst);
	 		$owl = '<owl:Thing rdf:about="&a;'.ExportOntologyBot::makeXMLAttributeContent($inst->getPartialURL()).'">'.LINE_FEED;
	 		if (count($categories) == 0) {
	 			$owl .= '	<rdf:type rdf:resource="&cat;DefaultRootConcept"/>'.LINE_FEED;
	 		} else {
	 			foreach($categories as $category) {
	 				$owl .= '	<rdf:type rdf:resource="&cat;'.ExportOntologyBot::makeXMLAttributeContent($category->getPartialURL()).'"/>'.LINE_FEED;
	 			}
	 		}
	 		$properties = smwfGetStore()->getProperties($inst);

	 		// export property values (aka annotations)
	 		foreach($properties as $p) {
	 			// create valid xml export ID for property. If no exists, skip it.
	 			$propertyLocal = ExportOntologyBot::makeXMLExportId($p->getPartialURL());
	 			if ($propertyLocal == NULL) continue;
	 			$values = smwfGetStore()->getPropertyValues($inst, $p);
	 			foreach($values as $smwValue) {
	 				// export WikiPage value as ObjectProperty
	 				if ($smwValue instanceof SMWWikiPageValue) {
	 					$target = $smwValue->getTitle();

							if ($target!=NULL) {
								$owl .= '	<prop:'.$propertyLocal.' rdf:resource="&a;'.ExportOntologyBot::makeXMLAttributeContent($target->getPartialURL()).'"/>'.LINE_FEED;
							}

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
	 		// export redirects
	 		$redirects = smwfGetSemanticStore()->getRedirectPages($inst);
	 		foreach($redirects as $r) {
	 			$owl .= "\t".'<owl:sameAs rdf:resource="&a;'.ExportOntologyBot::makeXMLAttributeContent($r->getPartialURL()).'"/>'.LINE_FEED;
	 		}

	 		$owl .= '</owl:Thing>'.LINE_FEED;
	 		fwrite($filehandle, $owl);

	 		$this->worked(1);
			}
			$requestoptions->offset += $this->limit;
		} while (count($instances) == $this->limit);
		GardeningBot::printProgress(1);
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

		$requestoptions = new SMWRequestOptions();
		$requestoptions->limit = $this->limit;
		$requestoptions->offset = 0;

		$this->numOfProperties = smwfGetSemanticStore()->getNumber(SMW_NS_PROPERTY);
		$this->addSubTask($this->numOfProperties);

		GardeningBot::printProgress(0);
		do {
			$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY), $requestoptions);

			foreach($properties as $rp) {

				$workDone = $this->getCurrentWorkDone();
				if ($workDone % 10 == 0 && $this->numOfProperties > 0) {
					usleep($this->delay);
					if ($this->isAborted()) break;
					GardeningBot::printProgress($workDone/$this->numOfProperties);
				}

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
					$firstType = $type[0]->getXSDValue();
				}

				if ($firstType == '_wpg') {
					// wikipage properties will be exported as ObjectProperties
					$owl = $this->exportObjectProperty($rp, $directSuperProperties, $maxCard, $minCard);
				} else { //TODO: how to handle n-aries? for the moment export them as string attributes
				$owl = $this->exportDatatypeProperty($rp, $firstType, $directSuperProperties, $maxCard, $minCard);
				}

				fwrite($filehandle, $owl);
				$this->worked(1);

			}
			$requestoptions->offset += $this->limit;
		} while (count($properties) == $this->limit);
		GardeningBot::printProgress(1);
	}


	private function exportSubcategories($filehandle, $superCategory, array & $visitedNodes) {
		$directSubcategories = smwfGetSemanticStore()->getDirectSubCategories($superCategory);
		array_push($visitedNodes, $superCategory->getArticleID());
		foreach($directSubcategories as $c) {

			if (in_array($c->getArticleID(), $visitedNodes)) {
				array_pop($visitedNodes);
				return;
			}
			$directSuperCategories = smwfGetSemanticStore()->getDirectSuperCategories($c);

			$owl = '<owl:Class rdf:about="&cat;'.ExportOntologyBot::makeXMLAttributeContent($c->getPartialURL()).'">'.LINE_FEED;
			$owl .= '	<rdfs:label xml:lang="en">'.smwfXMLContentEncode($c->getText()).'</rdfs:label>'.LINE_FEED;
			foreach($directSuperCategories as $sc) {
				$owl .= '	<rdfs:subClassOf rdf:resource="&cat;'.ExportOntologyBot::makeXMLAttributeContent($sc->getPartialURL()).'" />'.LINE_FEED;
			}
			// export redirects
			$redirects = smwfGetSemanticStore()->getRedirectPages($c);
			foreach($redirects as $r) {
				$owl .= "\t".'<owl:equivalentClass rdf:resource="&cat;'.ExportOntologyBot::makeXMLAttributeContent($r->getPartialURL()).'"/>'.LINE_FEED;
			}
			$owl .= '</owl:Class>'.LINE_FEED;


			fwrite($filehandle, $owl);

			$workDone = $this->getCurrentWorkDone();
			if ($workDone % 10 == 0 && $this->numOfCategories > 0) {
				usleep($this->delay);
				if ($this->isAborted()) break;
				GardeningBot::printProgress($workDone / $this->numOfCategories);
			}
			$this->worked(1);

			// depth-first in category tree
			$this->exportSubcategories($filehandle, $c, $visitedNodes);
		}
		array_pop($visitedNodes);
	}



	private function exportDatatypeProperty($rp, $firstType, $directSuperProperties, $maxCard, $minCard) {
		$xsdType = $this->mapWikiTypeToXSD[$firstType] == NULL ? 'string' : $this->mapWikiTypeToXSD[$firstType];
			
		// export as subproperty
		$owl = '<owl:DatatypeProperty rdf:about="&prop;'.ExportOntologyBot::makeXMLAttributeContent($rp->getPartialURL()).'">'.LINE_FEED;
		$owl .= '	<rdfs:label xml:lang="en">'.smwfXMLContentEncode($rp->getText()).'</rdfs:label>'.LINE_FEED;
		foreach($directSuperProperties as $dsp) {
			$owl .= '	<rdfs:subPropertyOf rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($dsp->getPartialURL()).'"/>'.LINE_FEED;
		}
		// export redirects
		$redirects = smwfGetSemanticStore()->getRedirectPages($rp);
		foreach($redirects as $r) {
			$owl .= "\t".'<owl:equivalentProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($r->getPartialURL()).'"/>'.LINE_FEED;
		}
		$owl .= '</owl:DatatypeProperty>'.LINE_FEED;
			
		// read all domains/ranges
		$domainRange = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->domainRangeHintRelation);
		if ($domainRange == NULL || count($domainRange) == 0) {
			// if no domainRange annotation exists, export as property of DefaultRootConcept
			$owl .= '	<owl:Class rdf:about="&cat;DefaultRootConcept">'.LINE_FEED;
			$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
			$owl .= '			<owl:Restriction>'.LINE_FEED;
			$owl .= '				<owl:onProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($rp->getPartialURL()).'" />'.LINE_FEED;
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
				$domain = $dvs[0] != NULL ? $dvs[0]->getTitle()->getPartialURL() : "";
				if ($domain == NULL) continue;
				$range = $dvs[1] != NULL ? $dvs[1]->getTitle()->getPartialURL() : "";
					
				$owl .= '	<owl:Class rdf:about="&cat;'.ExportOntologyBot::makeXMLAttributeContent($domain).'">'.LINE_FEED;
				$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
				$owl .= '			<owl:Restriction>'.LINE_FEED;
				$owl .= '				<owl:onProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($rp->getPartialURL()).'" />'.LINE_FEED;
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
		$owl = '<owl:ObjectProperty rdf:about="&prop;'.ExportOntologyBot::makeXMLAttributeContent($rp->getPartialURL()).'">'.LINE_FEED;
		$owl .= '	<rdfs:label xml:lang="en">'.smwfXMLContentEncode($rp->getText()).'</rdfs:label>'.LINE_FEED;
		if ($this->checkIfMemberOfCategory($rp, smwfGetSemanticStore()->symetricalCat)) {
			$owl .= '	<rdf:type rdf:resource="http://www.w3.org/2002/07/owl#SymmetricProperty"/>'.LINE_FEED;
		}
		// export as transitive property
		if ($this->checkIfMemberOfCategory($rp, smwfGetSemanticStore()->transitiveCat)) {
			$owl .= '	<rdf:type rdf:resource="http://www.w3.org/2002/07/owl#TransitiveProperty"/>'.LINE_FEED;
		}
			
		// export as subproperty
		foreach($directSuperProperties as $dsp) {
			$owl .= '	<rdfs:subPropertyOf rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($dsp->getPartialURL()).'"/>'.LINE_FEED;
		}
			
		// export as inverse property
		foreach($inverseRelations as $inv) {
			if (!($inv instanceof SMWWikiPageValue)) continue;
			$owl .= '	<owl:inverseOf rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($inv->getTitle()->getPartialURL()).'"/>'.LINE_FEED;
		}
			
		// export redirects
		$redirects = smwfGetSemanticStore()->getRedirectPages($rp);
		foreach($redirects as $r) {
			$owl .= "\t".'<owl:equivalentProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($r->getPartialURL()).'"/>'.LINE_FEED;
		}
		$owl .= '</owl:ObjectProperty>'.LINE_FEED;
		$domainRange = smwfGetStore()->getPropertyValues($rp, smwfGetSemanticStore()->domainRangeHintRelation);
		if ($domainRange == NULL || count($domainRange) == 0) {
			// if no domainRange annotation exists, export as property of DefaultRootConcept
			$owl .= '	<owl:Class rdf:about="&cat;DefaultRootConcept">'.LINE_FEED;
			$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
			$owl .= '			<owl:Restriction>'.LINE_FEED;
			$owl .= '				<owl:onProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($rp->getPartialURL()).'" />'.LINE_FEED;
			$owl .= '               <owl:allValuesFrom rdf:resource="&cat;DefaultRootConcept" />'.LINE_FEED;
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
				$domain = $dvs[0] != NULL ? $dvs[0]->getTitle()->getPartialURL() : "";
				if ($domain == NULL) continue;
				$range = $dvs[1] != NULL ? $dvs[1]->getTitle()->getPartialURL() : "";
					
				$owl .= '	<owl:Class rdf:about="&cat;'.ExportOntologyBot::makeXMLAttributeContent($domain).'">'.LINE_FEED;
				$owl .= '		<rdfs:subClassOf>'.LINE_FEED;
				$owl .= '			<owl:Restriction>'.LINE_FEED;
				$owl .= '				<owl:onProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($rp->getPartialURL()).'" />'.LINE_FEED;
				if ($range != '') {
					$owl .= '				<owl:allValuesFrom rdf:resource="&cat;'.ExportOntologyBot::makeXMLAttributeContent($range).'" />'.LINE_FEED;
				} else {
					$owl .= '               <owl:allValuesFrom rdf:resource="&cat;DefaultRootConcept" />'.LINE_FEED;
				}
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
		$owl .= '				<owl:onProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($property->getPartialURL()).'" />'.LINE_FEED;
		$owl .= '				 <owl:minCardinality rdf:datatype="&xsd;nonNegativeInteger">'.$minCard.'</owl:minCardinality>'.LINE_FEED;
		$owl .= '			</owl:Restriction>'.LINE_FEED;
		$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
		return $owl;
	}

	private function exportMaxCard($property, $maxCard) {
		$owl = '		<rdfs:subClassOf>'.LINE_FEED;
		$owl .= '			<owl:Restriction>'.LINE_FEED;
		$owl .= '				<owl:onProperty rdf:resource="&prop;'.ExportOntologyBot::makeXMLAttributeContent($property->getPartialURL()).'" />'.LINE_FEED;
		$owl .= '				 <owl:maxCardinality rdf:datatype="&xsd;nonNegativeInteger">'.$maxCard.'</owl:maxCardinality>'.LINE_FEED;
		$owl .= '			</owl:Restriction>'.LINE_FEED;
		$owl .= '		</rdfs:subClassOf>'.LINE_FEED;
		return $owl;
	}

	/**
	 * Checks if $title is member of $category
	 */
	private function checkIfMemberOfCategory($title, $category) {
		$db =& wfGetDB( DB_SLAVE );
		$res = $db->selectRow($db->tableName('categorylinks'), 'cl_to', array('cl_from'=>$title->getArticleID(), 'cl_to'=>$category->getPartialURL()));
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
				list($sivalue, $siunit) = $this->convertToSI($dv->getNumericValue(), $conv[0]->getXSDValue());
				$dv->setUserValue($sivalue . " " . $dv->getUnit()); // in order to translate to XSD
				if ($dv->getXSDValue() != null && $dv->getXSDValue() != '') {
					return "\t\t<prop:" . ExportOntologyBot::makeXMLExportId($pt->getPartialURL()) . ' rdf:datatype="&xsd;float">' .
					smwfXMLContentEncode($dv->getXSDValue()) .
								'</prop:' . ExportOntologyBot::makeXMLExportId($pt->getPartialURL()) . ">\n";
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



	/**
	 *  This function transforms a string that can be used as an XML-ID.
	 */
	static function makeXMLExportId($element) {
		// make sure it starts with a letter or underscore (necessary for valid XML)
		if (preg_match('/^[A-z_].*/', $element) === 0) {
			$element = "_".$element;
		}

		return preg_match('/^[A-z_][\d\w_-]*$/', $element) > 0 ? utf8_encode($element) : NULL;
	}

	/**
	 *  This function transforms a string that can be used as an XML attribute value.
	 *
	 *  @param $attribute value
	 *  @param $matchElementID true if value should be escaped same way as element name
	 */
	static function makeXMLAttributeContent($attribute) {

		// make sure it starts with a letter or underscore (to match the element names)
		if (preg_match('/^[A-z_].*/', $attribute) === 0) {
			$attribute = "_".$attribute;
		}
			
		return utf8_encode(str_replace( array('"'),
		array('&quot;'),
		$attribute));


	}

}


/*
 * Note: This bot filter has no real functionality. It is just a dummy to
 * prevent error messages in the GardeningLog. There are no gardening issues
 * about exporting. Instead there's a textual log.
 * */
define('SMW_EXPORTONTOLOGY_BOT_BASE', 1100);

class ExportOntologyBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_EXPORTONTOLOGY_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'));
	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {

	}

	public function getData($options, $request) {
		parent::getData($options, $request);
	}
}
// create one instance for registration at Gardening Framework
new ExportOntologyBot();
?>
