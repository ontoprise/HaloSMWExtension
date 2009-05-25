<?php
class RMForm {

	static function createRichMediaForm(&$parser) {
		global $wgOut, $wgParser;

		$size = "35";
		$inputId = "myWpDestFile";
		$inputName = "myWpDestFile";
		
		$html =<<<END
<form onsubmit="fb.loadAnchor($('link_id'));return false;">
END;
		$delimiter = "";

		$uploadWindowPage = SpecialPage::getPage('UploadWindow');
		$queryString = "sfInputID=$inputId";
		if ($delimiter != null)
		$queryString .= "&sfDelimiter=$delimiter";
		global $wgRequest;
		$articleTitle = $wgRequest->getText('title');
		global $smwgRMFormByNamespace;
		$smwgRMUploadFormName = $smwgRMFormByNamespace['RMUpload'];
		$queryString .= "&".$smwgRMUploadFormName."[RelatedArticles]=".$articleTitle."&wpIgnoreWarning=true";
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getFullURL($queryString);
		
		$uploadLabel = wfMsgNoTrans('smw_rm_uploadheadline');
		$buttonText = wfMsgNoTrans('smw_rm_formbuttontext');
		$html .= "<a id=\"link_id\" href=\"$uploadWindowUrl\" title=\"$uploadLabel\" rel=\"iframe\" rev=\"width:600 height:660\"></a><input type=\"submit\" value=\"$buttonText\"/></form>";

		//return array($html, 'noparse' => true, 'isHTML' => true);
		return $wgParser->insertStripItem( $html, $wgParser->mStripState );
	}
}
?>