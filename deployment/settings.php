<?php
/*  Copyright 2009, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class DF_Config  {
	
	// MANDATORY setting!
	// This is required for the webadmin tool. It is the same as specified in
	// LocalSettings.php for $wgScriptPath
	public static $scriptPath = "/deploywiki";
	
	
	// Wiki name the DF is working on (OPTIONAL setting)
	public static $wikiName = "";

	/*
	 * Uncomment the lines and set $df_authorizeByWiki to false
	 * if you do not want to authorize webadmin tool by the wiki user base.
	 *
	 */
	public static $df_authorizeByWiki = true;
	//public static $df_webadmin_user = "root";
	//public static $df_webadmin_pass = "pass";


	/*
	 * Genernal settings for smwadmin/webadmin
	 */
	public static $settings = array(

	//Proxy server e.g. "proxy.example.com:8080"
    'df_proxy' => '', 

	// Home directory where several information is stored
	// restore points, logs, uploaded files.
	// normally $HOME is used.
	// 'df_homedir' => '',

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