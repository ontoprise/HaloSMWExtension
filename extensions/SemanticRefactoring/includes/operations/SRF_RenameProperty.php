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
 * Rename operation for a property.
 *
 * @author Kai Kuehn
 *
 */
class SRFRenamePropertyOperation extends SRFRenameOperation {

	

	public function __construct($old, $new) {
		parent::__construct();
		$this->old = Title::newFromText($old, SMW_NS_PROPERTY);
		$this->new = Title::newFromText($new, SMW_NS_PROPERTY);;

	}

	

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		$titles=array();
		$this->previewData['sref_changedProperty'] = 0;
		// get all pages using $this->property
		$propertyDi = SMWDIProperty::newFromUserLabel($this->old->getText());
		$subjects = smwfGetStore()->getAllPropertySubjects($propertyDi);
		foreach($subjects as $s) {
			$titles[] = $s->getTitle();
			$this->previewData['sref_changedProperty'] += 1;
		}

		// get all pages using $this->property
		$this->previewData['sref_changedPropertyAsValue'] = 0;
		$objectDi = SMWDIWikiPage::newFromTitle($this->old);
		$properties = smwfGetStore()->getInProperties($objectDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $objectDi);
			foreach($subjects as $s) {
				$titles[] = $s->getTitle();
				$this->previewData['sref_changedPropertyAsValue'] += 1;
			}
		}

		// subproperties
		$this->previewData['sref_changedSubproperties'] = 0;
		$subPropertyDi = SMWDIProperty::newFromUserLabel('_SUBP');
		$subjects = smwfGetStore()->getPropertySubjects($subPropertyDi, $objectDi);

		foreach($subjects as $s) {
			$titles[] = $s->getTitle();
			$this->previewData['sref_changedSubproperties'] += 1;
		}


		// get all pages which uses links to $this->property
		$this->previewData['sref_changedLinks'] = 0;
		$subjects = $this->old->getLinksTo();
		foreach($subjects as $s) {
			$titles[] = $s;
			$this->previewData['sref_changedLinks'] += 1;
		}

		// get all queries using $this->property
		$this->previewData['sref_changedQueries'] = 0;
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($this->old->getPrefixedText() => true);
		$queryMetadataPattern->propertyConditions = array($this->old->getText() => true);
		$queryMetadataPattern->propertyPrintRequests = array($this->old->getText() => true);

		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		foreach($qmr as $s) {
			$titles[] = Title::newFromText($s->usedInArticle);
			$this->previewData['sref_changedQueries']  += 1;
		}

		$this->affectedPages = SRFTools::makeTitleListUnique($titles);
		return $this->affectedPages;
	}

	

	
	protected function replacePrintout($o) {
		$changed = false;

		$value = $o->getProperty();
		$value = trim($value);

		if ($this->equalsOld($value)) {
			$o->setXMLAttribute('property', $this->getNew()->getText());
			$changed=true;
		}


		return $changed;
	}


	public function applyOperation($title, $wikitext, & $logMessages) {
		$pom = WOMProcessor::parseToWOM($wikitext);
	
		# iterate trough queries
		# better support for ASK would be nice
		$changedQuery=false;
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PARSERFUNCTION);
		foreach($objects as $o){
			if ($o->getFunctionKey() == 'ask') {
				$results = array();
				SRFTools::findObjectByID($o, WOM_TYPE_NESTPROPERTY, $results);
				$changedQuery |= $this->replaceValueInNestedProperty($results);
				
				$results = array();
                SRFTools::findObjectByID($o, WOM_TYPE_NESTPROPERTY, $results);
                $changedQuery |= $this->replacePropertyInNestedProperty($results);
				
				$results = array();
				SRFTools::findObjectByID($o, WOM_TYPE_LINK, $results);
				$changedQuery |= $this->replaceLink($results);

				$results = array();
				SRFTools::findObjectByID($o, WOM_TYPE_QUERYPRINTOUT, $results);
				foreach($results as $o) {

					$changedQuery |= $this->replacePrintout($o);
				}
			}
		}
       
		# iterate trough the annotations
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$changedAnnotation = $this->replacePropertyInAnnotation($objects);
		$changedValue = $this->replaceValueInAnnotation($objects);

		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$changedLink = $this->replaceLink($objects);


		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();

		if ($changedAnnotation) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 as property of an annotation.', $title, $wikitext, array($this->old, $this->new));
		}
	    if ($changedValue) {
            $logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 as value of an annotation.', $title, $wikitext, array($this->old, $this->new));
        }
		if ($changedLink) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed link from $1 to $2.', $title, $wikitext, array($this->old, $this->new));
		}
		if ($changedQuery) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog("Changed $1 in query to $2.", $title, $wikitext, array($this->old, $this->new));
		}

		return $wikitext;
	}


}
