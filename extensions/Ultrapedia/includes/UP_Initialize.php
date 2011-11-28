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
 * Created on 01.09.2009
 *
 * Author: Ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_ULTRAPEDIA_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

$smwgUltraPediaIP = $IP . '/extensions/Ultrapedia';
$smwgUltraPediaScriptPath = $wgScriptPath . '/extensions/Ultrapedia';
$smwgUltraPediaEnabled = true;

// internal variable to add a version number when the css and js files are retrieved
$smwgUltraPediaStyleVersion= preg_replace('/[^\d]/', '', '{{$BUILDNUMBER}}' );
if (strlen($smwgUltraPediaStyleVersion) > 0)
    $smwgUltraPediaStyleVersion= '?'.$smwgUltraPediaStyleVersion;


global $wgExtensionFunctions, $wgHooks, $wgAutoloadClasses, $smwgUltraPediaEnableLocalEdit;
$wgExtensionFunctions[] = 'smwgUltraPediaSetupExtension';
$wgHooks['LanguageGetMagic'][] = 'UPParserFunctions::languageGetMagic';
$wgHooks['BeforePageDisplay'][]='smwfUltraPediaAddHTMLHeader';

$wgAutoloadClasses['UPParserFunctions'] = $smwgUltraPediaIP . '/includes/UP_ParserFunctions.php';


//change the edit tab only in the ultrapedia context, there for the config var has to be set true
if( $smwgUltraPediaEnableLocalEdit === true){
    $wgHooks['SkinTemplateTabs'][] = 'smwfUPWPCloneEditTab';
}

function smwfUPWPCloneEditTab($obj, $content_actions) {
		// make sure that this is not a special page, and
		// that the user is allowed to edit it
		if (isset($obj->mTitle) && ($obj->mTitle->getNamespace() != NS_SPECIAL)) {
			global $wgRequest, $wgUser;
			$wp_edit_tab_text = wfMsg('edit');
			$user_can_edit = $wgUser->isAllowed('edit') && $obj->mTitle->userCan('edit');
			if (array_key_exists('edit', $content_actions)) {
				$content_actions['edit']['text'] = $user_can_edit ? 'Local Edit' : wfMsg('viewsource');
			}
			$wp_edit_tab = array(
				'class' => '',
				'text' => $wp_edit_tab_text,
				'href' => str_replace('/up/index.php', '/wp/index.php', $obj->mTitle->getLocalURL('action=edit'))
			);
			$tab_keys = array_keys($content_actions);
			$tab_values = array_values($content_actions);
			$edit_tab_location = array_search('edit', $tab_keys);
			// if there's no 'edit' tab, look for the
			// 'view source' tab instead
			if ($edit_tab_location == NULL)
				$edit_tab_location = array_search('viewsource', $tab_keys);
			// this should rarely happen, but if there was
			// no edit *or* view source tab, set the
			// location index to -1, so the tab shows up
			// near the end
			if ($edit_tab_location == NULL)
				$edit_tab_location = -1;
			array_splice($tab_keys, $edit_tab_location, 0, 'wp_edit');
			array_splice($tab_values, $edit_tab_location, 0, array($wp_edit_tab));
			$content_actions = array();
			for ($i = 0; $i < count($tab_keys); $i++)
				$content_actions[$tab_keys[$i]] = $tab_values[$i];
//			global $wgUser;
//			if (! $wgUser->isAllowed('viewedittab')) {
//				// the tab can have either of those two actions
//				unset($content_actions['edit']);
//				unset($content_actions['viewsource']);
//			}
	}
	return true; // always return true, in order not to stop MW's hook processing!
}
function smwfUltraPediaInitMessages() {
	global $smwgUltraPediaMessagesInitialized;
	if (isset($smwgUltraPediaMessagesInitialized)) return; // prevent double init

	smwfUltraPediaInitUserMessages(); // lazy init for ajax calls

	$smwgUltraPediaMessagesInitialized = true;
}
function smwfUltraPediaInitUserMessages() {
	global $wgMessageCache, $smwgUltraPediaContLang, $wgLanguageCode;
	smwfUltraPediaInitContentLanguage($wgLanguageCode);

	global $smwgUltraPediaIP, $smwgUltraPediaLang;
	if (!empty($smwgUltraPediaLang)) { return; }
	global $wgMessageCache, $wgLang;
	$smwLangClass = 'UP_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($smwgUltraPediaIP . '/languages/'. $smwLangClass . '.php')) {
		include_once( $smwgUltraPediaIP . '/languages/'. $smwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($smwLangClass)) {
		global $smwgUltraPediaContLang;
		$smwgUltraPediaLang = $smwgUltraPediaContLang;
	} else {
		$smwgUltraPediaLang = new $smwLangClass();
	}

	$wgMessageCache->addMessages($smwgUltraPediaLang->getUserMsgArray(), $wgLang->getCode());
}
function smwfUltraPediaInitContentLanguage($langcode) {
	global $smwgUltraPediaIP, $smwgUltraPediaContLang;
	if (!empty($smwgUltraPediaContLang)) { return; }

	$smwContLangClass = 'UP_Language' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($smwgUltraPediaIP . '/languages/'. $smwContLangClass . '.php')) {
		include_once( $smwgUltraPediaIP . '/languages/'. $smwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($smwContLangClass)) {
		include_once($smwgUltraPediaIP . '/languages/UP_LanguageEn.php');
		$smwContLangClass = 'UP_LanguageEn';
	}
	$smwgUltraPediaContLang = new $smwContLangClass();
}

function smwfUltraPediaGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}

/**
 * Intializes Semantic UltraPedia Extension.
 * Called from UP during initialization.
 */
function smwgUltraPediaSetupExtension() {
	global $smwgUltraPediaIP, $wgExtensionCredits;
	global $wgParser, $wgHooks, $wgAutoloadClasses;

	smwfUltraPediaInitMessages();

	// register hooks
	if( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
		$wgHooks['ParserFirstCallInit'][] = 'UPParserFunctions::registerFunctions';
	} else {
		if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
			$wgParser->_unstub();
		}
		UPParserFunctions::registerFunctions( $wgParser );
	}

	global $wgRequest;

	$action = $wgRequest->getVal('action');
	// add some AJAX calls
	if ($action == 'ajax') {
		$method_prefix = smwfUltraPediaGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch($method_prefix) {
			case '_up_' :
				require_once($smwgUltraPediaIP . '/includes/UP_AjaxAccess.php');
				break;
		}
	}

	// Register Credits
	$wgExtensionCredits['parserhook'][]= array(
	'name'=>'Semantic&nbsp;UltraPedia&nbsp;Extension', 'version'=>SMW_ULTRAPEDIA_VERSION,
			'author'=>"Ning Hu, Justin Zhang, [http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang], sponsored by [http://projecthalo.com Project Halo], [http://www.vulcan.com Vulcan Inc.]", 
			'url'=>'http://wiking.vulcan.com/dev', 
			'description' => 'Utilities for UltraPedia.');

	return true;
}

function smwfUltraPediaGetJSLanguageScripts(&$pathlng, &$userpathlng) {
	global $smwgUltraPediaIP, $wgLanguageCode, $smwgUltraPediaScriptPath,
           $wgUser, $smwgUltraPediaStyleVersion;

	// content language file
	$lng = '/scripts/Language/UP_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgUltraPediaIP . $lng)) {
			$pathlng = $smwgUltraPediaScriptPath . $lng;
		} else {
			$pathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageEn.js'.$smwgUltraPediaStyleVersion;
		}
	} else {
		$pathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageEn.js'.$smwgUltraPediaStyleVersion;
	}

	// user language file
	$lng = '/scripts/Language/UP_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgUltraPediaIP . $lng)) {
			$userpathlng = $smwgUltraPediaScriptPath . $lng;
		} else {
			$userpathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageUserEn.js'.$smwgUltraPediaStyleVersion;
		}
	} else {
		$userpathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageUserEn.js'.$smwgUltraPediaStyleVersion;
	}
}

function smwfUltraPediaAddHTMLHeader(& $out) {
    // ext library causes problems with the Halo extension.
    return true;
	// add Ultrapedia abstract tooltip
	global $wgJsMimeType, $wgStylePath, $wgScriptPath, $smwgUltraPediaScriptPath,
           $upAbstractSparql, $smwgUltraPediaStyleVersion;
	$out->addLink( array(
		'rel' => 'stylesheet',
		'type' => 'text/css',
		'media' => "screen, projection",
        'href' => $smwgUltraPediaScriptPath. '/scripts/extjs/resources/css/ext-all.css'.$smwgUltraPediaStyleVersion
	));
	$out->addScript("<script type=\"{$wgJsMimeType}\" src=\"{$wgStylePath}/common/extjs/adapter/prototype/ext-prototype-adapter.js{$smwgUltraPediaStyleVersion}\"></script>
    <script type=\"{$wgJsMimeType}\" src=\"{$smwgUltraPediaScriptPath}/scripts/extjs/ext-all.js{$smwgUltraPediaStyleVersion}\"></script>
	<script type=\"text/javascript\" src=\"{$smwgUltraPediaScriptPath}/scripts/abstractilink.js{$smwgUltraPediaStyleVersion}\"></script>
	<script type=\"{$wgJsMimeType}\">
		AjaxInternalLinks.baseUrl = \"{$wgScriptPath}\";
		AjaxInternalLinks.sparql = \"{$upAbstractSparql}\";
	</script>\n");
	
	return true;
}

?>
