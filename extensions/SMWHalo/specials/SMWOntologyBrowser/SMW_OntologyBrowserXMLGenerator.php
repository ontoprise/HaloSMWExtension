<?php
/**
 * Created on 26.04.2007
 *
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloOntologyBrowser
 *
 * @author Kai K�hn
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

require_once($smwgHaloIP . '/includes/SMW_ChemistryParser.php');
require_once("SMW_OntologyBrowserErrorHighlighting.php");

class SMWOntologyBrowserXMLGenerator {

	/**
	 * Encapsulate an array of categories as a category partition in XML.
	 *
	 * @param array & $titles. Category titles
	 * @param $limit Max number of categories per partition
	 * @param $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
	 * @param $rootLevel True, if partition on root level. Otherwise false.
	 *
	 * @return XML string
	 */
	public static function encapsulateAsConceptPartition(array & $titles, array & $resourceAttachments, $limit, $partitionNum, $rootLevel = false) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		if (count($titles) == $limit) {
			if ($partitionNum == 0) {
				$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
			} else {
				$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
			}
		}
		if (count($titles) < $limit && $partitionNum > 0) {
			$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
		}
		$count++;
		$ts = new TSNamespaces();
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($titles as $e) {
			list($t, $isLeaf) = $e;
			if (SMWOntologyBrowserXMLGenerator::isPredefined($t)) {
				continue;
			}
			$leaf = $isLeaf ? 'isLeaf="true"' : '';
			$title_esc = htmlspecialchars($t->getDBkey());
			$definingRules = array_key_exists($t->getPrefixedDBkey(), $resourceAttachments) ? $resourceAttachments[$t->getPrefixedDBkey()] : array();
			$definingRulesXML = "";
			foreach($definingRules as $df) {
				$definingRulesXML .= "<definingRule>".htmlspecialchars($df)."</definingRule>";
			}
			$titleURLEscaped = htmlspecialchars(self::urlescape($t->getDBkey()));
			$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t);
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
			$uri_att = 'uri="'.htmlspecialchars($ts->getFullURI($t)).'"';
			$result = $result."<conceptTreeElement $leaf $uri_att title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" img=\"concept.gif\" id=\"ID_$id$count\">$gi_issues$definingRulesXML</conceptTreeElement>";
			$count++;
		}
		if ($rootLevel) {
			return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>" : "<result>$result</result>";
		} else {
			return $result == "" ? "noResult" : "<result>$result</result>";
		}
	}

	/**
	 * Encapsulate an array of instances as an instance partition in XML.
	 *
	 * @param Tuple $instances
	 *         ((instanceTitle, $instanceURI, $localInstanceURL, $metadataMap) , ($localCategoryURL, $categoryTitle))
	 * @param $limit Max number of instances per partition
	 * @param $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
	 *
	 * @return XML string
	 */
	public static function encapsulateAsInstancePartition(array & $instances, $limit, $partitionNum, $dataSrc = NULL) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		$dataSrc = (!is_null($dataSrc)) ? 'dataSrc="'.$dataSrc.'"' : '';

		if (count($instances) == $limit) {
			if ($partitionNum == 0) {
				$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\" $dataSrc/>";
			} else {
				$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" $dataSrc/>";
			}
		}
		if (count($instances) < $limit && $partitionNum > 0) {
			$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
		}
		$count++;
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($instances as $t) {

			list($instanceData, $categoryData) = $t;
			list($categoryURI, $categoryTitle) = $categoryData;
			list($instanceTitle, $instanceURI, $url, $metadata) = $instanceData;

			if ($instanceTitle instanceof SMWWikiPageValue) { // also accept SMW datavalue here
				$instanceTitle = $instanceTitle->getTitle();
			}
			if (is_null($instanceTitle)) {
				$invalidTitleText = wfMsg('smw_ob_invalidtitle');
				$result = $result."<instance title_url=\"$invalidTitleText\" title=\"$invalidTitleText\" namespace=\"0\" img=\"instance.gif\" id=\"ID_INVALID_$count\"></instance>";
				continue;
			}
			$titleEscaped = htmlspecialchars($instanceTitle->getDBkey());
			$namespace = $instanceTitle->getNsText();
			$titleURLEscaped = htmlspecialchars(self::urlescape($instanceTitle->getDBkey()));
			$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $instanceTitle);
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);

			// metadata
			$metadataTags = "<metadata id=\"".$id."_meta_".$count."\">";

			if (!is_null($metadata)) {
				// read metadata
				foreach($metadata as $mdProperty => $mdValues) {
					foreach($mdValues as $mdValue) {
						$metadataTags .= "<property name=\"".htmlspecialchars($mdProperty)."\">".htmlspecialchars($mdValue)."</property>";
					}
				}
			}
			$metadataTags .= "</metadata>";

			$localurl_att = "";
			if (!is_null($url)) {
				$localurl_att = 'localurl="'.htmlspecialchars($url).'"';
			}

			$instanceURI_att = "";
			if (!is_null($instanceURI)) {
				$instanceURI_att = 'uri="'.htmlspecialchars($instanceURI).'"';
			}

			if (!is_null($categoryTitle)) {
				$categoryTitle = htmlspecialchars($categoryTitle->getDBkey());
				$result = $result."<instance $instanceURI_att $localurl_att title_url=\"$titleURLEscaped\" title=\"".$titleEscaped."\" namespace=\"$namespace\" superCat=\"$categoryTitle\" img=\"instance.gif\" id=\"ID_$id$count\" inherited=\"true\">$gi_issues$metadataTags</instance>";
			} else {
				$result = $result."<instance $instanceURI_att $localurl_att title_url=\"$titleURLEscaped\" title=\"".$titleEscaped."\" namespace=\"$namespace\" img=\"instance.gif\" id=\"ID_$id$count\">$gi_issues$metadataTags</instance>";
			}
			$count++;
		}

		return $result == '' ? "<instanceList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_instances')."\"/>" : "<instanceList>$result</instanceList>";
	}

	/**
	 * Encapsulate an array of properties as a property partition in XML.
	 *
	 * @param array & $titles. Tuple (Title of property, boolean hasSubProperties, string URI, string local URL)
	 * @param array & $resourceAttachments (Property title => array of rule URIs)
	 * @param $limit Max number of properties per partition
	 * @param $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
	 * @param $rootLevel True, if partition on root level. Otherwise false
	 *
	 * @return XML string
	 */
	public static function encapsulateAsPropertyPartition(array & $titles, array & $resourceAttachments, $limit, $partitionNum, $rootLevel = false) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		if (count($titles) == $limit) {
			if ($partitionNum == 0) {
				$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
			} else {
				$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
			}
		}
		if (count($titles) < $limit && $partitionNum > 0) {
			$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
		}
		$count++;
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($titles as $e) {

			list($t, $isLeaf, $propertyURI, $localURL) = $e;
			if (SMWOntologyBrowserXMLGenerator::isPredefined($t)) {
				continue;
			}
			$leaf = $isLeaf ? 'isLeaf="true"' : '';
			$title = htmlspecialchars($t->getDBkey());
			$definingRules = array_key_exists($t->getPrefixedDBkey(), $resourceAttachments) ? $resourceAttachments[$t->getPrefixedDBkey()] : array();
			$definingRulesXML = "";
			foreach($definingRules as $df) {
				$definingRulesXML .= "<definingRule>".htmlspecialchars($df)."</definingRule>";
			}

			$propertyURI_att = !is_null($propertyURI) ? 'uri="'.htmlspecialchars($propertyURI).'"' : "";
			$localURL_att = !is_null($localURL) ? 'localurl="'.htmlspecialchars($localURL).'"' : "";
			$titleURLEscaped = htmlspecialchars(self::urlescape($t->getDBkey()));
			$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t);
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
			$result = $result."<propertyTreeElement $propertyURI_att $localURL_att $leaf title_url=\"$titleURLEscaped\" title=\"".$title."\" img=\"attribute.gif\" id=\"ID_$id$count\">$gi_issues$definingRulesXML</propertyTreeElement>";
			$count++;
		}
		if ($rootLevel) {
			return $result == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>" : "<result>$result</result>";
		} else {
			return $result == '' ? "noResult" : "<result>$result</result>";
		}
	}

	/**
	 * Encapsulate an array of annotations as XML.
	 *
	 * @param array & $propertyAnnotations: Tuple of ($property, $value)
	 * @param Title $instance
	 *
	 * @return XML string
	 */
	public static function encapsulateAsAnnotationList(array & $propertyAnnotations, Title $instance) {
		$result = "";
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($propertyAnnotations as $a) {
			list($property, $values) = $a;
			$val = $property->getDBkeys();
			$propertyTitle = Title::newFromText($val[0], SMW_NS_PROPERTY);
			$result .= SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotation($instance, $propertyTitle, $values);
		}
		// get low cardinality issues and "highlight" missing annotations. This is an exception because missing annotations do not exist.
		$issues = $gi_store->getGardeningIssues('smw_consistencybot', SMW_GARDISSUE_MISSING_ANNOTATIONS, NULL, $instance);
		$result .= SMWOntologyBrowserErrorHighlighting::getMissingAnnotations($issues);
		$instanceTitleEscaped = htmlspecialchars($instance->getDBkey());
		$namespaceInstance = $instance->getNsText();
		$titleURLEscaped = htmlspecialchars(self::urlescape($instance->getDBkey()));

		return $result == '' ? "<annotationsList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_annotations')."\" title_url=\"$titleURLEscaped\" title=\"$instanceTitleEscaped\" namespace=\"$namespaceInstance\"/>" : "<annotationsList>".$result."</annotationsList>";
	}


	/**
	 * Encapsulate an array of properties as XML
	 *
	 * @param array & $properties: Tuple of (title, minCard, maxCard, type, isSym, isTrans, range)
	 *
	 * @return XML string
	 */
	public static function encapsulateAsPropertyList(array & $properties) {

		$count = 0;
		$propertiesXML = "";
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($properties as $t) {
			$directIssues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t[0]);
			$propertiesXML .= SMWOntologyBrowserXMLGenerator::encapsulateAsProperty($t, $count, $directIssues);
			$count++;
		}

		return $propertiesXML == '' ? "<propertyList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_properties')."\"/>" : "<propertyList>".$propertiesXML."</propertyList>";
	}

	/**
	 * Returns an XML represenatation of a schema property
	 *
	 * @param array & schemaData. Tuple of (title, minCard, maxCard, type, isSym, isTrans, range)
	 * @param count continuous number for generating new IDs
	 * @param array & issues Gardening issues for that property
	 *
	 * @return XML string (fragment)
	 */
	private static function encapsulateAsProperty(array & $schemaData, $count, array & $issues) {
		$id = uniqid (rand());
		$content = "";

		// unpack schemaData array
		$title = $schemaData[0];
		$minCardinality = $schemaData[1];
		$maxCardinality = $schemaData[2];
		$type = $schemaData[3];
		$isMemberOfSymCat = $schemaData[4];
		$isMemberOfTransCat = $schemaData[5];
		$range = $schemaData[6];
		$inherited = $schemaData[7] == true ? "inherited=\"true\"" : "";
		$ts = new TSNamespaces();
		//FIXME: show primitive type as links to Type pages.
		if ($type == '_wpg') { // binary relation?
			if ($range == NULL) {
				$v = SMWDataValueFactory::newPropertyObjectValue(SMWPropertyValue::makeProperty("_TYPE"));
				$v->setDBkeys(array("_wpg"));
				$typeValues = $v->getTypeValues();
				$typesValue = reset($typeValues);
				$typeLabels = $typesValue->getTypeLabels();
				$content .= "<rangeType>".reset($typeLabels)."</rangeType>";
			} else {
				$content .= "<rangeType isLink=\"true\">".$range."</rangeType>";
			}
		} else {
			// it must be an attribute or n-ary relation otherwise.
			$v = SMWDataValueFactory::newPropertyObjectValue(SMWPropertyValue::makeProperty("_LIST"));
			$v->setDBkeys(array($type));
			$typesValues = $v->getTypeValues();

			foreach($typesValues as $typesValue) {
				$content .= "<rangeType>".reset($typesValue->getTypeLabels())."</rangeType>";
			}

		}

		// generate attribute strings

		$maxCardText = $maxCardinality != CARDINALITY_UNLIMITED ? "maxCard=\"".$maxCardinality."\"" : "maxCard=\"*\"";
		$minCardText = $minCardinality != CARDINALITY_MIN ? "minCard=\"".$minCardinality."\"" : "minCard=\"0\"";
		$isSymetricalText = $isMemberOfSymCat ? "isSymetrical=\"true\"" : "";
		$isTransitiveText = $isMemberOfTransCat ? "isTransitive=\"true\"" : "";
		$title_esc = htmlspecialchars($title->getDBkey());
		$titleURLEscaped = htmlspecialchars(self::urlescape($title->getDBkey()));
		$numberofUsage = smwfGetSemanticStore()->getNumberOfUsage($title);
		$numberOfUsageAtt = 'num="'.$numberofUsage.'"';
		$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
		$uri_att = 'uri="'.htmlspecialchars($ts->getFullURI($title)).'"';
		return "<property $uri_att title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" id=\"ID_".$id.$count."\" " .
					"$minCardText $maxCardText $isSymetricalText $isTransitiveText $numberOfUsageAtt $inherited>".
		$content.$gi_issues.
				"</property>";

	}

	/**
	 * Encapsulates an annotation as XML.
	 *
	 * @param $instance
	 * @param $annotation
	 * @param $smwValues
	 *
	 * @return XML string (fragment)
	 */
	private static function encapsulateAsAnnotation(Title $instance, Title $annotationTitle, $smwValues) {
		$id = uniqid (rand());
		$count = 0;
		$singleProperties = "";
		$multiProperties = "";
		$isFormula = false;
		$chemistryParser = new ChemEqParser();
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		$ts = new TSNamespaces();
			
		foreach($smwValues as $smwValue) {
			
			if ($smwValue instanceof SMWRecordValue) { // n-ary property

				$needRepaste = false;
				$parameters = "";
				foreach($smwValue->getDVs() as $params) {
					$uri_att = "";
                        $url_att = "";
					if ($params == NULL) {
						$parameters .= "<param></param>";
						continue;
					}
					if ($params->getTypeID() == '_che') {
						$isFormula = true;
						$chemistryParser->checkEquation(array_shift($params->getDBkeys()));
						$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
						$value = "<![CDATA[".($formulaAsHTML)."]]>";
					} else if ( $params->getTypeID() == '_chf') {
						$isFormula = true;
						$chemistryParser->checkFormula(array_shift($params->getDBkeys()));
						$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
						$value = "<![CDATA[".($formulaAsHTML)."]]>";
					} else {
						// escape potential HTML in a CDATA section
						
						if ($params instanceof SMWWikiPageValue) {
						  $uri_att = 'uri="'.htmlspecialchars($ts->getFullURI($params->getTitle())).'"';
                          $url_att = 'url="'.htmlspecialchars($params->getTitle()->getFullURL()).'"';
						  $value = "<![CDATA[".$params->getTitle()->getPrefixedDBkey()."]]>";
						} else {
						  $value = "<![CDATA[".(html_entity_decode(array_shift($params->getDBkeys())))." ".(html_entity_decode($params->getUnit()))."]]>";
						}
					}

					// check if re-paste is needed
					$needRepaste |= html_entity_decode(array_shift($params->getDBkeys())) != array_shift($params->getDBkeys()) || $params->getUnit() != '';

					// check if target is a wikipage and built param
					$isLink = $params instanceof SMWWikiPageValue ? "isLink=\"true\"" : "";
					
					$parameters .= "<param $isLink $uri_att $url_att>$value</param>";
				}
				$repasteMarker = $isFormula || $needRepaste ? "needRepaste=\"true\"" : "";
				$title = htmlspecialchars($annotationTitle->getDBkey());
				$titleURLEscaped = htmlspecialchars(self::urlescape($annotationTitle->getDBkey()));
				$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
				SMW_GARD_ISSUE_MISSING_PARAM, SMW_GARDISSUE_WRONG_TARGET_VALUE), NULL, array($instance, $annotationTitle));
					
				$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);

				// no metadata available on n-ary properties
				$multiProperties .= "<annotation  title_url=\"$titleURLEscaped\" title=\"".$title."\" id=\"ID_$id$count\" $repasteMarker>".$parameters."$gi_issues</annotation>";

			} else if ($smwValue instanceof SMWWikiPageValue || $smwValue->getTypeID() == '_uri') { // relation

				$title = htmlspecialchars($annotationTitle->getDBkey());
				$titleURLEscaped = htmlspecialchars(self::urlescape($annotationTitle->getDBkey()));
				$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
				SMW_GARDISSUE_WRONG_TARGET_VALUE), NULL, array($instance, $annotationTitle));
					
				$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);

				// metadata
				// check if metadata patch is applied
				$metadataTags = "<metadata id=\"".$id."_meta_".$count."\">";
				if (method_exists($smwValue, "getMetadataMap")) {
					// read metadata
					foreach($smwValue->getMetadataMap() as $mdProperty => $mdValues) {
						foreach($mdValues as $mdValue) {
							$metadataTags .= "<property name=\"".htmlspecialchars($mdProperty)."\">".htmlspecialchars($mdValue)."</property>";
						}
					}
				}
				$metadataTags .= "</metadata>";
                
				$propertyURI_att = 'uri="'.htmlspecialchars($ts->getFullURI($annotationTitle)).'"';
				if ($smwValue instanceof SMWWikiPageValue && !is_null($smwValue->getTitle())) {
					$targetNotExists = $smwValue->getTitle()->exists() ?  "" : "notexists=\"true\"";
					$uri_att = 'uri="'.htmlspecialchars($ts->getFullURI($smwValue->getTitle())).'"';
					$url_att = 'url="'.htmlspecialchars($smwValue->getTitle()->getFullURL()).'"';
					$singleProperties .= "<annotation $propertyURI_att title_url=\"$titleURLEscaped\" title=\"".$title."\" id=\"ID_$id$count\">".
					                     "<param isLink=\"true\" $uri_att $url_att $targetNotExists><![CDATA[".$smwValue->getTitle()->getPrefixedDBkey()."]]></param>".
					$gi_issues.$metadataTags.
					                     "</annotation>";
				} else if ($smwValue->getTypeID() == '_uri') {
					// any URI. External (=non wiki instances) are always of type _uri
					$uri = $smwValue->getWikiValue();
					if (strpos($uri, "#") !== false) {
						$local = substr($uri, strpos($uri, "#")+1);
					} else if (strrpos($uri, "/") !== false) {
						$local = substr($uri, strrpos($uri, "/")+1);
					} else {
						$local = $uri;
					}
					
					$uri_att = 'uri="'.htmlspecialchars($uri).'"';
					$url_att = 'url="'.htmlspecialchars($uri).'"';
					$singleProperties .= "<annotation $propertyURI_att title_url=\"$titleURLEscaped\" title=\"".$title."\" id=\"ID_$id$count\">".
                                         "<param isLink=\"true\" $uri_att $url_att><![CDATA[".$local."]]></param>".
					$metadataTags.
                                         "</annotation>";
				}
					
			} else if ($smwValue != NULL){ // normal attribute
				$typeURI = WikiTypeToXSD::getXSDType($smwValue->getTypeID());
				$typeURI_att = 'typeURI="'.htmlspecialchars($typeURI).'"';
				if ($smwValue->getTypeID() == '_che') {
					$isFormula = true;
					$chemistryParser->checkEquation(array_shift($smwValue->getDBkeys()));
					$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
					$value = "<param><![CDATA[".($formulaAsHTML)."]]></param>";
				} else if ($smwValue->getTypeID() == '_che') {
					$isFormula = true;
					$chemistryParser->checkEquation(array_shift($smwValue->getDBkeys()));
					$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
					$value = "<param><![CDATA[".($formulaAsHTML)."]]></param>";
				} else if ( $smwValue->getTypeID() == '_chf') {
					$isFormula = true;
					$chemistryParser->checkFormula(array_shift($smwValue->getDBkey()));
					$formulaAsHTML = html_entity_decode($chemistryParser->getHtmlFormat());
					$value = "<param><![CDATA[".($formulaAsHTML)."]]></param>";
				} else {
					// escape potential HTML in a CDATA section

					if ($smwValue->getTypeID() == '__tls') { // SMW_DV_TypeList
						$typesValues = $smwValue->getTypeValues();
						$value = "";
						foreach($typesValues as $typesValue) {
							$typeLabel = reset($typesValue->getTypeLabels());
							$typeTitle = Title::newFromText($typeLabel, SMW_NS_TYPE);
							$uri_att = 'uri="'.htmlspecialchars($ts->getFullURI($typeTitle)).'"';
							$value .= "<param isLink=\"true\" $uri_att><![CDATA[".html_entity_decode($typeTitle->getPrefixedDBkey())."]]></param>";
						}

					} else {
						// small hack for datetime type. It may occur that there is a T at the end.
						if ($smwValue->getTypeID() == '_dat') {
							$val = array_shift($smwValue->getDBkeys());
							$xsdValue = (substr($val, -1) == 'T') ? str_replace('T', '', $val) : $val;
						} else {
							$xsdValue = array_shift($smwValue->getDBkeys());
						}
						$value = strip_tags($xsdValue, "<sub><sup><b><i>");
						$value = "<param $typeURI_att><![CDATA[".html_entity_decode($value)." ".$smwValue->getUnit()."]]></param>";
					}

				}
				//special attribute mark for all things needed to get re-pasted in FF.
				$repasteMarker = $isFormula || strip_tags(array_shift($smwValue->getDBkeys())) != array_shift($smwValue->getDBkeys()) || $smwValue->getUnit() != '' ? "needRepaste=\"true\"" : "";

				$title = htmlspecialchars($annotationTitle->getDBkey());
				$titleURLEscaped = htmlspecialchars(self::urlescape($annotationTitle->getDBkey()));
				$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
				SMW_GARDISSUE_WRONG_UNIT), NULL, array($instance, $annotationTitle));
					
				// gardening issues
				$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
				$propertyURI_att = 'uri="'.htmlspecialchars($ts->getFullURI($annotationTitle)).'"';
				// metadata
				// check if metadata patch is applied
				$metadataTags = "<metadata id=\"".$id."_meta_".$count."\">";
				if (method_exists($smwValue, "getMetadataMap")) {
					// read metadata
					foreach($smwValue->getMetadataMap() as $mdProperty => $mdValues) {
						foreach($mdValues as $mdValue) {
							$metadataTags .= "<property name=\"".htmlspecialchars($mdProperty)."\">".htmlspecialchars($mdValue)."</property>";
						}
					}
				}
				$metadataTags .= "</metadata>";

				$singleProperties .= "<annotation $propertyURI_att title_url=\"$titleURLEscaped\" title=\"".$title."\" id=\"ID_".$id.$count."\" $repasteMarker>".
				$value.
				$gi_issues.
				$metadataTags.
				                      "</annotation>";
			}
			$count++;
		}
		return $singleProperties.$multiProperties;
	}




	/**
	 * Returns true, if $t is a pre-defined title.
	 */
	private static function isPredefined($t) {
		return ($t->getDBkey()== smwfGetSemanticStore()->domainRangeHintRelation->getDBkey())

		||  ($t->getDBkey()== smwfGetSemanticStore()->minCard->getDBkey())
		|| 	($t->getDBkey()== smwfGetSemanticStore()->maxCard->getDBkey())
		|| ($t->getDBkey()== smwfGetSemanticStore()->transitiveCat->getDBkey())
		|| ($t->getDBkey()== smwfGetSemanticStore()->symetricalCat->getDBkey());
	}

	/**
	 * Encode URL, but do not escape slashes (/)
	 *
	 * @param unknown_type $url
	 * @return unknown
	 */
	public static function urlescape($url) {
		$url_esc = urlencode($url);
		return str_replace("%2F", "/", $url_esc);
	}
}

