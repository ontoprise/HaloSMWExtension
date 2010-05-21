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
	global $wgRequest, $wgOut, $wgUser, $wgServer, $wgStyleVersion,
		$wgJsMimeType;

	// disable $wgOut - we'll print out the page manually, taking the
	// body created by the form, plus the necessary Javascript files,
	// and turning them into an HTML page
	$wgOut->disable();
	$form = new EmbedWindowForm( $wgRequest );
	$form->execute();
	global $smwgHaloScriptPath, $smwgRMScriptPath;
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
	$prototype_include = "<script type=\"text/javascript\" src=\"{$smwgRMScriptPath}/scripts/prototype.js?$wgStyleVersion\"></script>";
	$text = <<<END
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
{$wgOut->getScript()}
$prototype_include
$user_js
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
		global $wgOut, $wgUser, $smwgRMScriptPath, $wgServer;
		$sk = $wgUser->getSkin();
		if (isset( $this->mTarget ) && $this->mTarget != "" ) {
			$nt = Title::newFromText($this->mTarget);
		}
		else {
			$errorText = wfMsg('smw_rm_embed_notarget');
			$this->showError($errorText);
			return;
		}
		
		$image = Image::newFromTitle($nt);
		$imagePath = $image->getURL();
		$embedWidth = $image->getWidth();
		$embedHeight = $image->getHeight();
		
		$imageToSmall = false;
		if ($embedHeight <= 500 || $embedWidth <= 700) {
			$imageToSmall = true;
		}
		
		if ( $this->mfullResSize) {
			$fullResNow = "font-weight:bold;";
			$fitNow = "";
		}
		else {
			$embedWidth = "670px;";
			$embedHeight = "450px;";
			$fullResNow = "";
			$fitNow = "font-weight:bold;";
		}
		list( $major, $minor ) = Image::splitMime( $image->getMimeType() );
		if ($major == "image") {
			if (!$imageToSmall) {
				# fullResSize and FitToWindow links for images
				$embedWindowPage = SpecialPage::getPage('EmbedWindow');
				$targetString = "target=".urlencode($this->mTarget);
				$fullResSizeString = "&fullRes=true";
				$fullResSizeURL = $embedWindowPage->getTitle()->getFullURL($targetString.$fullResSizeString);
				$fitToWindowURL = $embedWindowPage->getTitle()->getFullURL($targetString);
				$fullResText = wfMsg('smw_rm_embed_fullres');
				$fitWinText = wfMsg('smw_rm_embed_fittowindow');
				$viewText = wfMsg('smw_rm_embed_view');
				$fullRes_fit_section = <<<END
				<td id="rmEWtdViewText">
				{$viewText}:
				</td>
				<td id="rmEWtdFullRes">
				<a class="rmEWWhite" href="{$fullResSizeURL}" rel="sameBox:true" style="{$fullResNow}">{$fullResText}</a>
				</td>
				<td class="rmEWSep">|</td>
				<td id="rmEWtdFitTo">
				<a class="rmEWWhite" href="$fitToWindowURL" rel="sameBox:true" style="{$fitNow}">{$fitWinText}</a>
				</td>
END;
			}
			else {
				$embedWidth = $image->getWidth();
				$embedHeight = $image->getHeight();
			}
			$embedObject = <<<END
			<table id="rmEWEmbedTable">
				<tr><td id="rmEWAlCenter">
					<img id="rmEWEmbeddedObject" src="{$imagePath}" width="{$embedWidth}" height="{$embedHeight}"/>
				</td></tr>
			</table>
END;
		}
		else{
			# fullResSize and FitToWindow link for allother embedded objects
			$fullRes_fit_section = "";
			$embedObject = <<<END
				<embed autostart="0" showcontrols="1" showstatusbar="1" id="rmEWEmbeddedObject" src="{$imagePath}" style="width:{$embedWidth}; height:{$embedHeight}" align="middle"/>
END;
		}

		$noEmbedMsg = wfMsg( 'smw_rm_noembed', $imagePath );
		$descText = wfMsg( 'smw_rm_embed_desctext' );
		$descLinkAlt = wfMsg( 'smw_rm_embed_desc_link', $nt->getBaseText() );
		$saveLinkText = wfMsg( 'smw_rm_embed_savetext' );
		$saveLinkAlt = wfMsg( 'smw_rm_embed_save', $nt->getBaseText() );

		$html = <<<END
		<div id="rmEWMainDiv">
			<table id="rmEWMainTable" cellspacing="0" cellpadding="0">
				<tr id="rmEWtr">
					<td class="rmEWtd" id="rmEWtdL">
						FileViewer
					</td>
					{$fullRes_fit_section}
					<td>&nbsp;</td>
					<!--<td class="rmEWtd" id="rmEWtdM">
						<a href="{$imagePath}" class="rmEWWhite" title="{$saveLinkAlt}">{$saveLinkText}
							<img id="rmEWSaveIcon" title="{$saveLinkAlt}" src="{$wgServer}{$smwgRMScriptPath}/skins/save_icon.png"></img>
						</a>
					</td>-->
					<td class="rmEWtd" id="rmEWtdR">
						<a class="rmEWWhite" href="{$nt->getFullURL()}" title="{$descLinkAlt}" target="_top">>>&nbsp;{$descText}
						</a>
					</td>
				</tr>
			</table>
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
?>