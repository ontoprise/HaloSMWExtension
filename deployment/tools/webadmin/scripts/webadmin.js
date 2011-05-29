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
					resultLog += '<img id="df_progress_indicator" src="skins/ajax-loader.gif"/>';
					var dialog = $('#df_install_dialog');
					dialog[0].innerHTML = resultLog; 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					$('#df_progress_indicator').hide();
					// finished installation
					var $dialog = $('#df_install_dialog');
					$dialog.dialog('option', 'title', dfgWebAdminLanguage.getMessage('df_webadmin_finished'));
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
					resultLog += '<img id="df_progress_indicator" src="skins/ajax-loader.gif"/>';
					var dialog = $('#df_install_dialog');
					dialog[0].innerHTML = resultLog; 
					
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					$('#df_progress_indicator').hide();
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
					resultLog += '<img id="df_progress_indicator" src="skins/ajax-loader.gif"/>';
					var dialog = $('#df_install_dialog');
					dialog[0].innerHTML = resultLog; 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					$('#df_progress_indicator').hide();
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
					resultLog += '<img id="df_progress_indicator" src="skins/ajax-loader.gif"/>';
					var dialog = $('#df_install_dialog');
					dialog[0].innerHTML = resultLog; 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					$('#df_progress_indicator').hide();
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
						title : dfgWebAdminLanguage.getMessage('df_webadmin_pleasewait'),
						modal: true,
						width: 800,
						height: 500
					});
										
					$dialog.dialog('open');
					$dialog.html('<img src="skins/ajax-loader.gif"/>');
					
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
					var text = "";
								
					if (extensionsToInstall['exception']) {
						text += extensionsToInstall['exception'][0];
					} else {
						text = dfgWebAdminLanguage.getMessage('df_webadmin_wouldbeinstalled');
						$.each(extensionsToInstall['extensions'], function(index, value) { 
							var id =value[0];
							var version =value[1];
							text += "<li>"+id+"-"+version;
						});
					}
					
					$('#check-extension-dialog-text').html(text);
					$( "#check-extension-dialog" ).dialog({
						resizable: false,
						height:250,
						modal: true,
						 buttons: [
				              {
				                  text: "OK",
				                  click: function() {
				                  	$( this ).dialog( "close" );
				          						 
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
		
		$('.df_extension_id').click(function(e2) {
			var $dialog = $('<div id="df_extension_details">TOOD: implement</div>')
			.dialog( {
				autoOpen : false,
				title : dfgWebAdminLanguage.getMessage('df_webadmin_extension_details'),
				modal: true,
				width: 800,
				height: 500
			});
			$dialog.dialog('open');
		});
		
		$('.df_deinstall_button').click(function(e2) {
			var id = $(e2.currentTarget).attr('id');
			id = id.split("__")[1];
			var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=deinstall&rsargs[]="+encodeURIComponent(id);
			var $dialog = $('<div id="df_install_dialog"></div>')
			.dialog( {
				autoOpen : false,
				title : dfgWebAdminLanguage.getMessage('df_webadmin_pleasewait'),
				modal: true,
				width: 800,
				height: 500
			});
								
			$dialog.dialog('open');
			$dialog.html('<img src="skins/ajax-loader.gif"/>');
			$.ajax( { url : url, dataType:"json", complete : deinstallStarted });
		});
		
		$('#df_global_update').click(function(e2) {
			
			
			var checkforGlobalUpdate = function(xhr, status) {
				var extensionsToInstall = $.parseJSON(xhr.responseText);
				
				var text = "";
				if (extensionsToInstall['extensions']) {
					text = dfgWebAdminLanguage.getMessage('df_webadmin_wouldbeupdated');
					$.each(extensionsToInstall['extensions'], function(index, value) { 
					text += "<li>"+value[0]+"-"+value[1];
				});
				}
				if (extensionsToInstall['exception']) {
					text += extensionsToInstall['exception'][0];
				}
				
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
			          								title : dfgWebAdminLanguage.getMessage('df_webadmin_pleasewait'),
			          								modal: true,
			          								width: 800,
			          								height: 500
			          							});
			          										
			          							$dialog.dialog('open');
			          							$dialog.html('<img src="skins/ajax-loader.gif"/>');
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
	
	
	/**
	 * Called when restore process has been started.
	 * 
	 * @param xhr HTTP responseObject 
	 * @param int status HTTP response code 
	 */
	var restoreStarted = function (xhr, status) {
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
					resultLog += '<img id="df_progress_indicator" src="skins/ajax-loader.gif"/>';
					var dialog = $('#df_install_dialog');
					dialog[0].innerHTML = resultLog; 
				}
				if (resultLog.indexOf("__OK__") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimeout(timer);
					$('#df_progress_indicator').hide();
					// done
				}
				
			} });
			
			
		};
		setTimeout( periodicLogLoad, 3000);
		
	};
	
	/**
	 * Called when "Create" button on the maintenance tab is clicked or 
	 * enter is pressed in the restore point name input field.
	 * 
	 * @param e event
	 */
	var restoreHandler = function(e) {
		var restorepoint = $('#df_restorepoint').val();
		var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=createRestorePoint&rsargs[]="+encodeURIComponent(restorepoint);
		var $dialog = $('<div id="df_install_dialog"></div>')
		.dialog( {
			autoOpen : false,
			title : dfgWebAdminLanguage.getMessage('df_webadmin_pleasewait'),
			modal: true,
			width: 800,
			height: 500
		});
							
		$dialog.dialog('open');
		$dialog.html('<img src="skins/ajax-loader.gif"/>');
		$.ajax( { url : url, dataType:"json", complete :restoreStarted });
	}
	
	// register restore handler
	$('#df_create_restorepoint').click(restoreHandler);
	$('#df_restorepoint').keypress(function(e) { 
		if (e.keyCode == 13) {
			restoreHandler(e);
		}
	});
	
	
	
	// register restore buttons
	$('.df_restore_button').click(function(e) {
		var restorepoint = $(e.currentTarget).attr('id');
		restorepoint = restorepoint.split("__")[1];
		var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=restore&rsargs[]="+encodeURIComponent(restorepoint);
		var $dialog = $('<div id="df_install_dialog"></div>')
		.dialog( {
			autoOpen : false,
			title : dfgWebAdminLanguage.getMessage('df_webadmin_pleasewait'),
			modal: true,
			width: 800,
			height: 500
		});
							
		$dialog.dialog('open');
		$dialog.html('<img src="skins/ajax-loader.gif"/>');
		$.ajax( { url : url, dataType:"json", complete :restoreStarted });
		
	});
});