<?php

/**
 * Detector
 * Enter description here ...
 * @author kai
 *
 */
class DeployWikiImporterDetector extends WikiImporter {
	
	var $result;
	var $ontologyID;
	var $mode;
	var $callback;
	var $logger;

	function __construct($source, $ontologyID, $mode, $callback) {
		parent::__construct($source);
		$this->mode = $mode;
		$this->callback = $callback;
		$this->ontologyID = $ontologyID;
		$this->logger = Logger::getInstance();
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
					$this->workRevision = new DeployWikiRevisionDetector($this->mode, $this->ontologyID, $this->callback);
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
					$this->workRevision = new DeployWikiRevision($this->mode, $this->callback);
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
		
		$this->result[] = $this->workRevision->getResult();
		$this->workTitle = null;
		$this->workRevision = null;
		$this->workRevisionCount = 0;
		$this->workSuccessCount = 0;
		$this->pageTitle = null;
		$this->origTitle = null;
	}
}
class DeployWikiRevisionDetector extends WikiRevision {
	
	// tuple describes the result of detection
	var $result;
	
	// ontology ID
	var $ontologyID;
	
	// callback function for user interaction
	var $callback;

	var $logger;

	public function __construct($mode = 0, $ontologyID, $callback = NULL) {
		$this->mode = $mode;
		$this->callback = $callback;
		$this->ontologyID = $ontologyID;
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

		$this->text = $this->replaceOrAddContentHash($this->text);


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

				$contenthashProperty = SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_part_of_ontology'));
				$values = smwfGetStore()->getPropertyValues($this->title, $contenthashProperty);

				if (count($values) > 0) {
					$v = reset($values);
					$ontologyID = $v->getDBkey();
					if ($ontologyID === $this->ontologyID) {
						// same ontology, no conflict but merging necessary
						$this->result = array($this->title, "merge");
					} else {
						// conflict
						$this->result = array($this->title, "conflict");
					}
				}

			}
		}
		return false;

	}

}