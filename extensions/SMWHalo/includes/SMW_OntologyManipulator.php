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
$wgAjaxExportList[] = 'smwf_om_MoveProperty';
$wgAjaxExportList[] = 'smwf_om_MoveInstance';
$wgAjaxExportList[] = 'smwf_om_invalidateAllPages';
$wgAjaxExportList[] = 'smwf_om_userCan';
$wgAjaxExportList[] = 'smwf_om_userCanMultiple';
$wgAjaxExportList[] = 'smwf_om_GetDerivedFacts';
$wgAjaxExportList[] = 'smwf_om_getDomainProperties';
$wgAjaxExportList[] = 'smwf_om_getSuperCategories';
$wgAjaxExportList[] = 'smwf_om_getAnnotatedCategories';
$wgAjaxExportList[] = 'smwf_om_annotateCategories';

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
	if (!$titleObj) {
		return "false";
	}
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
 *      - recordProperties (array): Empty, if the property is NOT a record. Otherwise
 *      						the name of each property in the record and if a
 *      						page for this property exists ("true" or "false")
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
		list($relSchema, $categories, $recordProperties, $recPropExists)
		= $relDescr->relationExists === 'true'
		? smwf_om_getRelationSchema($relDescr->name)
		: array(array('_wpg'), array(null), array());
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
		$recProp = array();
		foreach ($recordProperties as $idx => $propName) {
			$recProp[] = $propName;
			$recProp[] = $recPropExists[$idx];
		}

		$relDescr->valuePageInfo = $valuePageInfo;
		$relDescr->rangeCategories = $categories;
		$relDescr->relationSchema = $relSchema;
		$relDescr->recordProperties = $recProp;
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
 *  <param name="has Pressure"/>
 *  <param name="has Temperature"/>
 *  <param name="has State"/>
 * </relationSchema>
 *
 * @param relationTitle as String
 *
 * @return xml string
 */
function smwf_om_RelationSchemaData($relationName) {
	global $wgContLang;
	$propPrefix = $wgContLang->getNsText(SMW_NS_PROPERTY).":";

	$relSchema = '<relationSchema name="'.$relationName.'" arity="0">'.
             	 '</relationSchema>';
	$exists = smwf_om_ExistsArticle($propPrefix.$relationName);
	if (!$exists) {
		// There is no such relation
		return $relSchema;
	}
	list($schema, $categories, $recProperties)
	= smwf_om_getRelationSchema($relationName);
	$arity = count($schema) + 1; // +1 because of subject
	$relSchema = '<relationSchema name="'.$relationName.'" arity="'.$arity.'">';
	// If first parameter is a wikipage, take the property name + "|Page" as
	// label, otherwise use the label of the first property in a record.
	$firstParam = $relationName;
	if (count($schema) == 1) {
		if ($schema[0] === '_wpg') {
			$firstParam .= "|Page";
		}
	} else {
		$firstParam = htmlspecialchars(str_replace( '_', ' ', $recProperties[0]));
	}
	$relSchema .= '<param name="'.$firstParam.'"/>';
	for($i = 1, $n = $arity-1; $i < $n; $i++) {
		$relSchema .= '<param name="'.htmlspecialchars(str_replace( '_', ' ', $recProperties[$i])).'"/>';
	}
	$relSchema .= '</relationSchema>';

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

function smwf_om_getSuperCategoryTitles($categoryTitle){
	$directSuperCategeoryTitles = smwfGetSemanticStore()->getDirectSuperCategories($categoryTitle);
	return $directSuperCategeoryTitles;
}

/**
 * Rename a type. This function is invoked by an ajax call.
 *
 * @param string $pagename The name of the property.
 * @param string $newTypename The new type of the property (without type prefix).
 * @param int $newCard New cardinality
 * @param string $newRange The new range category (without category prefix)
 * @param string $oldType The old type of the property (without type prefix).
 * @param int $oldCard Old cardinality.
 * @param string $oldRange The old range category
 * @param string domainCategory The old (and new) domain category (without category prefix).
 */
function smwf_om_EditProperty($pagename, $newType, $newCard, $newRange, $oldType, $oldCard, $oldRange, $domainCategory, $ID) {

	//FIXME: (alami) $oldCard, $oldType are redundant. please remove.

	$newType = strip_tags($newType);
	if ($newType == '') return "false";

	if (smwf_om_userCan($pagename, 'move') === "false") {
		return "false,denied,$pagename";
	}

	$titleObj = Title::newFromText($pagename);
	$article = new Article($titleObj);
	$text = $article->getContent();

	global $smwgHaloContLang;
	$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
	$hasDomainAndRangeProperty = $ssp[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT];

	// Replace "Has domain range range" annotations
	$oldDomainCategory = Title::newFromText($domainCategory, NS_CATEGORY);
	if ($oldRange != '') {
		$oldRangeCategory = Title::newFromText($oldRange, NS_CATEGORY);
		$search = '/(\[\[(\s*)' . $hasDomainAndRangeProperty . '(\s*)::\s*'.$oldDomainCategory->getPrefixedText().'\s*;\s*'.$oldRangeCategory->getPrefixedText().'\s*(\|)?\s*\]\])/i';
		if ($newRange != '') {
			$newRangeCategory = Title::newFromText($newRange, NS_CATEGORY);
			$replace = '[[' . $hasDomainAndRangeProperty . '::'.$oldDomainCategory->getPrefixedText().'; '.$newRangeCategory->getPrefixedText().']]';
		} else {
			$replace = '[[' . $hasDomainAndRangeProperty . '::'.$oldDomainCategory->getPrefixedText().']]';
		}
	} else {
		$search = '/(\[\[(\s*)' . $hasDomainAndRangeProperty . '(\s*)::\s*'.$oldDomainCategory->getPrefixedText().'\s*(;)?\s*(\|)?\s*\]\])/i';
		if ($newRange != '') {
			$newRangeCategory = Title::newFromText($newRange, NS_CATEGORY);
			$replace = '[[' . $hasDomainAndRangeProperty . '::'.$oldDomainCategory->getPrefixedText().'; '.$newRangeCategory->getPrefixedText().']]';
		} else {
			$replace = '[[' . $hasDomainAndRangeProperty . '::'.$oldDomainCategory->getPrefixedText().']]';
		}
	}

	if (preg_match($search, $text) === 0) {
		// replacement does not yet exist, so add it simply
		$text .= "\n$replace";
	} else {
		$text = preg_replace($search, $replace, $text);
	}

	// Replace "has type" annotations
	global $smwgContLang;
	$propertyLabels = $smwgContLang->getPropertyLabels();

	$search = '/(\[\[(\s*)' . $propertyLabels['_TYPE'] . '(\s*)::\s*([^]|]+)\s*(\|)?\s*\]\])/i';
	$newTypeTitle = Title::newFromText($newType, SMW_NS_TYPE);
	$replace = '[[' . $propertyLabels['_TYPE'] . '::'.$newTypeTitle->getPrefixedText().']]';

	if (preg_match($search, $text) === 0) {
		// replacement does not yet exist, so add it simply
		$text .= "\n$replace";
	} else {
		$text = preg_replace($search, $replace, $text);
	}


	// Replace "has min cardinality" annotations
	$hasMinCardinalityProperty = $ssp[SMW_SSP_HAS_MIN_CARD];
	$search = '/(\[\[(\s*)' .$hasMinCardinalityProperty . '(\s*)::\s*[^]|]+\s*(\|)?\s*\]\])/i';
	$replace = '[[' . $hasMinCardinalityProperty . '::'.$newCard.']]';
	if (preg_match($search, $text) === 0) {
		// replacement does not yet exist, so add it simply
		$text .= "\n$replace";
	} else {
		$text = preg_replace($search, $replace, $text);
	}

	if ($article->exists()) {
		$reason = '';
		$article->doEdit($text, $reason);
	}
	return $text;
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
		$superCategoryTitles = _smwfGetSuperCategories($cT);
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

function _smwfGetSuperCategories($categoryTitle, $asTree = false, $superCategoryTitles = array()){
	$directSuperCatgeoryTitles = smwfGetSemanticStore()->getDirectSuperCategories($categoryTitle);
	if($asTree){
		$superCategoryTitles[$categoryTitle->getText()] = array();
	}
	foreach($directSuperCatgeoryTitles as $dSCT){
		if($asTree){
			$superCategoryTitles[$categoryTitle->getText()] =
			_smwfGetSuperCategories($dSCT, $asTree, $superCategoryTitles[$categoryTitle->getText()]);
		} else {
			$superCategoryTitles[$dSCT->getText()] = $dSCT;
			$superCategoryTitles = _smwfGetSuperCategories($dSCT, $asTree, $superCategoryTitles);
		}
	}
	$response = new AjaxResponse($superCategoryTitles);
	$response->setResponseCode(200);
	return $response;
}

/**
 * Returns all direct super categories
 *
 * @param $categoryTitle
 *
 * @return string comma-separated list of categories
 */
function smwf_om_getSuperCategories($articleTitle){
	$directSuperCatgeoryTitles = smwfGetSemanticStore()->getDirectSuperCategories(Title::newFromText($articleTitle, NS_CATEGORY));

	if($directSuperCatgeoryTitles == null){
		$supeCategories ='';
	}else{
		$supeCategories = $directSuperCatgeoryTitles[0]->getText();
		if(sizeof($directSuperCatgeoryTitles) > 1){
			for($i = 1, $n = sizeof($directSuperCatgeoryTitles); $i < $n; $i++) {
				$supeCategories .= ','.$directSuperCatgeoryTitles[$i]->getText();
			}
		}
	}
	return $supeCategories;
}



/**
 * Retrieves the annotated categories of the instance with the given name.
 *
 * @param string $titleString Prefixed title
 *
 * @return string (comma separated list)
 */
function smwf_om_getAnnotatedCategories($titleString) {
	$titleObj = Title::newFromText($titleString);
	$categories = smwfGetSemanticStore()->getCategoriesForInstance($titleObj);
	$result = "";
	foreach($categories as $c) {
		$result .= ",".$c->getText();
	}
	$result =  $result == '' ? '' : substr($result,1);
	$response = new AjaxResponse($result);
	$response->setResponseCode(200);
	return $response;
}

/**
 * Annotates a new set of categories and removes the old before.
 *
 * @param $articleTitle
 * @param $newAnnotatedCategories
 *
 * @return true
 */
function smwf_om_annotateCategories($articleTitle, $newAnnotatedCategories){

	$titleObj = Title::newFromText($articleTitle);
	$article = new Article($titleObj);
	$text = $article->getContent();

	global $wgLang;
	$Category = $wgLang->getNsText(NS_CATEGORY);
	$search = '/(\[\[\s*' . $Category . '\s*:\s*([^|]+)\s*(\|([^]])*)?\s*\]\])/i';
	$text = preg_replace($search, "", $text);

	$annotatedCategories = explode(",",$newAnnotatedCategories);

	foreach($annotatedCategories as $c) {
		$text .= "\n[[$Category:".$c."]]";
	}

	if ($article->exists()) {
		$reason = '';
		$status = $article->doEdit($text, $reason);
		if ($status->isOK()) {
			$response = new AjaxResponse("true");
			$response->setResponseCode(200);
			return $response;
		}
	} else {
		$response = new AjaxResponse("Title '$articleTitle' does not exist.");
		$response->setResponseCode(400);
		return $response;
	}

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
	$relationDI = SMWDIWikiPage::newFromTitle($relationTitle);
	$hasTypeDI = SMWDIProperty::newFromUserLabel("_TYPE");

	$type = smwfGetStore()->getPropertyValues($relationDI, $hasTypeDI);

	// if no 'has type' annotation => normal binary relation
	if (count($type) == 0) {
		// return binary schema with the default type 'page'
		$relSchema = array('_wpg');
		$categories = array(null);
		$recordProperties = array();
		return array($relSchema, $categories, $recordProperties);
	}
	$type = array_values($type);
	$relSchema = array();
	$categories = array();
	$recordProperties = array();
	$recordPropertiesExist = array();

	if (!($type[0] instanceof SMWDIUri)) {
		return array($relSchema, $categories, $recordProperties);
	}

	if ($type[0]->getFragment() == '_rec') {
		// This is a record (an n-ary property)
		// => the property "has fields" contains properties
		$fieldsProp = SMWDIProperty::newFromUserLabel("_LIST");
		$fields = smwfGetStore()->getPropertyValues($relationDI, $fieldsProp);
		if (count($fields) > 0) {
			$keys = array_keys($fields);
			$fields = $fields[$keys[0]]->getString();
			// get the names of all properties in record
			$recordProperties = explode(';',$fields);

			// get the types of all record properties and their range categories
			global $wgContLang;
			$propPrefix = $wgContLang->getNsText(SMW_NS_PROPERTY).":";
			foreach ($recordProperties as $recProp) {
				$exists = smwf_om_ExistsArticle($propPrefix.$recProp);
				$recordPropertiesExist[] = $exists;
				list($recPropSchema, $recPropCategories) = $exists === 'true'
				? smwf_om_getRelationSchema($recProp)
				: array(array('_wpg'), array(NULL));
				$relSchema[] = $recPropSchema[0];
				$categories[] = $recPropCategories[0];
			}
		}
	} else {
		// A simple property
		$store = smwfGetSemanticStore();
		$cats = $store->getRangeCategories($relationTitle);
		$relSchema[] = $type[0]->getFragment();
		if (count($cats) > 0) {
			$categories[] = htmlspecialchars($cats[0]->getText());
		} else {
			$categories[] = NULL;
		}
	}
	return array($relSchema, $categories, $recordProperties, $recordPropertiesExist);

}