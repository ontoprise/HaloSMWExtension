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


global $wgAjaxExportList;
$wgAjaxExportList[] = "lodGetDataSourceTable";
$wgAjaxExportList[] = 'smwf_ts_getSyncCommands';
$wgAjaxExportList[] = 'smwf_ts_getWikiNamespaces';
$wgAjaxExportList[] = 'smwf_ts_getWikiSpecialProperties';
$wgAjaxExportList[] = 'smwf_ts_triggerAsynchronousLoading';
$wgAjaxExportList[] = 'smwf_om_GetDerivedFacts';
    
/**
 * Returns the datasource table as HTML
 *  
 * @return AjaxResponse HTML. Otherwise an error message.
 *       
 */
function lodGetDataSourceTable() {
    $response = new AjaxResponse();
    $response->setContentType("text/html");

    try {
       $lodSourcePage = new LODSourcesPage();
       $response->addText($lodSourcePage->createSourceTable($lodSourcePage->getAllSources()));
    } catch(Exception $e) {
        $response->setResponseCode($e->getCode());
        $response->addText($e->getMessage());
    }
    
    return $response;
}

/**
 * Returns a list of SPARUL commands which are required to sync
 * with the TSC.
 *
 * @return string
 */
function smwf_ts_getSyncCommands() {
    global $smwgMessageBroker, $smwgHaloTripleStoreGraph, $wgDBtype, $wgDBport,
    $wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword, $wgDBprefix, $wgLanguageCode,
    $smwgIgnoreSchema, $smwgNamespaceIndex;

    $sparulCommands = array();

    // sync wiki module
    $sparulCommands[] = "DROP SILENT GRAPH <$smwgHaloTripleStoreGraph>"; // drop may fail. don't worry
    $sparulCommands[] = "CREATE SILENT GRAPH <$smwgHaloTripleStoreGraph>";
    $sparulCommands[] = "LOAD <smw://".urlencode($wgDBuser).":".urlencode($wgDBpassword).
    "@$wgDBserver:$wgDBport/$wgDBname?lang=$wgLanguageCode&smwstore=SMWHaloStore2".
    "&smwnsindex=$smwgNamespaceIndex#".urlencode($wgDBprefix).
    "> INTO <$smwgHaloTripleStoreGraph>";

    // sync external modules (only if DF is installed)
    if (defined('DF_VERSION')) {

        $externalArtifacts = DFBundleTools::getExternalArtifacts();

        foreach($externalArtifacts as $extArt) {
            list($fileTitle, $uri) = $extArt;
            $sparulCommands[] = "DROP SILENT GRAPH <$uri>"; // drop may fail. don't worry
            $sparulCommands[] = "CREATE SILENT GRAPH <$uri>";

            $localFile = wfLocalFile($fileTitle);
            $format = DFBundleTools::guessOntologyFileType($fileTitle->getText());
            $fileURL = $localFile->getFullUrl();
            $sparulCommands[] = "LOAD <$fileURL?format=$format> INTO <$uri>";
            $sparulCommands[] = "IMPORT ONTOLOGY <$uri> INTO <$smwgHaloTripleStoreGraph>";
        }


    }

    return implode("\n", $sparulCommands);
}



/**
 * Returns a list of namespace mappings.
 * Exported as ajax call.
 *
 * Need by TSC to get extra namespaces (besides the default of MW + SMW + SF) and required
 * to support other content languages than english.
 *
 * nsText(content language) => nsKey
 *
 * @return string
 */
function smwf_ts_getWikiNamespaces() {
    global $wgExtraNamespaces, $wgContLang;

    $allNS = array(NS_CATEGORY, SMW_NS_PROPERTY,SF_NS_FORM, SMW_NS_CONCEPT, NS_MAIN ,
    NS_FILE, NS_HELP, NS_TEMPLATE, NS_USER, NS_MEDIAWIKI, NS_PROJECT,   SMW_NS_PROPERTY_TALK,
    SF_NS_FORM_TALK,NS_TALK, NS_USER_TALK, NS_PROJECT_TALK, NS_FILE_TALK, NS_MEDIAWIKI_TALK,
    NS_TEMPLATE_TALK, NS_HELP_TALK, NS_CATEGORY_TALK, SMW_NS_CONCEPT_TALK);

    $extraNamespaces = array_diff(array_keys($wgExtraNamespaces), $allNS);
    $allNS = array_merge($allNS, $extraNamespaces);
    $result = "";
    $first = true;
    foreach($allNS as $nsKey) {

        $nsText = $wgContLang->getNSText($nsKey);
        if (empty($nsText) && $nsKey !== NS_MAIN) continue;
        $result .= (!$first ? "," : "").$nsText."=".$nsKey;
        $first = false;
    }
    return $result;
}

/**
 * Maps language constants of special properties/categories to content language.
 * Exported as ajax call.
 *
 * Need by TSC.
 *
 * Language constant representing a special property/category = Name in wiki's content language
 *
 * @return string
 */
function smwf_ts_getWikiSpecialProperties() {

    global $wgContLang, $smwgHaloContLang, $smwgContLang;
    $specialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
    $specialCategories = $smwgHaloContLang->getSpecialCategoryArray();
    $specialProperties = $smwgHaloContLang->getSpecialPropertyLabels();
    $specialPropertiesSMW = $smwgContLang->getPropertyLabels();
    
    

    $result = "HAS_DOMAIN_AND_RANGE=".$specialSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT].",".
                "HAS_MIN_CARDINALITY=".$specialSchemaProperties[SMW_SSP_HAS_MIN_CARD].",".
                "HAS_MAX_CARDINALITY=".$specialSchemaProperties[SMW_SSP_HAS_MAX_CARD].",".
                "IS_INVERSE_OF=".$specialSchemaProperties[SMW_SSP_IS_INVERSE_OF].",".
                "TRANSITIVE_PROPERTIES=".$specialCategories[SMW_SC_TRANSITIVE_RELATIONS].",".
                "SYMETRICAL_PROPERTIES=".$specialCategories[SMW_SC_SYMMETRICAL_RELATIONS].",".
                "CORRESPONDS_TO=".$specialPropertiesSMW['_CONV'].",".
                "HAS_TYPE=".$specialPropertiesSMW['_TYPE'].",".
                "HAS_FIELDS=".$specialPropertiesSMW['_LIST'].",".
                "MODIFICATION_DATE=".$specialPropertiesSMW['_MDAT'].",".
                "EQUIVALENT_URI=".$specialPropertiesSMW['_URI'].",".
                "DISPLAY_UNITS=".$specialPropertiesSMW['_UNIT'].",".
                "IMPORTED_FROM=".$specialPropertiesSMW['_IMPO'].",".
                "PROVIDES_SERVICE=".$specialPropertiesSMW['_SERV'].",".
                "ALLOWS_VALUE=".$specialPropertiesSMW['_PVAL'].",".
                "SUBPROPERTY_OF=".$specialPropertiesSMW['_SUBP'].",".
                "HAS_IMPROPER_VALUE_FOR=".$specialPropertiesSMW['_ERRP'].",".
			    "CREATOR=".$specialProperties['___CREA'][1].",".
			    "CREATION_DATE=".$specialProperties['___CREADT'][1].",".
			    "MODIFIER=".$specialProperties['___MOD'][1].",";

    // these two namespaces are required for ASK queries
    $result .= "CATEGORY=".$wgContLang->getNSText(NS_CATEGORY).",";
    $result .= "CONCEPT=".$wgContLang->getNSText(SMW_NS_CONCEPT);

    return $result;
}

/**
 * Trigger asynchronous loading operations. Usually called when TSC comes up.
 *
 * @return AjaxRespone object containing JSON encoded data.
 */
function smwf_ts_triggerAsynchronousLoading() {
    global $smwgHaloTripleStoreGraph;
    $result = array();
    $result['components'] = array();
    $result['errors'] = array();
    wfRunHooks("SMWHalo_AsynchronousLoading", array ($smwgHaloTripleStoreGraph, & $result));

    $json = json_encode($result);
    $response = new AjaxResponse($json);
    $response->setContentType( "application/json" );
    return $response;
}

/**
 * This function retrieves the derived facts of the article with the name
 * $titleName.
 *
 * @param string $titleName
 *
 * @return string
 *      The derived facts as HTML
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
                $valuesHtml[] = $dataValue->getLongHTMLText(  );
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
