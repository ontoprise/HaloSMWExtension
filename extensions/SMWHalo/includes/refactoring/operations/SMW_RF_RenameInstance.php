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
class SMWRFRenameInstanceOperation extends SMWRFRefactoringOperation {
	private $oldInstance;
	private $newInstance;
	private $adaptAnnotations;

	private $subjectDBKeys;

	public function __construct($oldInstance, $newInstance, $adaptAnnotations) {
		$this->oldInstance = Title::newFromText($oldInstance);
		$this->newInstance = Title::newFromText($newInstance);
		$this->adaptAnnotations = $adaptAnnotations;

	}

	public function getAffectedPages() {
		if (!$this->adaptAnnotations) return 0;

		// get all pages using $this->oldInstance in an annotation
		$instanceDi = SMWDIWikiPage::newFromTitle($this->oldInstance);
		$properties = smwfGetStore()->getInProperties($instanceDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $instanceDi);
			foreach($subjects as $s) {
				$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
			}
		}

		// get all pages which uses links with that instance
		$subjects = $this->oldInstance->getLinksTo();
		foreach($subjects as $s) {
			$subjectDBKeys[] = $s->getPrefixedDBkey();
		}

		// get all queries using $this->oldInstance
		// TODO: QRC_DOI_LABEL is missing
		/*$qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOI_LABEL);
		 $propertyWPDi = SMWDIWikiPage::newFromTitle($this->oldInstance);
		 $subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $propertyWPDi);
		 foreach($subjects as $s) {
			$subjectDBKeys[] = $s->getTitle()->getPrefixedDBkey();
			}*/

		$subjectDBKeys = array_unique($subjectDBKeys);
		return $subjectDBKeys;
	}

	public function refactor($save = true) {

		$subjectDBkeys = $this->getAffectedPages();

		foreach($subjectDBkeys as $dbkey) {
			$title = Title::newFromDBkey($dbkey);
			$rev = Revision::newFromTitle($title);

			$wikitext = $this->changeContent($title->getText(), $rev->getRawText());

			// stores article
			if ($save) {
				$a = new Article($title);
				$a->doEdit($wikitext, $rev->getRawComment(), EDIT_FORCE_BOT);
			}
		}

		// move article
		if ($save) {
			$this->oldInstance->moveTo($this->newInstance);
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
		foreach($objects as $o){
			$value = $o->getLink();

			if ($value == $this->oldInstance->getPrefixedText()) {
				$o->setLink($this->newInstance->getPrefixedText());
			}
		}
	}

	public function changeContent($titleName, $wikitext) {
		$pom = WOMProcessor::parseToWOM($wikitext);

		# iterate through the annotation values
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$this->replaceValueInAnnotation($objects);

		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$this->replaceInstanceInLink($objects);

		# iterate trough queries
		# better support for ASK would be nice
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);
		foreach($objects as $o){
			if ($o->getFunctionKey() == 'ask') {
				$results = array();
				$this->findObjectByID($o, WOM_TYPE_PROPERTY, $results);
				$this->replaceValueInAnnotation($results);

				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$this->replaceInstanceInLink($results);

			}
		}

		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();
		return $wikitext;
	}
}
