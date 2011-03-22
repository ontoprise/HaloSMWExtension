/*  Copyright 2011, MediaEvent Services GmbH & Co. KG
 *  This file is part of the LinkedData-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
(function($){
	
	$.lodMapping = function(container) {
		var base = this;
		base.container = container;

		/**
		 * Loads the editor with a specific mapping
		 * @param mappingURI
		 * @param title
		 */
		base.openEditor = function(mappingURI, title) {
			base.overviewTable.hide();
			base.newMappingButton.hide();
			base.backButton.show();
			base.editor = new $.r2rEditor(base.mainContainer, {
				title: title,
				sourceUrl: base.getAjaxUrl("getR2RMapping", [mappingURI]),
				serialize: true,
				onCommit: function(data) {
		//			$("#result").text(data);
				}
			});
		};
		
		base.closeEditor = function() {
			base.editor.remove();
			base.overviewTable.show();
			base.newMappingButton.show();
			base.backButton.hide();
		}
		
		/**
		 * Removes the specified mapping
		 * @param mappingURI
		 * @param title
		 */
		base.removeMapping = function(mappingURI, title) {
			// TODO
		};

		/**
		 * Show the overview table
		 */
		base.init = function() {
			$.r2rUI.showProgress();
			$.ajax({
				url: base.getAjaxUrl("getAllR2RMappings"),
				dataType:'json',
				success: function(data) {
					try {
						base.mainContainer = $("<div></div>").appendTo(base.container);
						base.overviewTable = $("<div>\
												<h1>All R2R Mappings</h1>\
												<div class=\"ui-tabs ui-widget ui-widget-content ui-corner-all\">\
												<table id=\"lodmapping-table\" class=\"r2redit-mappingTable\">\
												<thead class=\"ui-widget-header\">\
													<tr>\
														<th>ID</th>\
														<th>From</th>\
														<th>To</th>\
														<th>Edit</th>\
														<th>Remove</th>\
													</tr>\
													<tbody class=\"ui-widget-content\">\
													</tbody>\
												</thead>\
												</table>\
												</div>\
											</div>").appendTo(base.mainContainer);
						base.buttonPane = $("<div></div>")
											.attr("id", "lodmapping-buttonpane")
											.appendTo(base.container);

						base.newMappingButton = 
							$("<div></div>").button({
													icons: {
														primary: "ui-icon-add"
													},
													label: "New R2R Mapping",
											})
											.click(function() {
											})
											.appendTo(base.buttonPane);
											
						base.backButton = 
							$("<div></div>").button({
													icons: {
														primary: "ui-icon-back"
													},
													label: "Back to Overview",
											})
											.click(function() {
												base.closeEditor();
											})
											.appendTo(base.buttonPane)
											.hide();
											
						var overviewTableBody = base.overviewTable.find("tbody");
						$.each(data, function(key, mapping) {
							$("<tr></tr>")
								/* ID */
								.append(
									$("<td>" + mapping.id + "</td>")
									.addClass("r2redit-mappingTableProperty")
									.addClass("r2redit-mappingTableName")
								)
								/* From */
								.append(
									$("<td>" + mapping.source + "</td>")
									.addClass("r2redit-mappingTableProperty")
								)
								/* To */
								.append(
									$("<td>" + mapping.target + "</td>")
									.addClass("r2redit-mappingTableProperty")
								)
								/* Edit */
								.append(
									$("<td></td>")
										.addClass("r2redit-mappingTableAction")
										.addClass("r2redit-mappingTableClickable")
										.addClass("r2redit-mappingTableEdit")
										.click(function() {
											base.openEditor(mapping.uri, mapping.id); 
										})
								)
								/* Remove */
								.append(
									$("<td></td>")
										.addClass("r2redit-mappingTableAction")
										.addClass("r2redit-mappingTableClickable")
										.addClass("lodmappings-mappingTableRemove")
										.click(function() {
											base.removeMapping(mapping.uri, mapping.id); 
										})
								)
								.appendTo(overviewTableBody);
						});
					} catch (err) {
						$.r2rUI.showError("R2Redit error", err);
					}
		   		},
		        error: function(jqXHR, textStatus, err) {
					$.r2rUI.showError("Unable to load mapping list", err);        	
		        },
		        complete: function() {
     					$.r2rUI.hideProgress();
		        }
			});
		};
		
		base.getAjaxUrl = function(method, parameters) {
			var requestUrl = wgServer +
							((wgScript == null) ? (wgScriptPath + "/index.php") : wgScript) +
							"?action=ajax&rs=" + method;
			if (parameters) {
				$(parameters).each(function(key, value) {
					requestUrl += "&rsargs=" + escape(value);
				});
			}
			return requestUrl;
		};
	
		base.init();
		return base;
	};		

})(jQuery);