<?php


/**
 * @file
 * @ingroup SMWHaloMiscellaneous
 * 
 * Provides predefined text for different kinds of pages.
 * 
 * @author: Kai Kühn
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
		
		for($i = 0; $i < count($parameters); $i++) {
			$parameters[$i] = trim(str_replace("|", "", $parameters[$i]));
		}
		
		foreach($parameters as $m) {
			if ($m == '') continue;
			if (array_key_exists($m, $parameterMappings)) {
				$mappedValue = self::mappedValue($parameterMappings[$m]);
				$resultText .= "\n|$m=".$mappedValue;
			} else {
				$resultText .= "\n|$m=";
			}
		}
		
		// adds additional parameters not found in the template
		$additionalParameters = array_diff(array_keys($parameterMappings), $parameters);
		
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
