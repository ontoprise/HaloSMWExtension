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
class SMWRFChangeValueOperation extends SMWRFRefactoringOperation {
	private $instanceSet;
	private $property;
	private $oldValue;
	private $newValue; // empty means: remove annotation

	private $subjectDBKeys;

	public function __construct($instanceSet, $property, $oldValue, $newValue) {
		$this->instanceSet = $instanceSet;
		$this->property = Title::newFromText($property, SMW_NS_PROPERTY);
		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
	}

	public function getAffectedPages() {
		return $this->instanceSet;
	}

	public function refactor($save = true) {

	}

	public function changeContent($titleName, $wikitext) {
		$pom = new POMPage($titleName, $wikitext, array('POMExtendedParser'));

		# iterate trough the annotations
		$iterator = $pom->getProperties()->listIterator();
		$toDelete = array();
		$toAdd = array();
		while($iterator->hasNext()){
			$pomProperty = &$iterator->getNextNodeValueByReference(); # get reference for direct changes

			if (is_null($this->newValue)) {
				// remove annotation
				if ($pomProperty->getName() == $this->property->getText()) {
					$value = $pomProperty->getValue();
					if (is_null($this->oldValue) || $value == $this->oldValue) {
						$toDelete[] = $pomProperty;
					}
				}
			} if (is_null($this->oldValue)) {
				 // add new annotation
				 $toAdd[] = new POMProperty("[[".$this->property->getText()."::".$this->newValue."]]");
				
			}else {
				$value = $pomProperty->getValue();
				if ($pomProperty->getName() == $this->property->getText()) {
					   $value = $pomProperty->getValue();
					if (is_null($this->oldValue) || $value == $this->oldValue) {
						$pomProperty->setValue($this->newValue);
					}
				}
			}
		}

		foreach($toDelete as $d) {
			$pom->delete($d);
		}
		
		foreach($toAdd as $a) {
			$pom->addElement($a);
		}

		// calls sync() internally
		$wikitext = $pom->toString();
		return $wikitext;
	}
}