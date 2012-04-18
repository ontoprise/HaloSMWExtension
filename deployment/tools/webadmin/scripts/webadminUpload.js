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
 * webadmin scripts for DF_UploadTab
 * 	
 * @author: Kai Kühn
 *
 */
$(document)
		.ready(
				function(e) {
					// upload input field
					$('#df_upload_file_input').change(function(e) {
						$('#df_upload_progress_indicator').show();
						$('#df_upload_file_form').submit();
					});

					// register install file button
					$('.df_installfile_button')
							.click(
									function(e2) {
										var filepath = $(e2.currentTarget)
												.attr('loc');
										var globalSettings = $
										.toJSON($.webAdmin.settings
												.getSettings());
										
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=install&rsargs[]="
												+ encodeURIComponent(filepath)
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
										$
												.ajax( {
													url : url,
													dataType : "json",
													complete : $.webAdmin.operations.installStarted
												});
									});

					// register remove file button
					$('.df_removefile_button')
							.click(
									function(e2) {
										var filepath = $(e2.currentTarget)
												.attr('loc');
										$(e2.currentTarget).parent().parent()
												.remove();
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=removeFile&rsargs[]="
												+ encodeURIComponent(filepath);

										$.ajax( {
											url : url,
											dataType : "json"
										});
									});
				});