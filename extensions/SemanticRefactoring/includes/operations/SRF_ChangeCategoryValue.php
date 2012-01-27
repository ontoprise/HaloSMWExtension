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
class SRFChangeCategoryValueOperation extends SRFInstanceLevelOperation {
	

	private $oldValue;
	private $newValue; // empty means: remove annotation

	private $subjectDBKeys;

	public function __construct($instanceSet, $oldValue, $newValue) {
		parent::__construct($instanceSet);

		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
	}

	public function queryAffectedPages() {
		return $this->instanceSet;
	}

	public function getWork() {
		return count($this->instanceSet);
	}

	

	/**
	 * Replaces old value with new.
	 * Callback method for array_walk
	 *
	 * @param string $title Prefixed title
	 * @param int $index
	 */
	protected function replaceValue(& $value, $index) {
		if (ucfirst($value) == ucfirst($this->oldValue)) {
			$value = $this->newValue;
		}
	}

	public function applyOperation($title, $wikitext, & $logMessages) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_CATEGORY);

		$toDelete = array();
		$toAdd = array();

		if (is_null($this->oldValue)) {
			// add new annotation
			$toAdd[] = new WOMCategoryModel($this->newValue);
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Added category $1 ', $title, "", array(Title::newFromText($this->newValue, NS_CATEGORY)));
		} else {
			foreach($objects as $o){


				if (is_null($this->newValue)) {
					// remove annotation

					$value = $o->getName();
					if (is_null($this->oldValue) || ucfirst($value) == ucfirst($this->oldValue)) {
						$toDelete[] = $o->getObjectID();
						$logMessages[$title->getPrefixedText()][] = new SRFLog("Deleted category $1 ", $title, "", array(Title::newFromText($this->oldValue, NS_CATEGORY)));
					}

				} else {

					// change values
					$value = $o->getName();

					if (is_null($this->oldValue) || ucfirst($value) == ucfirst($this->oldValue)) {

						$o->setName($this->newValue);
						$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed category '$1' into '$2' ", $title, "", array(Title::newFromText($this->oldValue, NS_CATEGORY), Title::newFromText($this->newValue, NS_CATEGORY)));
					}

				}
			}
		}
		$toDelete = array_unique($toDelete);
		foreach($toDelete as $d) {
			$pom->removePageObject($d);
		}

		foreach($toAdd as $a) {
			$a->setObjectID(uniqid());
			$pom->appendChildObject($a);
		}


		$wikitext = $pom->getWikiText();

		// set final wiki text
		foreach($logMessages as $title => $set) {
			foreach($set as $lm) {
				$lm->setWikiText($wikitext);
			}
		}

		return $wikitext;
	}
}