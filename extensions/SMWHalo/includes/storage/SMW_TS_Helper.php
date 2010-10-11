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
			case '_wpg' : return NULL;

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
	public static function getTitleFromURI($sv) {

		foreach (TSNamespaces::$ALL_NAMESPACES as $nsIndsex => $ns) {
			if (stripos($sv, $ns) === 0) {
				$local = substr($sv, strlen($ns));
				return Title::newFromText($local, $nsIndsex);

			}
		}



		// result with unknown namespace
		if (stripos($sv, TSNamespaces::$UNKNOWN_NS) === 0) {


			$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
			$length = strpos($sv, "#") - $startNS;
			$ns = intval(substr($sv, $startNS, $length));

			$local = substr($sv, strpos($sv, "#")+1);

			return Title::newFromText($local, $ns);



		} else {
			// any URI
			if (strpos($sv, "#") !== false) {
				$local = substr($sv, strpos($sv, "#")+1);
			} else if (strrpos($sv, "/") !== false) {
				$local = substr($sv, strrpos($sv, "/")+1);
			} else {
				return NULL;
			}
			return Title::newFromText($local, NS_MAIN);
		}

		return NULL;
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
        $res .= TSNamespaces::getNSPrefix($title->getNamespace())
             .'#'
             .str_replace(' ', '_', $title->getText());
        return $res;        
    }
	
}


class TSNamespaces {

	// W3C namespaces
	public static $RDF_NS = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	public static $OWL_NS = "http://www.w3.org/2002/07/owl#";
	public static $RDFS_NS = "http://www.w3.org/2000/01/rdf-schema#";
	public static $XSD_NS = "http://www.w3.org/2001/XMLSchema#";
	public static $TSCTYPE_NS = "http://www.ontoprise.de/smwplus/tsc/unittype#";

	public static $CAT_NS;
	public static $PROP_NS;
	public static $INST_NS;
	public static $TYPE_NS;
	public static $IMAGE_NS;
	public static $HELP_NS;
	public static $TEMPLATE_NS;
	public static $USER_NS;
	public static $UNKNOWN_NS;

	public static $ALL_NAMESPACES;
	public static function getAllNamespaces() { return self::$ALL_NAMESPACES; }
	public static $ALL_PREFIXES;
	public static function getAllPrefixes() { return self::$ALL_PREFIXES; }
	public static $W3C_PREFIXES;
	public static function getW3CPrefixes() { return self::$W3C_PREFIXES; }
	public static $TSC_PREFIXES;
	public static function getTSCPrefixes() { return self::$TSC_PREFIXES; }
	
	// general namespace suffixes for different namespaces
	public static $CAT_NS_SUFFIX = "/category#";
	public static $PROP_NS_SUFFIX = "/property#";
	public static $INST_NS_SUFFIX = "/a#";
	public static $TYPE_NS_SUFFIX = "/type#";
	public static $IMAGE_NS_SUFFIX = "/image#";
	public static $HELP_NS_SUFFIX = "/help#";
	public static $TEMPLATE_NS_SUFFIX = "/template#";
	public static $USER_NS_SUFFIX = "/user#";
	public static $UNKNOWN_NS_SUFFIX = "/ns_"; // only fragment. # is missing!

	public static $initialized = false;

	public static $EMPTY_SPARQL_XML = '<?xml version="1.0"?><sparql></sparql>';

	function __construct() {
		global $smwgTripleStoreGraph, $smwgDefaultStore, $smwgBaseStore, $wgContLang, $wgExtraNamespaces;

		// use initialize flag because PHP classes do not have static initializers.
		if (self::$initialized) return;
		 
		self::$CAT_NS = $smwgTripleStoreGraph.self::$CAT_NS_SUFFIX;
		self::$PROP_NS = $smwgTripleStoreGraph.self::$PROP_NS_SUFFIX;
		self::$INST_NS = $smwgTripleStoreGraph.self::$INST_NS_SUFFIX;
		self::$TYPE_NS = $smwgTripleStoreGraph.self::$TYPE_NS_SUFFIX;
		self::$IMAGE_NS = $smwgTripleStoreGraph.self::$IMAGE_NS_SUFFIX;
		self::$HELP_NS = $smwgTripleStoreGraph.self::$HELP_NS_SUFFIX;
		self::$TEMPLATE_NS = $smwgTripleStoreGraph.self::$TEMPLATE_NS_SUFFIX;
		self::$USER_NS = $smwgTripleStoreGraph.self::$USER_NS_SUFFIX;
		self::$UNKNOWN_NS = $smwgTripleStoreGraph.self::$UNKNOWN_NS_SUFFIX;

		self::$ALL_NAMESPACES = array(NS_MAIN=>self::$INST_NS, NS_CATEGORY => self::$CAT_NS, SMW_NS_PROPERTY => self::$PROP_NS,
		SMW_NS_TYPE => self::$TYPE_NS, NS_IMAGE => self::$IMAGE_NS, NS_HELP => self::$HELP_NS, NS_TEMPLATE => self::$TEMPLATE_NS,
		NS_USER => self::$USER_NS);

		// declare all common namespaces as SPARQL PREFIX statement (W3C + standard wiki + SMW)
		self::$ALL_PREFIXES = 'PREFIX xsd:<'.self::$XSD_NS.'> PREFIX owl:<'.self::$OWL_NS.'> PREFIX rdfs:<'.
		self::$RDFS_NS.'> PREFIX rdf:<'.self::$RDF_NS.'> PREFIX cat:<'.self::$CAT_NS.'> PREFIX prop:<'.
		self::$PROP_NS.'> PREFIX a:<'.self::$INST_NS.'> PREFIX type:<'.self::$TYPE_NS.'> PREFIX image:<'.
		self::$IMAGE_NS.'> PREFIX help:<'.self::$HELP_NS.'> PREFIX template:<'.self::$TEMPLATE_NS.'> PREFIX user: <'.self::$USER_NS.'> ';

		self::$W3C_PREFIXES = 'PREFIX xsd:<'.self::$XSD_NS.'> PREFIX owl:<'.self::$OWL_NS.'> PREFIX rdfs:<'.
		self::$RDFS_NS.'> PREFIX rdf:<'.self::$RDF_NS.'> ';
		
		self::$TSC_PREFIXES = "PREFIX tsctype:<".self::$TSCTYPE_NS."> "; 
		
		// declare all other namespaces using ns_$index as prefix
		$extraNamespaces = array_diff(array_keys($wgExtraNamespaces), array(NS_CATEGORY, SMW_NS_PROPERTY, SMW_NS_TYPE, NS_IMAGE, NS_HELP, NS_MAIN));
		foreach($extraNamespaces as $nsIndex) {
			$nsText = strtolower($wgContLang->getNsText($nsIndex));
			self::$ALL_PREFIXES .= " PREFIX $nsText:<".$smwgTripleStoreGraph."/ns_$nsIndex#> ";
		}
	}

	/**
	 * Returns the namespace prefix used by the triplestore.
	 *
	 * @param int $namespace
	 * @return string
	 */
	public function getNSPrefix($namespace) {
		if ($namespace == SMW_NS_PROPERTY) return "property";
		elseif ($namespace == NS_CATEGORY) return "category";
		elseif ($namespace == NS_MAIN) return "a";
		elseif ($namespace == SMW_NS_TYPE) return "type";
		elseif ($namespace == NS_IMAGE) return "image";
		elseif ($namespace == NS_TEMPLATE) return "template";
		elseif ($namespace == NS_USER) return "user";
		elseif ($namespace == NS_HELP) return "help";
		else return "ns_$namespace";
	}
	
	/**
	 * Returns the NS URI (without local name)
	 * 
	 * @param int $namespace index 
	 */
    public function getNSURI($namespace) {
        global $smwgTripleStoreGraph;
        return $smwgTripleStoreGraph."/".$this->getNSPrefix($namespace)."#";
    }
    
    /**
     * Returns the full IRI used by the TS for $t
     * 
     * @param Title $t
     */
    public function getFullIRI(Title $t) {
    	global $smwgTripleStoreGraph;
    	return "<".$smwgTripleStoreGraph."/".$this->getNSPrefix($t->getNamespace())."#".$t->getDBkey().">";
    }
    
     /**
     * Returns the full IRI used by the TS for $p
     * 
     * @param SMWPropertyValue $t
     */
 	public function getFullIRIFromProperty(SMWPropertyValue $p) {
    	global $smwgTripleStoreGraph;
    	return "<".$smwgTripleStoreGraph."/".$this->getNSPrefix(SMW_NS_PROPERTY)."#".$p->getDBkey().">";
    }
    
    /**
     * Converts prefix from into full URI form 
     * 
     * @param string $prefixForm
     */
    public function prefix2FullURI($prefixForm) {
    	if (strpos($prefixForm, "#") === false) return $prefixForm;
    	list($prefix, $local) = explode("#", $prefixForm);
    	$local = ucfirst($local);
    	if (self::$CAT_NS_SUFFIX == ("/".$prefix."#")) {
    		return self::$CAT_NS.$local;
    	} else if (self::$PROP_NS_SUFFIX == ("/".$prefix."#")) {
    		return self::$PROP_NS.$local;
    	} else if (self::$INST_NS_SUFFIX == ("/".$prefix."#")) {
    		return self::$INST_NS.$local;
    	} else if (self::$TYPE_NS_SUFFIX == ("/".$prefix."#")) {
    		return self::$TYPE_NS.$local;
    	} else if (self::$IMAGE_NS_SUFFIX == ("/".$prefix."#")) {
    		return self::$IMAGE_NS.$local;
    	} else if (self::$HELP_NS_SUFFIX == ("/".$prefix."#")) {
    		return self::$HELP_NS.$local;
    	} // FIXME: get other namespaces
    	return $prefixForm;
    }

	/**
	 * Create a SPARQL PREFIX statement for unknown namespaces.
	 *
	 * @param string $suffix which serves also as prefix.
	 * @return string
	 */
	public function getUnknownNamespacePrefixes($suffix) {
		if (substr($suffix, 0, 3) == "ns_") {
			global $smwgTripleStoreGraph;
			return " PREFIX $suffix:<$smwgTripleStoreGraph/$suffix#> ";
		}
		return "";
	}
}


$smwhgTSNamespaces = new TSNamespaces();