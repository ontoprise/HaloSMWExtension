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
    public static $TYPES;
    
    public function __construct() {
    	self::$LABEL = SMWPropertyValue::makeUserProperty(wfMsgForContent('us_skos_preferedLabel'));
    	self::$SYNONYM = SMWPropertyValue::makeUserProperty(wfMsgForContent('us_skos_altLabel'));
    	self::$HIDDEN = SMWPropertyValue::makeUserProperty(wfMsgForContent('us_skos_hiddenLabel'));
    	self::$BROADER = SMWPropertyValue::makeUserProperty(wfMsgForContent('us_skos_broader'));
    	self::$NARROWER = SMWPropertyValue::makeUserProperty(wfMsgForContent('us_skos_narrower'));
    	self::$DESCRIPTION = SMWPropertyValue::makeUserProperty(wfMsgForContent('us_skos_description'));
    	self::$EXAMPLE = SMWPropertyValue::makeUserProperty(wfMsgForContent('us_skos_example'));
    	self::$TERM = Title::newFromText(wfMsgForContent('us_skos_term'), NS_CATEGORY);
    	
    	self::$ALL = array('us_skos_preferedLabel' => self::$LABEL, 'us_skos_altLabel' =>self::$SYNONYM, 'us_skos_hiddenLabel' => self::$HIDDEN, 
    	                   'us_skos_broader' => self::$BROADER, 'us_skos_narrower' => self::$NARROWER, 'us_skos_description' => self::$DESCRIPTION,
    	                  'us_skos_example' => self::$EXAMPLE, 'us_skos_term' => self::$TERM);
    	                   
    	self::$TYPES = array('us_skos_preferedLabel' => '_str', 
					    	'us_skos_altLabel' => '_str',
					    	'us_skos_hiddenLabel' => '_str',
					    	'us_skos_broader' => '_wpg',
					    	'us_skos_narrower' => '_wpg',
					    	'us_skos_description' => '_txt',
					    	'us_skos_example' => '_wpg');        
    }
}

new SKOSVocabulary(); // create one instance to initialize static fields
?>