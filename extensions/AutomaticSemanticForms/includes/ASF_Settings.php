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
$asfDisplayPropertiesAndCategoriesAsLinks = true;


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



/*
 * Set this true ti you want to get some extra information
 * about the ontology via tooltips in the forms. this is recommendet.
 */
global $asfShowTooltips;
$asfShowTooltips = true;
