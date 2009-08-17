<?php
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
    'df_description' => 'Beschreibung'
	);
}
?>