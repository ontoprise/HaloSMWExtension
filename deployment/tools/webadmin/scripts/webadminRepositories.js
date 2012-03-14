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
 * webadmin scripts for DF_RepositoriesTab
 * 	
 * @author: Kai Kühn
 *
 */
// register repository managment handler
$.webAdmin.operations.addRepositoryHandler = function() {
	var newrepositoryURL = $('#df_newrepository_input').val();
	newrepositoryURL = newrepositoryURL.replace('<', '&lt;');
	newrepositoryURL = newrepositoryURL.replace('&', '&amp;');

	var addToRepositoryCallack = function(xhr, status) {
		if (xhr.responseText.indexOf('session: time-out') != -1) {
			alert("Please login again. Session timed-out");
			return;
		}
		$('#df_settings_progress_indicator').hide();
		if (xhr.status != 200) {
			alert(xhr.responseText);
			return;
		}
		$('#df_repository_list').append(
				$('<option>' + newrepositoryURL + '</option>'));
		window.location.href = wgServer + wgScriptPath
				+ "/deployment/tools/webadmin/index.php?tab=4";
	};
	$('#df_settings_progress_indicator').show();
	var url = wgServer
			+ wgScriptPath
			+ "/deployment/tools/webadmin/index.php?rs=addToRepository&rsargs[]="
			+ encodeURIComponent($('#df_newrepository_input').val());
	$.ajax( {
		url : url,
		dataType : "json",
		complete : addToRepositoryCallack
	});
}

$(document)
		.ready(
				function(e) {
					$('#df_addrepository').click(
							$.webAdmin.operations.addRepositoryHandler);
					$('#df_newrepository_input').keypress(function(e) {
						if (e.keyCode == 13) { // 13 == enter
							$.webAdmin.operations.addRepositoryHandler();
						}
					});
					$('#df_repository_list').change(
							function(e) {
								var selectedURI = $(
										"#df_repository_list option:selected")
										.text();
								$('#df_newrepository_input').val(selectedURI);
							});
					$('#df_removerepository')
							.click(
									function(e) {
										$('#df_repository_list option:selected')
												.each(
														function() {
															var entry = $(this);
															var removeFromRepositoryCallack = function(
																	xhr, status) {
																if (xhr.responseText
																		.indexOf('session: time-out') != -1) {
																	alert("Please login again. Session timed-out");
																	return;
																}
																$(
																		'#df_settings_progress_indicator')
																		.hide();
																if (xhr.status != 200) {
																	alert(xhr.responseText);
																	return;
																}
																entry.remove();
																window.location.href = wgServer
																		+ wgScriptPath
																		+ "/deployment/tools/webadmin/index.php?tab=4";
															};
															$(
																	'#df_settings_progress_indicator')
																	.show();
															var url = wgServer
																	+ wgScriptPath
																	+ "/deployment/tools/webadmin/index.php?rs=removeFromRepository&rsargs[]="
																	+ encodeURIComponent(entry
																			.val());
															$
																	.ajax( {
																		url : url,
																		dataType : "json",
																		complete : removeFromRepositoryCallack
																	});

														});
									});
				});