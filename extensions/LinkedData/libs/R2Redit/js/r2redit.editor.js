/*
 *  Copyright (c) 2011, MediaEvent Services GmbH & Co. KG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
/**
 * @fileOverview R2R edit capabilities
 * @author Christian Becker
 */
(function($){
	var treeViewTypes = [];
	var functionReference = null;
	var allFunctions = [];
	
	/** 
	 * Represents an R2R tree object
	 * @param container The treeview
	 * @param obj rdfQuery object to represent
	 */
	$.r2rTreeViewObject = $.inherit({

		__constructor: function(obj){
			this.obj = obj;
			this.init();
	        return this;
		},
	
		init: function() {
			var base = this;
			this.el = $("<li></li>")
						.data("r2rObject", this);
			if (this.isVisible()) {
				$("<span></span>")
					.addClass(this.getClass())
					.addClass("r2redit-mappingTableClickable")
					.attr("title", this.getTooltip())
					.html(this.getLabel())
					.click(function() {
						base.getEditor().show();
					})
					.appendTo(this.el);
				$("<span></span>")
					.addClass("ui-icon-minus-small-circle")
					.addClass("r2redit-mappingTableClickable")
					.click(function() {
						base.remove();
					})
					.appendTo(this.el);
			} else {
				this.el.hide();
			}
		},
		addToTreeView: function(treeview) {
			treeview
				.append(this.el)
				.treeview({add: this.el});
			this.treeview = treeview;
		},
		removeFromTreeView: function() {
			if (this.treeview) {
				this.treeview.treeview({remove: this.el});
			}
		},
		remove: function() {
			this.removeFromTreeView();
			this.el.remove();
		},
		refresh: function() {
			if (!this.isVisible()) {
				return;
			}
			this.el.find("span:first").html(this.getLabel());
		},
		getClass: function() {
		},
		getUnderlyingObject: function() {
			return this.obj;
		},
		setUnderlyingObject: function(obj) {
			this.obj = obj;
		},
		getLabel: function() {
		},
		getTooltip: function() {
		},
		/**
		 * Treeview objects are usually visible, but we might want to include some information
		 * that is not directly modifiable, such as the mapping type
		 */
		isVisible: function() {
			return true;
		},
		/**
		 * Helper method to allow us to invoke the static method getProperty()
		 * on instances
		 */
		getProperty: function() {
			return this.__self.getProperty();
		}
	}, {
		getProperty: function() {
		}
	});
	
    /** 
	 * Represents an rdf:type definition (invisible)
	 */
	$.r2rTreeViewType = $.inherit(
		$.r2rTreeViewObject,
		{
			isVisible: function() {
				return false;
			}
		}, {
			getProperty: function() {
				return "rdf:type";
			}
		}
	);		
	treeViewTypes.push($.r2rTreeViewType);
	
    /** 
	 * Represents an r2r:mappingRef definition (invisible)
	 */
	$.r2rTreeViewMappingRef = $.inherit(
		$.r2rTreeViewObject,
		{
			isVisible: function() {
				return false;
			}
		}, {
			getProperty: function() {
				return "r2r:mappingRef";
			}
		}
	);		
	treeViewTypes.push($.r2rTreeViewMappingRef);
	
	/** 
	 * Represents a prefix definitions element in the treeview
	 */
	$.r2rTreeViewPrefixDefinitions = $.inherit(
		$.r2rTreeViewObject,
		{
			getClass: function() {
				return "ui-icon-colon";
			},
			getTooltip: function() {
				return "Prefix Definitions";
			},
			getLabel: function() {
				if (this.obj === undefined) {
					return "(error)";
				}
				
				var prefixes = $.r2rUtils.parsePrefixDefinitions(this.getUnderlyingObject().value);
				var label = "";
				$.each(prefixes, function(key, value) {
					label += (label != "" ? ", " : "") + key;
				});
				
				return label;
			},
			getEditor: function() {
				return new $.r2rPrefixEditor(this);
			}
		}, {
			getProperty: function() {
				return "r2r:prefixDefinitions";
			}
		}
	);
	treeViewTypes.push($.r2rTreeViewPrefixDefinitions);

    /** 
	 * Represents a source pattern in the treeview
	 */
	$.r2rTreeViewSourcePattern = $.inherit(
		$.r2rTreeViewObject,
		{
			getClass: function() {
				return "ui-icon-block-arrow-in";
			},
			getTooltip: function() {
				return "Source Pattern";
			},
			getLabel: function() {
				return (this.obj !== undefined ? this.obj.value : '(error)');
			},
			getEditor: function() {
				return new $.r2rSourcePatternEditor(this);
			}
		}, {
			getProperty: function() {
				return "r2r:sourcePattern";
			}
		}
	);	
	treeViewTypes.push($.r2rTreeViewSourcePattern);
		
    /** 
	 * Represents a target pattern in the treeview
	 */
	$.r2rTreeViewTargetPattern = $.inherit(
		$.r2rTreeViewObject,
		{
			getClass: function() {
				return "ui-icon-block-arrow";
			},
			getTooltip: function() {
				return "Target Pattern";
			},
			getLabel: function() {
				return (this.obj !== undefined ? this.obj.value : '(error)');
			},
			getEditor: function() {
				return new $.r2rTargetPatternEditor(this);
			}
		}, {
			getProperty: function() {
				return "r2r:targetPattern";
			}
		}
	);	
	treeViewTypes.push($.r2rTreeViewTargetPattern);

    /** 
	 * Represents a transformation in the treeview
	 */
	$.r2rTreeViewTransformation = $.inherit(
		$.r2rTreeViewObject,
		{
			getClass: function() {
				return "ui-icon-arrow-transition";
			},
			getTooltip: function() {
				return "Transformation";
			},
			getLabel: function() {
				return (this.obj !== undefined ? this.obj.value : '(error)');
			},
			getEditor: function() {
				return new $.r2rTransformationEditor(this);
			}
		}, {
			getProperty: function() {
				return "r2r:transformation";
			}
		}
	);
	treeViewTypes.push($.r2rTreeViewTransformation);
	
	/**
	 * Generates editor chrome for a given single mapping
	 * @param container jQuery element to host the table / editor
	 * @param rdfStore An rdfQuery store containing the mapping definitions
	 * @param mapping The URI of the mapping to edit, or null to create a new mapping
	 * @param parentMapping When creating a new property mapping, specifies the parent class mapping
	 * @param basePath	Base path to R2Redit
	 * @param onComplete Callback to invoke when editing has finished, adhering to the following interface:
	 * 	function(mapping, originalMapping, rdfRepresentation)
	 *		@param mapping The URI of the mapping that was edited
	 *		@param originalMapping The original URI of the mapping that was edited - this will differ from mapping if the user renamed it
	 *		@param rdfRepresentation An rdfStore containing the RDF representation of the mapping. If the item was removed, the value is null.
	 * 		@param action One of "save", "remove", "cancel"
	 */
	$.r2rEditorMappingEditor = function(container, rdfStore, mapping, parentMapping, basePath, onComplete) {
		var base = this;
		base.container = container;
		base.rdfStore = rdfStore;
		base.rdfRepresentation = $.rdf();
		base.mapping = base.originalMapping = mapping;
		base.parentMapping = parentMapping;
		base.basePath = basePath;
		base.onComplete = onComplete;
		
		base.init = function() {
			base.initUI();
			base.importData();
			base.loadReference();
		};
		
		base.loadReference = function() {
			if (functionReference) {
				return;
			}
			/*
			 * Just do this in the background without interrupting the user
			 * - the reference is easily loaded by the time he
			 * could reach the transformations dialog
			 */
			$.ajax({
				url: base.basePath + "json/transformations.json",
				dataType:'json',
				success: function(data) {
					functionReference = data;
		   		},
		        error: function(jqXHR, textStatus, err) {
					$.r2rUI.showError("Unable to function reference", err);        	
		        }
			});
		}
		
		base.initUI = function() {
			/* Main chrome */
			base.editor = $("<div>\
							<h1>" + (mapping ? "Edit " :"New ") + (parentMapping ? "Property Mapping" : "Class Mapping") + "</h1>\
							</div>")
							.addClass("r2redit-editor")
							.appendTo(container);
			/* Tabs */
			base.tabs = $("<div>\
				<ul>\
					<li><a href=\"#tree\">Mapping Tree</a></li>\
					<li><a href=\"#sourceCode\">Mapping Source</a></li>\
				</ul>\
			</div>")
			.appendTo(base.editor);
			
			/* Tree view */
			base.treeTab = $("<div id=\"tree\">\
					<div id=\"r2redit-controlbar\" class=\"ui-widget-content ui-corner-all\">\
					</div>\
					<input type=\"text\" id=\"r2redit-mappingName\" class=\"r2redit-hoverInput\" title=\"Please enter a full or prefixed URI\">\
				</div>")
				.appendTo(base.tabs);
				
			$("#r2redit-mappingName").change(function() {
				try {
					base.mapping = $.rdf.resource($("#r2redit-mappingName").val(), { namespaces: base.rdfStore.databank.namespaces });
				}
				catch(err) {
				}
			}).focus(function() {
				this.select();				
			});
				
			/* Controls */
			$.each({
				"Prefix Definitions": {
					icon: "ui-icon-colon",
					objectClass: $.r2rTreeViewPrefixDefinitions
				},
				"Source Pattern": {
					icon: "ui-icon-block-arrow-in",
					objectClass: $.r2rTreeViewSourcePattern
				},
				"Target Pattern": {
					icon: "ui-icon-block-arrow",
					objectClass: $.r2rTreeViewTargetPattern
				},
				"Transformation": {
					icon: "ui-icon-arrow-transition",
					objectClass: $.r2rTreeViewTransformation
				}
			}, function(key, options) {	
				base.treeTab.find("#r2redit-controlbar").append($("<div></div>")
						.button({
				            icons: {
				                primary: options.icon
				            },
				            label: key,
						})
						.click(function() {
							var f = new options.objectClass($.r2rUtils.createStringLiteral(""));
							f.getEditor().show(function() {
								f.addToTreeView(base.treeview);
							});
						})
					);
			});
			
			/* Treeview */
			base.treeview = $("<ul></ul>")
				.appendTo(base.treeTab)
				.treeview();
							
			base.editor.append(
				$("<div></div>")
				.attr("id", "r2redit-bottomPane")
				.addClass("ui-widget")
				.addClass("ui-widget-content")
				.addClass("ui-corner-bottom")
				.append($("<div></div>")
					.button({
			            label: "Save",
					})
					.click(function() {
						base.close("save");
					})
				)
				.append($("<div></div>")
					.button({
			            label: "Remove",
					})
					.click(function() {
						var dialogOpened = false;
						var dialog = $("<div class=\"r2redit-dialog\" title=\"Remove Mapping\">\
										<p>\
										<span class=\"ui-icon ui-icon-alert\"></span>\
										Are you sure?" + (base.parentMapping ? "" : " This will also remove all related property mappings.") + "\
										</p>\
										</div>");
						dialog.dialog({
							autoOpen: true,
							height: 150,
							width: 300,
							modal: true,
							buttons: {
								"Remove": function() {
									/**
									 * Workaround: This gets called once on initialization... seems to be a jQuery UI bug
									 */
									if (!dialogOpened) {
										return;
									}
									$(this).dialog("close");
									base.close("remove");
								},
								"Cancel": function() {
									/**
									 * Workaround: This gets called once on initialization... seems to be a jQuery UI bug
									 */
									if (!dialogOpened) {
										return;
									}
									$(this).dialog("close");
								}
							}
						});
						dialogOpened = true;
						$.r2rUI.fixJQueryUIDialogButtons(dialog);
					})
				)
				.append($("<div></div>")
					.button({
			            label: "Cancel",
					})
					.click(function() {
						base.close("cancel");
					})
				)
			);

			/* Source code view */
			base.soureCodeTab = $("<pre id=\"sourceCode\"></pre>")
				.appendTo(base.tabs);
			base.tabs.tabs({
				selected: 0,
				show: function(event, ui) {
					if (ui.index == 1) {
						base.generateRdfRepresentation();
						$("#sourceCode").text(base.rdfRepresentation.databank.dump({format:'text/turtle', serialize: true, indent: true}));									}
				}
			}).removeClass("ui-corner-all");
			/* Init qTips */
			base.editor.find("[title]").qtip({
				position: {
					corner: {
						target: "rightMiddle",
						tooltip: "leftMiddle"
					}
				},
				style: {
					background: '#feff9d',
					border: {
						width: 1,
						radius: 3,
						color: '#feff9d'
					},
					padding: 3, 
					textAlign: 'left',
					fontSize: '12px',
					tip: true, // Give it a speech bubble tip with automatic corner detection
					name: 'cream' // Style it according to the preset 'cream' style
				}
			});
		}
		
		base.importData = function() {
			if (base.mapping === null) {
				/* new mapping */
				var basePrefixStore = $.r2rUtils.basePrefixStore();				
				if (base.parentMapping) {
					new $.r2rTreeViewType($.rdf.resource("r2r:PropertyMapping", { namespaces: basePrefixStore.databank.namespaces })).addToTreeView(base.treeview);
					new $.r2rTreeViewMappingRef(base.parentMapping).addToTreeView(base.treeview);
					/* Add mandatory source pattern */
					new $.r2rTreeViewSourcePattern($.r2rUtils.createStringLiteral("")).addToTreeView(base.treeview);					
				} else {
					new $.r2rTreeViewType($.rdf.resource("r2r:ClassMapping", { namespaces: basePrefixStore.databank.namespaces })).addToTreeView(base.treeview);
					/* Add mandatory source pattern */
					new $.r2rTreeViewSourcePattern($.r2rUtils.createStringLiteral("")).addToTreeView(base.treeview);					
				}
				base.editor.find("#r2redit-mappingName").val("(please provide a name)");
				return;
			}
			
			/* Mapping URI */
			base.editor.find("#r2redit-mappingName").val($.r2rUtils.formatResource(base.mapping, base.rdfStore.databank.namespaces));
			
			/* Parse data */
			$(treeViewTypes).each(function(key, obj) {
				var objects = $.r2rUtils.findObjects(base.rdfStore, base.mapping, obj.getProperty());
				$(objects).each(function(key, value) {
					new obj(value).addToTreeView(base.treeview);
				});
			});
		},
		
		/**
		 * Popuplates rdfRepresentation object based on mappingObjects
		 */
		base.generateRdfRepresentation = function() {
			base.rdfRepresentation = $.rdf({namespaces: base.rdfStore.databank.namespaces});
			base.treeview.find("li").each(function(key, obj) {
				var mappingObject = $(obj).data("r2rObject");
				base.rdfRepresentation.add(
					$.rdf.triple(
						base.mapping,
						$.rdf.resource(mappingObject.getProperty(), { namespaces: base.rdfRepresentation.databank.namespaces }),
						mappingObject.getUnderlyingObject()
					)
				);
			});
		};
		
		base.close = function(action) {
			if (base.onComplete && action=="save") {
				base.generateRdfRepresentation();
			}
			base.treeview.find("li").remove();
			base.editor.remove();
			if (base.onComplete) {
				base.onComplete(base.mapping, base.originalMapping, base.rdfRepresentation, action);
			}
		};
		
		base.remove = function() {
			if (base.editor) {
				base.editor.remove();
			}
		}

        base.init();
        return base;
	};
	
	/** 
	 * Base value editor class
	 */
	$.r2rValueEditor = $.inherit({

		__constructor: function(obj){
			this.obj = obj;
			this.init();
	        return this;
		},
	
		init: function() {
		},
		
		getObject: function() {
			return this.obj;
		},
		
		show: function(onSave) {
			var base = this;
			this.form = $("<form></form>");
			this.fieldSet = $("<fieldset></fieldset>").appendTo(this.form);
			this.dialog = $("<div></div>")
				.addClass("r2redit-dialog")
				.attr("title", this.obj.getTooltip())
				.append(this.fieldSet);
			this.dialogOptions = {
				autoOpen: true,
				width: 350,
				height: 300,
				modal: true,
				buttons: {
					Save: function() {
						/**
						 * Workaround: This gets called once on initialization... seems to be a jQuery UI bug
						 */
						if (!dialogOpened) {
							return;
						}
						base.save();
						$(this).dialog("close");
						if (onSave) {
							onSave();
						}
					},
					"Cancel": function() {
						/**
						 * Workaround: This gets called once on initialization... seems to be a jQuery UI bug
						 */
						if (!dialogOpened) {
							return;
						}
						$(this).dialog("close");
					}
				},
				close: function() {
				}
			};
			this.initUI();
			var dialogOpened = false;
			this.dialog.dialog(this.dialogOptions);
			var dialogOpened = true;
			$.r2rUI.fixJQueryUIDialogButtons(this.dialog);
		},
		
		/**
		 * Override to add fields to edit form
		 */
		initUI: function() {
		},
		save: function() {
		}
	});
	
    /** 
	 * Prefix Definitions editor
	 */
	$.r2rPrefixEditor = $.inherit(
		$.r2rValueEditor,
		{
			initUI: function() {
				var base = this;
				this.__base();
				$.extend(this.dialogOptions, {
					width: 510,
					height: 200,
					dialogClass: "r2redit-editor-prefix-dialog"
				});
				$.each($.r2rUtils.parsePrefixDefinitions(this.obj.getUnderlyingObject().value), function(prefix, uri) {
					base.addLine(prefix, uri);
				});
				base.addLine();
			},
			save: function() {
				var prefixes = {};
				this.fieldSet.find(".r2redit-editor-prefix-dialog-line").each(function(key, line) {
					line = $(line);
					var prefix = line.find(".r2redit-editor-prefix-dialog-prefix").val();
					if (prefix != "(new)") {
						prefixes[prefix] = line.find(".r2redit-editor-prefix-dialog-uri").val();
					}
				});
				this.obj.setUnderlyingObject($.r2rUtils.createStringLiteral($.r2rUtils.constructPrefixDefinitions(prefixes)));
				this.obj.refresh();
			},
			addLine: function(prefix, uri) {
				var base = this;
				if (prefix == null) {
					prefix = "(new)";
					uri = "";
				}
				var line = $("<div class=\"r2redit-editor-prefix-dialog-line\">\
								<input type=\"text\" class=\"r2redit-editor-prefix-dialog-prefix r2redit-hoverInput\" value=\"" + prefix + "\"/>\
								<input type=\"text\" class=\"r2redit-editor-prefix-dialog-uri r2redit-input\" value=\"" + uri + "\"/>\
								<div class=\"r2redit-editor-prefix-dialog-remove ui-icon-minus-small-circle\"></div>\
								</div>")
					.appendTo(this.fieldSet);
				line.find(".r2redit-editor-prefix-dialog-prefix").focus(function() {
					if ($(this).val() == "(new)") {
						$(this)
							.select()
							.data("new", true);
					}
				}).change(function() {
					if ($(this).data("new")) {
						base.addLine();
						$(this).data("new", false);
					}
				});
				line.find(".ui-icon-minus-small-circle").click(function() {
					if ($(this).siblings(".r2redit-editor-prefix-dialog-prefix").val() != "(new)") {
						line.remove();
					}
				});
			},
			removeLine: function() {
			},
		}
	);
	
	/** 
	 * Basic string editor
	 */
	$.r2rStringEditor = $.inherit(
		$.r2rValueEditor,
		{
			initUI: function() {
				var value = (this.obj.getUnderlyingObject().value != "" ? this.obj.getUnderlyingObject().value : this.getDefaultValue());
				this.valueField = $("<textarea></textarea>").val(value)
					.appendTo(this.fieldSet);
				var description = this.getDescription();
				if (description) {
					$("<div class=\"r2redit-editor-description ui-widget-header ui-corner-all\">" + description + "</div>").appendTo(this.dialog);
				}
			},
			save: function() {
				this.obj.setUnderlyingObject($.r2rUtils.createStringLiteral(this.valueField.val()));
				this.obj.refresh();
			},
			getDescription: function() {
			},
			getDefaultValue: function() {
				return "";
			}
		}
	);
	
    /** 
	 * Source pattern editor
	 */
	$.r2rSourcePatternEditor = $.inherit(
		$.r2rStringEditor,
		{
			initUI: function() {
				$.extend(this.dialogOptions, {
					width: 500,
					height: 365,
				});
				
				this.__base();
			},
			getDescription: function() {
				return "<a class=\"r2redit-editor-helplink\" href=\"http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/#sourcepattern\" target=\"_blank\"></a>A Source Pattern expresses the structure of the source vocabulary terms.<br/>All of the SPARQL syntax that is valid in a WHERE-clause is allowed here, with the restriciton that properties must be explicit URIs. Also, in order to make it unambiguous which variable in the source pattern corresponds to the mapped resources, the variable ?SUBJ has to be used.";
			},
			getDefaultValue: function() {
				return "?SUBJ rdf:type ns:Class";
			}
		}
	);
	
    /** 
	 * Traget pattern editor
	 */
	$.r2rTargetPatternEditor = $.inherit(
		$.r2rStringEditor,
		{
			initUI: function() {
				$.extend(this.dialogOptions, {
					width: 500,
					height: 320,
				});
				
				this.__base();
			},
			getDescription: function() {
				return "<a class=\"r2redit-editor-helplink\" href=\"http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/#targetpattern\" target=\"_blank\"></a>A target pattern contains target triples that are constructed by using constants, variables of the source pattern or of tranformation patterns.";
			},
			getDefaultValue: function() {
				return "?SUBJ ns:prop ?var";
			}
		}
	);

    /** 
	 * Transformation editor
	 */
	$.r2rTransformationEditor = $.inherit(
		$.r2rValueEditor,
		{
			initUI: function() {
				var valueField = this.valueField = $("<textarea></textarea>").val(this.obj.getUnderlyingObject().value)
									.appendTo(this.fieldSet);
				var reference = $("<div class=\"r2redit-editor-transformation-reference\">\
									<div id=\"r2redit-editor-transformation-search\" class=\"ui-widget ui-widget-content ui-corner-top\">\
										<input type=\"text\" id=\"r2redit-editor-transformation-searchfield\" placeholder=\"Search\"/>\
									</div>\
									<select size=\"10\" id=\"r2redit-editor-transformation-functions\">\
									</select>\
									<div id=\"r2redit-editor-transformation-description\" class=\"ui-widget-header ui-corner-bottom\"></div>\
									</div>").appendTo(this.dialog);
				/* Function list */
				var functionList = reference.find("#r2redit-editor-transformation-functions");
				$.each(functionReference, function(group, functions) {
					var optGroup = $("<optgroup></optgroup")
									.attr("label", group)
									.appendTo(functionList);
					$.each(functions, function(functionName, options) {
						allFunctions[functionName] = options;
						$.each($.isArray(options.arguments) ? options.arguments : [ options.arguments ], function(index, arguments) {
							var syntax = (functionName == "_length" ? "length" : functionName) + "(" + arguments + ")";
							$("<option>" + syntax + "</option>")
								.attr("value", functionName)
								.dblclick(function() {
									valueField.insertAtCaret(syntax);
								})
								.appendTo(optGroup);
						});
					});
				});
				functionList
					.scrollTop(0)
					.change(function() {
						var description = reference.find("#r2redit-editor-transformation-description");
						var f = allFunctions[functionList.val()];
						if (f) {
							var usage = "";
							var functionName = (functionList.val() == "_length" ? "length" : functionList.val());
							$.each($.isArray(f.arguments) ? f.arguments : [ f.arguments ], function(index, arguments) {
								usage += "<b>" + functionName + "</b>(" + f.arguments + ")<br/>";
							});
							usage += "<br/>" + f.description;
							if (f.note) {
								usage += "<br/><em>Note: " + f.note + "</em>";
							}
							description.html(usage);
						} else {
							description.html("");
						}
					});

				/* Search */
				var searchField = reference.find('#r2redit-editor-transformation-searchfield');
				searchField
					.placeholder()
					.keyup(function() {
						var regexp = new RegExp(searchField.val(), "i");
						functionList.find("option").each(function(key, option) {
							var option = $(option);
							if (option.attr("value").search(regexp) != -1 || allFunctions[option.attr("value")].description.search(regexp) != -1) {
								option.show();
							} else {
								option.hide();
							}
						});
					});

				$.extend(this.dialogOptions, {
					width: 800,
					height: 340,
					dialogClass: "r2redit-editor-transformation-dialog"
				});
			},
			save: function() {
				this.obj.setUnderlyingObject($.r2rUtils.createStringLiteral(this.valueField.val()));
				this.obj.refresh();
			}
		}
	);	

})(jQuery);