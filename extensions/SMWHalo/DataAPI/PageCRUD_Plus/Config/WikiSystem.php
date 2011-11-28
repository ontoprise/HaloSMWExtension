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
