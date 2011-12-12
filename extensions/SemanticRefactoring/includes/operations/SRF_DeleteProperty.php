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
class SRFDeletePropertyOperation extends SRFRefactoringOperation {

	var $property;
	var $options;
	var $affectedPages;

	public function __construct($category, $options) {
		$this->property = Title::newFromText($category, SMW_NS_PROPERTY);
		$this->options = $options;
	}

	public function getNumberOfAffectedPages() {

		$this->affectedPages = $this->queryAffectedPages();
		$num += (array_key_exists('removeInstancesUsingProperty', $this->options) && $this->options['removeInstancesUsingProperty'] == true)
		|| (array_key_exists('removePropertyAnnotations', $this->options) && $this->options['removePropertyAnnotations'] == true) ? count($affectedPages['instances']) : 0;

		$num += array_key_exists('removeQueries', $this->options) && $this->options['removeQueries'] == true ? count($affectedPages['queries']) : 0;

		if (array_key_exists('includeSubproperties', $this->options) && $this->options['includeSubproperties'] == true) {

			if (array_key_exists('removeInstancesUsingProperty', $this->options) && $this->options['removeInstancesUsingProperty'] == true) {
				$num += $smwfGetSemanticStore()->getNumberOfUsage($this->property);
			} else {
				$subproperties = $store->getSubProperties($this->property);
				$num += count($subproperties);
			}
		}

		return $num;
	}


	public function queryAffectedPages() {
		// calculate only once
		if (!is_null($this->affectedPages)) return $this->affectedPages;
		$store = smwfGetSemanticStore();
		$smwstore = smwfGetStore();

		// get all instances using $this->property as annotation
		$propertyDi = SMWDIProperty::newFromUserLabel($this->property->getText());
		$pageDIs = $smwstore->getAllPropertySubjects($propertyDi);
		$instances = array();
		foreach($pageDIs as $di) {
			$instances[] = $di->getTitle();
		}

		// get all direct subproperties of $this->property
		$directSubProperties = array();
		$dsp = $store->getDirectSubProperties($this->property);
		foreach($dsp as $tuple) {
			list($property, $hasChildren) = $tuple;
			$directSubProperties[] = $property;
		}

		// get all queries $this->property is used in
		$queries = array();
		$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOP_LABEL);
		$propertyStringDi = new SMWDIString($this->property->getText());
		$subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $propertyStringDi);
		foreach($subjects as $s) {
			$queries[] = $s->getTitle();
		}
		$this->affectedPages = array();
		$this->affectedPages['instances'] = $instances;
		$this->affectedPages['queries'] = $queries;
		$this->affectedPages['directSubProperties'] = $directSubProperties;

		return $this->affectedPages;
	}

	public function refactor($save = true, & $logMessages, & $testData = NULL) {
		$results = $this->queryAffectedPages();
		
		if (array_key_exists('onlyProperty', $this->options) && $this->options['onlyProperty'] == true) {
			$a = new Article($this->property);
			if ($save) {
				if (!SRFTools::deleteArticle($a)) {
					$logMessages[] = 'Deletion failed: '.$this->property->getPrefixedText();
				}
			}
			$logMessages[] = 'Article deleted: '.$this->property->getPrefixedText();
			if (!is_null($testData)) {
				$testData[$this->property->getPrefixedText()] = 'deleted';
			}
			return;
		}


		if (array_key_exists('removeInstancesUsingProperty', $this->options) && $this->options['removeInstancesUsingProperty'] == true) {
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
			}
		} else if (array_key_exists('removePropertyAnnotations', $this->options) && $this->options['removePropertyAnnotations'] == true) {
			foreach($results['instances'] as $i) {
				$rev = Revision::newFromTitle($i);
				if (is_null($rev)) continue;
				$wikitext = $this->removePropertyAnnotation($rev->getRawText());
				if ($save) {
					$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
				}
				$logMessages[] = 'Removed property annotation from: '.$i->getPrefixedText();
				if (!is_null($testData)) {
					$testData[$i->getPrefixedText()] = array('removePropertyAnnotations', $wikitext);
				}
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
					$testData[$q->getPrefixedText()] = array('removePropertyAnnotations', $wikitext);
				}
			}
		}

		if (array_key_exists('includeSubproperties', $this->options) && $this->options['includeSubproperties'] == true) {
			foreach($results['directSubcategories'] as $p) {
				$op = new SRFDeletePropertyOperation($p, $this->options);
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
			$deleted = false;
			$results = array();
			$this->findObjectByID($o, WOM_TYPE_PROPERTY, $results);
			foreach($results as $c){
				$name = $c->getPropertyName();
				if ($name == $this->property->getText()) {
					$toDelete[] = $o->getObjectID();
					$deleted = true;
				}
			}

			if ($deleted) continue;

			// find printout
			$results = array();
			$this->findObjectByID($o, WOM_TYPE_PARAM_VALUE, $results);
			foreach($results as $paramValue) {
				$paramTexts = array();
				$this->findObjectByID($paramValue, WOM_TYPE_TEXT, $printouts);
				foreach($printouts as $po){
					$value = $po->getWikiText();
					$value = trim($value);
					if ($value == '?'.$this->oldProperty->getText()) {
						$toDelete[] = $o->getObjectID();
					}

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

	private function removePropertyAnnotation($wikitext) {

		$wom = WOMProcessor::parseToWOM($wikitext);
		$toDelete = array();

		# iterate trough the annotations
		$objects = $wom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		foreach($objects as $o){

			$name = $o->getPropertyName();
			if ($name == $this->property->getText()) {
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