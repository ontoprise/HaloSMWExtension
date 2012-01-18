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
class SRFChangeValueOperation extends SRFRefactoringOperation {
	private $instanceSet;
	private $property;
	private $oldValue; // empty means: add or set annotation
	private $newValue; // empty means: remove annotation
	private $set; // true means: annotation is set not added (default is false)

	private $subjectDBKeys;

	public function __construct($instanceSet, $property, $oldValue, $newValue, $set = false) {
		parent::__construct();
		foreach($instanceSet as $i) {
			$this->instanceSet[] = Title::newFromText($i);
		}
		$this->property = Title::newFromText($property, SMW_NS_PROPERTY);
		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
		$this->set = $set;
	}

	public function queryAffectedPages() {
		return $this->instanceSet;
	}

	public function getNumberOfAffectedPages() {
		return count($this->instanceSet);
	}

	public function refactor($save = true, & $logMessages) {
		foreach($this->instanceSet as $title) {

			$rev = Revision::newFromTitle($title);
			$wikitext = $this->changeContent($title, $rev->getRawText(), $logMessages);
			if (!is_null($this->mBot)) $this->mBot->worked(1);

			// stores article
			if ($save) {
				$status = $this->storeArticle($title, $wikitext, $rev->getRawComment());
				if (!$status->isGood()) {
					$l = new SRFLog('Saving of $title failed due to: $1', $title, $wikitext, array($status->getWikiText()));
					$l->setLogType(SREF_LOG_STATUS_WARN);
					$logMessages[$title->getPrefixedText()][] = $l;
				}
			}
		}
	}



	/**
	 * Replaces old value with new.
	 * Callback method for array_walk
	 *
	 * @param string $title Prefixed title
	 * @param int $index
	 */
	protected function replaceValue(& $value, $index) {
		if ($this->set || ucfirst($value) == ucfirst($this->oldValue)) {
			$value = $this->newValue;
		}
	}

	public function changeContent($title, $wikitext, & $logMessages) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);

		$toDelete = array();
		$toAdd = array();

		if (is_null($this->oldValue) && !$this->set) {
			// add new annotation
			$toAdd[] = new WOMPropertyModel($this->property->getText(), $this->newValue);
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Added value '$2' for $1 ", $title, "", array($this->property, $this->newValue));
		}

		foreach($objects as $o){

			$name = $o->getProperty()->getDataItem()->getLabel();
			if (is_null($this->newValue)) {
				// remove annotation
				if ($name == $this->property->getText()) {
					$value = $o->getPropertyValue();
					if (is_null($this->oldValue) || ucfirst($value) == ucfirst($this->oldValue)) {
						$toDelete[] = $o->getObjectID();
						$logMessages[$title->getPrefixedText()][] = new SRFLog("Deleted value '$2' for $1 ", $title, "", array($this->property, $this->oldValue));
					}
				}
			} else  {

				// change values
				$value = $o->getPropertyValue();
				if ($name == $this->property->getText()) {

					if ($this->set || ucfirst($value) == ucfirst($this->oldValue)) {
							
						$values = $this->splitRecordValues($value);
						array_walk($values, array($this, 'replaceValue'));
						$newValue = implode("; ", $values);
						if ($value != $newValue) {
							if ($this->set) {
								$logMessages[$title->getPrefixedText()][] = new SRFLog("Set value '$2' for $1 ", $title, "", array($this->property, $this->newValue));
							} else {
								$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed value '$2' into '$3' for $1 ", $title, "", array($this->property, $this->oldValue, $this->newValue));

							}
						}

						$newDataValue = SMWDataValueFactory::newPropertyObjectValue($o->getProperty()->getDataItem(), $newValue);
						$o->setSMWDataValue($newDataValue);
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