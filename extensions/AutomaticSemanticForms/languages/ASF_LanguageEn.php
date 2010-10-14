<?php 


global $asfIP;
include_once($asfIP . '/languages/ASF_Language.php');

class ASFLanguageEn extends ASFLanguage {
	
	protected $asfUserMessages = array(
		'asf_free_text' => "Free text:",
		'asf_dummy_article_edit_comment' => "Created by the Automatic Semantic Forms Extension",
		'asf_dummy_article_content' => "'''This article is required by the Automatic Semantic Forms Extension. Please do not move, edit or delete this article.'''",
		'asf_category_section_label' => "Enter ''$1'' data;",
	'asf_duplicate_property_placeholder' => "Bitte einen Wert im Eingabefeld oben eingeben."
	);

}


