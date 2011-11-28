<?php


/**
 * @file
 * @ingroup SMWHaloMiscellaneous
 *
 * Provides predefined text for different kinds of pages.
 *
 * @author: Kai K�hn
 *
 * Configure in LocalSettings.php (example):
 *
 *  $smwhgAutoTemplates = array(NS_CATEGORY => "Category");
 *  $smwhgAutoTemplatesParameters = array (NS_CATEGORY => array("PAGENAME"=>'$FULLPAGENAME', "Has author"=>'$USERNAME'));
 *
 */
class SMWPredefinitions {

	private static $title;

	/**
	 * Returns predefined text for a newly created page (only via Halo AJAX API)
	 *
	 * @param Title $title current Title
	 * @param string $categoryTemplate template name (without namespace prefix)
	 * @param hash array $parameterMappings (name=>value)
	 * @return string predefined text
	 */
	public static function getPredefinitions($title, $template, $parameterMappings) {
		self::$title = $title;
		// parse template parameters
		$rev = Revision::newFromTitle(Title::newFromText($template, NS_TEMPLATE));
		if (is_null($rev)) return "";
		$text = $rev->getText();
		preg_match_all('/\{\{\{([^}]+)\}\}\}/', $text, $matches);

		// serialize template with predefined values
		$resultText = "\n{{".$template;
		$parameters = array_unique($matches[1]);
		$normalizedParameters=array();
		foreach($parameters as $p) {
			$normalizedParameters[] = trim(str_replace("|", "", $p));
		}

		foreach($normalizedParameters as $m) {
			if ($m == '') continue;
			if (array_key_exists($m, $parameterMappings)) {
				$mappedValue = self::mappedValue($parameterMappings[$m]);
				$resultText .= "\n|$m=".$mappedValue;
			} else {
				$resultText .= "\n|$m=";
			}
		}

		// adds additional parameters not found in the template
		$additionalParameters = array_diff(array_keys($parameterMappings), $normalizedParameters);

		foreach($additionalParameters as $sp) {
			$mappedValue = self::mappedValue($parameterMappings[$sp]);
			if ($sp != '') $resultText .= "\n|$sp=".$mappedValue;
		}
		$resultText .= "\n}}\n";
		return $resultText;
	}

	/**
	 * Replace special variables.
	 *
	 * @param string $value
	 * @return string
	 */
	private static function mappedValue($value) {
		global $wgContLang;
		$userNsText = $wgContLang->getNsText(NS_USER);
		switch($value) {
			case '$USERNAME': global $wgUser; return $userNsText.":".$wgUser->getName();
			case '$PAGENAME': return self::$title->getText();
			case '$FULLPAGENAME': return self::$title->getPrefixedText();
			default: return $value;
		}
	}
}

/**
 * SMWHalo pre-defines some properties and categories.
 *
 * @author kuehn
 *
 */
class SMWHaloPredefinedPages {

	/**
	 * Domain and range property.
	 * Determines the domain and range of a property.
	 *
	 * It is a record property with take the domain as first parameter
	 * and the range as second. The range is optional.
	 *
	 * It is defined as:
	 *    [[has type::Type:Record]]
	 *    [[has fields::Property:Has domain; Property:Has range]]
	 *
	 * @var Title
	 */
	public static $HAS_DOMAIN_AND_RANGE;
	
	/**
	 * Domain property of $HAS_DOMAIN_AND_RANGE
	 * 
	 * @var Title
	 */
	public static $HAS_DOMAIN;
	
	/**
     * Range property of $HAS_DOMAIN_AND_RANGE
     * 
     * @var Title
     */
	public static $HAS_RANGE;

	/**
	 * Minimum cardinality.
	 * Determines how often an attribute or relations must be instantiated per instance at least.
	 * Allowed values: 0..n, default is 0.
	 *
	 * @var Title
	 */
	public static $HAS_MIN_CARDINALITY;

	/**
	 * Maximum cardinality.
	 * Determines how often an attribute or relations may instantiated per instance at most.
	 * Allowed values: 1..*, default is *, which means unlimited.
	 *
	 * @var Title
	 */
	public static $HAS_MAX_CARDINALITY;

	/**
	 * Marker category for a transitive property
	 * All relations of this category are transitive.
	 *
	 * @var Title
	 */
	public static $TRANSITIVE_PROPERTY;

	/**
	 * Marker category for a symmetrical property.
	 * All relations of this category are symetrical.
	 *
	 * @var Title
	 */
	public static $SYMMETRICAL_PROPERTY;

	/**
	 * Inverse property. Binary property which defines the inverse property.
	 *
	 * [[has type::Type:Page]]
	 *
	 * @var Title
	 */
	public static $IS_INVERSE_OF;

	/**
	 * Ontology representation in OntoStudio.
	 *
	 * [[has type::Type:URL]]
	 *
	 * @var Title
	 */
	public static $ONTOLOGY_URI;

	public function __construct() {
		global $smwgHaloContLang;
		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$smwSpecialCategories = $smwgHaloContLang->getSpecialCategoryArray();

		self::$HAS_DOMAIN_AND_RANGE = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT], SMW_NS_PROPERTY);
		self::$HAS_DOMAIN = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN], SMW_NS_PROPERTY);
		self::$HAS_RANGE = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_RANGE], SMW_NS_PROPERTY);
		self::$HAS_MIN_CARDINALITY = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MIN_CARD], SMW_NS_PROPERTY);
		self::$HAS_MAX_CARDINALITY = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_MAX_CARD], SMW_NS_PROPERTY);
		self::$TRANSITIVE_PROPERTY = Title::newFromText($smwSpecialCategories[SMW_SC_TRANSITIVE_RELATIONS], NS_CATEGORY);
		self::$SYMMETRICAL_PROPERTY = Title::newFromText($smwSpecialCategories[SMW_SC_SYMMETRICAL_RELATIONS], NS_CATEGORY);
		self::$IS_INVERSE_OF = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_IS_INVERSE_OF], SMW_NS_PROPERTY);
		self::$ONTOLOGY_URI = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_ONTOLOGY_URI], SMW_NS_PROPERTY);

	}
}

