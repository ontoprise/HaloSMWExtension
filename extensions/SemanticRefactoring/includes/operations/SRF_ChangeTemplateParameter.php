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
class SRFChangeTemplateParameterOperation extends SRFRefactoringOperation {

	private $instanceSet;
	private $template;
	private $parameter;
	private $oldValue; // empty means: add value
	private $newValue; // empty means: remove value
	private $set;

	public function __construct($instanceSet, $template, $parameter, $oldValue, $newValue, $set = false) {
		parent::__construct();
		foreach($instanceSet as $i) {
			$this->instanceSet[] = Title::newFromText($i);
		}
		$this->template = Title::newFromText($template, NS_TEMPLATE);
		$this->parameter = $parameter;
		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
		$this->set = $set;
	}

	public function queryAffectedPages() {
		return $this->instanceSet;
	}

	public function getWork() {
		return count($this->instanceSet);
	}

	public function refactor($save = true, & $logMessages) {
		foreach($this->instanceSet as $title) {
			if ($title->getNamespace() == SGA_NS_LOG) continue;
			$rev = Revision::newFromTitle($title);
			$wikitext = $this->changeContent($title, $rev->getRawText(), $logMessages);

			if (!is_null($this->mBot)) $this->mBot->worked(1);

			// stores article
			if ($save) {
				$status = $this->storeArticle($title, $wikitext, $rev->getRawComment());
				if (!$status->isGood()) {
					$logMessages[$title->getPrefixedText()][] = new SRFLog('Saving of $title failed due to: $1', $title, $wikitext, array($status->getWikiText()));
				}
			}
		}
	}


	public function changeContent($title, $wikitext, & $logMessages) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		if (is_null($this->oldValue) && is_null($this->newValue)) {
			return $pom->getWikiText();
		}
		//print_r($pom);
		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_TEMPLATE);

		$toDelete = array();
		$toAdd = array();



		foreach($objects as $o){

			$name = $o->getName();
			if (is_null($this->newValue)) {
				// remove annotation
				if ($name == $this->template->getText()) {
					$results = array();
					$this->findObjectByID($o, WOM_TYPE_PARAM_VALUE, $parameters);
					foreach($parameters as $p) {
						if (is_null($this->oldValue) || $p->getWikiText() == $this->oldValue) {
							$toDelete[] = $p->getObjectID();
							$logMessages[$title->getPrefixedText()][] = new SRFLog("Deleted value '$2' of '$1'", $title, "", array($this->parameter, $this->oldValue));
						}
					}
				}
			} else if (is_null($this->oldValue) && !$this->set) {
				// add new template parameter
				$paramValue = new WOMParamValueModel();
				$templateField = new WOMTemplateFieldModel($this->parameter);
				$templateField->insertObject(new WOMTextModel($this->newValue));
				$paramValue->insertObject($templateField);
				$o->insertObject($paramValue);
				$logMessages[$title->getPrefixedText()][] = new SRFLog("Added parameter '$2' of '$1'", $title, "", array($this->parameter, $this->newValue));
			} else  {

				if ($name == $this->template->getText()) {
					$results = array();
					$this->findObjectByID($o, WOM_TYPE_PARAM_VALUE, $parameters);
					foreach($parameters as $p) {
							
						if ($this->set || $p->getWikiText() == $this->oldValue) {
							$id = $p->getObjectID();
							$p->getParent()->updateObject(new WOMTextModel($this->newValue), $id);
							$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed value of '$1' from '$2' to '$3'", $title, "", array($this->parameter, $this->oldValue, $this->newValue));
						}
					}

				}
			}
		}

		$toDelete = array_unique($toDelete);
		foreach($toDelete as $d) {
			$pom->removePageObject($d);
		}


		// calls sync() internally
		$wikitext = $pom->getWikiText();

		// set final wiki text
		foreach($logMessages as $title => $set) {
			foreach($set as $lm) {
				$lm->setWikiText($wikitext);
			}
		}

		return $wikitext;
	}
}