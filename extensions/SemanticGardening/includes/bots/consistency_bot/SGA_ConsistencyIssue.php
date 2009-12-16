<?php
define('SMW_CONSISTENCY_BOT_BASE', 100);
// covariance issues
define('SMW_GARDISSUE_DOMAINS_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 1);
define('SMW_GARDISSUE_RANGES_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 2);
define('SMW_GARDISSUE_TYPES_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 3);
define('SMW_GARDISSUE_MINCARD_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 4);
define('SMW_GARDISSUE_MAXCARD_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 5);
define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1', SMW_CONSISTENCY_BOT_BASE * 100 + 6);
define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1', SMW_CONSISTENCY_BOT_BASE * 100 + 7);
define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2', SMW_CONSISTENCY_BOT_BASE * 100 + 8);
define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2', SMW_CONSISTENCY_BOT_BASE * 100 + 9);
// ...
// not defined issues
define('SMW_GARDISSUE_DOMAINS_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 1);
define('SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 2);
define('SMW_GARDISSUE_RANGES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 4);
define('SMW_GARDISSUE_TYPES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 5);


// doubles issues
define('SMW_GARDISSUE_DOUBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 1);
define('SMW_GARDISSUE_DOUBLE_MAX_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 2);
define('SMW_GARDISSUE_DOUBLE_MIN_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 3);


// wrong/missing values / entity issues
define('SMW_GARDISSUE_MAXCARD_NOT_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 1);
define('SMW_GARDISSUE_MINCARD_BELOW_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 2);
define('SMW_GARDISSUE_WRONG_MINCARD_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 3);
define('SMW_GARDISSUE_WRONG_MAXCARD_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 4);
define('SMW_GARDISSUE_WRONG_TARGET_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 5);
define('SMW_GARDISSUE_WRONG_DOMAIN_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 6);
define('SMW_GARDISSUE_TOO_LOW_CARD', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 7);
define('SMW_GARDISSUE_TOO_HIGH_CARD', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 8);
define('SMW_GARDISSUE_WRONG_UNIT', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 9);
define('SMW_GARD_ISSUE_MISSING_PARAM', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 10);
define('SMW_GARDISSUE_MISSING_ANNOTATIONS', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 11);

// incompatible entity issues
define('SMW_GARD_ISSUE_DOMAIN_NOT_RANGE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 1);
define('SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 2);
define('SMW_GARD_ISSUE_INCOMPATIBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 3);
define('SMW_GARD_ISSUE_INCOMPATIBLE_SUPERTYPES', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 4 );

// others
define('SMW_GARD_ISSUE_CYCLE', (SMW_CONSISTENCY_BOT_BASE+5) * 100 + 1);

// issues with type > 100000 are not displayed textually in GardeningLog
define('SMW_GARDISSUE_CONSISTENCY_PROPAGATION', 1000 * 100 + 1);

class ConsistencyBotIssue extends GardeningIssue {

    public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
        parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
    }

    protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
        $text1 = $local ? wfMsg('smw_gard_issue_local') : $text1;
        // show title2 as link if $skin is defined
        $text2 = $skin != NULL ? $skin->makeLinkObj($this->t2) : $text2;
        switch($this->gi_type) {
            case SMW_GARDISSUE_DOMAINS_NOT_COVARIANT:
                return wfMsg('smw_gardissue_domains_not_covariant', $text1, $text2);
            case SMW_GARDISSUE_RANGES_NOT_COVARIANT:
                return wfMsg('smw_gardissue_ranges_not_covariant', $text1, $text2);
            case SMW_GARDISSUE_TYPES_NOT_COVARIANT:
                return wfMsg('smw_gardissue_types_not_covariant', $text1);
            case SMW_GARDISSUE_MINCARD_NOT_COVARIANT:
                return wfMsg('smw_gardissue_mincard_not_covariant', $text1);
            case SMW_GARDISSUE_MAXCARD_NOT_COVARIANT:
                return wfMsg('smw_gardissue_maxcard_not_covariant', $text1);
            case SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1:
                return wfMsg('smw_gardissue_symetry_not_covariant1', $text1);
            case SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1:
                return wfMsg('smw_gardissue_transitivity_not_covariant1', $text1);
            case SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2:
                return wfMsg('smw_gardissue_symetry_not_covariant2', $text1);
            case SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2:
                return wfMsg('smw_gardissue_transitivity_not_covariant2', $text1);

            case SMW_GARDISSUE_DOMAINS_NOT_DEFINED:
                return wfMsg('smw_gardissue_domains_not_defined', $text1);
            case SMW_GARDISSUE_RANGES_NOT_DEFINED:
                return wfMsg('smw_gardissue_ranges_not_defined', $text1);
            case SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED:
                return wfMsg('smw_gardissue_domains_and_ranges_not_defined', $text1);
            case SMW_GARDISSUE_TYPES_NOT_DEFINED:
                return wfMsg('smw_gardissue_types_not_defined', $text1);


            case SMW_GARDISSUE_DOUBLE_TYPE:
                return wfMsg('smw_gardissue_double_type', $text1, $this->value);
            case SMW_GARDISSUE_DOUBLE_MAX_CARD:
                return wfMsg('smw_gardissue_double_max_card', $text1, $this->value);
            case SMW_GARDISSUE_DOUBLE_MIN_CARD:
                return wfMsg('smw_gardissue_double_min_card', $text1, $this->value);
            case SMW_GARD_ISSUE_MISSING_PARAM:
                return wfMsg('smw_gard_issue_missing_param',$text1, $text2, $this->value);

            case SMW_GARDISSUE_MAXCARD_NOT_NULL:
                return wfMsg('smw_gardissue_maxcard_not_null', $text1);
            case SMW_GARDISSUE_MINCARD_BELOW_NULL:
                return wfMsg('smw_gardissue_mincard_below_null', $text1);
            case SMW_GARDISSUE_WRONG_MINCARD_VALUE:
                return wfMsg('smw_gardissue_wrong_mincard_value', $text1);
            case SMW_GARDISSUE_WRONG_MAXCARD_VALUE:
                return wfMsg('smw_gardissue_wrong_maxcard_value', $text1);
            case SMW_GARDISSUE_WRONG_TARGET_VALUE:
                return wfMsg('smw_gardissue_wrong_target_value', $text1, $text2,  $skin != NULL ? $this->explodeTitlesToLinkObjs($skin, $this->value) : $this->value);
            case SMW_GARDISSUE_WRONG_DOMAIN_VALUE:
                return wfMsg('smw_gardissue_wrong_domain_value', $text1, $text2);
            case SMW_GARDISSUE_TOO_LOW_CARD:
                return wfMsg('smw_gardissue_too_low_card', $text1, $text2, $this->value);
            case SMW_GARDISSUE_MISSING_ANNOTATIONS:
                return wfMsg('smw_gardissue_missing_annotations', $text1, $text2, $this->value);
            case SMW_GARDISSUE_TOO_HIGH_CARD:
                return wfMsg('smw_gardissue_too_high_card', $text1, $text2, $this->value);
            case SMW_GARDISSUE_WRONG_UNIT:
                return wfMsg('smw_gardissue_wrong_unit', $text1, $text2, $this->value);

            case SMW_GARD_ISSUE_DOMAIN_NOT_RANGE:
                return wfMsg('smw_gard_issue_domain_not_range', $text1, $text2);
            case SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY:
                return wfMsg('smw_gard_issue_incompatible_entity', $text1, $text2);
            case SMW_GARD_ISSUE_INCOMPATIBLE_TYPE:
                return wfMsg('smw_gard_issue_incompatible_type',$text1, $text2);
            case SMW_GARD_ISSUE_INCOMPATIBLE_SUPERTYPES:
                return wfMsg('smw_gard_issue_incompatible_supertypes',$text1, $this->value);
            case SMW_GARD_ISSUE_CYCLE:
                return wfMsg('smw_gard_issue_cycle',  $skin != NULL ? $this->explodeTitlesToLinkObjs($skin, $this->value) : $this->value);
            case SMW_GARDISSUE_CONSISTENCY_PROPAGATION:
                return wfMsg('smw_gard_issue_contains_further_problems');

            default: return "Unknown issue!"; // should not happen
        }
    }
}

