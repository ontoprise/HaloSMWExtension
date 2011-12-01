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
class SMWRFRenameCategoryOperation extends SMWRFRefactoringOperation {
	private $oldCategory;
	private $newCategory;
	private $adaptAnnotations;

	private $subjectDBKeys;

	public function __construct($oldCategory, $newCategory, $adaptAnnotations) {
		$this->oldCategory = Title::newFromText($oldCategory, NS_CATEGORY);
		$this->newCategory = Title::newFromText($newCategory, NS_CATEGORY);
		$this->adaptAnnotations = $adaptAnnotations;

	}

	public function getAffectedPages() {
		if (!$this->adaptAnnotations) return 0;

		// get all pages using $this->oldCategory as category annotation
		$propertyDi = SMWDIProperty::newFromUserLabel('_TYPE');
		$subjects = smwfGetStore()->getAllPropertySubjects($propertyDi);
		foreach($subjects as $s) {
			$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
		}

		// get all pages using $this->oldCategory as property value
		$categoryDi = SMWDIWikiPage::newFromTitle($this->oldCategory);
		$properties = smwfGetStore()->getInProperties($categoryDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $categoryDi);
			foreach($subjects as $s) {
				$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
			}
		}

		// get all queries using $this->oldCategory
		$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOC_LABEL);
		$propertyWPDi = SMWDIWikiPage::newFromTitle($this->oldCategory);
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
			$this->oldCategory->moveTo($this->newCategory);
		}
	}

	public function changeContent($titleName, $wikitext) {
		$pom = new POMPage($titleName, $wikitext, array('POMExtendedParser'));

		# iterate trough the category annotations
		$iterator = $pom->getCategories()->listIterator();
		while($iterator->hasNext()){
			$pomCategory = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
			$value = $pomCategory->getValue();
			if ($value == $this->oldCategory->getText()) {
				$pomCategory->setValue($this->newCategory->getText());
			}
		}

		# iterate through property values
		$iterator = $pom->getProperties()->listIterator();
		while($iterator->hasNext()){
			$pomProperty = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
		
			$value = $pomProperty->getValue();
			if ($value == $this->oldCategory->getPrefixedText()) {
				$pomProperty->setValue($this->newCategory->getPrefixedText());
			}
		}
		
		# iterate trough the links
        $iterator = $pom->getElements()->getShortcuts('POMLink')->listIterator();
        while($iterator->hasNext()){
            $pomProperty = &$iterator->getNextNodeValueByReference(); # get reference for direct changes

            $value = $pomProperty->getValue();
           
            if ($value == ":".$this->oldCategory->getPrefixedText()) {
                $pomProperty->setValue(":".$this->newCategory->getPrefixedText());
            }
        }
		

		#iterate trough queries
		$iterator = $pom->getElements()->getShortcuts('POMAskFunction')->listIterator();
		$quotedCategoryName = preg_quote($this->oldCategory->getText());
		$quotedCategoryPrefixedName = preg_quote($this->oldCategory->getPrefixedText());
		while($iterator->hasNext()){

			$pomQuery = &$iterator->getNextNodeValueByReference(); # get reference for direct changes
			$queryText = $pomQuery->toString();

			// replace category as category
			$queryText = preg_replace('/\[\[\s*'.$quotedCategoryPrefixedName.'\s*\]\]/i', "[[".$this->newCategory->getPrefixedText()."]]", $queryText);

			// replace category as value
			$queryText = preg_replace('/\[\[([^:]|:[^:])+::\s*'.$quotedCategoryPrefixedName.'\s*\]\]/i', "[[$1::".$this->newCategory->getPrefixedText()."]]", $queryText);

			// replace category as category-link
			$queryText = preg_replace('/\[\[\s*:'.$quotedCategoryPrefixedName.'\s*\]\]/i', "[[:".$this->newCategory->getPrefixedText()."]]", $queryText);

			$pomQuery->setNodeText($queryText);
		}

		# TODO: iterate through rules
		# not yet implemented in Data-API

		// calls sync() internally
		$wikitext = $pom->toString();
		return $wikitext;
	}
}