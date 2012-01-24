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
			
		//overwrite original formlink parser function
		$parser->setFunctionHook( 'formlink', 
			array( 'ASFParserFunctions', 'renderFormLink' ) );

			
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
		
		global $wgOut;
		$wgOut->addModules( 'ext.automaticsemanticforms.main' );
		
		$result = str_replace(array('&lt;', '&gt;'), array('<', '>'), $result);
		
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
		$result .= '<fieldset id="fieldset_'.$asfCollapsableFieldSetCounter.'">';
		
		$result .= '<legend class="asf_legend" onKeyDown="javascript:if (event.keyCode == 32){ '
							." asf_hit_category_section('fieldset_$asfCollapsableFieldSetCounter');".'};">';
		$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/plus-act.gif';
		$result .= '<span style="display: '.$collapsedDisplay.'" class="asf_collapsed_legend">';
		$result .= "<img src=\"$imgSRC\" onclick=\"asf_show_category_section('fieldset_$asfCollapsableFieldSetCounter')\"></img>";
		$result .= $legend;
		$result .= '</img>';
		$result .= "</span>";
		
		$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/minus-act.gif';
		$result .= '<span style="display: '.$unCollapsedDisplay.'" class="asf_visible_legend">';
		$result .= "<img src=\"$imgSRC\" onclick=\"asf_hide_category_section('fieldset_$asfCollapsableFieldSetCounter')\"></img>";
		$result .= $legend;
		$result .= '</img>';
		$result .= "</span>";
		$result .= '</legend>';
		$result .= '<span class="asf_fieldset_content" style="display: '.$unCollapsedDisplay.'">';
		
		return $parser->insertStripItem( $result, $parser->mStripState );
	}
	
	/*
	 * Display end of collapsable fieldset
	 */
	static function renderCollapsableFieldSetEnd( &$parser) {
		return $parser->insertStripItem( '</span></fieldset>', $parser->mStripState );
	}
	
	/*
	 * The CreateSilentAnnotations parser function
	 */
	static function renderSilentAnnotationsTemplate( &$parser, $frame, $args) {
		$result = "";
		
		$store = smwfGetStore();
		
		global $asfSilentAnnotations;
		if(!is_array($asfSilentAnnotations)) $asfSilentAnnotations = array();
		foreach($args as $arg){
			$arg = explode("=",  trim($frame->expand( $arg)), 2);
			
			if(count($arg) != 2) continue;
			
			$propertyName = $arg[0];
			$value = $arg[1];
			
			$title = Title::newFromText($propertyName, SMW_NS_PROPERTY);
			
			$asfSilentAnnotations[$propertyName] = $value;
			
			if(!($title instanceof Title) || !$title->exists()){
				$result .= '[['.$propertyName.'::'.$value.'| ]]';
				continue;
			};
			
			$semanticData = ASFFormGeneratorUtils::getSemanticData($title);
			$maxCardinality = 
				ASFFormGeneratorUtils::getPropertyValue($semanticData, ASF_PROP_HAS_MAX_CARDINALITY);
			$delimiter = 
				ASFFormGeneratorUtils::getPropertyValue($semanticData, 'Delimiter');
			$isUploadable = 
				ASFFormGeneratorUtils::getPropertyValue($semanticData, ASF_PROP_IS_UPLOADABLE);
			
			global $wgContLang;
			if($maxCardinality != 1 || $delimiter){
				if(!$delimiter) $delimiter = ',';
				
				foreach(explode($delimiter, $value) as $val){
					
					if($isUploadable && strpos($val, ':') === false){
						$val = $wgContLang->getNSText(NS_FILE).':'.$val;
					}
					
					if(strlen(trim($val)) == 0) continue;
					$result .= '[['.$propertyName.'::'.$val.'| ]]';
				}
			} else {
				if($isUploadable && strpos($value, ':') == false){
					$value = $wgContLang->getNSText(NS_FILE).':'.$value;
				}
				
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
		
		if(array_key_exists(0, $args)){
			$result = '{{#show:{{PAGENAME}}|?'.trim($args[0]).'}}';
		}
		
		return array($result, 'noparse' => false);
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
		$popupClass = '';
		
		$hiddenInputFields = '';
		
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
			} else if($paramName == null && $paramValue == 'popup'){
				SFParserFunctions::loadScriptsForPopupForm( $parser );
				$popupClass = 'popupforminput';
				$hiddenInputFields .= '<input type="hidden" name="redirect" value="necessary"/>';
			}else { 
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
		$str = '<form action="'.$formEditURL.'" method="get" style="display: inline" class="'.$popupClass.'">';
		
		//create page input field
		if($type != 'category'){
			if(strlen($autocompletionQuery) > 0){
				if(strtolower($autocompletionQuery) != 'all'){
					$autocompletionQuery = 'ask: '.$autocompletionQuery;
				}
				$autocompletionQuery = ' class="wickEnabled" constraints="'.$autocompletionQuery.'"';
			}
			$str .= '<input type="text" name="target" size="'.$pageSize.'" value="'.$pageValue.'" '.$autocompletionQuery.'></input>';
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
				$str .= '<select size="1" name="categories"">';
				if(strlen($rootCategory) == 0){
					$categories = ASFCategoryAC::getCategories('', 500);
				} else {
					$categories = ASFCategoryAC::getCategories('', 500, $rootCategory);
				}
				
				foreach($categories as $category){
					$selected = '';
					if($category->getText() == $categoryValue){
						$selected = ' selected="selected" ';
					}
					$str .= '<option'.$selected.'>'.$category->getText().'</option>';
				}
				
				$str .= '</select>'; 
			}
		}
		
		//add constant article creation data
		if($type == 'page'){
			$str .= '<input type="hidden" name="categories" value="' .$categoryName. '"/>';
		} else if ($type == 'category'){
			$str .= '<input type="hidden" name="target" value="' .$pageName. '"/>';
		}
		
		if ( $queryString != '' ) {
			$queryString = str_replace( '&amp;', '%26', $queryString);
			$queryComponents = explode( '&', $queryString);
			foreach ( $queryComponents as $queryComponent ) {
				$queryComponent = urldecode($queryComponent);
				
				if(strpos($queryComponent, 'Property[') === 0){
					$queryComponent = 'CreateSilentAnnotations:[' . substr($queryComponent, strlen('Property['));
				}
				
				$varAndVal = explode( '=', $queryComponent, 2);
				if ( count($varAndVal) == 2){
					//$str .= '<input type="hidden" name="' . $varAndVal[0] . '" value="' . $varAndVal[1] . '" /> ';
					
					$str .= Xml::element( 'input',
						array(
							'type' => 'hidden',
							'name' => $varAndVal[0],
							'value' => $varAndVal[1],
						)
					) . "";
			
				}
			}
		}
		
		//add submit button
		wfLoadExtensionMessages( 'SemanticForms' );
		$buttonLabel = ( $buttonLabel != '' ) ? $buttonLabel : wfMsg( 'sf_formstart_createoredit' );
		$str .= '   <input type="submit" value="'.$buttonLabel.'" />';
		
		$str .= $hiddenInputFields;
		
		//end form tag
		$str .= '</form><p>';

		return $parser->insertStripItem( $str, $parser->mStripState );
	}
	
	public static function renderFormLink ( &$parser ) {

		$params = func_get_args();
		
		//echo('<pre>'.print_r($params, true).'</pre>');
		
		$params[0] = &$parser;
		$params[1] = '='.$params[1];
		
		return call_user_func_array('SFParserFunctions::renderFormLink', $params);
	}
}















