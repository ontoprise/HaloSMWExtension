<?php

/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

require_once("UserCredentials.php");
require_once("WikiSystem.php");

/**
 * Creates a configuration for the MW system being accessed. 
 * Should be used with the client version of PCP.
 * @deprecated
 */
class PCPConfiguration{
	/**
	 * The user credentials used.
	 *
	 * @var PCPUserCredentials
	 */
	public $uc = NULL;
	/**
	 * The wiki system used.
	 *
	 * @var PCPWikiSystem
	 */
	public $ws = NULL;	
	
	/**
	 * Class constructor.
	 *
	 * @param PCPUsercredentials $userCredentials
	 * @param PCPWikiSystem $wikiSystem
	 */
	public function PCPConfiguration($userCredentials=NULL, $wikiSystem=NULL){
		$this->uc = $userCredentials;
		if($wikiSystem==NULL){
			die ("The wiki system must be configured.");			
		}else{
			$this->ws = $wikiSystem;
		}
	}
	/**
	 * TODO: Create a function that read a configuration file
	 */
}
