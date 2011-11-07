<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
*
*  @file
*  @ingroup SMWHaloLanguage
*  @author Ontoprise
*/

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguageDe.php');

class SMW_HaloLanguageDe_formal extends SMW_HaloLanguageDe {
	
	public function __construct() {
		$this->smwContentMessages = array_merge($this->smwContentMessages, $this->contentMessagesToOverwrite );
		$this->smwUserMessages = array_merge($this->smwUserMessages, $this->userMessagesToOverwrite );
	}

protected $userMessagesToOverwrite = array(
    // add messages to overwrite in SMW_HaloLanguageDe
    
);

protected $contentMessagesToOverwrite = array(
    // add messages to overwrite in SMW_HaloLanguageDe
);

}
