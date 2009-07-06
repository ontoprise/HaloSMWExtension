<?php
class RMForm {

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
	
static function createRichMediaLink(&$parameter) {
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
			$rev_width = 'width:600';

		if ( array_key_exists( 3, $parameter ) && isset( $parameter[3] ) )
			$rev_height = 'height:' . $parameter[3];
		else
			$rev_height = 'height:660';
		
		$rev = $rev_width . ' ' . $rev_height;
		
		global $smwgRMFormByNamespace;
		$smwgRMUploadFormName = $smwgRMFormByNamespace['RMUpload'];
		$queryString = "wpIgnoreWarning=true";
		$uploadWindowPage = SpecialPage::getPage('UploadWindow');
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getFullURL($queryString);

		$html = "<a href=\"$uploadWindowUrl\" title=\"$link_title\" rel=\"iframe\" rev=\"$rev\">$link_name</a>";

		return $wgParser->insertStripItem( $html, $wgParser->mStripState );
	}
}
?>