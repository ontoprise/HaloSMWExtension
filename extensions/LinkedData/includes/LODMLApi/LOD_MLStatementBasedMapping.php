<?php
/**
 * @file
 * @ingroup LinkedData
 */

/**
 * Mapping Language API: LODMLStatementBasedMapping
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * Represents mappings that are provided in self-contained triple statements, e.g. owl:equivalentClass
 */
abstract class LODMLStatementBasedMapping extends LODMLMapping {

	protected static function type() {
		return null;
	}
	
	/**
	 * Returns the URI of the source entity 
	 * @return	string
	 */
	public function getSourceEntity() {
		return $this->uri;
	}
	
	/**
	 * Returns the URI of the target entity 
	 * @return	string
	 */
	public function getTargetEntity() {
		return $this->properties[$this->property()][0];
	}		

}

?>