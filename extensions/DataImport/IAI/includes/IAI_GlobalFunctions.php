<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Interwiki-Article-Import module (IAI) of the 
*  Data-Import-Extension.
*
*   The Data-Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data-Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
  * @ingroup DIInterWikiArticleImport
  * 
  * This file contains global functions that are called from the IAI-module.
 *
 * @author Thomas Schweitzer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file is part of the IAI module. It is not a valid entry point.\n" );
}

require_once('IAI_ImportBot.php');

$iaigUpdateWithBot = array();
$iaigLog = null;
$iaigStartTime = 0;


/**
 * Switch on Interwiki-Article-Import. This function must be called in
 * LocalSettings.php after IAI_Initialize.php was included and default values
 * that are defined there have been modified.
 * For readability, this is the only global function that does not adhere to the
 * naming conventions.
 *
 * This function installs the extension, sets up all autoloading, special pages
 * etc.
 */
function enableIAI() {
iaifStartLog("enableIAI");

    global $iaigIP, $wgExtensionFunctions, $wgAutoloadClasses, $wgSpecialPages, 
           $wgSpecialPageGroups, $wgHooks, $wgExtensionMessagesFiles, 
           $wgJobClasses, $wgExtensionAliasesFiles;

    $wgExtensionFunctions[] = 'iaifSetupExtension';
    $wgExtensionMessagesFiles['IAI'] = $iaigIP . '/languages/IAI_Messages.php'; // register messages (requires MW=>1.11)

    // Register special pages aliases file
//    $wgExtensionAliasesFiles['IAI'] = iaigIP . '/languages/IAI_Aliases.php';

    ///// Set up autoloading; essentially all classes should be autoloaded!
    $wgAutoloadClasses['IAIArticleImporter'] = $iaigIP . '/includes/IAI_ArticleImporter.php';

    //--- Autoloading for exception classes ---
    $wgAutoloadClasses['IAIException']        = $iaigIP . '/exceptions/IAI_Exception.php';

iaifEndLog("enableIAI");
    return true;
}

/**
 * Do the actual initialisation of the extension. This is just a delayed init that
 * makes sure MediaWiki is set up properly before we add our stuff.
 *
 * The main things this function does are: register all hooks, set up extension
 * credits, and init some globals that are not for configuration settings.
 */
function iaifSetupExtension() {
iaifStartLog("enableIAI");
	
	wfProfileIn('iaifSetupExtension');
    global $iaigIP, $wgHooks, $wgParser, $wgExtensionCredits,
    $wgLanguageCode, $wgVersion, $wgRequest, $wgContLang;

    //--- Register hooks ---
    global $wgHooks, $iaigUpdateDependenciesAfterAPIedit;
    
    if ($iaigUpdateDependenciesAfterAPIedit === true) {
		$wgHooks['APIEditBeforeSave'][] = 'iaifAPIEditBeforeSave';
	    $wgHooks['ArticleSaveComplete'][]  = 'iaifArticleSaveComplete';
    }
    	
	//--- Load extension messages ---
    wfLoadExtensionMessages('IAI');
    
    //--- Register specials pages ---
    global $wgSpecialPages, $wgSpecialPageGroups;
//    $wgSpecialPages['IAI']      = array('IAISpecial');
//    $wgSpecialPageGroups['IAI'] = 'iai_group';

    //--- credits (see "Special:Version") ---
    $wgExtensionCredits['other'][]= array(
        'name'=>'IAI',
        'version'=>IAI_VERSION,
        'author'=>"Thomas Schweitzer. Owned by [http://www.ontoprise.de ontoprise GmbH].",
        'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:Data_Import_Extension',
        'description' => 'Import articles from other Mediawikis.');

    wfProfileOut('iaifSetupExtension');
iaifEndLog("enableIAI");
    return true;
    
}


//call this funtion in LocalSettings.php
//in order to initialize the Wikipedia
//Ultrapedia Merger
function enableWUM(){
iaifStartLog("enableWUM");
	global $iaigIP;
	require_once($iaigIP."/WUM/WUM_MergeController.php");
iaifEndLog("enableWUM");
	
}

/**********************************************/
/***** namespace settings                 *****/
/**********************************************/

/**
 * Init the additional namespaces used by IAI. The
 * parameter denotes the least unused even namespace ID that is
 * greater or equal to 100.
 */
function iaifInitNamespaces() {
iaifStartLog("iaifInitNamespaces");
	
    wfProfileIn('iaifInitNamespaces');
	global $iaigNamespaceIndex, $wgExtraNamespaces, $wgNamespaceAliases,
    $wgNamespacesWithSubpages, $wgLanguageCode, $iaigContLang;

    if (!isset($iaigNamespaceIndex)) {
        $iaigNamespaceIndex = 400;
    }

    define('IAI_NS_IAI',       $iaigNamespaceIndex);
    define('IAI_NS_IAI_TALK',  $iaigNamespaceIndex+1);
    
    if (defined('SMW_VERSION')) {
    	global $smwgNamespacesWithSemanticLinks;
    	$smwgNamespacesWithSemanticLinks[IAI_NS_IAI] = true;
    }

    iaifInitContentLanguage($wgLanguageCode);

    // Register namespace identifiers
    if (!is_array($wgExtraNamespaces)) {
        $wgExtraNamespaces=array();
    }
    $namespaces = $iaigContLang->getNamespaces();
    $namespacealiases = $iaigContLang->getNamespaceAliases();
    $wgExtraNamespaces = $wgExtraNamespaces + $namespaces;
    $wgNamespaceAliases = $wgNamespaceAliases + $namespacealiases;

    wfProfileOut('iaifInitNamespaces');
iaifEndLog("iaifInitNamespaces");
    
}


/**********************************************/
/***** language settings                  *****/
/**********************************************/

/**
 * Initialise a global language object for content language. This
 * must happen early on, even before user language is known, to
 * determine labels for additional namespaces. In contrast, messages
 * can be initialised much later when they are actually needed.
 */
function iaifInitContentLanguage($langcode) {
iaifStartLog("iaifInitContentLanguage");
	
    global $iaigIP, $iaigContLang;
    if (!empty($iaigContLang)) {
        return;
    }
    wfProfileIn('iaifInitContentLanguage');

    $iaiContLangFile = 'IAI_Language' . str_replace( '-', '_', ucfirst( $langcode ) );
    $iaiContLangClass = 'IAILanguage' . str_replace( '-', '_', ucfirst( $langcode ) );
    if (file_exists($iaigIP . '/languages/'. $iaiContLangFile . '.php')) {
        include_once( $iaigIP . '/languages/'. $iaiContLangFile . '.php' );
    }

    // fallback if language not supported
    if ( !class_exists($iaiContLangClass)) {
        include_once($iaigIP . '/languages/IAI_LanguageEn.php');
        $iaiContLangClass = 'IAILanguageEn';
    }
    $iaigContLang = new $iaiContLangClass();

    wfProfileOut('iaifInitContentLanguage');
iaifEndLog("iaifInitContentLanguage");
    
}

/**
 * This function is called when an article is modified via the Mediawiki API.
 * The article's name is stored for later update with the IAI bot 
 * (see iaifArticleSaveComplete).
 *
 * @param EditPage $editPage
 * @param string $text
 * @param array $resultArr
 * @return bool true
 */
function iaifAPIEditBeforeSave(&$editPage, $text, &$resultArr) {
iaifStartLog("iaifAPIEditBeforeSave");
	
	global $iaigIP;
	
	global $iaigUpdateWithBot;

	$t = $editPage->getArticle()->getTitle()->getFullText();
	$iaigUpdateWithBot[] = $t;
	
	global $iaigLog, $iaigStartTime;
	fprintf($iaigLog, "iaifAPIEditBeforeSave: %f (ms)\n", (microtime(true) - $iaigStartTime)/1000);
iaifEndLog("iaifAPIEditBeforeSave");
	
	return true;
}

/**
 * This method is called, after an article has been saved and if import after
 * modification via Mediawiki API is enabled ($iaigUpdateDependenciesAfterAPIedit). 
 *
 * @param Article $article
 * @param User $user
 * @param strinf $text
 * @return true
 */
function iaifArticleSaveComplete(&$article, &$user, $text) {
iaifStartLog("iaifArticleSaveComplete");
	
	global $iaigUpdateWithBot, $iaigIP;
	
	$t = $article->getTitle()->getFullText();
	$k = array_search($t, $iaigUpdateWithBot);
	if ($k !== false) {
		GardeningBot::runBot('iai_importbot', "article=$t");
		unset($iaigUpdateWithBot[$k]);
	}

	global $iaigLog, $iaigStartTime;
	fprintf($iaigLog, "iaifArticleSaveComplete: %f (ms)\n", (microtime(true) - $iaigStartTime)/1000);
iaifEndLog("iaifArticleSaveComplete");
	
	return true;
}

function iaifStartLog($function) {
	global $iaigIP, $iaigLog, $iaigStartTime;
	if ($iaigLog == null) {
		$iaigLog = fopen("$iaigIP/IAI.log", "a");
		$iaigStartTime = microtime(true);
	}
	fprintf($iaigLog, "(%f) Entering function $function.\n", (microtime(true)-$iaigStartTime)/1000);
}

function iaifLog($msg) {
	global $iaigLog, $iaigStartTime;
	fprintf($iaigLog, "(%f) %s\n", (microtime(true)-$iaigStartTime)/1000, $msg);
	fflush($iaigLog);
	
}

function iaifEndLog($function) {
	global $iaigLog, $iaigStartTime;
	fprintf($iaigLog, "(%f) Leaving function $function.\n", (microtime(true)-$iaigStartTime)/1000);
	fflush($iaigLog);
}

