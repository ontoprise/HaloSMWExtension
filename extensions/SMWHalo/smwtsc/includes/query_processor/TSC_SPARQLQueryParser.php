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
 * @file
 * @ingroup SMWHaloSMWDeviations
 * 
 * @defgroup SMWHaloSMWDeviations SMWHalo SMW Deviations
 * @ingroup SMWHalo
 */
if (!defined('MEDIAWIKI')) die();
 
global $smwgIP;
require_once ( $smwgIP . '/includes/storage/SMW_Description.php');


class SMWSPARQLQuery extends SMWQuery {
	
	/**
	 * True, if query was converted from ASK
	 *
	 * @var boolean
	 */
	public $fromASK = false;
	
	
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
