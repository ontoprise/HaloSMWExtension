<?php
class RMForm {

	/**
	 * Paser function that creates the HTML form for the media list
	 *
	 * @param array $parameter
	 * @return HTML
	 */
	static function createRichMediaForm(&$parameter) {
		global $wgOut, $wgParser;

		$html =<<<END
<form onsubmit="fb.loadAnchor($('link_id'));return false;">
END;
		$uploadWindowPage = SpecialPage::getPage('UploadWindow');
		
		global $wgRequest;
		$articleTitle = urlencode($wgRequest->getText('title'));
		global $smwgRMFormByNamespace;
		$smwgRMUploadFormName = $smwgRMFormByNamespace['RMUpload'];
		$queryString = "&".$smwgRMUploadFormName."[RelatedArticles]=".$articleTitle."&wpIgnoreWarning=true";
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getFullURL($queryString);
		
		$uploadLabel = wfMsgNoTrans('smw_rm_uploadheadline');
		$buttonText = '>> ' . wfMsgNoTrans('smw_rm_formbuttontext');
		$html .= "<a id=\"link_id\" href=\"$uploadWindowUrl\" title=\"$uploadLabel\" rel=\"iframe\" rev=\"width:600 height:660\"></a><input style=\"font-weight:bold\" type=\"submit\" value=\"$buttonText\"/></form>";

		//return array($html, 'noparse' => true, 'isHTML' => true);
		return $wgParser->insertStripItem( $html, $wgParser->mStripState );
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
	 * @return HTML
	 */
	static function createRichMediaLink(&$parameters) {
		global $wgOut, $wgParser, $wgRequest, $smwgRMFormByNamespace;
		$rMUploadFormName = $smwgRMFormByNamespace['RMUpload'];;

		if ( array_key_exists( 0, $parameters ) && isset( $parameters[0] ) )
			$linkText = $parameters[0];
		else
			$linkText = 'Link';

		if ( array_key_exists( 1, $parameters ) && isset( $parameters[1] ) )
			$linkTitle = $parameters[1];
		else
			$linkTitle = wfMsgNoTrans('smw_rm_uploadheadline');
		
		
		$queryString = "";
		
		$queryParameters = explode('&', $parameters[2]);
		#cylce throuh other paramters and check if no preview value is blank!
		for ($i = 0; $i < count($queryParameters); $i++) {
			$queryParameter = $queryParameters[$i];
			
			if ( $queryString != "" )
				$combine = "&";
			$querySubParameter = explode('=', $queryParameter);
			if ( count ($querySubParameter) == 2) {
				if ( $querySubParameter[1] != "" ) {
					$queryString .= $combine.$querySubParameter[0]."=".$querySubParameter[1];
				}
			}
		}
		
		$rev = 'width:600 height:660';
		$queryString .= "&wpIgnoreWarning=true";
		$uploadWindowPage = SpecialPage::getPage('UploadWindow');
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getFullURL($queryString);

		$html = "<a href=\"$uploadWindowUrl\" title=\"$linkTitle\" rel=\"iframe\" rev=\"$rev\">$linkText</a>";

		return $wgParser->insertStripItem( $html, $wgParser->mStripState );
	}
	
	/**
	 * Parser function that creates a link to the EmbedWindow
	 *
	 * @param array $parameter
	 * @return HTML
	 */
	static function createRichMediaEmbedWindowLink(&$parameter) {
		global $wgOut, $wgParser, $wgRequest;

		if ( array_key_exists( 0, $parameter ) && isset( $parameter[0] ) )
			$link_name = $parameter[0];
		else
			$link_name = 'Link';

		if ( array_key_exists( 1, $parameter ) && isset( $parameter[1] ) )
			$link_title = $parameter[1];
		else
			$link_title = wfMsgNoTrans('smw_rm_uploadheadline');
		
		if ( array_key_exists( 2, $parameter ) && isset( $parameter[2] ) )
			$rev_width = 'width:' . $parameter[2];
		else
			$rev_width = 'width:700';

		if ( array_key_exists( 3, $parameter ) && isset( $parameter[3] ) )
			$rev_height = 'height:' . $parameter[3];
		else
			$rev_height = 'height:500';
		
		$rev = $rev_width . ' ' . $rev_height;
		
		$queryString = "target=$link_name";
		$embedWindowPage = SpecialPage::getPage('EmbedWindow');
		$embedWindowUrl = $embedWindowPage->getTitle()->getFullURL($queryString);

		$html = "<a href=\"$embedWindowUrl\" title=\"$link_title\" rel=\"iframe\" rev=\"$rev\">$link_title</a>";
		
		return $wgParser->insertStripItem( $html, $wgParser->mStripState );
	}
}
?>