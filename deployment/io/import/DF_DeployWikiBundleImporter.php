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
 * derived from MediaWiki page data importer
 *   Copyright (C) 2003, 2005, 2006 Brion Vibber <brion@pobox.com>
 *   http://www.mediawiki.org/
 * Extends the default wiki import mechanism.
 *
 *  1. Version control
 *  2. Bundle control
 *  3. Hash values to check integrity
 *
 */
class DeployWikiImporter {

	var $mode;
	var $callback;
	var $logger;
	var $bundleID;

	private $reader = null;
	private $mLogItemCallback, $mUploadCallback, $mRevisionCallback, $mPageCallback;
	private $mSiteInfoCallback, $mTargetNamespace, $mPageOutCallback;
	private $mDebug;

	function __construct($source, $mode, $callback, $bundleID) {
		$this->reader = new XMLReader();

		stream_wrapper_register( 'uploadsource', 'UploadSourceAdapter' );
		$id = UploadSourceAdapter::registerSource( $source );
		$this->reader->open( "uploadsource://$id" );

		// Default callbacks
		$this->setRevisionCallback( array( $this, "importRevision" ) );
		$this->setUploadCallback( array( $this, 'importUpload' ) );
		$this->setLogItemCallback( array( $this, 'importLogItem' ) );
		$this->setPageOutCallback( array( $this, 'finishImportPage' ) );

		$this->mode = $mode;
		$this->callback = $callback;
		$this->logger = Logger::getInstance();
		$this->bundleID = $bundleID;

	}




	private function throwXmlError( $err ) {
		$this->debug( "FAILURE: $err" );
		wfDebug( "WikiImporter XML error: $err\n" );
	}

	private function debug( $data ) {
		if( $this->mDebug ) {
			wfDebug( "IMPORT: $data\n" );
		}
	}

	private function warn( $data ) {
		wfDebug( "IMPORT: $data\n" );
	}

	private function notice( $data ) {
		global $wgCommandLineMode;
		if( $wgCommandLineMode ) {
			print "$data\n";
		} else {
			global $wgOut;
			$wgOut->addHTML( "<li>" . htmlspecialchars( $data ) . "</li>\n" );
		}
	}

	/**
	 * Set debug mode...
	 */
	function setDebug( $debug ) {
		$this->mDebug = $debug;
	}

	/**
	 * Sets the action to perform as each new page in the stream is reached.
	 * @param $callback callback
	 * @return callback
	 */
	public function setPageCallback( $callback ) {
		$previous = $this->mPageCallback;
		$this->mPageCallback = $callback;
		return $previous;
	}

	/**
	 * Sets the action to perform as each page in the stream is completed.
	 * Callback accepts the page title (as a Title object), a second object
	 * with the original title form (in case it's been overridden into a
	 * local namespace), and a count of revisions.
	 *
	 * @param $callback callback
	 * @return callback
	 */
	public function setPageOutCallback( $callback ) {
		$previous = $this->mPageOutCallback;
		$this->mPageOutCallback = $callback;
		return $previous;
	}

	/**
	 * Sets the action to perform as each page revision is reached.
	 * @param $callback callback
	 * @return callback
	 */
	public function setRevisionCallback( $callback ) {
		$previous = $this->mRevisionCallback;
		$this->mRevisionCallback = $callback;
		return $previous;
	}

	/**
	 * Sets the action to perform as each file upload version is reached.
	 * @param $callback callback
	 * @return callback
	 */
	public function setUploadCallback( $callback ) {
		$previous = $this->mUploadCallback;
		$this->mUploadCallback = $callback;
		return $previous;
	}

	/**
	 * Sets the action to perform as each log item reached.
	 * @param $callback callback
	 * @return callback
	 */
	public function setLogItemCallback( $callback ) {
		$previous = $this->mLogItemCallback;
		$this->mLogItemCallback = $callback;
		return $previous;
	}

	/**
	 * Sets the action to perform when site info is encountered
	 * @param $callback callback
	 * @return callback
	 */
	public function setSiteInfoCallback( $callback ) {
		$previous = $this->mSiteInfoCallback;
		$this->mSiteInfoCallback = $callback;
		return $previous;
	}

	/**
	 * Set a target namespace to override the defaults
	 */
	public function setTargetNamespace( $namespace ) {
		if( is_null( $namespace ) ) {
			// Don't override namespaces
			$this->mTargetNamespace = null;
		} elseif( $namespace >= 0 ) {
			// FIXME: Check for validity
			$this->mTargetNamespace = intval( $namespace );
		} else {
			return false;
		}
	}

	/**
	 * Default per-revision callback, performs the import.
	 * @param $revision WikiRevision
	 */
	public function importRevision( $revision ) {
		$dbw = wfGetDB( DB_MASTER );
		return $dbw->deadlockLoop( array( $revision, 'importOldRevision' ) );
	}

	/**
	 * Default per-revision callback, performs the import.
	 * @param $rev WikiRevision
	 */
	public function importLogItem( $rev ) {
		$dbw = wfGetDB( DB_MASTER );
		return $dbw->deadlockLoop( array( $rev, 'importLogItem' ) );
	}

	/**
	 * Dummy for now...
	 */
	public function importUpload( $revision ) {
		//$dbw = wfGetDB( DB_MASTER );
		//return $dbw->deadlockLoop( array( $revision, 'importUpload' ) );
		return false;
	}

	/**
	 * Mostly for hook use
	 */
	public function finishImportPage( $title, $origTitle, $revCount, $sRevCount, $pageInfo ) {
		$args = func_get_args();
		return wfRunHooks( 'AfterImportPage', $args );
	}

	/**
	 * Alternate per-revision callback, for debugging.
	 * @param $revision WikiRevision
	 */
	public function debugRevisionHandler( &$revision ) {
		$this->debug( "Got revision:" );
		if( is_object( $revision->title ) ) {
			$this->debug( "-- Title: " . $revision->title->getPrefixedText() );
		} else {
			$this->debug( "-- Title: <invalid>" );
		}
		$this->debug( "-- User: " . $revision->user_text );
		$this->debug( "-- Timestamp: " . $revision->timestamp );
		$this->debug( "-- Comment: " . $revision->comment );
		$this->debug( "-- Text: " . $revision->text );
	}

	/**
	 * Notify the callback function when a new <page> is reached.
	 * @param $title Title
	 */
	function pageCallback( $title ) {
		if( isset( $this->mPageCallback ) ) {
			call_user_func( $this->mPageCallback, $title );
		}
	}

	/**
	 * Notify the callback function when a </page> is closed.
	 * @param $title Title
	 * @param $origTitle Title
	 * @param $revCount Integer
	 * @param $sucCount Int: number of revisions for which callback returned true
	 * @param $pageInfo Array: associative array of page information
	 */
	private function pageOutCallback( $title, $origTitle, $revCount, $sucCount, $pageInfo ) {
		if( isset( $this->mPageOutCallback ) ) {
			$args = func_get_args();
			call_user_func_array( $this->mPageOutCallback, $args );
		}
	}

	/**
	 * Notify the callback function of a revision
	 * @param $revision A WikiRevision object
	 */
	private function revisionCallback( $revision ) {
		if ( isset( $this->mRevisionCallback ) ) {
			return call_user_func_array( $this->mRevisionCallback,
			array( $revision, $this ) );
		} else {
			return false;
		}
	}

	/**
	 * Notify the callback function of a new log item
	 * @param $revision A WikiRevision object
	 */
	private function logItemCallback( $revision ) {
		if ( isset( $this->mLogItemCallback ) ) {
			return call_user_func_array( $this->mLogItemCallback,
			array( $revision, $this ) );
		} else {
			return false;
		}
	}

	/**
	 * Shouldn't something like this be built-in to XMLReader?
	 * Fetches text contents of the current element, assuming
	 * no sub-elements or such scary things.
	 * @return string
	 * @access private
	 */
	private function nodeContents() {
		if( $this->reader->isEmptyElement ) {
			return "";
		}
		$buffer = "";
		while( $this->reader->read() ) {
			switch( $this->reader->nodeType ) {
				case XmlReader::TEXT:
				case XmlReader::SIGNIFICANT_WHITESPACE:
					$buffer .= $this->reader->value;
					break;
				case XmlReader::END_ELEMENT:
					return $buffer;
			}
		}

		$this->reader->close();
		return '';
	}

	# --------------

	/** Left in for debugging */
	private function dumpElement() {
		static $lookup = null;
		if (!$lookup) {
			$xmlReaderConstants = array(
                "NONE",
                "ELEMENT",
                "ATTRIBUTE",
                "TEXT",
                "CDATA",
                "ENTITY_REF",
                "ENTITY",
                "PI",
                "COMMENT",
                "DOC",
                "DOC_TYPE",
                "DOC_FRAGMENT",
                "NOTATION",
                "WHITESPACE",
                "SIGNIFICANT_WHITESPACE",
                "END_ELEMENT",
                "END_ENTITY",
                "XML_DECLARATION",
			);
			$lookup = array();

			foreach( $xmlReaderConstants as $name ) {
				$lookup[constant("XmlReader::$name")] = $name;
			}
		}

		print( var_dump(
		$lookup[$this->reader->nodeType],
		$this->reader->name,
		$this->reader->value
		)."\n\n" );
	}

	/**
	 * Primary entry point
	 */
	public function doImport() {
		$this->reader->read();

		if ( $this->reader->name != 'mediawiki' ) {
			throw new MWException( "Expected <mediawiki> tag, got ".
			$this->reader->name );
		}
		$this->debug( "<mediawiki> tag is correct." );

		$this->debug( "Starting primary dump processing loop." );

		$keepReading = $this->reader->read();
		$skip = false;
		while ( $keepReading ) {
			$tag = $this->reader->name;
			$type = $this->reader->nodeType;

			if ( !wfRunHooks( 'ImportHandleToplevelXMLTag', $this ) ) {
				// Do nothing
			} elseif ( $tag == 'mediawiki' && $type == XmlReader::END_ELEMENT ) {
				break;
			} elseif ( $tag == 'siteinfo' ) {
				$this->handleSiteInfo();
			} elseif ( $tag == 'page' ) {
				$this->handlePage();
			} elseif ( $tag == 'logitem' ) {
				$this->handleLogItem();
			} elseif ( $tag != '#text' ) {
				$this->warn( "Unhandled top-level XML tag $tag" );

				$skip = true;
			}

			if ($skip) {
				$keepReading = $this->reader->next();
				$skip = false;
				$this->debug( "Skip" );
			} else {
				$keepReading = $this->reader->read();
			}
		}

		return true;
	}

	private function handleSiteInfo() {
		// Site info is useful, but not actually used for dump imports.
		// Includes a quick short-circuit to save performance.
		if ( ! $this->mSiteInfoCallback ) {
			$this->reader->next();
			return true;
		}
		throw new MWException( "SiteInfo tag is not yet handled, do not set mSiteInfoCallback" );
	}

	private function handleLogItem() {
		$this->debug( "Enter log item handler." );
		$logInfo = array();

		// Fields that can just be stuffed in the pageInfo object
		$normalFields = array( 'id', 'comment', 'type', 'action', 'timestamp',
                    'logtitle', 'params' );

		while ( $this->reader->read() ) {
			if ( $this->reader->nodeType == XmlReader::END_ELEMENT &&
			$this->reader->name == 'logitem') {
				break;
			}

			$tag = $this->reader->name;

			if ( !wfRunHooks( 'ImportHandleLogItemXMLTag',
			$this, $logInfo ) ) {
				// Do nothing
			} elseif ( in_array( $tag, $normalFields ) ) {
				$logInfo[$tag] = $this->nodeContents();
			} elseif ( $tag == 'contributor' ) {
				$logInfo['contributor'] = $this->handleContributor();
			} elseif ( $tag != '#text' ) {
				$this->warn( "Unhandled log-item XML tag $tag" );
			}
		}

		$this->processLogItem( $logInfo );
	}

	private function processLogItem( $logInfo ) {
		$revision = new DeployWikiRevision($this->mode, $this->callback, $this->bundleID);

		$revision->setID( $logInfo['id'] );
		$revision->setType( $logInfo['type'] );
		$revision->setAction( $logInfo['action'] );
		$revision->setTimestamp( $logInfo['timestamp'] );
		$revision->setParams( $logInfo['params'] );
		$revision->setTitle( Title::newFromText( $logInfo['logtitle'] ) );

		if ( isset( $logInfo['comment'] ) ) {
			$revision->setComment( $logInfo['comment'] );
		}

		if ( isset( $logInfo['contributor']['ip'] ) ) {
			$revision->setUserIP( $logInfo['contributor']['ip'] );
		}
		if ( isset( $logInfo['contributor']['username'] ) ) {
			$revision->setUserName( $logInfo['contributor']['username'] );
		}

		return $this->logItemCallback( $revision );
	}

	private function handlePage() {
		// Handle page data.
		$this->debug( "Enter page handler." );
		$pageInfo = array( 'revisionCount' => 0, 'successfulRevisionCount' => 0 );

		// Fields that can just be stuffed in the pageInfo object
		$normalFields = array( 'title', 'id', 'redirect', 'restrictions' );

		$skip = false;
		$badTitle = false;

		while ( $skip ? $this->reader->next() : $this->reader->read() ) {
			if ( $this->reader->nodeType == XmlReader::END_ELEMENT &&
			$this->reader->name == 'page') {
				break;
			}

			$tag = $this->reader->name;

			if ( $badTitle ) {
				// The title is invalid, bail out of this page
				$skip = true;
			} elseif ( !wfRunHooks( 'ImportHandlePageXMLTag', array( $this,
			&$pageInfo ) ) ) {
				// Do nothing
			} elseif ( in_array( $tag, $normalFields ) ) {
				$pageInfo[$tag] = $this->nodeContents();
				if ( $tag == 'title' ) {
					$title = $this->processTitle( $pageInfo['title'] );

					if ( !$title ) {
						$badTitle = true;
						$skip = true;
					}

					$this->pageCallback( $title );
					list( $pageInfo['_title'], $origTitle ) = $title;
				}
			} elseif ( $tag == 'revision' ) {
				$this->handleRevision( $pageInfo );
			} elseif ( $tag == 'upload' ) {
				$this->handleUpload( $pageInfo );
			} elseif ( $tag != '#text' ) {
				$this->warn( "Unhandled page XML tag $tag" );
				$skip = true;
			}
		}

		$this->pageOutCallback( $pageInfo['_title'], $origTitle,
		$pageInfo['revisionCount'],
		$pageInfo['successfulRevisionCount'],
		$pageInfo );
	}

	private function handleRevision( &$pageInfo ) {
		$this->debug( "Enter revision handler" );
		$revisionInfo = array();

		$normalFields = array( 'id', 'timestamp', 'comment', 'minor', 'text' );

		$skip = false;

		while ( $skip ? $this->reader->next() : $this->reader->read() ) {
			if ( $this->reader->nodeType == XmlReader::END_ELEMENT &&
			$this->reader->name == 'revision') {
				break;
			}

			$tag = $this->reader->name;

			if ( !wfRunHooks( 'ImportHandleRevisionXMLTag', $this,
			$pageInfo, $revisionInfo ) ) {
				// Do nothing
			} elseif ( in_array( $tag, $normalFields ) ) {
				$revisionInfo[$tag] = $this->nodeContents();
			} elseif ( $tag == 'contributor' ) {
				$revisionInfo['contributor'] = $this->handleContributor();
			} elseif ( $tag != '#text' ) {
				$this->warn( "Unhandled revision XML tag $tag" );
				$skip = true;
			}
		}

		$pageInfo['revisionCount']++;
		if ( $this->processRevision( $pageInfo, $revisionInfo ) ) {
			$pageInfo['successfulRevisionCount']++;
		}
	}

	private function processRevision( $pageInfo, $revisionInfo ) {
		$revision = new DeployWikiRevision($this->mode, $this->callback, $this->bundleID);

		$revision->setID( array_key_exists('id', $revisionInfo) ? $revisionInfo['id'] : -1 );
		$revision->setText( $revisionInfo['text'] );
		$revision->setTitle( $pageInfo['_title'] );
		$revision->setTimestamp( $revisionInfo['timestamp'] );

		if ( isset( $revisionInfo['comment'] ) ) {
			$revision->setComment( $revisionInfo['comment'] );
		}

		if ( isset( $revisionInfo['minor'] ) )
		$revision->setMinor( true );

		if ( isset( $revisionInfo['contributor']['ip'] ) ) {
			$revision->setUserIP( $revisionInfo['contributor']['ip'] );
		}
		if ( isset( $revisionInfo['contributor']['username'] ) ) {
			$revision->setUserName( $revisionInfo['contributor']['username'] );
		}

		return $this->revisionCallback( $revision );
	}

	private function handleUpload( &$pageInfo ) {
		$this->debug( "Enter upload handler" );
		$uploadInfo = array();

		$normalFields = array( 'timestamp', 'comment', 'filename', 'text',
                    'src', 'size' );

		$skip = false;

		while ( $skip ? $this->reader->next() : $this->reader->read() ) {
			if ( $this->reader->nodeType == XmlReader::END_ELEMENT &&
			$this->reader->name == 'upload') {
				break;
			}

			$tag = $this->reader->name;

			if ( !wfRunHooks( 'ImportHandleUploadXMLTag', $this,
			$pageInfo ) ) {
				// Do nothing
			} elseif ( in_array( $tag, $normalFields ) ) {
				$uploadInfo[$tag] = $this->nodeContents();
			} elseif ( $tag == 'contributor' ) {
				$uploadInfo['contributor'] = $this->handleContributor();
			} elseif ( $tag != '#text' ) {
				$this->warn( "Unhandled upload XML tag $tag" );
				$skip = true;
			}
		}

		return $this->processUpload( $pageInfo, $uploadInfo );
	}

	private function processUpload( $pageInfo, $uploadInfo ) {
		$revision = new DeployWikiRevision($this->mode, $this->callback, $this->bundleID);

		$revision->setTitle( $pageInfo['_title'] );
		$revision->setID( $uploadInfo['id'] );
		$revision->setTimestamp( $uploadInfo['timestamp'] );
		$revision->setText( $uploadInfo['text'] );
		$revision->setFilename( $uploadInfo['filename'] );
		$revision->setSrc( $uploadInfo['src'] );
		$revision->setSize( intval( $uploadInfo['size'] ) );
		$revision->setComment( $uploadInfo['comment'] );

		if ( isset( $uploadInfo['contributor']['ip'] ) ) {
			$revision->setUserIP( $uploadInfo['contributor']['ip'] );
		}
		if ( isset( $uploadInfo['contributor']['username'] ) ) {
			$revision->setUserName( $uploadInfo['contributor']['username'] );
		}

		return $this->uploadCallback( $revision );
	}

	private function handleContributor() {
		$fields = array( 'id', 'ip', 'username' );
		$info = array();

		while ( $this->reader->read() ) {
			if ( $this->reader->nodeType == XmlReader::END_ELEMENT &&
			$this->reader->name == 'contributor') {
				break;
			}

			$tag = $this->reader->name;

			if ( in_array( $tag, $fields ) ) {
				$info[$tag] = $this->nodeContents();
			}
		}

		return $info;
	}

	private function processTitle( $text ) {
		$workTitle = $text;
		$origTitle = Title::newFromText( $workTitle );

		if( !is_null( $this->mTargetNamespace ) && !is_null( $origTitle ) ) {
			$title = Title::makeTitle( $this->mTargetNamespace,
			$origTitle->getDBkey() );
		} else {
			$title = Title::newFromText( $workTitle );
		}

		if( is_null( $title ) ) {
			// Invalid page title? Ignore the page
			$this->notice( "Skipping invalid page title '$workTitle'" );
			return false;
		} elseif( $title->getInterwiki() != '' ) {
			$this->notice( "Skipping interwiki page title '$workTitle'" );
			return false;
		}

		return array( $title, $origTitle );
	}


}

// only print information (dry run)
define('DEPLOYWIKIREVISION_INFO', 0);
// warn before continueing
define('DEPLOYWIKIREVISION_WARN', 1);
// overwrite always without warning
define('DEPLOYWIKIREVISION_FORCE', 2);

class DeployWikiRevision extends WikiRevision {


	// import mode
	var $mode;

	// callback function for user interaction
	var $callback;

	var $logger;

	var $bundleID;

	public function __construct($mode = 0, $callback = NULL, $bundleID) {
		$this->mode = $mode;
		$this->callback = $callback;
		$this->logger = Logger::getInstance();
		$this->bundleID = $bundleID;

	}

	function getComment() {
		return "auto-generated by Wiki admin tool";
	}

	/**
	 * Just like original importOldRevision,
	 * but asks before overwriting a page.
	 *
	 * @return unknown
	 */
	function importOldRevision() {

		global $dfgOut;
		$dbw = wfGetDB( DB_MASTER );
		// check revision here
		$linkCache = LinkCache::singleton();
		$linkCache->clear();

		global $dfgLang;
		if ($this->title->getNamespace() == NS_TEMPLATE && $this->title->getText() === $dfgLang->getLanguageString('df_partofbundle')) return false;
		if ($this->title->getNamespace() == NS_FILE) return false;

		$article = new Article( $this->title );
		$pageId = $article->getId();

		if( $pageId == 0 ) {

			# must create the page...
			if ($this->mode == DEPLOYWIKIREVISION_INFO) {
				return false;
			} else {
				$this->logger->info("[Imported page] ".$this->title->getPrefixedText());
				$dfgOut->outputln("\t".$this->title->getPrefixedText());
				$res = parent::importOldRevision();
				// notify import operation
				$user = User::newFromName( $this->getUser() );
				RecentChange::notifyNew(wfTimestampNow(), $this->title, $this->minor, $user, $this->getComment(), false);

				// refresh semantic data if SMW is available
				if (defined('SMW_VERSION')) {
					$article = new Article( $this->title );
					$pageId = $article->getId();
					smwfGetStore()->refreshData($pageId, 1, false, false);
				}

				return $res;
			}
		} else {

			$prior = Revision::loadFromTitle( $dbw, $this->title );
			if( !is_null( $prior ) ) {

				// check if page belongs to another bundle
				$bundleDIs = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($this->title),
				SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_partofbundle')));
				$bundleDI = reset($bundleDIs);
				if ($bundleDI !== false) {
					$anotherBundle = $bundleDI->getTitle()->getDBkey();

					if ($anotherBundle != ucfirst($this->bundleID)) {
						switch($this->mode) {
							case DEPLOYWIKIREVISION_INFO:
								$dfgOut->outputln($this->title->getText()." belongs to another bundle (".$anotherBundle.")");
								break;
							case DEPLOYWIKIREVISION_WARN:
								global $dfgGlobalOptionsValues;
								if (array_key_exists('df_watsettings_merge_with_other_bundle', $dfgGlobalOptionsValues)) {
									$result = $dfgGlobalOptionsValues['df_watsettings_merge_with_other_bundle'];
								} else {
									$this->callback->pageToBeMerged($this, $this->mode, $result);
									if ($result !== true) return false;
								}
								break;
							case DEPLOYWIKIREVISION_FORCE:
								break;
						}

					}
				}

				// read ontology bundles content (if existing)
				// and add it to the new revision
				$oldText = $prior->getRawText();
				$om = new OntologyMerger();
				$allbundleIDs = $om->getAllBundles($oldText);
				foreach($allbundleIDs as $bundleID) {
					$bundleContent = $om->getBundleContent($bundleID, $oldText);
					$this->text = $om->addBundle($bundleID, $this->text, $bundleContent);
				}

				// revision already exists.
				// that means we have to check if the page was changed in the meantime.
				// read the comment and decide then
				$comment = $prior->getComment(Revision::RAW);

				if ($comment == "auto-generated by Wiki admin tool"
				|| $comment == "auto-generated by onto2mwxml") {
					// most likely no user editing in the meantime, so simply overwrite
					if ($this->mode == DEPLOYWIKIREVISION_INFO) {
						return false;
					} else {
						$this->setID($prior->getId()+1);
						$this->setTimestamp(0);
						return $this->importAsNewRevision();
					}
				} else {
					global $dfgGlobalOptionsValues;
					// let the user confirm overwrite
					$result = false;
					if (array_key_exists('df_watsettings_overwrite_always', $dfgGlobalOptionsValues)) {
						$result = $dfgGlobalOptionsValues['df_watsettings_overwrite_always'];
					} else {
						if (!is_null($this->callback)) {
							$this->callback->modifiedPage($this, $this->mode, $result);
						}
						if ($result == true) {
							// if confirmed overwrite
							$this->setID($prior->getId()+1);
							$this->setTimestamp(0);
							return $this->importAsNewRevision();
						}
					}

				}
			}
		}
		return false;

	}



	function importAsNewRevision() {
		global $dfgOut;
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
				$dfgOut->outputln("\t[Skipping existing revision] ".$this->title->getPrefixedText());
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
		//FIXME: add old id and last timestamp
		RecentChange::notifyEdit($this->timestamp, $this->title, $this->minor, $user, $this->getComment(), 0, 0, false);

		// refresh semantic data if SMW is available
		if (defined('SMW_VERSION')) {
			$article = new Article( $this->title );
			$pageId = $article->getId();
			smwfGetStore()->refreshData($pageId, 1, false, false);
		}
		$this->logger->info("Imported new revision of page: ".$this->title->getPrefixedText());
		$dfgOut->outputln("\t[Imported new revision of page] ".$this->title->getPrefixedText());
		return true;
	}
}

