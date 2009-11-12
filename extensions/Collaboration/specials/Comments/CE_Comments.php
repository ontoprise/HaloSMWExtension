<?php
/*
 * Created on 11.11.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


class CEComments {
	
	
	function smwfCEAddHTMLHeader(&$out) {
		global $cegScriptPath;
		// All scripts that have been added later on... 
		$out->addScript("<script type=\"text/javascript\" src=\"".$cegScriptPath .  "Comments/scripts/CE_Comments.js\"></script>");
		// style definitions
		$out->addLink(array(
			'rel'   => 'stylesheet',
			'type'  => 'text/css',
			'media' => 'screen, projection',
			'href'  => $cegScriptPath . '/Comments/skins/CE_Comment.css'
		));
		
		return true;
	}
}

