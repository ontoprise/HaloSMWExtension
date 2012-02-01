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
 * Super-class for all refactoring operations.
 *
 * @author Kai Kuehn
 *
 */
global $smwgHaloIP;
require_once($srefgIP.'/includes/SRF_Log.php');
require_once($smwgHaloIP.'/includes/SMW_OntologyManipulator.php');

abstract class SRFRefactoringOperation {

	protected $mBot;
	protected $mRefOpTimeStamp;
	protected $affectedPages;
	protected $previewData;
	
	private $mGardeningLogCategory;
	

	protected function __construct() {
		$this->mRefOpTimeStamp = wfTimestampNow();
		$this->mGardeningLogCategory = Title::newFromText(wfMsg('smw_gardening_log_cat'), NS_CATEGORY);
		$this->affectedPages = NULL;
		$this->previewData = array();
		
	}

	/**
	 * Returns page titles which get processed in some way.
	 * 
	 * @return Title[]
	 */
	public abstract function queryAffectedPages();

	/**
	 * Performs the actual refactoring
	 *
	 * @param boolean $save
	 * @param string [] & $logMessages
	 *
	 */
	public abstract function refactor($save = true, & $logMessages);


	/**
	 * Returns the number of pages which get processed in some way.
	 *
	 * @return int
	 */
	public function getWork() {
		$this->affectedPages = $this->queryAffectedPages();
		return count($this->affectedPages);
	}

	/**
	 * Returns a preview of the operation.
	 *
	 * A preview is a list of tuples (message-id, number of affected pages)
	 *
	 * @return array (message-id => number of affected pages)
	 */
	public function preview() {
		$this->queryAffectedPages(); // make sure preview data is calculated.
		$this->previewData['sref_changedpage'] = $this->getWork();
		return $this->previewData;
	}
    
	/**
	 * True if $option is set.
	 * 
	 * @param string $option
	 * @param array $options Hash array of options
	 */
	public function isOptionSet($option, $options) {
		return (array_key_exists($option, $options) && $options[$option] == "true");
	}
	
	/**
	 * Set a GardeningBot to report progress
	 *
	 * @param GardeningBot $bot
	 */
	public function setBot(GardeningBot $bot) {
		$this->mBot = $bot;
	}

	
	// HELPER methods
	
	protected function splitRecordValues($value) {
		$valueArray = explode(";", $value);
		array_walk($valueArray, array($this, 'trim'));
		return $valueArray;
	}

	/**
	 * Returns trimmed string
	 * Callback method for array_walk
	 *
	 * @param string $s
	 * @param int $index
	 */
	private function trim(& $s, $i) {
		$s = trim($s);
	}

	/**
	 * Stores the $wikitext in the article $title.
	 *
	 * @param Title $title
	 * @param string wikitext
	 * @param string comment
	 *
	 * @return Status
	 */
	public function storeArticle($title, $wikitext, $comment) {
		$userCan = smwf_om_userCan($title->getText(), "edit", $title->getNamespace());
		if ($userCan == "false") return Status::newFatal(wfMsg('sref_no_sufficient_rights'));
		$a = new Article($title);
		if ($this->mRefOpTimeStamp < $a->getTimestamp()) {
			return Status::newFatal(wfMsg('sref_article_changed'));
		}
		if (smwfGetSemanticStore()->isInCategory($title, $this->mGardeningLogCategory)) {
			return Status::newFatal(wfMsg('sref_do_not_change_gardeninglog'));
		}
		$status = $a->doEdit($wikitext, $comment, EDIT_FORCE_BOT);
		return $status;
	}

	public function botWorked($worked) {
		if (!is_null($this->mBot)) $this->mBot->worked(1);
	}
	

	/**
	 * Find nodes with the given type-ID below $node.
	 * 
	 * @param WikiObjectModel $node
	 * @param string $id Type-ID
	 * @param array (out) $results
	 * 
	 * @return
	 */
	protected function findObjectByID($node, $id, & $results) {

		if ($node->isCollection()) {
			$objects = $node->getObjects();
			foreach($objects as $o) {
				if ($o->getTypeID() == $id) {

					$results[] = $o;

				}
				$this->findObjectByID($o, $id, $results);
			}
		} else {
			if ($node->getTypeID() == $id) {
					
				$results[] = $node;

			}
		}
	}






}




