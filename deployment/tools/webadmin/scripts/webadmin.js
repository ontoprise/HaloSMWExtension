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
	
	var finalizeHandler = function() {
		var finalizeurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=readlog&rsargs[]="+encodeURIComponent(id);
		var callbackForFinalize = function(html, status, xhr) {
		};
	}
	
	
	var installStarted = function (xhr, status) {
		var logfile = xhr.responseText;
		// poll until finished
		var timer;
		
		var periodicLogLoad = function(xhr2, status2) {
			if (timer) clearTimer(timer);
			timer = setTimer( periodicLogLoad, 5000);
			
			var readLogurl = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=readlog&rsargs[]="+encodeURIComponent(logfile);
			$.ajax( { url : readLogurl, dataType:"json", complete : function(xhr3, status3) { 
				var resultLog = xhr3.responseText;
				if (resultLog.indexOf("$$STOPPED$$") != -1 || resultLog.indexOf("$$NOTEXISTS$$") != -1) {
					clearTimer(timer);
					// finalize
				}
				
				$('#df_install_dialog').html(resultLog);
			} });
			
			
		};
	};
		
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
		}
		var searchvalue = $('#df_searchinput').val();
		var url = wgServer+wgScriptPath+"/deployment/tools/webadmin?rs=search&rsargs[]="+encodeURIComponent(searchvalue);
		$('#df_search_results').load(url, null, callbackHandler);
	
	};
	
	
	
	$('#df_search').click(searchHandler);
	$('#df_searchinput').keypress(function(e) { 
		if (e.keyCode == 13) {
			searchHandler(e);
		}
	});
	
	
	
	
});