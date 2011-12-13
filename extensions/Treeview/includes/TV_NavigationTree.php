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
 * @ingroup TreeView
 *
 * This file defines the class TVNavigationTree which provides the navigation tree
 * for OntoSkin.
 *
 * @author Thomas Schweitzer
 * Date: 13.12.2011
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * This class provides the navigation tree for OntoSkin.
 *
 * @author Thomas Schweitzer
 *
 */
class TVNavigationTree  {

	//--- Constants ---
	//	const XY= 0;		// the result has been added since the last time

	//--- Private fields ---
	private $mXY;    		//string: comment

	/**
	 * Constructor for  TVNavigationTree
	 *
	 * @param type $param
	 * 		Name of the notification
	 */
	function __construct() {
		//		$this->mXY = $xy;
	}


	//--- getter/setter ---
	//	public function getXY()           {return $this->mXY;}

	//	public function setXY($xy)               {$this->mXY = $xy;}

	//--- Public methods ---

	/**
	 * Echos the HTML code for the navigation tree if the system is not in
	 * edit mode.
	 */
	public static function showNavigationTree() {
		global $wgRequest, $wgTitle;
		//when in edit mode, don't insert tree into ontoskin
		if ($wgRequest->getVal('action') != 'edit' &&
		$wgRequest->getVal('action') != 'formedit' &&
		!($wgTitle->getNamespace()==NS_SPECIAL &&
		$wgTitle->getText() == 'FormEdit')) {
			echo self::generateNavigationTree();
		}
		return true;
	}

	//--- Private methods ---

	private static function generateNavigationTree() {
		global $wgUser,$wgTitle,$wgParser;
		
		// Check if the article "MediaWiki:NavTree" exists
		$navTitle = Title::newFromText('NavTree', NS_MEDIAWIKI);
		if (!$navTitle->exists()) {
			return "";	
		}
		
		// Create a parser
		$psr = (is_object($wgParser))
				? $wgParser
				: new Parser();
		$opt = ParserOptions::newFromUser($wgUser);

		// Check if content is cached
		$navArticleName = $wgTitle->getFullText()."_NavTree";
		$navArticleTitle = Title::newFromText($navArticleName);
		$navArticle = new Article($navArticleTitle);

		$parserCache = ParserCache::singleton();
		$cachedContent = $parserCache->get($navArticle, $opt);

		global $wgRequest;
		$nav = new Article($navTitle);
		$ts = $nav->getTimestamp();

		$purge = ($wgRequest->getVal('action') == 'purge');
		if ($cachedContent && $ts*1 > $cachedContent->getCacheTime()*1) {
			// Mediawiki:NavTree was changed => purge the cache of the tree
			$purge = true;
		}
		if ($cachedContent && !$purge) {
			$html = $cachedContent->getText();
		} else {
			$out = $psr->parse($nav->fetchContent(0,false,false),$wgTitle,$opt,true,true);
			$html = $out->getText();
			$parserCache->save($out, $navArticle, $opt);
		}

		$groups = $wgUser->getGroups();
		foreach($groups as $g) {
			$title = Title::newFromText('NavTree_'.$g, NS_MEDIAWIKI);
			if ($title->exists()) {
				$nav = new Article($title);
				$out = $psr->parse($nav->fetchContent(0,false,false),$wgTitle,$opt,true,true);
				$html .= '<br/>'.$out->getText();
			}
		}
		
		global $wgOut;
		$wgOut->addModules('ext.TreeView.tree');
		
		return $html;

	}
}
/**
 * Provides functionality to get partial trees in treeview
 * by an ajax request. One level only starting at a certain
 * node (given in parameter start) is fetched. Therefore
 * some of the parameters for the generic generateTree()
 * function are not needed or can be set to a certain value.
 *
 * @global Parser $wgParser
 * @param string $input parameter string i.e. p=propname&c=catname
 * @return string $value (empty)
 */
function smw_treeview_getTree($input) {
	global $wgParser;

	// fix: do not use parse_str here as this leads to errors if a value contains a decoded +
	$req = array();
	$args = explode('&', $input);
	foreach ($args as $tuple) {
		$kv = explode('=', $tuple);
		if (count($kv) == 2) $req[$kv[0]] = $kv[1];
	}

	$initOnload = (isset($req['i'])) ? true : false;
	$relation = (isset($req['p'])) ? 'property='.urldecode($req['p']) : NULL;
	$category = (isset($req['c'])) ? 'category='.urldecode($req['c']) : NULL;
	$start = (isset($req['s'])) ? 'start='.urldecode($req['s']) : NULL;
	$display = (isset($req['d'])) ? 'display='.urldecode($req['d']) : NULL;
	$linkTo = (isset($req['l'])) ? 'linkto='.urldecode($req['l']) : NULL;
	$condition = (isset($req['q'])) ? 'condition='.$req['q'] : NULL;
	$orderbyProperty = (isset($req['b'])) ? 'orderbyProperty='.urldecode($req['b']) : NULL;
	// checkNode only if dynamic expansion is used.
	$checkNode = (!isset($req['z']) && isset($req['n'])) ? 'checkNode=1' : NULL;

	// the following parameter depend on initOnload,
	// if this is not set, these are not needed because an dynamic expansion will fetch
	// the next level only. If the whole tree (or part of it) is loaded for the first time
	// we need to check these parameter as well and treat them accordingly

	// fetch one depth only if ajax expansion
	$maxDepth = ($initOnload) ? (isset($req['m']) ? 'maxDepth='.$req['m'] : NULL ) : 'maxDepth=1';
	// these are not needed for the next level on dynamic expansion
	$urlparams = ($initOnload && isset($req['u'])) ? 'urlparams='.$req['u'] : NULL;
	$redirectPage = ($initOnload && isset($req['r'])) ? 'redirectPage='.urldecode($req['r']) : NULL;
	$level = ($initOnload && isset($req['v'])) ? 'level='.$req['v'] : NULL;
	$opento = ($initOnload && isset($req['o'])) ? 'opento='.urldecode($req['o']) : NULL;
	$iolStatic = ($initOnload && isset($req['z'])) ? 'iolStatic=1' : NULL;

	$treeGenerator = new TreeGenerator;
	$treeGenerator->setJson();
	if (!$initOnload) $treeGenerator->setLoadNextLevel();
	if (isset($req['j'])) $treeGenerator->setUseTsc();
	$res= $treeGenerator->generateTree($wgParser, $relation, $category,
	$start, $display, $linkTo, $maxDepth,
	$redirectPage, $level, $condition, $urlparams,
	$opento, $iolStatic, $orderbyProperty, $checkNode);

	$return['treelist'] = $res;
	$return['result'] = 'success';
	if (isset($req['t'])) $return['token']= $req['t'];

	// output correct header
	$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
	(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));
	echo json_encode($return);

	return "";
}

