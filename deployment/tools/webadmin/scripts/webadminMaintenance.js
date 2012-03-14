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
 * webadmin scripts for DF_MaintenanceTab
 *
 * @author: Kai Kühn
 *
 */

/**
 * Called when restore process has been started.
 * 
 * @param xhr
 *            HTTP responseObject
 * @param int
 *            status HTTP response code
 */
$.webAdmin.operations.restoreStarted = function(xhr, status) {
	if (xhr.responseText.indexOf('session: time-out') != -1) {
		alert("Please login again. Session timed-out");
		return;
	}
	var logfile = xhr.responseText;

	// poll log until finished
	var timer;
	var readLogCallPending = false;
	var periodicLogLoad = function(xhr2, status2) {
		if (timer)
			clearTimeout(timer);
		timer = setTimeout(periodicLogLoad, 5000);

		if (readLogCallPending)
			return;
		readLogCallPending = true;
		var readLogurl = wgServer + wgScriptPath
				+ "/deployment/tools/webadmin/index.php?rs=readLog&rsargs[]="
				+ encodeURIComponent(logfile);
		$
				.ajax( {
					url : readLogurl,
					dataType : "json",
					complete : function(xhr3, status3) {
						readLogCallPending = false;
						var resultLog = xhr3.responseText;
						resultLog += '<br><a id="df_console_log_link" target="_blank" href="'
								+ wgServer
								+ wgScriptPath
								+ "/deployment/tools/webadmin/index.php?rs=readLog&rsargs[]="
								+ encodeURIComponent(logfile + ".console_out")
								+ '">'
								+ dfgWebAdminLanguage
										.getMessage('df_webadmin_console_log')
								+ '</a>';
						if (resultLog != '') {
							resultLog += '<img id="df_progress_indicator" src="skins/ajax-loader.gif"/>';
							var dialog = $('#df_install_dialog');
							resultLog = resultLog.replace(/__OK__/, "");
							dialog[0].innerHTML = resultLog;
							dialog[0].scrollTop = dialog[0].scrollHeight;
						}
						if (xhr3.responseText.indexOf("__OK__") != -1
								|| xhr3.responseText.indexOf("$$NOTEXISTS$$") != -1
								|| xhr3.responseText.indexOf("$$ERROR$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							var dialog = $('#df_install_dialog');
							dialog
									.dialog(
											'option',
											'title',
											dfgWebAdminLanguage
													.getMessage('df_webadmin_finished'));

							dialog.dialog("option", "buttons", {
								"Ok" : function() {
									$(this).dialog("close");
								}
							});
							var operation = $dialog.dialog('option',
									'operation');

							if (resultLog.indexOf("$$ERROR$$") != -1) {
								dialog[0].innerHTML += "<br/><br/>"
										+ dfgWebAdminLanguage
												.getMessage('df_webadmin_'
														+ operation
														+ '_failure');
							} else {
								dialog[0].innerHTML += "<br/><br/>"
										+ dfgWebAdminLanguage
												.getMessage('df_webadmin_'
														+ operation
														+ '_successful');
							}
							// make sure it is visible
							dialog[0].scrollTop = dialog[0].scrollHeight;
						}

					}
				});

	};
	setTimeout(periodicLogLoad, 3000);

};

/**
 * Called when "Create" button on the maintenance tab is clicked or enter is
 * pressed in the restore point name input field.
 * 
 * @param e
 *            event
 */
$.webAdmin.operations.restoreHandler = function(e) {
	var restorepoint = $('#df_restorepoint').val();
	var url = wgServer
			+ wgScriptPath
			+ "/deployment/tools/webadmin/index.php?rs=createRestorePoint&rsargs[]="
			+ encodeURIComponent(restorepoint);
	var $dialog = $('#df_install_dialog').dialog(
			{
				autoOpen : false,
				title : dfgWebAdminLanguage
						.getMessage('df_webadmin_pleasewait'),
				modal : true,
				width : 800,
				height : 500,
				close : function(event, ui) {
					window.location.href = wgServer + wgScriptPath
							+ "/deployment/tools/webadmin/index.php?tab=3";

				}
			});
	$dialog.html("<div></div>");
	$dialog.dialog('open');
	$dialog.html('<img src="skins/ajax-loader.gif"/>');
	$('.ui-dialog-titlebar-close').hide();
	$.ajax( {
		url : url,
		dataType : "json",
		complete : $.webAdmin.operations.restoreStarted
	});
}

$(document)
		.ready(
				function(e) {
					// register restore handler
					$('#df_create_restorepoint').click(
							$.webAdmin.operations.restoreHandler);
					$('#df_restorepoint').keypress(function(e) {
						if (e.keyCode == 13) {
							$.webAdmin.operations.restoreHandler(e);
						}
					});

					// register restore buttons
					$('.df_restore_button')
							.click(
									function(e) {
										var restorepoint = $(e.currentTarget)
												.attr('id');
										restorepoint = restorepoint.split("__")[1];

										$("#restore-dialog-confirm")
												.dialog(
														{
															resizable : false,
															height : 350,
															modal : true,
															buttons : [
																	{
																		text : dfgWebAdminLanguage
																				.getMessage('df_yes'),
																		click : function() {
																			$(
																					this)
																					.dialog(
																							"close");

																			var url = wgServer
																					+ wgScriptPath
																					+ "/deployment/tools/webadmin/index.php?rs=restore&rsargs[]="
																					+ encodeURIComponent(restorepoint);
																			var $dialog = $(
																					'#df_install_dialog')
																					.dialog(
																							{
																								autoOpen : false,
																								title : dfgWebAdminLanguage
																										.getMessage('df_webadmin_pleasewait'),
																								modal : true,
																								width : 800,
																								height : 500,
																								close : function(
																										event,
																										ui) {
																									window.location.href = wgServer
																											+ wgScriptPath
																											+ "/deployment/tools/webadmin/index.php?tab=0";

																								}
																							});
																			$dialog
																					.html("<div></div>");
																			$dialog
																					.dialog('open');
																			$dialog
																					.html('<img src="skins/ajax-loader.gif"/>');
																			$(
																					'.ui-dialog-titlebar-close')
																					.hide();
																			$
																					.ajax( {
																						url : url,
																						dataType : "json",
																						complete : $.webAdmin.operations.restoreStarted
																					});

																		}
																	},
																	{
																		text : dfgWebAdminLanguage
																				.getMessage('df_no'),
																		click : function() {
																			$(
																					this)
																					.dialog(
																							"close");
																		}
																	} ]

														});

									});

					$('.df_remove_restore_button')
							.click(
									function(e) {
										var restorepoint = $(e.currentTarget)
												.attr('id');
										restorepoint = restorepoint.split("__")[1];
										$("#remove-restore-dialog-confirm")
												.dialog(
														{
															resizable : false,
															height : 350,
															modal : true,
															buttons : [
																	{
																		text : dfgWebAdminLanguage
																				.getMessage('df_yes'),
																		click : function() {
																			$(
																					this)
																					.dialog(
																							"close");

																			var url = wgServer
																					+ wgScriptPath
																					+ "/deployment/tools/webadmin/index.php?rs=removeRestorePoint&rsargs[]="
																					+ encodeURIComponent(restorepoint);
																			var $dialog = $(
																					'#df_install_dialog')
																					.dialog(
																							{
																								autoOpen : false,
																								title : dfgWebAdminLanguage
																										.getMessage('df_webadmin_pleasewait'),
																								modal : true,
																								width : 800,
																								height : 500,
																								close : function(
																										event,
																										ui) {
																									window.location.href = wgServer
																											+ wgScriptPath
																											+ "/deployment/tools/webadmin/index.php?tab=0";

																								}
																							});
																			$dialog
																					.html("<div></div>");
																			$dialog
																					.dialog('open');
																			$dialog
																					.html('<img src="skins/ajax-loader.gif"/>');
																			$(
																					'.ui-dialog-titlebar-close')
																					.hide();
																			$
																					.ajax( {
																						url : url,
																						dataType : "json",
																						complete : $.webAdmin.operations.restoreStarted
																					});

																		}
																	},
																	{
																		text : dfgWebAdminLanguage
																				.getMessage('df_no'),
																		click : function() {
																			$(
																					this)
																					.dialog(
																							"close");
																		}
																	} ]

														});
									});
				});