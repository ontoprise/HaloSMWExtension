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
class SMWRFRenameInstanceOperation extends SMWRFRefactoringOperation {
	private $oldInstance;
	private $newInstance;
	private $adaptAnnotations;

	private $subjectDBKeys;

	public function __construct($oldInstance, $newInstance, $adaptAnnotations) {
		$this->oldInstance = Title::newFromText($oldInstance);
		$this->newInstance = Title::newFromText($newInstance);
		$this->adaptAnnotations = $adaptAnnotations;

	}

	public function getAffectedPages() {
		if (!$this->adaptAnnotations) return 0;

		// get all pages using $this->oldInstance
		$instanceDi = SMWDIWikiPage::newFromTitle($this->oldInstance);
		$properties = smwfGetStore()->getInProperties($instanceDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $instanceDi);
			foreach($subjects as $s) {
				$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
			}
		}

		// get all queries using $this->oldInstance
		// TODO: QRC_DOI_LABEL is missing
		/*$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOI_LABEL);
		 $propertyWPDi = SMWDIWikiPage::newFromTitle($this->oldInstance);
		 $subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $propertyWPDi);
		 foreach($subjects as $s) {
			$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
			}*/

		$subjectDBKeys = array_unique($subjectDBKeys);
		return $subjectDBKeys;
	}

	public function refactor($save = true) {

		$subjectDBkeys = $this->getAffectedPages();

		foreach($subjectDBkeys as $dbkey) {
			$title = Title::newFromDBkey($dbkey);
			$rev = Revision::newFromTitle($title);

			$wikitext = $this->changeContent($title->getText(), $rev->getRawText());

			// stores article
			if ($save) {
				$a = new Article($title);
				$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
			}
		}

		// move article
		if ($save) {
			$this->oldInstance->moveTo($this->newInstance);
		}
	}

	public function changeContent($titleName, $wikitext) {
		$pom = new POMPage($titleName, $wikitext, array('POMExtendedParser'));

		# iterate trough the annotations
		$iterator = $pom->getProperties()->listIterator();
		while($iterator->hasNext()){
			$pomProperty = &$iterator->getNextNodeValueByReference(); # get reference for direct changes

			$name = $pomProperty->getName();
			if ($name == $this->oldInstance->getText()) {
				$pomProperty->setName($this->newInstance->getText());
			}

			$value = $pomProperty->getValue();
			if ($value == $this->oldInstance->getPrefixedText()) {
				$pomProperty->setValue($this->newInstance->getPrefixedText());
			}
		}

		# iterate trough the links
		$iterator = $pom->getElements()->getShortcuts('POMLink')->listIterator();
		while($iterator->hasNext()){
			$pomProperty = &$iterator->getNextNodeValueByReference(); # get reference for direct changes

			$value = $pomProperty->getValue();
			 
			if ($value == $this->oldInstance->getPrefixedText()) {
				$pomProperty->setValue(":".$this->newInstance->getPrefixedText());
			}
		}

		#iterate trough queries
		$iterator = $pom->getElements()->getShortcuts('POMAskFunction')->listIterator();
		$quotedInstanceName = preg_quote($this->oldInstance->getText());
		$quotedInstancePrefixedName = preg_quote($this->oldInstance->getPrefixedText());
		while($iterator->hasNext()){

			$pomQuery = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
			$queryText = $pomQuery->toString();

			// replace instance as instance
			$queryText = preg_replace('/\[\[\s*'.$quotedInstancePrefixedName.'\s*\]\]/i', "[[".$this->newInstance->getPrefixedText()."]]", $queryText);

			// replace instance as value
			$queryText = preg_replace('/\[\[([^:]|:[^:])+::\s*'.$quotedInstancePrefixedName.'\s*\]\]/i', "[[$1::".$this->newInstance->getPrefixedText()."]]", $queryText);

			$pomQuery->setNodeText($queryText);
		}

		# TODO: iterate through rules
		# not yet implemented in Data-API

		// calls sync() internally
		$wikitext = $pom->toString();
		return $wikitext;
	}
}
