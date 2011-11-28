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


global $sgagIP;
include_once($sgagIP . '/languages/SGA_LanguageDe.php');

class SGA_LanguageDe_formal extends SGA_LanguageDe {
	
	public function __construct() {
		$this->contentMessages = array_merge($this->contentMessages, $this->contentMessagesToOverwrite );
		$this->userMessages = array_merge($this->userMessages, $this->userMessagesToOverwrite );
	}

	protected $userMessagesToOverwrite = array(
	    // add messages to overwrite in SMW_HaloLanguageDe
	);

	protected $contentMessagesToOverwrite = array(
	    // add messages to overwrite in SMW_HaloLanguageDe
	);

}
