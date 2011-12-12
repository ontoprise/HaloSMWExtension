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
 * Rename operation for a property.
 *
 * @author Kai Kuehn
 *
 */
class SRFRenamePropertyOperation extends SRFRefactoringOperation {

	private $oldProperty;
	private $newProperty;
	private $affectedPages;

	public function __construct($oldProperty, $newProperty, $adaptAnnotations) {
		$this->oldProperty = Title::newFromText($oldProperty, SMW_NS_PROPERTY);
		$this->newProperty = Title::newFromText($newProperty, SMW_NS_PROPERTY);;

	}

	public function getNumberOfAffectedPages() {
		$this->affectedPages = $this->queryAffectedPages();
		return count($this->affectedPages);
	}

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		// get all pages using $this->property
		$propertyDi = SMWDIProperty::newFromUserLabel($this->oldProperty->getText());
		$subjects = smwfGetStore()->getAllPropertySubjects($propertyDi);
		foreach($subjects as $s) {
			$subjects[] = $s->getTitle();
		}

		// get all pages which uses links to $this->property
		$subjects = $this->oldProperty->getLinksTo();
		foreach($subjects as $s) {
			$subjects[] = $s;
		}

		// get all queries using $this->property
		$queries = array();
		$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOP_LABEL);
		$propertyStringDi = new SMWDIString($this->property->getText());
		$subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $propertyStringDi);
		foreach($subjects as $s) {
			$queries[] = $s->getTitle();
		}

		$this->affectedPages = SRFTools::makeTitleListUnique($subjects);
		return $this->affectedPages;
	}

	public function refactor($save = true, & $logMessages, & $testData = NULL) {

		$this->queryAffectedPages();

		foreach($this->affectedPages as $title) {

			$rev = Revision::newFromTitle($title);

			$wikitext = $this->changeContent($rev->getRawText());

			// stores article
			if ($save) {
				$a = new Article($title);
				$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
			}
			$logMessages[] = 'Content of "'.$i->getPrefixedText().'" changed.';
			if (!is_null($this->mBot)) $this->mBot->worked(1);
		}
	
	}


	/**
	 * Replaces old property with new.
	 * Callback method for array_walk
	 *
	 * @param string $title Prefixed title
	 * @param int $index
	 */
	protected function replaceTitle(& $title, $index) {
		if ($title == $this->oldProperty->getPrefixedText()) {

			$title = $this->newProperty->getPrefixedText();
		}
	}

	private function replacePropertyInAnnotation($objects) {
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

	private function replacePropertyInLink($objects) {
		foreach($objects as $o){


			$value = $o->getLink();

			if ($value == $this->oldProperty->getPrefixedText()) {
				$o->setLink($this->newProperty->getPrefixedText());
			}
		}
	}

	private function replacePrintout($objects) {
		foreach($objects as $o){
			$value = $o->getWikiText();
			$value = trim($value);
			if ($value == '?'.$this->oldProperty->getText()) {
				$o->setText('?'.$this->newProperty->getText());
			}

		}
	}
	public function changeContent($wikitext) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$this->replacePropertyInAnnotation($objects);

		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$this->replacePropertyInLink($objects);

		# iterate trough queries
		# better support for ASK would be nice
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);
		foreach($objects as $o){
			if ($o->getFunctionKey() == 'ask') {
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_PROPERTY, $results);
				$this->replacePropertyInAnnotation($results);
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$this->replacePropertyInLink($results);

				$results = array();
				$this->findObjectByID($o, WOM_TYPE_PARAM_VALUE, $results);
				foreach($results as $o) {
					$paramTexts = array();
					$this->findObjectByID($o, WOM_TYPE_TEXT, $paramTexts);
					$this->replacePrintout($paramTexts);
				}
			}
		}

		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();
		return $wikitext;
	}


}
