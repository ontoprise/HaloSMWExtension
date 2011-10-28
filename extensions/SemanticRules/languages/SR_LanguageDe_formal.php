<?php
/**
 * @file
 * @ingroup SRLanguage
 * 
 * Language file De
 * 
 * @author: Kai K�hn / ontoprise / 2009
 *
 */
require_once("SR_Language.php");

class SR_LanguageDe_formal extends SR_Language {

    protected $srContentMessages = array(
    
    // Simple Rules formula parser
    'smw_srf_expected_factor' => 'Erwarte eine Funktion, Variable, Konstante oder Klammer bei $1',
    'smw_srf_expected_comma' => 'Erwarte ein Komma bei $1',
    'smw_srf_expected_(' => 'Erwarte eine �ffnende Klammer bei $1',
    'smw_srf_expected_)' => 'Erwarte eine schlie�ende Klammer bei $1',
    'smw_srf_expected_parameter' => 'Erwarte einen Parameter bei $1',
    'smw_srf_missing_operator' => 'Erwarte eine Operator bei $1',
    
    #Ontology browser extension
    'sr_ob_rulelist' => 'Regel-Metadaten',
    
     # These constants map internal TSC rule types to the wiki representation.
    'sr_definition_rule' => 'Definition',
    'sr_property_chaining' => 'Eigenschaftsverkettung',
    'sr_calculation' => 'Berechnung',
    
    #Rule widget
    'sr_ruleselector' => 'Regelformat: ',
    'sr_easyreadible' => 'Leicht lesbar',
    'sr_stylizedenglish' => 'Formales Englisch',
    'sr_rulesdefinedfor' => 'Regeln definiert f�r',
    'sr_rulestatus' => 'Status',
     'sr_rule_isactive_state' => 'aktiv',
    'sr_rule_isinactive_state' => 'inaktiv',
    'sr_prop' => 'Property',
    'sr_cat' => 'Kategorie',
    'sr_inst' => 'Instanz',
    
     #Unified search extension
    'sr_rulesfound' => 'Die folgenden Regeln wurden gefunden:'
    );
    
   protected $srUserMessages = array('smw_ob_ruleTree' => 'Rules');
}
