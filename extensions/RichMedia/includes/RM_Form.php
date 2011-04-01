<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the RichMedia-Extension.
*
*   The RichMedia-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The RichMedia-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup RichMedia
 *
 * This class handles all Parser functions created and used by the Rich Media extension.
 *
 * @author Benjamin Langguth
 */
class RMForm {

	/**
	 * Parser function that creates the HTML form for the media list
	 *
	 * @param array $parameter
	 * @return HTML
	 */
	static function createRichMediaForm($parser, &$parameter) {
		global $wgOut;

		$uploadWindowPage = SpecialPage::getPage('UploadWindow');

		global $wgRequest;
		$articleTitle = urlencode($wgRequest->getText('title'));
		global $smwgRMFormByNamespace;
		$smwgRMUploadFormName = $smwgRMFormByNamespace['RMUpload'];
		$queryString = $smwgRMUploadFormName."[RelatedArticles]=".$articleTitle."&wpIgnoreWarning=true";
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getFullURL($queryString);

		$fancyboxClass = 'rmlink';
		$uploadLabel = wfMsgNoTrans('smw_rm_uploadheadline');
		$buttonText = '>> ' . wfMsgNoTrans('smw_rm_formbuttontext');
		$html = "<input class=\"$fancyboxClass rmUploadButton\" type=\"button\" id=\"rmFormButton\"".
			"value=\"$buttonText\" title=\"$uploadLabel\" />";

		$fancybox_js =<<<END
<script type="text/javascript">
/* <![CDATA[ */
var wgRMUploadUrl = "{$uploadWindowUrl}";
/* ]]> */
</script>
END;
		SMWOutputs::requireHeadItem('rmlinkheaditem', $fancybox_js);

		return $parser->insertStripItem( $html, $parser->mStripState );
	}

	/**
	 * Parser funtion that creates a link to the upload overlay
	 *
	 *
	 * @param array $parameters
	 * 		1. The link text
	 * 		2. The link title
	 * 		3. the set of values that you want passed in through the query string to the upload form.
	 * 			It should look like a typical URL query string
	 * @return HTML the text embraced by "richmedialink"-tags that will be removed again later in createRichMediaLinkAfterTidy
	 */
	static function createRichMediaLink($parser, &$parameters) {
		global $wgOut, $wgRequest, $smwgRMFormByNamespace;
		$rMUploadFormName = $smwgRMFormByNamespace['RMUpload'];

		if ( array_key_exists( 0, $parameters ) && isset( $parameters[0] ) ) {
			$linkText = $parameters[0];
		} else {
			$linkText = 'Link';
		}
		if ( array_key_exists( 1, $parameters ) && isset( $parameters[1] ) ) {
			$linkTitle = $parameters[1];
		} else {
			$linkTitle = wfMsgNoTrans('smw_rm_uploadheadline');
		}

		$queryString = "";
		if( array_key_exists( 2, $parameters ) && isset( $parameters[2] ) ) {
			$queryParameters = explode('&', $parameters[2]);
			#cylce throuh other paramters and check if no preview value is blank!
			for ($i = 0; $i < count($queryParameters); $i++) {
				$queryParameter = $queryParameters[$i];

				if ( $queryString != "" ) {
					$combine = "&";
				}
				else {
					$combine ="";
				}
				$querySubParameter = explode('=', $queryParameter);
				if ( count ($querySubParameter) == 2) {
					if ( $querySubParameter[1] != "" ) {
						$queryString .= $combine.$querySubParameter[0]."=".urlencode($querySubParameter[1]);
					}
				}
			}
		}

		$rev = 'width:600 height:660';
		$queryString .= "&wpIgnoreWarning=true";
		$uploadWindowPage = SpecialPage::getPage('UploadWindow');
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getFullURL($queryString);
		$fancyboxClass = 'rmAlink';

		$output = "<a href=\"$uploadWindowUrl\" title=\"$linkTitle\" class=\"$fancyboxClass\">$linkText</a>";

		global $smwgRMMarkerList;
		$markercount = count($smwgRMMarkerList);
		$marker = "x-richmedialink-x".$markercount."-x-richmedialink-x";
		$smwgRMMarkerList[$markercount] = $output;
		return $marker;
	}

	/**
	 * Parser functions that finds the markers created by function createRichMediaLink
	 * in $text and replaces them with the actual output.
	 *
	 * @param Parser $parser
	 * @param $text
	 * @return bool
	 */
	static function createRichMediaLinkAfterTidy(&$parser, &$text) {
		global $smwgRMMarkerList;
		$keys = array();
		$marker_count = count($smwgRMMarkerList);

		for ($i = 0; $i < $marker_count; $i++) {
			$keys[] = 'x-richmedialink-x' . $i . '-x-richmedialink-x';
		}

		$text = str_replace($keys, $smwgRMMarkerList, $text);
		return true;
	}

	/**
	 * Parser function that creates a link to the EmbedWindow
	 *
	 * The file extension needs to be contained in (global) $smwgRMPreviewWhitelist
	 * or $smwgRMIgnoreWhitelistForPF must be set to true.
	 * Otherwise a link to the original file details page is generated.
	 *
	 * @param array $parameter
	 * @return HTML
	 */
	static function createRichMediaEmbedWindowLink($parser, &$parameter) {
		global $wgOut, $wgRequest, $wgUser;
		global $wgRMImagePreview, $smwgRMPreviewWhitelist, $smwgRMIgnoreWhitelistForPF;

		if ( array_key_exists( 0, $parameter ) && isset( $parameter[0] ) ) {
			$link_name = $parameter[0];
		} else {
			$link_name = 'Link';
		}
		if ( array_key_exists( 1, $parameter ) && isset( $parameter[1] ) ) {
			$link_title = $parameter[1];
		} else {
			$link_title = wfMsgNoTrans('smw_rm_uploadheadline');
		}
		if ( array_key_exists( 2, $parameter ) && isset( $parameter[2] ) ) {
			$rev_width = 'width:' . $parameter[2];
		} else {
			$rev_width = 'width:700';
		}
		if ( array_key_exists( 3, $parameter ) && isset( $parameter[3] ) ) {
			$rev_height = 'height:' . $parameter[3];
		} else {
			$rev_height = 'height:500';
		}

		$title = Title::newFromText($link_name);
		$temp_var = $title->getNamespace();
		$file = wfFindFile($title);
		if ( !$file ) {
			return wfMsgNoTrans( 'smw_rm_filenotfound', $link_name);
		}
		$ext = $file->getExtension();

		RMNamespace::isImage( $temp_var, $rMresult );
		if ( $rMresult && ( $smwgRMIgnoreWhitelistForPF
			|| ( is_array( $smwgRMPreviewWhitelist ) 
			&& in_array( $ext, $smwgRMPreviewWhitelist ) ) ) )
		{
			$queryString = "target=".urlencode($link_name);
			$embedWindowPage = SpecialPage::getPage('EmbedWindow');
			$embedWindowUrl = $embedWindowPage->getTitle()->getFullURL($queryString);
			$html = "<a href=\"$embedWindowUrl\" title=\"$link_title\" class=\"rmAlink\">$link_title</a>";
		} else {
			$html = $wgUser->getSkin()->link($title, $link_title);
		}
		return $parser->insertStripItem( $html, $parser->mStripState );
	}
}