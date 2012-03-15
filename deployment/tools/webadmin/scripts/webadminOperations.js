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
 * webadmin scripts for the WAT operations
 * 	
 * 	- finalizeStarted
 *  - installStarted
 *	- deinstallStarted
 *  - updateStarted
 *  - globalUpdateStarted
 *  - extensionsDetailsStarted
 *
 * @author: Kai Kühn
 *
 */

/**
 * Called when finalization process has been started.
 * 
 * @param xhr
 *            HTTP responseObject
 * @param int
 *            status HTTP response code
 */
$.webAdmin = { operations : {} };

$.webAdmin.operations.finalizeStarted = function(xhr, status) {
	if (xhr.responseText.indexOf('session: time-out') != -1) {
		alert("Please login again. Session timed-out");
		return;
	}
	var logfile = xhr.responseText;
	// poll until finished
	var timer;

	var oldLength = 0;
	var processCounter = 0;
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
						var length = resultLog.length;
						resultLog = resultLog.substr(oldLength);
						oldLength = length;
						resultLog += '<br><a id="df_finalization_console_log_link" target="_blank" href="'
								+ wgServer
								+ wgScriptPath
								+ "/deployment/tools/webadmin/index.php?rs=readLog&rsargs[]="
								+ encodeURIComponent(logfile + ".console_out")
								+ '">'
								+ dfgWebAdminLanguage
										.getMessage('df_webadmin_finalization_console_log')
								+ '</a>';
						if (resultLog != '') {
							resultLog += '<img id="df_progress_indicator" src="skins/ajax-loader.gif"/>';
							var dialog = $('#df_install_dialog');
							$('#df_progress_indicator').remove();
							$('#df_finalization_console_log_link').remove();
							resultLog = resultLog.replace(/__OK__/, "");
							dialog[0].innerHTML += resultLog;
							dialog[0].scrollTop = dialog[0].scrollHeight;
						}
						if (xhr3.responseText.indexOf("__OK__") != -1
								|| xhr3.responseText.indexOf("$$NOTEXISTS$$") != -1
								|| xhr3.responseText.indexOf("$$ERROR$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							// finished installation
							var $dialog = $('#df_install_dialog');
							$dialog
									.dialog(
											'option',
											'title',
											dfgWebAdminLanguage
													.getMessage('df_webadmin_finished'));
							$dialog.dialog("option", "buttons", {
								"Ok" : function() {
									$(this).dialog("close");
								}
							});
							var operation = $dialog.dialog('option',
									'operation');

							var errorstatus = $dialog.dialog('option',
									'errorstatus');
							if (resultLog.indexOf("$$ERROR$$") != -1
									|| errorstatus == 'true') {
								dialog[0].innerHTML += "<br/><br/>"
										+ dfgWebAdminLanguage
												.getMessage('df_webadmin_'
														+ operation
														+ '_failure');
							} else if (resultLog.indexOf("$$NOTEXISTS$$") != -1) {
								dialog[0].innerHTML += "<br/><br/>"
										+ dfgWebAdminLanguage
												.getMessage('df_webadmin_'
														+ operation
														+ '_brokenlog');
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
						$('.ui-dialog-titlebar-close').show();
					}
				});

		if (dfgOS != 'Windows XP') {
			// this call checks periodically if there is at least on PHP process
			// running
			var isProcessRunningUrl = wgServer
					+ wgScriptPath
					+ "/deployment/tools/webadmin/index.php?rs=isProcessRunning&rsargs[]=php";
			$
					.ajax( {
						url : isProcessRunningUrl,
						dataType : "json",
						complete : function(xhr3, status3) {
							if (xhr3.responseText == "false") {
								processCounter++;
								if (processCounter > 5) {
									processCounter = 0;
									$('.ui-dialog-titlebar-close').show();
									var dialog = $('#df_install_dialog');
									dialog[0].innerHTML += "<br/>Seems that operation is not running anymore... You might close the window.";
									dialog[0].scrollTop = dialog[0].scrollHeight;
								}
							}
						}
					});
		}

	};
	setTimeout(periodicLogLoad, 3000);
}

/**
 * Called when install process has been started.
 * 
 * @param xhr
 *            HTTP responseObject
 * @param int
 *            status HTTP response code
 */
$.webAdmin.operations.installStarted = function(xhr, status) {
	
	var globalSettings = $.webAdmin.settings.getSettings();
	
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
								|| xhr3.responseText.indexOf("$$NOTEXISTS$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							// start finalize
							var globalSettings = $.toJSON($.webAdmin.settings.getSettings());
							var finalizeurl = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=finalize&rsargs[]=&rsargs[]="+encodeURIComponent(globalSettings);
							$.ajax( {
								url : finalizeurl,
								dataType : "json",
								complete : $.webAdmin.operations.finalizeStarted
							});
						}

						if (xhr3.responseText.indexOf("$$ERROR$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							var $dialog = $('#df_install_dialog');
							$dialog
									.dialog(
											'option',
											'title',
											dfgWebAdminLanguage
													.getMessage('df_webadmin_finished'));
							$dialog.dialog('option', 'errorstatus', 'true');

							// start finalize
							var globalSettings = $.toJSON($.webAdmin.settings.getSettings());
							var finalizeurl = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=finalize&rsargs[]=&rsargs[]="+encodeURIComponent(globalSettings);
							$.ajax( {
								url : finalizeurl,
								dataType : "json",
								complete : $.webAdmin.operations.finalizeStarted
							});
						}

					}
				});

	};
	setTimeout(periodicLogLoad, 3000);

};

/**
 * Called when de-install process has been started.
 * 
 * @param xhr
 *            HTTP responseObject
 * @param int
 *            status HTTP response code
 */
$.webAdmin.operations.deinstallStarted = function(xhr, status) {
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
								|| xhr3.responseText.indexOf("$$NOTEXISTS$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							// start finalize
							var globalSettings = $.toJSON($.webAdmin.settings.getSettings());
							var finalizeurl = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=finalize&rsargs[]=&rsargs[]="+encodeURIComponent(globalSettings);
							$.ajax( {
								url : finalizeurl,
								dataType : "json",
								complete : $.webAdmin.operations.finalizeStarted
							});
						}

						if (xhr3.responseText.indexOf("$$ERROR$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							var $dialog = $('#df_install_dialog');
							$dialog
									.dialog(
											'option',
											'title',
											dfgWebAdminLanguage
													.getMessage('df_webadmin_finished'));
							$dialog.dialog('option', 'errorstatus', 'true');
							$('.ui-dialog-titlebar-close').show();
						}

					}
				});

	};
	setTimeout(periodicLogLoad, 3000);

};

/**
 * Called when update process has been started (no global update).
 * 
 * @param xhr
 *            HTTP responseObject
 * @param int
 *            status HTTP response code
 */
$.webAdmin.operations.updateStarted = function(xhr, status) {
	
	var globalSettings = $.webAdmin.settings.getSettings();
	
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
								|| xhr3.responseText.indexOf("$$NOTEXISTS$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							// start finalize
							var globalSettings = $.toJSON($.webAdmin.settings.getSettings());
							var finalizeurl = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=finalize&rsargs[]=&rsargs[]="+encodeURIComponent(globalSettings);
							$.ajax( {
								url : finalizeurl,
								dataType : "json",
								complete : $.webAdmin.operations.finalizeStarted
							});
						}

						if (xhr3.responseText.indexOf("$$ERROR$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							var $dialog = $('#df_install_dialog');
							$dialog
									.dialog(
											'option',
											'title',
											dfgWebAdminLanguage
													.getMessage('df_webadmin_finished'));
							$dialog.dialog('option', 'errorstatus', 'true');
							$dialog.dialog("option", "buttons", {
								"Ok" : function() {
									$(this).dialog("close");
								}
							});
						}

					}
				});

	};
	setTimeout(periodicLogLoad, 3000);

};

/**
 * Called when the global update process has been started.
 * 
 * @param xhr
 *            HTTP responseObject
 * @param int
 *            status HTTP response code
 */
$.webAdmin.operations.globalUpdateStarted = function(xhr, status) {
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
								|| xhr3.responseText.indexOf("$$NOTEXISTS$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							// start finalize
							var globalSettings = $.toJSON($.webAdmin.settings.getSettings());
							var finalizeurl = wgServer
									+ wgScriptPath
									+ "/deployment/tools/webadmin/index.php?rs=finalize&rsargs[]=&rsargs[]="+encodeURIComponent(globalSettings);
							$.ajax( {
								url : finalizeurl,
								dataType : "json",
								complete : $.webAdmin.operations.finalizeStarted
							});
						}

						if (xhr3.responseText.indexOf("$$ERROR$$") != -1) {
							clearTimeout(timer);
							$('#df_progress_indicator').hide();
							var $dialog = $('#df_install_dialog');
							$dialog
									.dialog(
											'option',
											'title',
											dfgWebAdminLanguage
													.getMessage('df_webadmin_finished'));
							$dialog.dialog("option", "buttons", {
								"Ok" : function() {
									$(this).dialog("close");
								}
							});
						}

					}
				});

	};
	setTimeout(periodicLogLoad, 3000);

};

/**
 * Called when the extension detail are requested.
 * 
 * @param xhr
 *            HTTP responseObject
 * @param int
 *            status HTTP response code
 */
$.webAdmin.operations.extensionsDetailsStarted = function(xhr, status) {
	if (xhr.responseText.indexOf('session: time-out') != -1) {
		alert("Please login again. Session timed-out");
		return;
	}
	var dd = $.parseJSON(xhr.responseText);

	if (dd.error) {
		$('#df_extension_details')[0].innerHTML = dd.error;
		return;
	}
	var id = dd.id;
	var version = dd.version
	var patchlevel = dd.patchlevel;
	var dependencies = dd.dependencies;
	var maintainer = dd.maintainer != '' ? dd.maintainer : "-";
	var vendor = dd.vendor != '' ? dd.vendor : "-";
	var license = dd.license != '' ? dd.license : "-";
	var helpurl = dd.helpurl != '' ? dd.helpurl : "-";
	var wikidumps = dd.wikidumps;
	var ontologies = dd.ontologies;
	var resources = dd.resources;
	var onlycopyresources = dd.onlycopyresources;
	var notice = dd.notice;

	var i = 0;
	var dependenciesHTML = "<ul class=\"df_enumeration\">";
	$.each(dependencies, function(index, value) {
		var id = value[0];
		var version = value[1];
		dependenciesHTML += "<li>" + id + "-" + version;
		i++;
	});
	dependenciesHTML += "</ul>";
	if (i == 0) {
		dependenciesHTML = "-";
	}

	var wikidumpsHTML = "";
	i = 0;
	if (wikidumps) {
		$.each(wikidumps, function(index, value) {
			var dumpfile = index;
			var titles = value;
			wikidumpsHTML += dumpfile + ":<ul>";
			$.each(titles, function(index, value) {
				var title = value;
				wikidumpsHTML += "<li>" + title + "</li>";
				i++;
			});
			wikidumpsHTML += "</ul>";
		});
	}
	if (i == 0) {
		wikidumpsHTML = "-";
	}

	i = 0;
	var ontologiesHTML = "";
	if (ontologies) {
		$.each(ontologies, function(index, value) {
			var dumpfile = index;
			var titles = value;
			ontologiesHTML += dumpfile + ":<ul class=\"df_enumeration\">";
			$.each(titles, function(index, value) {
				var title = value;
				ontologiesHTML += "<li>" + title + "</li>";
				i++
			});
			ontologiesHTML += "</ul>";
		});
	}
	if (i == 0) {
		ontologiesHTML = "-";
	}

	i = 0;
	var resourcesHTML = "<ul class=\"df_enumeration\">";
	$.each(resources, function(index, value) {
		var file = value;
		resourcesHTML += "<li>" + file + "</li>";
		i++;

	});
	resourcesHTML += "</ul>";
	if (i == 0) {
		resourcesHTML = "-";
	}

	i = 0;
	var resourcesCopyOnlyHTML = "<ul class=\"df_enumeration\">";
	$.each(onlycopyresources, function(index, value) {
		var file = value;
		resourcesCopyOnlyHTML += "<li>" + file + "</li>";
		i++;

	});
	resourcesCopyOnlyHTML += "</ul>";
	if (i == 0) {
		resourcesCopyOnlyHTML = "-";
	}

	var noticeHtml = "";
	if (notice != '') {
		noticeHtml = dfgWebAdminLanguage.getMessage('df_webadmin_checknotice')
				+ ':' + '<pre class="df_notice">'
				+ notice.replace(/\n/g, "<br>") + '</pre>';
	}

	var html = $('#df_extension_details')
			.html(
					'<div>'
							+ noticeHtml
							+ '<table class="df_extension_details" style="width: 100%;">'
							+ '<tr class="df_row_0"><td description="true">'
							+ dfgWebAdminLanguage.getMessage('df_webadmin_id')
							+ '</td><td value="true">'
							+ id
							+ '-'
							+ version
							+ '</td></tr>'
							+ '<tr class="df_row_1"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_patchlevel')
							+ '</td><td value="true">'
							+ patchlevel
							+ '</td></tr>'
							+ '<tr class="df_row_0"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_dependencies')
							+ '</td><td value="true">'
							+ dependenciesHTML
							+ '</td></tr>'
							+ '<tr class="df_row_1"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_maintainer')
							+ '</td><td value="true">'
							+ maintainer
							+ '</td></tr>'
							+ '<tr class="df_row_0"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_vendor')
							+ '</td><td value="true">'
							+ vendor
							+ '</td></tr>'
							+ '<tr class="df_row_1"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_license')
							+ '</td><td value="true">'
							+ license
							+ '</td></tr>'
							+ '<tr class="df_row_0"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_helpurl')
							+ '</td><td value="true"><a href="'
							+ helpurl
							+ '" target="_blank">Help</td></tr>'
							+ '<tr class="df_row_1"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_wikidumps')
							+ '</td><td value="true">'
							+ wikidumpsHTML
							+ '</td></tr>'
							+ '<tr class="df_row_0"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_ontologies')
							+ '</td><td value="true">'
							+ ontologiesHTML
							+ '</td></tr>'
							+ '<tr class="df_row_1"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_resources')
							+ '</td><td value="true">'
							+ resourcesHTML
							+ '</td></tr>'
							+ '<tr class="df_row_0"><td description="true">'
							+ dfgWebAdminLanguage
									.getMessage('df_webadmin_resourcecopyonly')
							+ '</td><td value="true">' + resourcesCopyOnlyHTML
							+ '</td></tr>' + '</table></div>');

};

