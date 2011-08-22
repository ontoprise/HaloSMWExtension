<?php
/**
 * @author: Kai Kühn / ontoprise / 2011
 *
 * derived from
 * MediaWiki page data importer
 * Copyright (C) 2003,2005 Brion Vibber <brion@pobox.com>
 * http://www.mediawiki.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 */

/**
 * @file
 * @ingroup DFIO
 * 
 * The detector is used to check if an ontology can be imported or if
 * mergings are necessary or if conflicts occur. A merging is required if
 * an ontology element from the same ontology is re-imported. A conflict occurs
 * if two ontology elements from two different ontology are going to have the
 * same name in the wiki.  
 * 
 * The detector is based on MW's XML import mechanism.
 * 
 * @author Kai Kühn / ontoprise / 2011
 *
 */
class DeployWikiImporterDetector extends WikiImporter {
    
    var $result;
    var $ontologyID;
  
    var $mode;
  
    var $logger;

    function __construct($source, $ontologyID, $mode) {
        parent::__construct($source);
        $this->mode = $mode;
     
        $this->ontologyID = $ontologyID;
        
        $this->logger = Logger::getInstance();
        $this->result = array();
    }


    public function getResult() {
        return $this->result;
    }
    
    function in_page( $parser, $name, $attribs ) {

        $name = $this->stripXmlNamespace($name);
        $this->debug( "in_page $name" );
        switch( $name ) {
            case "id":
            case "title":
            case "restrictions":
                $this->appendfield = $name;
                $this->appenddata = "";
                xml_set_element_handler( $parser, "in_nothing", "out_append" );
                xml_set_character_data_handler( $parser, "char_append" );
                break;
            case "revision":
                $this->push( "revision" );
                if( is_object( $this->pageTitle ) ) {
                    $this->workRevision = new DeployWikiRevisionDetector($this->mode, $this->ontologyID);
                    $this->workRevision->setTitle( $this->pageTitle );
                    $this->workRevisionCount++;
                } else {
                    // Skipping items due to invalid page title
                    $this->workRevision = null;
                }
                xml_set_element_handler( $parser, "in_revision", "out_revision" );
                break;
            case "upload":
                $this->push( "upload" );
                if( is_object( $this->pageTitle ) ) {
                    $this->workRevision = new DeployWikiRevisionDetector($this->mode, $this->ontologyID);
                    $this->workRevision->setTitle( $this->pageTitle );
                    $this->uploadCount++;
                } else {
                    // Skipping items due to invalid page title
                    $this->workRevision = null;
                }
                xml_set_element_handler( $parser, "in_upload", "out_upload" );
                break;
            default:
                return $this->throwXMLerror( "Element <$name> not allowed in a <page>." );
        }
    }

    function out_page( $parser, $name ) {
        $name = $this->stripXmlNamespace($name);
        $this->debug( "out_page $name" );
        $this->pop();
        if( $name != "page" ) {
            return $this->throwXMLerror( "Expected </page>, got </$name>" );
        }
        xml_set_element_handler( $parser, "in_mediawiki", "out_mediawiki" );

        $this->pageOutCallback( $this->pageTitle, $this->origTitle,
        $this->workRevisionCount, $this->workSuccessCount );
        
        if (!is_null($this->workRevision) && !is_null($this->workRevision->getResult())) {
        	 $this->result[] = $this->workRevision->getResult();
        }
       
        $this->workTitle = null;
        $this->workRevision = null;
        $this->workRevisionCount = 0;
        $this->workSuccessCount = 0;
        $this->pageTitle = null;
        $this->origTitle = null;
    }
    
    private function push( $name ) {
        array_push( $this->tagStack, $name );
        $this->debug( "PUSH $name" );
    }

    private function pop() {
        $name = array_pop( $this->tagStack );
        $this->debug( "POP $name" );
        return $name;
    }
    private function parentTag() {
        $name = $this->tagStack[count( $this->tagStack ) - 1];
        $this->debug( "PARENT $name" );
        return $name;
    }
    
}
class DeployWikiRevisionDetector extends WikiRevision {
    
    // tuple describes the result of detection
    var $result;
    
    // ontology ID
    var $ontologyID;
   
    var $logger;

    public function __construct($mode = 0, $ontologyID) {
        $this->mode = $mode;
     
        $this->ontologyID = $ontologyID;
  
        $this->result = NULL;
        $this->logger = Logger::getInstance();
    }


    public function getResult() {
        return $this->result;
    }
    
    /**
     *
     *
     * @return unknown
     */
    function importOldRevision() {


        $dbw = wfGetDB( DB_MASTER );
        // check revision here
        $linkCache = LinkCache::singleton();
        $linkCache->clear();

        global $dfgLang;
        if ($this->title->getNamespace() == NS_TEMPLATE && $this->title->getText() === $dfgLang->getLanguageString('df_contenthash')) return false;
        if ($this->title->getNamespace() == NS_TEMPLATE && $this->title->getText() === $dfgLang->getLanguageString('df_partofbundle')) return false;

       

        $article = new Article( $this->title );
        $pageId = $article->getId();

        if( $pageId == 0 ) {
            # page does not exist
        
            $this->result = array($this->title, "notexist");
            return false;
        } else {

            $prior = Revision::loadFromTitle( $dbw, $this->title );
            if( !is_null( $prior ) ) {

                // revision already exists.

                $partOfOntology = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_partofbundle'));
                $values = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($this->title), $partOfOntology);
                
                // FIXME: deal also with pages in MediaWiki namespace as possible conflict
                if ($this->title->getNamespace() == NS_MEDIAWIKI) {
                	 $this->result = array($this->title, "merge");
                	 return false;
                }
                if (count($values) > 0) {
                    $v = reset($values);
                    $ontologyID = $v->getString();
                    if ($ontologyID == ucfirst($this->ontologyID)) {
                        // same ontology, no conflict but merging necessary
                    
                        $this->result = array($this->title, "merge");
                    } else {
                        // conflict
                       
                        $this->result = array($this->title, "conflict");
                    }
                } else {
                
                	$this->result = array($this->title, "conflict");
                }

            }
        }
        return false;

    }

}