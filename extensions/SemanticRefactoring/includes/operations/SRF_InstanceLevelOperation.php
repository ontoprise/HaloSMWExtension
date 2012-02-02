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
 *
 * @author Kai Kuehn
 *
 */
class SRFInstanceLevelOperation extends SRFRefactoringOperation {

	protected $operations;

	public function __construct($instanceSet) {
		parent::__construct();
		$this->affectedPages = array();
		$this->operations = array();
		foreach($instanceSet as $i) {
			$this->affectedPages[] = Title::newFromText($i);
		}
	}

	public function queryAffectedPages() {
		return $this->affectedPages;
	}

	public function addOperation($operation) {
		$this->operations[] = $operation;
	}
	 
	/**
	 * Applies and stores the changes of this refactoring operation.
	 * (not used if several operations are combined, see applyOperation)
	 *
	 * (non-PHPdoc)
	 * @see extensions/SemanticRefactoring/includes/SRFRefactoringOperation::refactor()
	 */
	public function refactor($save = true, & $logMessages) {
		$this->applyOperations($save, $this->affectedPages, $this->operations, $logMessages);
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
	public function applyOperations($save = true, $titles, $operations, & $logMessages) {

		foreach($titles as $t) {
			$title = $t instanceof Title ? $t : Title::newFromText($t);
			$rev = Revision::newFromTitle($title);

			$wikitext = $rev->getRawText();
			$requireSave = false;
			foreach($operations as $op) {
				$wikitext = $op->applyOperation($title, $wikitext, $logMessages);
				$requireSave = $requireSave | $op->requireSave();
			}
			$this->botWorked(1);

			// stores article
			if ($save && $requireSave) {
				$status = $this->storeArticle($title, $wikitext, $rev->getRawText(), $rev->getRawComment(), $logMessages);
			} 
		}

	}
	
}