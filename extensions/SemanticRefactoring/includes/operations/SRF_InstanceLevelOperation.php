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
abstract class SRFInstanceLevelOperation extends SRFRefactoringOperation {
	
	public function __construct($instanceSet) {
		parent::__construct();
		$this->affectedPages = array();
		foreach($instanceSet as $i) {
			$this->affectedPages[] = Title::newFromText($i);
		}
	}

	public function queryAffectedPages() {
        return $this->affectedPages;
    }
    	
	/**
	 * Applies and stores the changes of this refactoring operation.
	 * (not used if several operations are combined, see applyOperation)
	 *
	 * (non-PHPdoc)
	 * @see extensions/SemanticRefactoring/includes/SRFRefactoringOperation::refactor()
	 */
	public function refactor($save = true, & $logMessages) {
		SRFRefactoringOperation::applyOperations($save, $this->affectedPages, array($this), $logMessages);
	}
	

	/**
	 * Applies the operation and returns the changed wikitext.
	 *
	 * @param Title $title
	 * @param string $wikitext
	 * @param array $logMessages
	 *
	 * @return string
	 */
	public abstract function applyOperation($title, $wikitext, & $logMessages);
}