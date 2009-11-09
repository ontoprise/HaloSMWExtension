<?php
/* 
 * Language file for the SMW User Manual extension
 */
class SMW_UMLanguageEn {

	protected $umeContentMessages = array(
        'smw_ume_help_link'       => 'Help',
        'smw_ume_box_headline'    => 'Help and Feedback',
        'smw_ume_tab_help'        => 'Help',
        'smw_ume_tab_feedback'    => 'Feedback',
        'smw_ume_cpt_headline_1'  => 'Context sensitive help',
        'smw_ume_cpt_headline_2'  => 'Choose feedback type',
        'smw_ume_select_topic'    => 'Select help topic:',
        'smw_ume_link_to_smwforum'=> '»Visit SMW<sup>+</sup> Userforum for more help',
        'smw_ume_did_it_help'     => 'Was this help content useful?',
        'smw_ume_ask_your_own_q'  => 'Ask your own question for the CSH',
        'smw_ume_add_comment'     => 'Add a comment to SMW<sup>+</sup>',
        'smw_ume_bug_discovered'  => 'Did you discover a bug?',
        'smw_ume_submit_feedback' => 'Submit feedback',
        'smw_ume_reset'           => 'Reset',
        'smw_ume_yes'             => 'yes',
        'smw_ume_no'              => 'no',
        // all these texts are for install only
        'smw_ume_create_props'    => 'Create properties, needed by the User Manual extension',
        'smw_ume_create_page'     => 'Create wiki page: %s',
        'smw_ume_warning_page'    => 'Warning, page already exists',
        'smw_ume_del_csh_pages'   => 'Deleting old CSH pages from your local wiki...',
        'smw_ume_get_csh_pages'   => 'Fetch new CSH pages from the SMW Forum and install these in this wiki',
        'smw_ume_no_article_list' => 'Could not get CSH article list from SMW forum',
        'smw_ume_install_done'    => 'Installation finished.',
        'smw_ume_done'            => 'Done.',
        'smw_ume_error_code'      => 'Error code:',
        'smw_ume_no_csh_articles' => 'No matching help articles found for current state.',
        'smw_ume_no_help_article' => 'Error: Could not fetch help article',
    );

    protected $umeNamespaces = array(
		SMW_NS_USER_MANUAL       => 'UserManual',
		SMW_NS_USER_MANUAL_TALK  => 'UserManual_talk',
	);

	protected $umeNamespaceAliases = array(
		'UserManual'       => SMW_NS_USER_MANUAL,
		'UserManual_talk'  => SMW_NS_USER_MANUAL_TALK
	);
    
    protected $umeTalkSuffix = '_talk';

    function getTexts() {
        return $this->umeContentMessages;
    }

    function getNsText($ns) {
        if (isset($this->umeNamespaces[$ns]))
            return $this->umeNamespaces[$ns];
        global $wgLang;
        if ($wgLang) return $wgLang->getNsText($ns);
    }

    function getTalkSuffix() {
        return $this->umeTalkSuffix;
    }
}