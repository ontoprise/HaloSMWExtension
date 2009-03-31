<?php
class RMForm {

	static function createRichMediaForm(&$parser) {
		global $wgOut;

		$size = "35";
		$input_id = "myWpDestFile";
		$input_name = "myWpDestFile";
		$className="";
		$html =<<<END
		<form onsubmit="addWpDestFile()">
		<table><tr><td>
		<input id="$input_id" name="$input_name" type="text" value="" size="$size" class="$className" /></td>
END;
		$delimiter = "";

		$upload_window_page = SpecialPage::getPage('UploadWindow');
		$query_string = "sfInputID=$input_id";
		if ($delimiter != null)
		$query_string .= "&sfDelimiter=$delimiter";
		global $wgRequest;
		$article_title = $wgRequest->getText('title');
		global $smwgRMFormByNamespace;
		$smwgRMUploadName = $smwgRMFormByNamespace['RMUpload'];
		$query_string .= "&".$smwgRMUploadName."[RelatedArticles]=".$article_title;
		$upload_window_url = $upload_window_page->getTitle()->getFullURL($query_string);
		
		$upload_label = wfMsg('smw_rm_uploadheadline');
		$buttonText = wfMsg('smw_rm_formbuttontext');
		$html .= " <td><a id=\"link_id\" href=\"$upload_window_url\" title=\"$upload_label\" rel=\"iframe\" rev=\"width:600 height:520\"></a>
		<input type=\"submit\" onclick=\"addWpDestFile(); return false;\" value=\"$buttonText\"/></td>";
		$html .= "</tr></table></form>";
		
		//Used for displaying the upload successful message:
		
		$html .= "<div id=\"uploadsuccessdiv\" style=\"display: none; text-align:center;\"><br><br></div>";
		$uploadSuccessfullTitle = wfMsg('smw_rm_uploadsuccesfulltitle');
		$html .= "<a id=\"uploadsuccesslink\" href=\"#uploadsuccessdiv\" title=\"$uploadSuccessfullTitle\" rel=\"iframe\" rev=\"sameBox:true\"/>";
		
		return array($html, 'noparse' => true, 'isHTML' => true);
	}
}
?>