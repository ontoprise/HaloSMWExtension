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

/**
 * @file
 * @ingroup SemanticGardening
 * 
 * @defgroup SemanticGardening Semantic Gardening extension
 * 
 * @defgroup SemanticGardeningBotsQ
 * @ingroup SemanticGardening
 * 
 * @author Kai K�hn
 * 
 */
if ( !defined( 'SMW_HALO_VERSION' ) )
    die("The Semantic Gardening extension requires the Halo extension to be installed.");

define('SGA_GARDENING_EXTENSION_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

// register initialize function
global $wgExtensionFunctions, $sgagIP, $IP;
$wgExtensionFunctions[] = 'sgagGardeningSetupExtension';
$sgagIP = $IP."/extensions/SemanticGardening";
$sgagScriptPath = $wgScriptPath . '/extensions/SemanticGardening';

$wgExtensionCredits['other'][] = array(
		'name' => 'Semantic Gardening extension',
		'version'=> SGA_GARDENING_EXTENSION_VERSION,
		'author'=>"Maintained by [http://smwplus.com ontoprise GmbH].", 
		'url' => 'http://smwforum.ontoprise.com/smwforum/index.php/Help:Semantic_Gardening_Extension',
		'description' => 'Gardening keeps your wiki clean and consistent and is a basis for '.
			'several other features like term import, webservice import or semantic notifications.',
);

global $smwgSGAStyleVersion;
$smwgSGAStyleVersion = preg_replace('/[^\d]/', '', '{{$BUILDNUMBER}}' );
if (strlen($smwgSGAStyleVersion) > 0)
    $smwgSGAStyleVersion = '?'.$smwgSGAStyleVersion;

function sgagGardeningSetupExtension() {

	global $wgAutoloadClasses, $wgHooks, $sgagIP;



	$wgHooks['BeforePageDisplay'][]='sgafGAAddHTMLHeader';
	$wgHooks['BeforePageDisplay'][]='sgaFWAddHTMLHeader';
	$wgHooks['ArticleSaveComplete'][] = 'sgafHaloSaveHook'; // gardening update (SMW does the storing)
	$wgHooks['ArticleDelete'][] = 'sgafHaloPreDeleteHook';
	$wgHooks['ArticleSave'][] = 'sgafHaloPreSaveHook';

	$wgAutoloadClasses['SMWSuggestStatistics'] = $sgagIP . '/specials/FindWork/SGA_SuggestStatistics.php';
	$wgAutoloadClasses['SGAGardening'] = $sgagIP . '/specials/Gardening/SGA_Gardening.php';
	$wgAutoloadClasses['SGAGardeningTableResultPrinter'] = $sgagIP . '/includes/SGA_QP_GardeningTable.php';
   
    
	global $smwgResultFormats;
	$smwgResultFormats['smwtable'] = 'SMWTableResultPrinter'; // keep old printer
	$smwgResultFormats['table'] = 'SGAGardeningTableResultPrinter'; // overwrite SMW printer
	$smwgResultFormats['broadtable'] = 'SGAGardeningTableResultPrinter'; // overwrite SMW printer

	global $sgagLocalGardening, $wgJobClasses, $sgagIP;
	//XXX: deactivated because of Performance
    if ($sgagLocalGardening == true){        
    	require_once($sgagIP . '/includes/jobs/SGA_LocalGardeningJob.php');
    	$wgJobClasses['SMW_LocalGardeningJob'] = 'SMW_LocalGardeningJob';
    }

	global $wgRequest;
	$action = $wgRequest->getVal('action');
	if ($action != 'ajax') {
		sgafGardeningInitMessages();
	}

	if ($action == 'ajax') {
		$method_prefix = sgafGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch($method_prefix) {

			case '_ga_' :
				require_once($sgagIP . '/includes/SGA_GardeningAjaxAccess.php');
				break;
					
		}
	} else {
		global $wgSpecialPages, $wgSpecialPageGroups;
		$wgAutoloadClasses['SGAGardening'] = $sgagIP . '/specials/Gardening/SGA_Gardening.php';
		$wgSpecialPages['Gardening'] = array('SGAGardening');
		$wgSpecialPageGroups['Gardening'] = 'smwplus_group';

		$wgSpecialPages['GardeningLog'] = array('SpecialPage','GardeningLog', '', true, 'smwfDoSpecialLogPage', $sgagIP . '/specials/GardeningLog/SGA_GardeningLogPage.php');
		$wgSpecialPageGroups['GardeningLog'] = 'smwplus_group';

		$wgSpecialPages['FindWork'] = array('SpecialPage','FindWork', '', true, 'smwfDoSpecialFindWorkPage', $sgagIP . '/specials/FindWork/SGA_FindWork.php');
		$wgSpecialPageGroups['FindWork'] = 'smwplus_group';

	}
	//XXX: deactivated because of Performance
	//require_once($sgagIP . '/includes/jobs/SGA_LocalGardeningJob.php');
	
	sgafRegisterResourceLoaderModules();
	return true;
}


function sgafGardeningInitMessages() {
	global $sgagMessagesInitialized;
	if (!$sgagMessagesInitialized) {
		wfGAInitUserMessages();
		wfGAInitContentMessages();
		$sgagMessagesInitialized = true;
	}
}

/**
 * Registers ACL messages.
 */
function wfGAInitUserMessages() {

	global $wgMessageCache, $wgLang, $sgagIP;

	$usLangClass = 'SGA_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($sgagIP.'/languages/'. $usLangClass . '.php')) {
		include_once($sgagIP.'/languages/'. $usLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($usLangClass)) {
		include_once('extensions/SemanticGardening/languages/SGA_LanguageEn.php' );
		$aclgHaloLang = new SGA_LanguageEn();
	} else {
		$aclgHaloLang = new $usLangClass();
	}
	$wgMessageCache->addMessages($aclgHaloLang->userMessages, $wgLang->getCode());


}

function wfGAInitContentMessages() {
	global $wgMessageCache, $wgLanguageCode, $sgagIP;
	$usLangClass = 'SGA_Language' . str_replace( '-', '_', ucfirst( $wgLanguageCode) );
	if (file_exists($sgagIP.'/languages/'. $usLangClass . '.php')) {
		include_once($sgagIP.'/languages/'. $usLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($usLangClass)) {
		include_once($sgagIP.'/languages/SGA_LanguageEn.php' );
		$aclgHaloLang = new SGA_LanguageEn();
	} else {
		$aclgHaloLang = new $usLangClass();
	}

	$wgMessageCache->addMessages($aclgHaloLang->contentMessages, $wgLanguageCode);

}


/**
 * Called *before* an article is saved. Used for LocalGardening
 *
 * @param Article $article
 * @param User $user
 * @param string $text
 * @param string $summary
 * @param bool $minor
 * @param bool $watch
 * @param unknown_type $sectionanchor
 * @param int $flags
 */
function sgafHaloPreSaveHook(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {
	// -- LocalGardening --

	global $sgagLocalGardening, $sgagIP;
	if (isset($sgagLocalGardening) && $sgagLocalGardening == true && (($flags & EDIT_FORCE_BOT) === 0)) {
		require_once($sgagIP . '/includes/jobs/SGA_LocalGardeningJob.php');
		$gard_jobs[] = new SMW_LocalGardeningJob($article->getTitle(), "save");
		Job :: batchInsert($gard_jobs);
	}
	return true;
	// --------------------
}


/**
 * Called *before* an article gets deleted.
 *
 * @param Article $article
 * @param User $user
 * @param string $reason
 * @return unknown
 */
function sgafHaloPreDeleteHook(&$article, &$user, &$reason) {
	// -- LocalGardening --
	global $sgagLocalGardening, $sgagIP;
	if (isset($sgagLocalGardening) && $sgagLocalGardening == true) {
		require_once($sgagIP . '/includes/jobs/SGA_LocalGardeningJob.php');
		$gard_jobs[] = new SMW_LocalGardeningJob($article->getTitle(), "remove");
		Job :: batchInsert($gard_jobs);
	}
	return true;
}
/**
 *  This method will be called after an article is saved
 *  and stores the semantic properties in the database. One
 *  could consider creating an object for deferred saving
 *  as used in other places of MediaWiki.
 *  This hook extends SMW's smwfSaveHook insofar that it
 *  updates dependent properties or individuals when a type
 *  or property gets changed.
 */
function sgafHaloSaveHook(&$article, &$user, $text) {
	global $sgagIP;
	include_once($sgagIP . '/includes/SGA_GardeningIssues.php');

	$title=$article->getTitle();
	SGAGardeningIssuesAccess::getGardeningIssuesAccess()->setGardeningIssueToModified($title);

	return true; // always return true, in order not to stop MW's hook processing!
}

// Gardening scripts callback
// includes necessary script and css files.
function sgafGAAddHTMLHeader(&$out) {
	global $wgTitle, $wgOut;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	$wgOut->addModules('ext.semanticgardening.gardening');
	return true;
}

// FindWork page callback
// includes necessary script and css files.
function sgaFWAddHTMLHeader(& $out) {
	global $wgTitle, $wgOut;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	$wgOut->addModules('ext.semanticgardening.findwork');
	return true;
}

function sgafGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}


/**
 * Initialize all resource loader modules for SemanticGardening
 */
function sgafRegisterResourceLoaderModules() {
	global $wgResourceModules, $sgagIP, $sgagScriptPath;
	
	$moduleTemplate = array(
		'localBasePath' => $sgagIP,
		'remoteBasePath' => $sgagScriptPath,
		'group' => 'ext.semanticgardening'
	);

	// Scripts and styles gardening
	$wgResourceModules['ext.semanticgardening.gardening'] = $moduleTemplate + array(
		'scripts' => array(
				'scripts/gardening.js'
				),
		'styles' => array(
				'skins/gardening.css',
				'skins/gardeningLog.css'
				),
		'dependencies' => array(
				'ext.ScriptManager.prototype',
				//'ext.smwhalo.general'
				)
				
	);
	
	// Scripts and styles findwork
	$wgResourceModules['ext.semanticgardening.findwork'] = $moduleTemplate + array(
		'scripts' => array(
				'scripts/findwork.js'
				),
		'styles' => array(
				'skins/findwork.css'
				),
		'dependencies' => array(
				'ext.ScriptManager.prototype'
				)
				
	);
	
}
