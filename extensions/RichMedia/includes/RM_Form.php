<?php
class RMForm {

	static function createRichMediaForm(&$parser) {
		global $wgOut;

		$size = "35";
		$inputId = "myWpDestFile";
		$inputName = "myWpDestFile";
		$html =<<<END
		<form onsubmit="richMediaPage.addWpDestFile();return false;">
		<table><tr><td>
		<input id="$inputId" name="$inputName" type="text" value="" size="$size" /></td>
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
		$queryString .= "&".$smwgRMUploadFormName."[RelatedArticles]=".$articleTitle;
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getFullURL($queryString);
		
		$uploadLabel = wfMsg('smw_rm_uploadheadline');
		$buttonText = wfMsg('smw_rm_formbuttontext');
		$html .= " <td><a id=\"link_id\" href=\"$uploadWindowUrl\" title=\"$uploadLabel\" rel=\"iframe\" rev=\"width:600 height:600\"></a>
		<input type=\"submit\" value=\"$buttonText\"/></td>";
		$html .= "</tr></table></form>";

		return array($html, 'noparse' => true, 'isHTML' => true);
	}
}
?>