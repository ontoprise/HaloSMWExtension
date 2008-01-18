<?php
/**
 * Created on 10.01.2008
 *	
 * Converts properties which have a type of either Type:Integer 
 * or Type:Float to the unified Type:Number
 * 
 * Note: this file must be placed in MediaWiki's "maintenance" directory!
 *
 * Usage:
 * php SMW_unifyTypes.php [options...]
 *
 * -v           Be verbose about the progress.
 * -c           Checks if there are any pages that need to be processed.
 *
 * Author: kai
 */
 
 require_once( 'commandLine.inc' );

global $smwgIP;
global $wgParser;

$verbose = array_key_exists( 'v', $options );
$check_only = array_key_exists( 'c', $options );

print "Checking if types need to be changed\n";

$options = new ParserOptions();
$dbr =& wfGetDB( DB_MASTER );
$specprops = $dbr->tableName('smw_specialprops');
$properties = $dbr->select( $specprops , 'subject_title', '(LOCATE('.$dbr->addQuotes('_int').', value_string) > 0 OR LOCATE('.$dbr->addQuotes('_flt').', value_string) > 0) ' .
							'AND subject_namespace = ' . SMW_NS_PROPERTY , 'SMW_convertNumberProperties script' );
$numProps = $properties->numRows();

if ($numProps === 0) {
	print "No integer or float properties found. Thus, no type unification required. Everything is fine.\n";
} else {
	print "Type unification is required. $numProps property pages need to be changed.\n";
	while ( $row = $properties->fetchObject() ) {
		// do only process pages which don't need to be updated manually
		
			if ($verbose) print "\n - Updated: ".$row->subject_title;
			
			// load latest revision
			$t = Title::newFromText($row->subject_title, SMW_NS_PROPERTY);
			if ($t == NULL) continue; // Title may be invalid!
			$revision = Revision::newFromTitle( $t );
			$a = new Article($t);
			if ( $revision === NULL ) continue;
			
			// get old text and transform types
			$oldtext = $revision->getText();
			$newtext = unifyTypes($oldtext);
			
			//print "\n".$newtext."\n";
			// save new text and re-parse article to get new semantic data.
			if ($newtext != NULL && !$check_only) {
				$a->doEdit($newtext, $revision->getComment(), EDIT_UPDATE);
				$wgParser->parse($newtext, $t, $options, true, true, $revision->getID());
				SMWFactbox::storeData(true);
			}
		
	}
}
$properties->free();
print "\n\nUnify types script done.\n";


function unifyTypes($oldtext) {
	global $smwgContLang;
	
	$ssp = $smwgContLang->getSpecialPropertiesArray();
	$dls = $smwgContLang->getDatatypeLabels();
	
	$numLabel = $dls["_num"];
	$aliases = array_keys($smwgContLang->getDatatypeAliases(), "_num");
	
	$typeAnnotations = array();
		
	preg_match_all('/\[\[\s*'.$ssp[SMW_SP_HAS_TYPE].'\s*:[:|=]([^]]*)\]\]/i', $oldtext, $typeAnnotations);
	
	
	if (count($typeAnnotations) > 1) {
		
		foreach($typeAnnotations[1] as $r) {
			$rpl = $r;
			foreach($aliases as $a) {
				$rpl = preg_replace("/$a/i", $numLabel, $rpl);
			}
			
			$replacement = "[[".$ssp[SMW_SP_HAS_TYPE]."::".$rpl."]]";
			$oldtext = preg_replace('/\[\[\s*'.$ssp[SMW_SP_HAS_TYPE].'\s*:[:|=]'.preg_quote($r).'\]\]/i', $replacement, $oldtext);
		}
	} 
	return $oldtext;
}
 
?>
