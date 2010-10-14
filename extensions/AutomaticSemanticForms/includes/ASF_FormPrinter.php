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
			$form_text = str_replace("&lt;fieldset&gt;", "<fieldset>", $form_text);
			$form_text = str_replace("&lt;/fieldset&gt;", "</fieldset>", $form_text);
			$form_text = str_replace("&lt;legend&gt;", "<legend>", $form_text);
			$form_text = str_replace("&lt;/legend&gt;", "</legend>", $form_text);
			
			//deal with the help icon
			global $wgScriptPath;
			$imgSRC = $wgScriptPath . '/extensions/AutomaticSemanticForms/skins/help.gif';
			$form_text = str_replace('&lt;img src="ASF_HELP_ICON', '<img src="'.$imgSRC, $form_text);
			$form_text = str_replace('ASF_HELP_ICON', $imgSRC, $form_text);
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
				
				//todo:does this work with wysiwyg
				$startFreeText = strpos($form_text, 'id="free_text"');
				$endFreeText = strpos($form_text, '</textarea>', $startFreeText);
				$form_text = substr($form_text, 0, $endFreeText).$additionalCategoryAnnotations.substr($form_text, $endFreeText);
			}
		}
		
		//echo('<pre>'.print_r($form_text, true).'</pre>');
			
		return array($form_text, $javascript_text, $data_text, $form_page_title, $generated_page_name);
	}

}
