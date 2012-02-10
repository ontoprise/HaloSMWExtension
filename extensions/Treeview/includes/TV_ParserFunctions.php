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
 * This file defines the parser functions of the treeview extension in the class
 * TVParserFunctions.
 * 
 * @author Thomas Schweitzer
 * Date: 02.12.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

/**
 * This class defines the parser functions of the Treeview extension.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TVParserFunctions  {
	
	//--- Constants ---
	// The default theme of the tree
	const DEFAULT_THEME = 'default';
	
	// Identifier for a special label in the tree. The rest of the line must
	// be a valid JSON string
	const GENERATE_TREE_JSON = 'generateTreeJSON:';
	
	const HTML_FOR_TREE = <<<HTML
<div id="wrapper_for_{treeID}" class="tvTreeWrapper" {dimensions}>
	<!-- This span contains the input field for the filter and the apply button. -->
	<div id="{treeID}_filter_wrapper" class="tvTreeFilterWrapper" style="display:none">
		<table style="width:100%">
			<tr>
				<td>{{tv_filter}}</td>
				<!-- Do not insert linebreaks. Otherwise MW will insert <p> tags -->
				<td style="width:100%">	<input id="{treeID}_filter_input" class="tvFilterInput" style="width:95%" type="text" title="{{tv_filter_help}}" /> </td>
				<td> <button id="{treeID}_filter_apply" type="button">{{tv_filter_apply}} </button> </td>
			</tr>
		</table>	
		<hr />
	</div>
	<!-- The tree is inserted here -->
	<span id="{treeID}"> </span>
</div>

HTML;

	const SCRIPT_FOR_TREE = <<<SCRIPT
<script type="{wgJsMimeType}">
	if (typeof(TreeView) === 'undefined') {
		TreeView = {};
	}
	if (typeof(TreeView.trees) === 'undefined') {
		TreeView.trees = [];
	}
	var treeObj = {
		id: '{treeID}',
		json: {jsonTree},
		theme: '{defaultTheme}',
		filter: {filterSwitch},
	};
	TreeView.trees.push(treeObj);
	if (TreeView.singleton) {
		if (TreeView.singleton.TreeViewLoader) {
			TreeView.singleton.TreeViewLoader.initializeTrees();
		}
	}
</script>
SCRIPT
;

	const TREE_ID = "TreeView_ID_";
//--- Private fields ---
	
	// {int} Counter for creating unique IDs 
	private static $mNextID = 0;
	
	// {array(String)} Parameters of the current parser call
	private static $mParameters; 

	//--- getter/setter ---
	
	//--- Public methods ---
	
	
	/**
	 * This method initializes the parser functions of the treeview. These are
	 * - #tree
	 * - #generateTree
	 */
	public static function initParserFunctions(&$parser) {
		$parser->setFunctionHook('tvtree', array('TVParserFunctions', 'tree'));
		$parser->setFunctionHook('tvgeneratetree', array('TVParserFunctions', 'generateTree'));
		
		return true;
	}
	
	/**
	 * Callback for parser function "#tree:".
	 *
	 * @param {Parser} $parser
	 * 		The parser object
	 * @param {String} $wikiText
	 * 		The wiki text that will be parsed
	 *
	 * @return string
	 * 		Wikitext
	 *
	 * @throws
	 */
	public static function tree(&$parser) {
		$params = func_get_args();
		$wikiText = $params[count($params)-1];
		// Get all supported parameters
		self::storeParameters($params, 'tree');
		
		// Convert the bullet list in the wikitext to a tree representation
		$json = self::createJsonTree($wikiText);
		$id = self::TREE_ID.self::$mNextID;
		++self::$mNextID;
		
		$theme = array_key_exists(TVLanguage::PFP_THEME, self::$mParameters)
					? self::$mParameters[TVLanguage::PFP_THEME]
					: self::DEFAULT_THEME;
					
		$filter = array_key_exists(TVLanguage::PFP_FILTER, self::$mParameters)
					? (self::$mParameters[TVLanguage::PFP_FILTER] === 'true')
					: false;
		
		$width = array_key_exists(TVLanguage::PFP_WIDTH, self::$mParameters)
					? self::$mParameters[TVLanguage::PFP_WIDTH]
					: false;
		$height = array_key_exists(TVLanguage::PFP_HEIGHT, self::$mParameters)
					? self::$mParameters[TVLanguage::PFP_HEIGHT]
					: false;
		
		$text = self::createHTML($id, $width, $height)
		        .self::createScript($id, $json, $theme, $filter);
		
		global $wgOut;
		$wgOut->addModules('ext.TreeView.tree');
		
		return array($text, 'noparse' => true, 'isHTML' => true);
	}
	
	/**
	 * Callback for parser function "#generateTree:".
	 *
	 * @param {Parser} $parser
	 * 		The parser object
	 * @param {String} $wikiText
	 * 		The wiki text that will be parsed
	 *
	 * @return string
	 * 		Wikitext
	 *
	 * @throws
	 */
	public static function generateTree(&$parser) {
		$params = func_get_args();
		$wikiText = $params[count($params)-1];
		// Get all supported parameters
		self::storeParameters($params, 'generateTree');
		
		$generateTreeJSON = self::GENERATE_TREE_JSON;
		$root = array_key_exists(TVLanguage::PFP_ROOT_LABEL, self::$mParameters)
					? self::$mParameters[TVLanguage::PFP_ROOT_LABEL]
					: wfMsg('tv_default_root_name');
		$root = json_encode($root);
		
		$property = '';
		if (array_key_exists(TVLanguage::PFP_PROPERTY, self::$mParameters)) {
			$property = json_encode(self::$mParameters[TVLanguage::PFP_PROPERTY]);
			$property = ", \"property\": $property";
		}

		$solrQuery = '';
		if (array_key_exists(TVLanguage::PFP_SOLR_QUERY, self::$mParameters)) {
			$solrQuery = json_encode(self::$mParameters[TVLanguage::PFP_SOLR_QUERY]);
			$solrQuery = ", \"solrQuery\": $solrQuery";
		}
		
		$text = <<<TEXT
$generateTreeJSON
{
	"data": {
		"title": $root,
		"attr": { 
					"generateTree": true 
					$solrQuery 
					$property
				}
	}
}
TEXT
;
		
		global $wgOut;
		$wgOut->addModules('ext.TreeView.tree');
		
		// The result must be returned in a single line
		$text = preg_replace("/[\n\r]/", "", $text);
		return array($text);
	}
	
	/**
	 * Defines the magic words for the parser functions of the treeview.
	 * 
	 * @param {array(String)} $magicWords
	 * 		Array of all magic words
	 * @param {String} $langCode
	 * 		The current language code
	 */
	public static function languageGetMagic(&$magicWords, $langCode) {
		global $tvgContLang;
		$magicWords['tvtree']
			= array( 0, $tvgContLang->getParserFunction(TVLanguage::PF_TREE));
		$magicWords['tvgeneratetree']
			= array( 0, $tvgContLang->getParserFunction(TVLanguage::PF_GENERATE_TREE));
		
		return true;
	}
	
	//--- Private methods ---

	/**
	 * Creates the tree structure of the bullet list and returns it as JSON
	 * string.
	 * 
	 * @param {String} $wikiText
	 * 		The wiki text that contains the bullet list.
	 * @return {String}
	 * 		A JSON representation of the tree.
	 */
	private static function createJsonTree($wikiText) {
		$numMatches = preg_match_all("/(?:^|\n)(\*+)(.*)/", $wikiText, $matches);
		if ($numMatches > 0) {
			$tree = self::buildTreeStructure($matches[1], $matches[2]);
			$json = self::serializeTreeAsJson($tree, true);
			$json = self::wrapJsonTree($json);
		} else {
			// There is no tree structure given
			$json = '{"data":'.json_encode(wfMsg('tv_missing_tree')).'}';
		}
		
		// Remove linebreaks. Otherwise the MW parser inserts <p> tags.
		$json = preg_replace("/\n/","",$json);
		return $json;
		
	}
	
	/**
	 * Creates a PHP array structure that represents the tree.
	 * This function calls itself recursively.
	 * 
	 * @param {array(String)} $levels
	 * 		This array contains the level structure of the tree in form of
	 * 		the asterisks of the wiki text
	 * @param {array(String)} $labels
	 * 		This array contains all labels in the tree.
	 * @param int $index
	 * 		The current index in the arrays $levels and $labels.
	 * @param int $currLevel
	 * 		The current level in the tree while building the structure.
	 */
	private static function buildTreeStructure($levels, $labels, &$index = 0, 
												$currLevel = 0) {
		$node = array();
		
		$level = strlen($levels[$index]);
		if ($level == $currLevel) {
			// add a label to the current level
			$node = self::parseLabel($labels[$index]);
			++$index;
		}
		
		while ($index < count($levels)) {
			$level = strlen($levels[$index]);
			if ($level > $currLevel) {
				// add a deeper level
				if (!array_key_exists('children', $node)) {
					$node['children'] = array();
				}
				$node['children'][] = self::buildTreeStructure($levels, $labels, $index, $currLevel+1);
			} else {
				// go back one level
				return $node;
			}
		}
		return $node;
	}
	
	/**
	 * Creates a piece of JavaScript that displays the tree in the page
	 * @param {String} $id
	 * 		ID of the HTML element to which the tree is attached.
	 * @param {String} $jsonTree
	 * 		JSON representation of the tree.
	 * @param {String} $theme
	 * 		The name of the tree's theme (skin). 
	 * @param {bool} $filter
	 * 		true, if the full-text filter should be enabled. 
	 * 
	 * @return {String}
	 * 		A piece of JavaScript
	 */
	private static function createScript($id, $jsonTree, $theme, $filter = false) {
		global $wgJsMimeType;
		$script = str_replace('{wgJsMimeType}', $wgJsMimeType, self::SCRIPT_FOR_TREE);
		$script = str_replace('{jsonTree}', $jsonTree, $script);
		$script = str_replace('{treeID}', $id, $script);
		$script = str_replace('{defaultTheme}', $theme, $script);
		$script = str_replace('{filterSwitch}', $filter ? 'true' : 'false', $script);
		
		return $script;
	}
	
	/**
	 * Creates a piece of HTML that displays the tree in the page
	 * @param {String} $id
	 * 		ID of the element that will be the anchor for the tree
	 * @param {bool/int} $width
	 * 		If not false, this is the width of the tree area in pixels
	 * @param {bool/int} $height
	 * 		If not false, this is the height of the tree area in pixels
	 * 
	 * @return {String}
	 * 		A piece of HTML
	 */
	private static function createHTML($id, $width, $height) {
		$html = str_replace('{treeID}', $id, self::HTML_FOR_TREE);
		
		// Set width and height of the div containing tree
		$dimensions = '';
		if ($width !== false && $height !== false) {
			$dimensions = "style=\"width:{$width}px;height:{$height}px\"";
		} else if ($width !== false) {
			$dimensions = "style=\"width:{$width}px\"";
		} else if ($height !== false) {
			$dimensions = "style=\"height:{$height}px\"";
		}
		$html = str_replace('{dimensions}', $dimensions, $html);
		
		$html = TreeViewExtension::replaceLanguageStrings($html);
		return $html;
	}
	
	/**
	 * Serializes the tree structure given in $tree as JSON for display in jstree
	 * 
	 * @param {array} $tree
	 * 		The tree as an array structure with the keys "label", "link", "json"
	 *      and "children".
	 * @param {boolean} $isTopLevel
	 * 		true, if this recursion step creates the top level of the tree  
	 * 		false otherwise
	 * 
	 * @return {String}
	 * 		JSON serialization of the tree.
	 */
	private static function serializeTreeAsJson($tree, $isTopLevel = false) {
		// If the node already contains JSON, just return it as it is.
		if (array_key_exists('json', $tree) && $tree['json']) {
			return $tree['json'];
		}
		
		// Add the label
		$label = array_key_exists('label', $tree)
					? $tree['label']
					: wfMsg('tv_missing_tree_level');
		$label = json_encode($label);
		
		// Add all children
		$children = "";
		if (array_key_exists('children', $tree)) {
			$children = ($isTopLevel) 
							? '"data":[' . "\n"
							: ",\n" . '"children":[' . "\n";
			
			$numChildren = count($tree['children']);
			$i = 0;
			foreach ($tree['children'] as $child) {
				$children .= self::serializeTreeAsJson($child);
				if (++$i < $numChildren) {
					$children .= ",\n";
				}
			}
			$children .= ']' . "\n";
		}

		// Add the link
		$link = $tree['link']
					? '"href":'.json_encode($tree['link'])
					: ''; 
		// Create the JSON representation
		if ($isTopLevel) {
			$json = $children;
		} else {
			$json = <<<JSON
{
	"data": {
		"title": $label,
		"attr": { $link }
	}
	$children
}
JSON;
		}	
		return $json;
	}
	
	/**
	 * Wraps the json structure for a tree in an root object.
	 * @param {String} $json
	 * 		The json representation of a tree.
	 * @return {String}
	 * 		The wrapped json
	 */
	private static function wrapJsonTree($json) {
		$json = <<<JSON
{
	$json
}		
JSON;
		return $json;		
	}
	
	/**
	 * Stores the valid parameters for the given parser function in the
	 * class variable $mParameters.
	 * 
	 * @param {array(String)} $params
	 * 		The parameters that were passed to the parser function.
	 * @param {String} $parserFunction
	 * 		The name of the parser function i.e. 'tree' or 'generateTree'
	 */
	private static function storeParameters($params, $parserFunction) {
		global $tvgContLang;
		self::$mParameters = array();
		$validParams = array();
		if ($parserFunction === 'tree') {
			$validParams = array(TVLanguage::PFP_THEME, TVLanguage::PFP_FILTER,
								 TVLanguage::PFP_WIDTH, TVLanguage::PFP_HEIGHT);
		} else if ($parserFunction === 'generateTree') {
			$validParams = array(TVLanguage::PFP_PROPERTY, TVLanguage::PFP_ROOT_LABEL,
			                     TVLanguage::PFP_SOLR_QUERY);
		}
		for ($i = 1; $i < count($params); ++$i) {
			$p = $params[$i];
			if (preg_match("/\s*(.*?)\s*=\s*(.*?)\s*$/", $p, $matches)) {
				foreach ($validParams as $pid) {
					$pname = $tvgContLang->getParserFunctionParameter($pid);
					if ($matches[1] === $pname) {
						self::$mParameters[$pid] = $matches[2];
					}
				}
			}
		}
	}
	
	/**
	 * A label in the tree structure may be 
	 * - normal text (i.e. a simple label),
	 * - a wiki link ([[...]]),
	 * - an external link ([...]),
	 * - a line beginning with the value of GENERATE_TREE_JSON
	 * This function parses the label and tries to identify links and generateTreeJSON.
	 *  
	 * @param {String} $label
	 * 		The label that may be a wiki or external link.
	 * @return array('label' => string, 'link' => bool/string, 'json' => bool/string)
	 * 		label: the label that can be displayed
	 * 		link: If not 'false', the full URL of the link
	 * 		json: If not 'false' some JSON code
	 */
	private static function parseLabel($label) {
		// Try to parse a wiki link
		$link = false;
		$json = false;
		if (preg_match("/\s*\[\[(.*?)(\|(.*?))?\]\]/", $label, $matches)) {
			if (count($matches) == 4) {
				// found page name and alternative label
				$link  = $matches[1];
				$label = $matches[3];
				$t = Title::newFromText($link);
				$link = $t->getFullURL();
			} else {
				// found only a page name
				$label = $matches[1];
				$t = Title::newFromText($label);
				$link = $t->getFullURL();
			}
			// The label may be a category with a leading colon (e.g. :Category:Foo)
			global $wgContLang;
			if ($label{0} === ':' && strpos($label, $wgContLang->getNsText(NS_CATEGORY)) === 1) {
				$label = substr($label, 1);
			}
		} else if (preg_match("/\s*((\[(http[s]?:\/\/.+?)( (.*?))?\])|(http[s]?:\/\/[^\s]+))\s*/", $label, $matches)) {
			// An external link was parsed
			if (count($matches) == 7) {
				// The pure link is given
				$link = $matches[6];
			} else if (count($matches) == 4) {
				// The pure link is given in braces
				$link = $matches[3];
			} else if (count($matches) == 6) {
				// The link and label are given in braces
				$link  = $matches[3];
				$label = $matches[5];
			}
			
		} else {
			// Check for the JSON generated by the generateTree parser function
			$regex = "/".self::GENERATE_TREE_JSON."\s*(.*)/";
			if (preg_match($regex, $label, $matches)) {
				$json = $matches[1];
			}	
		}
		return array('label' => $label, 'link' => $link, 'json' => $json);
	}
}

