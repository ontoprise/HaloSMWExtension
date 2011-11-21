<?php
/**
 * @file
 * @ingroup ExportObjectLogicBot
 *
 * @defgroup ExportObjectLogicBot
 * @ingroup SemanticGardeningBots
 *
 * @author Kai Kühn
 *
 * Created on 16.02.2011
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

/**
 * Exports object logic from TSC.
 *
 * @author kuehn
 *
 */
class ExportObjectLogicBot extends GardeningBot {
	function __construct() {
		parent::GardeningBot("smw_exportobjectlogicbot");
	}

	public function getHelpText() {
		return wfMsg('smw_gard_exportobl_docu');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function isVisible() {
		return smwfIsTripleStoreConfigured();
	}


	/**
	 * Returns an array of GardeningParamObjects
	 */
	public function createParameters() {
		global $dfgLang, $wgLang;
		$param1 = new GardeningParamTitle('GARD_OBLEXPORT_BUNDLE', wfMsg('smw_gard_exportobl_bundlename'), SMW_GARD_PARAM_OPTIONAL);
		$param1->setAutoCompletion(true);
		$param1->setConstraints("ask: [[".$wgLang->getNsText(NS_CATEGORY).":".$dfgLang->getLanguageString('df_contentbundle')."]]");
		return array($param1);
	}

	private function exportCategories($bundleID, $serializer) {

		$pageValuesOfOntology = OEBPageSource::getCategories($bundleID);
		$store = smwfGetSemanticStore();
		$ts = TSNamespaces::getInstance();
		$obl = "";
		foreach($pageValuesOfOntology as $title) {
			$superCategories = $store->getDirectSuperCategories($title);
			$obl .= $serializer->serializeCategory($title, $superCategories);
		}
		return $obl;
	}
	private function exportProperties($bundleID, $serializer) {
		$pageValuesOfOntology = OEBPageSource::getProperties($bundleID);
		$store = smwfGetSemanticStore();
		$ts = TSNamespaces::getInstance();
		$obl = "";
		foreach($pageValuesOfOntology as $title) {
			$range = NULL;
			$type = NULL;
			// get domain and range/type
			$domains = $store->getDomainCategories($title);

			$typeValues = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($title), SMWDIProperty::newFromUserLabel('_TYPE'));
			if (count($typeValues) == 0) {
				// relation
				// NOTE: There MUST be only one range category for OBL-Export. So use first
				// and ignore any others
				$ranges = $store->getRangeCategories($title);
				$range = reset($ranges);
			} else{
				$typeValue = reset($typeValues);
				$id = $typeValue->getFragment();
				if (WikiTypeToXSD::isPageType($id)) {
					// relation
					// NOTE: There MUST be only one range category for OBL-Export. So use first
					// and ignore any others
					$ranges = $store->getRangeCategories($title);
					$range = reset($ranges);
				} else {
					// attribute
					$type = WikiTypeToXSD::getXSDType($id);

				}
			}

			// get inverse
			$inverseOfIRI = NULL;
			$inverseOfValues = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($title), SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$IS_INVERSE_OF->getDBkey()));
			$inverseOfValue = reset($inverseOfValues); // must be only 1
			if ($inverseOfValue !== false) {
				$inverseOfProperty = $inverseOfValue->getTitle();

			} else {
				$inverseOfProperty = NULL;
			}


			// get sub-properties
			$subProperties = $store->getDirectSubProperties($title);


			// get cardinalities
			$minCardValues = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($title), SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_MIN_CARDINALITY->getDBkey()));
			$minCardValue = reset($minCardValues); // must be only 1

			$maxCardValues = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($title), SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_MAX_CARDINALITY->getDBkey()));
			$maxCardValue = reset($maxCardValues); // must be only 1

			if ($minCardValue !== false) {
				$minCardValue->getNumber();
				$minCardValue = reset($minCardValue);
			} else {
				$minCardValue = "0";
			}
			if ($maxCardValue !== false) {
				$maxCardValue->getNumber();
				$maxCardValue = reset($maxCardValue);
			} else {
				$maxCardValue = "*";
			}

			// get sym/trans state
			$transitive = false;
			$symetrical = false;
			$categories = $store->getCategoriesForInstance($title);
			foreach($categories as $c) {
				if ($c->equals(SMWHaloPredefinedPages::$TRANSITIVE_PROPERTY)) {
					$transitive = true;
				}
				if ($c->equals(SMWHaloPredefinedPages::$SYMMETRICAL_PROPERTY)) {
					$symetrical = true;
				}
			}

			$obl .= $serializer->serializeProperty($title, $domains, $range, $type, $minCardValue, $maxCardValue, $transitive, $symetrical, $inverseOfProperty, $subProperties);

		}
		return $obl;
	}

	private function exportInstances($bundleID, $serializer) {
		global $dfgLang;
		$pageValuesOfOntology = OEBPageSource::getInstances($bundleID);

		$internalProperties = array(str_replace(" ","_",$dfgLang->getLanguageString('df_partofbundle')),
		$dfgLang->getLanguageString('df_ontologyversion'),
		$dfgLang->getLanguageString('df_instdir'),$dfgLang->getLanguageString('df_dependencies'),
		$dfgLang->getLanguageString('df_vendor'),$dfgLang->getLanguageString('df_rationale'), SMWHaloPredefinedPages::$ONTOLOGY_URI->getText());

		$internalCategories = array($dfgLang->getLanguageString('df_contentbundle'));

		$ts = TSNamespaces::getInstance();
		$obl = "";
		foreach($pageValuesOfOntology as $title) {

			if (!($title instanceof Title)) continue;

			$sd = smwfGetStore()->getSemanticData(SMWDIWikiPage::newFromTitle($title));
			$properties = $sd->getProperties();
			foreach($properties as $p) {
				$values = $sd->getPropertyValues($p);

				if (in_array($p->getKey(), $internalProperties)) {
					// internal property of DF
					continue;
				}
				if ($p->getKey() == '_INST') {
					$value = reset($values);
					if (in_array($value->getTitle()->getText(), $internalCategories)) {
						continue;
					}
					$obl .= $serializer->serializeInstance($title, $value->getTitle());
					continue;
				}
				if (!$p->isUserDefined()) {
					continue;
				}
				$propertyTitle= Title::newFromText($p->getKey(), SMW_NS_PROPERTY);
				if (!($propertyTitle instanceof Title)) continue;
				$obl .= $serializer->serializeInstanceValues($title, $propertyTitle, $values);
			}

		}
		return $obl;
	}


	private function exportRules($bundleID, $ontologyURI, $serializer) {
		global $smwgHaloTripleStoreGraph;
		// do not export rules if SemanticRules extensions is not available.
		if (!defined('SEMANTIC_RULES_VERSION')) {
			return "";
		}


		global $dfgLang;
		$pageValuesOfOntology = OEBPageSource::getRules($bundleID);

		$obl = "";
		$ruleTagPattern = '/<rule(.*?>)(.*?.)<\/rule>/ixus';
		foreach($pageValuesOfOntology as $title) {
			$rev = Revision::newFromTitle($title);
			$text = $rev->getText();
			preg_match_all($ruleTagPattern, trim($text), $matches);

			// at least one parameter and content?
			for($i = 0; $i < count($matches[0]); $i++) {
				$header = trim($matches[1][$i]);
				$ruletext = trim($matches[2][$i]);

				// parse header parameters
				$ruleparamterPattern = "/([^=]+)=\"([^\"]*)\"/ixus";
				preg_match_all($ruleparamterPattern, $header, $matchesheader);

				$native = false;
				$active = true;
				$type="USER_DEFINED";
				$tsc_uri = "";
				for ($j = 0; $j < count($matchesheader[0]); $j++) {
					if (trim($matchesheader[1][$j]) == 'name') {
						$name = trim($matchesheader[2][$j]);
					}

					if (trim($matchesheader[1][$j]) == 'type') {
						$type = $matchesheader[2][$j];
					}
					if (trim($matchesheader[1][$j]) == 'uri') {
						$tsc_uri = $matchesheader[2][$j];
					}
				}

			 $obl .= $serializer->serializeRule($title, $name, $tsc_uri, $ruletext);

			}
		}
		return $obl;
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

		if (array_key_exists('GARD_OBLEXPORT_BUNDLE', $paramArray) && trim($paramArray['GARD_OBLEXPORT_BUNDLE']) != '')  {
			$bundleName = $paramArray['GARD_OBLEXPORT_BUNDLE'];
		} else {
			$bundleName = NULL;
		}

		$serializer = new OEBOBLSerializer();

		global $wgLanguageCode, $smwgHaloTripleStoreGraph;

		if (!defined('DF_VERSION')) {
			return "Bundle export requires the DF to be installed. ".
                "[http://smwforum.ontoprise.com/smwforum/index.php/Deployment_Framework Deployment Framework]";
		}
		$downloadLink="";

		$this->setNumberOfTasks(3);

		// get ontology URI
		if (is_null($bundleName)) {
			$ontologyURI = trim($smwgHaloTripleStoreGraph);
		} else {
			$ontologyURI = DFBundleTools::getOntologyURI($bundleName);
			if (is_null($ontologyURI) || empty($ontologyURI)) {
				$ontologyURI = trim($smwgHaloTripleStoreGraph . "/ontology/$bundleName");
			}
		}

		// start export consisting of 6 subtasks
		$this->addSubTask(6);

		echo "\nCreate OBL export from bundle $bundleName...";
		$export = "";
		$export .= "\n\n// schema categories";
		$export .= $this->exportCategories($bundleName, $serializer);
		$this->worked(1);

		$export .= "\n\n// schema properties";
		$export .= $this->exportProperties($bundleName, $serializer);
		$this->worked(1);

		$export .= "\n\n// instances";
		$export .= $this->exportInstances($bundleName, $serializer);
		$this->worked(1);

		$export .= "\n\n// rules";
		$export .= $this->exportRules($bundleName, $ontologyURI, $serializer);
		$this->worked(1);

		echo "done.";

		echo "\nCreate temp directory...";
		$tempdir = self::getTempDir()."/oblExports";
		self::mkpath($tempdir);
		echo "done.";

		// read external artifacts (always OBL)
		echo "\nRead external artifacts...";
		$externalArtifacts = $serializer->serializeExternalArtifacts($bundleName);
		$this->worked(1);

		// create header and footer and concat all parts
		$header = $serializer->serializeHeader($ontologyURI, $bundleName);
		$footer = $serializer->serializeFooter($ontologyURI, $bundleName);
		$totalDocument = $header . "\n\n" . $export . "\n\n" . $externalArtifacts . "\n\n" . $footer;
		$this->worked(1);

		// save temporarily in a file
		$this->addSubTask(2);
		$f = "export".uniqid().".obl";
		$handle = fopen($tempdir."/".$f, "w");
		fwrite($handle, $totalDocument);
		fclose($handle);
		$this->worked(1);

		// and upload it
		echo "\nUploading file: $tempdir/$f to ".basename($tempdir."/".$f)."...";
		$exportFileTitle = Title::newFromText(basename($tempdir."/".$f), NS_IMAGE);
		$im_file = wfLocalFile($exportFileTitle);
		$im_file->upload($tempdir."/".$f, "auto-inserted file", "noText");
		echo "done.";
		$downloadLink .= "\n*[[".$exportFileTitle->getPrefixedText()."]]";
		unlink($tempdir."/".$f);
		echo "\nRemoved temporary file: $tempdir/$f";

		$this->worked(1);

		return "\n\n$downloadLink\n\n";

	}


	/**
	 * Creates the given directory.
	 *
	 * @param string $path
	 * @return unknown
	 */
	private static function mkpath($path) {
		if(@mkdir($path) || file_exists($path)) return true;
		return (self::mkpath(dirname($path)) && @mkdir($path));
	}
	/**
	 * Returns the home directory.
	 * (path with slashes only also on Windows)
	 *
	 * @return string
	 */
	private static function getTempDir() {
		if (self::isWindows()) {
			exec("echo %TEMP%", $out, $ret);
			return str_replace("\\", "/", reset($out));
		} else {
			exec('echo $TMPDIR', $out, $ret);
			$tmpdir = trim(reset($out));
			if (empty($tmpdir)) {
				$tmpdir = "/tmp"; // fallback
			}
			return $tmpdir;
		}
	}

	/**
	 * Checks if script runs on a Windows machine or not.
	 *
	 * @return boolean
	 */
	private static function isWindows() {
		static $thisBoxRunsWindows;

		if (! is_null($thisBoxRunsWindows)) return $thisBoxRunsWindows;

		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();
		//Get Systemstring
		preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
		//Check if it consists 'windows' as string
		preg_match('/[Ww]indows/',$ma[1],$os);
		$thisBoxRunsWindows= count($os) > 0;
		return $thisBoxRunsWindows;
	}

	/**
	 * Shows the download progress.
	 *
	 * @param float $per
	 */
	public function downloadProgress($length, $contentLength = 0) {

	}

	public function downloadStart($filename) {
		if (!is_null($filename)) echo "\nDownloading $filename...\n";
	}

	public function downloadFinished($filename) {
		if (!is_null($filename)) echo "\n$filename was downloaded.";
	}
}

/**
 * Selects wiki pages of a bundle or in the whole wiki.
 *  
 * @author kuehn
 *
 */
class OEBPageSource {
	public static function getPages($namespace, $bundleID = NULL) {
		global $dfgLang;
		if (is_null($bundleID)) {
			return smwfGetSemanticStore()->getPages(array($namespace));
		} else {
			$titles = array();
			$bundleIDDi = SMWDIWikiPage::newFromTitle(Title::newFromText($bundleID));
			$pageValuesOfOntology = smwfGetStore()->getPropertySubjects(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_partofbundle')), $bundleIDDi);
			foreach($pageValuesOfOntology as $pv) {
				$title = $pv->getTitle();
				if ($title->getNamespace() == $namespace) {
					$titles[] = $title;
				}
			}
			return $titles;
		}
	}

	public static function getCategories($bundleID = NULL) {
		return self::getPages(NS_CATEGORY, $bundleID);
	}

	public static function getProperties($bundleID = NULL) {
		return self::getPages(SMW_NS_PROPERTY, $bundleID);
	}

	public static function getInstances($bundleID = NULL) {
		return self::getPages(NS_MAIN, $bundleID);
	}

	public static function getRules($bundleID = NULL) {
		$ruleStore = SMWRuleStore::getInstance();
		$titles = array();
		if (is_null($bundleID)) {
			$rules = $ruleStore->getAllRulePages();

			foreach($rules as $pageID) {
				$title = Title::newFromID($pageID);
				$titles[] = $title;
			}
			return $titles;
		} else {
			global $dfgLang;


			$bundleIDDi = SMWDIWikiPage::newFromTitle(Title::newFromText($bundleID));
			$pageValuesOfOntology = smwfGetStore()->getPropertySubjects(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_partofbundle')), $bundleIDDi);
			$obl = "";
			$ruleTagPattern = '/<rule(.*?>)(.*?.)<\/rule>/ixus';
			foreach($pageValuesOfOntology as $pv) {
				$title = $pv->getTitle();
				$rules = $ruleStore->getRules($title->getArticleID());
				if (count($rules) > 0 ) {
					$titles[] = $title;
				}
			}
			return $titles;
		}
	}
}

/**
 * Abstract class for defining a OntologyExportSerializer (OEB).
 *
 * @author kuehn
 *
 */
abstract class OEBOntologySerializer {

	/**
	 * Serializes the header.
	 *
	 * @param string $uri Ontology URI
	 * @param string $bundleName The bundles name or null
	 *
	 * @return string
	 */
	public abstract function serializeHeader($uri, $bundleName);

	/**
	 * Serializes a footer
	 *
	 * @param string $uri Ontology URI
	 * @param string $bundleName The bundles name or null
	 *
	 * @return string
	 */
	public abstract function serializeFooter($uri, $bundleName);

	/**
	 * Serializes categories.
	 *
	 * @param Title $category
	 * @param Title[] $superCategories
	 *
	 * @return string
	 */
	public abstract function serializeCategory($category, $superCategories);

	/**
	 * Serializes properties.
	 *
	 * @param Title $property
	 * @param Title[] $domainCategories
	 * @param Title $rangeCategory
	 *          If $rangeCategory == null, then $xsdType must be set
	 * @param string $xsdType
	 *          Local part, e.g. string, anyURI, integer, ...)
	 *          If $xsdType == null, then $rangeCategory must be set
	 * @param int $minCardValue
	 * @param mixed $maxCardValue. A number, but may also be '*' if unlimited.
	 * @param boolean $transitive
	 * @param boolean $symetrical
	 * @param Title $inverseOfProperty
	 * @param Tuple<Title, boolean>[] $subProperties
	 *
	 * @return string
	 */
	public abstract function serializeProperty($property, $domainCategories, $rangeCategory, $xsdType, $minCardValue, $maxCardValue, $transitive, $symetrical, $inverseOfProperty, $subProperties);

	/**
	 * Serializes instances regarding their category memberships.
	 *
	 * @param Title $instance
	 * @param Title $category
	 *
	 * @return string
	 */
	public abstract function serializeInstance($instance, $category);

	/**
	 * Serializes instance values (=property annotations)
	 *
	 * @param Title $instance
	 * @param Title $property
	 * @param SMWDataItem[] $values
	 *
	 * @return string
	 */
	public abstract function serializeInstanceValues($instance, $property, $values);

	/**
	 * Serializes rules (if supported by the serializer). May return empty string.
	 *
	 * @param Title $title Title of the page where it is contained.
	 * @param string $name Rule's name
	 * @param string $rule_uri Rule's URI (may be null, then name is created by title's URI and name)
	 * @param string $ruletext Rule's text
	 *
	 * @return string
	 */
	public abstract function serializeRule($title, $name, $rule_uri, $ruletext);

	/**
	 * Serializes any kinds of external artifacts. May return empty string.
	 *
	 * @param string $bundleName
	 *
	 * @return string
	 */
	public abstract function serializeExternalArtifacts($bundleName);

	protected function getTSCIRI($title) {

		$ts = TSNamespaces::getInstance();
		$uri = TSCMappingStore::getTSCURI($title);
		if (is_null($uri)) {
			return $ts->getFullIRI($title);
		}
		return "<$uri>";
	}

	protected function getTSCURI($title) {

		$ts = TSNamespaces::getInstance();
		$uri = TSCMappingStore::getTSCURI($title);
		if (is_null($uri)) {
			return $ts->getFullURI($title);
		}
		return $uri;
	}

	/**
	 * Converts a non-compliant SMW representation of
	 * dateTime and boolean into an XSD representation.
	 *
	 * All others than dateTime and bool a returned unchanged.
	 *
	 * @param string $value
	 * @param string $typeID
	 *
	 * @return string
	 */
	protected function fixType($value, $typeID) {
		if ($typeID == SMWDataItem::TYPE_TIME) {
			$date = str_replace("/","-",$value);
			$datearray = date_parse($date);
			$year = $datearray['year'];
			$month = $datearray['month'];
			if ($month < 10) $month = "0$month";
			$day = $datearray['day'];
			if ($day < 10) $day = "0$day";
			$hour = $datearray['hour'];
			if (empty($hour)) $hour = 0;
			if ($hour < 10) $hour = "0$hour";
			$minute = $datearray['minute'];
			if (empty($minute)) $minute = 0;
			if ($minute < 10) $minute = "0$minute";
			$second = $datearray['second'];
			if (empty($second)) $second = 0;
			if ($second < 10) $second = "0$second";
			return "$year-$month-$day"."T"."$hour:$minute:$second";
		} else if ($typeID == SMWDataItem::TYPE_BOOLEAN) {
			if ($value == "0") return "false";
			if ($value == "1") return "true";
		}
		return $value;
	}

}

class OEBOBLSerializer extends OEBOntologySerializer {

	public function serializeHeader($uri, $bundleName) {
		global $wgSitename;
		$obl = $this->createOBLHeader($uri, is_null($bundleName) ? $wgSitename : $bundleName);
		$prefixString = $this->getNamespacePrefixes($bundleName);
		return $obl . "\n\n" . $prefixString;
	}

	public function serializeFooter($uri, $bundleName) {
		return ""; // no footer required for OBL
	}

	public function serializeCategory($title, $superCategories) {
		$obl = "";
		if (count($superCategories) == 0) {
			// root concept
			$iri = $this->getTSCIRI($title);
			$obl .= "\n".$iri.'[].';
		} else {
			// subconcept
			foreach($superCategories as $scat) {
				$iri = $this->getTSCIRI($title);
				$obl .= "\n$iri::";

				$iri = $this->getTSCIRI($scat);
				$obl .= "$iri.";
			}
		}
		return $obl;
	}

	public function serializeProperty($title, $domains, $range, $type, $minCardValue, $maxCardValue, $transitive, $symetrical, $inverseOfProperty, $subProperties) {
		$obl = "";
		$modifiers = '{'.$minCardValue.':'.$maxCardValue;
		if ($transitive) {
			$modifiers .= ",transitive";
		}
		if ($symetrical) {
			$modifiers .= ",symetrical";
		}
		if (!is_null($inverseOfProperty)) {
			$inverseOfIRI = $this->getTSCIRI($inverseOfProperty);
			$modifiers .= ",inverseOf($inverseOfIRI)";
		}

		$modifiers .= "}";

		// build OBL string
		$propertyIRI = $this->getTSCIRI($title);
		if (is_null($range)) {
			$typeIRI = "<".str_replace("xsd:", TSNamespaces::$XSD_NS, $type).">";
		} else{
			if ($range !== false) {
				$rangeIRI = $this->getTSCIRI($range);
			}
		}
		foreach($domains as $d) {
			$domainIRI = $this->getTSCIRI($d);
			if (is_null($range)) {
				$obl .= "\n$domainIRI [ $propertyIRI $modifiers *=> $typeIRI ].";
			} else{
				if ($range === false) {
					$rangeIRI = $domainIRI;
					print "\nWARNING: $propertyIRI does not define a range category although it is an object property. Domain category used instead.";
				}
				$obl .= "\n$domainIRI [ $propertyIRI $modifiers *=> $rangeIRI ].";
			}
		}

		foreach($subProperties as $tuple) {
			list($subProperty, $hasSubProperties) = $tuple;
			$subPropertyIRI = $this->getTSCIRI($subProperty);
			$obl .= "\n$subPropertyIRI << $propertyIRI.";
		}
		return $obl;
	}

	public function serializeInstance($instance, $category) {
		$instanceIRI = $this->getTSCIRI($instance);
		$objectIRI = $this->getTSCIRI($category);
		$obl = "\n$instanceIRI : $objectIRI. ";
		return $obl;
	}

	public function serializeInstanceValues($instance, $propertyTitle, $values) {
		$instanceIRI = $this->getTSCIRI($instance);
		$propertyIRI = $this->getTSCIRI($propertyTitle);
		$obl = "";
		foreach($values as $v) {
			$typeID = $v->getDIType();
			if ($typeID == SMWDataItem::TYPE_WIKIPAGE) {
				if (!($v->getTitle() instanceof Title)) continue;
				$objectIRI = $this->getTSCIRI($v->getTitle());
				$obl .= "\n$instanceIRI [ $propertyIRI -> $objectIRI ]. ";
			} else {
				$ser = TSHelper::serializeDataItem($v);

				$value = str_replace('"','\"', $ser);
				$value = '"'.$this->fixType($value, $typeID).'"';

				$type = WikiTypeToXSD::getXSDTypeFromTypeID($typeID);
				$typeIRI = "<".str_replace("xsd:", TSNamespaces::$XSD_NS, $type).">";
				$obl .= "\n$instanceIRI [ $propertyIRI -> $value^^$typeIRI ]. ";

			}
		}
		return $obl;
	}

	public function serializeRule($title, $name, $rule_uri, $ruletext) {
		if (empty($rule_uri)) {
			$ruleURI = $this->getTSCURI($title);
			$ruleIRI = "<$ruleURI$name>";
		} else {
			$ruleIRI = "<$rule_uri>";
		}
		$ruletext = $this->translateRuleURIs($ruletext);
		$obl = "\n".'@{'.$ruleIRI."}";
		$obl .= "\n$ruletext";
		$obl .= "\n";
		return $obl;
	}

	public function serializeExternalArtifacts($bundleName) {
		$externalArtifacts = DFBundleTools::getExternalArtifacts(is_null($bundleName) ? '+' : $bundleName);
		$obl = "";
		foreach($externalArtifacts as $extArt) {
			list($fileTitle, $ontologyURI) = $extArt;
			$localFile = wfLocalFile($fileTitle);
			if (!file_exists($localFile->getPath())) continue;
			$extObl = file_get_contents($localFile->getPath());
			$extObl = $this->stripOblHeader($extObl);
			$obl .= "\n\n" . $extObl;
		}
		return $obl;
	}

	/**
	 * Creates a default header for OBL files
	 *
	 * version 2.1
	 * encoding ISO-8859-1 (default PHP)
	 *
	 * @param string $uri Module
	 */
	private function createOBLHeader($uri, $bundleName = '') {
		$date = date(DATE_RFC822);
		$header = <<<ENDS
//         
// auto-generated Wiki ontology export of '$bundleName'
// date: $date
//         
:- version("2.1").
:- encoding("ISO-8859-1").

:- default prefix = "$uri".
:- prefix orn = "http://schema.ontoprise.com/reserved#".
:- prefix xsd = "http://www.w3.org/2001/XMLSchema#".
:- module = <$uri>.
        
ENDS;
		return $header;
	}

	/**
	 * Strips header of a OBL file.
	 *
	 * @param string $obl
	 * @return string
	 */
	private function stripOBLHeader($obl) {
		$lines = explode("\n", $obl);
		$length = count($lines);
		for($i = 0; $i < $length; $i++) {
			if (trim($lines[$i]) == '') continue;
			$start = substr(trim($lines[$i]),0,2);
			if ($start == '//' || $start == ':-') continue;
			break;
		}
		return implode("\n", array_slice($lines, $i));
	}

	private function getNamespacePrefixes($bundleName) {
		if (is_null($bundleName)) {
			$prefixes = DFBundleTools::getRegisteredPrefixes();
			$prefixString = "";
			foreach($prefixes as $prefix => $uri) {
				$prefixString .= "\n:- prefix $prefix = \"$uri\".";
			}
			return $prefixString;
		} else {
			$prefixString = "";
			$prefixes = DFBundleTools::getPrefixesUsedBy($bundleName, false);
			foreach($prefixes as $prefix) {
				$uri = TSCMappingStore::getNamespaceMapping($prefix);
				$prefixString .= "\n:- prefix $prefix = \"$uri\".";
			}
			return $prefixString;
		}
	}

	private function translateRuleURIs($ruletext) {
		$fullIRIRegex = "/<[^>]>/";
		$ruletext = preg_replace_callback($fullIRIRegex, array($this, 'translateRuleURIsCallback_iri'), $ruletext);

		$prefixIRIRegex = "/\w*#[\\w]+/";
		$ruletext = preg_replace_callback($prefixIRIRegex, array($this, 'translateRuleURIsCallback_prefix'), $ruletext);
		return $ruletext;
	}

	public function translateRuleURIsCallback_iri($matches) {
		$uri = substr($matches[0], 1, strlen($matches[0])-1);
		$title = TSHelper::getTitleFromURI($uri);
		if (is_null($title)) return $matches[0];
		$iri = $this->getTSCIRI($title);
		return $iri;
	}

	public function translateRuleURIsCallback_prefix($matches) {
		global $smwgHaloTripleStoreGraph;
		list($prefix, $local) = explode('#', $matches[0]);
		if ($prefix == 'prop') $prefix = 'property';
		if ($prefix == 'cat') $prefix = 'category';
		if ($prefix == '') $prefix = 'a';
		$title = TSHelper::getTitleFromURI($smwgHaloTripleStoreGraph.'/'.$prefix.'/'.$local);
		if (is_null($title)) return $matches[0];
		$iri = $this->getTSCIRI($title);
		return $iri;
	}
}

/**
 * Serializer for RDF/XML.
 * 
 * @author ??
 *
 */
class OEBRDFXMLSerializer extends OEBOntologySerializer {
    
	//TODO: implement methods (merge from OntologyExportBot)
	
	public  function serializeHeader($uri, $bundleName) {
		return '';
	}

	public  function serializeFooter($uri, $bundleName){
		return '';
	}

	public  function serializeCategory($category, $superCategories){
		return '';
	}

	public  function serializeProperty($property, $domainCategories, $rangeCategory, $xsdType, $minCardValue, $maxCardValue, $transitive, $symetrical, $inverseOfProperty, $subProperties){
		return '';
	}

	public  function serializeInstance($instance, $category){
		return '';
	}
	public  function serializeInstanceValues($instance, $property, $values){
		return '';
	}

	public  function serializeRule($title, $name, $rule_uri, $ruletext){
		return '';
	}

	public  function serializeExternalArtifacts($bundleName){
		return '';
	}

}

/*
 * Note: This bot filter has no real functionality. It is just a dummy to
 * prevent error messages in the GardeningLog. There are no gardening issues
 * about exporting. Instead there's a textual log.
 * */
define('SMW_EXPORTOBECTLOGIC_BOT_BASE', 1200);
require_once ($sgagIP . '/includes/SGA_GardeningIssues.php');
class ExportObjectLogicBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_EXPORTOBECTLOGIC_BOT_BASE);
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



/**
 * @file
 * @ingroup DFIO
 *
 * HTTP Downloader implementation.
 *
 * @author Kai Kühn / Ontoprise / 2009
 *
 */
class SGA_HttpDownload {
	private $header;



	public function __construct() {

	}

	/**
	 * Downloads a resource via HTTP protocol and stores it into a file.
	 *
	 * @param URL $url
	 * @param string $filename
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgress($percentage).
	 *                     downloadFinished($filename)
	 */
	public function downloadAsFileByURL($url, $filename, $credentials = "", $callback = NULL) {
		$partsOfURL = parse_url($url);

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$this->downloadAsFile($path, $port, $host, $filename, $credentials, $callback);
	}

	/**
	 * Downloads a resource via HTTP protocol and stores it into a file.
	 *
	 * @param string $path
	 * @param int $port
	 * @param string $host
	 * @param string $filename (may contain path)
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgress($percentage).
	 *                     downloadFinished($filename)
	 *      If null, an internal rendering method uses the console to show a progres bar and a finish message.
	 */
	public function downloadAsFile($path, $port, $host, $filename, $credentials = "", $callback = NULL) {

		$credentials = trim($credentials);
		if ($credentials == ':') $credentials = ''; // make sure the credentials are not empty by accident
		$address = gethostbyname($host);
		$handle = fopen($filename, "wb");
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		socket_connect($socket, $address, $port);
		$in = "GET $path HTTP/1.0\r\n";
		$in .= "Host: $host\r\n";
		if ($credentials != '') $in .= "Authorization: Basic ".base64_encode(trim($credentials))."\r\n";
		$in .= "\r\n";
		socket_write($socket, $in, strlen($in));
		$this->headerFound = false;
		$this->header = "";
		$length = 0;
		$cb = is_null($callback) ? $this : $callback;
		call_user_func(array($cb,"downloadStart"), basename(dirname($path))."/".basename($path));
		$out = socket_read($socket, 2048);

		do {

			if (! $this->headerFound) {
				$this->header .= $out;
				$index = strpos($this->header, "\r\n\r\n");

				if ($index !== false) {

					$out = substr($this->header, $index+4);
					$length = strlen($out);
					$this->header = substr($this->header, 0, $index);
					$this->checkHeader($path);
					$contentLength = $this->getContentLength();
					$this->headerFound = true;
				} else {

					continue;
				}
			}
			$length += strlen($out);
			fwrite($handle, $out);

			call_user_func(array($cb,"downloadProgress"), $length,$contentLength);

		}  while ($out = socket_read($socket, 2048));

		call_user_func(array($cb,"downloadProgress"), 100,100);

		call_user_func(array($cb,"downloadFinished"), $filename);
		fclose($handle);
		socket_close($socket);
	}

	/**
	 * Downloads a resource via HTTP protocol returns the content as string.
	 *
	 * @param string $path
	 * @param int $port
	 * @param string $host
	 * @param object $callback: An object with 2 methods:
	 *                     downloadProgres($percentage).
	 *                     downloadFinished($filename)
	 *      If null, an internal rendering method uses the console to show a progres bar and a finish message.
	 * @return string
	 */
	public function downloadAsString($path, $port, $host, $credentials = "", $callback = NULL) {

		$credentials = trim($credentials);
		if ($credentials == ':') $credentials = ''; // make sure the credentials are not empty by accident
		$address = gethostbyname($host);
		$res = "";
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		socket_connect($socket, $address, $port);
		$in = "GET $path HTTP/1.0\r\n";
		$in .= "Host: $host\r\n";
		if ($credentials != '') $in .= "Authorization: Basic ".base64_encode(trim($credentials))."\r\n";
		$in .= "\r\n";
		socket_write($socket, $in, strlen($in));
		$this->headerFound = false;
		$this->header = "";
		$length = 0;
		$cb = is_null($callback) ? $this : $callback;
		call_user_func(array($cb,"downloadStart"), basename(dirname($path))."/".basename($path));
		$out = socket_read($socket, 2048);

		do {
			if (! $this->headerFound) {
				$this->header .= $out;
				$index = strpos($this->header, "\r\n\r\n");

				if ($index !== false) {

					$out = substr($this->header, $index+4);
					$length = strlen($out);
					$this->header = substr($this->header, 0, $index);
					$this->checkHeader($path);
					$contentLength = $this->getContentLength();

					$this->headerFound = true;


				} else {


					continue;
				}
			}
			$length += strlen($out);
			$res .= $out;
			call_user_func(array($cb,'downloadProgress'), $length, $contentLength);

		} while ($out = socket_read($socket, 2048));


		call_user_func(array($cb,"downloadProgress"), 100,100);

		call_user_func(array($cb,"downloadFinished"), NULL);

		socket_close($socket);
		return $res;
	}


	private function checkHeader($path) {
		preg_match('/HTTP\/\d.\d\s+(\d+)/', $this->header, $matches);
		if (!isset($matches[1])) throw new SGA_HttpError("Invalid HTTP header");
		switch($matches[1]) {
			case 200: break; // OK
			case 400: throw new SGA_HttpError("Bad request: $path", 400, $this->header);
			case 401: throw new SGA_HttpError("Authorization required: $path", 401, $this->header);
			case 403: throw new SGA_HttpError("Access denied: $path", 403, $this->header);
			case 404: throw new SGA_HttpError("File not found: $path", 404, $this->header);
			default: throw new SGA_HttpError("Unknown HTTP error", $matches[1], $this->header);
		}


	}






	/**
	 * Parser content length from HTTP header
	 *
	 * @return int
	 */
	private function getContentLength() {
		preg_match("/Content-Length:\\s*(\\d+)/", $this->header, $matches);
		//if (!isset($matches[1])) throw new SGA_HttpError("Content-Length not set", 0, $this->header);
		if (!isset($matches[1])) return 0; //Return 0 if contentlength is not set, e.g. filtert by a proxy
		if (!is_numeric($matches[1])) throw new SGA_HttpError("Content-Length not numeric", 0, $this->header);
		return intval($matches[1]);
	}


}

class SGA_HttpError extends Exception {
	var $errcode;
	var $msg;
	var $httpHeader;

	public function __construct($msg, $errcode, $httpHeader) {
		$this->msg = $msg;
		$this->errcode = $errcode;
		$this->httpHeader = $httpHeader;
	}

	public function getMsg() {
		return $this->msg;
	}

	public function getErrorCode() {
		return $this->errcode;
	}

	public function getHeader() {
		return $this->httpHeader;
	}
}



