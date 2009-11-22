<?php
/*
 * Language file for the SMW UP rating extension
 */
class SMW_UpRatingLanguage {

	protected $contentMessages = array(
        'en' => array(
            'smw_upr_rate_table_link'  => 'See all comments and rate this table',
            'smw_upr_data'             => 'Data',
            'smw_upr_complete_table'   => 'complete table',
            'smw_upr_data_correct'     => 'Data is correct',
            'smw_upr_data_invalid'     => 'Data is invalid',
            'smw_upr_comm_for_cell'    => '<b>1</b> comment has been added to this data cell.',
            'smw_upr_comms_for_cell'   => '<b>%s</b> comments have been added to this data cell.',
            'smw_upr_comm_for_table'   => '<b>1</b> comment has been added to this table.',
            'smw_upr_comms_for_table'  => '<b>%s</b> comments have been added to this table.',
            'smw_upr_comm_for_data'    => '<b>1</b> comment has been added to an individual data element.',
            'smw_upr_comms_for_data'   => '<b>%s</b> comments have been added to individual data elements.'
        ),
        'de' => array(
            'smw_upr_rate_table_link'  => 'Lese alle Kommentate und bewerte die Tabelle',
            'smw_upr_data'             => 'Daten',
            'smw_upr_complete_table'   => 'ganze Tabelle',
            'smw_upr_data_correct'     => 'Daten sind ok',
            'smw_upr_data_invalid'     => 'Daten sind falsch',
            'smw_upr_comm_for_cell'    => '<b>1</b> Kommentar für dieses Datenfeld.',
            'smw_upr_comms_for_cell'   => '<b>%s</b> Kommentare für dieses Datenfeld.',
            'smw_upr_comm_for_table'   => '<b>1</b> Kommentar für diese Tabelle.',
            'smw_upr_comms_for_table'  => '<b>%s</b> Kommentare für diese Tabelle.',
            'smw_upr_comm_for_data'    => '<b>1</b> Kommentar für individuelle Datenfelder.',
            'smw_upr_comms_for_data'   => '<b>%s</b> Kommentare für individuelle Datenfelder.'
        )
    );

    function getTexts($lang) {
        $lang = strtolower($lang);
        if (in_array($lang, array_keys($this->contentMessages)))
            return $this->contentMessages[$lang];
        return $this->contentMessages['en'];
    }

}

global $wgMessageCache, $wgLanguageCode;
$uprLang = new SMW_UpRatingLanguage();
$wgMessageCache->addMessages($uprLang->getTexts($wgLanguageCode), $wgLanguageCode);
unset($uprLang);

?>