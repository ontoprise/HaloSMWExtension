<?php

/**
 * @file
  * @ingroup RichMedia
  * 
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
	global $wgRequest, $wgOut, $wgUser, $wgServer, $wgXhtmlDefaultNamespace;

	// disable $wgOut - we'll print out the page manually, taking the
	// body created by the form, plus the necessary Javascript files,
	// and turning them into an HTML page
	$wgOut->disable();
	$sk = $wgUser->getSkin();
	$sk->initPage( $wgOut ); // need to call this to set skin name correctly
	$form = new EmbedWindowForm( $wgRequest );
	$form->execute();
	global $smwgHaloScriptPath, $smwgRMScriptPath;
	if ( method_exists( $wgOut, 'addModules' ) ) {
		$head_scripts = '';
		$body_scripts = $wgOut->getHeadScripts( $sk );
	} else {
		global $wgJsMimeType, $wgStylePath, $wgStyleVersion;
		$head_scripts = '';
		$body_scripts = '';
	}
	$user_js =<<<HTML
	<script type="{$wgJsMimeType}">
	
var headID = document.getElementsByTagName("head")[0];
var cssNode = document.createElement('link');
	
cssNode.type = 'text/css';
cssNode.rel = 'stylesheet';
cssNode.href = "{$smwgRMScriptPath}" + '/skins/richmedia_embedwindow.css';
cssNode.media = 'screen, projection';
headID.appendChild(cssNode);
</script>
HTML;
	$text = <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="{$wgXhtmlDefaultNamespace}"
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
$head_scripts
$user_js
</head>
<body>
{$wgOut->getHTML()}
$body_scripts
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
	var $mTarget, $mfullResSize, $mPdfHeight, $mPdfWidth;

	/**
	 * Constructor : initialise object
	 * Get data POSTed through the form and assign them to the object
	 * @param $request Data posted.
	 */
	function EmbedWindowForm( &$request ) {
		$this->mTarget = $request->getText( 'target' );
		$this->mfullResSize = $request->getCheck('fullRes');
	}
	
	/**
	 * start doing stuff
	 * @access public
	 */
	function execute(){
		global $wgOut, $wgUser, $smwgRMScriptPath, 
			$smwgRMEWEnableResizing, $smwgRMEWAllowScroll, $wgServer, $wgJsMimeType;
		if (isset( $this->mTarget ) && $this->mTarget != "" ) {
			$nt = Title::newFromText($this->mTarget);
		}
		else {
			$errorText = wfMsg('smw_rm_embed_notarget');
			$this->showError($errorText);
			return;
		}

		$image = wfLocalFile($nt);
		$imagePath = $image->getFullUrl();
		$embedWidth = $image->getWidth();
		$embedHeight = $image->getHeight();
		$embedMIMEType = $image->getMimeType();

		$fullRes_fit_section = "";
		list( $major, $minor ) = LocalFile::splitMime( $image->getMimeType() );
		if ($major == "image") {
			$embedObject = <<<END
			<table id="rmEWEmbedTable">
				<tr><td id="rmEWAlCenter">
					<img id="rmEWEmbeddedObject" src="{$imagePath}" width="{$embedWidth}" height="{$embedHeight}"/>
				</td></tr>
			</table>
END;
			if($smwgRMEWEnableResizing) {
				switch($smwgRMEWAllowScroll) {
					case true:
						$scroll = 1;
						break;
					case false:
						$scroll = 0;
						break;
				}
				
				$embedObject .= <<<HTML
	<script type="{$wgJsMimeType}">
	var screenHeight = top.screen.availHeight, screenWidth = top.screen.availWidth,
		paddingTotal = 20, extraHeight = 60; // some extra space to avoid Scrollbars
		extraWidth = 20, outer = top.jQuery('#fancybox-wrap'), 
		inner = top.jQuery('#fancybox-inner');

		if ({$scroll} !== 1 && ( {$embedWidth} > screenWidth || {$embedHeight} > screenHeight) ) {
		outer.css({ 
			height: screenHeight - 250, 
			width: screenWidth - 100 
		}); 
		inner.css({ 
			height: screenHeight - 220 - extraHeight, 
			width: screenWidth - 100 - extraWidth 
		});
		picture = document.getElementById('rmEWEmbeddedObject');
		picture.setAttribute( 'height', screenHeight - 220 - extraHeight - paddingTotal );
		picture.setAttribute( 'width', screenWidth - 100 - extraWidth - paddingTotal );
	} else {
		outer.css({ 
			height: {$embedHeight} + paddingTotal + extraHeight, 
			width: {$embedWidth} + paddingTotal + extraWidth 
		}); 
		inner.css({ 
			height: {$embedHeight} + extraHeight, 
			width: {$embedWidth} + extraWidth 
		});
	}
	top.jQuery.fancybox.center(); 
</script>
HTML;
			}
		} else {
			# for all other embedded objects
			// We could also use the new HTML5 tags <audio> and <video>.
			// But they are not supported by IE and Firefox is already playing
			// some audio files (i.e. ogg vorbis) without the need of an additional plugins.
			$noEmbedMsg = wfMsg('smw_rm_noembed', $embedMIMEType);
			$embedLoading = wfMsg('smw_rm_embedload');
			$embedObject = <<<END
			<div id="rmEWEmbedded">
				<object data="{$imagePath}" type="{$embedMIMEType}" id="rmEWEmbeddedObject"
					width="95%" height="85%" declare standby="{$embedLoading}">
					<p>{$noEmbedMsg}</p>
				</object>
			</div>
END;
		}

		$noEmbedMsg = wfMsg( 'smw_rm_noembed', $imagePath );
		$descText = wfMsg( 'smw_rm_embed_desctext' );
		$descLinkAlt = wfMsg( 'smw_rm_embed_desc_link', $nt->getBaseText() );
		$saveLinkText = wfMsg( 'smw_rm_embed_savetext' );
		$saveLinkAlt = wfMsg( 'smw_rm_embed_save', $nt->getBaseText() );

		$html = <<<END
		<div id="rmEWMainDiv">
			<span id="rmEWMainText">FileViewer</span>
			{$fullRes_fit_section}
			<span id="rmEWright">
				<a class="rmEWWhite" href="{$nt->getFullURL()}" title="{$descLinkAlt}" target="_top">>>&nbsp;{$descText}</a>
			</span>
		</div>
END;
		$html .= $embedObject;
		$wgOut->addHTML($html);

	}
	
	/**
	 * Show an Error
	 */
	function showError($description){
		global $wgOut;
		$wgOut->setPageTitle( wfMsg( "internalerror" ) );
		$wgOut->setRobotPolicy( "noindex,nofollow" );
		$wgOut->setArticleRelated( false );
		$wgOut->enableClientCache( false );
		$wgOut->addWikiText( $description );		
	}
}