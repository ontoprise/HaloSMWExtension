<?php
/**
 * @file
 * @ingroup SRLanguage
 * 
 * Language file En
 * 
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
require_once("SR_Language.php");

class SR_LanguageEn extends SR_Language {

    protected $srContentMessages = array(
    // Simple Rules formula parser
    'smw_srf_expected_factor' => 'Expected a function, variable, constant or braces near $1',
    'smw_srf_expected_comma' => 'Expected a comma near $1',
    'smw_srf_expected_(' => 'Expected an opening brace near $1',
    'smw_srf_expected_)' => 'Expected a closing brace near $1',
    'smw_srf_expected_parameter' => 'Expected a parameter near $1',
    'smw_srf_missing_operator' => 'Expected an operator near $1',
    
    
    
    );
    
    protected $srUserMessages = array('smw_ob_ruleTree' => 'Rules');
}
