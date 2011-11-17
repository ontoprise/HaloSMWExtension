<?php
/**
 * @file
 * @ingroup SRLanguage
 * 
 * Language file De
 * 
 * @author: Kai K�hn / ontoprise / 2009
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
