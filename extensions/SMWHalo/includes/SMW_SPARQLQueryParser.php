<?php
/**
 * @file
 * @ingroup SMWHaloSMWDeviations
 * 
 * @defgroup SMWHaloSMWDeviations SMWHalo SMW Deviations
 * @ingroup SMWHalo
 */
if (!defined('MEDIAWIKI')) die();
 
global $smwgIP;
require_once ( $smwgIP . '/includes/SMW_QueryProcessor.php');
require_once ( $smwgIP . '/includes/storage/SMW_Description.php');


class SMWSPARQLQuery extends SMWQuery {
	
	/**
	 * True, if query was converted from ASK
	 *
	 * @var boolean
	 */
	public $fromASK = false;
	public $mergeResults = 0; // 0 means: not set
	
	/**
	 * True if mainlabel is missing
	 *
	 * @var boolean
	 */
	public $mainLabelMissing = false;
	
	public function __construct($desc, $inline) {
		parent::__construct($desc, $inline);
	}
}

class SMWSPARQLQueryParser extends SMWQueryParser {
    protected $m_label;
    
    public function SMWSPARQLQueryParser() {
		parent::__construct();
		$this->m_defaultns = NULL;
	}

    
	/**
	 * Compute an SMWDescription from a query string. Returns whatever descriptions could be
	 * wrestled from the given string (the most general result being SMWThingDescription if
	 * no meaningful condition was extracted).
	 */
	public function getQueryDescription($querystring) {
		if (stripos($querystring, "select ") !== false) {
			wfProfileIn('SMWSPARQLQueryParser::getQueryDescription (SMW)');
			$this->m_errors = array();
			$this->m_label = '';
			$this->m_curstring = $querystring;
			$this->m_sepstack = array();
			$setNS = false;
			$result = new SMWSPARQLDescription();
			wfProfileOut('SMWSPARQLQueryParser::getQueryDescription (SMW)');
			return $result;
		} else {
			return parent::getQueryDescription($querystring);
		}
	}

	/**
	 * Return array of error messages (possibly empty).
	 */
	public function getErrors() {
		return $this->m_errors;
	}

	/**
	 * Return error message or empty string if no error occurred.
	 */
	public function getErrorString() {
		return smwfEncodeMessages($this->m_errors);
	}

	/**
	 * Return label for the results of this query (which
	 * might be empty if no such information was passed).
	 */
	public function getLabel() {
		return $this->m_label;
	}

}

class SMWSPARQLDescription extends SMWDescription {
	public function getQueryString($asvalue = false) {
		return '+';
	}

	public function isSingleton() {
		return false;
	}

	public function getSize() {
		return 0; // no real condition, no size or depth
	}

	public function prune(&$maxsize, &$maxdepth, &$log) {
		return $this;
	}
}
