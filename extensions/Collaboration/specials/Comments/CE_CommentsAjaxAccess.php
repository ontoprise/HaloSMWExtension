<?php
/*
 * Created on 11.11.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/Collaboration/includes/CE_Initialize.php" );
EOT;
        exit( 1 );
}

#AJAX functions
/**
 * @param wikurl etc...
 * @return xml
 * 
 */
function ceCreateNewPageAjax( $param ) {
	return "xml";
}