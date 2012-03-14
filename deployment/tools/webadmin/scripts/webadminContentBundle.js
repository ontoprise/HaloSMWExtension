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
 * webadmin scripts for DF_ContentBundleTab
 *
 * @author: Kai Kühn
 *
 */

$(document)
		.ready(
				function(e) {
					$('#df_bundles_list').change(function() {
						$('#df_createBundle').attr('disabled', false);
					});
					$('#df_createBundle')
							.click(
									function() {
										var selectedBundle = $('#df_bundlename')
												.val();
										if (!selectedBundle) {
											selectedBundle = $(
													"#df_bundles_list option:selected")
													.text();
										}
										if (selectedBundle == '') {
											alert(dfgWebAdminLanguage
													.getMessage('df_webadmin_emptybundlename'));
											return;
										}
										var url = wgServer
												+ wgScriptPath
												+ "/deployment/tools/webadmin/index.php?rs=createBundle&rsargs[]="
												+ encodeURIComponent(selectedBundle);
										$('#df_bundleexport_progress_indicator')
												.show();
										$('#df_createBundle').attr('disabled',
												true);
										$
												.ajax( {
													url : url,
													dataType : "json",
													complete : function(xhr,
															status) {
														$(
																'#df_bundleexport_progress_indicator')
																.hide();
														$('#df_createBundle')
																.attr(
																		'disabled',
																		false);
														var result = $
																.parseJSON(xhr.responseText);
														if (result.returnCode != 0) {
															$(
																	'#df_contentbundle_error')
																	.html(
																			dfgWebAdminLanguage
																					.getMessage('df_webadmin_contentbundle_error')
																					+ '<a target="_blank" href="'
																					+ wgServer
																					+ wgScriptPath
																					+ '/deployment/tools/webadmin/index.php?rs=readLog&rsargs[]='
																					+ result.outputFile
																					+ '&rsargs[]=text">log</a>.');
															return;
														} else {
															// ok, remove error
															// hint if any
															$(
																	'#df_contentbundle_error')
																	.html('');
														}
														window.location.href = wgServer
																+ wgScriptPath
																+ "/deployment/tools/webadmin/index.php?tab=8";
													}
												});
									});
				});