<?php

/**
 * @file
  * @ingroup DIWUM
  * 
  * @author Ingo Steinbauer
 */

global $wumWPURL, $wumUseTableBasedMerger, 
	$wumPatchMargin, $wumMatchDistance;

//enter the URL of the WikiPedia clone
$wumWPURL = "http://localhost/mediawiki/";

//decide wheter to use the table based merger in addition (recommended!)
$wumUseTableBasedMerger = true;

//decide how many characters before and after a patch
//must match in order to appöy a patch.
//Small values might lead to merge faults, i.e. because
//if the patch matches to several positions, then the patch will 
//not be applied.
//High values lead to merge values, i.e. because the patch
//might not match to any position in the text
$wumPatchMargin = 50; 

//define the size of the  text fragment of the new text version
//in which the merger tries to apply the patch.
//Too big and too small values might lead to merge faults.
$wumMatchDistance = 500;
