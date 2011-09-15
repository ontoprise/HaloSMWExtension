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

global $LOD_QRR_RATING_HTML;
$LOD_QRR_RATING_HTML = <<<HTML
	
<div class="lodDivRatingMain">
	<div class="lodRatingContent">
	    <div class="lodDivRatingValue">
	        {{lod_rt_heading}} <span id="lodRatingValue">***value***</span>
	    </div>
	    ***pathway***
	    <div class="lodDivRatingTriples">
	        ***allTriples***
	    </div>
	    <div class="lodDivRatingRateAndComment">
	        <div id="lodRatingTitleCorrect" class="lodRatingTitle" style="display:none">
	            {{lod_rt_flagged_correct}}
	        </div>
	        <div id="lodRatingTitleWrong" class="lodRatingTitle" style="display:none">
	            {{lod_rt_flagged_wrong}}
	        </div>
	        <div id="lodRatingYourComment">
	            {{lod_rt_enter_comment}}
	            <textarea id="lodRatingCommentTA" rows="3" cols="20" name="comment"></textarea>
	        </div>
	    </div>
	    <div class="lodDIVRatingSC" id="lodRatingShowOtherComments" style="display:none">
	    	<a id="lodRatingShowComments" 
	    		class="lodRatingActionLink"
	    		toggleText="{{lod_rt_hide_comments}}"
	    		value="show">{{lod_rt_show_comments}}</a>
		</div>    	
	    <div class="lodDivRatingOtherCommentsContainer" id="lodRatingOtherComments">
	    </div>
	</div>
    <div class="lodRatingSaveArea">
    	<div class="lodRatingButtons">
	        <input type="button" id="lodRatingSave" value="{{lod_rt_bt_save}}" /> 
	        <input type="button" id="lodRatingCancel" value="{{lod_rt_bt_cancel}}" />
	    </div>
    </div>
</div>
HTML;

/**
 * This class manages the HTML structure of the rating UI.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODQueryResultRatingUI  {
		
	//--- Constants ---
	
	// Number of characters that are displayed of the selected value
	const MAX_VALUE_LENGTH = 40;
	
	// Number of pathway links to display.
	const MAX_NUM_PATHWAYS = 7;
	
	//--- Public methods ---
	/**
	 * Returns the HTML for adding and viewing ratings of triples
	 * 
	 * @param $ratingKey
	 * 		The rating key is needed to retrieve the prefixes from the query.
	 * 		Can be <null>.
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
		global $LOD_QRR_RATING_HTML;
		$html = $LOD_QRR_RATING_HTML;
		
		$pm = TSCPrefixManager::getInstance();
		if (!is_null($ratingKey)) {
			$query = LODRatingAccess::getQueryForRatingKey($ratingKey);
			$pm->addPrefixesFromQuery($query[0]);
		}
		
		// Insert the value that will be rated
		if (strlen($value) > self::MAX_VALUE_LENGTH) {
			// Value is too long to be displayed
			$value = substr($value, 0, self::MAX_VALUE_LENGTH)."...";
		}
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
		
		// Replace language dependent strings
		$html = self::replaceLanguageStrings($html);
		return $html;
		
	}
	
	/**
	 * Returns the HTML representation of all ratings of the given $triple.
	 * @param TSCTriple $triple
	 * 		The ratings for this triple are retrieved
	 */
	public static function getRatingsForTripleHTML(TSCTriple $triple) {
		$ra = new LODRatingAccess();
		$ratings = $ra->getRatings($triple);

		// Create the list of all ratings with comments, authors and creation time
		$numCorrect = 0;
		$numWrong = 0;
		$html = <<<HTML
<div class="lodRatingCommentContainer">
	<table class="lodRatingComment">
	<colgroup>
		<col width="24px">
		<col width="100%%">
	</colgroup>
HTML;
		
		foreach ($ratings as $r) {
			$author = $r->getAuthor();
			$time = $r->getCreationTime();
			$time = str_replace("T","&nbsp;&nbsp;", $time);
			$time = str_replace("Z","", $time);
			$comment = $r->getComment();
			$comment = str_replace("\n", "<br />", $comment);
			$value = $r->getValue();
			$commentImg = $value === "true" ? "correct.png" : "wrong.png";
			$html .= <<<HTML
<tr>
	<td class="lodRatingCommentFlag">
		<img src="***imgPath***/$commentImg" alt="" />
	</td>
	<td>	
		<div>
			<span class="lodRatingAuthor">$author</span>
			<span class="lodRatingTime">$time</span>
		</div>
		<div>$comment</div>
	</td>
</tr>
HTML;
			if ($r->getValue() === "true") {
				++$numCorrect;
			} else if ($r->getValue() === "false") {
				++$numWrong;
			}
		}
		$html .= <<<HTML
	</table>
</div>
HTML;

		$html = <<<HTML
<div class="lodRatingStatistics">
	{{lod_rt_rating_statistics}}
	<img src="***imgPath***/correct.png" alt="" /> $numCorrect
	&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="***imgPath***/wrong.png" alt="" /> $numWrong
</div>
<div class="lodDivRatingComments">
	<p class="lodRatingCoou">{{lod_rt_user_comments}}</p>
	$html
</div>	
HTML;

		// Replace the image path
		global $lodgScriptPath;
		$imgPath = $lodgScriptPath . "/skins/img";
		$html = str_replace("***imgPath***", $imgPath, $html);
	
		// Replace language dependent strings
		$html = self::replaceLanguageStrings($html);
		
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

		// Each result set is wrapped in its own div
		
		$rsIdx = 1;
	    foreach ($triples as $resultSet) {
	    	$html .= <<<HTML
<div class="lodRatingResultSet" id="lodRatingResultSet_$rsIdx">
<span class="lodRatingGrayText">{{lod_rt_click_flag}}</span><br />
HTML;
			// Add the table of primary triples
			$html .= self::generateTriplesTable($rsIdx, 1, $resultSet[0]);
			
			// Add the table of secondary triples
			if (count($resultSet[1])) {
				$html .= <<<HTML
	<a class="lodRatingActionLink lodRatingOpenRelatedTriples" 
		id="lodRatingRateOthers_$rsIdx"
		toggleText="{{lod_rt_hide_related}}">{{lod_rt_rate_related}}</a>
	<div class="lodRatingRelatedTriples" id="lodRatingRelated_$rsIdx">
HTML;
				$html .= self::generateTriplesTable($rsIdx, 2, $resultSet[1]);
				$html .= <<<HTML
	</div>
HTML;
			}
			$html .= <<<HTML
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
	 * @param array<TSCTriple> $triples
	 * 		The array of triples
	 * @return string
	 * 		The HTML of the table
	 * 
	 */
	private static function generateTriplesTable($id, $primSec, array $triples) {
		$html = <<<HTML
<div class="lodRatingDivTriplesTable">	
	<table id="lodRatingTriples_{$id}_{$primSec}" class="lodRatingTriplesTable">
	<colgroup>
		<col width="20px">
		<col width="20px">
		<col width="33%">
		<col width="33%">
		<col width="33%">
	</colgroup>
HTML;
		
		$pm = TSCPrefixManager::getInstance();

		// Sort all triples by subject
		$tripleTable = array();
		foreach ($triples as $t) {
			$subj = $t->getSubject();
			$tripleTable[$subj][] = array($t->getPredicate(), $t->getObject());
		}
		
		$row = 0;
		foreach ($tripleTable as $subj => $poArray) {
			$first = true;
			$s = htmlentities($subj, ENT_COMPAT, "UTF-8");
			foreach ($poArray as $po) {
				$p = htmlentities($po[0], ENT_COMPAT, "UTF-8");
				$o = htmlentities($po[1], ENT_COMPAT, "UTF-8");
				
				$psubj = htmlentities($pm->makePrefixedURI($subj), ENT_COMPAT, "UTF-8");
				$ppred = htmlentities($pm->makePrefixedURI($po[0]), ENT_COMPAT, "UTF-8");
				$pobj  = htmlentities($pm->makePrefixedURI($po[1]), ENT_COMPAT, "UTF-8");
				
				$psubj = $first ? $psubj : "";
				
				$first = false;
			
				$html .= <<<HTML
<tr subject="$s" predicate="$p" object="$o" title="{{lod_rt_click_for_comments}}" class="lodRatingResultRow">
	<td>
		<img src="***imgPath***/correctDisabled.png" alt="" type="disabled" value="true" title="{{lod_rt_rate_correct}}" class="lodRatingFlag"/>
		<img src="***imgPath***/correct.png" alt="" type="hover" value="true" title="{{lod_rt_rate_correct}}" class="lodRatingFlag" style="display:none"/>
		<img src="***imgPath***/correctSelected.png" alt="" type="selected" value="true" title="{{lod_rt_rated_correct}}" class="lodRatingFlag" style="display:none"/>
	</td>
	<td>
		<img src="***imgPath***/wrongDisabled.png" alt="" type="disabled" value="false" title="{{lod_rt_rate_wrong}}" class="lodRatingFlag"/>
		<img src="***imgPath***/wrong.png" alt="" type="hover" value="false" title="{{lod_rt_rate_wrong}}" class="lodRatingFlag" style="display:none"/>
		<img src="***imgPath***/wrongSelected.png" alt="" type="selected" value="false" title="{{lod_rt_rated_wrong}}" class="lodRatingFlag" style="display:none"/>
	</td>
	<td>$psubj</td>
	<td>$ppred</td>
	<td title="{{lod_rt_value_may_differ}}">$pobj</td>
</tr>
HTML;

				++$row;
			}
		}
		$html .= <<<HTML
	</table>
</div>	
HTML;
		
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
		
		$numPathways = $numLinks = count($triples);
		$fastBack = "";
		$fastForward = "";
		$numTriples = "";
		if ($numPathways > self::MAX_NUM_PATHWAYS) {
			$numLinks = self::MAX_NUM_PATHWAYS;
			$numTriples = " ($numPathways)";
			$fastBack = "<a id=\"lodRatingPathwayFastBack\" class=\"lodRatingActionLink\">&lt;&lt;&nbsp;</a>";
			$fastForward = "<a id=\"lodRatingPathwayFastForward\" class=\"lodRatingActionLink\">&nbsp;&gt;&gt;</a>";
		}
		 
		$html = <<<HTML
<div class="lodDivRatingPathway" numpathways="$numPathways">
		{{lod_rt_pathways}}$numTriples $fastBack<a id="lodRatingPathwayBack" class="lodRatingActionLink">&lt;</a>
HTML;
		for ($i = 1, $len = $numLinks; $i <= $len; ++$i) {
			$html .= <<<HTML
 <a id="lodRatingPathway_$i" class="lodRatingPathwayIndex lodRatingActionLink">$i</a> 
HTML;
		}
		$html .= <<<HTML
        <a id="lodRatingPathwayForward" class="lodRatingActionLink">&gt;</a>$fastForward
    </div>
HTML;
		return $html;
	}
	
	/**
	 * Language dependent identifiers in $text that have the format {{identifier}}
	 * are replaced by the string that corresponds to the identifier.
	 * 
	 * @param string $text
	 * 		Text with language identifiers
	 * @return string
	 * 		Text with replaced language identifiers.
	 */
	private static function replaceLanguageStrings($text) {
		// Find all identifiers
		$numMatches = preg_match_all("/(\{\{(.*?)\}\})/", $text, $identifiers);
		if ($numMatches === 0) {
			return $text;
		}

		// Get all language strings
		$langStrings = array();
		foreach ($identifiers[2] as $id) {
			$langStrings[] = wfMsg($id);
		}
		
		// Replace all language identifiers
		$text = str_replace($identifiers[1], $langStrings, $text);
		return $text;
	}
}

