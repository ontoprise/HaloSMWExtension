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
    var $totalWork;

	public function __construct($category, $options) {
		parent::__construct();
		$this->category = Title::newFromText($category, NS_CATEGORY);
		$this->options = $options;
        $this->totalWork = -1;
	}

	public function getWork() {
		if ($this->totalWork == -1) {
			$this->queryAffectedPages();
		}
		return $this->totalWork;
	}


	private function collectWorkForCategory($category, & $num, & $previewData) {
		$affectedPages = $this->getAffectedPagesForCategory($category);

		
		if ( $this->isOptionSet('sref_removeInstances', $this->options)) {
			$num += count($affectedPages['instances']);
			$previewData['sref_deletedInstances'] += count($affectedPages['instances']);
		} else {
			if ( $this->isOptionSet('sref_removeCategoryAnnotations', $this->options)) {
				$num += count($affectedPages['instances']);
				$previewData['sref_changedInstances'] += count($affectedPages['instances']);
			}
		}
		if ( $this->isOptionSet('sref_removeQueriesWithCategories', $this->options)) {
			$num += count($affectedPages['queries']);
			$previewData['sref_changedQueries'] += count($affectedPages['queries']);
		}
		if ( $this->isOptionSet('sref_removePropertyWithDomain', $this->options)) {
			$num += count($affectedPages['propertiesWithDomain']);
			$previewData['sref_deletedPropertyWithDomain'] += count($affectedPages['propertiesWithDomain']);
		} else if ( $this->isOptionSet('sref_removeFromDomain', $this->options)) {
			$num += count($affectedPages['propertiesWithDomain']);
			$previewData['sref_changedPropertyWithDomain'] += count($affectedPages['propertiesWithDomain']);
		}

		
	}

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;
		$this->affectedPages = $this->getAffectedPagesForCategory($this->category);
		$this->previewData['sref_changedQueries'] = 0;
		$this->previewData['sref_changedInstances'] = 0;
		$this->previewData['sref_deletedInstances'] = 0;
		$this->previewData['sref_deletedPropertyWithDomain'] = 0;
		$this->previewData['sref_changedPropertyWithDomain'] = 0;

		$this->totalWork = 0;
		$this->collectWorkForCategory($this->category, $this->totalWork, $this->previewData);
		if ($this->isOptionSet('sref_includeSubcategories', $this->options)) {
			$subcategories = $store->getSubCategories($this->category);
			foreach($subcategories as $s) {
				$this->collectWorkForCategory($s, $this->totalWork, $this->previewData);
			}
		}
		
		return $this->affectedPages;
	}

	private function getAffectedPagesForCategory($category) {
		// calculate only once

		$store = smwfGetSemanticStore();
		$instances = $store->getDirectInstances($category);
		$directSubcategories = $store->getDirectSubCategories($category);

		// get all queries $this->category is used in
		$queries=array();
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($category->getPrefixedText() => true);
		$queryMetadataPattern->categoryConditions = array($category->getText() => true);
		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		foreach($qmr as $s) {
			$queries[] = Title::newFromText($s->usedInArticle);
		}

		// get properties with domain and/or ranges
		$propertiesWithDomain = $store->getPropertiesWithDomain($category);

		$affectedPages = array();
		$affectedPages['instances'] = $instances;
		$affectedPages['queries'] = $queries;
		$affectedPages['propertiesWithDomain'] = $propertiesWithDomain;
		$affectedPages['directSubcategories'] = $directSubcategories;

		return $affectedPages;
	}

	public function refactor($save = true, & $logMessages) {
		$this->queryAffectedPages();

		if (array_key_exists('sref_deleteCategory', $this->options) && $this->options['sref_deleteCategory'] == "true") {
			$a = new Article($this->category);
			$deleted = true;
			if ($save) {
				$deleted = SRFTools::deleteArticle($a);
			}
			if ($deleted) {
				$logMessages[$this->category->getPrefixedText()][] = new SRFLog('Article deleted',$this->category);
			} else {
				$logMessages[$this->category->getPrefixedText()][] = new SRFLog('Deletion failed',$this->category);
			}


			if (!is_null($this->mBot)) $this->mBot->worked(1);

		}



		// if instances are completely removed, there is no need to remove annotations before
		if (array_key_exists('sref_removeInstances', $this->options) && $this->options['sref_removeInstances'] == "true") {
			foreach($this->affectedPages['instances'] as $i) {
				$a = new Article($i);

				$deleted = true;
				if ($save) {
					$deleted = SRFTools::deleteArticle($a);
				}
				if ($deleted) {
					$logMessages[$i->getPrefixedText()][] = new SRFLog('Article deleted',$i);
				} else {
					$logMessages[$i->getPrefixedText()][] = new SRFLog('Deletion failed',$i);

				}

				if (!is_null($this->mBot)) $this->mBot->worked(1);


			}
		}

		if (array_key_exists('sref_removeFromDomain', $this->options) && $this->options['sref_removeFromDomain'] == "true") {
			foreach($this->affectedPages['propertiesWithDomain'] as $i) {
				// if the property should be completly removed
				if (array_key_exists('sref_removePropertyWithDomain', $this->options) && $this->options['sref_removePropertyWithDomain'] == "true") {
					$deleted = true;
					if ($save) {
						$deleted = SRFTools::deleteArticle($i);
					}
					if ($deleted) {
						$logMessages[][] = new SRFLog('Article deleted',$i);
					} else {
						$logMessages[][] = new SRFLog('Deletion failed',$i);
					}

					continue; // continue if completely removed.
				}

				$rev = Revision::newFromTitle($i);
				if (is_null($rev)) continue;
				$wikitext = $rev->getRawText();

				$wikitext = $this->removePropertyAnnotation(SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getText(), $wikitext);

				$logMessages[$i->getPrefixedText()][] = new SRFLog('Removed from domain and/or range',$i);

				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}
		}

		$set = array_merge($this->affectedPages['queries'],$this->affectedPages['instances']);
		$set = SRFTools::makeTitleListUnique($set);

		foreach($set as $i) {
			$rev = Revision::newFromTitle($i);
			if (is_null($rev)) continue;
			$wikitext = $rev->getRawText();

			if (array_key_exists('sref_removeCategoryAnnotations', $this->options) && $this->options['sref_removeCategoryAnnotations'] == "true"
			&& SRFTools::containsTitle($i, $this->affectedPages['instances'])) {
				$wikitext = $this->removeCategoryAnnotation($wikitext);

				$logMessages[$i->getPrefixedText()][] = new SRFLog('Removed category annotation',$i);

				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}



			if (array_key_exists('sref_removeQueriesWithCategories', $this->options) && $this->options['sref_removeQueriesWithCategories'] == "true"
			&& SRFTools::containsTitle($i, $this->affectedPages['queries'])) {

				$wikitext = $this->removeQuery($wikitext);

				$logMessages[$i->getPrefixedText()][] = new SRFLog('Removed query',$i);

				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}

			if ($save) {
				$status = $this->storeArticle($i, $wikitext, $rev->getRawText(), $rev->getRawComment(), $logMessages);
			}
		}




		if (array_key_exists('sref_includeSubcategories', $this->options) && $this->options['sref_includeSubcategories'] == "true") {
			foreach($this->affectedPages['directSubcategories'] as $c) {
				$op = new SRFDeleteCategoryOperation($c, $this->options);
				$op->setBot($mBot);
				$op->refactor($save, $logMessages);
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
			SRFTools::findObjectByID($o, WOM_TYPE_CATEGORY, $results);
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

	private function removePropertyAnnotation($property, $wikitext) {

		$wom = WOMProcessor::parseToWOM($wikitext);
		$toDelete = array();

		# iterate trough the annotations
		$objects = $wom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		foreach($objects as $o){

			$name = $o->getPropertyName();
			if ($name == $property) {
				$toDelete[] = $o->getObjectID();
			}

		}
		$toDelete = array_unique($toDelete);
		foreach($toDelete as $d) {
			$wom->removePageObject($d);
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