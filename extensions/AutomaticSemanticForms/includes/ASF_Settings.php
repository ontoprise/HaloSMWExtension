<?php

/*
 * This file provides some global varianles for 
 * configurting the behaviour of the Automatic Semantic
 * Forms extension
 */

/*
 * Name of the dummy form which is created and used by
 * the Automatic Semantic Forms extension. This form
 * will be created if it does not exist. It only contains a dummy text. 
 */
global $asfDummyFormName;
$asfDummyFormName = "AutomaticSemanticForm";

/*
 * Decide whther to make use of the Semantic Forms Inputs
 * extension features.
 */
global $asfUseSemanticFormsInputsFeatures;
$asfUseSemanticFormsInputsFeatures = true;


/*
 * Decide whther to compute the category sections in the
 * form so that no doublicate properties are shown or whether
 * to just display one category section for each category 
 * annotation of the instance
 */
global $asfDoEnhancedCategorySectionProcessing;
$asfDoEnhancedCategorySectionProcessing = true;


/*
 * Decide whether to display a category section for each
 * category in the category hierarchy of the instance or whether
 * to combine them where possible. 
 * This setting only has an effect if $asfDoEnhancedCategorySectionProcessing
 * is set to true;
 */
global $asfCombineCategorySectionsWherePossible;
$asfCombineCategorySectionsWherePossible = true;

/*
 * Decide whether to display property labels and category labels as links
 */
global $asfDisplayPropertiesAndCategoriesAsLinks;
$asfDisplayPropertiesAndCategoriesAsLinks = false;


/*
 * Specify whether to use the Halo autocompletion
 */
global $asfUseHaloAutocompletion;
$asfUseHaloAutocompletion = true;


/*
 * Set this to true, if you want ASF to deal with red-links
 */
global $asfEnableRedLinkHandler;
$asfEnableRedLinkHandler = true;
 
