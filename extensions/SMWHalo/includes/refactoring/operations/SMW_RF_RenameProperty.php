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
class SMWRFRenamePropertyOperation extends SMWRFRefactoringOperation {

	private $oldProperty;
	private $newProperty;
	private $adaptAnnotations;

	private $subjectDBKeys;

	public function __construct($oldProperty, $newProperty, $adaptAnnotations) {
		$this->oldProperty = Title::newFromText($oldProperty, SMW_NS_PROPERTY);
		$this->newProperty = Title::newFromText($newProperty, SMW_NS_PROPERTY);;
		$this->adaptAnnotations = $adaptAnnotations;

	}

	public function getAffectedPages() {
		if (!$this->adaptAnnotations) return 0;

		// get all pages using $this->property
		$propertyDi = SMWDIProperty::newFromUserLabel($this->oldProperty->getText());
		$subjects = smwfGetStore()->getAllPropertySubjects($propertyDi);
		foreach($subjects as $s) {
			$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
		}
		
	    // get all pages which uses links to $this->property
        $subjects = $this->oldProperty->getLinksTo();
        foreach($subjects as $s) {
            $subjectDBKeys[] = $s->getPrefixedDBkey();
        }

		// get all queries using $this->property
		$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOP_LABEL);
		$propertyWPDi = SMWDIWikiPage::newFromTitle($this->oldProperty);
		$subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $propertyWPDi);
		foreach($subjects as $s) {
			$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
		}

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
			$this->oldProperty->moveTo($this->newProperty);
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
	public function changeContent($titleName, $wikitext) {
		$pom = new POMPage($titleName, $wikitext, array('POMExtendedParser'));

		# iterate trough the annotations
		$iterator = $pom->getProperties()->listIterator();
		while($iterator->hasNext()){
			$pomProperty = &$iterator->getNextNodeValueByReference(); # get reference for direct changes

			$name = $pomProperty->getName();
			if ($name == $this->oldProperty->getText()) {
				$pomProperty->setName($this->newProperty->getText());
			}

			$value = $pomProperty->getValue();
			$values = $this->splitRecordValues($value);
			array_walk($values, array($this, 'replaceTitle'));

			$pomProperty->setValue(implode("; ",$values));
			 
		}

		# iterate trough the links
		$iterator = $pom->getElements()->getShortcuts('POMLink')->listIterator();
		while($iterator->hasNext()){
			$pomProperty = &$iterator->getNextNodeValueByReference(); # get reference for direct changes

			$value = $pomProperty->getValue();

			if ($value == $this->oldProperty->getPrefixedText()) {
				$pomProperty->setValue($this->newProperty->getPrefixedText());
			}
		}

		#iterate trough queries
		$iterator = $pom->getElements()->getShortcuts('POMAskFunction')->listIterator();
		$quotedCategoryName = preg_quote($this->oldProperty->getText());
		$quotedCategoryPrefixedName = preg_quote($this->oldProperty->getPrefixedText());
		while($iterator->hasNext()){

			$pomQuery = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
			$queryText = $pomQuery->toString();

			// replace property as property
			$queryText = preg_replace('/\[\[\s*'.$quotedCategoryName.'\s*::([^]])\]\]/i', "[[".$this->newProperty->getText()."::$1]]", $queryText);

			// replace property as value
			$queryText = preg_replace('/\[\[([^:]|:[^:])+::\s*'.$quotedCategoryPrefixedName.'\s*\]\]/i', "[[$1::".$this->newProperty->getPrefixedText()."]]", $queryText);

			// replace property as instance
			$queryText = preg_replace('/\[\[\s*'.$quotedCategoryPrefixedName.'\s*\]\]/i', "[[".$this->newProperty->getPrefixedText()."]]", $queryText);

			$pomQuery->setNodeText($queryText);
		}

		# TODO: iterate through rules
		# not yet implemented in Data-API

		// calls sync() internally
		$wikitext = $pom->toString();
		return $wikitext;
	}
}
