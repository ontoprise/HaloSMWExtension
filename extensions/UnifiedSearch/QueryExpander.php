<?php
define('US_SYNOMYM_EXP', 0);
define('US_SYNOMYM_BROAD_EXP', 1);
define('US_SYNOMYM_NARROW_EXP', 2);
define('US_SYNOMYM_EXP', 0);

class QueryExpander {


	private final static $LABEL = 'us_skos_preferedLabel';
	private final static $SYNONYM = 'us_skos_altLabel';
	private final static $HIDDEN = 'us_skos_hiddenLabel';
	private final static $BROADER = 'us_skos_broader';
	private final static $NARROWER = 'us_skos_narrower';
	private final static $DESCRIPTION = 'us_skos_description';
	private final static $EXAMPLE = 'us_skos_example';
	private final static $TERM = 'us_skos_term';

	public function __construct() {

	}

	public function expand($term, $mode = 0) {
		$result = "";
		$title = Title::newFromText($term);
		if ($title->exists()) {
			return;
		}

		$title = Title::newFromText($term, NS_CATEGORY);
		if ($title->exists()) {
			$subcategories = smwfGetSemanticStore()->getDirectSubCategories();
            $result .= self::opTerms($subcategories);
			$values = $this->getSKOSPropertyValues($title, $mode);
			
			return;
		}

		$title = Title::newFromText($term, SMW_NS_PROPERTY);
		if ($title->exists()) {
			return;
		}

		$title = Title::newFromText($term, NS_TEMPLATE);
		if ($title->exists()) {
			return;
		}


	}

	private function getSKOSPropertyValues($title, $mode) {
		$result = array();
		switch($mode) {
			case US_SYNOMYM_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_preferedLabel'));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);

				$prop = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_altLabel'));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);

				$prop = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_hiddenLabel'));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);
				break;
				
			case US_SYNOMYM_BROAD_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_broader'));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);
				break;
				
			case US_SYNOMYM_NARROW_EXP:
				$prop = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_narrower'));
				$values = smwfGetStore()->getPropertyValue($title, $prop);
				$result = array_merge($result, $values);
				break;
		}
	}
	
	private static function opTerms(array & $terms, $operator) {
		$i = 0;
		$result = "";
		foreach($terms as $t) {
			if ($t instanceof Title) {
				if ($i === 0) $result .= $t->getText(); else $result .= " $operator ".$t->getText();
			} else if ($t instanceof SMWPropertyValue ) {
				if ($i === 0) $result .= $t->getXSDValue(); else $result .= " $operator ".$t->getXSDValue();
			} else if (is_string($t)) {
				if ($i === 0) $result .= $t; else $result .= " $operator ".$t;
			}
			$i++;
		}
		return $result;
	}
}

?>