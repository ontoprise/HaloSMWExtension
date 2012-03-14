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
 * webadmin scripts for DF_LocalSettingsTab
 *
 * @author: Kai Kühn
 *
 */
$(document)
		.ready(
				function(e) {
					// register LocalSettings content
					$('#df_settings_save_button')
							.click(
									function(e2) {
										// save content
										var saveLocalSettingsCallback = function(
												xhr, status) {
											if (xhr.responseText
													.indexOf('session: time-out') != -1) {
												alert("Please login again. Session timed-out");
												return;
											}
											if (xhr.status == 200) {
												alert(dfgWebAdminLanguage
														.getMessage('df_webadmin_save_ok'));
												$('#df_settings_save_button')
														.attr('disabled', true);
											} else {
												alert(dfgWebAdminLanguage
														.getMessage('df_webadmin_save_failed'));
											}
										};
										var fragment = $(
												'#df_settings_textfield').val();
										var selectedId = $(
												"#df_settings_extension_selector option:selected")
												.text();
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php";
										$
												.ajax( {
													url : url,
													type : "POST",
													data : {
														"rs" : "saveLocalSettingFragment",
														"rsargs[]" : [
																selectedId,
																fragment ]
													},
													dataType : "json",
													complete : saveLocalSettingsCallback
												});
									});

					$('#df_settings_textfield')
							.keydown(
									function(e2) {
										// activate save button
										var selectedId = $(
												"#df_settings_extension_selector option:selected")
												.text();
										if (selectedId == dfgWebAdminLanguage
												.getMessage('df_webadmin_select_extension')) {
											return;
										}
										$('#df_settings_save_button').attr(
												'disabled', false);

									});

					$('#df_settings_extension_selector')
							.change(
									function(e2) {
										// load content

										var getLocalSettingsCallback = function(
												xhr, status) {
											if (xhr.responseText
													.indexOf('session: time-out') != -1) {
												alert("Please login again. Session timed-out");
												return;
											}
											if (xhr.status != 200) {
												$('#df_settings_textfield')
														.val("");
												$('#df_settings_textfield')
														.attr('disabled', true);
												alert(dfgWebAdminLanguage
														.getMessage('df_webadmin_fragment_not_found'));
												return;
											}
											$('#df_settings_textfield').attr(
													'disabled', false);
											$('#df_settings_textfield').val(
													xhr.responseText);
										};
										$('#df_settings_save_button').attr(
												'disabled', true);
										var selectedId = $(
												"#df_settings_extension_selector option:selected")
												.text();
										if (selectedId == dfgWebAdminLanguage
												.getMessage('df_webadmin_select_extension')) {
											$('#df_settings_textfield').attr(
													'disabled', true);
											$('#df_settings_textfield').val("");
											return;
										}
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=getLocalSettingFragment&rsargs[]="
												+ encodeURIComponent(selectedId);
										$.ajax( {
											url : url,
											dataType : "json",
											complete : getLocalSettingsCallback
										});
									});

				});