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
 * IMPORTANT: configure it and copy this file in the root folder of the admin tool!
 */

class DF_Config  {

	// MANDATORY setting!
	// This is required for the webadmin tool. It is the same as specified in
	// LocalSettings.php for $wgScriptPath
	public static $scriptPath = "/mediawiki";
	
	// MANDATORY setting!
	// DF GUI language (default is english)
	// same as in LocalSettings.php for $wgLang
	public static $df_lang = "en";

	// Arbitrary name for the wiki the DF is working on
	// if you use more than one wiki on a machine, make sure you
	// use different names for each. Don't change it afterwards,
	// otherwise DF won't find your restore points again.
	public static $df_wikiName = "mywiki";

	/*
	 * Uncomment the lines and set $df_authorizeByWiki to false
	 * if you do not want to authorize webadmin tool by the wiki user base.
	 *
	 * This is REQUIRED if 'curl'-extension is NOT installed in your PHP installation!
	 *
	 */
	public static $df_authorizeByWiki = true;
	//public static $df_webadmin_user = "root";
	//public static $df_webadmin_pass = "pass";
	
	/*
	 * Only for LDAP users  (works with LdapAuthentication extension)
	 * 
	 * The domain must be set if in order to access 
	 * the DF-GUI. 
	 */
	public static $df_webadmin_ldap_domain = "";

   
	
	/*
	 * Automatic check for updates if a sysop user logs in.
	 * Default is true.
	 * 
	 * @var boolean
	 */
	public static $df_checkForUpdateOnLogin = true;
	
	/**
	 * Account which is used to run commands on server for webadmin tool.
	 * If not set, the account on which the webserver runs is used.
	 * @var string
	 */
	public static $df_runas_user = NULL;
	public static $df_runas_password = NULL;
	
	/**
	 * Indicates if the TSC should be refreshed after a bundle/ontology import.
	 * 
	 * @var boolean
	 */
	public static $df_refresh_TSC = true;
	
	/*
	 * General settings for smwadmin/webadmin
	 */
	public static $settings = array(

// keep cmd window on Windows when using DF-GUI
'df_keep_cmd_window' => false,
	  
//Proxy server e.g. "proxy.example.com:8080"
//'df_proxy' => '', 

// Home directory where several information is stored
// restore points, logs, uploaded files.
// normally $HOME is used.
// 'df_homedir' => '',

// upload directory for DF-GUI, if not set home directory or temp is used.
//'df_uploaddir' => '',

// uncomment one of the following lines to use a particular HTTP impl.
// per default a suitable impl. is selected automatically
//'df_http_impl' => 'HttpDownloadCurlImpl',
//'df_http_impl' => 'HttpDownloadSocketImpl',

// set PHP executable (only if it is not in PATH or it has the wrong version)
// PHP 5 is required at least
'df_php_executable' => 'php',
	
// set MYSQL directory (only if it is not in PATH)
'df_mysql_dir' => '',

	);
	public static function getValue($identifier){
		if (array_key_exists($identifier, DF_Config::$settings)) {
			//return settingsvalue
			return DF_Config::$settings[$identifier];
		} else {
			//key not present, so no value is set
			return "";
		}
	}


}

define('DEPLOY_FRAMEWORK_NO_HOME_DIR', 1);
define('DEPLOY_FRAMEWORK_NO_TMP_DIR', 2);

/**
 * Setting errors
 * 
 * @author kai
 *
 */
class DF_SettingError extends Exception {
	var $msg;
	var $arg1;
	var $arg2;

	public function __construct($errCode, $msg = '', $arg1 = NULL, $arg2 = NULL) {
		$this->errCode = $errCode;
		$this->msg = $msg;
		$this->arg1 = $arg1;
		$this->arg2 = $arg2;
	}

	public function getMsg() {
		return $this->msg;
	}

	public function getErrorCode() {
		return $this->errCode;
	}

	public function getArg1() {
		return $this->arg1;
	}

	public function getArg2() {
		return $this->arg2;
	}
}
