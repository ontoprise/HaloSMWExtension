<?php

global $wgExtensionFunctions, $wgHooks;  
// Define a setup function for the {{ ws:}} Syntax Parser
$wgExtensionFunctions[] ='synsetPF_Setup';
//Add a hook to initialise the magic word for the {{ ws:}} Syntax Parser
$wgHooks['LanguageGetMagic'][] = 'synsetPF_Magic';

/**
 * Set a function hook associating the "webServiceUsage" magic word with our function
 */
function synsetPF_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'synsetPF', 'synsetPF_Render' );
}

/**
 * maps the magic word "webServiceUsage"to occurences of "ws:" in the wiki text
 */
function synsetPF_Magic( &$magicWords, $langCode ) {
	$magicWords['synsetPF'] = array( 0, 'synonyms' );
	return true;
}

/**
 * Parses the {{ synonyms: }} syntax and returns the resulting wikitext
 *
 * @param $parser
 * @return string
 * 		the rendered wikitext
 */
function synsetPF_Render( &$parser) {
	$parameters = func_get_args();
	$term = trim($parameters[1]);
	
	global $IP;
	require_once($IP."/extensions/UnifiedSearch/synsets/storage/SMW_SynsetStorageSQL.php");
	
	$st = new SynsetStorageSQL();
	$synonyms = $st->getSynsets($term);
	
	$result = "";
	
	$nFirst = false;
	foreach($synonyms as $s){
		if($nFirst){
			$result .= ", "; 	
		}
		$result .= implode(", ",$s);
		$nFirst = true; 
	}
	return $result; 
}


?>