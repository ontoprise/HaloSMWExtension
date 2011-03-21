<?php
/**
 * @file
 * @ingroup LinkedData
 */
/*  Copyright 2010, ontoprise GmbH
 *  This file is part of the HaloACL-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

$wgHooks['LanguageGetMagic'][] = 'lodfLanguageGetMagic';


function lodfInitParserfunctions() {
	global $wgParser, $lodgContLang;
	
	//Add to install and readme that mapping tag name has been changed
	
	$inst = LODParserFunctions::getInstance();
	$wgParser->setHook($lodgContLang->getParserFunction(LODLanguage::PF_RMAPPING), 
		array('LODParserFunctions', 'r2rMapping'));

	$wgParser->setHook($lodgContLang->getParserFunction(LODLanguage::PF_SMAPPING), 
		array('LODParserFunctions', 'silkMapping'));
	                   
	$wgParser->setFunctionHook('lodsourcedefinition', array($inst, 'lodSourceDefinition'));
	
	global $wgHooks;
	$wgHooks['ArticleSave'][] = 'LODParserFunctions::onArticleSave';
}

function lodfLanguageGetMagic( &$magicWords, $langCode ) {
	global $lodgContLang;
	$magicWords['lodsourcedefinition']
		= array( 0, $lodgContLang->getParserFunction(LODLanguage::PF_LSD));
	return true;
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
			
			$pm = LODPrefixManager::getInstance();
			
			//get mintNamespace
			$mintNamespaceName = strtolower(
				$lodgContLang->getParserFunctionParameter(LODLanguage::PFP_SILK_MAPPING_MINT_NAMESPACE));
			$mintNamespace = null;
			if (array_key_exists($mintNamespaceName, $params)) {
				if(!Http::isValidURI($params[$mintNamespaceName])){
					$msg .= '<br/>'.wfMsg("lod_mapping_invalid_mintNS");
					global $smwgTripleStoreGraph;
					$mintNamespace = '<'.$smwgTripleStoreGraph.'>';	
				} else { 
					$mintNamespace = '<'.$params[$mintNamespaceName].'>';
				}
			} else {
				global $smwgTripleStoreGraph;
				$mintNamespace = '<'.$smwgTripleStoreGraph.'>';
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
			$mapping = new LODSILKMapping($text, $source, $target, $mintNamespace, $mintLabelPredicates);
			
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
	 * Callback for parser function "#sourcedefinition:".
	 * This parser function defines a LOD source definition. It can appear 
	 * several times in an article and has the following parameters:
	 * 
	 * id (string) 1
	 * A short name like "dbpedia" for the source (to be precise: the dataset of
	 * a source). The ID will later be referenced in other places e.g. queries and
	 * mappings. 
	 *	
	 * Description (string) 0..1
	 * An optional textual description of the dataset. 
	 *
	 * Label (string) 0..1
	 * An optional label that provides the name of the dataset. 
	 *
	 * Homepage (URI) 0..1
	 * The homepage of the dataset. Note, this must be different from the 
	 * homepage of the creator or publisher to avoid incorrect 'smushing'. 
	 *
	 * SampleURI (URI) 0..*
	 * This tag can be used to point to a URI within the dataset which can be 
	 * considered a representative “sample”. This is useful for Semantic Web 
	 * clients to provide starting points for human exploration of the dataset. 
	 * There can be any number of sample URIs. 
	 *
	 * SparqlEndpointLocation (URI) 0..1
	 * The location of a SPARQL protocol endpoint for the dataset. There can be 
	 * zero or one for a dataset.
	 *
	 * SparqlGraphName (URI) 0..1
	 * If this optional parameter is present, then it specifies the URI of a 
	 * named graph within the SPARQL endpoint. This named graph is assumed to 
	 * contain the data of this dataset. This tag must be used only if 
	 * sparqlEndpointLocation is also present, and there must be at most one 
	 * sparqlGraphName per dataset.
	 * If the data is distributed over multiple named graphs in the endpoint, 
	 * then the publisher should either use a value of “*” for this tag, or 
	 * create separate datasets for each named graph.
	 * If the tag is omitted, the dataset is assumed to be available through the 
	 * endpoint's default graph. 
	 *
	 * SparqlGraphPattern string 0..*
	 * If this optional parameter is present, then it specifies a list of graph
	 * pattern restrictions that refer to the variables ?s, ?p, ?o and are applied
	 * in conjunction.
	 * An exemplary graph pattern restriction is FILTER (?p = <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>).
	 *
	 * DataDumpLocation (URI) 0..*
	 * Indicates the location of an RDF data dump file. There can be any numbers
	 * of data dump location assignments. The dataset is said to contain the RDF 
	 * merge of all the dumps. 
	 *
	 * LastMod (date) 0..1
	 * This optional parameter, defined by the Sitemap protocol, gives the date 
	 * of last modification of the dataset. This date should be in W3C Datetime
	 * format. Example values are 2007-11-21 and 2007-11-21T14:41:09+00:00. 
	 *
	 * ChangeFreq (string) 0..1
	 * This optional tag, defined by the Sitemap protocol, describes how often 
	 * the dataset is expected to be updated. Possible values are: always, hourly,
	 * daily, weekly, monthly, yearly, never. 
	 *
	 * Vocabulary (URI) 0..*
	 * Every RDF dataset uses one or more RDFS vocabularies or OWL ontologies. 
	 * The vocabulary provides the terms (classes and properties) for expressing 
	 * the data. The vocabulary property can be used to list vocabularies used in 
	 * a dataset. 
	 *
	 * PredicateToCrawl (URI) 0..*
	 * When importing from URIs, this property allows to specify a predicate
	 * that should be followed by the crawler.
	 *
	 * @param Parser $parser
	 * 		The parser object
	 *
	 * @return string
	 * 		Wikitext
	 *
	 */
	public function lodSourceDefinition(&$parser) {
		// Get the parameters of the parser function
		$params = $this->getParameters(func_get_args());
		
		// Check the consistency of the parameters
		$wikiText = "";
		$lsd = $this->createLSDFromParams($params, $wikiText);
		if (!is_null($lsd)) {
			$articleName = $parser->getTitle()->getFullText();
			// Data source definition is fine
			// => store it persistently with the $articleName as ID 
			// Store the source definition
			$store = LODAdministrationStore::getInstance();
			$store->storeSourceDefinition($lsd, $articleName);
		};
		
		return $wikiText;
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
		
		// Delete the triples of LOD source definitions
		$store = LODAdministrationStore::getInstance();
		$persistencyID = $article->getTitle()->getFullText();
		$store->deleteSourceDefinition(NULL, $persistencyID);
		
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
		
		$store = LODAdministrationStore::getInstance();
		$persistencyID = $article->getTitle()->getFullText();
		$store->deleteSourceDefinition(NULL, $persistencyID);
		
		self::deleteMappingsForArticle($article);
		
		return true;
	}

	//--- Private methods ---
	
	/**
	 * Returns the parser function parameters that were passed to the parser-function
	 * callback. The same parameter name may be used several times. In this case
	 * an array of values is returned.
	 *
	 * @param array(mixed) $args
	 * 		Arguments of a parser function callback
	 * @return array(string=>string/array)
	 * 		Array of argument names and their values.
	 */
	private function getParameters($args) {
		$parameters = array();

		foreach ($args as $arg) {
			if (!is_string($arg)) {
				continue;
			}
			if (preg_match('/^\s*(.*?)\s*=\s*(.*?)\s*$/', $arg, $p) == 1) {
				$key = strtolower($p[1]);
				if (array_key_exists($key, $parameters)) {
					if (is_array($parameters[$key])) {
						$parameters[$key][] = $p[2];
					} else {
						// until now only a single value was stored
						$parameters[$key] = array($parameters[$key], $p[2]);			
					}
				} else {
					$parameters[$key] = $p[2];
				}
			}
		}

		return $parameters;
	}
	
	/**
	 * Checks the consistency of the parameters of a LOD source definition and
	 * creates an object of type LODSourceDefinition. If the parameters
	 * are inconsistent a wiki text with error messages will be returned otherwise
	 * the wiki text will list the properties of the LSD.
	 * 
	 * @param array $params
	 * 		These parameters define the source
	 * @param string $wikiText
	 * 		The wiki text describes the errors or properties of the LSD..
	 * @return mixed LODSourceDefinition
	 * 		LODSourceDefinition: if all parameters are valid
	 * 		null: Some parameters are invalid
	 */
	private function createLSDFromParams(array $params, &$wikiText) {
		$errMsg = array();
		$wikiTextMsg = array();
		
		// ID: mandatory
		$id = $this->retrieveParam($params, LODLanguage::PFP_LSD_ID, 1, 1, $errMsg);
		if (is_null($id)) {
			$id = "dummy";
		} else {
			$wikiTextMsg[] = array(wfMsgForContent('lod_lsd_id'), array($id));
		}
		$lsd = new LODSourceDefinition($id);
		
		// Description of the parameters in a LODSourceDefinition
		// 0 - Language ID of the parameter name
		// 1 - minimum expected values
		// 2 - maximum expected values
		// 3 - Name of the setter function in LODSourceDefinition
		$paramDescr = array(
			array(LODLanguage::PFP_LSD_DESCRIPTION, 			0,  1, "setDescription", 			"lod_lsd_description"),
			array(LODLanguage::PFP_LSD_LABEL, 					0,  1, "setLabel", 					"lod_lsd_label"),
			array(LODLanguage::PFP_LSD_HOMEPAGE, 				0,  1, "setHomepage", 				"lod_lsd_homepage"),
			array(LODLanguage::PFP_LSD_SAMPLEURI, 				0, -1, "setSampleURIs", 			"lod_lsd_sampleuri"),
			array(LODLanguage::PFP_LSD_SPARQLENDPOINTLOCATION,	0,  1, "setSparqlEndpointLocation",	"lod_lsd_sparqlendpointlocation"),
			array(LODLanguage::PFP_LSD_SPARQLGRAPHNAME, 		0,  1, "setSparqlGraphName", 		"lod_lsd_sparqlgraphname"),
			array(LODLanguage::PFP_LSD_SPARQLGRAPHPATTERN, 		0, -1, "setSparqlGraphPatterns", 	"lod_lsd_sparqlgraphpattern"),
			array(LODLanguage::PFP_LSD_DATADUMPLOCATION, 		0, -1, "setDataDumpLocations", 		"lod_lsd_datadumplocation"),
			array(LODLanguage::PFP_LSD_LASTMOD, 				0,  1, "setLastMod", 				"lod_lsd_lastmod"),
			array(LODLanguage::PFP_LSD_CHANGEFREQ, 				0,  1, "setChangeFreq",				"lod_lsd_changefreq"),
			array(LODLanguage::PFP_LSD_VOCABULARY, 				0, -1, "setVocabularies", 			"lod_lsd_vocabulary"),
			array(LODLanguage::PFP_LSD_PREDICATETOCRAWL, 		0, -1, "setPredicatesToCrawl",		"lod_lsd_predicatetocrawl"),
			);
		
		// Retrieve and set all parameters of $lsd
		foreach ($paramDescr as $pd) {
			$val = $this->retrieveParam($params, $pd[0], $pd[1], $pd[2], $errMsg);
			if (!is_null($val)) {
				if ($pd[2] == -1 || $pd[2] > 1) {
					// If the maximum number of values is greater than 1 an
					// array is expected.
					if (!is_array($val)) {
						$val = array($val);
					}
				}
				// Set the parameter value in the LSD
				$lsd->$pd[3]($val);
				
				// Add the parameter/values-pairs to the wiki text.
				if (!is_array($val)) {
					$val = array($val);
				}
				$wikiTextMsg[] = array(wfMsgForContent($pd[4]),$val);
			}
		}
		
		if (!empty($errMsg)) {
			// error messages => return the invalid LSD
			$lsd = null;
		}
		
		// Generate the wiki text wich consists of a table of valid parameters
		// and a list of error messages.
		
		$title = wfMsgForContent("lod_lsdparser_title");
		
		$wikiText = "==$title $id==\n\n";
		
		$wikiText .= empty($errMsg) 
						? wfMsgForContent("lod_lsdparser_success")
						: wfMsgForContent("lod_lsdparser_failed");
						
		$wikiText .= "\n";						
						
		if (!empty($wikiTextMsg)) {
			$wikiText .= '{| cellspacing="0" border="1" cellpadding="3"';
			foreach ($wikiTextMsg as $propVal) {
				$wikiText .= "\n|-\n!{$propVal[0]}\n|";
				$num = count($propVal[1]);
				for ($i = 0; $i < $num; ++$i) {
					$wikiText .= $propVal[1][$i];
					if ($i < $num-1) {
						$wikiText .= "<br />";
					}
				}
			}
			$wikiText .= "\n|}\n\n";
		}
		
		// Generate the list of error messages
		if (!empty($errMsg)) {
			$wikiText .= "\n\n";
			$wikiText .= wfMsgForContent("lod_lsdparser_error_intro");
			$wikiText .= "\n";
			foreach ($errMsg as $em) {
				$wikiText .= "* $em\n";
			}
		}
		
		return $lsd;
	}
	
	/**
	 * Retrieves the value of a parser function parameter and performs cardinality
	 * checks. If these fail, the array of error messages is enhanced, otherwise
	 * the value is returned.
	 * 
	 * @param array<paramName => string/array> $params
	 * 		The array of all parser function parameters.
	 * @param int $pfpID
	 * 		The parser function parameter ID which is used to determine the
	 * 		language dependent parameter name.
	 * @param int $min
	 * 		Minimum number of values for the parameter
	 * @param int $max
	 * 		Maximum number of values for the parameter. -1 means infinite.
	 * @param array<string> $errMsg
	 * 		An array of error messages which is extended if the cardinality 
	 * 		constraints are violated.
	 * 
	 * @return mixed string/array/null
	 * 		string: The single value of the parameter
	 * 		array<string>: Multiple values of the parameter
	 * 		null: Cardinality constraint is violated
	 * 	
	 */
	private function retrieveParam(array $params, $pfpID, $min, $max, array &$errMsg) {
		global $lodgContLang;
		$pfp = $lodgContLang->getParserFunctionParameter($pfpID);
		
		$value = @$params[$pfp];
		if (!isset($value)) {
			// no value for this parameter
			if ($min > 0) {
				// expected at least one value
				$errMsg[] = wfMsgForContent("lod_lsdparser_expected_at_least", $min, $pfp);
				return null;
			}
		}
		
		if (is_array($value)) {
			// Check that the number of values is between $min and $max
			$num = count($value);
			if ($num >= $min && ($max == -1 || $num <= max)) {
				return $value;
			} else {
				$errMsg[] = ($max == -1)
								? wfMsgForContent("lod_lsdparser_expected_at_least", $min, $pfp)
								: ($min == $max) 
									? wfMsgForContent("lod_lsdparser_expected_exactly", $min, $pfp)
									: wfMsgForContent("lod_lsdparser_expected_between", $min, $max, $pfp);
				return null;								
			}
		} else {
			// A single value is given
			if ($min > 1 || ($max < 1 && $max != -1)) {
				// expected at least one value
				if ($max == -1) {
					$max = '*';
				}
				$errMsg[] = wfMsgForContent("lod_lsdparser_expected_between", $min, $max, $pfp);
				return null;
			}
			
		}
		return $value;
		
	}
	
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
		$pm = LODPrefixManager::getInstance();
		$prefix = $pm->getNamespaceURI('smwDatasources');
		if (strpos($dataSource, $prefix) === 0) {
			$dataSource = substr($dataSource, strlen($prefix));
		}
		return $dataSource;
	}
	
	
}
