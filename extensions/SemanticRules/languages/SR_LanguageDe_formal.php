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
 * @file
 * @ingroup SRLanguage
 * 
 * Language file De
 * 
 * @author: Kai K�hn
 *
 */
require_once("SR_LanguageDe.php");

class SR_LanguageDe_formal extends SR_LanguageDe {

   public function __construct() {
        $this->srContentMessages = array_merge($this->srContentMessages, $this->contentMessagesToOverwrite );
        $this->srUserMessages = array_merge($this->srUserMessages, $this->userMessagesToOverwrite );
    }
    
    protected $contentMessagesToOverwrite = array(
      // add messages to overwrite in SR_HaloLanguageDe
      
    );
    
   protected $userMessagesToOverwrite = array(
      // add messages to overwrite in SR_HaloLanguageDe
         
   );
}
