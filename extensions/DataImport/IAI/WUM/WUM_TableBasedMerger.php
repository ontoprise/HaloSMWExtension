<?php

/**
 * @file
  * @ingroup DIWUM
  * 
  * @author Ingo Steinbauer
 */

/**
 * This group contains all parts of the Data Import extension.that deal with the Wikipedia Ultrapedia Merger
 * @defgroup DIWUM
 * @ingroup DIInterWikiArticleImport
 */

global $wgExtensionFunctions, $wgHooks;
$wgExtensionFunctions[] ='wum_tabPF_Setup';
$wgHooks['LanguageGetMagic'][] = 'wum_tabPF_Magic';

function wum_tabPF_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'wumTabPF', 'wum_doTabPF', SFH_OBJECT_ARGS );
}

function wum_tabPF_Magic( &$magicWords, $langCode ) {
	$magicWords['wumTabPF'] = array( 0, 'wumtab' );
	return true;
}

function wum_doTabPF( &$parser, $frame, $args) {
	$args = wum_preprocessArgs($frame, $args);
	
	global $wumTabParserFunctions;
	$text = "{{#tab:";
	$tableCode = null;
	$tabCount = 0;
	foreach($args as $key => $arg){
		$argt = explode("=", $arg, 2);
		$argt[0] = explode(".", $argt[0], 2);
		if(count($argt[0]) > 1 && strtolower(trim($argt[0][1])) == "body"){
			$tabCount += 1;
			if($tabCount == 2){
				if(!preg_match('/^\[\[([^\[\]\|]+)(\|([^\[\]]+))?\]\]$/', $argt[1], $matches)) {
					$tableCode = explode("\n", $argt[1], 2);
					$text .= "\n|".implode(".", $argt[0])."=".$tableCode[0].
						"\n<embedwiki>###replace###</embedwiki>";
					$tableCode = $tableCode[1];
					continue;
				}
			} 
		} 
		$text .= "\n|".trim($arg);
	}
	$text .= "\n}}";
	
	if(!is_null($tableCode)){
		$wumTabParserFunctions[] = array($text, $tableCode);
	}
	return true;
}

function wum_preprocessArgs($frame, $args){
	//remove first arg after the colon
	array_shift($args);
	
	$preprocessedArgs = array();
	$lastPreprocessedArg = null;
	foreach($args as $arg){
		$arg = $frame->expand($arg);
		$argt = explode("=", $arg, 2);
		if(strtolower(trim($argt[0])) == "options"
				|| strtolower(trim($argt[0])) == "name"){

			if(!is_null($lastPreprocessedArg)){
				$preprocessedArgs[] = $lastPreprocessedArg;
			}
			$lastPreprocessedArg = ltrim(implode("=", $argt));
			continue;
		}
		
		$argt[0] = explode(".", trim($argt[0]), 2);
		if(count($argt[0]) > 1){
			if(strtolower($argt[0][1]) == "body"
					|| strtolower($argt[0][1]) == "option"){
				
				if(!is_null($lastPreprocessedArg)){
					$preprocessedArgs[] = $lastPreprocessedArg;
				}
				$argt[0] = implode(".", $argt[0]);
				$lastPreprocessedArg = ltrim(implode("=", $argt));
				continue;		
			}
		}
		
		$lastPreprocessedArg .= "|".$arg;
	}
	
	if(!is_null($lastPreprocessedArg)){
		$preprocessedArgs[] = $lastPreprocessedArg;
	}
	
	return $preprocessedArgs;
}

class WUMTableBasedMerger {
	
	static private $instance = null;

	/**
	 * singleton
	 * 
	 * @return WUMTableBasedMerger
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new self();
		}
		return self::$instance;
	}	
	
	private $unresolvedTableReplacements = array();
	private $wikipediaText = "";
	private $ultrapediaText = "";
	private $mergedText = "";
	private $title = "";
	
	function merge($title, $wikipediaText, $ultrapediaText){
		$this->title = $title;
		$this->wikipediaText = $wikipediaText;
		$this->ultrapediaText = $ultrapediaText;
		$this->getTableReplacements();
		$this->replaceStaticTables();
		
		$text = $this->getMergedText();
		
		$this->createMergeResultArticle();
		
		return $text;		
	}
	
	public function createMergeResultArticle(){
		$dateString = $this->getDateString();
		$title = $this->title."/WUM ".$dateString;
		
		$result = "\n==Merge Result==";
		$result .= "\n* Merged article was: [[merged article was::".$this->title."]]";
		$result .= "\n* Has merge date: [[has merge date::".$dateString."]]";
		$result .= "\n* Was merged successfully: [[was merged successfully::";
		if(count($this->unresolvedTableReplacements) == 0){
			$result .= "true]]";
		} else {
			$result .= "false]]";
			$result .= "\n===Occured merge faults===";
			foreach($this->unresolvedTableReplacements as $utr){
				$result .= "\n====Table: " 
					.str_replace("##", "; ",$utr->getFingerprint())."====";
				$result .= "\n<pre>".$utr->getOriginalText()."</pre>";
			}
		}
		
		$result .= "\n\n[[Category:WUMergeReport]]";

		smwf_om_EditArticle($title, 'WUM', $result, '');
	}
	
	private function getDateString(){
		$date = getdate();
		$mon = $date["mon"]<10 ? "0".$date["mon"] : $date["mon"];
		$mday = $date["mday"]<10 ? "0".$date["mday"] : $date["mday"];
		$hours = $date["hours"]<10 ? "0".$date["hours"] : $date["hours"];
		$minutes = $date["minutes"]<10 ? "0".$date["minutes"] : $date["minutes"];
		$seconds = $date["seconds"]<10 ? "0".$date["seconds"] : $date["seconds"];

		$dateString = $date["year"]."/".$mon."/".$mday." "
			.$hours.":".$minutes.":".$seconds;
	
		return $dateString;
	}
	
	private function getTableReplacements(){
		$text = str_replace("{{#tab:", "{{subst:#wumtab:", $this->ultrapediaText);
		$text = str_replace("<embedwiki>", "", $text);
		$text = str_replace("</embedwiki>", "", $text);
		
		global $wgParser;
		$t = Title::newFromText("XYZ");
		$popts = new ParserOptions();
		$wgParser->startExternalParse($t, $popts, Parser::OT_WIKI);
	
		global $wumTabParserFunctions;
		$wumTabParserFunctions = array();
		
		$wgParser->internalParse($text);
		
		foreach($wumTabParserFunctions as $tabParserFunction){
			$this->processText($tabParserFunction[1], "doCreateTableReplacement"
				,array($tabParserFunction));
		}
	}
	
	private function doCreateTableReplacement($tableText, $fingerprintArray, $tabParserFunction){
		$this->unresolvedTableReplacements[] = 
			new WUTableReplacement($fingerprintArray, $tabParserFunction[0][0]
				, $tabParserFunction[0][1]);
		return $tableText;
	}
	
	private function replaceStaticTables() {
		$this->mergedText = $this->processText($this->wikipediaText, "doReplace", array()); 
	}
	
	private function processText($sourceText, $callback, $callbackParameters) {
		$tableHeadersStack = array();
		$tableTextStack = array();
		$result = "";
	
		$lines = explode("\n", $sourceText);
		$td_history = array();
		foreach($lines as $outLine){
			$outLine .= "\n";
			$line = trim($outLine);
	
			if($line == ''){ // empty line, go to next line
				if(count($tableTextStack) > 0){
					$tableTextStack[count($tableTextStack)-1] .= $outLine;
				} else {
					$result .= $outLine;
				}
				continue;
			}
			$first_character = $line[0];
			if(preg_match( '/^(:*)\{\|(.*)$/', $line , $matches)){
				// First check if we are starting a new table
				array_push($td_history , false);
				array_push($tableHeadersStack, array());
				array_push($tableTextStack, $outLine);
				$outLine = "";
			} else if(count($td_history) == 0) {
				// Don't do any of the following
				$result .= $outLine;
				continue;
			} else if ( substr ( $line , 0 , 2 ) === '|}' ) {
				// We are ending a table
				array_pop ( $td_history );
				$fingerprintArray = array_pop($tableHeadersStack);
				$tableText = array_pop($tableTextStack);
				$tableText .= "|}";
				$outLine = substr($outLine, 2);
				$tableText = 
					$this->$callback($tableText, $fingerprintArray, $callbackParameters);
				if(count($tableTextStack) > 0){
					$tableTextStack[count($tableTextStack)-1] .= $tableText; 
				} else {
					$result .= $tableText;
				}
			} else if ( substr ( $line , 0 , 2 ) === '|-' ) {
				// Now we have a table row
				array_push ( $td_history , false );
			} else if ($first_character === '|' 
					|| $first_character === '!' || substr ( $line , 0 , 2 )  === '|+' ) {
				// This might be cell elements, td, th or captions
				if ( substr ( $line , 0 , 2 ) === '|+' ) {
					$line = substr( $line , 1);
				}
				$line = substr($line , 1);
	
				if($first_character === '!'){
					$line = str_replace('!!' , '||' , $line );
				}
	
				$cells = explode( '||' , $line );
	
				// Loop through each table cell
				if ( $first_character === '!' ) {
					foreach ( $cells as $cell ){
						// A cell could contain both parameters and data
						$cell_data = explode ( '|' , $cell , 2 );
	
						$tableHeader = array_pop($tableHeadersStack);
						if ( strpos( $cell_data[0], '[[' ) !== false ) {
							$tableHeader[] = $cell;
						} else if ( count ( $cell_data ) == 1 ){
							$tableHeader[] = $cell_data[0];
						} else {
							$tableHeader[] = $cell_data[1];
						}
						array_push ( $td_history , true );
						array_push($tableHeadersStack, $tableHeader);
					}
				}
			}
			
			if(count($tableTextStack) > 0){
				$tableTextStack[count($tableTextStack)-1] .= $outLine;
			} else {
				$result .= $outLine;
			}
		}
		
		return $result;
	}

	private function doReplace($tableText, $fingerprintArray, $ignore){
		foreach($this->unresolvedTableReplacements	 as $key => $uTR){
			if($uTR->matches($fingerprintArray)){
				unset($this->unresolvedTableReplacements[$key]);
				return $uTR->getNewText($tableText);
			}
		}
		return $tableText;
	}
	
	public function getMergedText(){
		return $this->mergedText;
	}
	
	public function getUnresolvedTableReplacements(){
		return $this->unresolvedTableReplacements;
	}
}


class WUTableReplacement {
	
	private $fingerprint = "";
	private $replacement = "";
	private $originalTableCode = "";
	
	function __construct($fingerprintArray, $replacement, $originalTableCode){
		$this->fingerprint = $this->computeFingerprint($fingerprintArray);
		$this->replacement = $replacement;
		$this->originalTableCode = $originalTableCode;
		return $this;
	}
	
	private function computeFingerprint($fingerprintArray){
		foreach($fingerprintArray as $key => $value){
			$fingerprintArray[$key] = trim($value);
		}
		
		return implode("##", $fingerprintArray);
	}
	
	public function matches($fingerprintArray){
		if($this->fingerprint == $this->computeFingerprint($fingerprintArray)){
			return true;
		}
		return false;
	}
	
	public function getFingerprint(){
		return $this->fingerprint;
	}
	
	public function getNewText($text){
		return str_replace("###replace###", $text, $this->replacement);
	}
	
	public function getOriginalText(){
		return str_replace("###replace###", $this->originalTableCode, $this->replacement);
	}
}