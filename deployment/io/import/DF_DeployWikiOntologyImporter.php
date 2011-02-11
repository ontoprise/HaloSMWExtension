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
 * @author Kai Kühn / ontoprise / 2011
 *
 */
class DeployWikiOntologyImporter extends WikiImporter {

	var $result;
	var $ontologyID;
	var $prefix;
	var $mode;
	var $callback;
	var $logger;

	function __construct($source, $ontologyID, $prefix, $mode, $callback) {
		parent::__construct($source);
		$this->mode = $mode;
		$this->callback = $callback;
		$this->ontologyID = $ontologyID;
		$this->prefix = $prefix;
		$this->logger = Logger::getInstance();
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
					$this->workRevision = new DeployWikiOntologyRevision($this->mode, $this->ontologyID, $this->prefix, $this->callback);
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
					$this->workRevision = new DeployWikiOntologyRevision($this->mode, $this->ontologyID, $this->prefix, $this->callback);
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

		$this->result[] = !is_null($this->workRevision) ? $this->workRevision->getResult() : NULL;
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
class DeployWikiOntologyRevision extends WikiRevision {

	// tuple describes the result of detection
	var $result;

	// ontology ID
	var $ontologyID;

	// prefix used for import
	var $prefix;

	var $ontologyMerger;

	// callback function for user interaction
	var $callback;

	var $logger;

	public function __construct($mode = 0, $ontologyID, $prefix, $callback = NULL) {
		global $dfgLang;

		$this->mode = $mode;
		$this->callback = $callback;
		$this->ontologyID = $ontologyID;
		$this->prefix = $prefix;
		$this->ontologyMerger = new OntologyMerger(
		      array($dfgLang->getLanguageString('is_inverse_of')),
		      array($dfgLang->getLanguageString('has_domain_and_range') => array('Type:Page', 'Type:Page')),
		      array($dfgLang->getLanguageString('has_domain_and_range'), $dfgLang->getLanguageString('imported_from'), $dfgLang->getLanguageString('part_of_ontology'))
		);
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


		if ($this->prefix != '') {
			$nsText = $this->title->getNamespace() !== NS_MAIN ? $this->title->getNsText().":" : "";
			$this->setTitle(Title::newFromText($nsText.$this->prefix.$this->title->getText()));
		}

		$article = new Article( $this->title );
		$pageId = $article->getId();

		if( $pageId == 0 ) {
			# page does not exist

			$this->logger->info("Imported page: ".$this->title->getPrefixedText());
			print "\n\t[Imported page] ".$this->title->getPrefixedText();
			return parent::importOldRevision();

		} else {

			$prior = Revision::loadFromTitle( $dbw, $this->title );
			if( !is_null( $prior ) ) {

				// merge annotations and wikitext
				$wikitext = $prior->getRawText();
				$wikitext = $this->ontologyMerger->stripAnnotations($wikitext);

				$extractAnnotations = $this->ontologyMerger->extractAnnotations($this->getText());
				$wikitext .= implode("\n", $extractAnnotations);
				$wikitext = $this->ontologyMerger->transformOntologyElements($this->prefix, $wikitext);

				$this->text = $wikitext;
				print "\n\t[Updated page] ".$this->title->getPrefixedText();
				return $this->importAsNewRevision();

				/*// revision already exists.
				 // that means we have to check if the page was changed in the meantime.
				 $contenthashProperty = SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_contenthash'));
				 $values = smwfGetStore()->getPropertyValues($this->title, $contenthashProperty);
				 if (count($values) > 0) $exp_hash = strtolower(Tools::getXSDValue(reset($values))); else $exp_hash = NULL;
				 $rawtext = preg_replace('/\{\{\s*'.$dfgLang->getLanguageString('df_contenthash').'\s*\|\s*value\s*=\s*\w*(\s*\|)?[^}]*\}\}/', "", $prior->getRawText());
				 $hash = md5($rawtext);

				 if (is_null($exp_hash) || $hash === $exp_hash) {
					// either no hash annotation given and no check possible
					// or site is as it is expected.
					return $this->importAsNewRevision();
					}
					if ($hash != $exp_hash) {
						
					return $this->importAsNewRevision();

					}*/
			}
		}
		return false;

	}


	/**
	 * Adds a content hash template call at the end of the text or replaces an existing.
	 *
	 * {{Content hash|value=<md5 of $text>}}
	 *
	 * If the template call already exists, it is only changed where it is located.
	 *
	 * Note: The MD5 value is calculated assuming there is no template call for obvious reasons.
	 *
	 * @param $text
	 * @return $text with added or changed template call
	 */
	function replaceOrAddContentHash($text) {
		global $dfgLang;
		$matchNums = preg_match('/\{\{\s*'.$dfgLang->getLanguageString('df_contenthash').'\s*\|\s*value\s*=\s*\w*(\s*\|)?[^}]*\}\}/', $text);
		if ($matchNums === 0) {
			$text .= "\n{{".$dfgLang->getLanguageString('df_contenthash')."|value=".md5($text)."}}";
		} else {
			$rawtext = preg_replace('/\{\{\s*'.$dfgLang->getLanguageString('df_contenthash').'\s*\|\s*value\s*=\s*\w*(\s*\|)?[^}]*\}\}/', "", $text);
			$text = preg_replace('/\{\{\s*'.$dfgLang->getLanguageString('df_contenthash').'\s*\|\s*value\s*=\s*\w*(\s*\|)?[^}]*\}\}/', "{{".$dfgLang->getLanguageString('df_contenthash')."|value=".md5($rawtext)."}}", $text);
		}
		return $text;
	}

	function importAsNewRevision() {

		$dbw = wfGetDB( DB_MASTER );

		# Sneak a single revision into place
		$user = User::newFromName( $this->getUser() );
		if( $user ) {
		$userId = intval( $user->getId() );
		$userText = $user->getName();
		} else {
		$userId = 0;
		$userText = $this->getUser();
		}

		// avoid memory leak...?
		$linkCache = LinkCache::singleton();
		$linkCache->clear();

		$article = new Article( $this->title );
		$pageId = $article->getId();
		if( $pageId == 0 ) {
		# must create the page...
		$pageId = $article->insertOn( $dbw );
		$created = true;
		} else {
		$created = false;

		$prior = $dbw->selectField( 'revision', '1',
		array( 'rev_page' => $pageId,
		'rev_timestamp' => $dbw->timestamp( $this->timestamp ),
		'rev_user_text' => $userText,
		'rev_comment'   => $this->getComment() ),
		__METHOD__
		);
		if( $prior ) {
		// FIXME: this could fail slightly for multiple matches :P
		$this->logger->info("Skipping existing revision: ".$this->title->getPrefixedText());
		print "\n\t[Skipping existing revision] ".$this->title->getPrefixedText();
		wfDebug( __METHOD__ . ": skipping existing revision for [[" .
		$this->title->getPrefixedText() . "]], timestamp " . $this->timestamp . "\n" );
		return false;
		}
		}

		# FIXME: Use original rev_id optionally (better for backups)
		# Insert the row
		$revision = new Revision( array(
		'page'       => $pageId,
		'text'       => $this->getText(),
		'comment'    => $this->getComment(),
		'user'       => $userId,
		'user_text'  => $userText,
		'timestamp'  => $this->timestamp,
		'minor_edit' => $this->minor,
		) );
		$revId = $revision->insertOn( $dbw );
		$changed = $article->updateIfNewerOn( $dbw, $revision );

		# To be on the safe side...
		$tempTitle = $GLOBALS['wgTitle'];
		$GLOBALS['wgTitle'] = $this->title;

		if( $created ) {
		wfDebug( __METHOD__ . ": running onArticleCreate\n" );
		Article::onArticleCreate( $this->title );

		wfDebug( __METHOD__ . ": running create updates\n" );
		$article->createUpdates( $revision );

		} elseif( $changed ) {
		wfDebug( __METHOD__ . ": running onArticleEdit\n" );
		Article::onArticleEdit( $this->title );

		wfDebug( __METHOD__ . ": running edit updates\n" );
		$article->editUpdates(
		$this->getText(),
		$this->getComment(),
		$this->minor,
		$this->timestamp,
		$revId );
		}
		$GLOBALS['wgTitle'] = $tempTitle;
		$this->logger->info("Imported new revision of page: ".$this->title->getPrefixedText());
		print "\n\t[Imported new revision of page] ".$this->title->getPrefixedText();
		return true;
	}
}