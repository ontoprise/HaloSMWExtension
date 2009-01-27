<?php
class SKOSVocabulary {
	
	 // SKOS terms
    public  static $LABEL;
    public  static $SYNONYM;
    public  static $HIDDEN;
    public  static $BROADER;
    public  static $NARROWER;
    public  static $DESCRIPTION;
    public  static $EXAMPLE;
    public  static $TERM;
    
    public static $ALL;
    
    public function __construct() {
    	self::$LABEL = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_preferedLabel'));
    	self::$SYNONYM = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_altLabel'));
    	self::$HIDDEN = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_hiddenLabel'));
    	self::$BROADER = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_broader'));
    	self::$NARROWER = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_narrower'));
    	self::$DESCRIPTION = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_description'));
    	self::$EXAMPLE = SMWPropertyValue::makeUserProperty(wfMsg('us_skos_example'));
    	self::$TERM = Title::newFromText(wfMsg('us_skos_term'), NS_CATEGORY);
    	
    	self::$ALL = array(self::$LABEL, self::$SYNONYM, self::$HIDDEN, 
    	                   self::$BROADER, self::$NARROWER, self::$DESCRIPTION,
    	                   self::$EXAMPLE, self::$TERM);
    }
}

new SKOSVocabulary(); // create one instance to initialize static fields
?>