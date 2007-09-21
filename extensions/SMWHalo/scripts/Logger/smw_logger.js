/**
 *  Logger - logs msgs to the database 
 */

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