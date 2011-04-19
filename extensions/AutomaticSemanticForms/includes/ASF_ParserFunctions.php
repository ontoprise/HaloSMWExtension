<?php

/*
 * This class provides some Parser Functions
 * that are shipped together with the Automatic
 * Semantic Forms Extension
 */
class ASFParserFunctions {

	/*
	 * Setup parser functions
	 */
	static function registerFunctions( &$parser ) {
		$parser->setFunctionHook( 'CreateSilentAnnotations', 
			array( 'ASFParserFunctions', 'renderSilentAnnotationsTemplate' ), SFH_NO_HASH + SFH_OBJECT_ARGS);
		$parser->setFunctionHook( 'shownow', 
			array( 'ASFParserFunctions', 'renderShowNow' ), SFH_OBJECT_ARGS); 
		$parser->setFunctionHook( 'asfforminput', 
			array( 'ASFParserFunctions', 'renderASFFormInput' ) );
		$parser->setFunctionHook( 'collapsableFieldSetStart', 
			array( 'ASFParserFunctions', 'renderCollapsableFieldSetStart'));
		$parser->setFunctionHook( 'collapsableFieldSetEnd', 
			array( 'ASFParserFunctions', 'renderCollapsableFieldSetEnd'));
		$parser->setFunctionHook( 'collapsableFieldSet', 
			array( 'ASFParserFunctions', 'renderCollapsableFieldSet'));
		$parser->setFunctionHook( 'qTip', 
			array( 'ASFParserFunctions', 'renderQTip'));
		$parser->setFunctionHook( 'qTipHelp', 
			array( 'ASFParserFunctions', 'renderQTipHelp'));

			
		global $wgHooks;
		$wgHooks['ParserAfterTidy'][] = 'ASFParserFunctions::finallyRenderShowNow';
		
		return true;
	}
	
	
	/*
	 * Initialize magic words
	 */
	static function languageGetMagic( &$magicWords, $langCode = "en" ) {
		$magicWords['CreateSilentAnnotations']	= array ( 0, 'CreateSilentAnnotations' );
		$magicWords['shownow']	= array ( 0, 'shownow' );
		$magicWords['asfforminput'] = array ( 0, 'asfforminput' );
		$magicWords['collapsableFieldSetStart'] = array(0, 'collapsableFieldSetStart');
		$magicWords['collapsableFieldSetEnd'] = array(0, 'collapsableFieldSetEnd');
		$magicWords['collapsableFieldSet'] = array(0, 'collapsableFieldSet');
		$magicWords['qTip'] = array(0, 'qTip');
		$magicWords['qTipHelp'] = array(0, 'qTipHelp');
		
		return true;
	}
	
	
	/*
	 * Displays a small help icon with a qTip Tooltip
	 */
	static function renderQTipHelp( &$parser) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser
		
		$tip = '';
		if(array_key_exists(0, $params)){
			$tip .= trim($params[0]);
			$tip = $parser->internalParse($tip);
		}
		
		global $wgScriptPath;
		$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/help.gif';
		
		$result = '<span class="asf_use_qtip">';
		$result .= '<img src="'.$imgSRC.'">';
		$result .= '<span class="asf_qtip_content" style="display: none">';
		$result .= $tip;
		$result .= '</span>';
		$result .= '</img></span>';
		
		return $parser->insertStripItem( $result, $parser->mStripState );
	}
	
	
	static function renderQTip( &$parser) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser
		
		$content = "";
		if(array_key_exists(0, $params)){
			$content .= trim($params[0]);
			$content = $parser->internalParse($content);
		}
		
		$tip = '';
		if(array_key_exists(1, $params)){
			$tip .= trim($params[1]);
			$tip = $parser->internalParse($tip);
		}
		
		$result = '<span class="asf_use_qtip">';
		$result .= $content;
		$result .= '<span class="asf_qtip_content" style="display: none">';
		$result .= $tip;
		$result .= '</span>';
		$result .= '</span>';
		
		return $parser->insertStripItem( $result, $parser->mStripState );
	}
	
	
	/*
	 * Display of collapsable fieldset
	 */
	static function renderCollapsableFieldSet( &$parser) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser
		
		$legend = "";
		if(array_key_exists(0, $params)){
			$legend = $params[0];
		}
		
		$content = "";
		if(array_key_exists(1, $params)){
			$content = $params[1];
		}
		
		$collapsed = '';
		if(array_key_exists(2, $params)){
			if(strtolower($params[2]) == 'true'){
				$collapsed = '| true';
			}
		}
		
		$result = "{{#collapsableFieldSetStart:".$legend.$collapsed.'}}';
		$result .= $content;
		$result .= "{{#collapsableFieldSetEnd:}}";
		
		return array($result, 'noparse' => false);
	}
	
	
	/*
	 * Display start of collapsable fieldset
	 */
	static function renderCollapsableFieldSetStart( &$parser) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser
		
		global $wgScriptPath, $asfCollapsableFieldSetCounter;
		
		if(is_null($asfCollapsableFieldSetCounter)){
			$asfCollapsableFieldSetCounter = 1;
		} else {
			$asfCollapsableFieldSetCounter += 1;
		}
		
		$legend = "";
		if(array_key_exists(0, $params)){
			$legend .= trim($params[0]);
			$legend = " ".$parser->internalParse($legend);
		}
		
		$collapsedDisplay = "none";
		$unCollapsedDisplay = "";
		if(array_key_exists(1, $params)){
			if(strtolower($params[1]) == 'true'){
				$collapsedDisplay = "";
				$unCollapsedDisplay = "none";
			}
		}
		
		$result = "";
		$result .= '<fieldset id="fieldset_'.$asfCollapsableFieldSetCounter.'_hidden" style="display: '.$collapsedDisplay.'">';
		
		$result .= '<legend>';
		$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/plus-act.gif';
		$result .= "<img src=\"$imgSRC\" onclick=\"asf_show_category_section('fieldset_$asfCollapsableFieldSetCounter')\"></img>";
		$result .= $legend;
		$result .= '</legend>';
		
		$result .= '</fieldset>';
		
		$result .= '<fieldset id="fieldset_'.$asfCollapsableFieldSetCounter.'_visible" style="display: '.$unCollapsedDisplay.'">';
		
		$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/minus-act.gif';
		$result .= '<legend>';
		$result .= "<img src=\"$imgSRC\" onclick=\"asf_hide_category_section('fieldset_$asfCollapsableFieldSetCounter')\"></img>";
		$result .= $legend;
		$result .= '</legend>';
		
		//Add javascript and css
		global $smgJSLibs; 
		$smgJSLibs[] = 'jquery'; 
		$smgJSLibs[] = 'qtip';
		
		return $parser->insertStripItem( $result, $parser->mStripState );
	}
	
	/*
	 * Display end of collapsable fieldset
	 */
	static function renderCollapsableFieldSetEnd( &$parser) {
		return $parser->insertStripItem( '</fieldset>', $parser->mStripState );
	}
	
	/*
	 * The CreateSilentAnnotations parser function
	 */
	static function renderSilentAnnotationsTemplate( &$parser, $frame, $args) {
		$result = "";
		
		$store = smwfNewBaseStore();
		
		global $asfSilentAnnotations;
		if(!is_array($asfSilentAnnotations)) $asfSilentAnnotations = array();
		foreach($args as $arg){
			$arg = explode("=",  trim($frame->expand( $arg)), 2);
			
			if(count($arg) != 2) continue;
			
			$propertyName = $arg[0];
			$value = $arg[1];
			
			$title = Title::newFromText($propertyName, SMW_NS_PROPERTY);
			
			$asfSilentAnnotations[$propertyName] = $value;
			
			if(!$title->exists()){
				$result .= '[['.$propertyName.'::'.$value.'| ]]';
				continue;
			};
			
			$semanticData = $store->getSemanticData($title);
			$maxCardinality = 
				ASFFormGeneratorUtils::getPropertyValue($semanticData, ASF_PROP_HAS_MAX_CARDINALITY);
			$delimiter = 
				ASFFormGeneratorUtils::getPropertyValue($semanticData, 'Delimiter');
			
			if($maxCardinality != 1 || $delimiter){
				if(!$delimiter) $delimiter = ',';
				
				foreach(explode($delimiter, $value) as $val){
					if(strlen(trim($val)) == 0) continue;
					$result .= '[['.$propertyName.'::'.$val.'| ]]';
				}
			} else {
				$result .= '[['.$propertyName.'::'.$value.'| ]]';
			}
		}
		
		return $result;
	}
	
	
	/*
	 * The #shownow parser function
	 */
	static function renderShowNow( &$parser, $frame, $args) {
		$result = "";
		
		$showProperty = array();
		
		if(array_key_exists(0, $args)){
			$result .= "__asf_shownow:";
			$showProperty['name'] = str_replace(' ', '_' , trim($args[0]));
			$result .= $showProperty['name'];
		}
		
		if(array_key_exists(1, $args)){
			$showProperty['linked'] = trim($frame->expand($args[1]));
			if($showProperty['linked'] == 'false') $showProperty['linked'] = false;
		} else {
			$showProperty['linked'] = true;
		}
		
		if(array_key_exists(2, $args) && array_key_exists(3, $args)){
			$showProperty['variable'] = trim($frame->expand($args[2]));
			
			$showProperty['evaluate'] = $args[3];
			$showProperty['frame'] = $frame;
			
			//$showProperty['evaluate'] = self::getArgValue($showProperty['evaluate'], 'inner');	
		}
		
		if(count($showProperty) > 0){
			global $asfShowNowProperties;
			$asfShowNowProperties[]= $showProperty;
		}
		
		return $result;
	}
	
	/*
	 * Helper method for the #show parser function
	 */
	private static function getArgValue($value, $tag = 'value'){
		$value = explode('<'.$tag.'>', $value);
		$value = substr($value[0], 0, strrpos($value[1], '</'.$tag.'>'));
		$value = trim($value);
		return $value;
	}
	
	/*
	 * Finally process the #shownow parser function and replace it
	 * with data from the semantic store
	 */
	public static function finallyRenderShowNow( &$parser, &$text ) {
		global $asfShowNowProperties;
		
		global $asfFinallyRenderShowNowStarted;
		if($asfFinallyRenderShowNowStarted) return true;
		$asfFinallyRenderShowNowStarted = true;
		
		if(count($asfShowNowProperties) == 0) return true;		
		
		$semanticData = $parser->getOutput()->mSMWData;
		
		foreach($asfShowNowProperties as $key => $prop){
			if(strpos($text, "asf_shownow:".$prop['name']) === false) continue;
			
			$value = 	
				ASFFormGeneratorUtils::getLongPropertyValues($semanticData, $prop['name'], $prop['linked']);
			
			if($prop['linked'] || (array_key_exists('variable', $prop) && array_key_exists('evaluate', $prop))){
				$p = new Parser();
				$popts = new ParserOptions();
				
				if(array_key_exists('variable', $prop) && array_key_exists('evaluate', $prop)){
					$evaluate = $prop['frame']->expand( $prop['evaluate'], PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES);
					$value = str_replace($prop['variable'], $value, $evaluate);
					$value  = $p->preprocessToDom( $value, $prop['frame']->isTemplate() ? Parser::PTD_FOR_INCLUSION : 0 );
					$value = trim( $prop['frame']->expand( $value ) );
				}
				
				$value = $p->parse($value, $parser->getTitle(), $popts, false)->getText();
			}	
				
			$text = str_replace('__asf_shownow:'.$prop['name'], $value, $text);
			unset($asfShowNowProperties[$key]);
		}
		
		return true;
	}
	
	
	static function renderASFFormInput ( &$parser ) {
		$params = func_get_args();
		array_shift( $params ); // don't need the parser
		
		// set defaults
		$type = ''; 
		$categoryName = $pageName = '';
		$value = $categoryValue = $pageValue = '';
		$autocompletionQuery = '';
		$size = $categorySize = $pageSize = 25;
		$categoryInputIntro = '';
		$buttonLabel = '';
		$useDropDown=false;
		$queryString = '';
		$rootCategory = '';
		
		// assign params - support unlabelled params, for backwards compatibility
		$unresolvedParameters = array();
		foreach ( $params as $i => $param ) {
			$elements = explode( '=', $param, 2 );
			$paramName = null;
			$paramValue = trim( $param );
			if ( count( $elements ) > 1) {
				$paramName = trim( $elements[0] );
				$paramValue = trim( $elements[1] );
			}
			
			if ( $paramName == 'type' )
				$type = $paramValue;
			else if ( $paramName == 'category name' )
				$categoryName = $paramValue;
			else if ( $paramName == 'page name' )
				$pageName = $paramValue;
			else if ( $paramName == 'size' )
				$size = $paramValue;
			 else if ( $paramName == 'page input size' )
				$pageSize = $paramValue;
			else if ( $paramName == 'category input size' )
				$categorySize = $paramValue;
			else if ( $paramName == 'default value' )
				$value = $paramValue;
			else if ( $paramName == 'default page value' )
				$pageValue = $paramValue;
				else if ( $paramName == 'default category value' )
				$categoryValue = $paramValue;
			else if ( $paramName == 'button text' )
				$buttonLabel = $paramValue;
			else if ( $paramName == 'autocomplete on category' ) {
				$autocompletionSource = $paramValue;
				$autocompletionType = 'category';
			} else if ( $paramName == 'autocomplete on namespace' ) {
				$autocompletionSource = $paramValue;
				$autocompletionType = 'namespace';
			} else if ( $paramName == 'category input intro' ) {
				$categoryInputIntro = $paramValue;
			}  else if ( $paramName == 'autocomplete' ) {
				$autocompletionQuery = $paramValue;
			} else if ( $paramName == 'use dropdown' ) {
				if(strtolower($paramValue) == 'true'){
					$useDropDown = true;
				}
			} else if($paramName == 'query string'){
				$queryString = $paramValue;
			} else if($paramName == 'category ac root'){
				$rootCategory = $paramValue;
			} else { 
				$unresolvedParameters[$i] = $param;
			}
		}
		
		If(strlen($type) == 0 && array_key_exists(0, $unresolvedParameters))
				$type = $unresolvedParameters[0];
				
		$errors = false;
		if($type == 'category'){
			If(strlen($pageName) == 0 && array_key_exists(1, $unresolvedParameters))
				$pageName = $unresolvedParameters[1];
				
			//get page name from url if necessary and possible
			if(strlen($pageName) == 0){
				global $wgRequest;
				$target = $wgRequest->getVal('target');
				$pageName = ($target) ? $target : '';
			}
			
			if($categorySize == 25 && array_key_exists(2, $unresolvedParameters) && $size == 25)
				$categorySize = $unresolvedParameters[2];
			if($categorySize != 25)
				$size = $categorySize;
			$categorySize = $size;
			
			if($categoryValue == '' && array_key_exists(3, $unresolvedParameters))
				$categoryValue = $unresolvedParameters[3];
			if($categoryValue != '')
				$value = $categoryValue;
			$categoryValue = $value;
			
			If(strlen($buttonLabel) == 0 && array_key_exists(4, $unresolvedParameters))
				$buttonLabel = $unresolvedParameters[4];
			
			If(!$useDropDown && array_key_exists(5, $unresolvedParameters)){
				if(strtolower($unresolvedParameters[5]) == 'true'){
					$useDropDown = true;		
				}
			}
			
			if(strlen($queryString) == 0 && array_key_exists(6, $unresolvedParameters)){
				$queryString = $unresolvedParameters[6];
			}
			
			if(strlen($rootCategory) == 0 && array_key_exists(7, $unresolvedParameters)){
				$rootCategory = $unresolvedParameters[7];
			}
		
		} else if ($type == 'page'){
			If(strlen($categoryName) == 0 && array_key_exists(1, $unresolvedParameters))
				$categoryName = $unresolvedParameters[1];
			
			if($pageSize == 25 && array_key_exists(2, $unresolvedParameters) && $size == 25)
				$pageSize = $unresolvedParameters[2];
			if($pageSize != 25)
				$size = $pageSize;
			$pageSize = $size;
			
			if($pageValue == '' && array_key_exists(3, $unresolvedParameters))
				$pageValue = $unresolvedParameters[3];
			if($pageValue != '')
				$value = $pageValue;
			$pageValue = $value;
			
			If(strlen($autocompletionQuery) == 0 && array_key_exists(4, $unresolvedParameters))
				$autocompletionQuery = $unresolvedParameters[4];
			
			If(strlen($buttonLabel) == 0 && array_key_exists(5, $unresolvedParameters))
				$buttonLabel = $unresolvedParameters[5];
			
			if(strlen($queryString) == 0 && array_key_exists(6, $unresolvedParameters)){
				$queryString = $unresolvedParameters[6];
			}
		
		} else {
			if($pageSize == 25 && array_key_exists(1, $unresolvedParameters))
				$pageSize = $unresolvedParameters[1];
			
			if($categorySize == 25 && array_key_exists(2, $unresolvedParameters))
				$categorySize = $unresolvedParameters[2];	
			
			if($pageValue == '' && array_key_exists(3, $unresolvedParameters))
				$pageValue = $unresolvedParameters[3];
			
			if($categoryValue == '' && array_key_exists(4, $unresolvedParameters))
				$categoryValue = $unresolvedParameters[4];
			
			If(strlen($autocompletionQuery) == 0 && array_key_exists(5, $unresolvedParameters))
				$autocompletionQuery = $unresolvedParameters[5];
			
			if($categoryInputIntro == '' && array_key_exists(6, $unresolvedParameters))
				$categoryInputIntro = $unresolvedParameters[6];
			
			If(strlen($buttonLabel) == 0 && array_key_exists(7, $unresolvedParameters))
				$buttonLabel = $unresolvedParameters[7];
				
			If(!$useDropDown && array_key_exists(8, $unresolvedParameters)){
				if(strtolower($unresolvedParameters[8]) == 'true'){
					$useDropDown = true;		
				}
			}
			
			if(strlen($queryString) == 0 && array_key_exists(9, $unresolvedParameters)){
				$queryString = $unresolvedParameters[9];
			}
			
			if(strlen($rootCategory) == 0 && array_key_exists(10, $unresolvedParameters)){
				$rootCategory = $unresolvedParameters[10];
			}
		}
		
		
		//open form tag
		$formEdit = SpecialPage::getPage( 'FormEdit' );
		$formEditURL = $formEdit->getTitle()->getLocalURL();
		$str = '<form action="'.$formEditURL.'" method="get" style="display: inline">';
		
		//create page input field
		if($type != 'category'){
			if(strlen($autocompletionQuery) > 0){
				if(strtolower($autocompletionQuery) != 'all'){
					$autocompletionQuery = 'ask: '.$autocompletionQuery;
				}
				$autocompletionQuery = 'class="wickEnabled" constraints="'.$autocompletionQuery.'"';
			}
			$str .= '<input type="text" name="target" size="'.$pageSize.'" value="'.$pageValue.'" '.$autocompletionQuery.'/>';
		}
		
		//create category input field
		if($type != 'page'){
			if($type != 'category'){
				//both input fields are shown and category input intro must be added
				$str .= $categoryInputIntro;
			}
			
			if(!$useDropDown){
				if(strlen($rootCategory) == 0){
					$cACConstraint = "asf-ac:_";
				} else {
					$cACConstraint = "asf-ac:".$rootCategory;	
				}
				$str .= '<input type="text" name="categories" size="'.$categorySize.'" value="'.$categoryValue
					.'" class="wickEnabled" constraints="'.$cACConstraint.'"/>';
			} else {
				$str .= '<select size="1" name="categories" size="'.$categorySize.'">';
				if(!defined('SMW_AC_MAX_RESULTS')){
					define('SMW_AC_MAX_RESULTS', 500);
				}
				if(strlen($rootCategory) == 0){
					$categories = ASFCategoryAC::getCategories('');
				} else {
					$categories = ASFCategoryAC::getCategories('', $rootCategory);
				}
				foreach($categories as $category){
					$str .= '<option>'.$category->getText().'</option>';
				}
				
				$str .= '</select>'; 
			}
		}
		
		//add constant article creation data
		if($type == 'page'){
			$str .= '<input type="hidden" name="categories" value="' .$categoryName. '">';
		} else if ($type == 'category'){
			$str .= '<input type="hidden" name="target" value="' .$pageName. '">';
		}
		
		if ( $queryString != '' ) {
			$queryString = str_replace( '&amp;', '%26', $queryString);
			$queryComponents = explode( '&', $queryString);
			foreach ( $queryComponents as $queryComponent ) {
				$queryComponent = urldecode( $queryComponent );
				if(strpos($queryComponent, 'Property[') === 0){
					$queryComponent = 'CreateSilentAnnotations:[' . substr($queryComponent, strlen('Property['));
				}
				$varAndVal = explode( '=', $queryComponent );
				if ( count($varAndVal) == 2){
					$str .= '<input type="hidden" name="' . $varAndVal[0] . '" value="' . $varAndVal[1] . '" /> ';
				}
			}
		}
		
		//add submit button
		wfLoadExtensionMessages( 'SemanticForms' );
		$buttonLabel = ( $buttonLabel != '' ) ? $buttonLabel : wfMsg( 'sf_formstart_createoredit' );
		$str .= '   <input type="submit" value="'.$buttonLabel.'" /></p>';
		
		//end form tag
		$str .= '</form>';
			
		return $parser->insertStripItem( $str, $parser->mStripState );
	}
}















