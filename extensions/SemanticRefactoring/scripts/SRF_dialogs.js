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
		
			var template = '<form action="" method="get" id="sref_option_form" operation="'+operation+'">'
				+ '<table id="fancyboxTable"><tr><td colspan="2" class="fancyboxTitleTd">Options</td></tr>'
				+ '<tr><td colspan="2"><span>Refactoring features are available. Please choose the operation details:</span></td></tr>'
				+ '<tr><td colspan="2">'
				+ '%%OPTIONS%%'
				+ '<tr><td colspan="2"><input type="button" id="sref_start_operation" value="'
				+ mw.msg('sref_start_operation') + '"></input></td></tr>' + '</table></form>';
			
			return template;
		},

		newCheckbox : function(id, checked, requiresBot) {
			var checkedAttribute = checked ? 'checked="true"' : '';
			var html = '<tr><td colspan="2"><input type="checkbox" id="' + id
					+ '" ' + checkedAttribute + ' requiresBot="'
					+ (requiresBot ? "true" : "false") + '">' + mw.msg(id)
					+ '</input></td><td>'+mw.msg(id+"_help")+'</td></tr>';
			return html;
		},

		createHtml : function(type) {
			var dialogMode = content[type];
			var checkBoxRows = "";
			for (checkBox in dialogMode) {
				checkBoxRows += content.newCheckbox(checkBox,
						dialogMode[checkBox][0], dialogMode[checkBox][1])
			}
			return content.htmlTemplate(type).replace(/%%OPTIONS%%/, checkBoxRows);
		},

		renameInstance : {
			'sref_rename_instance' : [ true, false ],
			'sref_rename_annotations' : [ true, true ]
		},
		
		renameProperty : {
			'sref_rename_property' : [ true, false ],
			'sref_rename_annotations' : [ true, true ]
		},

		renameCategory : {
			'sref_rename_category' : [ true, false ],
			'sref_rename_annotations' : [ true, true ]
		},

		deleteCategory : {
			'sref_deleteCategory' : [ true, false ],
			'sref_removeInstances' : [ true, true ],
			'sref_removeCategoryAnnotations': [ true, true ] ,
			/*'removeFromDomain' : [ false, true ],*/
			'sref_removePropertyWithDomain' : [ false, true ],
			'sref_removeQueriesWithCategories' : [ true, true ],
			'sref_includeSubcategories' : [ false, true ],
		},
		
		deleteProperty : {
			'sref_deleteProperty' : [ true, false ],
			'sref_removeInstancesUsingProperty' : [ true, true ],
			'sref_removePropertyAnnotations': [ true, true ] ,
			'sref_removeQueriesWithProperties' : [ false, true ],
			'sref_includeSubproperties' : [ false, true ]
			
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

							$('#sref_start_operation')
									.click(
											function() {
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
												var operation = $(
														'#sref_option_form')
														.attr('operation');

												if (requiresBot)
													dialog.launchBot(operation,
															ajaxParams);
												if (callback) callback(ajaxParams);
												$.fancybox.close();
											});

							// articleTitleTextBox.focus();
						}
					});
		},

		launchBot : function(operation, params) {

			var callBackOnRunBot = function() {
				alert("Bot started");
			}

			var callBackOnError = function() {
				alert("Error");
			}

			var paramString = "SRF_OPERATION=" + operation;
			for (p in params) {
				paramString += "," + p + "=" + params[p];
			}

			$.get(mw.config.get('wgScript'), {
				action : 'ajax',
				rs : 'smwf_ga_LaunchGardeningBot',
				rsargs : [ 'smw_refactoringbot', paramString, null, null ]
			});

		}
	};

	// make it global
	window.srefgDialog = dialog;

})(jQuery);