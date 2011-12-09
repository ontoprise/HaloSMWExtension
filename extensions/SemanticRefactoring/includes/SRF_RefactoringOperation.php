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
abstract class SMWRFRefactoringOperation {

	protected $mAffectedPages = NULL;
    protected $mBot;
    
	public function getAffectedPages() {
		if (is_null($this->mAffectedPages)) {
			$this->mAffectedPages = $this->queryAffectedPages();
		}
		return $this->mAffectedPages;
	}
	
	public function setBot(GardeningBot $bot) {
		$this->mBot = $bot;
	}
	
	/**
	 * Returns all pages which must be changed for a refactoring.
	 *
	 * @return string[] prefixed DBkeys
	 */
	public abstract function queryAffectedPages();

	/**
	 * Performs the actual refactoring
	 *
	 * @param boolean $save
	 * @param string [] & $logMessages
	 * @param array & $testData
	 */
	public abstract function refactor($save = true, & $logMessages, & $testData = NULL);

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

	

	protected function replaceValueInAnnotation($objects) {
		foreach($objects as $o){

			$value = $o->getPropertyValue();
			$values = $this->splitRecordValues($value);

			array_walk($values, array($this, 'replaceTitle'));

			$newValue = SMWDataValueFactory::newPropertyObjectValue($o->getProperty()->getDataItem(), implode("; ", $values));
			$o->setSMWDataValue($newValue);

		}
	}

	public static function makeTitleListUnique($titles) {
		usort($titles, array("SMWRFRefactoringOperation", "compareTitles"));

		$result = array();
		$last = reset($titles);
		if ($last !== false) $result[] = $last;
		for($i = 1, $n = count($titles); $i < $n; $i++ ) {
			if ($titles[$i]->getPrefixedText() == $last->getPrefixedText()) {
				$titles[$i] = NULL;
				continue;
			}
			$last = $titles[$i];
			$result[] = $titles[$i];
		}

		return $result;
	}

	/* callback methods */
	private static function compareTitles($a, $b) {
		return strcmp($a->getPrefixedText(), $b->getPrefixedText());
	}
	private static function isnull($a) {
		return is_null($a);
	}
}




