<?php
/**
 * @file
 * @ingroup LinkedData
 */

/**
 * Mapping Language API: LODMLPropertyMapping
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * Represents an r2r:PropertyMapping
 */
class LODMLPropertyMapping extends LODMLR2RMapping {
	
	private $classMapping = null;
	
	/** 
	 * @param	string	$uri
	 * @param	array<property> => array<string>	$properties
	 * @param	array<LODMLMapping>	$otherMappings
	 */
	public function __construct($uri, $properties, $otherMappings) {
		parent::__construct($uri, $properties, $otherMappings);

		if ($this->properties[LOD_ML_R2R_CLASSMAPPINGREF][0]) {
			if (!($this->classMapping = $otherMappings[$this->properties[LOD_ML_R2R_CLASSMAPPINGREF][0]])) {
				throw new Exception("$uri references inexistant ClassMapping " . $otherMappings[$this->properties[LOD_ML_R2R_CLASSMAPPINGREF][0]]);
			}
			$this->prefixes = array_merge($this->classMapping->getPrefixes(), $this->prefixes);
		}
	}	

	protected static function type() {
		return LOD_ML_R2R_PROPERTYMAPPING;
	}
	
	/**
	 * Returns the source pattern in its string representation
	 * @return	string
	 */
	public function getSourcePattern() {
		return $this->properties[LOD_ML_R2R_SOURCEPATTERN][0];
	}
		
	/**
	 * @return	LODMLClassMapping	The associated class mapping
	 */
	public function getClassMapping() {
		return $this->classMapping;
	}
	
}

?>