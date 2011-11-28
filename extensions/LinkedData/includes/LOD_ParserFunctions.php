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
 * @ingroup LinkedData
 */
/**
 * This file contains the implementation of parser functions for the LinkedData
 * extension.
 *
 * @author Thomas Schweitzer
 * Date: 12.04.2010
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

//--- Includes ---
global $lodgIP;
//require_once("$lodgIP/...");
$wgExtensionFunctions[] = 'lodfInitParserfunctions';




function lodfInitParserfunctions() {
	global $wgParser, $lodgContLang;
	
	//Add to install and readme that mapping tag name has been changed
	
	$inst = LODParserFunctions::getInstance();
	$wgParser->setHook($lodgContLang->getParserFunction(LODLanguage::PF_RMAPPING), 
		array('LODParserFunctions', 'r2rMapping'));

	$wgParser->setHook($lodgContLang->getParserFunction(LODLanguage::PF_SMAPPING), 
		array('LODParserFunctions', 'silkMapping'));
	                   
		
	global $wgHooks;
	$wgHooks['ArticleSave'][] = 'LODParserFunctions::onArticleSave';
}




/**
 * The class LODParserFunctions contains all parser functions of the LinkedData
 * extension. The following functions are parsed:
 * - mapping (as tag i.e. <mapping>
 *
 * @author Thomas Schweitzer
 *
 */
class LODParserFunctions {

	//--- Constants ---

	//--- Private fields ---

	// LODParserFunctions: The only instance of this class
	private static $mInstance = null;
		
	/**
	 * Constructor for HACLParserFunctions. This object is a singleton.
	 */
	public function __construct() {
	}


	//--- Public methods ---

	/**
	 * Returns the singleton of this class.
	 */
	public static function getInstance() {
		if (is_null(self::$mInstance)) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}

	/**
	 * Parses the r2rMapping tag <r2rMapping>. The tag may have two parameters:
	 * "source" and "target" of the mapping. If the source is not specified,
	 * the name of the article is taken as source. Users must be aware that the
	 * case of the article is modified by MW and the spaces are replaced by "_".
	 * If the target is undefined, the default target defined in the global variable
	 * $lodgDefaultMappingTarget is used. 
	 *
	 * @param string $text
	 * 		The content of the <mapping> tag
	 * @param array $params
	 * 		Parameters
	 * @param Parser $parser
	 * 		The parser
	 */
	public static function r2rMapping($text, $params, $parser)  {
		
		// The mapping function is only allowed in namespace "Mapping".
		$title = $parser->getTitle();
		$ns = $title->getNamespace();
		$msg = "";
		if ($ns != LOD_NS_MAPPING) {
			// Wrong namespace => add a warning
			$msg = wfMsg('lod_mapping_tag_ns');
		} else {
			$store = LODMappingStore::getStore();

			// Get the "target" parameter
			global $lodgContLang;
			$targetName = $lodgContLang->getParserFunctionParameter(LODLanguage::PFP_MAPPING_TARGET);
			$target = null;
			if (array_key_exists($targetName, $params)) {
				$target = $params[$targetName];
				$target = self::removeDataSourcePrefix($target);
			} else {
				global $lodgDefaultMappingTarget;
				$target = $lodgDefaultMappingTarget;
			}
			
			// Get the "source" parameter
			global $lodgContLang;
			$sourceName = $lodgContLang->getParserFunctionParameter(LODLanguage::PFP_MAPPING_SOURCE);
			$source = null;
			if (array_key_exists($sourceName, $params)) {
				$source = $params[$sourceName];
				$source = self::removeDataSourcePrefix($source);
			} else {
				// Article name is the default source
				$source = $title->getText();
			}
			
			// Store this mapping.
			$mapping = new LODR2RMapping(null, $text, $source, $target);
			
			$success = $store->addMapping($mapping, $title->getFullText());
			
			$store->addMappingToPage($title->getFullText(), $source, $target);
			
			if (!$success) {
				$msg = wfMsg("lod_saving_mapping_failed");
			}
			
		}
		
		$text = htmlentities($text);
		return "$msg\n\n<pre>$text</pre>";
	}
	
	
/**
	 * Parses the silkMapping tag <silkMapping>. The tag may have four parameters:
	 * "source", "target", "mintNamespace"  and "mintPredicateLabel".  
	 * If the source is not specified, the name of the article is taken as source. 
	 * Users must be aware that the case of the article is modified by MW and 
	 * the spaces are replaced by "_".
	 * If the target is undefined, the default target defined in the global variable
	 * $lodgDefaultMappingTarget is used. 
	 * If no mintNamespace is set, then the Wiki's default namespace is used.
	 * The mintPredicateLabel attribute can take zero or more space separated
	 * URIs.
	 *
	 * @param string $text
	 * 		The content of the <mapping> tag
	 * @param array $params
	 * 		Parameters
	 * @param Parser $parser
	 * 		The parser
	 */
	public static function silkMapping($text, $params, $parser)  {
		// The silk-mapping function is only allowed in namespace "Mapping".
		$title = $parser->getTitle();
		$ns = $title->getNamespace();
		$msg = "";
		if ($ns != LOD_NS_MAPPING) {
			// Wrong namespace => add a warning
			$msg = wfMsg('lod_mapping_tag_ns');
		} else {
			
			// Get the "target" parameter
			global $lodgContLang;
			$targetName = $lodgContLang->getParserFunctionParameter(LODLanguage::PFP_MAPPING_TARGET);
			$target = null;
			if (array_key_exists($targetName, $params)) {
				$target = $params[$targetName];
				$target = self::removeDataSourcePrefix($target);
			} else {
				global $lodgDefaultMappingTarget;
				$target = $lodgDefaultMappingTarget;
			}
			
			// Get the "source" parameter
			global $lodgContLang;
			$sourceName = $lodgContLang->getParserFunctionParameter(LODLanguage::PFP_MAPPING_SOURCE);
			$source = null;
			if (array_key_exists($sourceName, $params)) {
				$source = $params[$sourceName];
				$source = self::removeDataSourcePrefix($source);
			} else {
				// Article name is the default source
				$source = $title->getText();
			}
			
			$pm = TSCPrefixManager::getInstance();
			
			//get mintNamespace
			$mintNamespaceName = strtolower(
				$lodgContLang->getParserFunctionParameter(LODLanguage::PFP_SILK_MAPPING_MINT_NAMESPACE));
			$mintNamespace = null;
			if (array_key_exists($mintNamespaceName, $params)) {
				if(!Http::isValidURI($params[$mintNamespaceName])){
					$msg .= '<br/>'.wfMsg("lod_mapping_invalid_mintNS");
					global $smwgHaloTripleStoreGraph;
					$mintNamespace = '<'.$smwgHaloTripleStoreGraph.'>';	
				} else { 
					$mintNamespace = '<'.$params[$mintNamespaceName].'>';
				}
			} else {
				global $smwgHaloTripleStoreGraph;
				$mintNamespace = '<'.$smwgHaloTripleStoreGraph.'>';
			}
			
			//get mintPredicateLabels
			$mintLabelPredicateName = strtolower(
				$lodgContLang->getParserFunctionParameter(LODLanguage::PFP_SILK_MAPPING_MINT_LABEL_PREDICATE));
			$mintLabelPredicates = null;
			$mintLabelPredicates = array();
			if (array_key_exists($mintLabelPredicateName, $params)) {
				$mintLabelPredicates = explode(' ', $params[$mintLabelPredicateName]);
				
				foreach($mintLabelPredicates as $key => $labelPredicate){
					try{
						$labelPredicate = $pm->makeAbsoluteURI($labelPredicate, false);
					} catch (Exception $e){}
					
					if(!Http::isValidURI($labelPredicate)){
						$msg .= '<br/>'.wfMsg("lod_mapping_invalid_mintLP", $mintLabelPredicates[$key]);
						unset($mintLabelPredicates[$key]);
					} else {
						$mintLabelPredicates[$key] = '<'.$labelPredicate.'>';
					}
				}
				
			} 
			
			// Store this mapping.
			$mapping = new LODSILKMapping(null, $text, $source, $target, $mintNamespace, $mintLabelPredicates);
			
			//echo('<pre>'.print_r($mapping, true).'</pre>');
			
			$store = LODMappingStore::getStore();
			$success = $store->addMapping($mapping, $title->getFullText());
			
			$store->addMappingToPage($title->getFullText(), $source, $target);
			
			if (!$success) {
				$msg .= '<br/>'.wfMsg("lod_saving_mapping_failed");
			}
		}
		
		$text = htmlentities($text);
		return "$msg\n\n<pre>$text</pre>";
	}
	
	
	
	/**
	 * This method is called, when an article is deleted. If the article
	 * belongs to the namespace "Mapping", the LOD mappings for the article are
	 * deleted.
	 *
	 * @param Article $article
	 * 		The article that will be deleted.
	 * @param User $user
	 * 		The user who deletes the article.
	 * @param string $reason
	 * 		The reason, why the article is deleted.
	 */
	public static function articleDelete(&$article, &$user, &$reason) {
		self::deleteMappingsForArticle($article);
		
		return true;
	}
	

	/**
	 * This function checks for articles in the namespace "Mapping", if the 
	 * database contains a corresponding mapping. If not, an error message is 
	 * displayed on the page.
	 *
	 * @param OutputPage $out
	 * @param string $text
	 * @return bool true
	 */
	public static function outputPageBeforeHTML(&$out, &$text) {
		global $wgTitle;
		$title = $wgTitle;
		if (isset($title) && $title->getNamespace() == LOD_NS_MAPPING) {
			$store = LODMappingStore::getStore();
			
			if (count($store->getMappingsInArticle($title->getFullText())) == 0) {
				$msg = wfMsg("lod_no_mapping_in_ns");
				$out->addHTML("<div><b>$msg</b></div>");
			}
		}
		
		return true;
	}
	
	/**
	 * 
	 * Occurs whenever the software receives a request to save an article.
	 * The article may contain LOD source definitions that are no longer present
	 * or that changed in the new version of the article. All source definitions
	 * for the article are deleted in the triple store and the persistency layer.
	 * 
	 * If the article belongs to the Mapping namespace, all existing mappings for
	 * that article are deleted before new mappings are created.
	 */
	public static function onArticleSave(&$article) {
		
		self::deleteMappingsForArticle($article);
		
		return true;
	}

	//--- Private methods ---
	
	
	
	/**
	 * Deletes all mappings that are stored for $article.
	 * 
	 * @param Article $article
	 * 		This article may contain mappings if it belongs to the mapping 
	 * 		namespace.
	 */
	private static function deleteMappingsForArticle($article) {
		
		if ($article->getTitle()->getNamespace() == LOD_NS_MAPPING) {
			// The article is in the "Mapping" namespace.
			
			//remove all mappings defined in this article, because they may
			//have been deleted and updated. They will be stored again,
			//ehrn zhr mapping tags are evaluated.
			$articleName = $article->getTitle()->getFullText();
			$store = LODMappingStore::getStore();
			$store->removeAllMappingsFromPage($articleName);
		}
	}
	
	
	/*
	 * Removes the dta source prefix if available
	 */
	private static function removeDataSourcePrefix($dataSource){
		$pm = TSCPrefixManager::getInstance();
		$prefix = $pm->getNamespaceURI('smwDatasources');
		if (strpos($dataSource, $prefix) === 0) {
			$dataSource = substr($dataSource, strlen($prefix));
		}
		return $dataSource;
	}
	
	
}
