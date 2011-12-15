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
class SRFRenameInstanceOperation extends SRFRefactoringOperation {
	private $oldInstance;
	private $newInstance;

	private $affectedPages;

	public function __construct($oldInstance, $newInstance, $adaptAnnotations) {
		$this->oldInstance = Title::newFromText($oldInstance);
		$this->newInstance = Title::newFromText($newInstance);


	}

	public function getNumberOfAffectedPages() {
		$this->affectedPages = $this->queryAffectedPages();
		return count($this->affectedPages);
	}

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		// get all pages using $this->oldInstance in an annotation
		$titles = array();
		$instanceDi = SMWDIWikiPage::newFromTitle($this->oldInstance);
		$properties = smwfGetStore()->getInProperties($instanceDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $instanceDi);
			foreach($subjects as $s) {
				$titles[] = $s->getTitle();
			}
		}

		// get all pages which uses links with that instance
		$subjects = $this->oldInstance->getLinksTo();
		foreach($subjects as $s) {
			$titles[] = $s;
		}

		// get all queries using $this->oldInstance
		// TODO: QRC_DOI_LABEL is missing
		/*$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOI_LABEL);
		 $instanceStringDi = new SMWDIString($this->$this->oldInstance->getPrefixedText());
		 $subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $instanceStringDi);
		 foreach($subjects as $s) {
			$titles[] = $s->getTitle();
			}*/
        
		
		$this->affectedPages = SRFTools::makeTitleListUnique($titles);
		return $this->affectedPages;
	}

	public function refactor($save = true, & $logMessages) {

		$this->queryAffectedPages();

		foreach($this->affectedPages as $title) {

			$rev = Revision::newFromTitle($title);

			$wikitext = $this->changeContent($title, $rev->getRawText(), $logMessages);

			// stores article
			if ($save) {
				$a = new Article($title);
				$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
			}
			
			if (!is_null($this->mBot)) $this->mBot->worked(1);
		}


	}

	/**
	 * Replaces old title with new.
	 * Callback method for array_walk
	 *
	 * @param string $title Prefixed title
	 * @param int $index
	 */
	protected function replaceTitle(& $title, $index) {
		if ($title == $this->oldInstance->getPrefixedText()) {
			$changed = true;
			$title = $this->newInstance->getPrefixedText();
		}
	}

	private function replaceInstanceInLink($objects) {
		$changed = false;
		foreach($objects as $o){
			$value = $o->getLink();

			if ($value == $this->oldInstance->getPrefixedText()) {
				$o->setLink($this->newInstance->getPrefixedText());
				$changed = true;
			}
		}
		return $changed;
	}

	public function changeContent($title, $wikitext, & $logMessages) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate through the annotation values
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$changedValueinAnnotation = $this->replaceValueInAnnotation($objects);

		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$changedLink = $this->replaceInstanceInLink($objects);

		# iterate trough queries
		# better support for ASK would be nice
		$changedQuery=false;
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);
		foreach($objects as $o){
			if ($o->getFunctionKey() == 'ask') {
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_PROPERTY, $results);
				$changedQuery = $changedQuery || $this->replaceValueInAnnotation($results);

				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$changedQuery = $changedQuery || $this->replaceInstanceInLink($results);

			}
		}

		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();

		if ($changedValueinAnnotation) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed instance as value", $title, $wikitext);
		}
		if ($changedLink) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed link", $title, $wikitext);
		}
		if ($changedQuery) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed query", $title, $wikitext);
		}
		return $wikitext;
	}
}