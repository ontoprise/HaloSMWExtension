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

	protected function replaceValueInAnnotation($objects) {
		foreach($objects as $o){

			$name = $o->getProperty()->getDataItem()->getLabel();
			if ($name == $this->oldProperty->getText()) {
				$o->setProperty(SMWPropertyValue::makeUserProperty($this->newProperty->getText()));
			}

			$value = $o->getPropertyValue();
			$values = $this->splitRecordValues($value);
			array_walk($values, array($this, 'replaceTitle'));


			$newValue = SMWDataValueFactory::newPropertyObjectValue($o->getProperty()->getDataItem(), implode("; ", $values));
			$o->setSMWDataValue($newValue);

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
		if (ucfirst($value) == ucfirst($this->oldValue)) {
			$value = $this->newValue;
		}
	}

	public function changeContent($titleName, $wikitext) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);

		$toDelete = array();
		$toAdd = array();

		foreach($objects as $o){

			$name = $o->getProperty()->getDataItem()->getLabel();
			if (is_null($this->newValue)) {
				// remove annotation
				if ($name == $this->property->getText()) {
					$value = $o->getPropertyValue();
					if (is_null($this->oldValue) || ucfirst($value) == ucfirst($this->oldValue)) {
						$toDelete[] = $o;
					}
				}
			} else if (is_null($this->oldValue)) {
				// add new annotation
				$toAdd[] = new WOMPropertyModel($this->property->getText(), $this->newValue);

			} else {
				$value = $o->getPropertyValue();
				
				if ($name == $this->property->getText()) {
					
					if (is_null($this->oldValue) || ucfirst($value) == ucfirst($this->oldValue)) {
					
						$values = $this->splitRecordValues($value);
							
						array_walk($values, array($this, 'replaceValue'));
						
						$newValue = SMWDataValueFactory::newPropertyObjectValue($o->getProperty()->getDataItem(), implode("; ", $values));
						$o->setSMWDataValue($newValue);
					}
				}
			}
		}

		foreach($toDelete as $d) {
			$pom->removePageObject($d->getObjectID());
		}

		foreach($toAdd as $a) {
			$a->setObjectID(uniqid());
			$pom->appendChildObject($a);
		}


		// calls sync() internally
		$wikitext = $pom->getWikiText();
		return $wikitext;
	}
}