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
		$this->previewData['sref_changedInstances'] = 0;
		$subjects = smwfGetSemanticStore()->getDirectInstances($this->old);
		foreach($subjects as $s) {
			$this->previewData['sref_changedInstances'] += 1;
			$titles[] = $s;
		}

		$subjects = smwfGetSemanticStore()->getDirectSubCategories($this->old);
		$this->previewData['sref_changedSubcategories'] = 0;
		foreach($subjects as $tuple) {
			list($s, $hasSubcategories) = $tuple;
			$this->previewData['sref_changedSubcategories'] += 1;
			$titles[] = $s;
		}


		// get all pages using $this->old as property value
		$categoryDi = SMWDIWikiPage::newFromTitle($this->old);
		$properties = smwfGetStore()->getInProperties($categoryDi);
		$this->previewData['sref_changedPropertyvalues'] = 0;
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $categoryDi);
			foreach($subjects as $s) {
				$this->previewData['sref_changedPropertyvalues'] += 1;
				$titles[] = $s->getTitle();
			}
		}


		// get all pages which uses links to $this->old
		$subjects = $this->old->getLinksTo();
		$this->previewData['sref_changedLinks'] = 0;
		foreach($subjects as $s) {
			$titles[] = $s;
			$this->previewData['sref_changedLinks'] += 1;
		}


		// get all queries using $this->old
		$this->previewData['sref_changedQueries'] = 0;
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($this->old->getPrefixedText() => true);
		$queryMetadataPattern->categoryConditions = array($this->old->getText() => true);
		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		foreach($qmr as $s) {
			$titles[] = Title::newFromText($s->usedInArticle);
			$this->previewData['sref_changedQueries'] += 1;
		}

		$this->affectedPages = SRFTools::makeTitleListUnique($titles);
		return $this->affectedPages;
	}

   
	
	
	public function applyOperation($title, $wikitext, & $logMessages) {
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
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 in a category annotation.', $title, $wikitext, array($this->old, $this->new));
		}
		if ($changedCategoryValue) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 in an annotation value.', $title, $wikitext, array($this->old, $this->new));
		}
		if ($changedCategoryLink) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 in link.', $title, $wikitext, array($this->old, $this->new));
		}
		if ($changedQuery) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 in a query.', $title, $wikitext, array($this->old, $this->new));
		}
//		print "\n-----------------";
//		print "\n$wikitext";
		return $wikitext;
	}
}