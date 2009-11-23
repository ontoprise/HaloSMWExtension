<?php

global $wgExtensionFunctions, $wgHooks;
$wgExtensionFunctions[] = 'wd_templatePF_Setup';
$wgHooks['LanguageGetMagic'][] = 'wd_templatePF_Magic';


function wd_templatePF_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'wdTemplatePF', 'wd_doTemplatePF', SFH_OBJECT_ARGS );
	return true;
}

function wd_templatePF_Magic( &$magicWords, $langCode ) {
	$magicWords['wdTemplatePF'] = array( 0, 'wdTemplateParser' );
	return true;
}

function wd_doTemplatePF( &$parser, $frame, $args) {
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

class WDTemplateParser {
	
	private $relatedArticles = "";
	
	public function __construct($title){
		$this->parseTemplate($title);
		return $this;
	}
	
	private function parseTemplate($title){
		$wgWebDAVRichMediaMapping;
		$article = new Article($title);
		$text = $article->getContent();
		$text = str_replace("{{".$wgWebDAVRichMediaMapping["Filename"]
			, "{{subst:#wdTemplateParser:", $text);
		
		global $wgParser;
		$popts = new ParserOptions();
		$wgParser->startExternalParse($title, $popts, Parser::OT_WIKI);
		$wgParser->internalParse($text);
		
		global $wdTemplateParserResult, $wgWebDAVRichMediaMapping;
		if(array_key_exists($wgWebDAVRichMediaMapping["RelatedArticles"]
				, $wdTemplateParserResult)){
			$this->relatedArticles = 
				$wdTemplateParserResult[$wgWebDAVRichMediaMapping["RelatedArticles"]];
		}
	}
	
	public function getRelatedArticles(){
		return $this->relatedArticles;
	}
}