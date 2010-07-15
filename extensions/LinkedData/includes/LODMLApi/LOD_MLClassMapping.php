<?php
/**
 * @file
 * @ingroup LinkedData
 */

/**
 * Mapping Language API: LODMLClassMapping
 * Based on http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/
 * Author: Christian Becker
 */
 
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * Represents an r2r:ClassMapping
 */
class LODMLClassMapping extends LODMLR2RMapping {

	protected static function type() {
		return LOD_ML_R2R_CLASSMAPPING;
	}

}

?>