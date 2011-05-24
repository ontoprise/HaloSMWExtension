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

/**
 * @file
 * @ingroup WebAdmin
 *
 * webadmin scripts
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */

$(function() {
	
		
	/**
	 * Called when finalization process has been started.
	 * 
	 * @param xhr HTTP responseObject 
	 * @param int status HTTP response code 
	 */
	var finalizeStarted = function(xhr, status) { 
		var logfile = xhr.responseText;
		// poll until finished
		var timer;
		
		var periodicLogLoad = function(xhr2, status2) {
			if (timer) clearTimeout(timer);
			timer = setTimeout( periodicLogLoad, 5000);
			
			var readLogurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=readlog&rsargs[]="+encodeURIComponent(logfile);
			$.ajax( { url : readLogurl, dataType:"json", complete : function(xhr3, status3) { 
				var resultLog = xhr3.responseText;
				if (resultLog != '') { 
					$('#df_install_dialog').html(resultLog); 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					// finished installation
				}
				
			} });
			
			
		};
		setTimeout( periodicLogLoad, 3000);
	}
	
	/**
	 * Called when install process has been started.
	 * 
	 * @param xhr HTTP responseObject 
	 * @param int status HTTP response code 
	 */
	var installStarted = function (xhr, status) {
		var logfile = xhr.responseText;
		
		// poll log until finished
		var timer;
		var periodicLogLoad = function(xhr2, status2) {
			if (timer) clearTimeout(timer);
			timer = setTimeout( periodicLogLoad, 5000);
			
			var readLogurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=readlog&rsargs[]="+encodeURIComponent(logfile);
			$.ajax( { url : readLogurl, dataType:"json", complete : function(xhr3, status3) { 
				var resultLog = xhr3.responseText;
				if (resultLog != '') { 
					$('#df_install_dialog').html(resultLog); 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					// start finalize
					var finalizeurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=finalize&rsargs[]=";
					$.ajax( { url : finalizeurl, dataType:"json", complete : finalizeStarted });
				}
				
			} });
			
			
		};
		setTimeout( periodicLogLoad, 3000);
		
	};
	
	/**
	 * Called when de-install process has been started.
	 * 
	 * @param xhr HTTP responseObject 
	 * @param int status HTTP response code 
	 */
	var deinstallStarted = function (xhr, status) {
		var logfile = xhr.responseText;
		
		// poll log until finished
		var timer;
		var periodicLogLoad = function(xhr2, status2) {
			if (timer) clearTimeout(timer);
			timer = setTimeout( periodicLogLoad, 5000);
			
			var readLogurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=readlog&rsargs[]="+encodeURIComponent(logfile);
			$.ajax( { url : readLogurl, dataType:"json", complete : function(xhr3, status3) { 
				var resultLog = xhr3.responseText;
				if (resultLog != '') { 
					$('#df_install_dialog').html(resultLog); 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					// start finalize
					var finalizeurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=finalize&rsargs[]=";
					$.ajax( { url : finalizeurl, dataType:"json", complete : finalizeStarted });
				}
				
			} });
			
			
		};
		setTimeout( periodicLogLoad, 3000);
		
	};
	
	var globalUpdateStarted = function (xhr, status) {
		var logfile = xhr.responseText;
		
		// poll log until finished
		var timer;
		var periodicLogLoad = function(xhr2, status2) {
			if (timer) clearTimeout(timer);
			timer = setTimeout( periodicLogLoad, 5000);
			
			var readLogurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=readlog&rsargs[]="+encodeURIComponent(logfile);
			$.ajax( { url : readLogurl, dataType:"json", complete : function(xhr3, status3) { 
				var resultLog = xhr3.responseText;
				if (resultLog != '') { 
					$('#df_install_dialog').html(resultLog); 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					// start finalize
					var finalizeurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=finalize&rsargs[]=";
					$.ajax( { url : finalizeurl, dataType:"json", complete : finalizeStarted });
				}
				
			} });
			
			
		};
		setTimeout( periodicLogLoad, 3000);
		
	};
	
	/**
	 * Called when search button is clicked or 
	 * enter is pressed in the search input field.
	 * 
	 * @param e event
	 */
	var searchHandler = function(e) {
			
		var callbackHandler = function(html, status, xhr) {
			
			// register install buttons
			$('.df_install_button').click(function(e) {
				var id = $(e.currentTarget).attr('id');
				id = id.split("__")[1];
				var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=getdependencies&rsargs[]="+encodeURIComponent(id);
				var callbackForExtensions = function(xhr, status) {
					var extensionsToInstall = $.parseJSON(xhr.responseText);
					var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=install&rsargs[]="+encodeURIComponent(id);
					
					//var $dialog = $('<div></div>').html('<iframe src="'+url+'" width=\"750\" height/>')
					var $dialog = $('<div id="df_install_dialog"></div>')
					.dialog( {
						autoOpen : false,
						title : 'Please wait...',
						modal: true,
						width: 800,
						height: 500
					});
										
					$dialog.dialog('open');
					
					$.ajax( { url : url, dataType:"json", complete : installStarted });
					
					
				};
				$.ajax( { url : url, dataType:"json", complete : callbackForExtensions });
				
			});
			
			$('.df_check_button').click(function(e) {
				var id = $(e.currentTarget).attr('id');
				id = id.split("__")[1];
				var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=getdependencies&rsargs[]="+encodeURIComponent(id);
				var callbackForExtensions = function(xhr, status) {
					
					var extensionsToInstall = $.parseJSON(xhr.responseText);
					
					alert(xhr.responseText);
				}
				$.ajax( { url : url, dataType:"json", complete : callbackForExtensions });
			});
		}
		var searchvalue = $('#df_searchinput').val();
		var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=search&rsargs[]="+encodeURIComponent(searchvalue);
		$('#df_search_results').load(url, null, callbackHandler);
	
	};
	
	
	// register search handler
	$('#df_search').click(searchHandler);
	$('#df_searchinput').keypress(function(e) { 
		if (e.keyCode == 13) {
			searchHandler(e);
		}
	});
	
	
	$(document).ready(function(e) { 
		$('.df_deinstall_button').click(function(e2) {
			var id = $(e2.currentTarget).attr('id');
			id = id.split("__")[1];
			var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=deinstall&rsargs[]="+encodeURIComponent(id);
			var $dialog = $('<div id="df_install_dialog"></div>')
			.dialog( {
				autoOpen : false,
				title : 'Please wait...',
				modal: true,
				width: 800,
				height: 500
			});
								
			$dialog.dialog('open');
			$.ajax( { url : url, dataType:"json", complete : deinstallStarted });
		});
		
		$('#df_global_update').click(function(e2) {
			
			
			var checkforGlobalUpdate = function(xhr, status) {
				var extensionsToInstall = $.parseJSON(xhr.responseText);
				
				var text = "";
				$.each(extensionsToInstall['extensions'], function(index, value) { 
					text += value[0];
				});
				$('#global-updatedialog-confirm-text').html(text);
				
				$( "#global-updatedialog-confirm" ).dialog({
					resizable: false,
					height:250,
					modal: true,
					 buttons: [
			              {
			                  text: dfgWebAdminLanguage.getMessage('df_webadmin_doupdate'),
			                  click: function() {
			                  	$( this ).dialog( "close" );
			          							var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=doGlobalUpdate&rsargs[]=";
			          							var $dialog = $('<div id="df_install_dialog"></div>')
			          							.dialog( {
			          								autoOpen : false,
			          								title : 'Please wait...',
			          								modal: true,
			          								width: 800,
			          								height: 500
			          							});
			          										
			          							$dialog.dialog('open');
			          							$.ajax( { url : url, dataType:"json", complete : globalUpdateStarted }); 
			                   }
			              },
			               {
			                  text: "Cancel",
			                  click: function() {
			          							$( this ).dialog( "close" );
			          						}
			              }
			         ]
					
				});
			}
			var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=checkforGlobalUpdate&rsargs[]=";
			$.ajax( { url : url, dataType:"json", complete : checkforGlobalUpdate });
		});
	});
	
});