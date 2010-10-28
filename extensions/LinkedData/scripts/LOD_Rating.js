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
 * @file
 * @ingroup LinkedDataScripts
 * @author: Thomas Schweitzer
 */

if (typeof LOD == "undefined") {
// Define the LOD module	
	var LOD = { 
		classes : {}
	};
}

/**
 * This is the class of the rating editor.
 */
LOD.classes.RatingEditor = function () {
	var that = {};

	// The HTML-ID of the currently selected result set.
	var mCurrentResultSet = null;
	
	// The currently selected triple and its rating
	var mCurrentRating = LOD.classes.Rating();
	
	/**
	 * Initialize the rating editor for the given element.
	 * 
	 * @param elem
	 * 		A span with class "lodMetadata"
	 */
	that.initRatingEditor = function (elem) {
		var url = wgServer + wgScriptPath + "/index.php?action=ajax";
		var ratingKey = elem.find('.lodRatingKey').html(); 
		var value = elem.html();
		
		mOverlay = elem.qtip("api");

		jQuery.ajax({ url:  url, 
					  data: "rs=lodafGetRatingEditorForKey&rsargs[]="
						  	+ ratingKey
						  	+ "&rsargs[]="
						  	+ encodeURIComponent(value),
					  success: that.ratingEditorLoaded
					});
	};
	
	/**
	 * This function is called when the rating editor was completely loaded.
	 * The editor is initialized.
	 */
	that.ratingEditorLoaded = function (data) {
		
		var $ = jQuery;
		
		$.fancybox(data, { 'frameWidth': 800, 'frameHeight': 600 });
		
		$('div.lodDivRatingRateAndComment').hide();
		$('div.lodDivRatingOtherCommentsContainer').hide();
		// hide all result sets
		$('div.lodRatingResultSet').hide();
		// show only the first result set
		$('#lodRatingResultSet_1').show();
		// hide the related triples
		$('div.lodRatingRelatedTriples').hide();
		// hide the save and cancel buttons
		$('div.lodRatingSaveArea').hide();
		$.fancybox.resize();
		
		mCurrentResultSet = 'lodRatingResultSet_1';
		
		// Add click handler for pathways
		$('a.lodRatingPathwayIndex').click(function () {
			// Show the div that contains the selected result set 
			var id = $(this).attr('id');
			id = id.substring('"lodRatingPathway_'.length-1);
			$("#"+mCurrentResultSet).hide();
			mCurrentResultSet = 'lodRatingResultSet_' + id;
			$("#"+mCurrentResultSet).show();
			
			return false;
		});
		
		// Add click handler for the opening related triples
		$('a.lodRatingOpenRelatedTriples').click(function () {
			// Open the section with related triples
			var id = $(this).attr('id');
			id = id.substring('"lodRatingRateOthers_'.length-1);
			$("#lodRatingRelated_"+id).toggle();
			return false;
		});
		
		// Add click handler for the rating flags
		$('img.lodRatingFlag').click(function () {
			// As soon as a triple is chosen, the rating area is opened
			$("div.lodDivRatingRateAndComment").show();

			// Remove highlights in all triple table
			$('tr.lodRatingHighlightSelection').removeClass('lodRatingHighlightSelection');
			// Highlight the row of the selected triple
			$(this).parents().eq(1).addClass('lodRatingHighlightSelection');
			
			// Store the current rating in mCurrentRating
			var selectedTriple = $(this).parents().eq(1);
			var subj = selectedTriple.attr('subject');
			var pred = selectedTriple.attr('predicate');
			var obj  = selectedTriple.attr('object');
			mCurrentRating.triple(LOD.classes.Triple(subj, pred, obj));
			var rating = $(this).attr('value');
			mCurrentRating.rating(rating);
			mCurrentRating.comment($('#lodRatingCommentTA').val());
			
			// Choose the title of the rating area according to the rating flag
			if (rating == 'true') {
				$('#lodRatingTitleWrong').hide();
				$('#lodRatingTitleCorrect').show();
			} else {
				$('#lodRatingTitleWrong').show();
				$('#lodRatingTitleCorrect').hide();
			}
			
			// Show the save/cancel area
			$('div.lodRatingSaveArea').show();
			return true;
		});
		
		// Add click handler for showing other comments
		$('#lodRatingShowComments').click(function () {
			$("div.lodDivRatingOtherCommentsContainer").show();
			return false;
		});
		
		// Add click handler for Cancel button
		$('#lodRatingCancel').click(function () {
			// close the overlay
			$.fancybox.close();
			return false;
		});
		
		
		// Add click handler for Cancel button
		$('#lodRatingSave').click(function () {
			// Save the current rating
			that.saveRating();
			return false;
		});
		
	};
	
	that.saveRating = function () {
	};
	return that;
}

/**
 * This class defines a rating. It contains the triple, if it is right or wrong
 * and the comment.
 * 
 * @param LOD.classes.Triple triple
 * 		A triple
 * @param string rating
 * 		"true" or "false"
 * @param string comment
 * 		The comment for the rating
 */
LOD.classes.Rating = function (triple, rating, comment) {
	var that = {};
	
	var mTriple = triple;
	var mRating = rating;
	var mComment = comment;
	
	/**
	 * Getter/setter for the member mTriple
	 */
	that.triple = function (triple) {
		return (typeof triple === 'undefined') ? mTriple : (mTriple = triple);
	}
	
	/**
	 * Getter/setter for the member mRating
	 */
	that.rating = function (rating) {
		return (typeof rating === 'undefined') ? mRating : (mRating = rating);
	}

	/**
	 * Getter/setter for the member mComment
	 */
	that.comment = function (comment) {
		return (typeof comment === 'undefined') ? mComment : (mComment = comment);
	}

	return that;
}

/**
 * This class defines a triple. 
 */
LOD.classes.Triple = function (subject, predicate, object) {
	var that = {};
	
	var mSubject = subject;
	var mPredicate = predicate;
	var mObject = object;
	
	
	
	return that;
}





LOD.ratingEditor = LOD.classes.RatingEditor();


jQuery(document).ready( function ($) {
		
	$("span.lodMetadata").click(function () {
		LOD.ratingEditor.initRatingEditor($(this));
		return false;
	});
	
});
