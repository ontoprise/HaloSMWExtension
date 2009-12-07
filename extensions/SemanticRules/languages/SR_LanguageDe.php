<?php
/**
 * Language file De
 * 
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
require_once("SR_Language.php");

class SR_LanguageDe extends SR_Language {

    protected $srContentMessages = array(
    
    // Simple Rules formula parser
    'smw_srf_expected_factor' => 'Erwarte eine Funktion, Variable, Konstante oder Klammer bei $1',
    'smw_srf_expected_comma' => 'Erwarte ein Komma bei $1',
    'smw_srf_expected_(' => 'Erwarte eine öffnende Klammer bei $1',
    'smw_srf_expected_)' => 'Erwarte eine schließende Klammer bei $1',
    'smw_srf_expected_parameter' => 'Erwarte einen Parameter bei $1',
    'smw_srf_missing_operator' => 'Erwarte eine Operator bei $1',
    
    // Explanations
    'smw_explanations' => 'Explanations',
    'explanations' => 'Explanations',
    'smw_expl_not_all_inputs' => 'Bitte füllen Sie alle obenstehenden Felder aus.',
    'smw_expl_and' => 'UND',
    'smw_expl_because' => 'WEIL',
    'smw_expl_value' => 'Wert',
    'smw_expl_img' => 'Erklärung anfordern',
    'smw_expl_explain_category' => 'Erklärung für Kategoriezuordnung:',
    'smw_expl_explain_property' => 'Erklärung für Propertyzuordnung:',
    'smw_expl_error' => 'Leider gab es einen Fehler während der Auswertung der Erklärung:'
    
    );
    
    protected $srUserMessages = array();
}
