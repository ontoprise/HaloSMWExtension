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

		//initialize datapicker input types only if data import extension is installed
		if(defined('SMW_DI_VERSION')){
			global $dapi_instantiations;
			foreach($dapi_instantiations as $dpId => $dC){
				$this->setInputTypeHook( $dpId, array( 'ASFDataPickerInputType', 'getHTML' ), array());
			}
		}
		
		//echo('<pre>'.print_r($this->mInputTypeHooks, true).'</pre>');
	}


	/*
	 * This method is called by the Semantic Forms extension in order to render a form
	 *
	 * This method adds some Automatic Semantic Forms Features, i.e. it replaces the form definition
	 * retrieved from the form article with the one which was automatically created. Finally it calls its
	 * parent method and then does some postprocessing.
	 */
	function formHTML( $form_def, $form_submitted, $source_is_page, $form_id = null, $existing_page_content = null, $page_name = null, $page_name_formula = null, $is_query = false, $embedded = false ) {

		$postProcess = false;
		if($formDefinition = ASFFormGenerator::getInstance()->getFormDefinition()){

			global $wgOut;
			$wgOut->addModules( 'ext.automaticsemanticforms.main' );
			
			if(!$form_submitted){
				$existing_page_content = 
					ASFWikiTextManipulator::getInstance()->getWikiTextForSF($page_name, $existing_page_content);
			}
			
			$form_def = $formDefinition->getFormDefinition(); 
			
			$page_name_formula = $formDefinition->getPageNameTemplate();
			
			$postProcess = true;
		}

		list ($form_text, $javascript_text, $data_text, $form_page_title, $generated_page_name) =
			parent::formHTML( $form_def, $form_submitted, $source_is_page, $form_id, $existing_page_content, $page_name, $page_name_formula, $is_query, $embedded);

		if($form_submitted){
			$data_text = 
				ASFWikiTextManipulator::getInstance()->getWikiTextForSaving($generated_page_name, $data_text);
		}
		
		if($postProcess&& !$form_submitted){
			//remove this if bug has been fixed in sf
			global $asfDisplayPropertiesAndCategoriesAsLinks;
			if($asfDisplayPropertiesAndCategoriesAsLinks){
				$form_text = ASFFormGeneratorUtils::retranslateParseSaveLink($form_text);
			}

			//deal with autocompletion div - necessary because otherwise no other
			//HTML elements are possible in the same row as the input field
			$form_text = str_replace('<div class="page_name_auto_complete"',
				'<div style="display: inline" class="page_name_auto_complete"', $form_text);

			//deal with additional category annotations
			if($additionalCategoryAnnotations = $formDefinition->getAdditionalCategoryAnnotations()){
				$additionalCategoryAnnotationsString = '';
				foreach($additionalCategoryAnnotations  as $category){
					$additionalCategoryAnnotationsString .= "[[".$category."]] ";
				}

				$additionalContent = "";

				//deal with preloading
				$additionalContent .= $formDefinition->getPreloadContent($page_name);

				$additionalContent .= $additionalCategoryAnnotationsString;
				
				//render for fck if necessary
				global $wgFCKEditorDir;
				if ( $wgFCKEditorDir ) {
					$showFCKEditor = SFFormUtils::getShowFCKEditor();
					if ( $showFCKEditor & RTE_VISIBLE ) {
						$additionalContent = SFFormUtils::prepareTextForFCK($additionalContent);
					}
				}
				 
				$startFreeText = strpos($form_text, 'id="free_text"');
				$endFreeText = strpos($form_text, '</textarea>', $startFreeText);
				$form_text = substr($form_text, 0, $endFreeText).$additionalContent.substr($form_text, $endFreeText);
			}
				
			//deal with standard text input with
			$startPos = strpos($form_text, 'id="free_text"');
			$startPos = strrpos(substr($form_text, 0, $startPos), '<textare');
			$styleStartPos = strpos($form_text, 'style=', $startPos);
			if($styleStartPos > 0){
				$styleStartPos = strpos($form_text, '"', $styleStartPos);
				$form_text = substr($form_text, 0, $styleStartPos).'width: 100%;'.substr($form_text, $styleStartPos);
			} else {
				$endPos = strpos($form_text, '>', $startPos);
				$form_text = substr($form_text, 0, $endPos).'style="width: 100%;"'.substr($form_text, $endPos);
			}
			
			$form_text = str_replace(
				'&lt;span class="asf-hide-freetext"/&gt;',
				'<span class="asf-hide-freetext" style="display: none"></span>',
				$form_text);
				
	}

	//echo('<pre>'.print_r($form_text, true).'</pre>');
		
	return array($form_text, $javascript_text, $data_text, $form_page_title, $generated_page_name);
}


}
