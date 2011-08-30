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
 * @fileOverview Listing of available R2R mappings
 * @author Christian Becker
 */
(function($){
	
	$.r2rExpandedClassMappings = [];
		
	/** 
	 * Represents an object in the overview table
	 * @param {Object} uri, name, source, target, onEdit(mapping, parentMapping)
	 */
	$.mappingRow = $.inherit({

		__constructor: function(container, options){
			this.container = container;
			this.options = options;
			this.init();
	        return this;
		},
	
		init: function() {
			this.el = $("<tr></tr>").addClass(this.getClass());
			this.el.appendTo(this.container);
			/* Collapse / expand */
			$("<td></td>")
				.addClass("r2redit-mappingTableAction")
				.addClass("r2redit-mappingTableClickable")
				.appendTo(this.el);
			/* Name */
			$("<td>" + this.options.name + "</td>")
				.addClass("r2redit-mappingTableProperty")
				.addClass("r2redit-mappingTableName")
				.appendTo(this.el);
			/* Source */
			$("<td>" + this.options.source + "</td>")
				.addClass("r2redit-mappingTableProperty")
				.appendTo(this.el);
			/* Target */
			$("<td>" + this.options.target + "</td>")
				.addClass("r2redit-mappingTableProperty")
				.appendTo(this.el);
			/* Edit link */
			$("<td></td>")
				.appendTo(this.el)
				.addClass("r2redit-mappingTableAction")
				.addClass("r2redit-mappingTableClickable")
				.addClass("r2redit-mappingTableEdit");
		}
	});
		
    /** 
	 * Represents a class mapping in the overview table
	 * @param {Object} uri, name, source, target, onEdit(mapping, parentMapping)
	 */
	$.classMappingRow = $.inherit(
		$.mappingRow,
		{
			getClass: function() {
				return "r2redit-mappingTableClassMapping";
			},
			init: function() {
				this.__base();
				var base = this;
				base.id = $.classMappingRow.idCtr++;
				/* Add collapse / expand handling */
				base.el.find(".r2redit-mappingTableAction:first")
					.addClass(this.isCollapsed() ? "r2redit-arrow-collapsed" : "r2redit-arrow-expanded")
					.click(function() {
						if ($(this).hasClass("r2redit-arrow-collapsed")) {
							$(this).removeClass("r2redit-arrow-collapsed").addClass("r2redit-arrow-expanded");
							base.el.siblings(".r2redit-parent" + base.getId()).show();
							$.r2rExpandedClassMappings.push(base.options.uri);
						} else {
							$(this).removeClass("r2redit-arrow-expanded").addClass("r2redit-arrow-collapsed");
							base.el.siblings(".r2redit-parent" + base.getId()).hide();
							$.r2rExpandedClassMappings = $.grep($.r2rExpandedClassMappings, function(value) { return value != base.options.uri; });
						}
					});
				/* Allow clicking on property rows */
				base.el.find(".r2redit-mappingTableProperty")
					.addClass("r2redit-mappingTableClickable")
					.click(function() {
						$(this).siblings(".r2redit-mappingTableAction:first").click();
					});
				/* Add edit handling */
				base.el.find(".r2redit-mappingTableEdit")
					.click(function() {
						base.options.onEdit(base.options.uri);
					});
			},
			getId: function() {
				return this.id;
			},
			getUri: function() {
				return this.options.uri;
			},
			isCollapsed: function() {
				return $.inArray(this.options.uri, $.r2rExpandedClassMappings) == -1;
			}
		},
		{
			idCtr: 0
		}
	); 
 
    /** 
	 * Represents a property mapping in the overview table
	 * @param {Object} uri, name, source, target, parentClassMapping
	 */
	$.propertyMappingRow = $.inherit(
		$.mappingRow,
		{
			getClass: function() {
				return "r2redit-mappingTablePropertyMapping";
			},
			init: function() {
				this.__base();
				var base = this;
				base.el
					.addClass("r2redit-parent" + base.options.parentClassMapping.getId());
				if (base.options.parentClassMapping.isCollapsed()) {
					base.el.hide();
				}
				/* Add edit handling */
				base.el.find(".r2redit-mappingTableEdit")
					.click(function() {
						base.options.onEdit(base.options.uri, base.options.parentClassMapping.getUri());
					});
			}
		}
	);
	
	
	/** 
	 * Represents an action row in the overview table
	 * @param {Object}
	 */
	$.actionRow = $.inherit({

		__constructor: function(container, options){
			this.container = container;
			this.options = options;
			this.init();
	        return this;
		},
	
		init: function() {
			var base = this;
			this.el = $("<tr></tr>").addClass(this.getClass());
			this.el.appendTo(this.container);
			/* Collapse / expand */
			$("<td></td>")
				.addClass("r2redit-mappingTableAction")
				.appendTo(this.el);
			/* Name / Source / Target */
			$("<td colspan=\"4\"></td>")
				.addClass("r2redit-mappingTableProperty")
				.appendTo(this.el)
				.append($("<div></div>")
					.button({
			            icons: {
			                primary: "ui-icon-add"
			            },
			            label: this.getLabel(),
					})
					.click(this.getClickHandler())
				);
				
		}
	});	
	
    /** 
	 * Represents an action row to add a new class mapping
	 * @param {Object} onEdit(mapping, parentMapping)
	 */
	$.addClassMappingRow = $.inherit(
		$.actionRow,
		{
			init: function() {
				this.__base();
			},
			getClass: function() {
				return "r2redit-mappingTableAddClassMapping";
			},
			getLabel: function() {
				return "New Class Mapping";
			},
			getClickHandler: function() {
				var base = this;
				return function() {
					base.options.onEdit(null);
				};
			}
		}
	);

    /** 
	 * Represents an action row to add a new property mapping
	 * @param {Object} parentClassMapping, onEdit(mapping, parentMapping)
	 */
	$.addPropertyMappingRow = $.inherit(
		$.actionRow,
		{
			init: function() {
				this.__base();
				this.el
					.addClass("r2redit-parent" + this.options.parentClassMapping.getId())
					.addClass("r2redit-mappingTablePropertyMapping");
				if (this.options.parentClassMapping.isCollapsed()) {
					this.el.hide();
				}
			},
			getClass: function() {
				return "r2redit-mappingTableAddPropertyMapping";
			},
			getLabel: function() {
				return "New Property Mapping";
			},
			getClickHandler: function() {
				var base = this;
				return function() {
					base.options.onEdit(null, base.options.parentClassMapping.getUri());
				};
			}
		}
	);
	
	/**
	 * Generates an overview table for a given mapping from source
	 * @param container	jQuery element to host the table / editor
	 * @param options
	 *		title Editor title to use
	 *		basePath	Base path to R2Redit
	 * @param rdfStore rdfQuery object containing the mappings
	 * @param onCommit	function(rdfStore)
	 */
	$.r2rEditorMappingTable = function(container, options, rdfStore, onCommit) {
		var base = this;
		base.container = container;
		base.title = options.title;
		base.basePath = options.basePath;
		base.rdfStore = rdfStore;
		base.onCommit = onCommit;
		
		/**
		 * Initialization
		 */
		base.init = function() {
			base.mappingTable = $("<div>\
										<h1>" + base.title + "</h1>\
										<div class=\"ui-tabs ui-widget ui-widget-content ui-corner-all\">\
										<table id=\"mappings\" class=\"r2redit-mappingTable\">\
										<thead class=\"ui-widget-header\">\
											<tr>\
												<th class=\"r2redit-mappingTableAction\"></th>\
												<th class=\"r2redit-mappingTableProperty r2redit-mappingTableName\">Name</th>\
												<th class=\"r2redit-mappingTableProperty\">Source</th>\
												<th class=\"r2redit-mappingTableProperty\">Target</th>\
												<th class=\"r2redit-mappingTableAction\">Edit</th>\
											</tr>\
											<tbody class=\"ui-widget-content\">\
											</tbody>\
										</thead>\
										</table>\
										</div>\
									</div>").appendTo(container);
			base.mappingTableBody = base.mappingTable.find("tbody");
			base.rebuild();
		};
		
		/**
		 * Build table based on store contents
		 */
		base.rebuild = function() {
			base.mappingTableBody.empty();
			/* Add class mappings to the table */
			var classMappingResults = {};
			var classMappingKeys = [];
			base.rdfStore
				.where("?c a r2r:ClassMapping")
				.where("?c r2r:sourcePattern ?sourcePattern") /* "Each mapping must have exactly one source pattern" */
				.each(function () {
					var key = $.r2rUtils.formatResource(this.c, base.rdfStore.databank.namespaces);
					classMappingResults[key] = this;
					classMappingKeys.push(key);
				});
				
			$(classMappingKeys).sort().each(function(index,key) {
					var result = classMappingResults[key];
					var prefixDefinitions = $.r2rUtils.findObjects(base.rdfStore, result.c, "r2r:prefixDefinitions");
					var env = $.r2rQueryEnv(prefixDefinitions);
					var targetPatterns = $.r2rUtils.findObjects(base.rdfStore, result.c, "r2r:targetPattern");
					
					var classMapping = new $.classMappingRow(base.mappingTableBody, {
						uri: result.c,
						name: $.r2rUtils.formatResource(result.c, base.rdfStore.databank.namespaces),
						source: env.formatPattern([result.sourcePattern], true),
						target: env.formatPattern(targetPatterns, true),
						onEdit: base.edit
					});
					
					/* Add related property mappings to the table */
					var propertyMappingResults = {};
					var propertyMappingKeys = [];
					base.rdfStore
						.where("?p a r2r:PropertyMapping")
						.where("?p r2r:mappingRef " + result.c)
						.where("?p r2r:sourcePattern ?sourcePattern") /* "Each mapping must have exactly one source pattern" */
						.each(function () {
							var key = $.r2rUtils.formatResource(this.p, base.rdfStore.databank.namespaces);
							propertyMappingResults[key] = this;
							propertyMappingKeys.push(key);
						});
						
					$(propertyMappingKeys).sort().each(function(index,key) {
							var result = propertyMappingResults[key];
							var propertyEnv = env;
							var prefixDefinitions = $.r2rUtils.findObjects(base.rdfStore, result.p, "r2r:prefixDefinitions");
							if (prefixDefinitions.length > 0) {
								propertyEnv = $.r2rQueryEnv(env.databank.namespaces);
								propertyEnv.addPrefixDefinitions(prefixDefinitions);
							}
							var targetPatterns = $.r2rUtils.findObjects(base.rdfStore, result.p, "r2r:targetPattern");
							var row = new $.propertyMappingRow(base.mappingTableBody, {
								uri: result.p,
								name: $.r2rUtils.formatResource(result.p, base.rdfStore.databank.namespaces),
								source: propertyEnv.formatSourcePattern([result.sourcePattern], targetPatterns),
								target: propertyEnv.formatPattern(targetPatterns),
								parentClassMapping: classMapping,
								onEdit: base.edit
							});
						});

					new $.addPropertyMappingRow(base.mappingTableBody, {
						parentClassMapping: classMapping,
						onEdit: base.edit
					});
				});

			new $.addClassMappingRow(base.mappingTableBody, {
				onEdit: base.edit
			});
		};
		
		/**
		 * Edit callback
		 * @param mapping The URI of the mapping to edit, or null to create a new mapping
		 * @param parentMapping When creating a new property mapping, specifies the parent class mapping
		 */
		base.edit = function(mapping, parentMapping) {
			base.mappingTable.hide();
			base.editor = $.r2rEditorMappingEditor(base.container, base.rdfStore, mapping, parentMapping, base.basePath, base.onEditComplete);
		};
		
		/**
		 * Edit completion
		 * @param mapping The URI of the mapping that was edited
		 * @param originalMapping The original URI of the mapping that was edited - this will differ from mapping if the user renamed it
		 * @param rdfRepresentation An rdfStore containing the RDF representation of the mapping
		 * @param action One of "save", "remove", "cancel"
		 */
		base.onEditComplete = function(mapping, originalMapping, rdfRepresentation, action) {
			if (action != "cancel") {
				/*
				 * Remove all old data
				 */
				if (originalMapping != null) {
					base.rdfStore
						.where(originalMapping + " ?p ?o")
						.remove(originalMapping + " ?p ?o");

					if (action == "remove") {
						/*
						 * When removing a class mapping, remove its property mappings
						 */
						base.rdfStore
							.where("?p a r2r:PropertyMapping")
							.where("?p r2r:mappingRef " + originalMapping)
							.each(function () {
								base.rdfStore
									.where(this.p + " ?p ?o")
									.remove(this.p + " ?p ?o");
							});
					}
				}
				 
				if (action == "save") {
					/*
					 * Add new data
					 */
					// Works, but creates union with distinct namespace definitions:
					// base.rdfStore = base.rdfStore.add(rdfRepresentation);
					$(rdfRepresentation.databank.tripleStore).each(function(key, value) {
						base.rdfStore.add(value);
					});
					
					if (originalMapping != null && originalMapping.toString() != mapping.toString()) {
						/*
						 * When renaming a class mapping, rename all mappingRefs from property mappings
						 */
						base.rdfStore
							.where("?p a r2r:PropertyMapping")
							.where("?p r2r:mappingRef " + originalMapping)
							.add("?p r2r:mappingRef " + mapping)
							.remove("?p r2r:mappingRef " + originalMapping);
						/* Bonus: Also copy the collapse state! */
						var collapsePos = $.inArray(originalMapping, $.r2rExpandedClassMappings);
						if (collapsePos != -1) {
							$.r2rExpandedClassMappings[collapsePos] = mapping;
						}
					}
				}
				if (base.onCommit) {
					base.onCommit(base.rdfStore);
				}
			}
			base.rebuild();
			base.mappingTable.show();			
		};
		
		base.remove = function() {
			if (base.mappingTable) {
				base.mappingTable.remove();
			}
			if (base.editor) {
				base.editor.remove();
			}
		};
				
        base.init();
        return base;
	};
})(jQuery);