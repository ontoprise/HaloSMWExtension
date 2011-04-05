<?php

/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Collaboration-Extension.
 *
 *   The Collaboration-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Collaboration-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Script extension which manages the including of common JS script libraries.
 *
 * @author Kai K�hn
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Script Manager extension. It is not a valid entry point.\n" );
}

define('SCM_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

global $wgExtensionFunctions, $wgScriptPath;;
$smgSMPath = $wgScriptPath . '/extensions/ScriptManager';

$wgExtensionFunctions[] = 'smgSetupExtension';

function smgSetupExtension() {
	global $wgHooks, $wgExtensionCredits;
	$wgHooks['BeforePageDisplay'][]='smfAddHTMLHeader';
	
	// Register Credits
    $wgExtensionCredits['parserhook'][]= array('name'=>'ScriptManager&nbsp;Extension', 'version'=>SCM_VERSION,
            'author'=>"Kai&nbsp;K&uuml;hn. Owned by [http://www.ontoprise.de ontoprise GmbH].", 
            'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:Script_Manager_Extension',
            'description' => 'Organizes javascript libraries.');
}

function smfAddHTMLHeader(& $out) {
	global $smgJSLibs, $smgSMPath, $smwgDeployVersion;
    static $outputSend;
    if (isset($outputSend) || !is_array($smgJSLibs)) return true;
	$smgJSLibs = array_unique($smgJSLibs);
	$smgJSLibs = smfSortScripts($smgJSLibs);
	foreach($smgJSLibs as $lib_id) {

		switch($lib_id) {
			case 'prototype':
				$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/prototype.js\" id=\"Prototype_script_inclusion\"></script>");
				break;
			case 'jquery':
				if ( method_exists( 'OutputPage', 'includeJQuery' ) ) {
					$out->includeJQuery();
					//make it not conflicting with other libraries like prototype
					$out->addScript("<script type=\"text/javascript\">var \$jq = jQuery.noConflict();</script>");
				} else {
					$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/jquery.js\"></script>");
					global $smwgJQueryIncluded;
					$smwgJQueryIncluded = true;
				}
				break;
			case 'qtip':
                if (isset($smwgDeployVersion) && $smwgDeployVersion !== false)
					$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/qTip/jquery.qtip-1.0.0-rc3.min.js\"></script>");
                else
					$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/qTip/jquery.qtip-1.0.0-rc3.js\"></script>");
				break;
			case 'json':
                if (isset($smwgDeployVersion) && $smwgDeployVersion !== false)
					$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/json2.min.js\"></script>");
                else
					$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/json2.js\"></script>");
				break;
			case 'fancybox':
				$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/fancybox/jquery.fancybox-1.3.1.js\"></script>");
				$out->addStyle($smgSMPath."/scripts/fancybox/jquery.fancybox-1.3.1.css", 'screen, projection');
				break;
			case 'ext':
				$out->addLink($smgSMPath.'/scripts/extjs/resources/css/ext-all.css', 'screen, projection');
				$out->addScript('<script type="text/javascript" src="' . $smgSMPath . '/scripts/extjs/adapter/ext/ext-base.js"></script>');
				$out->addScript('<script type="text/javascript" src="' . $smgSMPath . '/scripts/extjs/ext-all.js"></script>');
				break;
						
		}
	}
    $outputSend = true;
	return true;
}

function smfSortScripts($smgJSLibs) {
	$newList = array();
	if (in_array('jquery', $smgJSLibs)) {
		$newList[] = 'jquery';
	}
	foreach($smgJSLibs as $lib) {
		if ($lib != 'jquery') {
			$newList[] = $lib;
		}
	}
	return $newList;
}


/**
 * MW enables multiple extensions. Different extensions may use same css in js frameworks.
 * In order to avoid css conflict, this function will merge same css links (based on filename pattern)
 *
 * Non-framework css files in different extensions may have the same filename,
 * this may cause HTML rendering bugs
 *
 * This function will be called in SkinTemplate outputPage, set 'headlinks'
 *
 * @param string $headLinks
 * @return headlinks merged
 */
function smfMergeHeadLinks( $headLinks ) {
	// apply common link pattern, <link ... href="LINK_FILE" ... />
	preg_match_all( '/\<\s*link\b[^\>]+\bhref\s*=\s*[\'"]([^\'"]*)[\'"][^\>]*\/\>/i', $headLinks, $links, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
	$newlink = ''; // new head link string
	$offset = 0; // offset in $headLinks
	$ls = array( ); // a keyword list to store css file strings
	foreach ( $links as $l ) {
		// append head string outside link patterns
		$newlink .= substr( $headLinks, $offset, $l[0][1] - $offset );
		// calculate new offset
		$offset = $l[0][1] + strlen( $l[0][0] );
		// get file keyword (file name only), e.g. 'extensions/EA/css/jquery.css', the keyword is 'jquery.css'
		$start = strrpos( $l[1][0], '/' );
		$key = substr( $l[1][0], ($start === false ? -1 : $start) + 1 );
		// if the css keyword is first used, append to head link, otherwise, omit it
		if ( !isset( $ls[$key] ) ) {
			$newlink .= $l[0][0];
			$ls[$key] = true;
		}
	}
	// append the rest head string
	$newlink .= substr( $headLinks, $offset );

	return $newlink;
}

/**
 * MW enables multiple extensions. Different extensions may use same js frameworks.
 * In order to avoid js conflict, this function will merge same js srces (based on filename pattern)
 * Also, it renders multiple js frameworks in proper sequence.
 *
 * Non-framework js files in different extensions may have the same filename,
 * this may cause HTML js bugs
 *
 * This function will be called in SkinTemplate outputPage, set 'headscripts'
 *
 * @param string $scripts
 * @return scripts merged
 */
function smfMergeHeadScripts( $scripts ) {
	// split head scripts with pattern '</script>', which will always be a script end mark
	$sc = preg_split( '/\<\s*\/script\s*\>/i', $scripts );
	$newscript = ''; // new head script string
	$ls = array( ); // a keyword list to store js file strings
	// registered js frameworks, jquery, jqueryui, prototype, extjs, yui, ...
	$js_frameworks = array(
		'jquery' => false,
		'jqueryui' => false,
		'jqueryfancybox' => false,
		'prototype' => false,
		'yui' => false,
		'extjs' => false
	);
	foreach ( $sc as $s ) {
		// test if current script piece is in common script src pattern, <script ... src="JS_FILE" ... >
		if ( preg_match( '/\<\s*script\b[^\>]+\bsrc\s*=\s*[\'"]([^\'"]*)[\'"][^\>]*\>/i', $s, $script, PREG_OFFSET_CAPTURE ) )
		{
			// append head string outside script patterns
			$newscript .= substr( $s, 0, $script[0][1] );
			// get file keyword (file name only)
			// e.g. 'extensions/EA/scripts/jquery.js', the keyword is 'jquery.js'
			$start = strrpos( $script[1][0], '/' );
			$key = substr( $script[1][0], ($start === false ? -1 : $start) + 1 );
			// judge common js frameworks with filename patterns
			if ( preg_match( '/\bjquery(-[\d]+(\.[\d]+)*)?(\.min)?\.js\b/i', $key ) ) {
				// jquery, jquery.js / jquery-1.3.2.js / jquery-1.3.2.min.js / jquery.min.js
				$js_frameworks['jquery'] = true;
			} else if ( preg_match( '/\bjquery-ui(-[\d]+(\.[\d]+)*)?(\.min)?\.js\b/i', $key ) ) {
				// jquery-ui.js / jquery-ui-1.7.2.js / jquery-ui-1.7.2.min.js
				$js_frameworks['jqueryui'] = true;
			} else if ( preg_match( '/\bjquery.fancybox(-[\d]+(\.[\d]+)*)?(\.min)?\.js\b/i', $key ) ) {
				// jquery's fancybox plugin
				if( $js_frameworks['jquery'] ) {
					// jquery has to be included before
					$js_frameworks['jqueryfancybox'] = true;
				} else {
					// otherwise, just append js piece
					$newscript .= $s . '</script>';
				}
			} else if ( preg_match( '/\bprototype(-[\d]+(\.[\d]+)*)?(\.min)?\.js\b/i', $key ) ) {
				// prototype, prototype.js / prototype-1.6.0.js / prototype-1.6.0.min.js
				$js_frameworks['prototype'] = true;
			} else if ( preg_match( '/\bext-[^\.]+\.js\b/i', $key ) ) {
				// extjs, ext-all.js / ext-base.js / ext-jquery-adapter.js / ...
				$js_frameworks['extjs'] = true;
			} else {
				// if the js keyword is first used, append to head script, otherwise, omit it
				if ( !isset( $ls[$key] ) ) {
					$newscript .= substr( $s, $script[0][1] ) . '</script>';
					$ls[$key] = true;
				}
			}
		} else {
			// just append js piece
			$newscript .= $s . '</script>';
		}
	}
	$newscript = substr( $newscript, 0, strlen( $newscript ) - strlen( '</script>' ) );

	// generate framework scripts
	global $wgJsMimeType, $smgSMPath;
	$frameworks = '';
	if ( $js_frameworks['jquery'] ) {
		// jquery with noConflict flag
		$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/jquery-1.3.2.min.js\"></script>\n";
		$frameworks .= "<script type=\"{$wgJsMimeType}\">jQuery.noConflict();jQuery.noConflict=function( deep ) {return jQuery;};</script>\n";
	}
	if ( $js_frameworks['jqueryui'] ) {
		// jquery ui
		$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/jquery-ui-1.7.2.custom.min.js\"></script>\n";
	}
	if ( $js_frameworks['jqueryfancybox'] ) {
		// jQuery's fancybox plugin
		$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/fancybox/jquery.fancybox-1.3.1.js\"></script>\n";
	}
	if ( $js_frameworks['prototype'] ) {
		// prototype
		$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/prototype.js\"></script>\n";
	}
	if ( $js_frameworks['extjs'] ) {
		// extjs with multiple adapter
		if ( $js_frameworks['prototype'] ) {
			$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/extjs/adapter/prototype/ext-prototype-adapter.js\"></script>\n";
		} else if ( $js_frameworks['yui'] ) {
			$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/extjs/adapter/yui/ext-yui-adapter.js\"></script>\n";
		} else if ( $js_frameworks['jquery'] ) {
			$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/extjs/adapter/jquery/ext-jquery-adapter.js\"></script>\n";
		} else {
			$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/extjs/adapter/ext/ext-base.js\"></script>\n";
		}
		$frameworks .= "<script type=\"{$wgJsMimeType}\" src=\"{$smgSMPath}/scripts/extjs/ext-all.js\"></script>\n";
	}
	// add js framework to top
	return $frameworks . $newscript;
}
