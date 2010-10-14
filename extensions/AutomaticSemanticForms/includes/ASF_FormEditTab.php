<?php

/*
 * This class is a copy of SFFormEditTab which is shipped
 * together with the Semantic Forms extension.
 * 
 * It contains some modifications that are required for the
 * Automatic Semantic Forms extension.
 */
class ASFFormEditTab {
	
	
	/**
	 * Adds an "action" (i.e., a tab) to edit the current article with
	 * a form
	 * 
	 * MODIFICATIONS: Adds formedit links if no default form but
	 * Automatic Semantic Forms exist
	 */
	static function displayTab( $obj, &$content_actions ) {
		if ( isset( $obj->mTitle ) &&
				( $obj->mTitle->getNamespace() != NS_SPECIAL ) ) {
			
			$form_names = SFLinkUtils::getFormsForArticle( $obj );
			
			//Check if no default form is set and if an automatic semantic form can be created
			if ( count( $form_names ) == 0 
					&& ASFFormGeneratorUtils::canFormForArticleBeCreated($obj->getTitle())) {

				//Set article to a dummy with a dummy default form in order to trick SF
				global $asfDummyFormName;
				ASFFormGeneratorUtils::createFormDummyIfNecessary();
				
				//Set global variable for Ontoskin3 so that the edit button can be set correctly.
				global $asfAutomaticFormExists;
				$asfAutomaticFormExists = true;
				
				$title = Title::newFromText($asfDummyFormName, SF_NS_FORM);
				$article = Article::newFromID($title->getArticleID());
				
				$result = SFFormEditTab::displayTab($article, $content_actions);

				//adjus content action links
				$originalTitleFullText = $obj->getTitle()->getFullText();
				foreach($content_actions as $key=>$action){
					if(strpos($action['href'], $title->getFullText()) !== false){
						$action['href'] = str_replace($title->getFullText(), $originalTitleFullText, $action['href']);
						$content_actions[$key] = $action;
					}
				}
				
				return $result;
			}
		}
		return true; // always return true, in order not to stop MW's hook processing!
	}
	
	/**
	 * Function currently called only for the 'Vector' skin, added in
	 * MW 1.16 - will possibly be called for additional skins later
	 */
	static function displayTab2( $obj, &$links ) {
		// the old '$content_actions' array is thankfully just a
		// sub-array of this one
		$views_links = $links['views'];
		self::displayTab( $obj, $views_links );
		$links['views'] = $views_links;
		return true;
	}
	
	/*
	 * Method is called on action = formedit
	 */
	static function displayForm( $action, $article ) {
		global $sfgIP, $sfgUseFormEditPage;

		if ( $action != 'formedit' ) {
			return true;
		}
			
		//Create form definition
		$formDefinition = ASFFormGenerator::getInstance()->generateFromTitle($article->getTitle());
		if($formDefinition){
			global $asfDummyFormName;
			$errors = ASFFormGeneratorUtils::createFormDummyIfNecessary();
			$form_name = $asfDummyFormName;
			
			global $asfFormDefData;
			$asfFormDefData = array();
			$asfFormDefData['formdef'] = $formDefinition;

			$target_title = $article->getTitle();
			$target_name = SFLinkUtils::titleString( $target_title );
			SFFormEdit::printForm( $form_name, $target_name );
		
			return false;
		}
		
		return true;
	}
}