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

	public function __construct($oldProperty, $newProperty) {
		$this->oldProperty = Title::newFromText($oldProperty, SMW_NS_PROPERTY);
		$this->newProperty = Title::newFromText($newProperty, SMW_NS_PROPERTY);;

	}

	public function getNumberOfAffectedPages() {
		$this->affectedPages = $this->queryAffectedPages();
		return count($this->affectedPages);
	}

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		$titles=array();
		// get all pages using $this->property
		$propertyDi = SMWDIProperty::newFromUserLabel($this->oldProperty->getText());
		$subjects = smwfGetStore()->getAllPropertySubjects($propertyDi);
		foreach($subjects as $s) {
			$titles[] = $s->getTitle();
		}

		// get all pages using $this->property
		$objectDi = SMWDIWikiPage::newFromTitle($this->oldProperty);
		$properties = smwfGetStore()->getInProperties($objectDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $objectDi);
			foreach($subjects as $s) {
				$titles[] = $s->getTitle();
			}
		}

		// subproperties
		$subPropertyDi = SMWDIProperty::newFromUserLabel('_SUBP');
		$subjects = smwfGetStore()->getPropertySubjects($subPropertyDi, $objectDi);

		foreach($subjects as $s) {
			$titles[] = $s->getTitle();
		}


		// get all pages which uses links to $this->property
		$subjects = $this->oldProperty->getLinksTo();
		foreach($subjects as $s) {
			$titles[] = $s;
		}

		// get all queries using $this->property
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($this->oldProperty->getPrefixedText() => true);
		$queryMetadataPattern->propertyConditions = array($this->oldProperty->getText() => true);
		$queryMetadataPattern->propertyPrintRequests = array($this->oldProperty->getText() => true);
		
		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		foreach($qmr as $s) {
			$titles[] = Title::newFromText($s->usedInArticle);
		}

		$this->affectedPages = SRFTools::makeTitleListUnique($titles);
		return $this->affectedPages;
	}

	public function refactor($save = true, & $logMessages) {

		$this->queryAffectedPages();

		foreach($this->affectedPages as $title) {


			$rev = Revision::newFromTitle($title);

			$wikitext = $this->changeContent($title, $rev->getRawText(), $logMessages);

			// stores article
			if ($save) {
				$status = $this->storeArticle($title, $wikitext, $rev->getRawComment());
				if (!$status->isGood()) {
					$logMessages[$title->getPrefixedText()][] = new SRFLog('Saving of $title failed due to: $1', $title, $wikitext, array($status->getWikiText()));
				}
			}

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

		// some properties appear only with their local
		// name in annotations (e.g. Subproperty of)
		if ($title == $this->oldProperty->getPrefixedText()
		|| $title == $this->oldProperty->getText()) {

			$title = $this->newProperty->getPrefixedText();
		}
	}

	private function replacePropertyInAnnotation($objects) {
		$changed = false;
		foreach($objects as $o){

			$name = $o->getProperty()->getDataItem()->getLabel();
			if ($name == $this->oldProperty->getText()) {
				$o->setProperty(SMWPropertyValue::makeUserProperty($this->newProperty->getText()));
				$changed = true;
			}

			$value = $o->getPropertyValue();
			$values = $this->splitRecordValues($value);
			array_walk($values, array($this, 'replaceTitle'));
			$newValue = implode("; ", $values);

			if ($value != $newValue) {
				$changed = true; //FIXME: may be untrue because of whitespaces
			}

			$newDataValue = SMWDataValueFactory::newPropertyObjectValue($o->getProperty()->getDataItem(), $newValue);
			$o->setSMWDataValue($newDataValue);

		}
		return $changed;
	}

	private function replacePropertyInLink($objects) {
		$changed = false;
		foreach($objects as $o){


			$value = $o->getLink();

			if ($value == $this->oldProperty->getPrefixedText()) {
				$o->setLink($this->newProperty->getPrefixedText());
				$changed = true;
			}
		}
		return $changed;
	}

	private function replacePrintout($objects) {
		$changed = false;
		foreach($objects as $o){
			$value = $o->getWikiText();
			$value = trim($value);
			if ($value == '?'.$this->oldProperty->getText()) {
				$o->setText('?'.$this->newProperty->getText());
				$changed=true;
			}

		}
		return $changed;
	}
	public function changeContent($title, $wikitext, & $logMessages) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$changedAnnotation = $this->replacePropertyInAnnotation($objects);

		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$changedLink = $this->replacePropertyInLink($objects);

		# iterate trough queries
		# better support for ASK would be nice
		$changedQuery=false;
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);
		foreach($objects as $o){
			if ($o->getFunctionKey() == 'ask') {
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_PROPERTY, $results);
				$changedQuery = $changedQuery || $this->replacePropertyInAnnotation($results);
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$changedQuery = $changedQuery || $this->replacePropertyInLink($results);

				$results = array();
				$this->findObjectByID($o, WOM_TYPE_PARAM_VALUE, $results);
				foreach($results as $o) {
					$paramTexts = array();
					$this->findObjectByID($o, WOM_TYPE_TEXT, $paramTexts);
					$changedQuery = $changedQuery || $this->replacePrintout($paramTexts);
				}
			}
		}

		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();

		if ($changedAnnotation) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed property or value", $title, $wikitext);
		}
		if ($changedLink) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed link", $title, $wikitext);
		}
		if ($changedQuery) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed query", $title, $wikitext);
		}

		return $wikitext;
	}


}
