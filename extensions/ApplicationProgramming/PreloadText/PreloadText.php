<?php
/*
 PreloadText.php
 Fills the edit box of a new article with a text that is given in the URL parameter
 preloadtext as in ?title=Article&preloadtext=This+is+the+initial+content
 
 Please note the the text to preload must be URL encoded


 Author: Thomas Schweitzer
       
 Version 1.0 (2011/5/13)
*/
 
$wgExtensionCredits['other'][] = array(
	'name' => 'Preload text',
	'version' => '1.0',
	'url' => 'http://smwforum.ontoprise.com/smwforum/index.php/Help:Preload_text',
	'author' => 'Thomas Schweitzer. Owned by [http://www.ontoprise.de ontoprise GmbH].',   
	'description' => 'Fills the edit box of a new article with a text that is given in the URL parameter "preloadtext"'
);
 
$wgHooks['EditFormPreloadText'][] = array('wfExtPreloadText');

function wfExtPreloadText(&$text) {
	global $wgRequest;
	$preloadText = $wgRequest->getVal('preloadtext', '');
	if (!empty($preloadText)) {
		$text = $preloadText;
	}
		
	return true;
}

