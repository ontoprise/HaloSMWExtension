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
 * webadmin scripts for DF_StatusTab
 * 
 * @author: Kai Kühn
 * 
 */
$(document)
		.ready(
				function(e) {

					// refresh button
					$('#df_refresh_status')
							.click(
									function(e2) {
										window.location.href = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?tab=0";
									});

					// finalize button
					$('#df_run_finalize')
							.click(
									function(e2) {
										// start finalize
										var $dialog = $('#df_install_dialog')
												.dialog(
														{
															autoOpen : false,
															title : dfgWebAdminLanguage
																	.getMessage('df_webadmin_pleasewait'),
															modal : true,
															width : 800,
															height : 500,
															operation : "finalize",
															close : function(
																	event, ui) {
																window.location.href = wgServer
																		+ wgScriptPath
																		+ "/deployment/tools/webadmin/index.php?tab=0";

															}
														});
										$dialog.html("<div></div>");
										$dialog.dialog('open');
										$dialog
												.html('<img src="skins/ajax-loader.gif"/>');
										$('.ui-dialog-titlebar-close').hide();
										var globalSettings = $
												.toJSON($.webAdmin.settings
														.getSettings());
										var finalizeurl = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=finalize&rsargs[]=&rsargs[]="
												+ encodeURIComponent(globalSettings);
										$.ajax( {
											url : finalizeurl,
											dataType : "json",
											complete : function(xhr, status) {
												$dialog.html('');
												$.webAdmin.operations
														.finalizeStarted(xhr,
																status);
											}
										});

									});

					// add about link
					$('#df_webadmin_aboutlink')
							.click(
									function(e2) {
										var $dialog = $(
												'#df_webadmin_about_dialog')
												.dialog(
														{
															autoOpen : false,
															title : dfgWebAdminLanguage
																	.getMessage('df_webadmin_about_title'),
															modal : true,
															width : 350,
															height : 250
														});
										var parts = dfgVersion.split(" ");
										var text = dfgWebAdminLanguage
												.getMessage('df_webadmin_about_desc');
										text += "<br/><br/>Version: "
												+ parts[0];
										text += "<br/>Build: " + parts[1];
										$dialog.html("<div>" + text + "</div>");
										$dialog.dialog('open');
									});

					// register every extension in status view for showing
					// extension details on a click event.
					$('.df_extension_id')
							.click(
									function(e2) {
										var id = $(e2.currentTarget).attr(
												"ext_id");
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=getLocalDeployDescriptor&rsargs[]="
												+ encodeURIComponent(id);
										var $dialog = $('#df_extension_details')
												.dialog(
														{
															autoOpen : false,
															title : dfgWebAdminLanguage
																	.getMessage('df_webadmin_extension_details'),
															modal : true,
															width : 800,
															height : 500
														});
										$dialog.html("<div></div>");
										$dialog.dialog('open');
										$dialog
												.html('<img src="skins/ajax-loader.gif"/>');
										$
												.ajax( {
													url : url,
													dataType : "json",
													complete : $.webAdmin.operations.extensionsDetailsStarted
												});
									});

					// register de-install buttons
					$('.df_deinstall_button')
							.click(
									function(e2) {
										var id = $(e2.currentTarget).attr('id');
										id = id.split("__")[1];

										var deInstallConfirmDialog = $("#deinstall-dialog-confirm")
												.dialog(
														{
															resizable : false,
															height : 350,
															width : 600,
															modal : true,
															

														});
										deInstallConfirmDialog.html('<img src="skins/ajax-loader.gif"/>');
										var globalSettings = $
												.toJSON($.webAdmin.settings
														.getSettings());
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=getDeletionOrder&rsargs[]="
												+ encodeURIComponent(id)
												+ "&rsargs[]="
												+ encodeURIComponent(globalSettings);
										$
												.ajax( {
													url : url,
													dataType : "json",
													complete : function(xhr,
															status) {
														var extensionsToDeInstall = $
																.parseJSON(xhr.responseText);

														if ($.webAdmin.settings
																.getSettings().df_watsettings_deinstall_dependant === false) {
															if (extensionsToDeInstall.length > 1) {
																deInstallConfirmDialog.html(dfgWebAdminLanguage
																		.getMessage('df_webadmin_deinstall_not_possible'));
																return;
															}
														}
														deInstallConfirmDialog.dialog("option", "buttons", {
															"Yes" : {
																	text : dfgWebAdminLanguage
																			.getMessage('df_yes'),
																	click : function() {
																		$(
																				this)
																				.dialog(
																						"close");
																		var globalSettings = $
																				.toJSON($.webAdmin.settings
																						.getSettings());
																		var url = wgServer
																				+ wgScriptPath
																				+ "/deployment/tools/webadmin/index.php?rs=deinstall&rsargs[]="
																				+ encodeURIComponent(id)
																				+ "&rsargs[]="
																				+ encodeURIComponent(globalSettings);
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
																							operation : "deinstall",
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
																					complete : $.webAdmin.operations.deinstallStarted
																				});
																	}
																},
																"No": {
																	text : dfgWebAdminLanguage
																			.getMessage('df_no'),
																	click : function() {
																		$(
																				this)
																				.dialog(
																						"close");
																	}
																}
														
														});
														var text = dfgWebAdminLanguage
																.getMessage('df_webadmin_want_touninstall');
														text += "<ul>";
														$
																.each(
																		extensionsToDeInstall,
																		function(
																				i,
																				e) {
																			text += "<ul><li>"
																					+ e
																					+ "</li></ul>";

																		});
														text += "</ul>";
														deInstallConfirmDialog.html(text);

													}
												});

									});

					// register update buttons
					$('.df_update_button')
							.click(
									function(e2) {
										var idAttr = $(e2.currentTarget).attr(
												'id');
										var parts = idAttr.split("__");
										var id = parts[1];
										var version = parts[2].split("_")[0];
										var patchlevel = parts[2].split("_")[1];
										var dialogText = dfgWebAdminLanguage
												.getMessage('df_webadmin_wouldbeupdated')
										$('#updatedialog-confirm-text').html(
												dialogText + "<ul><li>" + id
														+ "-" + version + "_"
														+ patchlevel
														+ "</li></ul>");
										$("#updatedialog-confirm")
												.dialog(
														{
															resizable : false,
															height : 350,
															modal : true,
															buttons : [
																	{
																		text : dfgWebAdminLanguage
																				.getMessage('df_webadmin_doupdate'),
																		click : function() {
																			$(
																					this)
																					.dialog(
																							"close");

																			var globalSettings = $
																					.toJSON($.webAdmin.settings
																							.getSettings());
																			var url = wgServer
																					+ wgScriptPath
																					+ "/deployment/tools/webadmin/index.php?rs=update&rsargs[]="
																					+ encodeURIComponent(id
																							+ "-"
																							+ version)
																					+ "&rsargs[]="
																					+ encodeURIComponent(globalSettings);
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
																								operation : "update",
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
																						complete : $.webAdmin.operations.updateStarted
																					});
																		}
																	},
																	{
																		text : dfgWebAdminLanguage
																				.getMessage('df_webadmin_cancel'),
																		click : function() {
																			$(
																					this)
																					.dialog(
																							"close");
																		}
																	} ]

														});
									});

					// register global update button
					$('#df_global_update')
							.click(
									function(e2) {

										var checkforGlobalUpdate = function(
												xhr, status) {
											if (xhr.responseText
													.indexOf('session: time-out') != -1) {
												alert("Please login again. Session timed-out");
												return;
											}
											$('#df_gu_progress_indicator')
													.hide();
											var extensionsToInstall = $
													.parseJSON(xhr.responseText);

											var text = "";
											if (extensionsToInstall['extensions']) {
												text = dfgWebAdminLanguage
														.getMessage('df_webadmin_wouldbeupdated');
												text += "<ul>";
												$
														.each(
																extensionsToInstall['extensions'],
																function(index,
																		value) {
																	text += "<li>"
																			+ value[0]
																			+ "-"
																			+ value[1]
																			+ "_"
																			+ value[2]
																			+ "</li>";
																});
												text += "</ul>";
											}
											if (extensionsToInstall['exception']) {
												text += extensionsToInstall['exception'][0];
											}

											$(
													'#global-updatedialog-confirm-text')
													.html(text);

											$("#global-updatedialog-confirm")
													.dialog(
															{
																resizable : false,
																height : 350,
																modal : true,
																buttons : []

															});
											
											// show button if there are no exceptions
											if (!extensionsToInstall['exception']) {
												$("#global-updatedialog-confirm").dialog("option", "buttons", {
													doupdate: {
														text : dfgWebAdminLanguage
																.getMessage('df_webadmin_doupdate'),
														click : function() {
															$(
																	this)
																	.dialog(
																			"close");
															var globalSettings = $
																	.toJSON($.webAdmin.settings
																			.getSettings());
															var url = wgServer
																	+ wgScriptPath
																	+ "/deployment/tools/webadmin/index.php?rs=doGlobalUpdate"
																	+ "&rsargs[]="
																	+ encodeURIComponent(globalSettings);
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
																				operation : "update",
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
																		complete : $.webAdmin.operations.globalUpdateStarted
																	});
														}
													}, cancel:	{
														text : dfgWebAdminLanguage
																.getMessage('df_webadmin_cancel'),
														click : function() {
															$(
																	this)
																	.dialog(
																			"close");
														}
													}
												});
											}
										}
										$('#df_gu_progress_indicator').show();
										var globalSettings = $
										.toJSON($.webAdmin.settings
												.getSettings());
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=checkforGlobalUpdate&rsargs[]="
												+ encodeURIComponent(globalSettings);
										$.ajax( {
											url : url,
											dataType : "json",
											complete : checkforGlobalUpdate
										});
									});
				});