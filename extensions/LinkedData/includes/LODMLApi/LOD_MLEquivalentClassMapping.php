<?php
/**
 * @file
 * @ingroup LinkedData
 */

/**
 * Mapping Language API: LODMLEquivalentClassMapping
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * Represents an owl:equivalentClass mapping
 */
class LODMLEquivalentClassMapping extends LODMLStatementBasedMapping {

	protected static function property() {
		return LOD_ML_OWL_EQUIVALENTCLASS;
	}

}

?>