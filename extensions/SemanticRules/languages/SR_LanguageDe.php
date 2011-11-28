<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup SRLanguage
 * 
 * Language file De
 * 
 * @author: Kai K�hn
 *
 */
require_once("SR_Language.php");

class SR_LanguageDe extends SR_Language {

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
