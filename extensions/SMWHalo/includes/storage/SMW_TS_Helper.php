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
            case '_cod' :
            case '_ema' :
            case '_uri' :
            case '_anu' : return 'xsd:string';

            // single unit type in SMW
            case '_tem' : return 'xsd:unit';

            //only relevant for schema import
            case '_wpc' :
            case '_wpf' :
            case '_wpp' :
            case '_wpg' : return 'cat:DefaultRootCategory';

            // unknown or composite type
            default:
                // if builtin (starts with _) then regard it as string
                if (substr($wikiTypeID, 0, 1) == '_') return "xsd:string";
                // if n-ary, regard it as string
                if (preg_match('/\w+(;\w+)+/', $wikiTypeID) !== false) return "xsd:string";
                // otherwise assume a unit
                return 'xsd:unit';
        }

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
            default: return "_str";

        }
    }
}


class TSNamespaces {
    
	// W3C namespaces
    public static $RDF_NS = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
    public static $OWL_NS = "http://www.w3.org/2002/07/owl#";
    public static $RDFS_NS = "http://www.w3.org/2000/01/rdf-schema#";
    public static $XSD_NS = "http://www.w3.org/2001/XMLSchema#";
    
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

    function __construct() {
    	global $smwgTripleStoreGraph, $smwgDefaultStore, $smwgBaseStore, $wgContLang, $wgExtraNamespaces;
      
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
        SMW_NS_TYPE => self::$TYPE_NS_SUFFIX, NS_IMAGE => self::$IMAGE_NS, NS_HELP => self::$HELP_NS, NS_TEMPLATE => self::$TEMPLATE_NS,
        NS_USER => self::$USER_NS);
        
        // declare all common namespaces as SPARQL PREFIX statement (W3C + standard wiki + SMW)
        self::$ALL_PREFIXES = 'PREFIX xsd:<'.self::$XSD_NS.'> PREFIX owl:<'.self::$OWL_NS.'> PREFIX rdfs:<'.
        self::$RDFS_NS.'> PREFIX rdf:<'.self::$RDF_NS.'> PREFIX cat:<'.self::$CAT_NS.'> PREFIX prop:<'.
        self::$PROP_NS.'> PREFIX a:<'.self::$INST_NS.'> PREFIX type:<'.self::$TYPE_NS.'> PREFIX image:<'.
        self::$IMAGE_NS.'> PREFIX help:<'.self::$HELP_NS.'> PREFIX template:<'.self::$TEMPLATE_NS.'> PREFIX user: <'.self::$USER_NS.'> ';
        
        self::$W3C_PREFIXES = 'PREFIX xsd:<'.self::$XSD_NS.'> PREFIX owl:<'.self::$OWL_NS.'> PREFIX rdfs:<'.
        self::$RDFS_NS.'> PREFIX rdf:<'.self::$RDF_NS.'> ';
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
