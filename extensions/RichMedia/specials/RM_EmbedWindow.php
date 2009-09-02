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
	var $mTarget, $mActualSize, $mPdfHeight, $mPdfWidth;

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
		
		if ( $this->mActualSize) {
			$embedWidth = $image->getWidth();
			$embedHeight = $image->getHeight();
			$actualNow = "font-weight:bold;";
			$fitNow = "";
		}
		else {
			$embedWidth = "100%";
			$embedHeight = "93%"; #because of the headline
			$actualNow = "";
			$fitNow = "font-weight:bold;";
		}
		list( $major, $minor ) = Image::splitMime( $image->getMimeType() );
		if ($major == "image") {
			# actualSize and FitToWindow links for images 
			$embedWindowPage = SpecialPage::getPage('EmbedWindow');
			$targetString = "target=$this->mTarget";
			$actualSizeString = "&actualSize=true";
			$actualSizeURL = $embedWindowPage->getTitle()->getFullURL($targetString.$actualSizeString);
			$fitToWindowURL = $embedWindowPage->getTitle()->getFullURL($targetString);
			$actual_fit_section = <<<END
				<td style="padding-left:5px;padding-right:5px;padding-top:0px;padding-bottom:0px;color:white;">
					<a href="{$actualSizeURL}" rel="sameBox:true" style="color:white;{$actualNow}">Actual size</a>
				</td>
				<td style="color:white;">|</td>
				<td style="border-right:1px dotted black;padding-left:5px;padding-right:10px;padding-top:0px;padding-bottom:0px;color:white;">
					<a href="$fitToWindowURL" rel="sameBox:true" style="color:white;{$fitNow}">Fit to Window</a>
				</td>
END;
			$embedObject = <<<END
				<img id="embedded_object" src="{$imagePath}" style="margin-top:5px;" width="{$embedWidth}" height="{$embedHeight}" align="middle"/>
END;
		}
		else{
			# actualSize and FitToWindow link for allother embedded objects
			$actual_fit_section = <<<END
				<td style="padding-left:5px;padding-right:5px;padding-top:0px;padding-bottom:0px;color:white;">
					<a href="#" onClick="javascript:getElementById('embedded_object').style.height=getTopWindowHeight();getElementById('embedded_object').style.width=getTopWindowWidth()" style="color:white;{$actualNow}">Actual size</a>
				</td>
				<td style="color:white;">|</td>
				<td style="border-right:1px dotted black;padding-left:5px;padding-right:10px;padding-top:0px;padding-bottom:0px;color:white;">
					<a href="#" onClick="javascript:getElementById('embedded_object').style.height='93%';getElementById('embedded_object').style.width='100%'" style="color:white;{$fitNow}">Fit to Window</a>
				</td>
END;
			$embedObject = <<<END
				<embed autostart="0" showcontrols="1" showstatusbar="1" id="embedded_object" src="{$imagePath}" style="margin-top:5px;width:{$embedWidth}; height:{$embedHeight}" align="middle"/>
END;
		}

		$noEmbedMsg = wfMsg( 'smw_rm_noembed', $filePath );
		$descLinkAlt = wfMsg( 'smw_rm_embed_desc_link', $nt->getBaseText() );
		$saveLinkAlt = wfMsg( 'smw_rm_embed_save', $nt->getBaseText() );
		//TODO: language!
		$html = <<<END
		<div style="border:1px solid black;">
			<table style="padding:0px;margin:0px" cellspacing="0" cellpadding="0">
				<tr style="top: 0px; right: 0px; bottom: 0px; left: 0px;background-color:#0091A6;margin:0px">
					<td style="left:0px;border-right:1px solid black;background-color:white;padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:0px;font-size:larger;font-weight:bold">
						FileViewer
					</td>
					<td style="padding-left:10px;padding-right:5px;padding-top:0px;padding-bottom:0px;color:white;font-weight:bold;">
						View:
					</td>
					{$actual_fit_section}
					<td style="padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:0px;color:white;font-weight:bold;">Save file</td>
					<td style="border-right:1px dotted black;padding-top:0px;padding-bottom:0px;padding-right:10px;color:white;">
						<a href="{$imagePath}">
							<img title="{$saveLinkAlt}" src="{$wgServer}{$smwgRMScriptPath}/skins/save_icon.png" style="vertical-align:middle;border-width:0px"></img></a>
					</td>
					<td style="padding-top:0px;padding-bottom:0px;padding-left:10px;padding-right:10px;color:white;font-weight:bold;">Description page:</td>
					<td style="border-right:1px solid black;padding-top:0px;padding-bottom:0px;padding-right:10px;color:white;">
						<a href="{$nt->getFullURL()}" target="_top">
							<img title="{$descLinkAlt}" src="{$wgServer}{$smwgRMScriptPath}/skins/desc_icon.png" style="vertical-align:middle;border-width:0px"></img></a>
					</td>
				</tr>
			</table>
		</div>
END;
		
		$html .= $embedObject;
		$wgOut->addHTML($html);
		
		$javascriptText = <<<END
		<script type="text/javascript">/* <![CDATA[ */
		function getTopWindowHeight() {
		var factor = 0.7;
	    if (top.window.innerHeight) {
	        return top.window.innerHeight*factor;
	    } else {
			//Common for IE
	        if (top.window.document.documentElement && top.window.document.documentElement.clientHeight) {
	            return typeof(top.window) == 'undefined' ? 0 : top.window.document.documentElement.clientHeight*factor;
	        } else {
				//Fallback solution for IE, does not always return usable values
				if (top.document.body && top.document.body.offsetHeight) {
					return typeof(win) == 'undefined' ? 0 : top.document.body.offsetHeight*factor;
		        }
			return 0;
			}
	    }
	}
	
	function getTopWindowWidth() {
	    var factor = 0.7;
	    if (top.window.innerWidth) {
	        return top.window.innerWidth*factor;
	    } else {
			//Common for IE
	        if (top.window.document.documentElement && top.window.document.documentElement.clientWidth) {
	            return typeof(top.window) == 'undefined' ? 0 : top.window.document.documentElement.clientWidth*factor;
	        } else {
				//Fallback solution for IE, does not always return usable values
				if (top.document.body && top.document.body.offsetWidth) {
					return typeof(top.window) == 'undefined' ? 0 : top.document.body.offsetWidth*factor;
		        }
			return 0;
			}
	    }
	}
/* ]]> */ </script>

END;
		$wgOut->addHTML($javascriptText);

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