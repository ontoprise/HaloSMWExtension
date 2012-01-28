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
abstract class SRFRenameOperation extends SRFRefactoringOperation {
	protected $old;
	protected $new;

	protected $affectedPages;


	public function getWork() {
		$this->affectedPages = $this->queryAffectedPages();
		return count($this->affectedPages);
	}

	public function equalsOldPrefixed($prefixedTitle) {
		return $prefixedTitle == $this->old->getPrefixedText() || $prefixedTitle == ":".$this->old->getPrefixedText();

	}

	public function equalsOld($local) {
		return $local == $this->old->getText() || $local == ':'.$this->old->getText();
	}

	public function getNew() {
		return $this->new;
	}

	public function refactor($save = true, & $logMessages) {

		$this->queryAffectedPages();

		foreach($this->affectedPages as $title) {
			if ($title->getNamespace() == SGA_NS_LOG) continue;
			$rev = Revision::newFromTitle($title);

			$wikitext = $this->applyOperation($title, $rev->getRawText(), $logMessages);

			// stores article
			if ($save) {
				$status = $this->storeArticle($title, $wikitext, $rev->getRawComment());
				if (!$status->isGood()) {
					$logMessages[$title->getPrefixedText()][] = new SRFLog('Saving of $title failed due to: $1', $title, $wikitext, array($status->getWikiText()));
				}
			}

			if (!is_null($this->mBot)) $this->mBot->worked(1);
		}


	}

	protected function replaceValueInAnnotation($objects) {
		$changed = false;

		foreach($objects as $o){

			$value = $o->getSMWDataValue();
				
			if (!$o->getProperty()->getDataItem()->isUserDefined()) continue;
				
			$newvalues = array();
			$oldvalues = array();
			if ($value instanceof SMWRecordValue) {
				$dis = $value->getDataItems();
				foreach($dis as $di) {
					if (is_null($di)) {
						$newvalues[] = '';
						continue;
					}
					if ($di->getDIType() == SMWDataItem::TYPE_WIKIPAGE) {
						$title = $di->getTitle()->getPrefixedText();
						$oldvalues[] = $title;
						$this->replacePrefixedTitle($title, 0, $changed);
						$newvalues[] = $title;
					} else {
						// all other types are simply copied
						$newvalues[] = TSHelper::serializeDataItem($di);
					}
				}
			} else if ($value instanceof SMWWikiPageValue) {
				$title = $value->getDataItem()->getTitle()->getPrefixedText();
				$oldvalues[] = $title;
				$this->replacePrefixedTitle($title, 0, $changed);
				$newvalues[] = $title;
			} else if (!is_null($value)) {
				// all other types are simply copied
				$newvalues[] = TSHelper::serializeDataItem($value->getDataItem());
			} else {
				$newvalues[] = trim($o->getPropertyValue());
			}

			$newValue = implode("; ", $newvalues);

			$newDataValue = SMWDataValueFactory::newPropertyObjectValue($o->getProperty()->getDataItem(),$newValue );

			if ($newDataValue->isValid()) {
				$o->setSMWDataValue($newDataValue);
			}

		}
		return $changed;
	}

	protected function replacePropertyInAnnotation($objects) {
		$changed = false;
		foreach($objects as $o){

			$name = $o->getProperty()->getDataItem()->getLabel();
			if ($this->equalsOld($name)) {
				$o->setProperty(SMWPropertyValue::makeUserProperty($this->getNew()->getText()));
				$changed = true;
			}


		}
		return $changed;
	}

	protected function replacePropertyInQuery($objects) {
		$changed = false;
		foreach($objects as $o){

			$name = $o->getProperty()->getDataItem()->getLabel();
			if ($this->equalsOld($name)) {
				$o->setProperty(SMWPropertyValue::makeUserProperty($this->getNew()->getText()));
				$changed = true;
			}
		}
		return $changed;
	}

	/**
	 * Replaces old category with new.
	 * Callback method for array_walk
	 *
	 * @param string $title Prefixed title
	 * @param int $index
	 */
	public function replacePrefixedTitle(& $title, $index, & $changed) {
		$changed = false;
		if ($this->equalsOldPrefixed($title)) {
			$title = $this->getNew()->getPrefixedText();
			$changed = true;
		}
	}

	public function replaceTitle(& $title, $index) {

		if ($this->equalsOld($title)) {
			$title = $this->getNew()->getText();
		}
	}

	protected function replaceCategoryAnnotation($objects) {
		$changed = false;
		foreach($objects as $o){

			$name = $o->getName();
			if ($this->equalsOld($name)) {
				$o->setName( $this->getNew()->getText());
				$changed = true;
			}

		}
		return $changed;
	}

	protected function replaceLink($objects) {
		$changed = false;
		foreach($objects as $o){

			$title = $o->getLink();

			if ($this->equalsOldPrefixed($title)) {
				$esc = "";
				if ($this->getNew()->getNamespace() == NS_CATEGORY) {
					$esc = ":";
				}
				$o->setLink($esc.$this->getNew()->getPrefixedText());
				$changed = true;
			}
		}
		return $changed;
	}

	protected function replaceValueInNestedProperty($objects) {
		$changed = false;
		foreach($objects as $o){

			$value = $o->getValueText();
			$values = $this->splitRecordValues($value);
			array_walk($values, array($this, 'replacePrefixedTitle'));
			$newValue = implode("; ", $values);

			if ($value != $newValue) {
				$changed = true; //FIXME: may be untrue because of whitespaces
				$new = new WOMNestPropertyValueModel();
				$new->insertObject(new WOMTextModel($newValue));
				$o->updateObject($new, $o->getLastObject()->getObjectID());
					
			}

		}
		return $changed;
	}

	protected function replacePropertyInNestedProperty($objects) {
		$changed = false;
		foreach($objects as $o){

			$name = $o->getProperty()->getDataItem()->getLabel();
			if ($this->equalsOld($name)) {
				$o->setProperty(SMWPropertyValue::makeUserProperty($this->getNew()->getText()));
				$changed = true;
			}


		}
		return $changed;
	}

}
