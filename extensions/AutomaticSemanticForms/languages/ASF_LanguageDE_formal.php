<?php 


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
