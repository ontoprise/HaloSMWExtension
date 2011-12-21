<?php
/**
 * Serializer for RDF/XML.
 *
 * @author ??
 *
 */
define('OEB_LINE_FEED', "\n");
class OEBRDFXMLSerializer extends OEBOntologySerializer {

	var $rdfsSemantics;

	//TODO: implement methods (merge from OntologyExportBot)

	public function __construct() {
		$this->rdfsSemantics = false;
	}

	public  function serializeHeader($uri, $bundleName) {
		global $smwgHaloTripleStoreGraph;
		if (!isset($smwgHaloTripleStoreGraph)) {
			$baseURI = "http://mywiki";
		} else {
			$baseURI = $smwgHaloTripleStoreGraph;
		}
		$header = '<?xml version="1.0" encoding="UTF-8"?>'.OEB_LINE_FEED;
		$header .= '<!DOCTYPE owl ['.OEB_LINE_FEED;
		$header .=  '<!ENTITY xsd  "http://www.w3.org/2001/XMLSchema#" >'.OEB_LINE_FEED;
		$header .=  '<!ENTITY owl  "http://www.w3.org/2002/07/owl#" >'.OEB_LINE_FEED;
		$header .=  '<!ENTITY a  "'.$baseURI.'/" >'.OEB_LINE_FEED;
		$header .=  '<!ENTITY prop  "'.$baseURI.'/property/" >'.OEB_LINE_FEED;
		$header .=  '<!ENTITY cat  "'.$baseURI.'/category/" > ]>'.OEB_LINE_FEED;

		$header .=  '<rdf:RDF'.OEB_LINE_FEED;
		$header .=  'xmlns:a   ="&a;"'.OEB_LINE_FEED;
		$header .=  'xmlns:cat ="&cat;"'.OEB_LINE_FEED;
		$header .=  'xmlns:prop ="&prop;"'.OEB_LINE_FEED;
		$header .=  'xmlns:owl ="http://www.w3.org/2002/07/owl#"'.OEB_LINE_FEED;
		$header .=  'xmlns:rdf ="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.OEB_LINE_FEED;
		$header .=  'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">'.OEB_LINE_FEED;
		$header .=  '<owl:Ontology rdf:about="'.$baseURI.'">'.OEB_LINE_FEED;
		$header .=  '   <rdfs:comment>HaloWiki Export</rdfs:comment>'.OEB_LINE_FEED;
		$header .=  '   <rdfs:label>HaloWiki Ontology</rdfs:label>'.OEB_LINE_FEED;
		$header .=  '</owl:Ontology>'.OEB_LINE_FEED;
		return $header;
	}

	public  function serializeFooter($uri, $bundleName){
		$footer = '</rdf:RDF>'.OEB_LINE_FEED;
		return $footer;
	}

	public  function serializeCategory($category, $superCategories){
		if (count($superCategories) == 0) {
			$owl = '<owl:Class rdf:about="&cat;'.self::makeXMLAttributeContent($category->getPartialURL()).'">'.OEB_LINE_FEED;
			$owl .= '   <rdfs:label xml:lang="en">'.self::encodeXMLContent($category->getText()).'</rdfs:label>'.OEB_LINE_FEED;
			$owl .= '</owl:Class>'.OEB_LINE_FEED;
		} else {
			$owl = '<owl:Class rdf:about="&cat;'.self::makeXMLAttributeContent($category->getPartialURL()).'">'.OEB_LINE_FEED;
			$owl .= '   <rdfs:label xml:lang="en">'.self::encodeXMLContent($category->getText()).'</rdfs:label>'.OEB_LINE_FEED;
			foreach($superCategories as $superCategory) {
				$pURL = $superCategory->getPartialURL();
				$owl .= '   <rdfs:subClassOf rdf:resource="&cat;'.$pURL.'" />'.OEB_LINE_FEED;
			}
			$owl .= '</owl:Class>'.OEB_LINE_FEED;
		}
		return $owl;
	}

	public  function serializeProperty($property, $domainCategories, $rangeCategory, $xsdType, $minCardValue, $maxCardValue, $transitive, $symetrical, $inverseOfProperty, $subProperties){
		if (is_null($rangeCategory)) {
			$this->serializeDatatypeProperty($property, $domainCategories, $xsdType, $minCardValue, $maxCardValue, $subProperties);
		} else {
			$this->serializeObjectProperty($property, $domainCategories, $rangeCategory, $minCardValue, $maxCardValue, $transitive, $symetrical, $inverseOfProperty, $subProperties);
		}
	}

	private function serializeDatatypeProperty($property, $domainCategories, $xsdType, $minCardValue, $maxCardValue, $subProperties) {
		 

		switch ($xsdType) {
			case 'tsctype:unit':
				$localXSD = 'double';
				break;
			case 'tsctype:page':
			case 'tsctype:record':
				$localXSD = 'string';
				break;
			default:
				list($prefixXSD, $localXSD) = explode(":", $xsdType);
				break;
		}


		$redirectedPropertiesToCreate = array();

		// export as subproperty
		$owl = '<owl:DatatypeProperty rdf:about="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'">'.OEB_LINE_FEED;
		$owl .= '   <rdfs:label xml:lang="en">'.self::encodeXMLContent($property->getText()).'</rdfs:label>'.OEB_LINE_FEED;
		foreach($subProperties as $dsp) {
			list($subProperty, $hasChildren) = $dsp;
			$owl .= '   <rdfs:subPropertyOf rdf:resource="&prop;'.self::makeXMLAttributeContent($subProperty->getPartialURL()).'"/>'.OEB_LINE_FEED;
		}
		// export redirects
		/* $redirects = smwfGetSemanticStore()->getRedirectPages($rp);
		 foreach($redirects as $r) {
		 $owl .= "\t".'<owl:equivalentProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($r->getPartialURL()).'"/>'.OEB_LINE_FEED;
		 $redirectedPropertiesToCreate[] = self::makeXMLAttributeContent($r->getPartialURL());
		 }*/
		if (!$this->rdfsSemantics) $owl .= '</owl:DatatypeProperty>'.OEB_LINE_FEED;

		// read all domains/ranges

		if ($domainCategories == NULL || count($domainCategories) == 0) {
			// if no domainRange annotation exists, export as property of DefaultRootConcept
			if (!$this->rdfsSemantics) {
				$owl .= '<owl:Class rdf:about="&owl;Thing">'.OEB_LINE_FEED;
				$owl .= '   <rdfs:subClassOf>'.OEB_LINE_FEED;
				$owl .= '       <owl:Restriction>'.OEB_LINE_FEED;
				$owl .= '           <owl:onProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'" />'.OEB_LINE_FEED;
				$owl .= '           <owl:allValuesFrom rdf:resource="&xsd;'.$localXSD.'" />'.OEB_LINE_FEED;
				$owl .= '       </owl:Restriction>'.OEB_LINE_FEED;
				$owl .= '   </rdfs:subClassOf>'.OEB_LINE_FEED;
				if ($maxCardValue != NULL) {
					$owl .= $this->exportMaxCard($property, $maxCardValue);
				}
				if ($minCardValue != NULL) {
					$owl .= $this->exportMinCard($property, $minCardValue);
				}
				$owl .= '</owl:Class>'.OEB_LINE_FEED;
			} else {
				$owl .= '       <rdfs:domain rdf:resource="&owl;Thing" />'.OEB_LINE_FEED;
				$owl .= '       <rdfs:range rdf:resource="&xsd;'.$localXSD.'" />'.OEB_LINE_FEED;
				$owl .= '</owl:DatatypeProperty>'.OEB_LINE_FEED;
			}
		} else {

			$owlTemp = '';
			foreach($domainCategories as $domainCategory) {
					
				if (!$this->rdfsSemantics) {
					$owl .= '<owl:Class rdf:about="&cat;'.self::makeXMLAttributeContent($domainCategory).'">'.OEB_LINE_FEED;
					$owl .= '   <rdfs:subClassOf>'.OEB_LINE_FEED;
					$owl .= '       <owl:Restriction>'.OEB_LINE_FEED;
					$owl .= '           <owl:onProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'" />'.OEB_LINE_FEED;
					$owl .= '           <owl:allValuesFrom rdf:resource="&xsd;'.$localXSD.'" />'.OEB_LINE_FEED;
					$owl .= '       </owl:Restriction>'.OEB_LINE_FEED;
					$owl .= '   </rdfs:subClassOf>'.OEB_LINE_FEED;
					if ($maxCardValue != NULL) {
						$owl .= $this->exportMaxCard($property, $maxCardValue);
					}
					if ($minCardValue != NULL) {
						$owl .= $this->exportMinCard($property, $minCardValue);
					}
					$owl .= '</owl:Class>'.OEB_LINE_FEED;
				} else {
					$owl .= '      <rdfs:domain rdf:resource="&cat;'.self::makeXMLAttributeContent($domainCategory).'" />'.OEB_LINE_FEED;
					$owl .= '      <rdfs:range rdf:resource="&xsd;'.$localXSD.'" />'.OEB_LINE_FEED;
					if ($maxCardValue != NULL || $minCardValue != NULL) {
						$owlTemp .= '<owl:Class rdf:about="&cat;'.self::makeXMLAttributeContent($domainCategory).'">'.OEB_LINE_FEED;
						if ($maxCard != NULL) {
							$owlTemp .= $this->exportMaxCard($property, $maxCardValue);
						}
						if ($minCard != NULL) {
							$owlTemp .= $this->exportMinCard($property, $minCardValue);
						}
						$owlTemp .= '</owl:Class>'.OEB_LINE_FEED;
					}
				}
			}

			if ($this->rdfsSemantics) $owl .= '</owl:DatatypeProperty>'.OEB_LINE_FEED;

			if(strlen($owlTemp) > 0) $owl .= $owlTemp;

		}


		/*  foreach($redirectedPropertiesToCreate as $r) {
		 $owl = '<owl:DatatypeProperty rdf:about="&prop;'.$r.'">'.OEB_LINE_FEED;
		 $owl .= '</owl:DatatypeProperty>'.OEB_LINE_FEED;
		 }*/
		return $owl;
	}

	private  function serializeObjectProperty($property, $domainCategories, $rangeCategory, $minCardValue, $maxCardValue, $transitive, $symetrical, $inverseOfProperty, $subProperties){


		// export as symmetrical property
		$owl = '<owl:ObjectProperty rdf:about="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'">'.OEB_LINE_FEED;
		$owl .= '   <rdfs:label xml:lang="en">'.self::encodeXMLContent($property->getText()).'</rdfs:label>'.OEB_LINE_FEED;
		if ($transitive) {
			$owl .= '   <rdf:type rdf:resource="http://www.w3.org/2002/07/owl#SymmetricProperty"/>'.OEB_LINE_FEED;
		}
		// export as transitive property
		if ($symetrical) {
			$owl .= '   <rdf:type rdf:resource="http://www.w3.org/2002/07/owl#TransitiveProperty"/>'.OEB_LINE_FEED;
		}

		// export as subproperty
		foreach($subProperties as $subP) {
			list($subProperty, $hasChildren) = $subP;
			$owl .= '   <rdfs:subPropertyOf rdf:resource="&prop;'.self::makeXMLAttributeContent($subProperty->getPartialURL()).'"/>'.OEB_LINE_FEED;
		}

		// export as inverse property
		if (!is_null($inverseOfProperty)) {
			$owl .= '   <owl:inverseOf rdf:resource="&prop;'.self::makeXMLAttributeContent($inverseOfProperty->getPartialURL()).'"/>'.OEB_LINE_FEED;
		}

		// export redirects
		/*$redirects = smwfGetSemanticStore()->getRedirectPages($rp);
		 foreach($redirects as $r) {
		 $owl .= "\t".'<owl:equivalentProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($r->getPartialURL()).'"/>'.OEB_LINE_FEED;
		 $redirectedPropertiesToCreate[] = self::makeXMLAttributeContent($r->getPartialURL());
		 }*/
		if (!$this->rdfsSemantics) $owl .= '</owl:ObjectProperty>'.OEB_LINE_FEED;

			
		if ($domainCategories == NULL || count($domainCategories) == 0) {
			// if no domainRange annotation exists, export as property of DefaultRootConcept
			if (!$this->rdfsSemantics) {
				$owl .= '<owl:Class rdf:about="&owl;Thing">'.OEB_LINE_FEED;
				$owl .= '   <rdfs:subClassOf>'.OEB_LINE_FEED;
				$owl .= '       <owl:Restriction>'.OEB_LINE_FEED;
				$owl .= '           <owl:onProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'" />'.OEB_LINE_FEED;
				$owl .= '              <owl:allValuesFrom rdf:resource="&owl;Thing" />'.OEB_LINE_FEED;
				$owl .= '       </owl:Restriction>'.OEB_LINE_FEED;
				$owl .= '   </rdfs:subClassOf>'.OEB_LINE_FEED;
				if ($maxCardValue != NULL) {
					$owl .= $this->exportMaxCard($property, $maxCardValue);
				}
				if ($minCardValue != NULL) {
					$owl .= $this->exportMinCard($property, $minCardValue);
				}
				$owl .= '</owl:Class>'.OEB_LINE_FEED;
			} else {
				$owl .= '       <rdfs:domain rdf:resource="&owl;Thing" />'.OEB_LINE_FEED;
				$owl .= '       <rdfs:range rdf:resource="&owl;Thing" />'.OEB_LINE_FEED;
				$owl .= '</owl:ObjectProperty>'.OEB_LINE_FEED;
			}
		} else {


			foreach($domainCategories as $domainCategory) {
				if (!$this->rdfsSemantics) {
					$owl .= '<owl:Class rdf:about="&cat;'.self::makeXMLAttributeContent($domainCategory).'">'.OEB_LINE_FEED;
					$owl .= '   <rdfs:subClassOf>'.OEB_LINE_FEED;
					$owl .= '       <owl:Restriction>'.OEB_LINE_FEED;
					$owl .= '           <owl:onProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'" />'.OEB_LINE_FEED;
					if (!is_null($rangeCategory)) {
						$owl .= '           <owl:allValuesFrom rdf:resource="&cat;'.self::makeXMLAttributeContent($rangeCategory).'" />'.OEB_LINE_FEED;
					} else {
						$owl .= '           <owl:allValuesFrom rdf:resource="&owl;Thing" />'.OEB_LINE_FEED;
					}
					$owl .= '       </owl:Restriction>'.OEB_LINE_FEED;
					$owl .= '   </rdfs:subClassOf>'.OEB_LINE_FEED;
					if ($maxCardValue != NULL) {
						$owl .= $this->exportMaxCard($property, $maxCardValue);
					}
					if ($minCardValue != NULL) {
						$owl .= $this->exportMinCard($property, $minCardValue);
					}
					$owl .= '</owl:Class>'.OEB_LINE_FEED;
				} else {
					$owl .= '      <rdfs:domain rdf:resource="&cat;'.self::makeXMLAttributeContent($domain).'" />'.OEB_LINE_FEED;
					if ($range != '') {
						$owl .= '     <rdfs:range rdf:resource="&cat;'.self::makeXMLAttributeContent($range).'" />'.OEB_LINE_FEED;
					} else {
						$owl .= '     <rdfs:range rdf:resource="&owl;Thing" />'.OEB_LINE_FEED;
					}

				}
			}

			if ($this->rdfsSemantics) {
				$owl .= '</owl:ObjectProperty>'.OEB_LINE_FEED;
				if ($maxCardValue != NULL || $minCardValue != NULL) {
					foreach($domainCategories as $domainCategory) {
						$owl .= '<owl:Class rdf:about="&cat;'.self::makeXMLAttributeContent($domainCategory).'">'.OEB_LINE_FEED;
						if ($maxCardValue != NULL) {
							$owl .= $this->exportMaxCard($rp, $maxCardValue);
						}
						if ($minCardValue != NULL) {
							$owl .= $this->exportMinCard($rp, $minCardValue);
						}
						$owl .= '</owl:Class>'.OEB_LINE_FEED;
					}
				}
			}
		}


		/*foreach($redirectedPropertiesToCreate as $r) {
			$owl .= '<owl:ObjectProperty rdf:about="&prop;'.$r.'">'.OEB_LINE_FEED;
			$owl .= '</owl:ObjectProperty>'.OEB_LINE_FEED;
			}*/

		return $owl;

	}

	private function exportMinCard($property, $minCard) {
		$owl = '        <rdfs:subClassOf>'.OEB_LINE_FEED;
		$owl .= '           <owl:Restriction>'.OEB_LINE_FEED;
		$owl .= '               <owl:onProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'" />'.OEB_LINE_FEED;
		$owl .= '               <owl:minCardinality rdf:datatype="&xsd;nonNegativeInteger">'.$minCard.'</owl:minCardinality>'.OEB_LINE_FEED;
		$owl .= '           </owl:Restriction>'.OEB_LINE_FEED;
		$owl .= '       </rdfs:subClassOf>'.OEB_LINE_FEED;
		return $owl;
	}

	private function exportMaxCard($property, $maxCard) {
		$owl = '        <rdfs:subClassOf>'.OEB_LINE_FEED;
		$owl .= '           <owl:Restriction>'.OEB_LINE_FEED;
		$owl .= '               <owl:onProperty rdf:resource="&prop;'.self::makeXMLAttributeContent($property->getPartialURL()).'" />'.OEB_LINE_FEED;
		$owl .= '               <owl:maxCardinality rdf:datatype="&xsd;nonNegativeInteger">'.$maxCard.'</owl:maxCardinality>'.OEB_LINE_FEED;
		$owl .= '           </owl:Restriction>'.OEB_LINE_FEED;
		$owl .= '       </rdfs:subClassOf>'.OEB_LINE_FEED;
		return $owl;
	}


	public  function serializeInstance($instance, $category){
		$owl = '<owl:Thing rdf:about="&a;'.self::makeXMLAttributeContent($instance->getPartialURL()).'">'.OEB_LINE_FEED;
		if (is_null($category)) {
			$owl .= '   <rdf:type rdf:resource="&owl;Thing"/>'.OEB_LINE_FEED;
		} else {
			$owl .= '   <rdf:type rdf:resource="&cat;'.self::makeXMLAttributeContent($category->getPartialURL()).'"/>'.OEB_LINE_FEED;
		}
		return $owl;
	}
	public  function serializeInstanceValues($instance, $property, $values){

		// create valid xml export ID for property. If no exists, skip it.
		$propertyLocal = self::makeXMLExportId($property->getDBkey());
		if ($propertyLocal == NULL) return;
		$propertyDi = SMWDIProperty::newFromUserLabel($property->getText());
		$owl = "";
		foreach($values as $dataItem) {
			$propertyTypeId = $propertyDi->findPropertyTypeID();
			list($prefixXSD, $localXSD) = explode(":", WikiTypeToXSD::getXSDType($propertyTypeId));
			// export WikiPage value as ObjectProperty
			if ($propertyTypeId == '_wpg') {
				$target = $dataItem->getTitle();

				if ($target!=NULL) {
					$owl .= '   <prop:'.$propertyLocal.' rdf:resource="&a;'.self::makeXMLAttributeContent($target->getPartialURL()).'"/>'.OEB_LINE_FEED;
				}

			} else { // and all others as datatype properties (including n-aries)

				if ($propertyTypeId == '_qty') {
					// special handling for units
					//FIXME: add unit
					$content = preg_replace("/\x07/","", self::encodeXMLContent(TSHelper::serializeDataItem($dataItem)));
					$owl .= '   <prop:'.$propertyLocal.' rdf:datatype="&tsctype;unit">'.$content.'</prop:'.$propertyLocal.'>'.OEB_LINE_FEED;
				} else {
						
					$content = preg_replace("/\x07/","", self::encodeXMLContent(TSHelper::serializeDataItem($dataItem)));
					$owl .= '   <prop:'.$propertyLocal.' rdf:datatype="&xsd;'.$localXSD.'">'.$content.'</prop:'.$propertyLocal.'>'.OEB_LINE_FEED;
				}

			}
		}

		// export redirects
		/*$redirects = smwfGetSemanticStore()->getRedirectPages($inst);
		 foreach($redirects as $r) {
			$owl .= "\t".'<owl:sameAs rdf:resource="&a;'.self::makeXMLAttributeContent($r->getPartialURL()).'"/>'.OEB_LINE_FEED;
			}
			*/
		$owl .= '</owl:Thing>'.OEB_LINE_FEED;
		return $owl;
	}

	public  function serializeRule($title, $name, $rule_uri, $ruletext){
		return ''; // RDF/XML does not support rules
	}

	public  function serializeExternalArtifacts($bundleName){
		return ''; // no external artifacts
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

	static function encodeXMLContent( $text ) {
		return str_replace( array( '&', '<', '>' ), array( '&amp;', '&lt;', '&gt;' ), Sanitizer::decodeCharReferences( $text ) );
	}
}