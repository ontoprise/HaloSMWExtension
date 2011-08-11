<?php

/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

/**
 * Represents a MW system being accessed remote.
 * <b>NOT USED IN THE CURRENT VERSION.</b>
 */
class PCPWikiSystem{
	
	/**
	 * The URL of the system.
	 *
	 * @var unknown_type
	 */
	public $url = '';
	/**
	 * The port for accessing the system.
	 *
	 * @var unknown_type
	 */
	public $port = '';
	
	/**
	 * The URI of the api.php on the system.
	 *
	 * @var unknown_type
	 */
	public $api = '';
	
	/**
	 * The proxy address to be used when accessing the wiki system.
	 *
	 * @var string
	 */
	public $proxyAddr = '';
	
	/**
	 * Class constructor.
	 *
	 * @param string $url The URL of the target wiki system, e.g. <i>http://mywikisystem.example.com</i>.	 
	 * @param string $api The address of the API - set ONLY the script name.
	 * @param string $proxyAddress The address of the proxy server.
	 */
	public function PCPWikiSystem($url = NULL, $api = 'api.php', $proxyAddress = ''){
		if($url==NULL){
			die ("Missing URL.");
		}else{
			$this->url = $url;			
			$this->api = $api;
			$this->proxyAddr = $proxyAddress;
		}
	}
}
