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
class SRFRenameCategoryOperation extends SRFRefactoringOperation {
	private $oldCategory;
	private $newCategory;

	private $affectedPages;



	public function __construct($oldCategory, $newCategory) {
		parent::__construct();
		$this->oldCategory = Title::newFromText($oldCategory, NS_CATEGORY);
		$this->newCategory = Title::newFromText($newCategory, NS_CATEGORY);


	}

	public function getNumberOfAffectedPages() {
		$this->affectedPages = $this->queryAffectedPages();
		return count($this->affectedPages);
	}

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		// get all pages using $this->oldCategory as category annotation
		$titles = array();
		$subjects = smwfGetSemanticStore()->getDirectInstances($this->oldCategory);
		foreach($subjects as $s) {
			$titles[] = $s;
		}

		$subjects = smwfGetSemanticStore()->getDirectSubCategories($this->oldCategory);
		foreach($subjects as $tuple) {
			list($s, $hasSubcategories) = $tuple;
			$titles[] = $s;
		}


		// get all pages using $this->oldCategory as property value
		$categoryDi = SMWDIWikiPage::newFromTitle($this->oldCategory);
		$properties = smwfGetStore()->getInProperties($categoryDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $categoryDi);
			foreach($subjects as $s) {
				$titles[] = $s->getTitle();
			}
		}


		// get all pages which uses links to $this->oldCategory
		$subjects = $this->oldCategory->getLinksTo();
		foreach($subjects as $s) {
			$titles[] = $s;
		}


		// get all queries using $this->oldCategory
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($this->oldCategory->getPrefixedText() => true);
		$queryMetadataPattern->categoryConditions = array($this->oldCategory->getText() => true);
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
		$changed = false;
		foreach($objects as $o){

			$name = $o->getName();
			if ($name == $this->oldCategory->getText()) {
				$o->setName($this->newCategory->getText());
				$changed = true;
			}

		}
		return $changed;
	}

	private function replaceCategoryInLink($objects) {
		$changed = false;
		foreach($objects as $o){
			$value = $o->getLink();

			if ($value == ":".$this->oldCategory->getPrefixedText()) {
				$o->setLink(":".$this->newCategory->getPrefixedText());
				$changed = true;
			}
		}
		return $changed;
	}


	public function changeContent($title, $wikitext, & $logMessages) {
		$pom = WOMProcessor::parseToWOM($wikitext);
		# iterate trough queries
		# better support for ASK would be nice
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);
		$changedQuery =false;
		foreach($objects as $o){
			if ($o->getFunctionKey() == 'ask') {
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_CATEGORY, $results);
				$changedQuery = $changedQuery || $this->replaceCategoryInAnnotation($results);

				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$changedQuery = $changedQuery || $this->replaceCategoryInLink($results);

			}
		}

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_CATEGORY);
		$changedCategoryAnnotation = $this->replaceCategoryInAnnotation($objects);

		# iterate through the annotation values
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$changedCategoryValue = $this->replaceValueInAnnotation($objects);


		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$changedCategoryLink = $this->replaceCategoryInLink($objects);


		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();

		if ($changedCategoryAnnotation) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed category annotation", $title, $wikitext);
		}
		if ($changedCategoryValue) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed category as annotation value", $title, $wikitext);
		}
		if ($changedCategoryLink) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed link", $title, $wikitext);
		}
		if ($changedQuery) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed query", $title, $wikitext);
		}
		return $wikitext;
	}
}