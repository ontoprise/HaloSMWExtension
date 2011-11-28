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
 * The user credentials class is used for authentification in order to execute actions over the API.
 *
 * @author Dian
 */
class PCPUserCredentials{
	/**
	 * Username.
	 *
	 * @var string
	 */
	public $un = '';

	/**
	 * Password.
	 *
	 * @var string
	 */
	public $pwd = '';

	/**
	 * User ID. Provided after the login if not available.
	 *
	 * @var unknown_type
	 */
	public $id = '';

	/**
	 * The login token. Needed for each action executed after the login.
	 *
	 * @var string
	 */
	public $lgToken ='';

	/**
	 * The edit token. Needed when editing a page. One edit token is used for all the pages, but changes at every login.
	 * Used also for deleting and moving a page.
	 *
	 * @var string
	 */
	public $editToken = '';

	/**
	 * The class constructor. At creation time common use is to set only username and password.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken
	 * @param string $editToken
	 * @return PCPUserCredentials
	 */
	public function PCPUserCredentials($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL){
		$this->un = $username;
		$this->pwd = $password;
		$this->id = $id;
		$this->lgToken = $loginToken;
		$this->editToken = $editToken;
	}
	/**
	 * Converts the object to an XML node.<br/>
	 * <p><i>Example: &lt;userCredentials un="testuser" pwd="" id ="2" lgToken="1234asdf" editToken=""/&gt;<br/>
	 * </i></p>
	 *
	 *
	 * @return string The XML node.
	 * @deprecated
	 *
	 * @return unknown
	 */
	public function toXML(){
		$__xml = '';

		$__xml.= "<userCredentials ".
		'un="'.$this->un.'" '.
		'pwd="'.$this->pwd.'" '.
		'id="'.$this->id.'" '.
		'lgToken="'.$this->lgToken.'" '.
		'editToken="'.$this->editToken.'" '.		
		"/>"		
		;

		return $__xml;
	}

	/**
	 * Converts the attributes of the object in keys of a hashmap.
	 *
	 *
	 * @return array The hashmap.
	 */
	public function toHashmap(){
		$__hmUc = array();

		$__hmUc= array(
		"username" => $this->un,
		"userid" => $this->id,
		"logintoken" => $this->lgToken,		
		);

		return $__hmUc;
	}
}
