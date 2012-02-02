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
	var $totalWork;

	public function __construct($category, $options) {
		parent::__construct();
		$this->property = Title::newFromText($category, SMW_NS_PROPERTY);
		$this->options = $options;
		$this->totalWork = -1;
	}

	public function getWork() {
		if ($this->totalWork == -1) {
			$this->queryAffectedPages();
		}
		return $this->totalWork;
	}

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;
		$this->affectedPages = $this->getAffectedPagesForProperty($this->property);
		$this->previewData['sref_changedQueries'] = 0;
		$this->previewData['sref_changedInstances'] = 0;
		$this->previewData['sref_deletedInstances'] = 0;

		$this->totalWork = 0;
		$this->collectWorkForProperty($this->property, $this->totalWork, $this->previewData);

		if ($this->isOptionSet('sref_includeSubproperties', $this->options)) {
			$subproperties = $store->getSubProperties($this->property);
			foreach($subproperties as $s) {
				$this->collectWorkForProperty($s, $this->totalWork, $this->previewData);
			}
		}
		return $this->affectedPages;
	}
	
	private function collectWorkForProperty($property, & $num, & $previewData) {

		
		$affectedPages = $this->getAffectedPagesForProperty($property);

		if ( $this->isOptionSet('sref_removeInstancesUsingProperty', $this->options)) {
			$num += count($affectedPages['instances']);
			$previewData['sref_deletedInstances'] += count($affectedPages['instances']);
		} else {
			if ( $this->isOptionSet('sref_removePropertyAnnotations', $this->options)) {
				$num += count($affectedPages['instances']);
				$previewData['sref_changedInstances'] += count($affectedPages['instances']);
			}
		}
		if ( $this->isOptionSet('sref_removeQueriesWithProperties', $this->options)) {
			$num += count($affectedPages['queries']);
			$previewData['sref_changedQueries'] += count($affectedPages['queries']);
		}
			
		
	}



	private function getAffectedPagesForProperty($property) {
		// calculate only once

		$store = smwfGetSemanticStore();
		$smwstore = smwfGetStore();

		// get all instances using $this->property as annotation
		$propertyDi = SMWDIProperty::newFromUserLabel($property->getText());
		$pageDIs = $smwstore->getAllPropertySubjects($propertyDi);
		$instances = array();
		foreach($pageDIs as $di) {
			$instances[] = $di->getTitle();
		}

		// get all direct subproperties of $this->property
		$directSubProperties = array();
		$dsp = $store->getDirectSubProperties($property);
		foreach($dsp as $tuple) {
			list($property, $hasChildren) = $tuple;
			$directSubProperties[] = $property;
		}

		// get all queries $this->property is used in
		$queries = array();
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($property->getPrefixedText() => true);
		$queryMetadataPattern->propertyConditions = array($property->getText() => true);
		$queryMetadataPattern->propertyPrintRequests = array($property->getText() => true);

		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		foreach($qmr as $s) {
			$queries[] = Title::newFromText($s->usedInArticle);
		}

		$affectedPages = array();
		$affectedPages['instances'] = $instances;
		$affectedPages['queries'] = $queries;
		$affectedPages['directSubProperties'] = $directSubProperties;

		return $affectedPages;
	}

	public function refactor($save = true, & $logMessages) {
		$this->queryAffectedPages();

		if (array_key_exists('sref_deleteProperty', $this->options) && $this->options['sref_deleteProperty'] == "true") {
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


		if (array_key_exists('sref_removeInstancesUsingProperty', $this->options) && $this->options['sref_removeInstancesUsingProperty'] == "true") {
			foreach($this->affectedPages['instances'] as $i) {
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


			}
		}

		$set = array_merge($this->affectedPages['instances'], $this->affectedPages['queries']);
		$set = SRFTools::makeTitleListUnique($set);

		foreach($set as $i) {
			$rev = Revision::newFromTitle($i);
			if (is_null($rev)) continue;
			$wikitext = $rev->getRawText();
			if (array_key_exists('sref_removePropertyAnnotations', $this->options) && $this->options['sref_removePropertyAnnotations'] == "true"
			&& SRFTools::containsTitle($i, $this->affectedPages['instances'])) {
				$wikitext = $this->removePropertyAnnotation($wikitext);
				$logMessages[$i->getPrefixedText()][] = new SRFLog('Removed property annotation',$i);

			}


			if (array_key_exists('sref_removeQueriesWithProperties', $this->options) && $this->options['sref_removeQueriesWithProperties'] == "true"
			&& SRFTools::containsTitle($i, $this->affectedPages['queries'])) {
				$wikitext = $this->removeQuery($wikitext);
				$logMessages[$i->getPrefixedText()][] = new SRFLog('Removed query',$i);

			}

			if ($save) {
				$status = $this->storeArticle($i, $wikitext, $rev->getRawText(), $rev->getRawComment(), $logMessages);
			
			}
		}

		if (array_key_exists('sref_includeSubproperties', $this->options) && $this->options['sref_includeSubproperties'] == "true") {
			foreach($this->affectedPages['directSubcategories'] as $p) {
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
			SRFTools::findObjectByID($o, WOM_TYPE_PROPERTY, $results);
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
			SRFTools::findObjectByID($o, WOM_TYPE_PARAM_VALUE, $results);
			foreach($results as $paramValue) {
				$paramTexts = array();
				SRFTools::findObjectByID($paramValue, WOM_TYPE_TEXT, $printouts);
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