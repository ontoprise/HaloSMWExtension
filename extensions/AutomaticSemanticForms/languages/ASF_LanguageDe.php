<?php 


global $asfIP;
include_once($asfIP . '/languages/ASF_Language.php');

class ASFLanguageDe extends ASFLanguage {
	
	protected $asfUserMessages = array(
		'asf_free_text' => "Freitext Eingabefeld:",
		'asf_dummy_article_edit_comment' => "Erzeugt von der Automatic Semantic Forms Extension",
		'asf_dummy_article_content' => "'''Dieser Artikel wird von der Automatic Semantic Forms Extension ben&ouml;t. Bitte l&ouml;schen, editieren oder verschieben Sie ihn daher nicht.'''",
		'asf_category_section_label' => "Eingabe von ''$1'' Daten;",
		'asf_duplicate_property_placeholder' => "Please enter value in the input field above.",
	);

}


