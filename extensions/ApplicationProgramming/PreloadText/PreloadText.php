<?php
/*
 * Copyright (C) ontoprise GmbH
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
	'author'=>"Maintained by [http://smwplus.com ontoprise GmbH].", 
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

