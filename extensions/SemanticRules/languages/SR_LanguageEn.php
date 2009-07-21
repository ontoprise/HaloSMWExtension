<?php
/**
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
    
    // Explanations
    'smw_explanations' => 'Explanations',
    'explanations' => 'Explanations',
    'smw_expl_not_all_inputs' => 'Please provide an input for each field above.',
    'smw_expl_and' => 'AND',
    'smw_expl_because' => 'BECAUSE',
    'smw_expl_value' => 'Value',
    'smw_expl_img' => 'Trigger explanation',
    'smw_expl_explain_category' => 'Explain category assignment:',
    'smw_expl_explain_property' => 'Explain property assignment:',
    'smw_expl_error' => 'Unfortunately, some error occured during the request for the explanation:'
    
    );
    
    protected $srUserMessages = array();
}
?>