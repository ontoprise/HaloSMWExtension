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
 * @fileOverview R2R query environment
 * @author Christian Becker
 */
(function($){
			
	/**
	 * Provides an environment to query details about R2R mappings
	 * @param prefixesArray Array of r2r:prefixDefinition objects
	 */
	$.r2rQueryEnv = function(prefixesArray) {
		var base = this;
		
		base.init = function() {
			base.prefixStore = $.r2rUtils.basePrefixStore();
			base.addPrefixDefinitions(prefixesArray);
		};
		
		/**
		 * Parses R2R prefix definition and adds the prefixes to the internal rdf object.
		 * @param prefixesArray Array of r2r:prefixDefinition objects
		 *
		 * Example:
		 *	["smwcat: <http://mywiki/resource/category/> .
		 *	  smwprop: <http://mywiki/resource/property/> ."]
		 */
		base.addPrefixDefinitions = function(prefixesArray) {
			$(prefixesArray).each(function(key, prefixes) {
				if (prefixes === undefined) {
					return;
				}
				
				$.each($.r2rUtils.parsePrefixDefinitions(prefixes.value), function(key, value) {
					base.prefixStore.prefix(key, value);
				});
			});
		};
				
		/**
		 * Tries to output a resource in prefix notation
		 * @param resource
		 * @return string
		 */
		base.formatResource = function(resource) {
			try {
				return $.createCurie(resource.value, { namespaces: base.prefixStore.databank.namespaces });
			} catch (g) {
				return resource.value;
			}
		};
		
		/**
		 * A pattern is usually characterized by the properties that are generated for ?SUBJ
		 * @param classMapping If true and the property is rdf:type, the object is used instead (as it's more descriptive)
		 */
		base.formatPattern = function(patternArray, classMapping) {
			try {
				/* Parse statements to find the properties that ?SUBJ is addressed with */
				var query = base.prefixStore;
				var properties = [];
				$(patternArray).each(function(key, pattern) {
					var subPatterns = pattern.value.split(".");
					$(subPatterns).each(function(key,value) {
						query = query.where(value);
						if (query && query.filterExp && (query.filterExp.subject == "?SUBJ" || query.filterExp.object == "?SUBJ")) {
							if (classMapping && patternArray.length == 1 && subPatterns.length == 1
								&& base.formatResource(query.filterExp.property) == "rdf:type") {
								properties.push(base.formatResource(query.filterExp.object));
							} else {
								properties.push(base.formatResource(query.filterExp.property));
							}
						}
					});
				});
				if (properties.length) {
					return properties.join("<br/>");
				}
			} catch(err) {
			}
			/* default: return pattern with minor cleanup */
			if (patternArray.length == 1 && patternArray[0].value.split(".").length == 1) {
				try {
					var query = base.prefixStore;
					query = query.where(patternArray[0].value);
					return base.formatResource(query.filterExp.property);
				} catch (err) {
				}
			}
			var result = "";
			$(patternArray).each(function(key, pattern) {
				result += pattern.value.replace("?SUBJ a ", "") + "<br/>";
			});
			return result;
		};
		
		/**
		 * A source pattern is characterized by the properties that are generated for the variables used in target patterns
		 */
		base.formatSourcePattern = function(sourcePattern, targetPattern) {
			if (0 == sourcePattern.length) {
				return '';
			}
			try {
				/* Parse target patterns to find the variables used */
				var query = base.prefixStore;
				var variables = [];
				$(targetPattern).each(function(key, pattern) {
					$(pattern.value.split(".")).each(function(key,value) {
						query = query.where(value);
						if (query && query.filterExp) {
							$([query.filterExp.subject, query.filterExp.object]).each(function(key, value) {
								value = $.r2rUtils.cleanVariable(value);
								if (value != "?SUBJ" && -1 == $.inArray(value, variables)) {
									variables.push(value);
								}
							});
						}
					});
				});
				
				/* Parse source patterns to find the properties used with these variables */
				var query = base.prefixStore;
				var properties = [];
				$(sourcePattern).each(function(key, pattern) {
					$(pattern.value.split(".")).each(function(key,value) {
						query = query.where(value);
						if (query && query.filterExp && (-1 != $.inArray(query.filterExp.subject, variables) || -1 != $.inArray(query.filterExp.object, variables))) {
						properties.push(base.formatResource(query.filterExp.property));
						}
					});
				});
				if (properties.length) {
					return properties.join("<br/>");
				}
			} catch(err) {
//				console.log(sourcePattern);
//				console.log(err);
			}
			/* default: return pattern with minor cleanup */
			if (sourcePattern.length == 1 && sourcePattern[0].value.split(".").length == 1) {
				try {
					var query = base.prefixStore;
					query = query.where(sourcePattern[0].value);
					return base.formatResource(query.filterExp.property);
				} catch (err) {
				}
			}
			var result = "";
			$(sourcePattern).each(function(key, pattern) {
				result += pattern.value.replace("?SUBJ a ", "") + "<br/>";
			});
			return result;
		};

		base.init();
		return base;
	};
})(jQuery);