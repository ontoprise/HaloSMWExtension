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
class SRFRenameCategoryOperation extends SRFRenameOperation {
	

	public function __construct($old, $new) {
		parent::__construct();
		$this->old = Title::newFromText($old, NS_CATEGORY);
		$this->new = Title::newFromText($new, NS_CATEGORY);


	}
	


	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		// get all pages using $this->old as category annotation
		$titles = array();
		$subjects = smwfGetSemanticStore()->getDirectInstances($this->old);
		foreach($subjects as $s) {
			$titles[] = $s;
		}

		$subjects = smwfGetSemanticStore()->getDirectSubCategories($this->old);
		foreach($subjects as $tuple) {
			list($s, $hasSubcategories) = $tuple;
			$titles[] = $s;
		}


		// get all pages using $this->old as property value
		$categoryDi = SMWDIWikiPage::newFromTitle($this->old);
		$properties = smwfGetStore()->getInProperties($categoryDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $categoryDi);
			foreach($subjects as $s) {
				$titles[] = $s->getTitle();
			}
		}


		// get all pages which uses links to $this->old
		$subjects = $this->old->getLinksTo();
		foreach($subjects as $s) {
			$titles[] = $s;
		}


		// get all queries using $this->old
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($this->old->getPrefixedText() => true);
		$queryMetadataPattern->categoryConditions = array($this->old->getText() => true);
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
            if ($title->getNamespace() == SGA_NS_LOG) continue;
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
				$changedQuery |= $this->replaceCategoryAnnotation($results);
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_NESTPROPERTY, $results);
				$changedQuery |= $this->replaceValueInNestedProperty($results);

				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$changedQuery |=  $this->replaceLink($results);

			}
		}

		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_CATEGORY);
		$changedCategoryAnnotation = $this->replaceCategoryAnnotation($objects);

		# iterate through the annotation values
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$changedCategoryValue = $this->replaceValueInAnnotation($objects);


		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$changedCategoryLink = $this->replaceLink($objects);


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
//		print "\n-----------------";
//		print "\n$wikitext";
		return $wikitext;
	}
}