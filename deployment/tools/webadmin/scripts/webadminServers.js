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
 * webadmin scripts for DF_ServersTab
 * 	
 * @author: Kai Kühn
 *
 */
$(document)
		.ready(
				function(e) {
					// register process polling
					if (dfgOS != 'Windows XP') {
						var timer;
						var periodicProcessPoll = function() {
							if (timer)
								clearTimeout(timer);
							timer = setTimeout(periodicProcessPoll, 20000);

							var servers = [ "apache", "mysql", "solr", "tsc",
									"memcached" ];
							var commands = [];
							$(servers).each(
									function() {
										commands.push($(
												'#df_servers_' + this
														+ '_command').val());
									});
							var url = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=areServicesRunning&rsargs[]="
									+ servers.join(",") + "&rsargs[]="
									+ encodeURIComponent(commands.join(","));
							var updateProcessDisplay = function(xhr, status) {

								var result = xhr.responseText.split(",");
								var i = 0;
								$(result)
										.each(
												function(index, s) {
													var flag = $('#df_run_flag_'
															+ servers[i]);
													if (s == "1") {
														flag.text("running");
														flag
																.addClass('df_running_process');
														flag
																.removeClass('df_not_running_process');
													} else {
														flag
																.text("not running")
														flag
																.addClass('df_not_running_process');
														flag
																.removeClass('df_running_process');
													}
													i++;
												});

							};
							$.ajax( {
								url : url,
								dataType : "json",
								complete : updateProcessDisplay,
								timeout : 10000
							});
						}
						setTimeout(periodicProcessPoll, 20000);
					}

					// server command change listener
					$('.df_servers_command').change(
							function(e) {
								var process = $(e.currentTarget).attr('id')
										.split("_")[2];
								var runCommand = $(
										'#df_servers_' + process + '_command')
										.val();
								var selectedId = $("#" + process
										+ "_selector option:selected");
								selectedId.attr("value", runCommand);
								$('#df_servers_save_settings').attr('disabled',
										false);
							});

					// show selected server command
					$('.df_action_selector').change(
							function(e) {
								var process = $(e.currentTarget).attr('id')
										.split("_")[0];
								var selectedId = $("#" + process
										+ "_selector option:selected");
								var runCommand = selectedId.attr("value");
								$('#df_servers_' + process + '_command').val(
										runCommand);
							});

					// load current server command settings
					var loadServerSettings = function(xhr, status) {
						if (xhr.responseText.indexOf('session: time-out') != -1) {
							alert("Please login again. Session timed-out");
							return;
						}
						var result = xhr.responseText;
						if (result == "false")
							return;
						var settings = $.parseJSON(result);
						for (id in settings) {
							var selector = $('#' + id);
							var values = settings[id];
							var startAction = selector[0].firstChild;
							var endAction = startAction.nextSibling;
							$(startAction).attr("value", values[0]);
							if (values.length > 1)
								$(endAction).attr("value", values[1]);

							// set start command
							var process = id.split("_")[0];
							$('#df_servers_' + process + '_command').val(
									values[0]);
						}
					};
					var url = wgServer
							+ wgScriptPath
							+ "/deployment/tools/webadmin/index.php?rs=loadServerSettings&rsargs[]=";
					$.ajax( {
						url : url,
						dataType : "json",
						complete : loadServerSettings
					});

					// save current server command settings
					$('#df_servers_save_settings')
							.click(
									function(e2) {
										var storeServerSettingsExecuted = function(
												xhr, status) {
											if (xhr.responseText
													.indexOf('session: time-out') != -1) {
												alert("Please login again. Session timed-out");
												return;
											}
											var result = xhr.responseText;
											if (result == "true") {
												alert("Server settings are saved!");
											} else {
												alert("Error on saving server settings")
											}
										}
										var settings = {};
										$('.df_action_selector')
												.each(
														function(index, e) {
															var startAction = e.firstChild;
															var endAction = startAction.nextSibling;
															settings[$(e).attr(
																	"id")] = [
																	$(
																			startAction)
																			.attr(
																					"value"),
																	$(endAction)
																			.attr(
																					"value") ];
														});
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=storeServerSettings&rsargs[]="
												+ encodeURIComponent($
														.toJSON(settings));
										$
												.ajax( {
													url : url,
													dataType : "json",
													complete : storeServerSettingsExecuted
												});
										$('#df_servers_save_settings').attr(
												'disabled', true);
									});

					// execute server command button
					var executeCommand = function(e) {
						var commandExecuted = function(xhr, status) {
							if (xhr.responseText.indexOf('session: time-out') != -1) {
								alert("Please login again. Session timed-out");
								return;
							}
							$(e.currentTarget).attr('disabled', false);
						};
						var process = $(e.currentTarget).attr('id').split("_")[2];
						var runCommand = $(
								'#df_servers_' + process + '_command').val();
						var operation = $(
								'#' + process + "_selector option:selected")
								.text();
						var url = wgServer
								+ wgScriptPath
								+ "/deployment/tools/webadmin/index.php?rs=startProcess&rsargs[]="
								+ encodeURIComponent(runCommand) + "&rsargs[]="
								+ operation;
						$.ajax( {
							url : url,
							dataType : "json",
							complete : commandExecuted
						});
						$(e.currentTarget).attr('disabled', true);
					};
					$('.df_servers_execute').click(executeCommand);
				});