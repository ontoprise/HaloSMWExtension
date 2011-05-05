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
class DF_Language_De extends DF_Language {
	protected $language_constants = array(
	'df_ontologyversion' => 'Ontologieversion',
	'df_partofbundle' => 'Teil des Pakets',
	'df_contenthash' => 'Inhaltshash',
	'df_dependencies'=> 'Abhängigkeit',
    'df_instdir' => 'Installationsverzeichnis',
    'df_ontologyvendor' => 'Anbieter',
    'df_description' => 'Beschreibung',
	'df_contentbundle' => 'Content bundle',
    'df_ontologyuri' => 'Ontologie URI',
	
	'checkinstallation' => 'Prüfe Installation',
	'category' => 'Kategorie',
	'is_inverse_of' => 'Ist invers zu',
    'has_domain_and_range' => 'Hat Domain und Range',
	'imported_from'=>'Importiert aus',
	
	// user
    'checkinstallation' => 'Prüfe Installation',
    'df_checkforupdates' => 'Prüfe auf Updates',
	'df_updatesavailable' => 'Updates verfügbar!',
    'df_updateforextensions' => 'Es gibt Updates für folgende Extensions:',
    'df_noupdatesfound' => 'Keine Updates gefunden!'
    
	);
}
