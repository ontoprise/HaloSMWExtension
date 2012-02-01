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
class SRFRenameInstanceOperation extends SRFRenameOperation {


	public function __construct($old, $new) {
		parent::__construct();
		$this->old = Title::newFromText($old);
		$this->new = Title::newFromText($new);
	}

   

	public function queryAffectedPages() {
		if (!is_null($this->affectedPages)) return $this->affectedPages;

		// get all pages using $this->old in an annotation
		$titles = array();
		$this->previewData['sref_changedInstances'] = 0;
		$instanceDi = SMWDIWikiPage::newFromTitle($this->old);
		$properties = smwfGetStore()->getInProperties($instanceDi);
		foreach($properties as $p) {
			$subjects = smwfGetStore()->getPropertySubjects($p, $instanceDi);
			foreach($subjects as $s) {
				$titles[] = $s->getTitle();
				$this->previewData['sref_changedInstances'] += 1;
			}
		}
		
		// get all pages which uses links with that instance
		$this->previewData['sref_changedLinks'] = 0;
		$subjects = $this->old->getLinksTo();
		foreach($subjects as $s) {
			if ($s->isRedirect()) continue;
			$titles[] = $s;
			$this->previewData['sref_changedLinks'] += 1;
		}
		
		// get all queries using $this->old
		$this->previewData['sref_changedQueries'] = 0;
		$queryMetadataPattern = new SMWQMQueryMetadata(true);
		$queryMetadataPattern->instanceOccurences = array($this->old->getPrefixedText() => true);
			
		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		foreach($qmr as $s) {
			$titles[] = Title::newFromText($s->usedInArticle);
			$this->previewData['sref_changedQueries'] += 1;
		}
	
		$this->affectedPages = SRFTools::makeTitleListUnique($titles);
		return $this->affectedPages;
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
				$this->findObjectByID($o, WOM_TYPE_NESTPROPERTY, $results);
				$changedQuery |= $this->replaceValueInNestedProperty($results);

				$results = array();
				$this->findObjectByID($o, WOM_TYPE_LINK, $results);
				$changedQuery |= $this->replaceLink($results);

			}
		}

		# iterate through the annotation values
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_PROPERTY);
		$changedValueinAnnotation = $this->replaceValueInAnnotation($objects);

		# iterate trough the links
		$objects = $pom->getObjectsByTypeID(WOM_TYPE_LINK);
		$changedLink = $this->replaceLink($objects);


		# TODO: iterate through rules
		# not yet implemented in WOM*/

		$wikitext = $pom->getWikiText();

		if ($changedValueinAnnotation) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 as value of an annotation.', $title, $wikitext, array($this->old, $this->new));
		}
		if ($changedLink) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 in a link.', $title, $wikitext, array($this->old, $this->new));
		}
		if ($changedQuery) {
			$logMessages[$title->getPrefixedText()][] = new SRFLog('Changed $1 to $2 in a query.', $title, $wikitext, array($this->old, $this->new));
		}
		return $wikitext;
	}
}
