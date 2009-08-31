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

	// disable $wgOut - we'll print out the page manually, taking the
	// body created by the form, plus the necessary Javascript files,
	// and turning them into an HTML page
	$wgOut->disable();
	$form = new EmbedWindowForm( $wgRequest );
	$form->execute();
	global $smwgHaloScriptPath, $smwgRMScriptPath;
	$prototype_include = "<script type=\"text/javascript\" src=\"{$smwgHaloScriptPath}/scripts/prototype.js?$wgStyleVersion\"></script>";
	$text .= <<<END

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
{$wgOut->getScript()}
$prototype_include
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
	var $mTarget, $mActualSize;

	/**
	 * Constructor : initialise object
	 * Get data POSTed through the form and assign them to the object
	 * @param $request Data posted.
	 */
	function EmbedWindowForm( &$request ) {
		$this->mTarget = $request->getText( 'target' );
		$this->mActualSize = $request->getCheck('actualSize');
	}
	
	/**
	 * start doing stuff
	 * @access public
	 */
	function execute(){
		global $wgOut, $wgUser, $smwgRMScriptPath, $wgServer;
		//if ( !$this->mTarget )
		//	$wgOut->addHtml('nothing defined!');
		//	return;
		$sk = $wgUser->getSkin();
		if (isset( $this->mTarget ) && $this->mTarget != "" ) {
			$nt = Title::newFromText($this->mTarget);
		}
		
		else {
			$errorText = wfMsg('smw_rm_embed_notarget');;
			$this->showError($errorText);
			return;
		}
		
		$image = Image::newFromTitle($nt);
		$imagePath = $image->getURL();
		
		list( $major, $minor ) = Image::splitMime( $image->getMimeType() );
		
		if ( $major == "image" && $this->mActualSize ) {
			$embedWidth = $image->getWidth();
			$embedHeight = $image->getHeight();
			$actualNow = "font-weight:bold;";
			$fitNow = "";
		}
		else {
			$embedWidth = "100%";
			$embedHeight = "100%";
			$actualNow = "";
			$fitNow = "font-weight:bold;";
		}
		
		
		$noEmbedMsg = wfMsg( 'smw_rm_noembed', $filePath );
		$descLinkAlt = wfMsg( 'smw_rm_embed_desc_link', $nt->getBaseText() );
		$saveLinkAlt = wfMsg( 'smw_rm_embed_save', $nt->getBaseText() );
		
		$embedWindowPage = SpecialPage::getPage('EmbedWindow');
		$targetString = "target=$this->mTarget";
		$actualSizeString = "&actualSize=true";
		$actualSizeURL = $embedWindowPage->getTitle()->getFullURL($targetString.$actualSizeString);
		
		$fitToWindowURL = $embedWindowPage->getTitle()->getFullURL($targetString);
		//TODO: language!
		$html = <<<END
		<div style="border:1px solid black;">
			<table style="padding:0px;margin:0px">
				<tr style="top: 0px; right: 0px; bottom: 0px; left: 0px;background-color:#0091A6;margin:0px">
					<td style="left:0px;border-right:1px solid black;background-color:white;padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:0px;font-size:larger;font-weight:bold">
						FileViewer
					</td>
					<td style="padding-left:10px;padding-right:5px;padding-top:0px;padding-bottom:0px;color:white;font-weight:bold;">
						View:
					</td>
					<td style="padding-left:5px;padding-right:5px;padding-top:0px;padding-bottom:0px;color:white;">
						<a href="{$actualSizeURL}" rel="sameBox:true" style="color:white;{$actualNow}">Actual size</a>
					</td>
					<td style="color:white;">|</td>
					<td style="border-right:1px dotted black;padding-left:5px;padding-right:10px;padding-top:0px;padding-bottom:0px;color:white;">
						<a href="$fitToWindowURL" rel="sameBox:true" style="color:white;{$fitNow}">Fit to Window</a>
					</td>
					<td style="padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:0px;color:white;font-weight:bold;">Save file</td>
					<td style="border-right:1px dotted black;padding-top:0px;padding-bottom:0px;padding-right:10px;color:white;">
						<a href="{$imagePath}">
							<img title="$descLinkAlt" src="{$wgServer}{$smwgRMScriptPath}/skins/this_file_pointer.png" style="vertical-align:middle;border-width:0px"></img></a>
					</td>
					<td style="padding-top:0px;padding-bottom:0px;padding-left:10px;padding-right:10px;color:white;font-weight:bold;">Description page:</td>
					<td style="border-right:1px solid black;padding-top:0px;padding-bottom:0px;padding-right:10px;color:white;">
						<a href="{$nt->getFullURL()}" target="_top" alt="{$descLinkAlt}">
							<img title="{$saveLinkAlt}" src="{$wgServer}{$smwgRMScriptPath}/skins/this_file_pointer.png" style="vertical-align:middle;border-width:0px"></img></a>
					</td>
				</tr>
			</table>
		</div>
END;
		
		$html .= <<<END
		<embed style="margin-top:5px" src="{$imagePath}" width="{$embedWidth}" height="{$embedHeight}" autostart="0" showcontrols="1" showstatusbar="1" align="middle"/>
		<noembed>{$noEmbedMsg}</noembed>
END;
		$javascriptText = "$('')";
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