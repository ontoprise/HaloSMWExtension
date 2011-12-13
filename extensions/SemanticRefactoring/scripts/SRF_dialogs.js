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
		renamePropertyContent : '<form action="" method="get" id="sref_option_form" operation="renameProperty">'
				+ '<table id="fancyboxTable"><tr><td colspan="2" class="fancyboxTitleTd">Options</td></tr>'
				+ '<tr><td colspan="2"><span>Refactoring features are available. Please choose the operation details:</span></td></tr>'
				+ '<tr><td colspan="2"><input type="checkbox" id="rename_property" checked="true" requiresBot="false">'
				+ mw.msg('rename_property')
				+ '</input></td></tr>'
				+ '<tr><td colspan="2"><input type="checkbox" id="rename_annotations" checked="true" requiresBot="true">'
				+ mw.msg('rename_annotations')
				+ '</input></td></tr>'
				+ '<tr><td colspan="2"><input type="button" id="rename" value="'
				+ mw.msg('rename') + '"></input></td></tr>' + '</table></form>',

		renameCategoryContent : '<form action="" method="get" id="sref_option_form" operation="renameCategory">'
			+ '<table id="fancyboxTable"><tr><td colspan="2" class="fancyboxTitleTd">Options</td></tr>'
			+ '<tr><td colspan="2"><span>Refactoring features are available. Please choose the operation details:</span></td></tr>'
			+ '<tr><td colspan="2"><input type="checkbox" id="rename_property" checked="true" requiresBot="false">'
			+ mw.msg('rename_category')
			+ '</input></td></tr>'
			+ '<tr><td colspan="2"><input type="checkbox" id="rename_annotations" checked="true" requiresBot="true">'
			+ mw.msg('rename_annotations')
			+ '</input></td></tr>'
			+ '<tr><td colspan="2"><input type="button" id="rename" value="'
			+ mw.msg('rename') + '"></input></td></tr>' + '</table></form>'
	}

	var dialog = {

		openDialog : function(type, parameters, callback) {
			$.fancybox( {
				'content' : content[type],
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

					$('#rename').click(function() {
						var ajaxParams = { };
						for(p in parameters) {
							ajaxParams[p] = parameters[p];
						}
						var requiresBot = false;
						$('input', $('#sref_option_form')).each(function(i, e) {
							var p = $(e).attr("id");
							var value = $(e).attr('checked');
							ajaxParams[p] = value;
							if (value) requiresBot = requiresBot || $(e).attr('requiresBot') == 'true'; 
						});
						var operation = $('#sref_option_form').attr('operation'); 
						
						if (requiresBot) dialog.launchBot(operation, ajaxParams);
						callback(ajaxParams);
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
			
			var paramString = "SRF_OPERATION="+operation;
			for(p in params) {
				paramString += ","+p+"="+params[p];
			}
			
			$.get(mw.config.get( 'wgScript' ), {
				action : 'ajax',
				rs : 'smwf_ga_LaunchGardeningBot',
				rsargs : [ 'smw_refactoringbot', paramString , null, null ]
			});

		}
	};

	// make it global
	window.srefgDialog = dialog;

})(jQuery);