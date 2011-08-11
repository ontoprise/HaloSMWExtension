/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *  Logger - logs msgs to the database 
 */
var smwghLoggerEnabled = false;

var SmwhgLogger = Class.create();
SmwhgLogger.prototype = {
	
	/**
	* default constructor
	* Constructor
	*
	*/
	initialize: function() {
	},
	
	/**
	 * Logs msgs through Ajax
	 * * @param 
	 * 
	 * Remote function in php is:
	 * smwLog($logmsg, $errortype = "" , $timestamp = "",$userid = "",$location="", $function="")
	 * 
	 */
	log: function(logmsg, type, func){
		if (!smwghLoggerEnabled) {
			return;
		}
		//Default values
		var logmsg = (logmsg == null) ? "" : logmsg; 
		var type = (type == null) ? "" : type; 
			//Get Timestamp
			var time = new Date();
			var timestamp = time.toGMTString();
		var userid = (wgUserName == null) ? "" : wgUserName; 
		var locationURL = (wgPageName == null) ? "" : wgPageName; 
		var func= (func == null) ? "" : func;
		
		sajax_do_call('smwLog', 
		              [logmsg,type,func,locationURL,timestamp], 
		              this.logcallback.bind(this));	
	},
	
	/**
	 * Shows alert if logging failed
	 * * @param ajax xml returnvalue
	 */
	logcallback: function(param) {
		if(param.status!=200){
			alert('logging failed: ' + param.statusText);
		}
	}
	
}

var smwhgLogger = new SmwhgLogger();