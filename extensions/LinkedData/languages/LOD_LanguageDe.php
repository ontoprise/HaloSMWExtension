<?php
/**
 * @file
 * @ingroup LinkedData_Language
 */

/*  Copyright 2010, ontoprise GmbH
*   This file is part of the LinkedData-Extension.
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

/*
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $lodgIP;
include_once($lodgIP . '/languages/LOD_Language.php');


/**
 * German language labels for important LinkedData labels (namespaces, ,...).
 *
 * @author Thomas Schweitzer
 */
class LODLanguageDe extends LODLanguage {

	protected $mNamespaces = array(
		LOD_NS_LOD       => 'LOD',
		LOD_NS_LOD_TALK  => 'LOD_Diskussion',
		LOD_NS_MAPPING       => 'Mapping',
		LOD_NS_MAPPING_TALK  => 'Mapping_Diskussion'
	);

	protected $mParserFunctions = array(
		LODLanguage::PF_MAPPING				=> 'zuordnung', 
	);
	
	protected $mParserFunctionsParameters = array(
//		LODLanguage::...	=> '...', 
	);
		
}


