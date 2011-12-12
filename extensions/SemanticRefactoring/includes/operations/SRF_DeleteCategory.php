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
 *
 * @author Kai Kuehn
 *
 */
class SRFDeleteCategoryOperation extends SRFRefactoringOperation {

	var $category;
	var $options;
	var $affectedPages;

	public function __construct($category, $options) {

		$this->category = Title::newFromText($category, NS_CATEGORY);
		$this->options = $options;
		$this->affectedPages = NULL;
	}

	public function getNumberOfAffectedPages() {

		$this->affectedPages = $this->queryAffectedPages();
		$num += (array_key_exists('removeInstances', $this->options) && $this->options['removeInstances'] == true)
		|| (array_key_exists('removeCategoryAnnotations', $this->options) && $this->options['removeCategoryAnnotations'] == true) ? count($affectedPages['instances']) : 0;

		$num += array_key_exists('removeQueries', $this->options) && $this->options['removeQueries'] == true ? count($affectedPages['queries']) : 0;

		if (array_key_exists('includeSubcategories', $this->options) && $this->options['includeSubcategories'] == true) {
				
			if (array_key_exists('removeInstances', $this->options) && $this->options['removeInstances'] == true) {
				$num += $smwfGetSemanticStore()->getNumberOfInstancesAndSubcategories();
			} else {
				$subcategories = $store->getSubCategories($this->category);
				$num += count($subcategories);
			}
		}

		return $num;
	}

	public function queryAffectedPages() {

		// calculate only once
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		$store = smwfGetSemanticStore();
		$instances = $store->getDirectInstances($this->category);
		$directSubcategories = $store->getDirectSubCategories($this->category);

		// get all queries $this->category is used in
		$queries = array();
		$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOC_LABEL);
		$categoryStringDi = new SMWDIString($this->category->getText());
		$subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $categoryStringDi);
		foreach($subjects as $s) {
			$queries[] = $s->getTitle();
		}
		$this->affectedPages = array();
		$this->affectedPages['instances'] = $instances;
		$this->affectedPages['queries'] = $queries;
		$this->affectedPages['directSubcategories'] = $directSubcategories;

		return $this->affectedPages;
	}

	public function refactor($save = true, & $logMessages, & $testData = NULL) {
		$results = $this->queryAffectedPages();
		
		if (array_key_exists('onlyCategory', $this->options) && $this->options['onlyCategory'] == true) {
			$a = new Article($this->category);
			if ($save) {
				if (!SRFTools::deleteArticle($a)) {
					$logMessages[] = 'Deletion failed: '.$this->category->getPrefixedText();
				}
			}
			$logMessages[] = 'Article deleted: '.$this->category->getPrefixedText();
			if (!is_null($testData)) {
				$testData[$this->category->getPrefixedText()] = 'deleted';
			}
			if (!is_null($this->mBot)) $this->mBot->worked(1);
			return;
		}


		if (array_key_exists('removeInstances', $this->options) && $this->options['removeInstances'] == true) {
			// if instances are completely removed, there is no need to remove annotations before
			foreach($results['instances'] as $i) {
				$a = new Article($i);
				if ($save) {
					if (!SRFTools::deleteArticle($a)) {
						$logMessages[] = 'Deletion failed: '.$i->getPrefixedText();
					}
				}
				$logMessages[] = 'Article deleted: '.$i->getPrefixedText();
				if (!is_null($testData)) {
					$testData[$i->getPrefixedText()] = 'deleted';
				}
				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}
		} else if (array_key_exists('removeCategoryAnnotations', $this->options) && $this->options['removeCategoryAnnotations'] == true) {
			foreach($results['instances'] as $i) {
				$rev = Revision::newFromTitle($i);
				if (is_null($rev)) continue;
				$wikitext = $this->removeCategoryAnnotation($rev->getRawText());
				if ($save) {
					$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
				}
				$logMessages[] = 'Removed category annotation from: '.$i->getPrefixedText();
				if (!is_null($testData)) {
					$testData[$i->getPrefixedText()] = array('removeCategoryAnnotations', $wikitext);
				}
				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}
		}

		if (array_key_exists('removeQueries', $this->options) && $this->options['removeQueries'] == true) {
			foreach($results['queries'] as $q) {
				$rev = Revision::newFromTitle($q);
				if (is_null($rev)) continue;
				$wikitext = $this->removeQuery($rev->getRawText());
				if ($save) {
					$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
				}
				$logMessages[] = 'Removed query from: '.$q->getPrefixedText();
				if (!is_null($testData)) {
					$testData[$q->getPrefixedText()] = array('removeCategoryAnnotations', $wikitext);
				}
				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}
		}

		if (array_key_exists('includeSubcategories', $this->options) && $this->options['includeSubcategories'] == true) {
			foreach($results['directSubcategories'] as $c) {
				$op = new SRFDeleteCategoryOperation($c, $this->options);
				$op->setBot($mBot);
				$op->refactor($save, $logMessages, $testData);
			}
		}


	}

	private function removeQuery($wikitext) {

		$wom = WOMProcessor::parseToWOM($wikitext);
		$toDelete = array();

		# iterate trough the annotations
		$objects = $wom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);

		foreach($objects as $o){
			$results = array();
			$this->findObjectByID($o, WOM_TYPE_CATEGORY, $results);
			foreach($results as $c){
				$name = $c->getName();
				if ($name == $this->category->getText()) {
					$toDelete[] = $o->getObjectID();
				}
			}

		}
		
		$toDelete = array_unique($toDelete);

		foreach($toDelete as $id) {
			$wom->removePageObject($id);
		}

		$wikitext = $wom->getWikiText();
		return $wikitext;
	}

	private function removeCategoryAnnotation($wikitext) {

		$wom = WOMProcessor::parseToWOM($wikitext);
		$toDelete = array();

		# iterate trough the annotations
		$objects = $wom->getObjectsByTypeID(WOM_TYPE_CATEGORY);
		foreach($objects as $o){

			$name = $o->getName();
			if ($name == $this->category->getText()) {
				$toDelete[] = $o;
			}

		}

		foreach($toDelete as $d) {
			$wom->removePageObject($d->getObjectID());
		}

		$wikitext = $wom->getWikiText();
		return $wikitext;
	}


}