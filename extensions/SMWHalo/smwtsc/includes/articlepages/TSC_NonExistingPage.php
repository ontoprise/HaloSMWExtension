<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
 *  This file is part of the LinkedData-Extension.
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
 * Insert description here
 *
 * @author Thomas Schweitzer
 * Date: 14.09.2010
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

//--- Includes ---
global $haclgIP;
//require_once("$haclgIP/...");

/**
 * This class is derived from Article and generates content for non-existing pages.
 *
 * @author Thomas Schweitzer
 *
 */
class  TSCNonExistingPage extends Article {

    private $mUri;

    public function TSCNonExistingPage($title, $uri = '') {
        parent::__construct($title);
        $this->mUri = $uri;
    }

    //--- Constants ---

    //--- Private fields ---
    private $mArticleID = 0; // ID of this article


    //--- getter/setter ---

    //--- Public methods ---

    /**
     * This function overwrites Article::getID() and pretends that the article
     * exists while the method view() is being processed.
     */
    public function getID() {
        return $this->mArticleID;
    }

    /**
     * This method is called when an article will be displayed. In this phase the
     * articles content is fetched. This class replaces the content of non-existing
     * pages, which would normally be an error message. Unfortunately, replacing
     * the content is not enough for non-existing pages. The original method
     * Article::view() first fetches the content and then the error message, if
     * the ID of the article is 0. Because of this, this method temporarily pretends
     * that the ID is not 0.
     */
    public function view() {
        $this->mArticleID = -1;
        parent::view();
        $this->mArticleID = 0;

        // We do not want to show the Semantic Toolbar on non-existing pages
        global $wgHooks;
        $wgHooks['MakeGlobalVariablesScript'][] = "TSCNonExistingPage::hideSemanticToolbar";
    }

    /**
     * Returns the content of non-existing pages.
     *
     * @return string
     *      Content of the page.
     */
    public function getContent() {
        $text = self::getCreateLink($this, $this->mUri);
        $text .= self::getContentOfNEP($this, $this->mUri);
        return $text;
    }

    /**
     * Generates the content of the non-existing page which is specified by
     * $article.
     *
     * @param Article $article
     *      Description of the non-existing page.
     * @return string
     *      The wiki text for the page
     *
     */
    public static function getContentOfNEP(Article $article, $uri = '') {
        $t = $article->getTitle();
        $name = $t->getText();
        $uri = $uri != '' ? $uri : TSHelper::getUriFromTitle($t);
        $ns = $t->getNamespace();

            
        $content = array();
        if ($ns == NS_CATEGORY) {
            // add content for category pages
            self::addCategoryPageContent($content);
        } else if (defined('SMW_NS_PROPERTY') && $ns == SMW_NS_PROPERTY) {
            // add content for property pages
            self::addPropertyPageContent($content);
        } else {
            // add content for all other pages
            self::addCategoryContent($uri, $content);
            self::addCategoryAnnotations($uri, $content);
            self::addGenericContent(false, $content);
        }

        // Replace variables in wiki text
        // $name$ will be replaced by the name of the article
        // $uri" will be replaced by the URI that is associated with the article
        foreach ($content as $key => $wikiText) {
            if (isset($wikiText)) {
                $wikiText = str_replace('$name$', $name, $wikiText);
                $content[$key] = str_replace('$uri$', $uri, $wikiText);
            }
        }


        // Assemble the complete content
        $text = "";
        foreach ($content as $wikiText) {
            $text .= $wikiText;
        }

        return $text;

    }

    /**
     * We do not want to show the Semantic Toolbar on non-existing pages. Add a
     * global javascript variable that suppresses the STB.
     * @param $vars
     *      This array of global variables is enhanced with "wgHideSemanticToolbar"
     */
    public static function hideSemanticToolbar(&$vars) {
        $vars['wgHideSemanticToolbar'] = true;

        return true;
    }

    //--- Private methods ---


    /**
     * Adds the content of the generic template for non-existing pages with the
     * key "Generic" to the array $content.
     *
     * @param boolean $categoryContentAdded
     *      <true>, if content for categories has been added before. In this case
     *      the generic content can possibly be omitted.
     * @param array<string => string> $content
     *      This array contains key/values pairs for the content. This method
     *      adds the content of the generic template with the key "Generic".
     *
     */
    private static function addGenericContent($categoryContentAdded, &$content) {
        global $lodgNEPUseGenericTemplateIfCategoryMember;
        if (!$lodgNEPUseGenericTemplateIfCategoryMember && $categoryContentAdded) {
            // No generic content wanted for items with a type
            return;
        }

        global $lodgNEPGenericTemplate;
        if (!isset($lodgNEPGenericTemplate)) {
            // no template for generic content defined
            return;
        }

        // Get the content of the generic template
        $content["Generic"] = self::getContentOfArticle($lodgNEPGenericTemplate);

    }

    /**
     * Adds the content of the property page template for non-existing property
     * pages (e.g. Property:MyProperty) with the key "PropertyPage" to the array
     * $content.
     *
     * @param array<string => string> $content
     *      This array contains key/values pairs for the content. This method
     *      adds the content of the generic template with the key "PropertyPage".
     *
     */
    private static function addPropertyPageContent(&$content) {
        global $lodgNEPPropertyPageTemplate;
        if (!isset($lodgNEPPropertyPageTemplate)) {
            // no template for property page content defined
            return;
        }

        // Get the content of the property page template
        $content["PropertyPage"] = self::getContentOfArticle($lodgNEPPropertyPageTemplate);

    }

    /**
     * Adds the content of the category page template for non-existing category
     * pages (e.g. Category:MyCategory) with the key "CategoryPage" to the array
     * $content.
     *
     * @param array<string => string> $content
     *      This array contains key/values pairs for the content. This method
     *      adds the content of the generic template with the key "CategoryPage".
     *
     */
    private static function addCategoryPageContent(&$content) {
        global $lodgNEPCategoryPageTemplate;
        if (!isset($lodgNEPCategoryPageTemplate)) {
            // no template for category page content defined
            return;
        }

        // Get the content of the category page template
        $content["CategoryPage"] = self::getContentOfArticle($lodgNEPCategoryPageTemplate);

    }


    /**
     * Adds the categories as annotations. Note: Will not work with LOD.
     *
     * @param string $uri
     *      URI of the Linked Data item that can have types (rdf:type)
     * @param array<string => string> $content
     *      This array contains key/values pairs for the content. This method
     *      adds the content of the category templates with the key
     *      "Category:<category name>".
     *
     */
    private static function addCategoryAnnotations($uri, &$content) {

        // get the categories of the entity

        $categories = self::getCategoriesForURI($uri);
        
        $content['Categories'] = ''; // category string

        // Fetch templates from categories of the entity
        foreach ($categories as $cat) {
            $content['Categories'] .= "\n[[".$cat->getPrefixedText()."]]\n";
        }
    }

    /**
     * Gets all categories of a given page given by URI.
     * 
     * @param string $uri
     * 
     * @return Title []
     */
    private static function getCategoriesForURI($uri) {
    	
    	global $smwgHaloQuadMode, $smwgHaloTripleStoreGraph;
    	
    	if (isset($smwgHaloQuadMode) && $smwgHaloQuadMode == true) {
    		$where = "GRAPH ?G { <$uri> rdf:type ?C . }";
    		$defaultGraph = "";
    	} else {
    		$where = "GRAPH <$smwgHaloTripleStoreGraph> { <$uri> rdf:type ?C . }";
    		$defaultGraph = $smwgHaloTripleStoreGraph;
    	}
    	
        $query = <<<SPARQL
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            SELECT ?C
            WHERE {
                
                $where
                
            }
SPARQL;
      
        $result = self::queryTripleStore($query, $defaultGraph);
        $result = self::parseSparqlXMLResult($result);
        if (!$result) {
            // No categories found for the given URI
            return array();
        }

        $categories = array();
        foreach ($result as $catURI) {
            $t = TSHelper::getTitleFromURI($catURI);
            $categories[] = $t;
        }
        return $categories;
    }

    /**
     * Adds the content of each category template for non-existing pages.
     * The Linked Data item with the URI $uri can have several types that are
     * associated with mediawiki categories. For each of them a template can be
     * defined and its content (wiki text) is added to the array $content.
     *
     * @param string $uri
     *      URI of the Linked Data item that can have types (rdf:type)
     * @param array<string => string> $content
     *      This array contains key/values pairs for the content. This method
     *      adds the content of the category templates with the key
     *      "Category:<category name>".
     *
     */
    private static function addCategoryContent($uri, &$content) {
        global $lodgNEPCategoryTemplatePattern;
        if (!isset($lodgNEPCategoryTemplatePattern)) {
            // no category articles defined
            return;
        }

        // get the categories of the entity

       $categories = self::getCategoriesForURI($uri);
       
        // Fetch templates from categories of the entity
        foreach ($categories as $cat) {
            $catTemplate = str_replace("{cat}", $cat->getText(), $lodgNEPCategoryTemplatePattern);
            $con = self::getContentOfArticle($catTemplate);
            if (!is_null($con)) {
                $content["Category:".$cat->getText()] = $con;
            }
        }
    }

    /**
     * Retrieves the content (wiki text) of the article with the name $articleName.
     * @param string $articleName
     *      Name of the article whose wiki text is retrieved.
     *
     * @return string
     *      Returns the wiki text of the specified article or <null> if the
     *      article does not exist.
     */
    private static function getContentOfArticle($articleName) {
        $titleObj = Title::newFromText($articleName);
        $article = new Article($titleObj);

        return ($article->exists())
        ? $article->getContent()
        : null;

    }

    /**
     * Generates the HTML for a link that creates the article with the displayed
     * content.
     *
     * @param Article $article
     *      The article for which the link is generated.
     * @param string URI of entity this article describes
     */
    private static function getCreateLink(Article $article, $uri = '') {


        $t = $article->getTitle();
        if ($uri == '') {
            $link = $t->getFullUrl(array('action' => 'edit',
                                     'preloadNEP' => 'true',
                                     'mode' => 'wysiwyg'));
        } else {
            $categoriesAsText = array();
            $categories = self::getCategoriesForURI($uri);
            foreach($categories as $c) $categoriesAsText[] = $c->getText();
            if (defined('ASF_VERSION')) {
                $link = ASFFormGeneratorUtils::getCreateNewInstanceLink($article->getTitle()->getPrefixedDBkey(), $categoriesAsText);
            } else {
                $link = false;
            }
            if ($link === false) {
                $link = $t->getFullUrl(array('action' => 'edit',
                                     'preloadNEP' => 'true',
                                     'mode' => 'wysiwyg',
                                     'uri' => $uri));
            } else {
                $queryString = wfArrayToCGI(array('preloadNEP' => 'true',
                                     'mode' => 'wysiwyg',
                                     'uri' => $uri));
                $link .= "&$queryString";
            }
        }
        $name = $t->getFullText();
        $message = wfMsg('lod_nep_link', $name);
        $link = "[$link $message] <br />";
        return $link;

    }

    public static function queryTripleStore($query, $graph = "", $params = null) {
        try {
            $con = TSConnection::getConnector();
            $con->connect();
            $p = "merge=false";
            if (isset($params)) {
                $p .= "|$params";
            }
            $result = $con->query($query, $p, $graph);
            $con->disconnect();
        } catch (Exception $e) {
            // An exception occurred => no result
            return null;
        }

        return $result;
    }

    /**
     * Parses a result of categories
     * @param string SPARQL-XML
     *
     * @return string[] Category URIs
     */
    private static function parseSparqlXMLResult($sparqlXMLResult) {
        $dom = simplexml_load_string($sparqlXMLResult);
        $dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
        if ($dom === FALSE) {
            return null;
        }

        // add all rows to the query result
        $results = $dom->xpath('//sparqlxml:result');
        $categoryURIs = array();
        foreach ($results as $r) {
            $binding = $r->binding;
            $b = $binding[0];

            if (isset($b->uri)) {
                $categoryURIs[] = (string) $b->uri;
            }
        }

        return $categoryURIs;

    }

}