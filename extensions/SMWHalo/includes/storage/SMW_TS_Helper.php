<?php
/**
 * @file
 * @ingroup SMWHaloTriplestore
 *
 * @author kai
 *
 */
class WikiTypeToXSD {

	/**
	 * Map primitve types or units to XSD values
	 *
	 * @param unknown_type $wikiTypeID
	 * @return unknown
	 */
	public static function getXSDType($wikiTypeID) {
		switch($wikiTypeID) {

			// direct supported types
			case '_str' : return 'xsd:string';
			case '_txt' : return 'xsd:string';
			case '_num' : return 'xsd:double';
			case '_boo' : return 'xsd:boolean';
			case '_dat' : return 'xsd:dateTime';

			// not supported by TS. Take xsd:string
			case '_geo' :
			case '_cod' : return 'xsd:string';

			case '_tel' :
			case '_ema' :
			case '_uri' :
			case '_anu' : return 'xsd:anyURI';

			// single unit type in SMW
			case '_tem' : return 'tsctype:unit';

			//only relevant for schema import
			case '_wpc' :
			case '_wpf' :
			case '_wpp' :
			case '_wpg' : return 'tsctype:page' ;

			case '_rec' : return 'tsctype:record';

			// unknown or composite type
			default:
				if (is_null($wikiTypeID)) return "xsd:string";
				// if builtin (starts with _) then regard it as string
				if (substr($wikiTypeID, 0, 1) == '_') return "xsd:string";
				// if n-ary, regard it as string
				if (preg_match('/\w+(;\w+)+/', $wikiTypeID) > 0) return "xsd:string";
				// otherwise assume a unit
				return 'tsctype:unit';
		}

	}

	public static function isPageType($wikiType) {
		switch($wikiType) {
			//only relevant for schema import
			case '_wpc' :
			case '_wpf' :
			case '_wpp' :
			case '_wpg' : return true;
		}
		return false;
	}

	/**
	 * Translates XSD-URIs to wiki datatype IDs.
	 * @param $xsdURI
	 * @return unknown_type
	 */
	public static function getWikiType($xsdURI) {
		$hashIndex = strpos($xsdURI, '#');
		if ($hashIndex !== false) {
			$xsdURI = substr($xsdURI, $hashIndex+1);
		}
		switch($xsdURI) {
			case 'string': return "_str";
			case 'float': return "_num";
			case 'double': return "_num";
			case 'boolean': return "_boo";
			case 'dateTime': return "_dat";
			case 'anyURI': return "_uri";
			default: return "_str";

		}
	}


}

class TSHelper {

	public static function guessLocalName($uri) {
		if (strpos($uri, "http://") !== false) $uri = substr($uri, 8);

		$lastSlash = strrpos($uri, "/");
		if ($lastSlash == false) {
			$lastHash = strrpos($uri, "#");
			$localname = substr($uri, $lastHash+1);
		} else {
			$localname = substr($uri, $lastSlash+1);
		}
		return $localname;
	}
	
	/**
	 * Converts a URI into a Title object.
	 * 
	 * If $forceTitle is true, a title object is always returned, even
	 * in cases where the URI could not be converted because it matches no wiki URI
	 * or a localname could not be found.
	 * 
	 * If $forceTitle is false, the URI is returned unchanged in these cases.
	 * 
	 * @param string $sv URI
	 * @param boolean $forceTitle
	 * 
	 * @return Title
	 */
	public static function getTitleFromURI($sv, $forceTitle = true) {
        
		if (is_null($sv)) {
			// URI is null
			if ($forceTitle) {
				return Title::newFromText("empty URI", NS_MAIN);
			}
			return NULL;
		}
		
		// check if it is a wiki URI
		foreach (TSNamespaces::$ALL_NAMESPACES as $nsIndsex => $ns) {
			if (stripos($sv, $ns) === 0) {
				$local = substr($sv, strlen($ns));
				return Title::makeTitle($nsIndsex, $local);

			}
		}

		// check if it is an unknown namespace (superfluous now?)
		if (stripos($sv, TSNamespaces::$UNKNOWN_NS) === 0) {


			$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
			$length = strrpos($sv, "/") - $startNS;
			$ns = intval(substr($sv, $startNS, $length));

			$local = substr($sv, strrpos($sv, "/")+1);

			return Title::makeTitle($ns, $local);

		} else {
			
			// any other URI
			if ($forceTitle) {
				if (strpos($sv, "obl:") === 0) {
					// function term on OBL
					$local = TSHelper::convertOBLFunctionalTerm($sv);
				} else if (strpos($sv, "#") !== false) {
					// consider part after # as localname
					$local = substr($sv, strpos($sv, "#")+1);
				} else if (strrpos($sv, "/") !== false) {
					// consider part after / as localname
					$local = substr($sv, strrpos($sv, "/")+1);
				} 
				// make sure to return a Title
				return Title::newFromText("not interpretable URI", NS_MAIN);
			} else {
				// return URI unchanged.
				return $sv;
			}
		}


	}

	public static function convertOBLFunctionalTerm($uri) {
		$uri = urldecode($uri);
		// echo $uri;die();
		preg_match('/\(([^)]*)\)/',$uri, $matches);
		if (count($matches) > 1) {
			$uri = $matches[1];
			$arguments = explode(",", $uri);
			for($i = 0; $i < count($arguments); $i++) {
				$a = $arguments[$i];
				if (strpos($a,":") !== false) {
					// could be URI
					if (strpos($a, "#") !== false) {
						$local = substr($a, strpos($a, "#")+1);
					} else if (strrpos($a, "/") !== false) {
						$local = substr($a, strrpos($a, "/")+1);
					}
					$a = $local;
				}
				$a = self::eliminateTitleCharacters($a);
				$arguments[$i]= $a;

			}
			$uri = implode("_", $arguments);
		} else {
			$uri = self::eliminateTitleCharacters($uri);
		}
			
		return $uri;
	}

	private static function eliminateTitleCharacters($uri) {
		$uri = str_replace("#", "_", $uri);
		$uri = str_replace("(", "_", $uri);
		$uri = str_replace(")", "_", $uri);
		$uri = str_replace(",", "_", $uri);
		$uri = str_replace("\"", "_", $uri);
		$uri = str_replace("'", "_", $uri);
		$uri = str_replace("^", "_", $uri);
		$uri = str_replace("<", "_", $uri);
		$uri = str_replace(">", "_", $uri);
		$uri = str_replace(":", "_", $uri);
		$uri = str_replace("%", "_", $uri);
		$uri = str_replace(" ", "_", $uri);
		$uri = preg_replace('/__+/', "_", $uri);
		$uri = str_replace("_", " ", $uri);
		$uri = trim($uri);
		$uri = str_replace(" ", "_", $uri);
		return $uri;
	}

	public static function isLocalURI($uri) {
		foreach (TSNamespaces::$ALL_NAMESPACES as $nsIndsex => $ns) {
			if (stripos($uri, $ns) === 0) {
				$local = substr($uri, strlen($ns));
				return true;

			}
		}
		if (stripos($uri, TSNamespaces::$UNKNOWN_NS) === 0) return true;

		return false;
	}

	/**
	 * Returns a local URL and Title object if the given URI matches the wiki graph.
	 * Otherwise the URI is returned unchanged an its localname is used to create a Title object.
	 *
	 * @param string $uri
	 * @return tuple($url, Title)
	 */
	public static function makeLocalURL($uri) {
		global $smwgTripleStoreGraph;

		$title = self::getTitleFromURI($uri);
		if (stripos($uri, $smwgTripleStoreGraph) === 0) {
			$uri = $title->getFullURL();
		}

		return array($uri, $title);
	}

	/**
	 * Returns a local URI for the wiki graph of a given Title object.
	 *
	 * @param Title $title
	 * @return string $uri
	 */
	public static function getUriFromTitle($title) {
		global $smwgTripleStoreGraph;
		$res= $smwgTripleStoreGraph;
		if (strpos($res, -1) != '/')
		$res .= '/';
		$res .= TSNamespaces::getInstance()->getNSPrefix($title->getNamespace())
		.'/'
		.str_replace(' ', '_', $title->getText());
		return $res;
	}

	/**
	 * Escapes double quotes, backslash and line feeds for a SPARUL string literal.
	 *
	 * @param string $literal
	 * @return string
	 */
	public static function escapeForStringLiteral($literal) {
		return str_replace(array("\\", "\"", "\n", "\r"), array("\\\\", "\\\"", "\\n" ,"\\r"), $literal);
	}

	/**
	 * Set metadata values (if available)
	 *
	 *
	 * @param SMWDataValue $v
	 * @param array of SimpleXMLElement $metadata
	 */
	public static function setMetadata(SMWDataValue $v, $metadata) {
		if (!is_null($metadata) && $metadata !== '') {
			foreach($metadata as $m) {
				$name = (string) $m->attributes()->name;
				$datatype = (string) $m->attributes()->datatype;
				$mdValues = array();
				foreach($m->value as $mdValue) {
					$mdValues[] = (string) $mdValue;
				}
				$v->setMetadata($name, $datatype, $mdValues);
			}
		}
	}

}


class TSNamespaces {

	// W3C namespaces
	public static $RDF_NS = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	public static $OWL_NS = "http://www.w3.org/2002/07/owl#";
	public static $RDFS_NS = "http://www.w3.org/2000/01/rdf-schema#";
	public static $XSD_NS = "http://www.w3.org/2001/XMLSchema#";
	public static $TSCTYPE_NS = "http://www.ontoprise.de/smwplus/tsc/unittype#";
	public static $HALOPROP_NS = "http://www.ontoprise.de/smwplus/tsc/haloprop#";

	// collections of namespaces
	public static $ALL_NAMESPACES;
	public static function getAllNamespaces() { return self::$ALL_NAMESPACES; }
	public static $ALL_PREFIXES;
	public static function getAllPrefixes() { return self::$ALL_PREFIXES; }
	public static $W3C_PREFIXES;
	public static function getW3CPrefixes() { return self::$W3C_PREFIXES; }
	public static $TSC_PREFIXES;
	public static function getTSCPrefixes() { return self::$TSC_PREFIXES; }


	// general namespace suffixes for different namespaces
	public static $UNKNOWN_NS;
	public static $UNKNOWN_NS_SUFFIX = "ns_"; // only fragment. / is missing!

	// MW + SMW + SF namespaces (including talk namespaces)
	public static $ALL_NAMESPACE_KEYS = array(NS_CATEGORY, SMW_NS_PROPERTY,SF_NS_FORM, SMW_NS_CONCEPT, NS_MAIN ,
	SMW_NS_TYPE,NS_FILE, NS_HELP, NS_TEMPLATE, NS_USER, NS_MEDIAWIKI, NS_PROJECT,	SMW_NS_PROPERTY_TALK,
	SF_NS_FORM_TALK,NS_TALK, NS_USER_TALK, NS_PROJECT_TALK, NS_FILE_TALK, NS_MEDIAWIKI_TALK,
	NS_TEMPLATE_TALK, NS_HELP_TALK, NS_CATEGORY_TALK, SMW_NS_CONCEPT_TALK, SMW_NS_TYPE_TALK);

	public static $EMPTY_SPARQL_XML = '<?xml version="1.0"?><sparql></sparql>';
	public static $DEFAULT_VALUE_URI = 'http://__defaultvalue__/doesnotexist';

	public static $initialized = false;
	private static $INSTANCE = NULL;


	public static function getInstance() {
		if (is_null(self::$INSTANCE)) {
			self::$INSTANCE = new TSNamespaces();
		}
		return self::$INSTANCE;
	}

	function __construct() {
		global $smwgTripleStoreGraph, $smwgDefaultStore, $smwgBaseStore, $wgContLang, $wgExtraNamespaces;

		// use initialize flag because PHP classes do not have static initializers.
		if (self::$initialized) return;

		self::$UNKNOWN_NS = $smwgTripleStoreGraph.self::$UNKNOWN_NS_SUFFIX;

		// SET $ALL_PREFIXES constant
		// add W3C namespaces
		self::$ALL_PREFIXES = "PREFIX xsd:<".self::$XSD_NS."> \nPREFIX owl:<".self::$OWL_NS."> \nPREFIX rdfs:<".
		self::$RDFS_NS."> \nPREFIX rdf:<".self::$RDF_NS."> ";

		// add all namespaces (including talk namespaces)
		global $wgContLang;

		$extraNamespaces = array_diff(array_keys($wgExtraNamespaces), self::$ALL_NAMESPACE_KEYS);
		self::$ALL_NAMESPACE_KEYS = array_merge(self::$ALL_NAMESPACE_KEYS, $extraNamespaces);

		foreach(self::$ALL_NAMESPACE_KEYS as $nsKey) {
			$nsText = $wgContLang->getNSText($nsKey);
			if ($nsKey == NS_MAIN) {
			    $prefix = "a";
			    $nsText = "a";
			} else if ($nsKey == NS_PROJECT) {
				$prefix = "wiki";
			} else if ($nsKey == NS_PROJECT_TALK) {
                $prefix = "wiki_talk";
            } else {
				$prefix = str_replace(" ","_",strtolower($nsText));
			}
			if (empty($prefix)) continue;
			
			// check for validity of prefix
			preg_match('/\w([\w_0-9-]|\.[\w_0-9-])*/', $prefix, $matches);
			if (isset($matches[0]) && $matches[0] != $prefix) continue;
			
			$nsText = str_replace(" ","_",strtolower($nsText));
			$uri = $smwgTripleStoreGraph."/$nsText/";
			self::$ALL_PREFIXES .= "\nPREFIX $prefix:<$uri> ";
			self::$ALL_NAMESPACES[$nsKey] = $uri;
		}

		// add special prefixes "cat" and "prop" for compatibility with < SMWHalo 1.5.2
		self::$ALL_PREFIXES .= "\nPREFIX cat:<".$smwgTripleStoreGraph."/".str_replace(" ","_",strtolower($wgContLang->getNSText(NS_CATEGORY))).'/> '.
							   "\nPREFIX prop:<".$smwgTripleStoreGraph."/".str_replace(" ","_",strtolower($wgContLang->getNSText(SMW_NS_PROPERTY))).'/> ';

		// SET $W3C_PREFIXES constant
		self::$W3C_PREFIXES = 'PREFIX xsd:<'.self::$XSD_NS.'> PREFIX owl:<'.self::$OWL_NS.'> PREFIX rdfs:<'.
		self::$RDFS_NS.'> PREFIX rdf:<'.self::$RDF_NS.'> ';

		// SET $TSC_PREFIXES constant
		self::$TSC_PREFIXES = "PREFIX tsctype:<".self::$TSCTYPE_NS."> ";
		self::$TSC_PREFIXES .= "PREFIX haloprop:<".self::$HALOPROP_NS."> ";


	}

	/**
	 * Returns the namespace prefix used by the triplestore.
	 *
	 * @param int $namespace
	 * @return string
	 */
	public function getNSPrefix($namespace) {
		global $wgContLang;
		if ($namespace == NS_MAIN) return "a";
		return str_replace(" ","_",strtolower($wgContLang->getNSText($namespace)));
	}

	/**
	 * Returns the NS URI (without local name)
	 *
	 * @param int $namespace index
	 */
	public function getNSURI($namespace) {
		global $smwgTripleStoreGraph;
		return $smwgTripleStoreGraph."/".$this->getNSPrefix($namespace)."/";
	}

	/**
	 * Returns the full IRI used by the TS for a namespace index and a localname.
	 *
	 * @param int $namespace
	 * @param string $localname
	 */
	public function getFullIRIByName($namespace, $localname) {
		global $smwgTripleStoreGraph;
		$localname = str_replace(" ", "_", $localname);
		return "<".$smwgTripleStoreGraph."/".$this->getNSPrefix($namespace)."/$localname>";
	}

	/**
	 * Returns the full IRI used by the TS for $t
	 *
	 * @param Title $t
	 */
	public function getFullIRI(Title $t) {
		global $smwgTripleStoreGraph;
		return "<".$smwgTripleStoreGraph."/".$this->getNSPrefix($t->getNamespace())."/".$t->getDBkey().">";
	}

	/**
	 * Returns the full URI used by the TS for $t
	 *
	 * @param Title $t
	 */
	public function getFullURI(Title $t) {
		global $smwgTripleStoreGraph;
		return $smwgTripleStoreGraph."/".$this->getNSPrefix($t->getNamespace())."/".$t->getDBkey();
	}

	/**
	 * Returns the full IRI used by the TS for $p
	 *
	 * @param SMWPropertyValue $t
	 */
	public function getFullIRIFromProperty(SMWPropertyValue $p) {
		global $smwgTripleStoreGraph;
		return "<".$smwgTripleStoreGraph."/".$this->getNSPrefix(SMW_NS_PROPERTY)."/".$p->getDBkey().">";
	}

	/**
	 * Converts prefix from into full URI form
	 *
	 * @param string $prefixForm
	 */
	public function prefix2FullURI($prefixForm) {
		$lastSlashIndex = strrpos($prefixForm, "/");
		if ( $lastSlashIndex === false) return $prefixForm;
		$prefix = substr($prefixForm, 0, $lastSlashIndex+1);
		$local = substr($prefixForm, $lastSlashIndex);

		$local = ucfirst($local);

		foreach(self::$ALL_NAMESPACE_KEYS as $nsKey) {
			$suffix = $this->getNSPrefix($nsKey);
			if ($suffix == $prefix) {
				return $this>getNSURI($nsKey).$local;
			}
		}

		return $prefixForm;
	}


}


$smwhgTSNamespaces = TSNamespaces::getInstance();