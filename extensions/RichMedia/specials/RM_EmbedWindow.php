<?php
/*
 * Copyright (C) Vulcan Inc.
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


/**
 * @file
  * @ingroup RichMedia
  *
  * RM_EmbedWindow - used for displaying various types of files inline.
 *
 * @addtogroup SpecialPage
 *
 * @author Benjamin Langguth
 * @author michel from our SMWforum community
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the RichMedia extension. It is not a valid entry point.\n" );
}

class RMEmbedWindow extends UnlistedSpecialPage {

	/**
	 * Constructor : initialise object
	 * @param WebRequest $request Data posted.
	 */
	public function __construct( $request = null ) {
		parent::__construct( 'EmbedWindow' );
	}

	/**
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute( $query ) {
		$this->setHeaders();
		$this->outputHeader();

		doSpecialEmbedWindow();
	}
}

/**
 * Entry point
 */
function doSpecialEmbedWindow() {
	wfProfileIn( __METHOD__ . ' [Rich Media]' );
	global $wgRequest, $wgOut, $wgUser, $wgServer, $wgXhtmlDefaultNamespace,
		$wgJsMimeType, $wgStylePath, $wgStyleVersion, $smwgHaloScriptPath, $smwgRMScriptPath;

	// disable $wgOut - we'll print out the page manually, taking the
	// body created by the form, plus the necessary Javascript files,
	// and turning them into an HTML page
	$wgOut->disable();

	$sk = $wgUser->getSkin();
	$sk->initPage( $wgOut ); // need to call this to set skin name correctly
	$form = new EmbedWindowForm( $wgRequest );
	$form->execute();
	$user_js =<<<HTML
<script type="{$wgJsMimeType}">
	var headID = document.getElementsByTagName( 'head' )[0],
		cssNode = document.createElement( 'link' );

	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = "{$smwgRMScriptPath}" + '/skins/richmedia_embedwindow.css';
	cssNode.media = 'screen, projection';
	headID.appendChild(cssNode);
</script>
HTML;
	$text = <<<END
<html xmlns="{$wgXhtmlDefaultNamespace}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	$user_js
</head>
<body>
	{$wgOut->getHTML()}
</body>
</html>

END;
	print $text;
	wfProfileOut( __METHOD__ . ' [Rich Media]' );
}

/**
 * implements Special:UploadWindow
 * @addtogroup SpecialPage
 */
class EmbedWindowForm {
	/**
	 * Constructor : initialise object
	 * Get data POSTed through the form and assign them to the object
	 * @param WebRequest $request Data posted.
	 */
	public function __construct( &$request ) {
		$this->mTarget = $request->getText( 'target' );
		$this->mfullResSize = $request->getCheck('fullRes');
	}

	/**
	 * Variables
	 * @access private
	 */
	var $mTarget, $mfullResSize, $mPdfHeight, $mPdfWidth;

	/**
	 * start doing stuff
	 * @access public
	 */
	function execute(){
		wfProfileIn( __METHOD__ . ' [Rich Media]' );
		global $wgOut, $wgUser, $smwgRMScriptPath,
			$smwgRMEWEnableResizing, $smwgRMEWAllowScroll,
			$smwgRMEWMinWidth, $smwgRMEWMinHeight,
			$wgServer, $wgJsMimeType;

		if (isset( $this->mTarget ) && $this->mTarget != "" ) {
			$nt = Title::newFromText($this->mTarget);
		} else {
			$errorText = wfMsg('smw_rm_embed_notarget');
			$this->showError($errorText);
			wfProfileOut( __METHOD__ . ' [Rich Media]' );
			return;
		}

		$image = wfLocalFile($nt);
		$imagePath = $image->getFullUrl();
		$embedWidth = $image->getWidth();
		$embedHeight = $image->getHeight();
		$embedMIMEType = $image->getMimeType();

		$fullRes_fit_section = '';
		list( $major, $minor ) = LocalFile::splitMime( $image->getMimeType() );
		if( $major == 'image' ) {
			$embedObject = <<<END
			<table id="rmEWEmbedTable">
				<tr><td id="rmEWAlCenter">
					<img id="rmEWEmbeddedObject" src="{$imagePath}"
						width="{$embedWidth}" height="{$embedHeight}"/>
				</td></tr>
			</table>
END;

			if( $smwgRMEWEnableResizing ) {
				$newWidth = $embedWidth;
				$newHeight = $embedHeight;
				if( $embedWidth < $smwgRMEWMinWidth ) {
					$newWidth = $smwgRMEWMinWidth;
				}
				if ( $embedHeight < $smwgRMEWMinHeight ) {
					$newHeight = $smwgRMEWMinHeight;
				}
				switch( $smwgRMEWAllowScroll ) {
					case true:
						$scroll = 1;
						break;
					case false:
						$scroll = 0;
						break;
				}

				$embedObject .= <<<HTML
<script type="{$wgJsMimeType}">
	var outer = top.jQuery('#fancybox-wrap'),
		inner = top.jQuery('#fancybox-content'),
		// size of all the borders, paddings and margins around the embedded object.
		// defined in CSS file. Includes 10px padding, 8px margin, 2px borderspacing
		// (all both left&right), 22px main text height
		paddingHeight = 67,
		// includes 10px padding, 8px margin, 2px borderspacing (all both left&right)
		paddingWidth = 40,
		// size of overlay window should be relative to browser window size.
		// Minimum size can be specified
		windowHeight = Math.max( top.window.outerHeight - 200,
			{$smwgRMEWMinHeight} + paddingHeight + 20 ),
		windowWidth  = Math.max( top.window.outerWidth - 100,
			{$smwgRMEWMinWidth} + paddingWidth + 20 ),
		// the maximum size an image should have after resize
		imageMaxHeight = windowHeight - paddingHeight,
		imageMaxWidth  = windowWidth - paddingWidth,
		scaleHeight,
		scaleWidth,
		scaleFactor;

	// now check if we have to resize
	// --> image size > image max size in one direction at least
	if ( {$scroll} !== 1 && ( {$embedWidth} > imageMaxWidth ||
		{$embedHeight} > imageMaxHeight) )
	{
		// first, calculate the scaling factors required to resize image
		scaleHeight = imageMaxHeight / {$embedHeight};
		scaleWidth = imageMaxWidth / {$embedWidth};

		// dimension which has to be scaled down most wins!
		scaleFactor = Math.min( scaleHeight, scaleWidth );

		// now set outer and inner size. If one dimension falls below minimum size,
		// then set to minimum. This avoids too little overlays and takes care
		// that the caption line is displayed correctly
		outer.css({
			height: Math.max( {$embedHeight} * scaleFactor,
				{$smwgRMEWMinHeight} ) + paddingHeight + 20,
			width: Math.max( {$embedWidth} * scaleFactor,
				{$smwgRMEWMinWidth} ) + paddingWidth + 20
		});
		inner.css({
			height: Math.max( {$embedHeight} * scaleFactor,
				{$smwgRMEWMinHeight} ) + paddingHeight,
			width: Math.max( {$embedWidth} * scaleFactor,
				{$smwgRMEWMinWidth} ) + paddingWidth
		});
		picture = document.getElementById( 'rmEWEmbeddedObject' );
		picture.setAttribute( 'height', {$embedHeight} * scaleFactor );
		picture.setAttribute( 'width', {$embedWidth} * scaleFactor );
	} else {
		// adjusted to match above variables and calculations
		outer.css({
			height: {$newHeight} + paddingHeight + 20,
			width: {$newWidth} + paddingWidth + 20
		});
		inner.css({
			height: {$newHeight} + paddingHeight,
			width: {$newWidth} + paddingWidth
		});
	}
	top.jQuery.fancybox.center( true );
</script>
HTML;
			}
		} else {
			# for all other embedded objects
			// We could also use the new HTML5 tags <audio> and <video>.
			// But they are not supported by IE and Firefox is already playing
			// some audio files (i.e. ogg vorbis) without the need of an additional plugins.
			$noEmbedMsg = wfMsg( 'smw_rm_noembed', $embedMIMEType );
			$embedLoading = wfMsg( 'smw_rm_embedload' );
			$embedObject = <<<END
			<div id="rmEWEmbedded">
				<object data="{$imagePath}" type="{$embedMIMEType}"
					id="rmEWEmbeddedObject" width="95%" height="85%"
					standby="{$embedLoading}">
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
				<a class="rmEWWhite" href="{$nt->getFullURL()}"
					title="{$descLinkAlt}" target="_top">>>&nbsp;{$descText}
				</a>
			</span>
		</div>
END;
		$html .= $embedObject;
		$wgOut->addHTML( $html );

		wfProfileOut( __METHOD__ . ' [Rich Media]' );
	}

	/**
	 * Show an Error
	 */
	function showError( $description ) {
		global $wgOut;
		$wgOut->setPageTitle( wfMsg( 'internalerror' ) );
		$wgOut->setRobotPolicy( 'noindex,nofollow' );
		$wgOut->setArticleRelated( false );
		$wgOut->enableClientCache( false );
		$wgOut->addWikiText( $description );
	}
}