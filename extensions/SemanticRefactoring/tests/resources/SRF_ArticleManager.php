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
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

require_once('SRF_TestDeleteCategoryArticles.php');
require_once('SRF_TestDeletePropertyArticles.php');
require_once('SRF_TestRenamePropertyArticles.php');
require_once('SRF_TestRenameCategoryArticles.php');
require_once('SRF_TestRenameInstanceArticles.php');
require_once('SRF_TestChangeValueArticles.php');
require_once('SRF_TestChangeTemplateArticles.php');
require_once('SRF_TestChangeCategoryArticles.php');

/**
 * This file contains common classes for all test cases.
 * 
 * @author Thomas Schweitzer
 * Date: 02.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

 //--- Includes ---

/**
 * 
 * This class creates and deletes articles that are used in test case.
 * 
 * @author Thomas Schweitzer
 * 
 */
class ArticleManager {
    
    //--- Constants ---
    //--- Private fields ---
    // List of articles that were added during a test.
    private $mAddedArticles = array();

    
    
        
    /**
     * Constructor for  ArticleManager
     *
     */     
    function __construct() {
    }
    

    //--- getter/setter ---
    public function getAddedArticles()  { return $this->mAddedArticles; }
    
    //--- Public methods ---
    
    /**
     * Adds the article with the name $articleName for later deletion.
     * 
     * @param string $articleName
     *  Full name of the article
     */
    public function addArticle($articleName) {
        $this->mAddedArticles[] = $articleName;
    }
    
    /**
     * Creates articles as a given user. The names of all created articles are 
     * stored so that they can be deleted later.
     * 
     * @param array(string => string) $articles
     *      A map from article names to their content.
     * @param string $user
     *      Name of the user who will create the articles
     * @param array(string) $orderOfArticles
     *      If specified, the articles are created in the order given by the 
     *      article names in this array.
     */
    public function createArticles($articles, $orderOfArticles = null) {
       
        if (!is_null($orderOfArticles)) {
            foreach ($orderOfArticles as $name) {
                $this->createArticle($name, $articles[$name]);
                $this->mAddedArticles[] = $name;
            }
        } else {
            foreach ($articles as $name => $content) {
                //              $pf = HACLParserFunctions::getInstance();
                //              $pf->reset();
                $this->createArticle($name, $content);
                $this->mAddedArticles[] = $name;
            }
        }
    }
    
        
    /**
     * Delete all articles that were created during a test.
     */
    function deleteArticles() {
       
        
        foreach ($this->mAddedArticles as $a) {
            $t = Title::newFromText($a);
            $wgOut->setTitle($t); // otherwise doDelete() will throw an exception
            $article = new Article($t);
            $article->doDelete("Testing");
        }
        $this->mAddedArticles = array();
    }
    
    /**
     * Checks if the article with the given name $title exists.
     * 
     * @param string $title
     *      Name of the article.
     * @return
     *      <true>, if the article exists
     *      <false> otherwise
     */
    public static function articleExists($title) {
        $title = Title::newFromText($title);
        return $title->exists();
    }
    

    //--- Private methods ---
    /**
     * Creates the article with the name $title and the given $content.
     * @param string $title
     *      Name of the article
     * @param string $content
     *      Content of the article
     */
    private function createArticle($title, $content) {
    
        global $wgTitle;
        $wgTitle = Title::newFromText($title);
        $article = new Article($wgTitle);
        
        // Set the article's content
        $status = $article->doEdit($content, 'Created for test case', 
                                    $article->exists() ? EDIT_UPDATE : EDIT_NEW);
        if (!$status->isOK()) {
            echo "Creating article ".$wgTitle->getFullText()." failed\n";
        }
    }
    
}

