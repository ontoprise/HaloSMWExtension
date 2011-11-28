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



global $asfIP;
include_once($asfIP . '/languages/ASF_LanguageDe.php');

class ASFLanguageDeformal extends ASFLanguageDe {
	
	public function __construct() {
		//$this->asfContentMessages = array_merge($this->asfContentMessages, $this->contentMessagesToOverwrite );
		$this->asfUserMessages = array_merge($this->asfUserMessages, $this->userMessagesToOverwrite );
	}

protected $userMessagesToOverwrite = array(
    // add messages to overwrite in SMW_HaloLanguageDe
    
);

protected $contentMessagesToOverwrite = array(
    // add messages to overwrite in SMW_HaloLanguageDe
);

}
