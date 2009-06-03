<?php
 
// ##### ##### ##### ##### ##### ##### ##### ##### ##### ##### ##### ##### ####
// File:        NoTitle.php
// Belongs-To:  MediaWiki NoTitle Extension
// Version:     1.0-live
// Authors:     Carlo Cabanilla / Andrew Dodd
// Email:       andrewdodd13@gmail.com          [Expires 31/12/2009]
// Purpose:     Hide title on pages with __NOTITLE__ magic word
// Reqs:        None
// ##### ##### ##### ##### ##### ##### ##### ##### ##### ##### ##### ##### ####
 
$NoTitle = new NoTitle();
 
$wgHooks['MagicWordMagicWords'][] = array($NoTitle, 'addMagicWord');
$wgHooks['MagicWordwgVariableIDs'][] = array($NoTitle, 'addMagicWordId');
$wgHooks['LanguageGetMagic'][] = array($NoTitle, 'addMagicWordLanguage');
$wgHooks['ParserAfterStrip'][] = array($NoTitle, 'checkForMagicWord');
$wgHooks['BeforePageDisplay'][] = array($NoTitle, 'hideTitle');
 
class NoTitle
{
    function NoTitle() {}
 
    function addMagicWord(&$magicWords) {
        $magicWords[] = 'MAG_NOTITLE';
        return true;
    }
 
    function addMagicWordId(&$magicWords) {
        $magicWords[] = MAG_NOTITLE;
        return true;
    }
 
    function addMagicWordLanguage(&$magicWords, $langCode) {
        switch($langCode) {
        default:
            $magicWords['MAG_NOTITLE'] = array(0, '__NOTITLE__');
        }
        return true;
    }
 
 
    function checkForMagicWord(&$parser, &$text, &$strip_state) {
        global $action;
        $mw = MagicWord::get('MAG_NOTITLE');
 
        if ($mw->matchAndRemove($text)) {
            $parser->mOptions->mHideTitle = true;
            $parser->disableCache();
        }
 
        return true;
    }
 
    function hideTitle(&$page) {
        if (isset($page->parserOptions()->mHideTitle) && $page->parserOptions()->mHideTitle) {
            $page->mScripts .= "<style type='text/css'>h1.firstHeading { display:none; } </style>";
        }
 
        return true;
    }
}

?>