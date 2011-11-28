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
 * This class handles the auto-completion mechanism in case of a TSC in quad mode.
 *
 * @author Kai Kühn
 *
 */
class AutoCompletionStorageTSCQuad extends AutoCompletionStorageSQL2 {

    public  function runASKQuery($rawquery, $userInput, $column = "_var0") {

        global $smwgResultFormats, $smwgHaloIP;
        require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";
        $smwgResultFormats['xml'] = 'SMWXMLResultPrinter';

        // add query as first rawparam

        $rawparams[] = $rawquery;
        if ($column != "_var0") $rawparams[] = "?$column";

        // parse params and answer query
        SMWSPARQLQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
        $params['format'] = "xml";
        $params['limit'] = 400;
        if ($column != "_var0") $params['sort'] = $column;
        $querystring = str_replace("{{USERINPUT}}", $userInput, $querystring);
        return SMWSPARQLQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_FILE);

    }


    //TODO: This is not efficient. Must be replaced by another implementation
    // Ideally, the SOLR server should handle this
    public function getPages($match, $namespaces = NULL) {
        $client = TSConnection::getConnector();
        $client->connect();


        if ($namespaces == NULL || count($namespaces) == 0) {

            $response = $client->query("SELECT DISTINCT ?s WHERE { GRAPH ?G {  ?s ?p ?o. FILTER(regex(str(?s), \"#[^/:#]*$match\",\"i\")) } }",  "limit=".SMW_AC_MAX_RESULTS);

        } else {
            $tsn = TSNamespaces::getInstance();
            $filter = "";
            $first = true;
            for ($i = 0, $n = count($namespaces); $i < $n; $i++) {
                $namespaceText = $tsn->getNSPrefix($namespaces[$i]);
                if (!$first) {
                    $filter .= " UNION ";
                }
                $first = false;
                $filter .= " { GRAPH ?G { ?s ?p ?o. FILTER( regex(str(?s),\"/$namespaceText#[^/:#]*$match\",\"i\") ) } } ";
            }
            $response = $client->query("SELECT DISTINCT ?s WHERE { $filter }",  "limit=".SMW_AC_MAX_RESULTS);
        }
        $result = $this->parseSPARQLResults($response);
            

        return $result;
    }

    protected function parseSPARQLResults($response) {
        $dom = simplexml_load_string($response);
        $dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
        $result = array();
        $results = $dom->xpath('//sparqlxml:result');
        foreach ($results as $r) {

            $children = $r->children(); // binding nodes
            $b = $children->binding[0]; // predicate

            $sv = $b->children()->uri[0];

            if (!is_null($sv) && $sv !== '') {
                $title = TSHelper::getTitleFromURI((string) $sv);
                if (is_null($title)) {

                    continue;
                }
                $extraData = ($title->getNamespace() == SMW_NS_PROPERTY) ? $this->getPropertyData($title) : NULL;
                $result[] = array('title'=>$title, 'inferred'=>false, 'pasteContent'=>NULL, 'schemaData'=>$extraData);
            } else {
                $sv = $b->children()->literal[0];
                $result[] = array((string) $sv, false);
            }

        }

        return $result;
    }


    public function getPropertyForInstance($userInputToMatch, $instance, $matchDomainOrRange) {
        $client = TSConnection::getConnector();
        $client->connect();
        $tsn = TSNamespaces::getInstance();
        $instance_iri = $tsn->getFullIRI($instance);

        $pos = $matchDomainOrRange ? 0 : 1;

        $response = $client->query("SELECT DISTINCT ?p WHERE { GRAPH ?G {  $instance_iri rdf:type ?c. ?p prop:Has_domain_and_range ?blank1 . ?blank1 prop:$pos ?c . ".
                                   " FILTER(regex(str(?p), \"/property#[^/:#]*$userInputToMatch\",\"i\")) } }",  "limit=".SMW_AC_MAX_RESULTS);

        $result = array();
        $this->parseSPARQLResults($response, $result);
        return $result;
    }


    public function getPropertyForAnnotation($userInputToMatch, $category) {
        $client = TSConnection::getConnector();
        $client->connect();
        $tsn = TSNamespaces::getInstance();
        $category_iri = $tsn->getFullIRI($category);

        $response = $client->query("SELECT DISTINCT ?p WHERE { GRAPH ?G { ?s rdf:type $category_iri. ?s ?p ?o . ".
                                   " FILTER(regex(str(?p), \"/property#[^/:#]*$userInputToMatch\",\"i\")) } }",  "limit=".SMW_AC_MAX_RESULTS);

        $result = array();
        $this->parseSPARQLResults($response, $result);
        return $result;
    }

    public function getValueForAnnotation($userInputToMatch, $property) {
        $client = TSConnection::getConnector();
        $client->connect();
        $tsn = TSNamespaces::getInstance();
        $property_iri = $tsn->getFullIRI($property);


        $response = $client->query("SELECT DISTINCT ?v WHERE { GRAPH ?G {  ?s $property_iri ?v. ".
                                   " FILTER(regex(str(?v), \"$userInputToMatch\",\"i\")) } }",  "limit=".SMW_AC_MAX_RESULTS);

        $result = array();
        $this->parseSPARQLResults($response, $result);
        return $result;

    }


    public function getInstanceAsTarget($userInputToMatch, $domainRangeAnnotations) {
        $client = TSConnection::getConnector();
        $client->connect();
        $tsn = TSNamespaces::getInstance();

        $first = true;
        $constraint = "";
        global $smwgHaloContLang;
        $ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
        
        foreach($domainRangeAnnotations as $dr) {
           $sd = $dr->getSemanticData();

            $range = $sd->getPropertyValues(SMWDIProperty::newFromUserLabel($ssp[SMW_SSP_HAS_RANGE]));
            $rangeDi = reset($range);
            if (is_null($rangeDi) || $rangeDi === false) continue;
            $category_iri = $tsn->getFullIRI($rangeDi->getTitle());
            if (!$first) {
                $constraint .= " UNION ";
            }
            $constraint = "{ GRAPH ?G { ?s rdf:type $category_iri . ".
                                   " FILTER(regex(str(?s), \"#[^/:#]*$userInputToMatch\",\"i\")) } }";
            $first = false;
        }

        $response = $client->query("SELECT DISTINCT ?s WHERE { $constraint }",  "limit=".SMW_AC_MAX_RESULTS);

        $result = array();
        $this->parseSPARQLResults($response, $result);
        return $result;
    }


    public function getImageURL($categoryTitle) {
        static $image_urls = array();

        if (is_null($categoryTitle)) return NULL;

        if (array_key_exists($categoryTitle->getPrefixedDBkey(), $image_urls)) {
            return $image_urls[$categoryTitle->getPrefixedDBkey()];
        }
        $catHasIconPropertyDi = SMWDIProperty::newFromUserLabel(wfMsg('smw_ac_category_has_icon'));
        $iconValues = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($categoryTitle), $catHasIconPropertyDi, NULL, '', true);
        $iconValue = reset($iconValues); // consider only first
        if ($iconValue === false) return NULL;
        
        $im_file = wfLocalFile($iconValue->getTitle());
        $url = !is_null($im_file) && $im_file instanceof File ? $im_file->getURL(): NULL;

        if (!is_null($url)) {
            $image_urls[$categoryTitle->getPrefixedDBkey()] = $url;
        }

        return $url;
    }

}
