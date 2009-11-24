<?php

global $wgExtensionFunctions, $wgHooks;
$wgExtensionFunctions[] = 'wd_templatePF_Setup';
$wgHooks['LanguageGetMagic'][] = 'wd_templatePF_Magic';


function wd_templatePF_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'wdTemplatePFPut', 'wd_doTemplatePFPut', SFH_OBJECT_ARGS );
	$wgParser->setFunctionHook( 'wdTemplatePFDelete', 'wd_doTemplatePFDelete', SFH_OBJECT_ARGS );
	$wgParser->setFunctionHook( 'wdTemplatePFPutUpdate', 
		'wd_doTemplatePFPutUpdate', SFH_OBJECT_ARGS );
	return true;
}

function wd_templatePF_Magic( &$magicWords, $langCode ) {
	$magicWords['wdTemplatePFPut'] = array( 0, 'wdTemplateParserPut' );
	$magicWords['wdTemplatePFDelete'] = array( 0, 'wdTemplateParserDelete' );
	$magicWords['wdTemplatePFPutUpdate'] = array( 0, 'wdTemplateParserPutUpdate' );
	return true;
}

function wd_doTemplatePFPut( &$parser, $frame, $args) {
	global $wdTemplateParserResult, $wgWebDAVRichMediaMapping;
	$wdTemplateParserResult = array();
	foreach($args as $arg){
		$arg = $frame->expand($arg);
		if(strpos($arg, "=") !== 0){
			$argName = trim(substr($arg, 0, strpos($arg, "=")));
			if($argName == $wgWebDAVRichMediaMapping["RelatedArticles"]){
				$wdTemplateParserResult[$wgWebDAVRichMediaMapping["RelatedArticles"]] =
					substr($arg, strpos($arg, "=")+1);
			}
		}
	}
	return true;
}

function wd_doTemplatePFDelete( &$parser, $frame, $args) {
	global $wgWebDAVRichMediaMapping, $wgWDOriginalTemplate, $wgWDNewTemplate, $wgWDDeleteTitle; 
	$wgWDOriginalTemplate = "";
	$wgWDNewTemplate = "";
	$result = "{{".$wgWebDAVRichMediaMapping["TemplateName"];
	$first = true;
	foreach($args as $arg){
		$arg = $frame->expand($arg);
		if(!$first){
			$wgWDOriginalTemplate .= "|";
			$wgWDNewTemplate .= "|";
		}
		$first = false;
		$wgWDOriginalTemplate .= $arg;
		if(strpos($arg, "=") !== 0){
			$argName = trim(substr($arg, 0, strpos($arg, "=")));
			if($argName == $wgWebDAVRichMediaMapping["RelatedArticles"]){
				$relatedArticleTitles = substr($arg, strpos($arg, "=")+1);
				$relatedArticleTitles = explode($wgWebDAVRichMediaMapping["Delimiter"], 
					$relatedArticleTitles);
					foreach($relatedArticleTitles as $key => $article){
						if(trim($article) == $wgWDDeleteTitle){
							unset($relatedArticleTitles[$key]);
						}
					}
					$wgWDNewTemplate .= $argName."=".implode($wgWebDAVRichMediaMapping["Delimiter"], 
						$relatedArticleTitles);
			} else {
				$wgWDNewTemplate .= $arg;
			}
		} else {
			$wgWDNewTemplate .= $arg;
		}
	}
	
	return true;
}

function wd_doTemplatePFPutUpdate( &$parser, $frame, $args) {
	global $wgWebDAVRichMediaMapping, $wgWDOriginalTemplate, 
		$wgWDNewTemplate, $wgWDPutUpdateTitle; 
	$wgWDOriginalTemplate = "";
	$wgWDNewTemplate = "";
	$result = "{{".$wgWebDAVRichMediaMapping["TemplateName"];
	$first = true;
	foreach($args as $arg){
		$arg = $frame->expand($arg);
		if(!$first){
			$wgWDOriginalTemplate .= "|";
			$wgWDNewTemplate .= "|";
		}
		$first = false;
		$wgWDOriginalTemplate .= $arg;
		if(strpos($arg, "=") !== 0){
			$argName = trim(substr($arg, 0, strpos($arg, "=")));
			if($argName == $wgWebDAVRichMediaMapping["RelatedArticles"]){
				$relatedArticleTitles = substr($arg, strpos($arg, "=")+1);
				$relatedArticleTitles = explode($wgWebDAVRichMediaMapping["Delimiter"], 
					$relatedArticleTitles);
					$found = false;
					foreach($relatedArticleTitles as $key => $article){
						if(trim($article) == $wgWDPutUpdateTitle){
							$found = true;
						}
					}
					if(!$found){
						$relatedArticleTitles[] = $wgWDPutUpdateTitle; 
					}
					$wgWDNewTemplate .= $argName."=".implode($wgWebDAVRichMediaMapping["Delimiter"], 
						$relatedArticleTitles);
			} else {
				$wgWDNewTemplate .= $arg;
			}
		} else {
			$wgWDNewTemplate .= $arg;
		}
	}
	return true;
}

class WDTemplateParser {
	
	private $relatedArticles = "";
	private $newArticleTextForDelete = "";
	private $newArticleTextForPutUpdate = "";
	
	public function __construct($title, $methodFlag, $extraTitle = null){
		if($methodFlag == "put"){
			$this->parseTemplateForPut($title);
		} else if ($methodFlag == "delete"){
			$this->parseTemplateForDelete($title, $extraTitle);
		} else if ($methodFlag == "putupdate"){
			$this->parseTemplateForPutUpdate($title, $extraTitle);
		}
		return $this;
	}
	
	private function parseTemplateForPut($title){
		global $wgWebDAVRichMediaMapping;
		$article = new Article($title);
		$text = $article->getContent();
		$text = str_replace("{{".$wgWebDAVRichMediaMapping["TemplateName"]
			, "{{subst:#wdTemplateParserPut:", $text);
		
		global $wgParser;
		$popts = new ParserOptions();
		$wgParser->startExternalParse($title, $popts, Parser::OT_WIKI);
		$wgParser->internalParse($text);
		
		global $wdTemplateParserResult, $wgWebDAVRichMediaMapping;
		print_r($wdTemplateParserResult);
		if(array_key_exists($wgWebDAVRichMediaMapping["RelatedArticles"]
				, $wdTemplateParserResult)){
			$this->relatedArticles = 
				$wdTemplateParserResult[$wgWebDAVRichMediaMapping["RelatedArticles"]];
		}
	}
	
	private function parseTemplateForDelete($title, $deleteTitle){
		global $wgWebDAVRichMediaMapping;
		$article = new Article($title);
		$text = $article->getContent();
		$textForParsing = str_replace("{{".$wgWebDAVRichMediaMapping["TemplateName"]
			, "{{subst:#wdTemplateParserDelete:", $text);
		
		global $wgParser, $wgWDDeleteTitle;
		$wgWDDeleteTitle = $deleteTitle;
		$popts = new ParserOptions();
		$wgParser->startExternalParse($title, $popts, Parser::OT_WIKI);
		$wgParser->internalParse($textForParsing);
		
		global $wgWDOriginalTemplate, $wgWDNewTemplate;
		$text = str_replace($wgWDOriginalTemplate, $wgWDNewTemplate, $text);
		$this->newArticleTextForDelete = $text; 
	}
	
	private function parseTemplateForPutUpdate($title, $putUpdateTitle){
		global $wgWebDAVRichMediaMapping;
		$article = new Article($title);
		$text = $article->getContent();
		$textForParsing = str_replace("{{".$wgWebDAVRichMediaMapping["TemplateName"]
			, "{{subst:#wdTemplateParserPutUpdate:", $text);
		
		global $wgParser, $wgWDPutUpdateTitle;
		$wgWDPutUpdateTitle = $putUpdateTitle;
		$popts = new ParserOptions();
		$wgParser->startExternalParse($title, $popts, Parser::OT_WIKI);
		$wgParser->internalParse($textForParsing);
		
		global $wgWDOriginalTemplate, $wgWDNewTemplate;
		$text = str_replace($wgWDOriginalTemplate, $wgWDNewTemplate, $text);
		$this->newArticleTextForPutUpdate = $text; 
	}
	
	public function getRelatedArticles(){
		return $this->relatedArticles;
	}
	
	public function getNewArticleTextForDelete(){
		return $this->newArticleTextForDelete;
	}
	
	public function getNewArticleTextForPutUpdate(){
		return $this->newArticleTextForPutUpdate;
	}
}