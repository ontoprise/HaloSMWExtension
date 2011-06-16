<?php
/**
 * @author ning
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

global $wgOMIP;
include_once( $wgOMIP . '/languages/WOMLanguage.php' );

class WOMLanguageEn extends WOMLanguage {

	protected $wContentMessages = array(

	);

	protected $wUserMessages = array(
	/*Messages for Object Model*/
		'objecteditor' => 'Object Editor',
		'wom_editor' => 'Object Model',
	);

	protected $wWOMTypeLabels = array(
		'_cat'  => 'Category', // Category
		'_wpg'  => 'Page', // Page
		'_tpl'  => 'Template', // Template
		'_tfv'  => 'TemplateFieldValue', // Template field value
		'_pro'  => 'Property', // Property
		'_txt'  => 'Text', // Plain text
		'_lnk'  => 'Link', // URL/URI type
		'_fun'  => 'Parser Function', // Parser function
		'_sec'  => 'Section', // Section
	);
}


