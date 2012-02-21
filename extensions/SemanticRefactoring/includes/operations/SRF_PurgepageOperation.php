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
class SRFPurgepageOperation extends SRFApplyOperation {

	public function applyOperation(& $title, $wikitext, & $logMessages) {
		// do not store because nothing changed.
		// only purge

		$a = new Article($title);
		$a->doPurge();

		$logMessages[$title->getPrefixedText()][] = new SRFLog("Touched '$1'", $title, "", array($title));
		return $wikitext;
	}

	public function requireSave() {
		return false;
	}
}