<?php
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
 * @ingroup LinkedData
 */

/**
 * Mapping Language API
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

define("LOD_ML_NS_RDF", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
define("LOD_ML_RDF_TYPE", LOD_ML_NS_RDF . "type");

define("LOD_ML_NS_RDFS", "http://www.w3.org/2000/01/rdf-schema#");
define("LOD_ML_RDFS_SUBCLASSOF", LOD_ML_NS_RDFS . "subClassOf");
define("LOD_ML_RDFS_SUBPROPERTYOF", LOD_ML_NS_RDFS . "subPropertyOf");

define("LOD_ML_NS_OWL", "http://www.w3.org/2002/07/owl#");
define("LOD_ML_OWL_EQUIVALENTCLASS", LOD_ML_NS_OWL . "equivalentClass");
define("LOD_ML_OWL_EQUIVALENTPROPERTY", LOD_ML_NS_OWL . "equivalentProperty");

define("LOD_ML_NS_R2R", "http://www4.wiwiss.fu-berlin.de/bizer/r2r/");
define("LOD_ML_R2R_CLASSMAPPING", LOD_ML_NS_R2R . "ClassMapping");
define("LOD_ML_R2R_PROPERTYMAPPING", LOD_ML_NS_R2R . "PropertyMapping");
define("LOD_ML_R2R_SOURCEPATTERN", LOD_ML_NS_R2R . "sourcePattern");
define("LOD_ML_R2R_TARGETPATTERN", LOD_ML_NS_R2R . "targetPattern");
define("LOD_ML_R2R_PREFIXDEFINITIONS", LOD_ML_NS_R2R . "prefixDefinitions");
define("LOD_ML_R2R_TRANSFORMATION", LOD_ML_NS_R2R . "transformation");
define("LOD_ML_R2R_MAPPINGREF", LOD_ML_NS_R2R . "mappingRef");

/**
 * Creates mapping language objects from RDF serialization
 */
class LODMLMappingLanguageAPI {
	
	/**
	 * Array of LODMLMapping subclasses that can be created
	 */
	private static $classes = array("LODMLClassMapping", "LODMLPropertyMapping", "LODMLEquivalentClassMapping", "LODMLEquivalentPropertyMapping", "LODMLSubclassMapping", "LODMLSubpropertyMapping");
	
	/**
	 * Creates mapping language objects from RDF serialization
	 * @param	string	$filenameOrData	Path to an RDFXML, TTL/N3 or NT file, or data in one of these serializations
	 * @return	array<LODMLMapping>	Mapping objects
	 * @throws	Exception when unknown objects are encountered
	 */
	public static function parse($filenameOrData) {
		$parser = ARC2::getRDFParser();
		if (file_exists($filenameOrData)) {
			$parser->parse($filenameOrData);
		} else {
			$parser->parse(null, $filenameOrData);
		}
		
		$mappings = array();
		
		foreach ($parser->getSimpleIndex() as $uri => $properties) {
			$handled = false;
			
			foreach (self::$classes as $class) {
				/*PHP 5.3: if ($class::handles($properties)) { */
				$instance = new $class(null, null, null);
				/*PHP 5.2: if ($instance->handles($properties)) { */
                if ($class::handles($properties)) {
					$mappings[$uri] = new $class($uri, $properties, $mappings);
					$handled = true;
					break;
				}
			}
			if (!$handled) {
				throw new Exception("Unable to find suitable object for $uri");
			}
		}
		
		return $mappings;
	}
}

?>
