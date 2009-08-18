<?php
/*  Copyright 2009, ontoprise GmbH
*  
*   The deployment tool is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The deployment tool is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once('DF_Language.php');
/**
 * Language abstraction.
 *
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
class DF_Language_En extends DF_Language {
	protected $language_constants = array(
    'df_ontologyversion' => 'Ontology version',
    'df_partofbundle' => 'Part of bundle',
    'df_contenthash' => 'Content hash',
	'df_dependencies'=> 'Dependency',
	'df_instdir' => 'Installation dir',
	'df_ontologyvendor' => 'Vendor',
	'df_description' => 'Description'
    );
    
}
