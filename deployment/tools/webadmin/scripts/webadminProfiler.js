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
 * @ingroup WebAdmin
 * 
 * webadmin scripts for DFProfilerTab
 * 
 * @author: Kai Kühn
 * 
 */
$(document).ready(
		function(e) {

			var profilingEnabled = false;
			var currentLog = [];
			var currentRequests = null;
			
			// intialize, check if profiling is enabled 
			var url = wgServer
			+ wgScriptPath
			+ "/deployment/tools/webadmin/index.php?rs=getProfilingState";
			$.ajax( {
				url : url,
				dataType : "json",
				complete : function(xhr,
						status) {
					if (xhr.status == 200) {
						profilingEnabled = (xhr.responseText !== "false");
						if (profilingEnabled) {
							$('#df_webadmin_profiler_content').show();
						} else {
							$('#df_webadmin_profiler_content').hide();
						}
						$('#df_enableprofiling').attr("disabled", false);
						$('#df_enableprofiling').attr("value",
								profilingEnabled ? dfgWebAdminLanguage
										.getMessage('df_webadmin_disableprofiling') : dfgWebAdminLanguage
										.getMessage('df_webadmin_enableprofiling'));
						
						$('#df_webadmin_profiler_content input').attr("disabled", !profilingEnabled);
					}
				}
			});
			
			// enable/disable profiling button
			$('#df_enableprofiling').click(
					function() {
						
						$('#df_enableprofiling').attr("disabled", true);
						var url = wgServer
						+ wgScriptPath
						+ "/deployment/tools/webadmin/index.php?rs=switchProfiling&rsargs[]="
						+ (profilingEnabled ? "false" : "true");
						$.ajax( {
							url : url,
							dataType : "json",
							complete : function(xhr,
									status) {
								$('#df_enableprofiling').attr("disabled", false);
								if (xhr.status == 200) {
									if (!profilingEnabled) {
										currentLog = logParser.splitLog(xhr.responseText);

										var html = logParser.createTable(currentLog);
										$('#df_webadmin_profilerlog').html(html);
									}
									$('#df_enableprofiling').attr("value",
											profilingEnabled ? dfgWebAdminLanguage
													.getMessage('df_webadmin_enableprofiling') : dfgWebAdminLanguage
													.getMessage('df_webadmin_disableprofiling'));
									//$('#df_webadmin_profiler_content textarea').attr("disabled", profilingEnabled);
									$('#df_webadmin_profiler_content input').attr("disabled", profilingEnabled);
									profilingEnabled = !profilingEnabled;
								} else {
									alert(xhr.responseText);
								}
								if (profilingEnabled) {
									$('#df_webadmin_profiler_content').show();
								} else {
									$('#df_webadmin_profiler_content').hide();
								}
						}
						});
			});
			
			// refresh profiling requests button
			$('#df_refreshprofilinglog').click(function() { 
				var url = wgServer
				+ wgScriptPath
				+ "/deployment/tools/webadmin/index.php?rs=getProfilingLogIndices";
				$.ajax( {
					url : url,
					dataType : "json",
					complete : function(xhr,
							status) {
					$('#df_webadmin_profiler_refresh_progress_indicator').hide();
						var html = "";
						var result = $.parseJSON(xhr.responseText);
						var indices = result.indices;
						var filesize = result.filesize;
						var oldIndex = 0;
						$.each(indices, function(i, e) { 
							var index = e[0];
							var logUrl = e[1];
							if (logUrl == '') return;
							html += "<option from=\""+index+"\" to=\""+oldIndex+"\">"+$('<div/>').text(logUrl).html();+"</option>";
							oldIndex = index;
							
						});
						$('#df_webadmin_profiler_selectlog').attr("logfilesize", filesize);
						
						$('#df_webadmin_profiler_selectlog').html(html);
						currentRequests = [];
						$('#df_webadmin_profiler_selectlog option').each(function(e, i) {
							currentRequests.push($(e));
						});
					}
				});
				$('#df_webadmin_profiler_refresh_progress_indicator').show();
			});
			
			// filter input field
			var timeout = null;
			$('#df_profiler_filtering').keydown(function() {
				if (timeout != null) clearTimeout(timeout);
				timeout = setTimeout(function() { 
					var searchFor = $('#df_profiler_filtering').val().toLowerCase();
					
					var selectedLines = $.grep(currentLog, function(l, i) { 
						return (l.text.toLowerCase().indexOf(searchFor) != -1);
					});
				
					
					var html = logParser.createTable(selectedLines);
					var table = $('#df_webadmin_profilerlog');
					table.empty();
					table.html(html);
					
					$('#df_webadmin_profilerlog').html(html);
					
				}, 500);
			});
			
			var timeoutRequestFiltering = null;
			$('#df_profiler_requests_filtering').keydown(function() {
				if (timeoutRequestFiltering != null) clearTimeout(timeoutRequestFiltering);
				timeoutRequestFiltering = setTimeout(function() { 
					var searchFor = $('#df_profiler_requests_filtering').val().toLowerCase();
					
					if (currentRequests == null) {
						// initialize with current set
						currentRequests = [];
						$('#df_webadmin_profiler_selectlog option').each(function(i, e) {
							currentRequests.push($(e));
						});
					}
					var selectBox = $('#df_webadmin_profiler_selectlog');
					selectBox.empty();
					var selectedLines = $.each(currentRequests, function(i, l) { 
						if (l.text().toLowerCase().indexOf(searchFor) != -1) {
							selectBox.append(l[0]);
						}
					});
					
				}, 500);
			});
			
			
			// select a request from list
			$('#df_webadmin_profiler_selectlog').change(function(e) {  
				var from = $(
				"#df_webadmin_profiler_selectlog option:selected")
				.attr("from");
				var to = $(
				"#df_webadmin_profiler_selectlog option:selected")
				.attr("to");
				var logfilesize = $(
				"#df_webadmin_profiler_selectlog")
				.attr("logfilesize");
				var url = wgServer
				+ wgScriptPath
				+ "/deployment/tools/webadmin/index.php?rs=getProfilingLog&rsargs[]="+from+"&rsargs[]="+to+"&rsargs[]="+logfilesize;
				$.ajax( {
					url : url,
					dataType : "json",
					complete : function(xhr,
							status) {
					currentLog = logParser.splitLog(xhr.responseText);
					var html = logParser.createTable(currentLog);
					var table = $('#df_webadmin_profilerlog');
					table.empty();
					table.html(html);
					$('#df_webadmin_profiler_progress_indicator').hide();
					}
				});
				$('#df_webadmin_profiler_progress_indicator').show();
				var table = $('#df_webadmin_profilerlog');
				table.empty();
			});
			
			// clear profiling requests button
			$('#df_clearprofilinglog').click(function() { 
				var url = wgServer
				+ wgScriptPath
				+ "/deployment/tools/webadmin/index.php?rs=clearProfilingLog";
				$.ajax( {
					url : url,
					dataType : "json",
					complete : function(xhr,
							status) {
					$('#df_webadmin_profiler_refresh_progress_indicator').hide();
					if (xhr.status == 200) {
						var table = $('#df_webadmin_profiler_selectlog').empty();
					} else {
						alert("Error on clear log");
					}
					}
				});
				$('#df_webadmin_profiler_refresh_progress_indicator').show();
			});
			
			// logging parser
			var logParser = {};
			logParser.parseLine = function(line) {
				var COLUMN = /\s\s\s/g;
				var matches = line.split(COLUMN);
				if (matches == null || matches.length < 6) return { text: null };
				matches = $.grep(matches, function(e) { 
					return $.trim(e) != '';
				});
				//  Calls         Total          Each             %       Mem
				return { text: matches[0], calls: parseInt(matches[1]), total: parseFloat(matches[2]), 
						each : parseFloat(matches[3]), percentage : parseFloat(matches[4]), mem : parseFloat(matches[5]) };
			}
			
			logParser.splitLog = function(log) {
				var lines = log.split("\n");
				var logArray = [];
				$.each(lines, function(i, l) {
					var nums = logParser.parseLine(l);
					if (nums.text == null ) return;
					logArray.push(nums);
				});
				return logArray;
			} 
			
					
			logParser.createTable = function(logArray) {
			
				var html = "";
				html += "<tr>";
				html += "<th>Function</th>";
				html += "<th>Calls</th>";
				html += "<th>Total</th>";
				html += "<th>Each</th>";
				html += "<th>Percentage</th>";
				html += "<th>Mem</th>";
				html += "</tr>";
				$.each(logArray, function(i, l) { 
					
					html += "<tr>";
					html += "<td>"+l.text+"</td>";
					html += "<td>"+l.calls+"</td>";
					html += "<td>"+l.total+"</td>";
					html += "<td>"+l.each+"</td>";
					html += "<td>"+l.percentage+"</td>";
					html += "<td>"+l.mem+"</td>";
					html += "</tr>";
				});
				return html;
			}
});