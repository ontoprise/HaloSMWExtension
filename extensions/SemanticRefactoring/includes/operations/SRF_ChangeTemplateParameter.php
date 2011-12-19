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

	public function __construct($instanceSet, $template, $parameter, $oldValue, $newValue) {
	parent::__construct();
        foreach($instanceSet as $i) {
          $this->instanceSet[] = Title::newFromText($i);
        }
		$this->template = Title::newFromText($template, NS_TEMPLATE);
		$this->parameter = $parameter;
		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
	}

	public function queryAffectedPages() {
		return $this->instanceSet;
	}

	public function getNumberOfAffectedPages() {
		return count($this->instanceSet);
	}

	public function refactor($save = true, & $logMessages) {
		foreach($this->instanceSet as $title) {
			$rev = Revision::newFromTitle($title);
			$this->changeContent($title, $rev->getRawText(), $logMessages);

			if (!is_null($this->mBot)) $this->mBot->worked(1);
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
							$logMessages[$title->getPrefixedText()][] = new SRFLog("Delete value", $title);
						}
					}
				}
			} else if (is_null($this->oldValue)) {
				// add new template parameter
				$paramValue = new WOMParamValueModel();
				$templateField = new WOMTemplateFieldModel($this->parameter);
				$templateField->insertObject(new WOMTextModel($this->newValue));
				$paramValue->insertObject($templateField);
				$o->insertObject($paramValue);
				$logMessages[$title->getPrefixedText()][] = new SRFLog("Added value", $title);
			} else {

				if ($name == $this->template->getText()) {
					$results = array();
					$this->findObjectByID($o, WOM_TYPE_PARAM_VALUE, $parameters);
					foreach($parameters as $p) {
							
						if ($p->getWikiText() == $this->oldValue) {
							$id = $p->getObjectID();
							$p->getParent()->updateObject(new WOMTextModel($this->newValue), $id);
							$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed value", $title);
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