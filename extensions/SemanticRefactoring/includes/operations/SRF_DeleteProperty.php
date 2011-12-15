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
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($this->property->getPrefixedText() => true);
		$queryMetadataPattern->propertyConditions = array($this->property->getText() => true);
		$queryMetadataPattern->propertyPrintRequests = array($this->property->getText() => true);

		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		foreach($qmr as $s) {
			$queries[] = Title::newFromText($s->usedInArticle);
		}

		$this->affectedPages = array();
		$this->affectedPages['instances'] = $instances;
		$this->affectedPages['queries'] = $queries;
		$this->affectedPages['directSubProperties'] = $directSubProperties;

		return $this->affectedPages;
	}

	public function refactor($save = true, & $logMessages) {
		$results = $this->queryAffectedPages();

		if (array_key_exists('sref_deleteProperty', $this->options) && $this->options['sref_deleteProperty'] == true) {
			$a = new Article($this->property);
			$deleted = true;
			if ($save) {
				$deleted = SRFTools::deleteArticle($a);
			}
			if ($deleted) {
				$logMessages[$this->property->getPrefixedText()][] = new SRFLog('Article deleted',$this->property);
			} else {
				$logMessages[$this->property->getPrefixedText()][] = new SRFLog('Deletion failed',$this->property);
			}
			
		}


		$set = array_merge($this->affectedPages['instances'], $this->affectedPages['queries']);
		$set = SRFTools::makeTitleListUnique($set);
		foreach($set as $i) {
			if (array_key_exists('sref_removeInstancesUsingProperty', $this->options) && $this->options['sref_removeInstancesUsingProperty'] == true) {
				// if instances are completely removed, there is no need to remove annotations before

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

				continue; // if article is removed, then continue;
			}
			$rev = Revision::newFromTitle($i);
			if (is_null($rev)) continue;
			$wikitext = $rev->getRawText();

			if (array_key_exists('sref_removePropertyAnnotations', $this->options) && $this->options['sref_removePropertyAnnotations'] == true
			&& SRFTools::containsTitle($i, $this->affectedPages['instances'])) {
				$wikitext = $this->removePropertyAnnotation($wikitext);

				$logMessages[$i->getPrefixedText()][] = new SRFLog('Removed property annotation',$i);

			}


			if (array_key_exists('sref_removeQueriesWithProperties', $this->options) && $this->options['sref_removeQueriesWithProperties'] == true
			&& SRFTools::containsTitle($i, $this->affectedPages['queries'])) {
				$wikitext = $this->removeQuery($wikitext);
				if ($save) {
					$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
				}

				$logMessages[$i->getPrefixedText()][] = new SRFLog('Removed query',$i);

			}
		}

		if ($save) {
			$status = $this->storeArticle($title, $wikitext, $rev->getRawComment());
			if (!$status->isGood()) {
				$logMessages[$title->getPrefixedText()][] = new SRFLog('Saving of $title failed due to: $1', $title, $wikitext, array($status->getWikiText()));
			}
		}


		if (array_key_exists('sref_includeSubproperties', $this->options) && $this->options['sref_includeSubproperties'] == true) {
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