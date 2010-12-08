<?php

global $smwgDIIP;
require_once ("$smwgDIIP/IAI/WUM/WUM_ThreeWayBasedMerger.php");
require_once ("$smwgDIIP/IAI/WUM/WUM_SectionBasedMerger.php");
require_once ("$smwgDIIP/IAI/WUM/WUM_TableBasedMerger.php");
require_once ("$smwgDIIP/IAI/WUM/WUM_Settings.php");


global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'wumInitExtension';

define('WUM_TAG_OPEN', '<upc>');
define('WUM_TAG_CLOSE', '</upc>');

function wumInitExtension(){
	global $wgHooks;
	$wgHooks['APIEditBeforeSave'][] = 'wum_doAPIEdit';
	$wgHooks['ParserBeforeInternalParse'][] = 'wumUPParserHook';

}

/**
 * This method is the main entry point for merging WP and UP articles.
 * It is called by WP if one edits an article there.
 *
 * @param unknown_type $editPage
 * @param unknown_type $text
 * @param unknown_type $resultArr
 * @return unknown_type
 */
function wum_doAPIEdit($editPage, $text, &$resultArr){
	$title = $editPage->mArticle->getTitle()->getFullText();
	$editPage->textbox1 =
	WUMMergeController::getInstance()->merge($title, $text, $editPage->mArticle->getContent());
	return true;
}

function wumUPParserHook(&$parser, &$text, &$strip_state) {
	$text = str_replace(WUM_TAG_OPEN,'',$text);
	$text = str_replace(WUM_TAG_CLOSE,'',$text);
	return true;
}

/**
 * This class controls the flow between the ThreeWayBased-, the SectionBased-,
 * and the TableBasedMerger.
 *
 * @author Ingo Steinbauer
 *
 */
class WUMMergeController{

	static private $instance = null;

	/**
	 * singleton
	 *
	 * @return WUMMergeController
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * This method is indirectly called by WP if one edits
	 * an article in the WP clone. It starts and controls the
	 * merge process and returns its result.
	 *
	 * @param $title
	 * @param $newWPText
	 * @param $currentUPText
	 * @return unknown_type
	 */
	public function merge($title, $newWPText, $currentUPText){
		wfProfileIn('WUMMergeController->merge');
		
		if($this->checkIgnoreNewWPVersion($currentUPText)){
			return $currentUPText;
		}

		$overwrite = $this->checkOverwriteUPVersion($newWPText);
		if($overwrite !== false){
			return $overwrite;
		}
		
		//get the last WP version
		$originalWPText = $this->getOriginalWPText($title);
		
		$newWPText = $this->prepareText($newWPText);
		$originalWPText = $this->prepareText($originalWPText);
		$currentUPText = $this->prepareText($currentUPText);

		if(strlen($originalWPText) == 0){
			return $newWPText."\n".$currentUPText;
		}
		
		//check whether to use the table based merger
		global $wumUseTableBasedMerger;
		if($wumUseTableBasedMerger){
			$tbm = WUMTableBasedMerger::getInstance();
			list($newWPText, $currentUPText, $originalWPText) = $tbm->merge($title, $newWPText, $currentUPText, $originalWPText); 
			//wfProfileOut('WUMMergeController->merge');
			//return $currentUPText;
		}

		//do three way merge
		$twmResult = WUMThreeWayBasedMerger::getInstance()
			->merge($originalWPText, $newWPText, $currentUPText);
			
		//do section based merge if merge faults occured during three way merge
		if(count($twmResult['mergeFaults']) == 0){
			$text = $twmResult['mergedText'];
		} else {
			$text = WUMSectionBasedMerger::getInstance()
				->merge($originalWPText, $newWPText, $currentUPText, $twmResult['mergedText'], $twmResult['mergeFaults']);
		}

		//finalize merge result, i.e. replace temporary characters
		$text = WUMThreeWayBasedMerger::getInstance()->finalizeMergeResult($text);

		wfProfileOut('WUMMergeController->merge');
		return $text;
	}

	/**
	 * This method gets the predecessor of the WP article
	 * from the WP clone via a MediaWiki API call.
	 *
	 * @param unknown_type $title
	 * @return string|unknown
	 */
	private function getOriginalWPText($title){
		wfProfileIn('WUMMergeController->getOriginalWPText');
		$text= '';

		global $wumWPURL;
		$url = $wumWPURL."api.php";
		$title = urlencode($title);

		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);

		//ask the wp clone for the last revision of the article
		$response = stream_get_contents(
		fopen($url."?action=query&titles=".$title."&prop=revisions&rvprop=ids&rvlimit=2&format=xml",
			'rb', true, $ctx));
		$response = new SimpleXMLElement($response);
		$crid = $response->xpath("//rev[1]/@revid");
		$crid = $crid[0];
		$lrid = $response->xpath("//rev[2]/@revid");
		$lrid = $lrid[0];

		if(!$lrid){
			// a previous version of this article should not exist
			return $text;
		}

		//ask the mw clone for the content of the last revision
		$response = stream_get_contents(
		fopen($url."?action=query&revids=".$lrid."&prop=revisions&rvprop=content&format=xml",
			'rb', true, $ctx));
		$response = new SimpleXMLElement($response);
		$content = $response->xpath("//rev[1]");
		$text = $content[0];

		wfProfileOut('WUMMergeController->getOriginalWPText');

		return $text;
	}

	/**
	 * This method checks, wheter a merge process should be started.
	 * UP articles that contain a special keyword will ignore new
	 * versions of WP articles.
	 *
	 * @param $text
	 * @return unknown_type
	 */
	private function checkIgnoreNewWPVersion($text){
		if(strpos($text, "_WUM_DO_NOT_MERGE__") > 0){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check whether to overwrite the UP article. Indicated by the
	 * '__WUM_Overwrite__ keyword.
	 *
	 * @param unknown_type $text
	 * @return unknown_type
	 */
	private function checkOverwriteUPVersion($text){
		$text = trim($text);
		if(strpos($text, "_WUM_Overwrite__") !== false){
			if(strlen("##__WUM_Overwrite__") == strlen($text)){
				return "";
			} else {
				return str_replace("__WUM_Overwrite__", "", $text);
			}
		} else {
			return false;
		}
	}

	private function prepareText($text){
		return str_replace("\r\n","\n", $text);
	}

}