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
 * webadmin scripts for DF_SearchTab
 * 	
 * @author: Kai Kühn
 *
 */
/**
 * Called when search button is clicked or enter is pressed in the search input
 * field.
 * 
 * @param e
 *            event
 */
$.webAdmin.operations.searchHandler = function(e) {

	// show repositories
	var html = dfgWebAdminLanguage
			.getMessage('df_webadmin_searching_in_repository');
	$('#df_repository_list option').each(
			function(i, element) {
				var url = $(element).val();
				if ($.trim(url).substr(url.length - 1) != "/") {
					url += "/";
				}
				html += '<br><a target="_blank" href="' + url
						+ 'repository.xml">' + url + '</a>';
			});
	$('#df_search_results_header').html(html);

	var callbackHandler = function(html, status, xhr) {

		$('#df_search_progress_indicator').hide();
		smw_makeSortable($('#df_search_results_table')[0]);
		$('.df_install_all').show();
		// register install buttons
		$('.df_install_button')
				.click(
						function(e) {
							var idAttr = $(e.currentTarget).attr('id');
							var parts = idAttr.split("__");
							var id = parts[1];
							var version = parts[2].split("_")[0];
							var patchlevel = parts[2].split("_")[1];
							var url = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=getDependencies&rsargs[]="
									+ encodeURIComponent(id) + "&rsargs[]="
									+ encodeURIComponent(version);
							var callbackForExtensions = function(xhr, status) {
								if (xhr.responseText
										.indexOf('session: time-out') != -1) {
									alert("Please login again. Session timed-out");
									return;
								}
								var extensionsToInstall = $
										.parseJSON(xhr.responseText);
								var globalSettings = $
										.toJSON($.webAdmin.settings
												.getSettings());
								var url = wgServer
										+ wgScriptPath
										+ "/deployment/tools/webadmin/index.php?rs=install&rsargs[]="
										+ encodeURIComponent(id + "-" + version)
										+ "&rsargs[]="
										+ encodeURIComponent(globalSettings);

								var $dialog = $('#df_install_dialog')
										.dialog(
												{
													autoOpen : false,
													title : dfgWebAdminLanguage
															.getMessage('df_webadmin_pleasewait'),
													modal : true,
													width : 800,
													height : 500,
													operation : "install",
													close : function(event, ui) {
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
								$
										.ajax( {
											url : url,
											dataType : "json",
											complete : $.webAdmin.operations.installStarted
										});

							};
							$.ajax( {
								url : url,
								dataType : "json",
								complete : callbackForExtensions
							});

						});

		// register check buttons
		$('.df_check_button')
				.click(
						function(e) {
							var idAttr = $(e.currentTarget).attr('id');
							var parts = idAttr.split("__");
							var id = parts[1];
							var version = parts[2].split("_")[0];
							var patchlevel = parts[2].split("_")[1];
							var url = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=getDependencies&rsargs[]="
									+ encodeURIComponent(id) + "&rsargs[]="
									+ encodeURIComponent(version);
							var callbackForExtensions = function(xhr, status) {
								if (xhr.responseText
										.indexOf('session: time-out') != -1) {
									alert("Please login again. Session timed-out");
									return;
								}
								var extensionsToInstall = $
										.parseJSON(xhr.responseText);
								var text = "";

								if (extensionsToInstall['exception']) {
									text += extensionsToInstall['exception'][0];
								} else {
									text = dfgWebAdminLanguage
											.getMessage('df_webadmin_wouldbeinstalled');
									text += "<ul>";
									$.each(extensionsToInstall['extensions'],
											function(index, value) {
												var id = value[0];
												var version = value[1];
												text += "<li>" + id + "-"
														+ version + "</li>";
											});
									text += "</ul>";
								}

								$('#check-extension-dialog-text').html(text);
								$("#check-extension-dialog")
										.dialog(
												{
													resizable : false,
													height : 400,
													modal : true,
													buttons : [ {
														text : dfgWebAdminLanguage
																.getMessage('df_webadmin_close'),
														click : function() {
															$(this).dialog(
																	"close");

														}
													} ]

												});
							}
							$.ajax( {
								url : url,
								dataType : "json",
								complete : callbackForExtensions
							});
						});

		$('.df_update_button_search')
				.click(
						function(e2) {
							var idAttr = $(e2.currentTarget).attr('id');
							var parts = idAttr.split("__");
							var id = parts[1];
							var version = parts[2].split("_")[0];
							var patchlevel = parts[2].split("_")[1];
							var dialogText = dfgWebAdminLanguage
									.getMessage('df_webadmin_wouldbeupdated')
							$('#updatedialog-confirm-text').html(
									dialogText + "<ul><li>" + id + "-"
											+ version + "_" + patchlevel
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
																$(this)
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
																$(this)
																		.dialog(
																				"close");
															}
														} ]

											});
						});

		// addhandler for click on extension column
		$('#df_search_results .df_extension_id')
				.click(
						function(e2) {
							var id = $(e2.currentTarget).attr("ext_id");
							var url = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=getDeployDescriptor&rsargs[]="
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
							$dialog.html('<img src="skins/ajax-loader.gif"/>');
							$
									.ajax( {
										url : url,
										dataType : "json",
										complete : $.webAdmin.operations.extensionsDetailsStarted
									});
						});

		// addhandler for click on version column
		$('#df_search_results .df_version')
				.click(
						function(e2) {
							var id = $(e2.currentTarget).attr('extid');
							var version = $(e2.currentTarget).attr('version');
							version = version.split("_")[0]; // remove
							// patchlevel
							var url = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=getDeployDescriptor&rsargs[]="
									+ encodeURIComponent(id) + "&rsargs[]="
									+ encodeURIComponent(version);
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
							$dialog.html('<img src="skins/ajax-loader.gif"/>');
							$
									.ajax( {
										url : url,
										dataType : "json",
										complete : $.webAdmin.operations.extensionsDetailsStarted
									});
						});

		// register handler for selection checkboxes in search results
		// this is for multi-install
		$('.df_checkbox', '#df_search_results').change(function(e) {
			if ($("input:checked", '#df_search_results').length > 0) {
				$('.df_install_all').attr('disabled', false);
			} else {
				$('.df_install_all').attr('disabled', true);
			}
		});

	}
	var searchvalue = $('#df_searchinput').val();
	$('#df_search_progress_indicator').show();
	var url = wgServer + wgScriptPath
			+ "/deployment/tools/webadmin/index.php?rs=search&rsargs[]="
			+ encodeURIComponent(searchvalue);
	$('#df_search_results').load(url, null, callbackHandler);

};

$(document)
		.ready(
				function(e) {

					// register search handler
					$('#df_search').click(function(e) {
						$.webAdmin.operations.searchHandler(e);
					});
					$('#df_searchinput').keypress(function(e) {
						if (e.keyCode == 13) {
							$.webAdmin.operations.searchHandler(e);
						}
					});

					$('.df_install_all')
							.click(
									function(e) {
										$('.df_install_all_progress_indicator').show();
										var selectedExtensionsToInstall = [];
										$("input:checked", '#df_search_results')
												.each(
														function(i, e) {
															var id = $(e).attr(
																	"extid");
															var version = $(e)
																	.attr(
																			"version")
																	.split("_")[0];
															selectedExtensionsToInstall
																	.push(id
																			+ "-"
																			+ version);
														});
										var globalSettings = $
										.toJSON($.webAdmin.settings
												.getSettings());
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=getAllDependencies&rsargs[]="
												+ encodeURIComponent(selectedExtensionsToInstall
														.join(","))+"&rsargs[]="+encodeURIComponent(globalSettings);

										var callbackForExtensions = function(
												xhr, status) {
											$('.df_install_all_progress_indicator').hide();
											if (xhr.responseText
													.indexOf('session: time-out') != -1) {
												alert("Please login again. Session timed-out");
												return;
											}

											var extensionsToInstall = $
													.parseJSON(xhr.responseText);
											var text = "";

											if (extensionsToInstall['exception']) {
												text += extensionsToInstall['exception'][0];
											} else {
												text = dfgWebAdminLanguage
														.getMessage('df_webadmin_wouldbeinstalled');
												text += "<ul>";
												$
														.each(
																extensionsToInstall['extensions'],
																function(index,
																		value) {
																	var id = value[0];
																	var version = value[1];
																	text += "<li>"
																			+ id
																			+ "-"
																			+ version
																			+ "</li>";
																});
												text += "</ul>";
											}

											
											$('#updatedialog-confirm-text')
													.html(text);
											$("#updatedialog-confirm")
													.dialog(
															{
																resizable : false,
																height : 400,
																modal : true,
																buttons : [ {
																	text : dfgWebAdminLanguage
																		.getMessage('df_webadmin_doinstall'),
																	click : function() {
																		$(this)
																				.dialog(
																						"close");
																		var globalSettings = $
																				.toJSON($.webAdmin.settings
																						.getSettings());
																		var url = wgServer
																				+ wgScriptPath
																				+ "/deployment/tools/webadmin/index.php?rs=installAll&rsargs[]="
																				+ encodeURIComponent(selectedExtensionsToInstall
																						.join(","))
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
																							operation : "install",
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
																					complete : $.webAdmin.operations.installStarted
																				});
																	}
																},
																{
																	text : dfgWebAdminLanguage
																			.getMessage('df_webadmin_cancel'),
																	click : function() {
																		$(this)
																				.dialog(
																						"close");
																	}
																}  ]

															});

										}
										$.ajax( {
											url : url,
											dataType : "json",
											complete : callbackForExtensions
										});

									});

				});