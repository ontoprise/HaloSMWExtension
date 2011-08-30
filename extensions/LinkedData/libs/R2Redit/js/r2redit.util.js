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
 * @fileOverview Generic R2R utility methods
 * @author Christian Becker
 */
(function($){
			
	/**
	 * Generic R2R utility methods
	 */
	$.r2rUtils = {
		/**
		 * In R2R, mappings are executed with these prefixes are already set
		 * These are these PrefixMapping.Standard prefixes plus R2R
		 */
		builtInPrefixes: {
			rdfs: "http://www.w3.org/2000/01/rdf-schema#",
			rdf: "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
			dc: "http://purl.org/dc/terms/",
			daml: "http://www.daml.org/2001/03/daml+oil#",
			owl: "http://www.w3.org/2002/07/owl#",
			xsd: "http://www.w3.org/2001/XMLSchema#",
			r2r: "http://www4.wiwiss.fu-berlin.de/bizer/r2r/"
		},
		/**
		 * Tries to output a resource in prefix notation
		 * @param resource
		 * @return string
		 */
		formatResource: function(resource, namespaces) {
			try {
				return $.createCurie(resource.value, { namespaces: namespaces });
			} catch (g) {
				return resource.value;
			}
		},
		formatPattern: function(pattern, namespaces) {
			return (pattern === undefined ? '' : pattern.value).replace("?SUBJ a ", "").replace("?SUBJ ", "");
		},
		/**
		 * Apply built-in prefixes so that mappings depending on them can be parsed correctly.
	     * If a prefix is already defined as something else, rdfQuery will expand the
	     * respective URIs to their correct value before replacing the prefix.
	     */
		initPrefixes: function(rdf) {			
			$.each($.r2rUtils.builtInPrefixes, function(prefix, namespace) {
				rdf.prefix(prefix, namespace);
			});
		},
		basePrefixStore: function() {
			var store = $.rdf();
			$.r2rUtils.initPrefixes(store);
			return store;
		},
		/**
		 * Removes type coercion syntax to simplify working with a variable
		 *
		 * Sample input:
		 *	?'id'^^xsd:int
		 * Sample output:
		 * 	?id
		 */
		cleanVariable: function(variable) {
			var matches = variable.match(/\?'([^']+)'/);
			return (matches ?  "?" + matches[1] : variable);
		},
		/** 
		 * Find all objects for a given subject and property
		 */
		findObjects: function(rdf, subject, property) {
			var results = [];
			rdf.where(subject + " " + property + " ?o").each(function(){
				if (this.o !== undefined) {
					results.push(this.o);
				}
			});
			return results;
		},
		/**
		 * Loads an RDF/XML or TTL document into a rdfQuery object initialized with the built-in r2r prefixes
		 * @param rdfSource RDF/XML source code
		 * @return rdfQuery object
		 */
		loadRDF: function(rdfSource) {
			var rdf = $.rdf().load(rdfSource, {});
			$.r2rUtils.initPrefixes(rdf);
			return rdf;
		},
		/**
		 * Parses R2R prefix definitions into JavaScript objects
		 * @param prefixDefinitions string value
		 *
		 * Example input:
		 *	"smwcat: <http://mywiki/resource/category/> .
		 *	  smwprop: <http://mywiki/resource/property/> ."
		 * Example output:
		 *	{smwcat: "http://mywiki/resource/category/",
		 *	  smwprop: "http://mywiki/resource/property/"}"
		 */
		parsePrefixDefinitions: function(prefixDefinitions) {
			var resultObj = {};
			if (prefixDefinitions === undefined) {
				return;
			}
			
			var matches = prefixDefinitions.match(/([^:]+):.*?<([^>]+)>\s?\.?\s*/g);
			if (matches) {
				$(matches).each(function(key, val) {
					var result = val.match(/([^:]+):.*?<([^>]+)>\s?\.?\s*/);
					resultObj[result[1]] = result[2];
				});
			}
			return resultObj;
		},
		/**
		 * Creates an R2R prefix definition from a JavaScript object (inverse of parsePrefixDefinitions)
		 */
		constructPrefixDefinitions: function (obj) {
			var prefixDefinitions = "";
			$.each(obj, function(key, value) {
				prefixDefinitions += key + ": <" + value + "> .\n";
			});
			return prefixDefinitions;
		},
		createStringLiteral: function(str) {
			return $.rdf.literal('"' + str.replace(/"/g, '\\"') + '"');
		}
	};
})(jQuery);