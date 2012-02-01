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
 * @author Kai Kuehn
 * 
 */
(function($) {

	var content = {

		htmlTemplate : function(operation) {  
		
			var template = '<div id="sref_main"><form action="" method="get" id="sref_option_form" operation="'+operation+'">'
				+ '<table cellspacing="0" id="fancyboxTable"><tr><td colspan="2" class="fancyboxTitleTd">Options</td></tr>'
				+ '<tr><td colspan="2"><span>Refactoring features are available. Please choose the operation details:</span></td></tr>'
				+ '<tr><td colspan="2">'
				+ '%%OPTIONS%%'
				+ '</table><div style="margin-top: 20px">%%WARNING%%<input type="button" id="sref_start_operation" value="'
				+ mw.msg('sref_start_operation') + '"></input><input style="margin-left:5px" type="button" id="sref_cancel_operation" value="'
				+ mw.msg('sref_cancel_operation') + '"></input><input style="margin-left:5px" type="button" id="sref_preview_operation" value="'
				+ mw.msg('sref_preview_operation') + '"></input></div>' + '</form></div>';
			
			// preview button
			template += '<div id="sref_preview" style="display: none;"><div id="sref_preview_content"></div><input style="margin-left:5px" type="button" id="sref_closepreview_operation" value="'
				+ mw.msg('sref_closepreview_operation') + '"></input></div>';
			
			return template;
		},

		newCheckbox : function(id, checked, requiresBot, granted) {
			
			var disabled = granted ? "" : 'disabled="true"'; 
			var checkedAttribute = checked && granted ? 'checked="true"' : '';
			var html = '<tr><td class="sref_option_table" colspan="2"><input type="checkbox" id="' + id
					+ '" ' + checkedAttribute + ' requiresBot="'
					+ (requiresBot ? "true" : "false") + '" '+disabled+'>' + mw.msg(id)
					+ '</input></td><td class="sref_option_table">'+mw.msg(id+"_help")+'</td></tr>';
			return html;
		},

		createHtml : function(type) {
			var dialogMode = content[type];
			var checkBoxRows = "";
			var granted = false;
			var validGroups = mw.config.get('srefValidGroups').groups;						
			$(mw.config.get('wgUserGroups')).each(function(i, e) {
				var j;
				for( j = 0; j < validGroups.length; j++) {
					if (validGroups[j] == e) { 
						granted = true;
					}
				}
			});
			for (checkBox in dialogMode) {
				checkBoxRows += content.newCheckbox(checkBox,
						dialogMode[checkBox][0], dialogMode[checkBox][1], dialogMode[checkBox][2] | granted)
			}
			var html = content.htmlTemplate(type).replace(/%%OPTIONS%%/, checkBoxRows);
			if (!granted) {
				html = html.replace(/%%WARNING%%/, '<span class="sref_warning">'+mw.msg("sref_warning_no_gardening")+'</span>');
			} else {
				html = html.replace(/%%WARNING%%/, "");
			}
			return html;
		},

		renameInstance : {
			'sref_rename_instance' : [ true, false, true ],
			'sref_rename_annotations' : [ true, true, false ]
		},
		
		renameProperty : {
			'sref_rename_property' : [ true, false , true],
			'sref_rename_annotations' : [ true, true, false ]
		},

		renameCategory : {
			'sref_rename_category' : [ true, false, true ],
			'sref_rename_annotations' : [ true, true, false ]
		},

		deleteCategory : {
			'sref_deleteCategory' : [ true, false, true ],
			'sref_removeInstances' : [ true, true, false ],
			'sref_removeCategoryAnnotations': [ true, true, false ] ,
			/*'removeFromDomain' : [ false, true ],*/
			'sref_removePropertyWithDomain' : [ false, true, false ],
			'sref_removeQueriesWithCategories' : [ true, true, false ],
			'sref_includeSubcategories' : [ false, true, false ],
		},
		
		deleteProperty : {
			'sref_deleteProperty' : [ true, false, '*' ],
			'sref_removeInstancesUsingProperty' : [ true, true, 'gardening' ],
			'sref_removePropertyAnnotations': [ true, true, 'gardening' ] ,
			'sref_removeQueriesWithProperties' : [ false, true , 'gardening'],
			'sref_includeSubproperties' : [ false, true, 'gardening' ]
			
		}
	}

	var dialog = {

		openDialog : function(type, parameters, callback) {
			$
					.fancybox( {
						'content' : content.createHtml(type),
						'modal' : true,
						'width' : '75%',
						'height' : '75%',
						'autoScale' : false,
						'overlayColor' : '#222',
						'overlayOpacity' : '0.8',
						'scrolling' : 'no',
						'titleShow' : false,
						'onCleanup' : function() {

						},
						'onComplete' : function() {
							$('#fancybox-close').show();

							$.fancybox.resize();
							$.fancybox.center();
							
							$('#sref_cancel_operation').click(function() { 
								$.fancybox.close();
							});
							
							var previewRequested = false;
							$('#sref_preview_operation').click(function() { 
								$('#sref_main').toggle();
								$('#sref_preview').toggle();
								var onSuccess = function(responseText) {
									
									// show preview results
									var preview = $.parseJSON(responseText);
									var p, html = "<table>";
									for(p in preview) {
										if (p == 'sref_changedpage') continue;
										html += '<tr>';
										html += '<td>'+mw.msg(p, preview[p])+'</td>';
										html += '</tr>';
									}
									html += '<td class="sref_total_number_preview">'+mw.msg('sref_changedpage', "<b>"+preview['sref_changedpage']+"</b>")+'</td>';
									html += '</table>';
									$('#sref_preview_content').html(html);
								} 
								if (previewRequested) return;
								previewRequested = true;
								var res = dialog.getParameters(parameters);
								var ajaxParams = res['ajaxParams'];
								var operation = $(
										'#sref_option_form')
										.attr('operation');
								dialog.requestPreview(operation, ajaxParams, onSuccess);
								
							});
							$('#sref_closepreview_operation').click(function() { 
								$('#sref_main').toggle();
								$('#sref_preview').toggle();
							});
							$('#sref_start_operation')
									.click(
											function() {
												var res = dialog.getParameters(parameters);
												var ajaxParams = res['ajaxParams'];
												var requiresBot = res['requiresBot'];
												var operation = $(
														'#sref_option_form')
														.attr('operation');

												if (requiresBot)
													dialog.launchBot(operation,
															ajaxParams);
												if (callback) callback(ajaxParams);
												$.fancybox.close();
											});

							
						}
					});
		},
		
		getParameters: function(parameters) {
			var ajaxParams = {};
			for (p in parameters) {
				ajaxParams[p] = parameters[p];
			}
			var requiresBot = false;
			$('input',
					$('#sref_option_form'))
					.each(
							function(i, e) {
								var p = $(e)
										.attr(
												"id");
								var value = $(
										e)
										.attr(
												'checked');
								ajaxParams[p] = value;
								if (value)
									requiresBot = requiresBot
											|| $(
													e)
													.attr(
															'requiresBot') == 'true';
							});
			return { 'ajaxParams':  ajaxParams, 'requiresBot' : requiresBot };
		},

		launchBot : function(operation, params) {

			var onSuccess = function(xhr) {
				// silently ignore
			}

			var onError = function(xhr) {
				if (xhr.status == 403) {
					alert(mw.msg('sref_not_allowed_botstart'));
				} else {
					alert(xhr.responseText);
				}
			}
			
			// set bot parameters
			var paramString = "SRF_OPERATION=" + operation;
			for (p in params) {
				paramString += "," + p + "=" + params[p];
			}

			// launch Bot
			$.ajax({
				url: mw.config.get('wgScript'),
				data: {	action : 'ajax',
						rs : 'smwf_ga_LaunchGardeningBot',
						rsargs : [ 'smw_refactoringbot', paramString, null, null ] 
					},
				success: onSuccess,
				error: onError
			});

		},
		
		requestPreview: function(operation, params, onSuccess) {
			
			var onError = function(xhr) {
				if (xhr.status == 403) {
					alert(mw.msg('sref_not_allowed_botstart'));
				} else {
					alert(xhr.responseText);
				}
			}
			
			// set bot parameters
			var paramString = "SRF_OPERATION=" + operation;
			for (p in params) {
				paramString += "," + p + "=" + params[p];
			}

			// launch Bot
			$.ajax({
				url: mw.config.get('wgScript'),
				data: {	action : 'ajax',
						rs : 'sreff_requestPreview',
						rsargs : [ paramString ] 
					},
				success: onSuccess,
				error: onError
			});
		}
	};

	// make it global
	window.srefgDialog = dialog;

})(jQuery);