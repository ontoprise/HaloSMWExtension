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
