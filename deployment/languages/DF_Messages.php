<?php
require_once('DF_Language_En.php');
require_once('DF_Language_De.php');
$messages = array();

/** 
 *  @author Kai Kühn
 */
$en = new DF_Language_En();
$de = new DF_Language_De();
$messages['en'] = $en->getLanguageArray();
$messages['de'] = $de->getLanguageArray();