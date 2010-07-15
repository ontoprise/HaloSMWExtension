<?php
/**
 * @file
 * @ingroup LinkedData
 */

/**
 * Mapping Language API: LODMLSubclassMapping
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * Represents an rdfs:subClassOf mapping
 */
class LODMLSubclassMapping extends LODMLStatementBasedMapping {

	protected static function property() {
		return LOD_ML_RDFS_SUBCLASSOF;
	}

}

?>