<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Base class for all visitors of a SPARQL query structure.
 * 
 * @author Thomas Schweitzer
 * Date: 19.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * 
 * This is the base class of a visitor that visits the structure of a SPARQL 
 * query. These queries have a tree-like structure so that there is a pre-, inter-
 * and post-visit method for each node.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TSCSparqlQueryVisitor  {

	//--- Interface methods ---
	
	public function preVisitRoot(&$pattern) {}
	public function preVisitQuery(&$pattern) {}
	public function preVisitGroup(&$pattern) {}
	public function preVisitUnion(&$pattern) {}
	public function preVisitOptional(&$pattern) {}
	public function preVisitFilter(&$pattern) {}
	public function preVisitGraph(&$pattern) {}
	public function preVisitTriples(&$pattern) {}
	public function preVisitTriple(&$pattern) {}

	public function interVisitRoot(&$pattern) {}
	public function interVisitQuery(&$pattern) {}
	public function interVisitGroup(&$pattern) {}
	public function interVisitUnion(&$pattern) {}
	public function interVisitOptional(&$pattern) {}
	public function interVisitFilter(&$pattern) {}
	public function interVisitGraph(&$pattern) {}
	public function interVisitTriples(&$pattern) {}
	public function interVisitTriple(&$pattern) {}
	
	public function postVisitRoot(&$pattern) {}
	public function postVisitQuery(&$pattern) {}
	public function postVisitGroup(&$pattern) {}
	public function postVisitUnion(&$pattern) {}
	public function postVisitOptional(&$pattern) {}
	public function postVisitFilter(&$pattern) {}
	public function postVisitGraph(&$pattern) {}
	public function postVisitTriples(&$pattern) {}
	public function postVisitTriple(&$pattern) {}
	
}
