<?php

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