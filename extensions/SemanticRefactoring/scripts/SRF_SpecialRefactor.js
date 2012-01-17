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
		level1 : {	
			0 : mw.msg('sref_category'),
			1 : mw.msg('sref_annotationproperty'),
			2 : mw.msg('sref_template') }
		,
		
		level2 : {
			0 : [mw.msg('sref_add'), mw.msg('sref_remove'), mw.msg('sref_replace')],
			1 : [mw.msg('sref_add'), mw.msg('sref_remove'), mw.msg('sref_replace'), mw.msg('sref_setvalue')],
			2 : [mw.msg('sref_setvalue'), mw.msg('sref_rename'), mw.msg('sref_replace')]
		},
		
		operationnames: {
			'00' : 'addCategory',
			'01' : 'removeCategory',
			'02' : 'replaceCategory',
			
			'10' : 'addAnnotation',
			'11' : 'removeAnnotation',
			'12' : 'replaceAnnotation',
			'13' : 'setValueOfAnnotation',
			
			'20' : 'setValueOfTemplate',
			'21' : 'renameTemplateParameter',
			'22' : 'replaceTemplateValue',
		},
		
		
		parameters : { 
			'00' : [ { id : 'category', ac : 'namespace: Category', title : mw.msg('sref_category'), optional : false } ],
			'01' : [ { id : 'category', ac : 'namespace: Category', title : mw.msg('sref_category'), optional : false } ],
			'02' : [ { id : 'old_category', ac : 'namespace: Category', title : mw.msg('sref_old_category'), optional : false },
			         { id : 'new_category', ac : 'namespace: Category', title : mw.msg('sref_new_category'), optional : false } ],
			'10' : [ { id : 'property', ac : 'namespace: Property', title : mw.msg('sref_property'), optional : false },
			         { id : 'value', title : mw.msg('sref_value'), optional : false } ],
			'11' : [ { id : 'property', ac : 'namespace: Property', title : mw.msg('sref_property') },
			         { id : 'value', title : mw.msg('sref_value'), optional : true } ],
			'12' : [ { id : 'property', ac : 'namespace: Property', title : mw.msg('sref_property'), optional : false },
			         { id : 'old_value', title : mw.msg('sref_old_value'), optional : false } ,
			         { id : 'new_value', title : mw.msg('sref_new_value'), optional : false } ],
			'13' : [ { id : 'property', ac : 'namespace: Property', title : mw.msg('sref_property'), optional : false },
					 { id : 'value', title : mw.msg('sref_value'), optional : false } ],
			'20' : [ { id : 'template', ac : 'namespace: Template', title :  mw.msg('sref_template'), optional : false },
					 { id : 'parameter', title : mw.msg('sref_parameter'), optional : false },
					 { id : 'value', title : mw.msg('sref_value'), optional : false } ],
			'21' : [ { id : 'template', ac : 'namespace: Template', title : mw.msg('sref_template'), optional : false },
					 { id : 'old_parameter', title : mw.msg('sref_old_parameter'), optional : false },
					 { id : 'new_parameter', title : mw.msg('sref_new_parameter'), optional : false }],
								 
			'22' : [ { id : 'template', ac : 'namespace: Template', title : mw.msg('sref_template'), optional : false },
					 { id : 'parameter', title : mw.msg('sref_parameter'), optional : false },
					 { id : 'old_value', title :  mw.msg('sref_old_value'), optional : false },
					 { id : 'new_value', title :  mw.msg('sref_new_value'), optional : false }]
		
		
		}
	
	};
	
	var visibleSlice = 0;

	var commandBox = {
			
		current_operation : -1, 
		
		createHTML : function() {
			var html = '';
			html += '<div style="float:left"><select id="sref_operation_type" class="sref_operation_selector" size="5">';
			for (e in content.level1) { 
				html += '<option value="'+content.level1[e]+'">'+content.level1[e]+'</option>';
			}
			html += '</select></div>';
			
			html += '<div style="float:left"><img src="'+wgScriptPath+'/extensions/SemanticRefactoring/skins/images/arrow.png"/></div>';
			html += '<div style="float:left"><select id="sref_operation" class="sref_operation_selector" size="5">';
			html += '</select></div>';
			
			html += '<div style="float:left"><img src="'+wgScriptPath+'/extensions/SemanticRefactoring/skins/images/arrow.png"/></div>'
			html += '<div style="float:left" id="sref_parameters">';
			html += '</div>';
			
			return html;
		},
		
		
	
		addListeners: function() {
			$('#sref_operation_type').change(function(e) { 
				var i = e.currentTarget.selectedIndex;
				commandBox.current_operation = i;
				var html = "";
				$(content.level2[i]).each(function(i, e) { 
					html += '<option value="'+e+'">'+e+'</option>';
				});
				$('#sref_operation').html(html);
			});
			
			$('#sref_operation').change(function(e) { 
				var i = e.currentTarget.selectedIndex;
				var html = '<table class="sref_command_parameters">';
				i = ""+commandBox.current_operation+i;
				$(content.parameters[i]).each(function(i, e) {
					html += "<tr>";
					html += commandBox.createInputField(e);
					html += "</tr>";
				});
				html += "</table>";
				$('#sref_parameters').html(html);
			});
			
			
			
		},
		
		createInputField : function(e) {
	
			var acAttr = "";
			if (e.ac && e.ac != null) {
				acAttr='class="wickEnabled"';
				acAttr+=' constraints="'+e.ac+'"';
			}
			var optionalAttr = "";
			if (typeof(e.optional) != 'undefined') {
				optionalAttr = e.optional == true ? 'optional="true"' : 'optional="false"';
			}
			var html = '<td class="sref_param_label">'+e.title+"</td>"+'<td class="sref_param_input"><input id="'+e.id+'" '+optionalAttr+' type="text" size="30" value="" '+acAttr+'></input></td>';
			return html;
		}
	};
	
	var resultBox = {
			
			updateNextPrev: function() {
				var pageNum = $('#sref_slice0').attr('pageNum');
				if (pageNum == undefined) pageNum = 1;
				if (visibleSlice == 0) {
					$('#sref_prev_page_disabled').show();
					$('#sref_prev_page').hide();
					
				} else {
					$('#sref_prev_page_disabled').hide();
					$('#sref_prev_page').show();
				}
				if (visibleSlice == pageNum - 1) {
					$('#sref_next_page_disabled').show();
					$('#sref_next_page').hide();
				} else {
					$('#sref_next_page_disabled').hide();
					$('#sref_next_page').show();
				}
				$('#sref_page_counter').html(mw.msg('sref_page')+' '+(visibleSlice+1)+" - "+pageNum);
			},
	
			addListeners: function() {
				$('#sref_clear_query').click(function(e) { 
					$('#sref_querybox_textarea').val("");
					$('#sref_run_query').attr('disabled', true);
				});
				
				$('#sref_open_qi').click(function(e) { 
					// alert('not implemented yet'); //TODO: implement
					queryInterface.showQI();
				});
				
				// disable query textbox if it contains nothing
				var disabled = ($.trim($('#sref_querybox_textarea').val()) == '');
				$('#sref_run_query').attr('disabled', disabled);
				$('#sref_querybox_textarea').keyup(function(e) { 
					var disabled = ($.trim($('#sref_querybox_textarea').val()) == '');
					$('#sref_run_query').attr('disabled', disabled);
				});
				
				// request of running ops
				$(document).ready(function(e) {
					runningOperations.requestTable();
					setInterval(runningOperations.requestTable, 20000);
				});
				
				// (de-)select all
				$('#sref_selectall').click(function(e) { 
					var results = $('input', '#sref_resultbox');
					results.each(function(i, e) { 
						$(e).attr("checked", true);
					});
				});
				$('#sref_deselectall').click(function(e) { 
					var results = $('input', '#sref_resultbox');
					results.each(function(i, e) { 
						$(e).attr("checked", false);
					});
				});
				
				// next - prev arrows
				$('#sref_prev_page').click(function(i, e) {
					$('#sref_slice'+visibleSlice).hide();
					visibleSlice -= 1;
					$('#sref_slice'+visibleSlice).show();
					resultBox.updateNextPrev();
					
				});
				$('#sref_next_page').click(function(i, e) {
					$('#sref_slice'+visibleSlice).hide();
					visibleSlice += 1;
					$('#sref_slice'+visibleSlice).show();
					resultBox.updateNextPrev();
				});
				resultBox.updateNextPrev();
			}
	};
	
	var queryInterface = { 
			showQI : function() {
				queryInterface.openQueryInterfaceDialog(mw.config.get('wgScript') 
							+ '?action=ajax&rs=smwf_qi_getAskPage&rsargs[]=CKE%26returnObject=srfgASKListener',
							queryInterface.setNewAskQuery);
			},
			
			openQueryInterfaceDialog: function(href, onCleanup){
			    jQuery.fancybox({
			      'href' : href,
			      'width' : 977,
			      'height' : 600,
			      'padding': 10,
			      'margin' : 0,
			      'autoScale' : false,
			      'transitionIn' : 'none',
			      'transitionOut' : 'none',
			      'type' : 'iframe',
			      'overlayColor' : '#222',
			      'overlayOpacity' : '0.8',
			      'hideOnContentClick' : false,
			      'scrolling' : 'auto',
			      'onCleanup' : onCleanup
			      
			    });
			  },
			 
			  
			  /**
				 * set new query annotations
				 */
			    setNewAskQuery:function() {
			      var qiHelperObj = queryInterface.getQIHelper();
			      
			      var newQuery = qiHelperObj.getAskQueryFromGui();
			      if( typeof( qiHelperObj.querySaved) == 'undefined' ||
			        qiHelperObj.querySaved !== true ) {
			        return;
			      }
			      newQuery = newQuery.replace(/\]\]\[\[/g, "]]\n[[");
			      newQuery = newQuery.replace(/>\[\[/g, ">\n[[");
			      newQuery = newQuery.replace(/\]\]</g, "]]\n<");
			      newQuery = newQuery.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
			  	
			      $('#sref_querybox_textarea').val(newQuery);
			      delete qiHelperObj;
			    },
			    
			    getQIHelper: function(){
			        // some extensions use the YUI lib that adds an additional
					// iframe
			        if(!queryInterface.qihelper){
			          for (i=0; i<window.top.frames.length; i++) {
			            if (window.top.frames[i].qihelper) {
			            	queryInterface.qihelper = window.top.frames[i].qihelper;
			              break;
			            }
			          }
			        }

			        return queryInterface.qihelper;
			      },
			      saveQuery: function(){
			    	   var qiHelperObj = queryInterface.getQIHelper();
			    	   qiHelperObj.querySaved = true;
			    	    jQuery.fancybox.close();
			    	    delete qiHelperObj;

			    	  },

			    	  cancelQuery: function(){
			    		  var qiHelperObj = queryInterface.getQIHelper();
			    		  qiHelperObj.querySaved = false;
			    	    jQuery.fancybox.close();
			    	    delete qiHelperObj;
			    	  }
			      
			     
	};
	
	// export one object to outside to be able to communicate with the QI overlay
	window.srfgASKListener = {};
	window.srfgASKListener.saveQuery = queryInterface.saveQuery;
	window.srfgASKListener.cancelQuery = queryInterface.cancelQuery;
	
	var runningOperations = {
		showTable : function(response) {
			var table = $.parseJSON(response);
			var html = "<table width=\"100%\" class=\"smwtable\"><tr><th>"+mw.msg('sref_comment')+"</th><th>"+mw.msg('sref_starttime')+"</th><th>"
						+mw.msg('sref_endtime')+"</th><th>"+mw.msg('sref_progress')+"</th><th>"+mw.msg('sref_status')+"</th></tr>";
			$(table).each(function(i, e) { 
				html += "<tr>";
				html += "<td>";
				html += e.comment;
				html += "</td>";
				html += "<td>";
				html += e.starttime;
				html += "</td>";
				html += "<td>";
				html += e.endtime;
				html += "</td>";
				html += "<td>";
				html += (e.progress * 100) + "%";
				html += "</td>";
				html += "<td>";
				html += (e.progress == 1 ? '<span style="color:green; font-weight:bold">'+mw.msg('sref_finished')+'</span>' 
											: '<span style="color:blue; font-weight:bold">'+mw.msg('sref_running')+'</span>');
				html += "</td>";
				
			});
			html += "</table>";
			$('#sref_operations').html(html);
		},
		
		onError : function(xhr) {
			if (xhr.status == 403) {
				alert(mw.msg('sref_not_allowed_botstart'));
			} else {
				alert(xhr.responseText);
			}
		},
		
		requestTable : function(response) {
			$.ajax({
				url: mw.config.get('wgScript'),
				data: {	action : 'ajax',
						rs : 'smwf_ga_GetGardeningLogAsJSON',
						rsargs : [ 'smw_refactoringbot' ] 
					},
				success: runningOperations.showTable,
				error: runningOperations.onError
			});
			
		}
	
	
	};
			
	$('#sref_commandboxes').html(commandBox.createHTML());
	commandBox.addListeners();
	resultBox.addListeners();
	$('#sref_add_command').click(function() { 
		alert("Command");
	});
	
	$('#sref_start_operation').click(function(e) { 
		var results = $('input[checked="true"]', '#sref_resultbox');
		prefixedTitles = [];
		results.each(function(i,e) { 
			var prefixedTitle = $(e).attr("prefixedTitle");
			prefixedTitles.push(prefixedTitle);
		});
		
		var selectedOperationType = $('#sref_operation_type option:selected');
		var selectedOperation =  $('#sref_operation option:selected');
		if (selectedOperationType.length == 0 || selectedOperation.length == 0) {
			alert("Select operation"); // TODO: localize
			return;
		}
		var operationTypeIndex = selectedOperationType[0].index;
		var operationIndex = selectedOperation[0].index;
		
		var operationKey = ""+operationTypeIndex+operationIndex;
		var operation = content.operationnames[operationKey];
		
		if (operation == null) {
			alert("Internal error"); // TODO: localize
			return;
		}
		
		// read parameters from DOM
		var message = "";
		var params = {};
		$('#sref_parameters input').each(function(i, e) {
			var jqe = $(e);
			if ($.trim(jqe.val()) == '' && jqe.attr('optional') == "false") {
				message += "\n"+'Parameter ' +jqe.attr('id')+ " is mandatory."; // TODO:
			}
			params[jqe.attr('id')] = jqe.val();
		});
		if (message != '') {
			alert(message);
			return;
		}
		
		// set bot parameters
		var paramString = "SRF_OPERATION=" + operation;
		for (p in params) {
			paramString += "," + p + "=" + params[p];
		}
		
		paramString += ",titles="+prefixedTitles.join("%%");
				
		// launch Bot
		
		var onError = function(xhr) {
			if (xhr.status == 403) {
				alert(mw.msg('sref_not_allowed_botstart'));
			} else {
				alert(xhr.responseText);
			}
		}
		
		$.ajax({
			type: "POST",
			url: mw.config.get('wgScript'),
			data: {	action : 'ajax',
					rs : 'smwf_ga_LaunchGardeningBot',
					rsargs : [ 'smw_refactoringbot', paramString, null, null ] 
				},
			success: runningOperations.requestTable,
			error: onError
		});
		
		
		
	});
	
})(jQuery);	


