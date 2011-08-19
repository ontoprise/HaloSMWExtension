<?php
/*  Copyright 2007, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 *
 * @defgroup SMWHaloSemanticToolbar SMWHalo SemanticToolbar
 * @ingroup SMWHalo
 * Created on 22.05.2007
 *
 * This file contains methods that modify the ontology.
 *
 * @author Thomas Schweitzer
 */

global $wgAjaxExportList;

$wgAjaxExportList[] = 'smwf_om_CreateArticle';
$wgAjaxExportList[] = 'smwf_om_EditArticle';
$wgAjaxExportList[] = 'smwf_om_TouchArticle';
$wgAjaxExportList[] = 'smwf_om_ExistsArticle';
$wgAjaxExportList[] = 'smwf_om_ExistsArticleMultiple';
$wgAjaxExportList[] = 'smwf_om_ExistsArticleIgnoreRedirect';
$wgAjaxExportList[] = 'smwf_om_MultipleRelationInfo';
$wgAjaxExportList[] = 'smwf_om_RelationSchemaData';
$wgAjaxExportList[] = 'smwf_om_GetWikiText';
$wgAjaxExportList[] = 'smwf_om_DeleteArticle';
$wgAjaxExportList[] = 'smwf_om_RenameArticle';
$wgAjaxExportList[] = 'smwf_om_EditProperty';
$wgAjaxExportList[] = 'smwf_om_MoveCategory';
$wgAjaxExportList[] = 'smwf_om_MoveProperty';
$wgAjaxExportList[] = 'smwf_om_invalidateAllPages';
$wgAjaxExportList[] = 'smwf_om_userCan';
$wgAjaxExportList[] = 'smwf_om_userCanMultiple';
$wgAjaxExportList[] = 'smwf_om_GetDerivedFacts';
$wgAjaxExportList[] = 'smwf_om_getDomainProperties';

/**
 * Creates a new article or appends some text if it already
 * exists. This function is invoked by an ajax call.
 *
 * @param string $title
 * 			The name of the article
 * @param string $user
 * 			The name of the user
 * @param string $content
 * 			The initial content of the article. It is only set if the article
 * 			is newly created.
 * @param string optionalText
 * 			This text is appended to the article, if it is not already part
 * 			of it. The text may contain variables of the language files
 * 			that are replaced by their representation.
 * @param string creationComment
 * 			This text describes why the article has been created.
 *
 * @return string Comma separated list:
 * 			bool success
 * 	 			<true> if the operation was successful.
 * 			bool created
 * 				<true> if the article was created,
 * 				<false> if it was only modified
 * 				<denied> if the permission was denied
 * 			string title
 * 				Title of the (new) article
 *
 */
function smwf_om_CreateArticle($title, $user, $content, $optionalText, $creationComment) {

	global $smwgContLang, $smwgHaloContLang;

	$success = false;
	$created = true;

	$title = strip_tags($title);
	if ($title == '') return "false";

	if (smwf_om_userCan($title, 'create') === "false") {
		return "false,denied,$title";
	}

	$title = Title::newFromText($title);

	// add predefined content if configured
	global $smwhgAutoTemplates, $smwhgAutoTemplatesParameters;
	$autoContent = "";
	if (isset($smwhgAutoTemplates)) {
		if (array_key_exists($title->getNamespace(), $smwhgAutoTemplates)) {
			require_once('SMW_Predefinitions.php');
			$mappings = array_key_exists($title->getNamespace(), $smwhgAutoTemplatesParameters) ? $smwhgAutoTemplatesParameters[$title->getNamespace()] : array();
			$metadataText = SMWPredefinitions::getPredefinitions($title, $smwhgAutoTemplates[$title->getNamespace()], $mappings);
			$autoContent .= $metadataText;
		}
	}

	// add pre-defined text
	global $smwhgAutoTemplateText;
	if (isset($smwhgAutoTemplateText)) {
		if (array_key_exists($title->getNamespace(), $smwhgAutoTemplateText)) {
			require_once('SMW_Predefinitions.php');
			$addedText = $smwhgAutoTemplateText[$title->getNamespace()];
			$addedText .= "\n";
			$autoContent .= $addedText;
		}
	}

	$content = $autoContent.$content;

	$article = new Article($title);

	if ($article->exists()) {
		// The article exists => get its current content. The passed content
		// will be ignored.
		$text = $article->getContent();

		if ($text === false) {
			return "false,false,".$title.getText();
		}
		$content = $text;
		$created = false;
	}

	if (!empty($optionalText)) {
		$supportedConstants = array("_SUBP",
                                    "SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT",
                                    "SMW_SSP_HAS_MAX_CARD",
                                    "SMW_SSP_HAS_MIN_CARD",
                                    "_TYPE",
									"_rec",
									"_LIST");

		// Some optional text is given
		$sp = $smwgContLang->getPropertyLabels()
		+ $smwgContLang->getDatatypeLabels();
		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();

		$num = count($supportedConstants);
		for ($i = 0; $i < $num; $i++) {
			$constant = $supportedConstants[$i];

			$pos = strpos($optionalText, $constant);
			if ($pos) {
				$langString = "Unsupported constant";
				if (strpos($constant, "SMW_SSP_") !== false) {
					$langString = $ssp[constant($constant)];
				} else {
					$langString = $sp[$constant];
				}
				$optionalText = str_replace($constant,
				$langString,
				$optionalText);
			}
		}

		// does the optional text already exist?
		if ($article->exists()) {
			$pos = strpos($content, $optionalText);
			if ($pos === false) {
				// optional text not found => append it
				$content .= $optionalText;
			}
		} else {
			// The article will be created with content and optional text
			$content .= $optionalText;
		}
	}

	// Set the article's content
	$success = $article->doEdit($content, $creationComment);

	return ($success ? "true," : "false,").
	($created ? "true," : "false,").
	$title->getPrefixedText();
}

/**
 * Replaces the complete content of an article in the wiki. If the article
 * does not exist, it will be created.
 *
 * @param string $title
 * 			Title of the article.
 * @param string $user
 * 			The name of the user
 * @param string $content
 * 			New content of the article.
 * @param string $editComment
 * 			This text describes why the article has been edited.
 * @param string $action
 * 			The way how the article is edited. This is important for checking the
 * 			access rights. Possible values are: edit (default), annotate,
 * 			formedit, wysiwyg
 *
 *
 * @return string Comma separated list:
 * 			bool success
 * 	 			<true> if the operation was successful.
 * 			bool created
 * 				<true> if the article was edited,
 * 				<false> if it was only modified
 * 				<denied> if the permission was denied
 * 			string title
 * 				Title of the (new) article
 *
 */
function smwf_om_EditArticle($title, $user, $content, $editComment, $action = 'edit') {

	global $smwgContLang, $smwgHaloContLang;

	$success = false;
	$created = true;

	$title = strip_tags($title);
	if ($title == '') return "false";

	if (smwf_om_userCan($title, $action) === "false") {
		return "false,denied,$title";
	}

	$title = Title::newFromText($title);

	$article = new Article($title);

	if ($article->exists()) {
		// The article exists
		$created = false;
	}

	// Set the article's content
	$success = $article->doEdit($content, $editComment);

	return ($success ? "true," : "false,").
	($created ? "true," : "false,").
	$title->getNsText().":".$title->getText();
}

/**
 * Touches the article with the given title, i.e. the article's HTML-cache is
 * invalidated.
 *
 * @param string $title
 * 		Name of the article
 * @return string
 * 	'true', if the article exists
 *  'false', otherwise
 */
function smwf_om_TouchArticle($title) {
	if (smwf_om_userCan($title, 'edit') === "false") {
		return "false,denied,$title";
	}

	$title = Title::newFromText($title);

	$article = new Article($title);

	if ($article->exists()) {
		// The article exists => invalidate its cache

		// The resolution of the article's timestamp is only one second
		// => wait a little bit to get a 'new' timestamp
		sleep(1);
		$title->invalidateCache();
		return "true";
	}
	return "false";

}

/**
 * Checks if an article exists. This function is invoked by an ajax call.
 *
 * @param string $title
 * 			The name of the article
 * @return string "true" => the article exists
 *                "false" => the article does not exist
 * 				  "false,denied,$title" => if access to title is denied
 *
 */
function smwf_om_ExistsArticle($title) {
	if (smwf_om_userCan($title, 'read') === "false") {
		return "false,denied,$title";
	}

	global $wgContLang;

	if (strpos($title,"Attribute:") == 0) {
		$title = str_replace("Attribute:",
		$wgContLang->getNsText(SMW_NS_PROPERTY).":",
		$title);
	}
	$titleObj = Title::newFromText($title);
	$article = new Article($titleObj);

	if ($article->exists()) {
		return "true";
	}

	if (defined('SMW_NS_RELATION') && $titleObj->getNamespace() == SMW_NS_RELATION) {
		// Attributes and relations are deprecated. They are replaced by Properties.
		$titleObj = Title::newFromText($wgContLang->getNsText(SMW_NS_PROPERTY).":".$titleObj->getText());
		$article = new Article($titleObj);

		if ($article->exists()) {
			return "true";
		}
	}

	// Is the article a special property?
	$title = str_replace($wgContLang->getNsText(SMW_NS_PROPERTY).":", "", $title);
	$title = strtolower ( substr ( $title , 0 , 1 ) ) . substr ( $title , 1 ) ;

	global $smwgContLang, $smwgHaloContLang;
	$specialProps = $smwgContLang->getPropertyLabels();
	foreach ($specialProps as $prop) {
		$prop = strtolower ( substr ( $prop , 0 , 1 ) ) . substr ( $prop , 1 ) ;
		if ($title == $prop) {
			return "true";
		}
	}
	// Is the article a special schema property?
	$specialProps = $smwgHaloContLang->getSpecialSchemaPropertyArray();
	foreach ($specialProps as $prop) {
		$prop = strtolower ( substr ( $prop , 0 , 1 ) ) . substr ( $prop , 1 ) ;
		if ($title == $prop) {
			return "true";
		}
	}

	return "false";
}

/**
 * Checks if the titles given in $titleNames exist.
 *
 * @param string $titleNames
 * 		Comma separated list of article names
 *
 * @return array(array(string, string))
 * 		This json encoded array contains an array for each title of the list of
 * 		the form
 * 		array(title name, "true"or "false" or "false,denied,$title")
 */

function smwf_om_ExistsArticleMultiple($titleNames) {
	$titleNames = explode(',', $titleNames);
	$results = array();
	foreach ($titleNames as $t) {
		$title = Title::newFromText(trim($t));
		$results[] = array($t, smwf_om_ExistsArticle($t));
	}
	return json_encode($results);
}

/**
 * Checks if an article exists. This function is invoked by an ajax call.
 *
 * @param string $title
 * 			The name of the article
 * @return string "true" => the article exists
 *                "false" => the article does not exist
 *
 */
function smwf_om_ExistsArticleIgnoreRedirect($title) {
	global $wgContLang;

	if (smwf_om_userCan($title, 'read') === "false") {
		return "false,denied,$title";
	}


	$titleObj = Title::newFromText($title);
	$article = new Article($titleObj);

	if ($article->exists() && !smwf_om_IsRedirect($titleObj)) {
		return "true";
	}



	// Is the article a special property?
	$title = str_replace($wgContLang->getNsText(SMW_NS_PROPERTY).":", "", $title);
	$title = strtolower ( substr ( $title , 0 , 1 ) ) . substr ( $title , 1 ) ;

	global $smwgContLang, $smwgHaloContLang;
	$specialProps = $smwgContLang->getPropertyLabels();
	foreach ($specialProps as $prop) {
		$prop = strtolower ( substr ( $prop , 0 , 1 ) ) . substr ( $prop , 1 ) ;
		if ($title == $prop) {
			return "true";
		}
	}
	// Is the article a special schema property?
	$specialProps = $smwgHaloContLang->getSpecialSchemaPropertyArray();
	foreach ($specialProps as $prop) {
		$prop = strtolower ( substr ( $prop , 0 , 1 ) ) . substr ( $prop , 1 ) ;
		if ($title == $prop) {
			return "true";
		}
	}

	return "false";
}

function smwf_om_IsRedirect(Title $title) {
	$db =& wfGetDB( DB_SLAVE );
	$pagetable = $db->tableName('page');
	return $db->selectRow($pagetable, 'page_is_redirect', array('page_title' => $title->getDBkey(), 'page_namespace' => $title->getNamespace(), 'page_is_redirect' => 1)) !== false;
}

/**
 * This function retrieves information about several relations. It checks if
 * - there is an article that defines the relation
 * - access control grants the requested access
 * - values of type page are valid existing pages
 *
 * @param string $relations
 * 		JSON representation of all relations to check:
 * 		array(Object(name => string, values => array(string))
 * @return string
 * 		JSON representation of the objects that were given as parameter with
 * 		additional values:
 * 		- relationExists: "true", "false" or "false,denied,<relation name>"
 * 		- accessGranted: "true" or "false"
 * 		- valuePageInfo (array): For each value of the property, if it is the
 *                               name of an article and if it exists:
 *								 "exists"  => Value is an existing page
 *								 "redlink" => Value is a non-existing page
 *								 "no page" => Value is not a page
 *								 "missing type info" => The type info for the
 *                                                      value is  missing
 *		- rangeCategories (array): The categories of each page value of the property
 *      - relationSchema (array): The type IDs for each type of the property values
 *
 */
function smwf_om_MultipleRelationInfo($relations) {
	if (empty($relations)) {
		return '[]';
	}

	$results = array();
	// The relations have to be decoded twice as sajax_do_call does not correctly
	// encode JavaScript-objects, but it does encode the string with a JSON
	// representation of an object
	$relations = json_decode($relations);
	$relations = json_decode($relations);
	foreach ($relations as $relDescr) {
		// Check if an article for the relation exists
		$relDescr->relationExists = smwf_om_ExistsArticle($relDescr->name);

		// Check access request for the relation
		$relDescr->accessGranted = smwf_om_userCan($relDescr->name, $relDescr->accessRequest);

		// Check if values of the relation are valid pages
		list($relSchema, $categories) = $relDescr->relationExists === 'true'
		? smwf_om_getRelationSchema($relDescr->name)
		: array(array('_wpg'), array(null));
		// Store for each value of the property if it is the name of an article
		// and if it exists. This is encoded as follows:
		// "exists"  => Value is an existing page
		// "redlink" => Value is a non-existing page
		// "no page"   => Value is not a page
		// "missing type info" => The type info for the value is  missing
		$valuePageInfo = array();
		for ($i = 0, $n = count($relDescr->values); $i < $n; ++$i) {
			if ($i < count($relSchema)) {
				// Type info for value exists
				if ($relSchema[$i] == '_wpg') {
					// Value should be a page
					$exists = smwf_om_ExistsArticle($relDescr->values[$i]);
					$valuePageInfo[] = $exists == 'true' ? "exists" : "redlink";
				} else {
					// value is of another type
					$valuePageInfo[] = "no page";
				}
			} else {
				$valuePageInfo[] = "missing type info";
			}
		}
		$relDescr->valuePageInfo = $valuePageInfo;
		$relDescr->rangeCategories = $categories;
		$relDescr->relationSchema = $relSchema;
		$results[] = $relDescr;
	}

	return json_encode($results);
}

/**
 * Returns relation schema data as XML.
 *
 * 1. Arity of relation
 * 2. Parameter names
 *
 * Example: (Note that arity is #params + 1, because of subject)
 *
 * <relationSchema name="hasState" arity="4">
 *  <param name="Pressure"/>
 *  <param name="Temperature"/>
 *  <param name="State"/>
 * </relationSchema>
 *
 * @param relationTitle as String
 *
 * @return xml string
 */
function smwf_om_RelationSchemaData($relationName) {
	global $smwgHaloContLang;
	$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();

	// get type definition (if it exists)
	$relationTitle = Title::newFromText($relationName, SMW_NS_PROPERTY);
	$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
	$type = smwfGetStore()->getPropertyValues($relationTitle, $hasTypeDV);

	// if no 'has type' annotation => normal binary relation
	if (count($type) == 0) {
		// return binary schema (arity = 2)
		$relSchema = '<relationSchema name="'.$relationName.'" arity="0">'.
           	  		 '</relationSchema>';
	} else {
		$typeLabels = $type[0]->getTypeLabels();
		$typeValues = $type[0]->getTypeValues();
		if ($type[0] instanceof SMWTypesValue) {

			// get arity
			$arity = count($typeLabels) + 1;  // +1 because of subject
			$relSchema = '<relationSchema name="'.$relationName.'" arity="'.$arity.'">';

			// If first parameter is a wikipage, take the property name + "|Page" as label, otherwise use type label.
			$firstParam = $typeValues[0] instanceof SMWWikiPageValue ? $relationName."|Page" : $typeLabels[0];
			$relSchema .= '<param name="'.$firstParam.'"/>';
			for($i = 1, $n = $arity-1; $i < $n; $i++) {

				// for all other wikipage parameters, use the range hint as label. If no range hint exists, simply print 'Page'.
				// makes normally only sense if at most one wikipage parameter exists. This will be handeled in another way in future.
				if ($typeValues[$i] instanceof SMWWikiPageValue) {
					$rangeHints = smwfGetStore()->getPropertyValues($relationTitle, smwfGetSemanticStore()->domainRangeHintProp);
					if (count($rangeHints) > 0) {
						$dvs = $rangeHints->getDVs();
						if ($dvs[1] !== NULL) {
							$labelToPaste = htmlspecialchars($dvs[1]->getTitle()->getText());
						} else {
							$labelToPaste = 'Page';
						}
					} else {
						$labelToPaste = 'Page';
					}
				} else {
					$labelToPaste = $typeLabels[$i];
				}
				$relSchema .= '<param name="'.$labelToPaste.'"/>';
			}
			$relSchema .= '</relationSchema>';

		} else { // this should never happen, huh?
			$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
							'<param name="Page"/>'.
        	   	  		 '</relationSchema>';
		}
	}
	return $relSchema;
}


/**
 * Returns the wiki text of an article. This function is invoked by an ajax call.
 *
 * @param string $pagename
 * 			The name of the article
 * @return string The wiki text or an empty string.
 *
 */
function smwf_om_GetWikiText($pagename) {

	if (smwf_om_userCan($pagename, 'read') === "false") {
		return "false,denied,$pagename";
	}
	$titleObj = Title::newFromText($pagename);
	if (! is_object($titleObj)) return "";
	$article = new Article($titleObj);

	if ($article->exists()) {
		return $article->getContent();
	} else {
		return "";
	}
}

/**
 * Deletes an article. This function is invoked by an ajax call.
 *
 * @param string $pagename The name of the article.
 * @param string $reason A reason why it was deleted.
 * @param string $user The name of the user who wants to delete the article
 */
function smwf_om_DeleteArticle($pagename, $user, $reason) {
	if (smwf_om_userCan($pagename, 'delete') === "false") {
		return "false,denied,$pagename";
	}

	$titleObj = Title::newFromText($pagename);

	$article = new Article($titleObj);

	if ($article->exists()) {
		$article->doDelete($reason);
	}
	return "true";
}

/**
 * Rename a type. This function is invoked by an ajax call.
 *
 * @param string $pagename The name of the property.
 * @param string $newTypename The new typename of the property.
 * @param string $reason A reason why it was renamed.
 * @param string $user The name of the user who requested this action.
 */
function smwf_om_EditProperty($pagename, $newtypename, $newCard, $newRange, $oldType, $oldCard, $oldRange, $category, $ID) {
	$newtypename = strip_tags($newtypename);
	if ($newtypename == '') return "false";

	if (smwf_om_userCan($pagename, 'move') === "false") {
		return "false,denied,$pagename";
	}
	$reason = '';
	$category = Title::newFromText($category);
	$titleObj = Title::newFromText($pagename);
	$oldType = Title::newFromText($oldType);
	$oldCard = Title::newFromText($oldCard);
	$oldRange = Title::newFromText($oldRange);
	$oldRange = strtolower($oldRange);
	$oldR = ''.$oldRange;
	$newType = Title::newFromText($newtypename);
	$newCard = Title::newFromText($newCard);
	$newRange = Title::newFromText($newRange);
	$newR = ''.$newRange;
	$Card = 'Has min cardinality::'.$oldCard;
	$nCard = 'Has min cardinality::'.$newCard;

	$type = 'Has type::Type:'.$oldType;
	$nType = 'Has type::Type:'.$newType;

	$range = 'Category:'.$oldRange;
	$nRange = 'Category:'.$newRange;

	$article = new Article($titleObj);

	$text = $article->getContent();

	//category and range
	if($oldR == ''){
		if($newR != ''){
			$hasDom    = '[[Has domain and range::Category:'.$category.']]';
			$newHasDom = '[[Has domain and range::Category:'.$category.'; '.$nRange.'| ]]';
		}
	}
	if($oldR != ''){
		if($newR == ''){
			$hasDom    = '[[Has domain and range::Category:'.$category.'; '.$range.'| ]]';
			$newHasDom = '[[Has domain and range::Category:'.$category.']]';
		}
		if($newR != ''){
			$hasDom    = '[[Has domain and range::Category:'.$category.'; '.$range.'| ]]';
			$newHasDom = '[[Has domain and range::Category:'.$category.'; '.$nRange.'| ]]';
		}
	}

	// write new property's properties
	$newContent = $text;
	if($oldType != $newType){
		$newContent = str_replace($type, $nType, $newContent);
	}
	if($newType != 'Page' ){
		$newContent = str_replace($hasDom, $newHasDom, $newContent);
	}
	if($oldCard != $newCard){
		$newContent = str_replace($Card, $nCard, $newContent);
	}
	if($oldR != $newRange){
		$newContent = str_replace($hasDom, $newHasDom, $newContent);
	}

	if ($article->exists()) {
		$article->doEdit($newContent, $reason);
	}
	return $newContent;
}

/**
 * Rename an article. This function is invoked by an ajax call.
 *
 * @param string $pagename The name of the article.
 * @param string $newpagename The new name of the article.
 * @param string $reason A reason why it was renamed.
 * @param string $user The name of the user who requested this action.
 */
function smwf_om_RenameArticle($pagename, $newpagename, $reason, $user) {
	$newpagename = strip_tags($newpagename);
	if ($newpagename == '') return "false";

	if (smwf_om_userCan($pagename, 'move') === "false") {
		return "false,denied,$pagename";
	}

	$titleObj = Title::newFromText($pagename);

	$newTitleObj = Title::newFromText($newpagename);
	$success = false;
	if ($titleObj->exists() && !$newTitleObj->exists()) {
		$success = $titleObj->moveTo($newTitleObj, true, $reason);
		$dummyForm = "";
		wfRunHooks( 'SpecialMovepageAfterMove', array( &$dummyForm , &$titleObj , &$newTitleObj ) )	;
	} else if (smwf_om_IsRedirect($newTitleObj)) { // target is redirect, so delete first
		smwf_om_DeleteArticle($newTitleObj->getPrefixedText(),"redirectrename", "Target is redirect");
		$success = $titleObj->moveTo($newTitleObj, true, $reason);
		$dummyForm = "";
		wfRunHooks( 'SpecialMovepageAfterMove', array( &$dummyForm , &$titleObj , &$newTitleObj ) )	;
	}
	return $success === true ? "true" : "false";
}

/**
 * Moves a category to a new super category.
 *
 * @param $draggedCategory Title of category to move (String)
 * @param $oldSuperCategory Title of old supercategory. (String) May be NULL
 * @param $newSuperCategory Title of new supercategory. (String) May be NULL
 */
function smwf_om_MoveCategory($draggedCategory, $oldSuperCategory, $newSuperCategory) {

	if (smwf_om_userCan($draggedCategory, 'move') === "false") {
		return "false";
	}

	$newSuperCategory = strip_tags($newSuperCategory);
	if ($newSuperCategory == '') return "false";

	$draggedOnRootLevel = $oldSuperCategory == 'null' || $oldSuperCategory == NULL;
	$draggedCategoryTitle = Title::newFromText($draggedCategory, NS_CATEGORY);
	$oldSuperCategoryTitle = Title::newFromText($oldSuperCategory, NS_CATEGORY);
	$newSuperCategoryTitle = Title::newFromText($newSuperCategory, NS_CATEGORY);


	if ($draggedCategoryTitle == NULL) {
		// invalid titles
		return "false";
	}


	$draggedCategoryRevision = Revision::newFromTitle($draggedCategoryTitle);
	$draggedCategoryArticle = new Article($draggedCategoryTitle);

	if ($draggedCategoryRevision == NULL || $draggedCategoryArticle == NULL) {
		// some problem occured.
		return "false";
	}

	$text = $draggedCategoryRevision->getText();


	if ($newSuperCategory == NULL || $newSuperCategory == 'null') {
		// remove all category links
		$newText = preg_replace("/\[\[\s*".$draggedCategoryTitle->getNsText()."\s*:\s*".preg_quote($oldSuperCategoryTitle->getText())."\s*\]\]/i", "", $text);

	} else if ($draggedOnRootLevel) {
		// dragged category was on root level
		$newText .= $text."\n[[".$draggedCategoryTitle->getNsText().":".$newSuperCategoryTitle->getText()."]]";
	} else {
		// replace on article $draggedCategory [[category:$oldSuperCategory]] with [[category:$newSuperCategory]]
		$newText = preg_replace("/\[\[\s*".$draggedCategoryTitle->getNsText()."\s*:\s*".preg_quote($oldSuperCategoryTitle->getText())."\s*\]\]/i", "[[".$draggedCategoryTitle->getNsText().":".$newSuperCategoryTitle->getText()."]]", $text);

	}
	$draggedCategoryArticle->doEdit($newText, $draggedCategoryRevision->getComment(), EDIT_UPDATE);
	return "true";
}

/**
 * Moves a property to a new super property.
 *
 * @param $draggedProperty Title of property to move (String)
 * @param $oldSuperProperty Title of old superproperty. (String) May be NULL
 * @param $newSuperProperty Title of new superproperty. (String) May be NULL
 */
function smwf_om_MoveProperty($draggedProperty, $oldSuperProperty, $newSuperProperty) {

	$newSuperProperty = strip_tags($newSuperProperty);
	if ($newSuperProperty == '') return "false";

	if (smwf_om_userCan($draggedProperty, 'move') === "false") {
		return "false";
	}

	$draggedOnRootLevel = $oldSuperProperty == 'null' || $oldSuperProperty == NULL;
	$draggedPropertyTitle = Title::newFromText($draggedProperty, SMW_NS_PROPERTY);
	$oldSuperPropertyTitle = Title::newFromText($oldSuperProperty, SMW_NS_PROPERTY);
	$newSuperPropertyTitle = Title::newFromText($newSuperProperty, SMW_NS_PROPERTY);

	if ($draggedPropertyTitle == NULL || $newSuperPropertyTitle == NULL) {
		// invalid titles
		return "false";
	}


	$draggedPropertyRevision = Revision::newFromTitle($draggedPropertyTitle);
	$draggedPropertyArticle = new Article($draggedPropertyTitle);

	if ($draggedPropertyRevision == NULL || $draggedPropertyArticle == NULL) {
		// some problem occured.
		return "false";
	}

	$text = $draggedPropertyRevision->getText();

	global $smwgContLang,$wgParser;
	$options = new ParserOptions();
	$sp = $smwgContLang->getPropertyLabels();

	if ($newSuperProperty == NULL || $newSuperProperty == 'null') {
		$newText = preg_replace("/\[\[\s*".$sp["_SUBP"]."\s*:[:|=]\s*".preg_quote($oldSuperPropertyTitle->getPrefixedText())."\s*\]\]/i", "", $text);
	} else if ($draggedOnRootLevel) {
		// dragged property was on root level
		$newText = $text."\n[[".$sp["_SUBP"]."::".$newSuperPropertyTitle->getPrefixedText()."]]";
	} else {
		// replace on article $draggedProperty [[Subproperty of::$oldSuperProperty]] with [[Subproperty of::$newSuperProperty]]
		$newText = preg_replace("/\[\[\s*".$sp["_SUBP"]."\s*:[:|=]\s*".preg_quote($oldSuperPropertyTitle->getPrefixedText())."\s*\]\]/i", "[[".$sp["_SUBP"]."::".$newSuperPropertyTitle->getPrefixedText()."]]", $text);
	}

	// save article
	$draggedPropertyArticle->doEdit($newText, $draggedPropertyRevision->getComment(), EDIT_UPDATE);
	$wgParser->parse($newText, $draggedPropertyTitle, $options, true, true, $draggedPropertyRevision->getID());
	//SMWFactbox::storeData(true);
	return "true";
}

function smwf_om_invalidateAllPages() {
	smwfGetSemanticStore()->invalidateAllPages();
	return "true";
}

/**
 * Checks if the current user can perform the given $action on the article with
 * the given $titleName.
 *
 * @param string $titleName
 * 		Name of the article
 * @param string $action
 * 		Name of the action
 * @param int $namespaceID
 * 		ID of the namespace of the title
 *
 * @return bool
 * 		<true> if the action is permitted
 * 		<false> otherwise
 */
function smwf_om_userCan($titleName, $action, $namespaceID = 0) {
	// Special handling if the extension HaloACL is present
	if (defined('HACL_HALOACL_VERSION')) {
		$etc = haclfDisableTitlePatch();
	}
	$title = Title::newFromText($titleName, $namespaceID);
	if (defined('HACL_HALOACL_VERSION')) {
		haclfRestoreTitlePatch($etc);
	}
	global $wgUser;
	$result = true;
	wfRunHooks('userCan', array($title, $wgUser, $action, &$result));
	if (isset($result) && $result == false) {
		return "false";
	} else {
		return "true";
	}
}

/**
 * Checks if the current user can perform the given $action on the articles with
 * the given $titleNames.
 *
 * @param string $titleName
 * 		Comma separated list of article names
 * @param string $action
 * 		Name of the action
 *
 * @return bool
 * 		A JSON encoded array of results:
 * 		array(
 * 			array(titlename, allowed or not: true/false),
 * 			...
 * 		)
 */
function smwf_om_userCanMultiple($titleNames, $action) {
	// Special handling if the extension HaloACL is present
	global $wgUser;

	$titleNames = explode(',', $titleNames);
	if (defined('HACL_HALOACL_VERSION')) {
		$etc = haclfDisableTitlePatch();
	}
	$results = array();
	foreach ($titleNames as $t) {
		$result = true;
		$title = Title::newFromText(trim($t));
		wfRunHooks('userCan', array($title, $wgUser, $action, &$result));
		if (isset($result) && $result == false) {
			$results[] = array($t, "false");
		} else {
			$results[] = array($t, "true");
		}
			
	}
	if (defined('HACL_HALOACL_VERSION')) {
		haclfRestoreTitlePatch($etc);
	}
	return json_encode($results);
}


/**
 * This function retrieves the derived facts of the article with the name
 * $titleName.
 *
 * @param string $titleName
 *
 * @return string
 * 		The derived facts as HTML
 */
function smwf_om_GetDerivedFacts($titleName) {
	$linker = new Linker();

	$t = Title::newFromText($titleName);
	if ($t == null) {
		// invalid title
		return wfMsg('smw_df_invalid_title');
	}

	if (!smwfIsTripleStoreConfigured()) {
		global $wgParser;
		$parserOutput = $wgParser->parse( wfMsg('smw_df_tsc_advertisment'), $t, new ParserOptions,
		true, true, 0 );
		return $parserOutput->getText();
	}

	$semdata = smwfGetStore()->getSemanticData(new SMWDIWikiPage($t->getDBkey(), $t->getNamespace(), ""));
	wfLoadExtensionMessages('SemanticMediaWiki');
	global $wgContLang;
	list($derivedFacts, $derivedCategories) = SMWFullSemanticData::getDerivedProperties($semdata);
	$derivedFactsFound = false;

	$text = '<div class="smwfact">' .
				'<span class="smwfactboxhead">' . 
	wfMsg('smw_df_derived_facts_about',
	$derivedFacts->getSubject()->getTitle()->getText()) .
				'</span>' .
				'<table class="smwfacttable">' . "\n";

	foreach($derivedFacts->getProperties() as $propertyDi) {
		$propertyDv = SMWDataValueFactory::newDataItemValue($propertyDi, null);

		if ( !$propertyDi->isShown() ) { // showing this is not desired, hide
			continue;
		} elseif ( $propertyDi->isUserDefined() ) { // user defined property
			$propertyDv->setCaption( preg_replace( '/[ ]/u', '&#160;', $propertyDv->getWikiValue(), 2 ) );
			/// NOTE: the preg_replace is a slight hack to ensure that the left column does not get too narrow
			$text .= '<tr><td class="smwpropname">' . $linker->makeLink($propertyDi->getDiWikiPage()->getTitle()->getPrefixedText()) . '</td><td class="smwprops">';
		} elseif ( $propertyDv->isVisible() ) { // predefined property
			$text .= '<tr><td class="smwspecname">' . $linker->makeLink($propertyDi->getDiWikiPage()->getTitle()->getPrefixedText()) . '</td><td class="smwspecs">';
		} else { // predefined, internal property
			continue;
		}

		$propvalues = $derivedFacts->getPropertyValues($propertyDi);

		$valuesHtml = array();

		foreach ( $propvalues as $dataItem ) {
			$dataValue = SMWDataValueFactory::newDataItemValue( $dataItem, $propertyDi );

			if ( $dataValue->isValid() ) {
				$derivedFactsFound = true;
				$valuesHtml[] = $dataValue->getLongWikiText( true );
			}
		}

		$text .= $GLOBALS['wgLang']->listToText( $valuesHtml );
		$text .= '</td></tr>';
	}
	$text .= '</table>';

	$categoryLinks=array();
	foreach($derivedCategories as $c) {
		$derivedFactsFound=True;
		$categoryLinks[] = $linker->link($c);
	}
	$text .= '<br>'.implode(", ", $categoryLinks);
	$text .= '</div>';

	if (!$derivedFactsFound) {
		$text = wfMsg('smw_df_no_df_found');
	}
	return $text;
}

/**
 * This function retrieves all properties that have one of the given categories as domain
 *
 * @param: string $categoryNames
 * 		Comma seperated list of category names including the NS
 *
 * @return:
 * 	 A JSON encoded array of results:
 * 		array(
 * 			array(propName, inherited, [inheritChain]),
 * 			...
 * 		)
 */

function smwf_om_getDomainProperties($categoryNames) {

	$categoryNames = explode(',', $categoryNames);
	$properties = array();

	if( !is_array($categoryNames) || empty($categoryNames) ) {
		return json_encode($properties);
	}
	foreach($categoryNames as $cN){
		if( empty($cN) ) {
			continue;
		}
		$cT = Title::newFromText($cN, NS_CATEGORY);
		//direct
		foreach(smwfGetSemanticStore()->getPropertiesWithDomain($cT) as $p){
			$notDefined = true;
			for( $i = 0; $i <= sizeof($properties); ++$i) {
				if( isset($properties[$i]) &&
				array_key_exists('propText', $properties[$i]) &&
				$properties[$i]['propText'] == $p->getText()) {
					$notDefined = false;
					if( array_key_exists('inherited', $properties[$i]) &&
					$properties[$i]['inherited'] == 'true' ) {
						// replace inherited property with direct one
						array_splice($properties, $i, 1);
						$itemRemoved = true;
					}
				}
			}
			if( $notDefined || ( !$notDefined && $itemRemoved ) ) {
				$properties[] = array(
					'propText' => $p->getText(),
					'inherited' => 'false',
					'inheritLink' => array()
				);
			}
		}
		// inherited
		$superCategoryTitles = smwf_om_getSuperCategories($cT);
		foreach($superCategoryTitles as $sCT){
			foreach(smwfGetSemanticStore()->getPropertiesWithDomain($sCT) as $p){
				// avoid multiple things
				$notDefined = true;
				foreach( $properties as $prop ) {
					if($prop['propText'] == $p->getText()) {
						$notDefined = false;
					}
				}
				if( $notDefined ) {
					$properties[] = array(
						'propText' => $p->getText(),
						'inherited' => 'true',
						'inheritLink' => array($cT->getText(), $sCT->getText())
					);
				}
			}
		}
	}

	return json_encode($properties);
}

/**
 * Get all supercategories of a given category
 *
 * @param: object $categoryTitle
 * 		A Title object
 * @param: boolean $asTree
 * @param: array $superCategoryTitles
 * 		An array of Title objects
 *
 * @return:
 * 	 An array of Title objects
 */
function smwf_om_getSuperCategories($categoryTitle, $asTree = false, $superCategoryTitles = array()){
	$directSuperCatgeoryTitles = smwfGetSemanticStore()->getDirectSuperCategories($categoryTitle);
	if($asTree){
		$superCategoryTitles[$categoryTitle->getText()] = array();
	}
	foreach($directSuperCatgeoryTitles as $dSCT){
		if($asTree){
			$superCategoryTitles[$categoryTitle->getText()] =
			smwf_om_getSuperCategories($dSCT, $asTree, $superCategoryTitles[$categoryTitle->getText()]);
		} else {
			$superCategoryTitles[$dSCT->getText()] = $dSCT;
			$superCategoryTitles = smwf_om_getSuperCategories($dSCT, $asTree, $superCategoryTitles);
		}
	}
	return $superCategoryTitles;
}

/**
 * Retrieves the schema of the relation with the given name.
 * This is a "private" function.
 *
 * @param string $relationName
 * 		Name of the relation
 *
 */
function smwf_om_getRelationSchema($relationName) {
	// get type definition (if it exists)
	$relationTitle = Title::newFromText($relationName, SMW_NS_PROPERTY);
	$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
	$type = smwfGetStore()->getPropertyValues($relationTitle, $hasTypeDV);

	// if no 'has type' annotation => normal binary relation
	if (count($type) == 0) {
		// return binary schema with the default type 'page'
		$relSchema = array('_wpg');
		$categories = array(null);
		return array($relSchema, $categories);
	}
	$type = array_values($type);
	$typeLabels = $type[0]->getTypeLabels();
	$typeValues = $type[0]->getTypeValues();
	$relSchema = array();
	$categories = array();

	if (!($type[0] instanceof SMWTypesValue)) {
		return array($relSchema, $categories);
	}

	// Get category names for page values
	$rangeHints = smwfGetStore()
	->getPropertyValues($relationTitle,
	smwfGetSemanticStore()->domainRangeHintProp);
	$rangeHints = array_values($rangeHints);

	if ($type[0]->getDBkey() == '_rec') {
		// This is an n-ary property
		// => the property "has fields" contains the actual types
		$fieldsProp = SMWPropertyValue::makeProperty("_LIST");
		$fields = smwfGetStore()->getPropertyValues($relationTitle, $fieldsProp);
		$fields = array_values($fields);
		$typeValues = $fields[0]->getTypeValues();
		foreach ($typeValues as $tv) {
			$relSchema[] = $tv->getDBkey();
		}

		// Assign the range hints to array $categories
		$i = 0;
		foreach ($relSchema as $valueType) {
			$cat = null;
			if ($valueType == '_wpg' && count($rangeHints) > $i) {
				$dvs = $rangeHints[$i]->getDVs();
				if ($dvs[1] !== NULL) {
					$cat = htmlspecialchars($dvs[1]->getTitle()->getText());
				}
				++$i;
			}
			$categories[] = $cat;
		}
			
	} else {
		// A simple property
		$relSchema[] = $type[0]->getDBkey();
		$cat = null;
		if ($relSchema[0] == '_wpg') {
			if (count($rangeHints) > 0) {
				$dvs = $rangeHints[0]->getDVs();
				if ($dvs[1] !== NULL) {
					$cat = htmlspecialchars($dvs[1]->getTitle()->getText());
				}
			}
		}
		$categories[] = $cat;
	}
	return array($relSchema, $categories);

}