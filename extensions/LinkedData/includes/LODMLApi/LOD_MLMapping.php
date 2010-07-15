<?php
/**
 * @file
 * @ingroup LinkedData
 */

/**
 * Mapping Language API: LODMLMapping
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * Base class for mapping language objects
 */
abstract class LODMLMapping {
	
	/**
	 * The mapping URI, or for statement-based mappings, the subject URI
	 */
	protected $uri;
	
	/**
	 * ARC property tree
	 */
	protected $properties;
	
	/** 
	 * @param	string	$uri
	 * @param	array<property> => array<string>	$properties
	 * @param	array<LODMLMapping>	$otherMappings
	 */
	public function __construct($uri, $properties, $otherMappings) {
		$this->uri = $uri;
		$this->properties = $properties;
		if (!static::handles($properties)) {
			throw new Exception("$uri can not be handled by this class");
		}
	}
	
	/**
	 * Returns the mapping URI
	 * @return	string
	 */
	public function getURI() {
		return $this->uri;
	}
	
	/**
	 * Allows to determine whether a given resource is handled by this classes
	 * @param	array<property> => array<string>	$properties
	 * @return	boolean
	 */
	public static function handles($properties) {
		if (!$properties) {
			return;
		}

		if ($properties[LOD_ML_RDF_TYPE]) {
			foreach ($properties[LOD_ML_RDF_TYPE] as $type) {
				if (static::handlesType($type)) {
					return true;
				}
			}
		}

		foreach (array_keys($properties) as $property) {
			if (static::handlesProperty($property)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Allows to determine whether a given RDF type is handled by this class
	 * @param	string	$type
	 * @return	boolean
	 */
	public static function handlesType($type) {
		return (static::type() && static::type() == $type);
	}

	/**
	 * Allows to determine whether a given RDF property is handled by this class
	 * @param	string	$property
	 * @return	boolean
	 */
	public static function handlesProperty($property) {
		return (static::property() && static::property() == $property);
	}

	/**
	 * @return	The type handled by a mapping, or null if not applicable
	 */
	abstract protected static function type();

	/**
	 * @return	The property handled by a mapping, or null if not applicable
	 */
	abstract protected static function property();
}
?>