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
 * Created on 26.04.2007
 *
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloDataExplorer
 *
 * @author Kai K�hn
 */
if ( !defined( 'MEDIAWIKI' ) ) die;


require_once("SMW_OntologyBrowserErrorHighlighting.php");

/**
 *
 * Serializes OntologyBrowser GUI objects to XML so they can transformed to HTML by a XSLT processor.
 * @author Kai Kuehn
 *
 */
class SMWOntologyBrowserXMLGenerator {

	/**
	 * Create a XML tree structure for visualizing a category tree.
	 *
	 * @param array & CategoryTreeElement $categoryTreeElement
	 * @param array & $resourceAttachments (Category title => array of rule URIs)
	 * @param int $limit  Max number of categories per partition
	 * @param int $partitionNum  Number of partition (0 <= $partitionNum <= total number / limit)
	 * @param boolean $rootLevel True, if partition on root level. Otherwise false.
	 *
	 * @return XML string
	 */
	public static function encapsulateAsConceptPartition(array & $categoryTreeElements, array & $resourceAttachments, $limit, $partitionNum, $rootLevel = false) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		if (count($categoryTreeElements) == $limit) {
			if ($partitionNum == 0) {
				$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
			} else {
				$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
			}
		}
		if (count($categoryTreeElements) < $limit && $partitionNum > 0) {
			$result .= "<categoryPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
		}
		$count++;
		$ts = TSNamespaces::getInstance();
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($categoryTreeElements as $e) {
			$t = $e->getTitle();
			$isLeaf = $e->isLeaf();
			if (smwfCheckIfPredefinedSMWHaloProperty($t)) {
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
			$uri_att = 'uri="'.htmlspecialchars($e->getURI()).'"';
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
	 * Create a XML structure for visualizing an instance list.
	 *
	 * @param array & InstanceListElement $instances
	 * @param int $limit Max number of instances per partition
	 * @param int $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
	 * @param string $dataSrc Denotes by which method the instance list was created.
	 *
	 * @return XML string
	 */
	public static function encapsulateAsInstancePartition($numOfresults, array & $instances, $limit, $partitionNum, $dataSrc = NULL) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		$dataSrc = (!is_null($dataSrc)) ? 'dataSrc="'.$dataSrc.'"' : '';

		if ($numOfresults == $limit) {
			if ($partitionNum == 0) {
				$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\" $dataSrc/>";
			} else {
				$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" $dataSrc/>";
			}
		}
		if ($numOfresults < $limit && $partitionNum > 0) {
			$result .= "<instancePartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
		}
		$count++;
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($instances as $t) {


			$instanceTitle = $t->getTitle();
			$instanceURI = $t->getURI();
			$url = $t->getURL();
			$categoryTreeElements = $t->getCategoryTreeElements();

			$categoryTitles = array();
			if (!is_null($categoryTreeElements)) {
				foreach($categoryTreeElements as $ce) {
					$categoryTitles[] = is_null($ce) ? NULL : $ce->getTitle();
				}
			}
			$metadata = $t->getMetadata();

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
				if (!is_null($instanceURI)) {
					$localurl_att = 'localurl="'.htmlspecialchars($url).'?uri='.urlencode($instanceURI).'"';
				}else{
					$localurl_att = 'localurl="'.htmlspecialchars($url).'"';
				}
			}

			$instanceURI_att = "";
			if (!is_null($instanceURI)) {
				$instanceURI_att = 'uri="'.htmlspecialchars($instanceURI).'"';
			}

			$notexist_att = "";
			if (!$instanceTitle->exists()) {
				$notexist_att = 'notexists="true"';
			}

			$categoryXML = "";
			$inherited = 'inherited="true"';

			foreach($categoryTitles as $ctitle) {
				if (is_null($ctitle)) {
					$inherited = ""; // is not inherited
					continue;
				}
				$categoryXML .= "<category>".htmlspecialchars($ctitle->getDBkey())."</category>";
			}
			$result = $result."<instance $instanceURI_att $localurl_att title_url=\"$titleURLEscaped\" title=\"".$titleEscaped."\" namespace=\"$namespace\" $notexist_att img=\"instance.gif\" id=\"ID_$id$count\" $inherited>$categoryXML$gi_issues$metadataTags</instance>";

			$count++;
		}

		return $result == '' ? "<instanceList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_instances')."\"/>" : "<instanceList>$result</instanceList>";
	}

	/**
	 * Create an XML tree structure for visualizing the property tree.
	 *
	 * @param array & PropertyTreeElement $propertyTreeElements
	 * @param array & $resourceAttachments (Property title => array of rule URIs)
	 * @param int $limit Max number of properties per partition
	 * @param int $partitionNum Number of partition (0 <= $partitionNum <= total number / limit)
	 * @param boolean $rootLevel True, if partition on root level. Otherwise false
	 *
	 * @return XML string
	 */
	public static function encapsulateAsPropertyPartition(array & $propertyTreeElements, array & $resourceAttachments, $limit, $partitionNum, $rootLevel = false) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		if (count($propertyTreeElements) == $limit) {
			if ($partitionNum == 0) {
				$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hidePreviousArrow=\"true\"/>";
			} else {
				$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\"/>";
			}
		}
		if (count($propertyTreeElements) < $limit && $partitionNum > 0) {
			$result .= "<propertyPartition id=\"ID_$id$count\" partitionNum=\"$partitionNum\" length=\"$limit\" hideNextArrow=\"true\"/>";
		}
		$count++;
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($propertyTreeElements as $e) {

			//list($t, $isLeaf, $propertyURI, $localURL) = $e;
			$t = $e->getTitle();
			$isLeaf = $e->isLeaf();
			$propertyURI = $e->getURI();
			$localURL = $e->getURL();

			if (smwfCheckIfPredefinedSMWHaloProperty($t)) {
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
	 * Creates an XML structure for visualizing a list of annotations.
	 *
	 * @param array & Annotation $propertyAnnotations
	 * @param InstanceListElement $instanceListElement Subject of annotation (may be null, if annotations for multiple subjects)
	 *
	 * @return XML string
	 */
	public static function encapsulateAsAnnotationList(array & $propertyAnnotations, $instanceListElement) {
		$result = "";
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		$instance = !is_null($instanceListElement) ? $instanceListElement->getTitle() : NULL;
		foreach($propertyAnnotations as $a) {

			$property = $a->getProperty();
			$values = $a->getValues();
			$inferred_values = $a->getInferredValues();

			$result .= SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotation($instance, $property, $values, false);
			$result .= SMWOntologyBrowserXMLGenerator::encapsulateAsAnnotation($instance, $property, $inferred_values, true);

		}
		// get low cardinality issues and "highlight" missing annotations. This is an exception because missing annotations do not exist.
		if (!is_null($instance)) {
			$issues = $gi_store->getGardeningIssues('smw_consistencybot', SMW_GARDISSUE_MISSING_ANNOTATIONS, NULL, $instance);
			$result .= SMWOntologyBrowserErrorHighlighting::getMissingAnnotations($issues);
			$instanceTitleEscaped = htmlspecialchars($instance->getDBkey());
			$namespaceInstance = $instance->getNsText();
			$titleURLEscaped = htmlspecialchars(self::urlescape($instance->getDBkey()));

			return $result == '' ? "<annotationsList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_annotations')."\" title_url=\"$titleURLEscaped\" title=\"$instanceTitleEscaped\" namespace=\"$namespaceInstance\"/>" : "<annotationsList>".$result."</annotationsList>";
		} else{
			return $result == '' ? "<annotationsList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_annotations')."\"/>" : "<annotationsList>".$result."</annotationsList>";
		}
	}


	/**
	 * Creates an XML structure for visualizing a list schema properties
	 *
	 * @param array & PropertySchemaElement $properties
	 *
	 * @return XML string
	 */
	public static function encapsulateAsPropertyList(array & $properties) {

		$count = 0;
		$propertiesXML = "";
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($properties as $t) {
			$directIssues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $t->getPropertyTitle());
			$propertiesXML .= SMWOntologyBrowserXMLGenerator::encapsulateAsProperty($t, $count, $directIssues);
			$count++;
		}

		return $propertiesXML == '' ? "<propertyList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_properties')."\"/>" : "<propertyList>".$propertiesXML."</propertyList>";
	}

	/**
	 * Creates an XML structure for visualizing a schema property
	 *
	 * @param PropertySchemaElement $propertySchemaElement
	 * @param int count continuous number for generating new IDs
	 * @param array & issues Gardening issues for that property
	 *
	 * @return XML string (fragment)
	 */
	private static function encapsulateAsProperty($propertySchemaElement, $count, array & $issues) {
		$id = uniqid (rand());
		$content = "";

		// unpack schemaData array
		$schemaData=$propertySchemaElement->getSchemaData();
		$title =$schemaData->getTitle();
		$minCardinality = $schemaData->getMinCard();
		$maxCardinality = $schemaData->getMaxCard();
		$type = $schemaData->getType();
		$isMemberOfSymCat = $schemaData->isSymetrical();
		$isMemberOfTransCat = $schemaData->isTransitive();
		$range = $schemaData->getRange();
		$inherited = $schemaData->isInherited() ? "inherited=\"true\"" : "";

		$ts = TSNamespaces::getInstance();

		list($ns, $fragment) = explode("#", $type);
		$noedit = "";
		if ($fragment == '_rec') {
			// records can not be edited
			$noedit = "noedit=\"true\"";
			$subProperties = SMWRecordValue::findPropertyDataItems(SMWDIProperty::newFromUserLabel($title->getText()));

			foreach($subProperties as $subProperty) {
				$propertyName = $subProperty->getDiWikiPage()->getTitle()->getText();
				$content .= "<rangeType>".$propertyName."</rangeType>";
			}

		} else {

			// primitive or binary property

			$typeList = SMWTypesValue::newFromTypeId($fragment);
			$propertyName = $typeList->getLongWikiText();
			$range_att = $range != '' ? "category=\"".str_replace('"', "&quot;", $range)."\"" : "";
			$content .= "<rangeType $range_att>".$propertyName."</rangeType>";

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
		$uri_att = !is_null($propertySchemaElement->getURI()) ? 'uri="'.htmlspecialchars($propertySchemaElement->getURI()).'"': "";
		return "<property $noedit $uri_att title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" id=\"ID_".$id.$count."\" " .
					"$minCardText $maxCardText $isSymetricalText $isTransitiveText $numberOfUsageAtt $inherited>".
		$content.$gi_issues.
				"</property>";

	}

	/**
	 * Creates an XML structure for visualizing an annotation.
	 *
	 * @param $instance Title of subject
	 * @param $annotation Annotation
	 * @param mixed array of tuple ($smwValues,$uri) or single tuple ($smwValues,$uri)
	 *
	 * @return XML string (fragment)
	 */
	private static function encapsulateAsAnnotation($instance, $annotation, $smwValues, $inferred = false) {
		$id = uniqid (rand());
		$count = 0;
		$annotations = "";

		$isFormula = false;

		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		$ts = TSNamespaces::getInstance();
		$inferred_att = $inferred ? 'inferred="true"' : '';
		if (!is_array($smwValues)) $smwValues = array($smwValues);
		foreach($smwValues as $v) {
				
			list($smwValue, $uri) = $v;
			if ($smwValue instanceof SMWRecordValue) { // n-ary property

				$needRepaste = false;
				$parameters = "";
				$sd = $smwValue->getDataItem()->getSemanticData();
				$properties = $sd->getProperties();
				foreach($properties as $propertyDi) {
					$dis = $sd->getPropertyValues($propertyDi);
					foreach($dis as $di) {
						if (is_null($di)) {
							$parameters .= "<param></param>";
							continue;
						}
						$parameters .= self::createValueAsXML(SMWDataValueFactory::newDataItemValue($di, null), $uri);
					}
				}

				// serialize titles
				$title_att = htmlspecialchars($annotation->getPropertyTitle()->getDBkey());
				$titleURLEscaped_att = htmlspecialchars(self::urlescape($annotation->getPropertyTitle()->getDBkey()));

				// serialize gardening issues
				if (!is_null($instance)) {

					$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
					SMW_GARD_ISSUE_MISSING_PARAM, SMW_GARDISSUE_WRONG_TARGET_VALUE), NULL, array($instance, $annotation->getPropertyTitle()));
					$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);
				} else{
					$gi_issues = "";
				}

				// no metadata available on n-ary properties
				$annotations .= "<annotation  title_url=\"$titleURLEscaped_att\" title=\"".$title_att."\" id=\"ID_$id$count\" $inferred_att>"
				                .$parameters.
				                $gi_issues
				                ."</annotation>";

			} else { // all other properties

				// get annotation value (param node)
				$value = self::createValueAsXML($smwValue, $uri);
				
				$propertyDBkey = $annotation->getPropertyTitle()->getDBkey();
				$title_att = htmlspecialchars($propertyDBkey);
				$titleURLEscaped_att = htmlspecialchars(self::urlescape($propertyDBkey));

				if (!is_null($instance)) {
					$issues = $gi_store->getGardeningIssuesForPairs('smw_consistencybot', array(SMW_GARDISSUE_WRONG_DOMAIN_VALUE, SMW_GARDISSUE_TOO_LOW_CARD, SMW_GARDISSUE_TOO_HIGH_CARD,
					SMW_GARDISSUE_WRONG_UNIT), NULL, array($instance, $annotation->getPropertyTitle()));
				} else{
					$issues = array();
				}
				// gardening issues
				$gi_issues = SMWOntologyBrowserErrorHighlighting::getAnnotationIssuesAsXML($issues, $smwValue);

				// metadata
				// check if metadata patch is applied
				$metadataTags = "<metadata id=\"".$id."_meta_".$count."\">";
				if (method_exists($smwValue, "getMetadataMap")) {

					foreach($smwValue->getMetadataMap() as $mdProperty => $mdValues) {
						foreach($mdValues as $mdValue) {
							$metadataTags .= "<property name=\"".htmlspecialchars($mdProperty)."\">".htmlspecialchars($mdValue)."</property>";
						}
					}
				}
				$metadataTags .= "</metadata>";

				$propertyURI_att = 'uri="'.htmlspecialchars($annotation->getURI()).'"';
				$annotations .= "<annotation $propertyURI_att title_url=\"$titleURLEscaped_att\" title=\"".$title_att."\" id=\"ID_".$id.$count."\" $inferred_att>".
				$value.         // values
				$gi_issues.     // gardening issues
				$metadataTags.  // metadata
				                "</annotation>";
			}
			$count++;
		}
		return $annotations;
	}

	/**
	 * Creates the annotation value in XML.
	 *
	 * @param mixed SMWDataItem / SMWDataValue $smwValue
	 * @param URI (in case of a SMWWikiPageValue or SMWURIValue otherwise NULL)
	 */
	private static function createValueAsXML($smwValue, $uri) {
		$ts = TSNamespaces::getInstance();
		
         if ($smwValue instanceof SMWDataItem) {
         	$smwValue = SMWDataValueFactory::newDataItemValue($smwValue, null);
         }

		if ($smwValue instanceof SMWWikiPageValue || $smwValue->getTypeID() == '_uri') { // relation


			if ($smwValue instanceof SMWWikiPageValue && !is_null($smwValue->getTitle())) {
				$targetNotExists = $smwValue->getTitle()->exists() ?  "" : "notexists=\"true\"";
				$uri_att = 'uri="'.htmlspecialchars($uri).'"';
				$url_att = 'url="'.htmlspecialchars($smwValue->getTitle()->getFullURL()).'"';
				$value = "<param isLink=\"true\" $uri_att $url_att $targetNotExists><![CDATA[".$smwValue->getTitle()->getPrefixedDBkey()."]]></param>";
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
				$value = "<param isLink=\"true\" $uri_att $url_att><![CDATA[".$local."]]></param>";

			}

		} else if ($smwValue != NULL){ // normal attribute
			$typeURI = WikiTypeToXSD::getXSDType($smwValue->getTypeID());
			$typeURI_att = 'typeURI="'.htmlspecialchars($typeURI).'"';

			// escape potential HTML in a CDATA section

			if ($smwValue->getTypeID() == '__pls') { // SMW_DV_TypeList

				foreach($smwValue as $subProperty) {
					$propertyName = $subProperty->getDiWikiPage()->getTitle()->getText();
					$value .= "<param isLink=\"true\"><![CDATA[".html_entity_decode($propertyName)."]]></param>";
				}

			} else {
				// small hack for datetime type. It may occur that there is a T at the end.
				if ($smwValue->getTypeID() == '_dat') {
					$xsdValue = $smwValue->getWikiValue();
					$xsdValue = (substr($xsdValue, -1) == 'T') ? str_replace('T', '', $xsdValue) : $xsdValue;
				} else {
					$xsdValue = $smwValue->getWikiValue();

				}
				$value = strip_tags($xsdValue, "<sub><sup><b><i>");
				$value = "<param $typeURI_att><![CDATA[".html_entity_decode($value)."]]></param>";
			}

		}
		return $value;
	}

	
	/**
	 * Encode URL, but do not escape slashes (/)
	 *
	 * @param string $url
	 * @return string
	 */
	public static function urlescape($url) {
		$url_esc = urlencode($url);
		return str_replace("%2F", "/", $url_esc);
	}
}

/**
 * XMLTreeObject represents a node in a tree.
 * It can have other XMLTreeObjects as children.
 *
 * A tree can be serialized as XML which can be rendered by the OntologyBrowser.
 *
 * @author Kai Kühn
 */
class XMLTreeObject {

	/*
	 * Title
	 */
	private $title;

	/*
	 * array of Title
	 */
	private $children;

	/*
	 * boolean
	 */
	private $hasChild;

	public function __construct($title, $hasChild = true) {
		$this->title = $title;
		$this->children = array();
		$this->hasChild = $hasChild;
	}

	/**
	 * Returns title of node.
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns the children of the node.
	 *
	 * @return array of Title
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * True if node has at least one child
	 * @return boolean
	 */
	public function hasChild() {
		return $this->hasChild;
	}
	/**
	 * Adds a child node if it does not already exist.
	 */
	public function addChild($childTitle, $hasChild = true) {
		if (!array_key_exists($childTitle->getText(), $this->children)) {
			$this->children[$childTitle->getText()] = new XMLTreeObject($childTitle, $hasChild);
		}
		return $this->children[$childTitle->getText()];
	}

	/**
	 * Serializes the tree structure (without root node)
	 */
	public function serializeAsXML($type) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		$lengthOfPath = count($this->children);
		foreach($this->children as $title => $treeObject) {
			$isExpanded = count($treeObject->children) == 0 ? "false" : "true";
			$title_esc = htmlspecialchars($treeObject->getTitle()->getDBkey());
			$titleURLEscaped = htmlspecialchars(SMWOntologyBrowserXMLGenerator::urlescape($treeObject->getTitle()->getDBkey()));
			$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $treeObject->getTitle());
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
			if (!$treeObject->hasChild()) $isLeaf_att = 'isLeaf="true"'; else $isLeaf_att ='';
			$result .= "<$type title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" $isLeaf_att img=\"$type.gif\" id=\"ID_$id$count\" expanded=\"$isExpanded\">";
			$result .= $gi_issues;
			$result .= $treeObject->serializeAsXML($type);
			$result .= "</$type>";
			$count++;
		}
		return $result;
	}



	/**
	 * Sorts the children (possibly recursive!)
	 */
	public function sortChildren($recursive = true) {
		if ($recursive) {
			$this->_sortChildren($this);
		} else {
			ksort($this->children);
		}
	}

	private function _sortChildren($treeObject) {
		foreach($treeObject->children as $title => $to) {
			$this->_sortChildren($to);
		}
		ksort($treeObject->children);
	}

}


