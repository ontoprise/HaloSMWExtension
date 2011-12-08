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
class SMWRFDeleteCategoryOperation extends SMWRFRefactoringOperation {

	var $category;
	var $options;

	public function __construct($category, $options) {
		$this->category = Title::newFromText($category, NS_CATEGORY);
		$this->options = $options;
	}

	public function queryAffectedPages() {

		$store = smwfGetSemanticStore();
		$instances = $store->getDirectInstances($this->category);
		$directSubcategories = $store->getDirectSubCategories($this->category);
		$allSubCategories = $store->getSubCategories($this->category);

		// get all queries $this->category is used in
		$queries = array();
		$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOC_LABEL);
		$propertyWPDi = SMWDIWikiPage::newFromTitle($this->oldCategory);
		$subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $propertyWPDi);
		foreach($subjects as $s) {
			$queries[] = $s->getTitle();
		}
		$results = array();
		$results['instances'] = $instances;
		$results['queries'] = $queries;
		$results['directSubcategories'] = $directSubcategories;
		$results['allSubCategories'] = $allSubCategories;

		return $results;
	}

	public function refactor($save = true) {
		if (array_key_exists('onlyCategory', $this->options) && $this->options['onlyCategory'] == true) {
			$a = new Article($this->category);
			$this->deleteArticle($a);
			return;
		}

		$results = $this->getAffectedPages();

		if (array_key_exists('removeInstances', $this->options) && $this->options['removeInstances'] == true) {
			// if instances are completely removed, there is no need to remove annotations before
			foreach($results['instances'] as $i) {
				$a = new Article($i);
				$this->deleteArticle($a);
			}
		} else if (array_key_exists('removeCategoryAnnotations', $this->options) && $this->options['removeCategoryAnnotations'] == true) {
			$pom = WOMProcessor::parseToWOM($wikitext);

			# iterate trough the annotations
			$objects = $pom->getObjectsByTypeID(WOM_TYPE_CATEGORY);
			foreach($objects as $o){

				$name = $o->getName();
				if ($name == $this->category->getText()) {
					$toDelete[] = $o;
				}

			}
		}

		if (array_key_exists('removeQueries', $this->options) && $this->options['removeQueries'] == true) {

		}

		if (array_key_exists('deleteSubcategories', $this->options) && $this->options['deleteSubcategories'] == true) {

		}

		if (array_key_exists('deleteAllInstancesOfSubcategories', $this->options) && $this->options['deleteAllInstancesOfSubcategories'] == true) {

		}
	}

	protected function deleteArticle($a) {
		global $wgUser;
		$reason = "Removed by Semantic Refactoring extension";
		if ( wfRunHooks( 'ArticleDelete', array( &$a, &$wgUser, &$reason, &$error ) ) ) {
			if ( $this->doDeleteArticle( $reason ) ) {
				$deleted = $this->mTitle->getPrefixedText();
				wfRunHooks( 'ArticleDeleteComplete', array( &$this, &$wgUser, $reason, $id ) );
			}
		}
	}
}