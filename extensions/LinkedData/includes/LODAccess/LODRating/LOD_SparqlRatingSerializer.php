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
 * This file defines the class LODSparqlRatingSerializer
 * 
 * @author Thomas Schweitzer
 * Date: 20.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * This class serializes queries that have been modified by the LODRatingRewriter.
 * It ignores FILTER statements during the serialization.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODSparqlRatingSerializer extends TSCSparqlSerializerVisitor {

	//--- Public methods ---
	
	/**
	 * Simply overwrites the parent method and do nothing => FILTER is ignored.
	 * @param array $pattern
	 */
	public function preVisitFilter(&$pattern) { }
	
	//--- Private methods ---
}
