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

		// get properties with domain and/or ranges
		$domainProperties = $store->getPropertiesWithDomain($this->category);
		$rangeProperties = $store->getPropertiesWithRange($this->category);
		$domainsOrRanges = array_merge($domainProperties, $rangeProperties);
		$domainsOrRanges = SRFTools::makeTitleListUnique($domainsOrRanges);

		$this->affectedPages = array();
		$this->affectedPages['instances'] = $instances;
		$this->affectedPages['queries'] = $queries;
		$this->affectedPages['domainsOrRanges'] = $domainsOrRanges;
		$this->affectedPages['directSubcategories'] = $directSubcategories;

		return $this->affectedPages;
	}

	public function refactor($save = true, & $logMessages) {
		$results = $this->queryAffectedPages();

		if (array_key_exists('onlyCategory', $this->options) && $this->options['onlyCategory'] == true) {
			$a = new Article($this->category);
			$deleted = true;
			if ($save) {
				$deleted = SRFTools::deleteArticle($a);
			}
			if ($deleted) {
				$logMessages[$this->category->getPrefixedText()] = new SRFLog('Article deleted',$this->category);
			} else {
				$logMessages[$this->category->getPrefixedText()] = new SRFLog('Deletion failed',$this->category);
			}


			if (!is_null($this->mBot)) $this->mBot->worked(1);
			return;
		}

		$set = array_merge($this->affectedPages['instances'], $this->affectedPages['queries'],$this->affectedPages['domainsOrRanges']);
		$set = SRFTools::makeTitleListUnique($set);


		// if instances are completely removed, there is no need to remove annotations before
		foreach($set as $i) {
			$a = new Article($i);

			if (array_key_exists('removeInstances', $this->options) && $this->options['removeInstances'] == true) {
				$deleted = true;
				if ($save) {
					$deleted = SRFTools::deleteArticle($a);
				}
				if ($deleted) {
					$logMessages[$i->getPrefixedText()] = new SRFLog('Article deleted',$i);
				} else {
					$logMessages[$i->getPrefixedText()] = new SRFLog('Deletion failed',$i);

				}

				if (!is_null($this->mBot)) $this->mBot->worked(1);

				continue; // continue if article is completely removed.
			}

			$rev = Revision::newFromTitle($i);
			if (is_null($rev)) continue;
			$wikitext = $rev->getRawText();

			if (array_key_exists('removeCategoryAnnotations', $this->options) && $this->options['removeCategoryAnnotations'] == true
			&& SRFTools::containsTitle($i, $this->affectedPages['instances'])) {
				$wikitext = $this->removeCategoryAnnotation($wikitext);

				$logMessages[$i->getPrefixedText()] = new SRFLog('Removed category annotation',$i);

				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}

			if (array_key_exists('removeFromDomainOrRange', $this->options) && $this->options['removeFromDomainOrRange'] == true
			&& SRFTools::containsTitle($i, $this->affectedPages['domainsOrRanges'])) {

				// if the property should be completly removed
				if (array_key_exists('removeDomainOrRangeProperty', $this->options) && $this->options['removeDomainOrRangeProperty'] == true) {
					$deleted = true;
					if ($save) {
						$deleted = SRFTools::deleteArticle($i);
					}
					if ($deleted) {
						$logMessages[] = new SRFLog('Article deleted',$i);
					} else {
						$logMessages[] = new SRFLog('Deletion failed',$i);
					}

					continue;
				}

				$wikitext = $this->removePropertyAnnotation(SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getText(), $wikitext);

				$logMessages[$i->getPrefixedText()] = new SRFLog('Removed from domain and/or range',$i);

				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}

			if (array_key_exists('removeQueries', $this->options) && $this->options['removeQueries'] == true
			&& SRFTools::containsTitle($i, $this->affectedPages['queries'])) {

				$wikitext = $this->removeQuery($wikitext);

				$logMessages[$i->getPrefixedText()] = new SRFLog('Removed query',$i);

				if (!is_null($this->mBot)) $this->mBot->worked(1);
			}

			if ($save) {
				$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
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