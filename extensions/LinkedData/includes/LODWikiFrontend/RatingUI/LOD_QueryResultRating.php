<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the HTML fragments needed for the UI for rating query
 * results. The fragments are stored as constants in the class
 * LODQueryResultRatingUI.
 * 
 * @author Thomas Schweitzer
 * Date: 26.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class manages the HTML structure of the rating UI.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODQueryResultRatingUI  {
		
	const RATING_HTML = <<<HTML
	
<div class="lodDivRatingMain">
    <div class="lodDivRatingValue">
        Rate a relation of this value: <span id="lodRatingValue">***value***</span>
    </div>
    ***pathway***
    <div class="lodDivRatingTriples">
        ***allTriples***
    </div>
    <div class="lodDivRatingRateAndComment">
        <p id="lodRatingTitleCorrect" class="lodRatingTitle" style="display:none">
            In your opinion the selected triple is <b>correct</b>.
        </p>
        <p id="lodRatingTitleWrong" class="lodRatingTitle" style="display:none">
            In your opinion the selected triple is <b>wrong</b>.
        </p>
        <p>
            Your comment:
            <textarea id="lodRatingCommentTA" rows="5" cols="20" name="comment"></textarea>
        </p>
    	<a id="lodRatingShowComments">Show other comments</a>
    </div>
    <div class="lodDivRatingOtherCommentsContainer">
    	<div class="lodDivRatingOtherComments">
            Others flagged this:  <img src="***imgPath***/correct.png" alt="" /> <span id="lodRatingCorrect">(0)</span>&nbsp;&nbsp;<img src="***imgPath***/wrong.png" alt="" />  <span id="lodRatingWrong">(0)</span>
        
	        <div class="lodDivRatingComments">
            	<p class="lodRatingCoou">Comments of other users:</p>
	            <div class="lodRatingCommentContainer">
	                <div lodratingcomment="" class="">
	                    - Is Hesse really english?
	                </div>
	                <div lodratingcomment="" class="">
	                    - That's wrong. Hesse is a german author!
	                </div>
	            </div>
	        </div>
	    </div>
    </div>
    <div class="lodRatingSaveArea">
        <input type="button" id="lodRatingSave" value="Save" /> 
        <input type="button" id="lodRatingCancel" value="Cancel" />
    </div>
</div>
HTML;

	/**
	 * Returns the HTML for adding and viewing ratings of triples
	 * 
	 * @param $ratingKey
	 * 		The rating key is needed to retrieve the prefixes from the query.
	 * @param string $value
	 * 		The value whose relations will be rated.
	 * @param array $triples
	 * 		An array of result sets with primary and secondary triples as returned
	 * 		by LODRatingAccess::getTriplesForRatingKey()
	 * 
	 * @return string
	 * 		The requested HTML structure.
	 */
	public static function getRatingHTML($ratingKey, $value, array $triples) {
		$html = self::RATING_HTML;
		
		$query = LODRatingAccess::getQueryForRatingKey($ratingKey);
		$pm = LODPrefixManager::getInstance();
		$pm->addPrefixesFromQuery($query);
		
		// Insert the value that will be rated
		$html = str_replace("***value***", $value, $html);
		
		// Insert pathway selector for triples if needed
		$pathwayHTML = self::generateHTMLForPathway($triples);
		$html = str_replace("***pathway***", $pathwayHTML, $html);
		
		// Insert the tables of triples
		$tripleHTML = self::generateHTMLForTriples($triples);
		$html = str_replace("***allTriples***", $tripleHTML, $html);

		// Replace the image path
		global $lodgScriptPath;
		$imgPath = $lodgScriptPath . "/skins/img";
		$html = str_replace("***imgPath***", $imgPath, $html);
		
		return $html;
		
	}
	
	//--- Private function ---
	
	/**
	 * Returns the HTML that contains the triples that can be rated. There are
	 * several result sets with primary and secondary triples. 
	 * 
	 * @param array $triples
	 * 		An array of result sets with primary and secondary triples as returned
	 * 		by LODRatingAccess::getTriplesForRatingKey()
	 * 
	 * @return string
	 * 		The requested HTML structure.
	 */
	private static function generateHTMLForTriples(array $triples) {

		$html = "";

		// init language dependent strings
		$clickFlag = "Click a flag to rate a triple";
		$rateRelated  = "Rate other related triples";
		
		// Each result set is wrapped in its own div
		
		$rsIdx = 1;
	    foreach ($triples as $resultSet) {
	    	$html .= <<<HTML
<div class="lodRatingResultSet" id="lodRatingResultSet_$rsIdx">
<span class="lodRatingGrayText"> $clickFlag</span><br />
HTML;
			// Add the table of primary triples
			$html .= self::generateTriplesTable($rsIdx, 1, $resultSet[0]);
			
			// Add the table of secondary triples
			$html .= <<<HTML
	<a class="lodRatingOpenRelatedTriples" id="lodRatingRateOthers_$rsIdx">$rateRelated</a>
	<div class="lodRatingRelatedTriples" id="lodRatingRelated_$rsIdx">
HTML;
			$html .= self::generateTriplesTable($rsIdx, 2, $resultSet[1]);
			$html .= <<<HTML
	</div>
</div>
HTML;

			++$rsIdx;
	    }
		
	    return $html;
	    
	}
	
	/**
	 * Generates the HTML for a table that contains triples and a radio button
	 * for selecting them.
	 * 
	 * @param int $id
	 * 		An ID (the result set index) that is added to the ID of the generated
	 * 		HTML elements.
	 * @param int $primSec
	 * 		1 or 2 for primary and secondary tripels
	 * @param array<LODTriple> $triples
	 * 		The array of triples
	 * @return string
	 * 		The HTML of the table
	 * 
	 */
	private static function generateTriplesTable($id, $primSec, array $triples) {
		$html = <<<HTML
<table id="lodRatingTriples_$id_$primSec" class="lodRatingTriplesTable">
HTML;
		
		$pm = LODPrefixManager::getInstance();

		// Sort all triples by subject
		$tripleTable = array();
		foreach ($triples as $t) {
			$subj = $t->getSubject();
			$tripleTable[$subj][] = array($t->getPredicate(), $t->getObject());
		}
		
		$row = 0;
		foreach ($tripleTable as $subj => $poArray) {
			$first = true;
			$s = htmlentities($subj);
			foreach ($poArray as $po) {
				$p = htmlentities($po[0]);
				$o = htmlentities($po[1]);
				
				$displaySubject = $first ? "" : "style=\"visibility:hidden\"";
				
				$psubj = htmlentities($pm->makePrefixedURI($subj));
				$ppred = htmlentities($pm->makePrefixedURI($po[0]));
				$pobj  = htmlentities($pm->makePrefixedURI($po[1]));
				
				$first = false;
			
				$html .= <<<HTML
<tr subject="$s" predicate="$p" object="$o">
	<td>
		<img src="***imgPath***/correct.png" alt="" value="true" class="lodRatingFlag"/>
	</td>
	<td>
		<img src="***imgPath***/wrong.png" alt="" value="false" class="lodRatingFlag"/>
	</td>
	<td $displaySubject>$psubj</td>
	<td>$ppred</td>
	<td>$pobj</td>
</tr>
HTML;

				++$row;
			}
		}
		$html .= "</table>";
		
		return $html;
	}
	
	/**
	 * If there are several result sets of triples in $triples, the HTML for
	 * a pathway selector is added.
	 * 
	 * @param array $triples
	 * 		An array of result sets with primary and secondary triples as returned
	 * 		by LODRatingAccess::getTriplesForRatingKey()
	 * 
	 * @return string
	 * 		The requested HTML structure.
	 */
	private static function generateHTMLForPathway(array $triples) {
		if (count($triples) == 1) {
			// only one result => now pathway selector needed
			return "";
		}
		
		// language dependent strings
		$pathwayLabel = "Pathways to this value:";
		$html = <<<HTML
<div class="lodDivRatingPathway">
	$pathwayLabel <a id="lodRatingPathwayBack">&lt;</a>
HTML;
		for ($i = 1, $len = count($triples); $i <= $len; ++$i) {
			$html .= <<<HTML
 <a id="lodRatingPathway_$i" class="lodRatingPathwayIndex">$i</a> 
HTML;
		}
		$html .= <<<HTML
        <a id="lodRatingPathwayForward">&gt;</a>
    </div>
HTML;
		return $html;
	}
}

