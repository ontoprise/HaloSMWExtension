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

		if (is_array($this->properties) && array_key_exists(LOD_ML_R2R_MAPPINGREF, $this->properties) &&
		$this->properties[LOD_ML_R2R_MAPPINGREF][0]) {
			if (!($this->classMapping = $otherMappings[$this->properties[LOD_ML_R2R_MAPPINGREF][0]])) {
				throw new Exception("$uri references inexistent ClassMapping " . $otherMappings[$this->properties[LOD_ML_R2R_MAPPINGREF][0]]);
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
