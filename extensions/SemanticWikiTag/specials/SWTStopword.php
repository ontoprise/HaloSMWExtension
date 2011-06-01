<?php
/*
 * Author: ning
 */

if ( !defined( 'MEDIAWIKI' ) ) die();



global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

global $smwgWTIP;

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SWTStopword extends SpecialPage {
	public function __construct() {
		parent::__construct( 'Stopword' );
	}
	/*
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute() {
		global $wgRequest, $wgOut, $wgUser;

		$wgOut->setPageTitle( 'WikiTags Stopword list' );
		
		$wgOut->addHTML( '<p>
	To enable or disable WikiTags stopword list filtering, please edit "$IP/LocalSettings.php" and add <br/>
	<pre>$wgEnableWikiTagsStopword = TRUE; // FALSE;</pre>
	<br/>
	For the stopword list, upload a file named "stopword.txt" under "$IP/images/", each stopword one line, and modify in this special page.
</p>' );
		
		if(!in_array( 'sysop', $wgUser->getEffectiveGroups() )) {
			$wgOut->addHTML( 'Access denied. Please access this page with "sysop" account.' );
			return;
		}

		global $wgUploadDirectory;
		$file = $wgUploadDirectory.'/stopword.txt';
		if(file_exists($file)) {
			$stopwordList = file_get_contents($file);
		}
		
		if($wgRequest->getVal('wpSave')) {
			$stopwordList = $wgRequest->getText('stopwordlist');

			file_put_contents($file, $stopwordList);
		}
		
		global $wgEnableWikiTagsStopword;
		$html = '<form id="stopwordform" name="stopwordform" method="post" action="">
<p>WikiTags stopword filter is <b>' . ($wgEnableWikiTagsStopword ? 'enabled ' : 'disabled') . '</b> now.</p>
<p><textarea name="stopwordlist" id="stopwordlist" cols="60" rows="25" tabindex="2">' . htmlentities($stopwordList) . '</textarea><br />
Separate every stopword with an "enter".</p>
<p><input id="wpSave" name="wpSave" type="submit" tabindex="3" value="Update stopword settings" accesskey="s" title="Save your changes [s]" /></p>
</form>';
		$wgOut->addHTML( $html );
	}
}
