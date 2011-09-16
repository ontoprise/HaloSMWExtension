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
$wgExtensionFunctions[] = 'tscfInitParserfunctions';

$wgHooks['LanguageGetMagic'][] = 'tscfLanguageGetMagic';
//--- Includes ---


function tscfInitParserfunctions() {
    global $wgParser, $tscgContLang;
  
    $inst = TSCParserFunctions::getInstance();
    //Add to install and readme that mapping tag name has been changed
    $wgParser->setFunctionHook('sourcedefinition', array($inst, 'tscSourceDefinition'));
    
    global $wgHooks;
    $wgHooks['ArticleSave'][] = 'TSCParserFunctions::onArticleSave';
}

function tscfLanguageGetMagic( &$magicWords, $langCode ) {
    global $tscgContLang;
    $magicWords['sourcedefinition'] = array( 0, 'sourcedefinition'/* $tscgContLang->getParserFunction(TSCLanguage::PF_LSD)*/);
    return true;
}



/**
 * The class TSCParserFunctions contains all parser functions of the LinkedData
 * extension. The following functions are parsed:
 * - mapping (as tag i.e. <mapping>
 *
 * @author Thomas Schweitzer
 *
 */
class TSCParserFunctions {

	//--- Constants ---

	//--- Private fields ---

	// TSCParserFunctions: The only instance of this class
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
	 * Callback for parser function "#sourcedefinition:".
	 * This parser function defines a TSC source definition. It can appear 
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
         * LevelsToCrawl (integer) 0..1
	 * Indicates the maximum number of levels to be crawled.
         *
	 *
	 * @param Parser $parser
	 * 		The parser object
	 *
	 * @return string
	 * 		Wikitext
	 *
	 */
	public function tscSourceDefinition(&$parser) {
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
			$store = TSCAdministrationStore::getInstance();
			$store->storeSourceDefinition($lsd, $articleName);
		};
		
		return $wikiText;
	}
	
	/**
	 * This method is called, when an article is deleted. If the article
	 * belongs to the namespace "Mapping", the TSC mappings for the article are
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
		
		// Delete the triples of TSC source definitions
		$store = TSCAdministrationStore::getInstance();
		$persistencyID = $article->getTitle()->getFullText();
		$store->deleteSourceDefinition(NULL, $persistencyID);
		
		return true;
	}
	

	
	
	/**
	 * 
	 * Occurs whenever the software receives a request to save an article.
	 * The article may contain TSC source definitions that are no longer present
	 * or that changed in the new version of the article. All source definitions
	 * for the article are deleted in the triple store and the persistency layer.
	 * 
	 * If the article belongs to the Mapping namespace, all existing mappings for
	 * that article are deleted before new mappings are created.
	 */
	public static function onArticleSave(&$article) {
		
		$store = TSCAdministrationStore::getInstance();
		$persistencyID = $article->getTitle()->getFullText();
		$store->deleteSourceDefinition(NULL, $persistencyID);
		
		
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
	 * Checks the consistency of the parameters of a TSC source definition and
	 * creates an object of type TSCSourceDefinition. If the parameters
	 * are inconsistent a wiki text with error messages will be returned otherwise
	 * the wiki text will list the properties of the LSD.
	 * 
	 * @param array $params
	 * 		These parameters define the source
	 * @param string $wikiText
	 * 		The wiki text describes the errors or properties of the LSD..
	 * @return mixed TSCSourceDefinition
	 * 		TSCSourceDefinition: if all parameters are valid
	 * 		null: Some parameters are invalid
	 */
	private function createLSDFromParams(array $params, &$wikiText) {
		$errMsg = array();
		$wikiTextMsg = array();
		
		// ID: mandatory
		$id = $this->retrieveParam($params, TSCLanguage::PFP_LSD_ID, 1, 1, $errMsg);
		if (is_null($id)) {
			$id = "dummy";
		} else {
			$wikiTextMsg[] = array(wfMsgForContent('tsc_lsd_id'), array($id));
		}
		$lsd = new TSCSourceDefinition($id);
		
		// Description of the parameters in a TSCSourceDefinition
		// 0 - Language ID of the parameter name
		// 1 - minimum expected values
		// 2 - maximum expected values
		// 3 - Name of the setter function in TSCSourceDefinition
		$paramDescr = array(
			array(TSCLanguage::PFP_LSD_DESCRIPTION, 			0,  1, "setDescription", 			"tsc_lsd_description"),
			array(TSCLanguage::PFP_LSD_LABEL, 					0,  1, "setLabel", 					"tsc_lsd_label"),
			array(TSCLanguage::PFP_LSD_HOMEPAGE, 				0,  1, "setHomepage", 				"tsc_lsd_homepage"),
			array(TSCLanguage::PFP_LSD_SAMPLEURI, 				0, -1, "setSampleURIs", 			"tsc_lsd_sampleuri"),
			array(TSCLanguage::PFP_LSD_SPARQLENDPOINTLOCATION,	0,  1, "setSparqlEndpointLocation",	"tsc_lsd_sparqlendpointlocation"),
			array(TSCLanguage::PFP_LSD_SPARQLGRAPHNAME, 		0,  1, "setSparqlGraphName", 		"tsc_lsd_sparqlgraphname"),
			array(TSCLanguage::PFP_LSD_SPARQLGRAPHPATTERN, 		0, -1, "setSparqlGraphPatterns", 	"tsc_lsd_sparqlgraphpattern"),
			array(TSCLanguage::PFP_LSD_DATADUMPLOCATION, 		0, -1, "setDataDumpLocations", 		"tsc_lsd_datadumplocation"),
			array(TSCLanguage::PFP_LSD_LASTMOD, 				0,  1, "setLastMod", 				"tsc_lsd_lastmod"),
			array(TSCLanguage::PFP_LSD_CHANGEFREQ, 				0,  1, "setChangeFreq",				"tsc_lsd_changefreq"),
			array(TSCLanguage::PFP_LSD_VOCABULARY, 				0, -1, "setVocabularies", 			"tsc_lsd_vocabulary"),
			array(TSCLanguage::PFP_LSD_PREDICATETOCRAWL, 		0, -1, "setPredicatesToCrawl",		"tsc_lsd_predicatetocrawl"),
                        array(TSCLanguage::PFP_LSD_LEVELSTOCRAWL, 		0, -1, "setLevelsToCrawl",		"tsc_lsd_levelstocrawl"),
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
		
		$title = wfMsgForContent("tsc_lsdparser_title");
		
		$wikiText = "==$title $id==\n\n";
		
		$wikiText .= empty($errMsg) 
						? wfMsgForContent("tsc_lsdparser_success")
						: wfMsgForContent("tsc_lsdparser_failed");
						
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
			$wikiText .= wfMsgForContent("tsc_lsdparser_error_intro");
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
		global $tscgContLang;
		$pfp = $tscgContLang->getParserFunctionParameter($pfpID);
		
		$value = @$params[$pfp];
		if (!isset($value)) {
			// no value for this parameter
			if ($min > 0) {
				// expected at least one value
				$errMsg[] = wfMsgForContent("tsc_lsdparser_expected_at_least", $min, $pfp);
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
								? wfMsgForContent("tsc_lsdparser_expected_at_least", $min, $pfp)
								: ($min == $max) 
									? wfMsgForContent("tsc_lsdparser_expected_exactly", $min, $pfp)
									: wfMsgForContent("tsc_lsdparser_expected_between", $min, $max, $pfp);
				return null;								
			}
		} else {
			// A single value is given
			if ($min > 1 || ($max < 1 && $max != -1)) {
				// expected at least one value
				if ($max == -1) {
					$max = '*';
				}
				$errMsg[] = wfMsgForContent("tsc_lsdparser_expected_between", $min, $max, $pfp);
				return null;
			}
			
		}
		return $value;
		
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
