<?php
/**
 * RM_EmbedWindow - used for displaying various types of files inline.
 *
 * @addtogroup SpecialPage
 *
 * @author Benjamin Langguth
 */
if (!defined('MEDIAWIKI')) die();

class RMEmbedWindow extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	function RMEmbedWindow() {
		UnlistedSpecialPage::UnlistedSpecialPage('EmbedWindow');
	}

	function execute($query) {
		$this->setHeaders();
		doSpecialEmbedWindow();
	}
}

/**
 * Entry point
 */
function doSpecialEmbedWindow() {
	global $wgRequest, $wgOut, $wgUser, $wgServer;
	global $wgScript, $wgJsMimeType, $wgStylePath, $wgStyleVersion;
	global $wgContLang, $wgLanguageCode, $wgXhtmlDefaultNamespace, $wgXhtmlNamespaces;
	global $wgUseAjax, $wgAjaxUploadDestCheck, $wgAjaxLicensePreview, $wgScriptPath, $sfgYUIBase;

	// disable $wgOut - we'll print out the page manually, taking the
	// body created by the form, plus the necessary Javascript files,
	// and turning them into an HTML page
	$wgOut->disable();
	$form = new EmbedWindowForm( $wgRequest );
	$form->execute();
	
	$target = $wgRequest->getText('target');
	
$text .= <<<END

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
{$wgOut->getScript()}
</head>
<body>
{$wgOut->getHTML()}
</body>
</html>

END;
	print $text;
}

/**
 * implements Special:UploadWindow
 * @addtogroup SpecialPage
 */
class EmbedWindowForm {
	/**#@+
	 * @access private
	 */
	var $mTarget, $mFilePath;

	/**
	 * Constructor : initialise object
	 * Get data POSTed through the form and assign them to the object
	 * @param $request Data posted.
	 */
	function EmbedWindowForm( &$request ) {
		$this->mTarget = $request->getText( 'target' );
	}
	
	/**
	 * start doing stuff
	 * @access public
	 */
	function execute(){
		global $wgOut, $wgUser;
		//if ( !$this->mTarget )
		//	$wgOut->addHtml('nothing defined!');
		//	return;
		$sk = $wgUser->getSkin();
		$nt = Title::newFromText($this->mTarget);
		$imageDescLink = $sk->makeKnownLinkObj( $nt, '','','','','target="_top"' );
		$image = Image::newFromTitle($nt);
		$imagePath = $image->getURL();
		
		$noEmbedMsg = wfMsg('smw_rm_noembed',$filePath);
		$descLinkText = wfMsg('smw_rm_embed_desc_link');
		
		$html = <<<END
		<div id="smw_rm_embedheadline" style="background-color:#455387;width:100%;padding:0px;margin:0px;text-align:center;font-size:1.2em;">
		<font color="white">The EmbedWindow for {$this->mTarget}</font></div>
		<div style="padding:5px;margin:5px;text-align:center;">{$descLinkText}{$imageDescLink}</div>
		<embed src="{$imagePath}" width="100%" height="520" autostart="0" showcontrols="1" showstatusbar="1" align="middle"/>
		<noembed>{$noEmbedMsg}</noembed>
END;
		$wgOut->addHTML($html);
	}
}
?>