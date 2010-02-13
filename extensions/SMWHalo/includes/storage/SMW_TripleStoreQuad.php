<?php
global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/storage/SMW_TripleStore.php" );


/**
 * Triple store connector class.
 *
 * This class is a wrapper around the default SMWStore class. It delegates all
 * read operations to the default implementation. Write operation, namely:
 *
 *  1. updateData
 *  2. deleteSubject
 *  3. changeTitle
 *  4. setup
 *  5. drop
 *
 * are delegated too, but also sent to a MessageBroker supporting the Stomp protocol.
 * All commands are written in the SPARUL(1) syntax.
 *
 * SPARQL queries are sent to the triple store via webservice (SPARQL endpoint). ASK
 * queries are delgated to default SMWStore.
 *
 * (1) refer to http://jena.hpl.hp.com/~afs/SPARQL-Update.html
 *
 * Configuration in LocalSettings.php:
 *
 *  $smwgMessageBroker: The name or IP of the message broker
 *  $smwgWebserviceEndpoint: The name or IP of the SPARQL endpoint (with port if not 80)
 *
 * @author: Kai
 */

class SMWTripleStoreQuad extends SMWTripleStore {



    public static $fullSemanticData;
    private $tsNamespace;


    /**
     * Creates and initializes Triple store connector.
     *
     * @param SMWStore $smwstore All calls are delegated to this implementation.
     */
    function __construct() {
        global $smwgBaseStore;
        $this->smwstore = new $smwgBaseStore;
        $this->tsNamespace = new TSNamespaces();
    }




    /**
     * Add an URI to an array of results
     *
     * @param string $sv A single value
     * @param PrintRequest prs
     * @param array & $allValues
     */
    protected function addURIToResult($uris, $prs, & $allValues) {
   
        foreach($uris as $uri) {
            list($sv, $provenance) = $uri;

            $nsFound = false;
            foreach (TSNamespaces::getAllNamespaces() as $nsIndsex => $ns) {
                if (stripos($sv, $ns) === 0) {
                    $allValues[] = $this->createSMWDataValue($sv, $provenance, $ns, $nsIndsex);
                    $nsFound = true;
                }
            }

            if ($nsFound) continue;

            // result with unknown namespace
            if (stripos($sv, TSNamespaces::$UNKNOWN_NS) === 0) {

                if (empty($sv)) {
                    $v = SMWDataValueFactory::newTypeIDValue('_wpg');
                    if (!is_null($provenance) && $provenance != '' ){
                        $v->setProvenance($provenance);
                    }
                    $allValues[] = $v;
                } else {
                    $startNS = strlen(TSNamespaces::$UNKNOWN_NS);
                    $length = strpos($sv, "#") - $startNS;
                    $ns = intval(substr($sv, $startNS, $length));


                    if (!is_null($provenance) && $provenance != '' && strpos($provenance, "section=") !== false) {
                    	
                        // UP special behaviour: if provenance contains section, use it as fragment identifier
                        $uri_parts = explode("#", $provenance);
                        $local = substr($uri_parts[0], strrpos($uri_parts[0], "/")+1);

                        $sectionIndex = strpos($uri_parts[1], "section=");
                        $section = substr($uri_parts[1], $sectionIndex, strpos($uri_parts[1], "&", $sectionIndex));

                        $sections_parts = explode("=", $section);
                        $section_name = urldecode($sections_parts[1]);
                        $section_name = preg_replace('/\{\{[^}]*\}\}/', '', $section_name);
                        $section_name = str_replace("'","",$section_name);
                        $title = Title::makeTitle(0, $local, $section_name);
                    } else {
                        $local = substr($sv, strpos($sv, "#")+1);
                        $title = Title::newFromText($local, $ns);
                    }
                    if (is_null($title)) {
                        $title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
                    }
                    $v = SMWDataValueFactory::newTypeIDValue('_wpg');
                    $v->setValues($title->getDBkey(), $ns, $title->getArticleID(), false, '', $title->getFragment());
                    if (!is_null($provenance) && $provenance != '' ) {
                        $v->setProvenance($provenance);
                    }
                    $allValues[] = $v;
                }
            } else {
                // external URI

                $v = SMWDataValueFactory::newTypeIDValue('_uri');
                $v->setXSDValue($sv);
                if (!is_null($provenance) && $provenance != '') {
                    $v->setProvenance($provenance);
                }
                $allValues[] = $v;

            }
        }

    }

    /**
     * Add a literal to an array of results
     *
     * @param string $sv A single value
     * @param PrintRequest prs
     * @param array & $allValues
     */
   

    /**
     * Creates  SWMDataValue object from a (possibly) merged result.
     *
     * @param string $sv
     * @param string $nsFragment
     * @param int $ns
     * @return SMWDataValue
     */
    protected function createSMWDataValue($sv, $provenance, $nsFragment, $ns) {

        if (!is_null($provenance) && $provenance != '' && strpos($provenance, "section=") !== false) {
            // UP special behaviour: if provenance contains section, use it as fragment identifier
            $uri_parts = explode("#", $provenance);
            $local = substr($uri_parts[0], strrpos($uri_parts[0], "/")+1);

            $sectionIndex = strpos($uri_parts[1], "section=");
            $section = substr($uri_parts[1], $sectionIndex, strpos($uri_parts[1], "&", $sectionIndex));

            $sections_parts = explode("=", $section);
            $section_name = urldecode($sections_parts[1]);
            $section_name = preg_replace('/\{\{[^}]*\}\}/', '', $section_name);
            $section_name = str_replace("'","",$section_name);
            $title = Title::makeTitle(0, $local, $section_name);
        } else {
            $local = substr($sv, strlen($nsFragment));
            $title = Title::newFromText($local, $ns);
        }
        if (is_null($title)) {
            $title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
        }
        $v = SMWDataValueFactory::newTypeIDValue('_wpg');
        $v->setValues($title->getDBkey(), $ns, $title->getArticleID(), false, '', $title->getFragment());
        if (!is_null($provenance) && $provenance != '' ){
            $v->setProvenance($provenance);
        }
        return $v;

    }

   
}





