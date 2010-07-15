<?php
/**
 * @file
 * @ingroup LinkedData
 */

/**
 * Mapping Language API: LODMLSubpropertyMapping
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * Represents an rdfs:subPropertyOf mapping
 */
class LODMLSubpropertyMapping extends LODMLStatementBasedMapping {

	protected static function property() {
		return RDFS_SUBPROPERTYOF;
	}

}

?>