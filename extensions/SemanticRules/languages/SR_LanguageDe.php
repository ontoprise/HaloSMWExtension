<?php
/**
 * @file
 * @ingroup SRLanguage
 * 
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
    
   'sr_ob_rulelist' => 'Regel-Metadaten'
    
    );
    
   protected $srUserMessages = array('smw_ob_ruleTree' => 'Rules');
}
