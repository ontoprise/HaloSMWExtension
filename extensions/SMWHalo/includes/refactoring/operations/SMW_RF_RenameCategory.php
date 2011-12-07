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

		// get all pages which uses links to $this->oldCategory
		$subjects = $this->oldCategory->getLinksTo();
		foreach($subjects as $s) {
			$subjectDBKeys[] = $s->getPrefixedDBkey();
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

	/**
	 * Replaces old category with new.
	 * Callback method for array_walk
	 *
	 * @param string $title Prefixed title
	 * @param int $index
	 */
	public function replaceTitle(& $title, $index) {
		
		if ($title == ":".$this->oldCategory->getPrefixedText()) {
			$changed = true;
			$title = $this->newCategory->getPrefixedText();
		}
	}

	private function replaceCategoryInAnnotation($objects) {
		foreach($objects as $o){

			$name = $o->getName();
			if ($name == $this->oldCategory->getText()) {
				$o->setName($this->newCategory->getText());
			}

		}
	}

	private function replaceCategoryInLink($objects) {
		foreach($objects as $o){
			$value = $o->getLink();

			if ($value == ":".$this->oldCategory->getPrefixedText()) {
				$o->setLink(":".$this->newCategory->getPrefixedText());
			}
		}
	}

	 
	public function changeContent($titleName, $wikitext) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_CATEGORY);
		$this->replaceCategoryInAnnotation($objects);

		# iterate through the annotation values
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$this->replaceValueInAnnotation($objects);


		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$this->replaceCategoryInLink($objects);

		# iterate trough queries
		# better support for ASK would be nice
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);
		foreach($objects as $o){
			if ($o->getFunctionKey() == 'ask') {
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_CATEGORY, $results);
				$this->replaceCategoryInAnnotation($results);
				
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$this->replaceCategoryInLink($results);

			}
		}

		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();
		return $wikitext;
	}
}