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

	private $mGardeningLogCategory;

	protected function __construct() {
		$this->mRefOpTimeStamp = wfTimestampNow();
		$this->mGardeningLogCategory = Title::newFromText(wfMsg('smw_gardening_log_cat'), NS_CATEGORY);
	}
	/**
	 * Returns the number of pages which get processed in some way.
	 *
	 * @return int
	 */
	public abstract function getWork();

	/**
	 * Performs the actual refactoring
	 *
	 * @param boolean $save
	 * @param string [] & $logMessages
	 * @param array & $testData
	 */
	public abstract function refactor($save = true, & $logMessages);
    
	
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
	 * Applies the given operations on the set of titles.
	 *
	 * NOTE:
	 * Any titles to work on which are specified in the operation itself
	 * are ignored!
	 *
	 * @param boolean $save
	 * @param string/Title[] $titles Titles or full qualified title strings
	 * @param SRFRefactoringOperation[] $operations
	 * @param array $logMessages
	 */
	public static function applyOperations($save = true, $titles, $operations, & $logMessages) {

		foreach($titles as $t) {
			$title = $t instanceof Title ? $t : Title::newFromText($t);
			$rev = Revision::newFromTitle($title);

			$wikitext = $rev->getRawText();
			foreach($operations as $op) {
				$wikitext = $op->applyOperation($title, $wikitext, $logMessages);
				$op->botWorked(1);
			}

			// stores article
			if ($save) {
				$status = $op->storeArticle($title, $wikitext, $rev->getRawComment());
				if (!$status->isGood()) {
					$l = new SRFLog('Saving of $title failed due to: $1', $title, $wikitext, array($status->getWikiText()));
					$l->setLogType(SREF_LOG_STATUS_WARN);
					$logMessages[$title->getPrefixedText()][] = $l;
				}
			}
		}

	}

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




