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
/*
 * Created on 22.05.2007
 *
 * This file contains methods that modify the ontology.
 *
 * @author Thomas Schweitzer
 */

global $wgAjaxExportList;

$wgAjaxExportList[] = 'smwfCreateArticle';
$wgAjaxExportList[] = 'smwfExistsArticle';
$wgAjaxExportList[] = 'smwfRelationSchemaData';

/**
 * Creates a new article or appends some text if it already
 * exists. This function is invoked by an ajax call.
 *
 * @param string $title
 * 			The name of the article
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
 * 			string title
 * 				Title of the (new) article
 *
 */
function smwfCreateArticle($title, $content, $optionalText, $creationComment) {

	global $smwgContLang, $smwgHaloContLang;

	$success = false;
	$created = true;

	$title = Title::newFromText($title);

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
		$supportedConstants = array("SMW_SP_SUBPROPERTY_OF",
                                    "SMW_SSP_HAS_DOMAIN_HINT",
                                    "SMW_SSP_HAS_RANGE_HINT",
                                    "SMW_SSP_HAS_MAX_CARD",
                                    "SMW_SSP_HAS_MIN_CARD",
                                    "SMW_SP_HAS_TYPE");

		// Some optional text is given
		$sp = $smwgContLang->getSpecialPropertiesArray();
		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();

		$num = count($supportedConstants);
		for ($i = 0; $i < $num; $i++) {
			$constant = $supportedConstants[$i];

 			$pos = strpos($optionalText, $constant);
 			if ($pos) {
 				$langString = "Unsupported constant";
 				if (strpos($constant, "SMW_SSP_") !== false) {
 					$langString = $ssp[constant($constant)];
 				} else if (strpos($constant, "SMW_SP_") !== false) {
 					$langString = $sp[constant($constant)];
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
	       $title->getNsText().":".$title->getText();
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
function smwfExistsArticle($title) {
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

	if ($titleObj->getNamespace() == SMW_NS_RELATION) {
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
	$specialProps = $smwgContLang->getSpecialPropertiesArray();
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
function smwfRelationSchemaData($relationName) {
	global $smwgHaloContLang;
	$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();

	// get type definition (if it exists)
	$relationTitle = Title::newFromText($relationName, SMW_NS_PROPERTY);
	$type = smwfGetStore()->getSpecialValues($relationTitle, SMW_SP_HAS_TYPE);

	// if no 'has type' annotation => normal binary relation
	if (count($type) == 0) {
		// return binary schema (arity = 2)
		$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
						'<param name="Page"/>'.
           	  		 '</relationSchema>';
	} else {
		$typeLabels = $type[0]->getTypeLabels();
		$typeValues = $type[0]->getTypeValues();
		if ($type[0] instanceof SMWTypesValue) {

			// get arity
			$arity = count($typeLabels) + 1;  // +1 because of subject
	   		$relSchema = '<relationSchema name="'.$relationName.'" arity="'.$arity.'">';

	   		// If first parameter is a wikipage, take the property name as label, otherwise use type label.
	   		$firstParam = $typeValues[0] instanceof SMWWikiPageValue ? $relationName : $typeLabels[0];
	   		$relSchema .= '<param name="'.$firstParam.'"/>';
	   		for($i = 1, $n = $arity-1; $i < $n; $i++) {

	   			// for all other wikipage parameters, use the range hint as label. If no range hint exists, simply print 'Page'.
	   			// makes normally only sense if at most one wikipage parameter exists. This will be handeled in another way in future.
	   			if ($typeValues[$i] instanceof SMWWikiPageValue) {

	   				$domainRangeHintRelation = Title::newFromText($smwSpecialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT], SMW_NS_PROPERTY);
	   				$rangeHints = smwfGetStore()->getPropertyValues($relationTitle, $domainRangeHintRelation);
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

?>
