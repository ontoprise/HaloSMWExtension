<?php

/*
 * Overwrites the formHtml method of SFFormPrinter
 * in order to add some extra features
 */
class ASFFormPrinter extends SFFormPrinter {
	

	/* 
	 * Checks if original form printer
	 * already has been initialized and saves its state
	 */
	function __construct() {
		global $sfgFormPrinter;
		if(StubObject::isRealObject($sfgFormPrinter)){
			$this->mSemanticTypeHooks = $sfgFormPrinter->mSemanticTypeHooks;
  			$this->mInputTypeHooks = $sfgFormPrinter->mInputTypeHooks;
  			
  			$this->standardInputsIncluded = $sfgFormPrinter->standardInputsIncluded;
  			$this->mPageTitle = $sfgFormPrinter->mPageTitle;	
		} else {
			parent::__construct();
		}
	}


	/*
	 * This method is called by the Semantic Forms extension in order to render a form
	 * 
	 * This method adds some Automatic Semantic Forms Features, i.e. it replaces the form definition
	 * retrieved from the form article with the one which was automatically created. Finally it calls its
	 * parent method and then does some postprocessing.
	 */
	function formHTML( $form_def, $form_submitted, $source_is_page, $form_id = null, $existing_page_content = null, $page_name = null, $page_name_formula = null, $is_query = false, $embedded = false ) {
		global $asfFormDefData;  
		$postProcess = false;
		if(isset($asfFormDefData) && array_key_exists('formdef', $asfFormDefData) && $asfFormDefData['formdef'] != false){
			$form_def = $asfFormDefData['formdef'];
			$postProcess = true;
		} 
		
		list ($form_text, $javascript_text, $data_text, $form_page_title, $generated_page_name) =
			parent::formHTML( $form_def, $form_submitted, $source_is_page, $form_id, $existing_page_content, $page_name, $page_name_formula, $is_query, $embedded);
			
		if($postProcess){
			//unescape fieldset and legend HTNL tag
			$form_text = str_replace("&lt;fieldset", "<fieldset", $form_text);
			$form_text = str_replace("&lt;/fieldset&gt;", "</fieldset>", $form_text);
			$form_text = str_replace("&gt;&lt;legend&gt;", "><legend>", $form_text);
			$form_text = str_replace("&lt;/legend&gt;", "</legend>", $form_text);
			
			//deal with the help icon
			global $wgScriptPath;
			$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/help.gif';
			$form_text = str_replace('&lt;img src="ASF_HELP_ICON', '<img src="'.$imgSRC, $form_text);
			$form_text = str_replace('ASF_HELP_ICON', $imgSRC, $form_text);
			$form_text = str_replace('&gt;<span class="asf', '><span class="asf', $form_text);
			$form_text = str_replace('</span>&lt;/img&gt;', '</span></img>', $form_text);
			
			//deal with thecollapse images
			$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/minus-act.gif';
			$form_text = str_replace('&lt;img src="ASF_MINUS_ICON', '<img src="'.$imgSRC, $form_text);
			$form_text = str_replace('ASF_MINUS_ICON', $imgSRC, $form_text);
			
			$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/plus-act.gif';
			$form_text = str_replace('&lt;img src="ASF_PLUS_ICON', '<img src="'.$imgSRC, $form_text);
			$form_text = str_replace('ASF_PLUS_ICON', $imgSRC, $form_text);
			
			$form_text = str_replace('&gt;&lt;/img&gt;', '></img>', $form_text);
			
			//deal with autocompletion diff - necessary because otherwise no other
			//HTML elements are possible in the same row as the input field
			$form_text = str_replace('<div class="page_name_auto_complete"', 
				'<div style="display: inline" class="page_name_auto_complete"', $form_text);

			//deal with additional category annotations
			if(array_key_exists('additional catehory annotations', $asfFormDefData)){
				$additionalCategoryAnnotations = "\n";
				foreach($asfFormDefData['additional catehory annotations'] as $category){
					$additionalCategoryAnnotations .= "[[".$category."]] ";
				}
				
				//render for fck if necessary
				global $wgFCKEditorDir;
    			if ( $wgFCKEditorDir ) {
					$additionalCategoryAnnotations = SFFormUtils::prepareTextForFCK($additionalCategoryAnnotations);
    			}
				
				//todo:does this work with wysiwyg
				$startFreeText = strpos($form_text, 'id="free_text"');
				$endFreeText = strpos($form_text, '</textarea>', $startFreeText);
				$form_text = substr($form_text, 0, $endFreeText).$additionalCategoryAnnotations.substr($form_text, $endFreeText);
			}
			
			//deal with standard text input with
			$startPos = strpos($form_text, 'id="free_text"');
			$startPos = strrpos(substr($form_text, 0, $startPos), '<textare');
			$styleStartPos = strpos($form_text, 'style=', $startPos);
			if($styleStartPos > 0){
				$styleStartPos = strpos($form_text, '"', $styleStartPos);
				$form_text = substr($form_text, 0, $endPos).'width: 100%;'.substr($form_text, $endPos);
			} else {
				$endPos = strpos($form_text, '>', $startPos);
				$form_text = substr($form_text, 0, $endPos).'style="width: 100%"'.substr($form_text, $endPos);
			}
			
			
			//Deal with display templates
			//todo: Use something better than a global variable here
			global $asfAllDirectCategoryAnnotations;
			
			foreach($asfAllDirectCategoryAnnotations as $templateName){
				if($templateName){
					$startPos = strpos($form_text, 'name="'.$templateName.'[categories]"'); 
					$startPos = strpos($form_text, 'value="', $startPos);
					$endPos = strpos($form_text, '"', $startPos + strlen('value="'));
					$form_text = substr($form_text, 0, $startPos)
						.'value="'.implode(',',array_keys($asfAllDirectCategoryAnnotations)).'"'
						.substr($form_text, $endPos + 1);
				}
			}
		}
		
		//echo('<pre>'.print_r($form_text, true).'</pre>');
			
		return array($form_text, $javascript_text, $data_text, $form_page_title, $generated_page_name);
	}

}
