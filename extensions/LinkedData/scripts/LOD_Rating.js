/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
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
	
	// 0-based index of the first result set (pathway) link
	var mCurrentResultSetBase = 0;
	
	// The currently selected triple and its rating
	var mCurrentRating = LOD.classes.Rating();
	
	// The currently selected triple 
	var mCurrentlySelectedTriple = null;
	
	// The jQuery object of the currently selected flag image
	var mActiveFlag = null;
	
	/**
	 * Initialize the rating editor for the given element.
	 * 
	 * @param elem
	 * 		A span with class "lodMetadata"
	 */
	that.initRatingEditor = function (elem) {
		var url = wgServer + wgScriptPath + "/index.php?action=ajax";
		var ratingKey = elem.find('.lodRatingKey').html(); 
		var value = "";
		// Get the text value of <elem> (first text only, not the hidden rating key)
		elem.contents()
			.each(function () {
				if (this.nodeValue !== "" && value === "") {
					value = this.nodeValue;
				}
			});

		// Show the throbber
		var imgSource = wgServer + wgScriptPath + '/extensions/LinkedData/skins/img/throbber.gif';
		elem.append('<img src="'+imgSource+'" id="lodRatingThrobber" />');

		// Load the editor via ajax
		jQuery.ajax({ url:  url, 
					  data: "rs=lodafGetRatingEditorForKey&rsargs[]="
						  	+ ratingKey
						  	+ "&rsargs[]="
						  	+ encodeURIComponent(value),
					  success: that.ratingEditorLoaded,
					  type: 'POST',
					  complete: function (request, status) {
							jQuery('#lodRatingThrobber').remove();
					  }
					});
	};
	
	/**
	 * This function is called when the rating feature is selected in the 
	 * Ontology Browser. A triple is passed in <subject>, <predicate> and
	 * <object>.
	 * If at least one of these values is <null>, nothing happens. Otherwise
	 * the rating editor is opened for that triple. 
	 * 
	 * @param {string} subject
	 * @param {string} predicate
	 * @param {string} object
	 * @param {string} value
	 * 		The selected value
	 */
	that.selectedTripleInOB = function(subject, predicate, object, value) {
		if (!subject || !predicate || !object) {
			return;
		}
		var url = wgServer + wgScriptPath + "/index.php?action=ajax";

		var triple = LOD.classes.Triple(subject, predicate, object);
		var tripleJSON = JSON.stringify(triple);
		// Load the editor via ajax
		jQuery.ajax({ url:  url, 
					  data: "rs=lodafGetRatingEditorForTriple&rsargs[]="
						  	+ encodeURIComponent(tripleJSON)
						  	+ "&rsargs[]="
						  	+ encodeURIComponent(value),
							
					  success: that.ratingEditorLoaded,
					  type: 'POST',
					});
	}
	
	
	/**
	 * This function is called when the rating editor was completely loaded.
	 * The editor is initialized.
	 */
	that.ratingEditorLoaded = function (data) {
		var $ = jQuery;
		
		$.fancybox(data, 
				{
					'hideOnOverlayClick' : false,
					'scrolling' : 'no'
				});
		
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
		
		$('#fancbox-wrap')
			.height($('.lodDivRatingMain').height()+24)
			.width($('.lodDivRatingMain').width()+24);
		
		// Init the style of the indexes in the pathway
		$('#lodRatingPathway_1')
			.css('font-weight', 'bolder')
			.css('font-size', '150%')
		
		mCurrentResultSet = 'lodRatingResultSet_1';
		
		that.initRatingEditorEventBindings();
		
	};
	
	/**
	 * Initializes the event bindings of the UI elements in the rating editor.
	 */
	that.initRatingEditorEventBindings = function () {
		var $ = jQuery;
		
		// Add click handler for all four pathway arrows
		$('#lodRatingPathwayBack, #lodRatingPathwayForward,'
		  + '#lodRatingPathwayFastBack, #lodRatingPathwayFastForward').click(function () {
			that.pathwayArrowClicked($(this));
			return false;
		});

		// Add click handler for pathway indices
		$('a.lodRatingPathwayIndex').click(function () {
			that.pathwayIndexClicked($(this));
			return false;
		});
		
		// Add click handler for opening related triples
		$('a.lodRatingOpenRelatedTriples').click(function () {
			that.relatedTriplesClicked($(this));
			return false;
		});
		
		// Add mouse-enter handler for the disabled rating flags
		$('img.lodRatingFlag[type=disabled]').mouseenter(function () {
			that.showHoverFlag($(this));
			return false;
		});

		// Add click handler for the hover rating flags
		$('img.lodRatingFlag[type=hover]').click(function () {
			that.ratingFlagClicked(jQuery(this));
			return false;
		});
		
		// Add click handler for rows in the result tables
		$('.lodRatingResultRow').click(function () {
			that.resultRowClicked($(this));
			return true;
		});
		
		// Add click handler for showing other comments
		$('#lodRatingShowComments').click(function () {
			that.showCommentsClicked($(this));
			return false;
		});
		
		// Add click handler for Cancel button
		$('#lodRatingCancel').click(function () {
			// close the overlay
			$.fancybox.close();
			return false;
		});
		
		
		// Add click handler for Save button
		$('#lodRatingSave').click(function () {
			// Save the current rating
			that.saveRating();
			return false;
		});
		
	}
	
	/**
	 * Loads the HTML for the ratings and comments of the currently selected
	 * triple.
	 */
	that.showOtherComments = function () {
	
		var url = wgServer + wgScriptPath + "/index.php?action=ajax";

		// Show the throbber
		var imgSource = wgServer + wgScriptPath + '/extensions/LinkedData/skins/img/throbber.gif';
		jQuery('#lodRatingOtherComments').children().remove();
		jQuery('#lodRatingOtherComments').append('<img src="'+imgSource+'" id="lodRatingThrobber" />');
		jQuery("#lodRatingOtherComments").show();

		var t = mCurrentlySelectedTriple;
		
		// Load the editor via ajax
		jQuery.ajax({ 
			url:  url, 
			data: "rs=lodafGetRatingsForTriple&rsargs[]="
			  	+ encodeURIComponent(JSON.stringify(t)),
			success: that.tripleRatingsLoaded,
			error: function (request, status, error) {
				jQuery('#lodRatingOtherComments')
					.replaceWith("<div>"+request.responseText+"</div>");
			},
			type: 'POST',
			complete: function (request, status) {
				jQuery('#lodRatingThrobber').remove();
			}
		});
		
	};
	
	/**
	 * This function is called when the ratings and comments for a triple are 
	 * completely loaded.
	 * The HTML is inserted into the editor.
	 * 
	 * @param string html
	 * 	The HTML for all ratings
	 * 
	 */
	that.tripleRatingsLoaded = function (html) {
		
		var $ = jQuery;
		$('#lodRatingOtherComments').children().remove();
		$('#lodRatingOtherComments').append(html);
		
	};
	
	/**
	 * Sets the text of the link "Show comments"/"Hide comments" according to 
	 * the current visibility of the comments section.
	 */
	that.updateShowCommentsLink = function() {
		var commentsVisible = jQuery("#lodRatingOtherComments").is(":visible");
		// If the comments are visible, the link must offer to hide them
		var link = jQuery('#lodRatingShowComments');
		var currLinkAction = link.attr('value');
		
		var toggle = false;
		if (commentsVisible && currLinkAction == 'show') {
			currLinkAction = 'hide';
			toggle = true;
		} else if (!commentsVisible && currLinkAction == 'hide') {
			currLinkAction = 'show';
			toggle = true;
		}
		if (toggle) {
			// Toggle the content of the action link
			var toggleText = link.attr('toggleText');
			var currentText = link.text();
			link.text(toggleText)
				.attr('toggleText', currentText)
				.attr('value', currLinkAction);
		}

	}
		
	/***************************************************************************
	 * 
	 * Event callbacks
	 * 
	 **************************************************************************/
	
	/**
	 * Shows the hover rating flag that is a sibling of <elem>
	 * @param jQuery object elem
	 * 		A disabled or selected flag
	 */
	that.showHoverFlag = function (elem) {
		elem.parent().children('[type!=hover]').hide();
		elem.parent().children('[type=hover]')
			.show()
			.unbind('mouseleave')
			.mouseleave(function () {
				that.showDisabledFlag(jQuery(this));
				return false;
			});
	};
	
	/**
	 * Shows the disabled rating flag that is a sibling of <elem>
	 * @param jQuery object elem
	 * 		A hover or selected flag
	 */
	that.showDisabledFlag = function (elem) {
		elem.parent().children('[type!=disabled]').hide();
		elem.parent().children('[type=disabled]').show();
	};

	/**
	 * Shows the selected rating flag that is a sibling of <elem>
	 * @param jQuery object elem
	 * 		A hover or disabled flag
	 */
	that.showSelectedFlag = function (elem) {
		// remove the mouse-leave event from the hover flag
		elem.parent().children('[type=hover]').unbind('mouseleave');
		
		elem.parent().children('[type!=selected]').hide();
		elem.parent().children('[type=selected]').show();
		
	};
	
	/**
	 * This function is called when a pathway arrow was clicked.
	 * Shows the next result set according to the selected arrow.
	 * @param elem
	 * 		The wrapped element that was clicked
	 */
	that.pathwayArrowClicked = function (elem) {
		var $ = jQuery;
		
		// Get the number of results
		var numPathwayLinks = $('.lodRatingPathwayIndex').length;
		var numResults = $('.lodDivRatingPathway').attr('numpathways')*1;
		
		var idx = mCurrentResultSet.substr('"lodRatingResultSet_'.length-1)*1;

		if (elem.attr('id') == 'lodRatingPathwayBack') {
			// Backward arrow clicked
			--idx;
		} else if (elem.attr('id') == 'lodRatingPathwayForward') {
			// Forward arrow clicked
			++idx;
		} else if (elem.attr('id') == 'lodRatingPathwayFastBack') {
			// Fast backward arrow clicked
			idx -= numPathwayLinks;
		} else if (elem.attr('id') == 'lodRatingPathwayFastForward') {
			// Fast forward arrow clicked
			idx += numPathwayLinks;
		}
		
		// Consistency checks for the new index
		if (idx < 1) {
			// Go to the last set of pathway links
			idx = numResults;
		} else if (idx > numResults) {
			// Go to the first set of pathway links
			idx = 1;
		}
		
		// Show the correct set of pathway links
		var baseIdx = Math.floor((idx-1) / numPathwayLinks) * numPathwayLinks;
		if (baseIdx != mCurrentResultSetBase) {
			// Relabel all pathway links
			mCurrentResultSetBase = baseIdx;
			var linkIdx = baseIdx + 1;
			$('.lodRatingPathwayIndex').each(function () {
				$(this).text(linkIdx);
				if (linkIdx > numResults) {
					$(this).hide();
				} else {
					$(this).show();
				}
				linkIdx++;
			});
		}
		that.pathwayIndexClicked($('#lodRatingPathway_' + (idx - mCurrentResultSetBase)));
		
		return false;
	};
	
	/**
	 * This function is called when a pathway index was clicked.
	 * Shows the next result set according to the selected index.
	 * @param elem
	 * 		The wrapped element that was clicked
	 */
	that.pathwayIndexClicked = function (elem) {
		var $ = jQuery;
		
		// Show the selected index inbold face
		$('.lodRatingPathwayIndex')
			.css('font-weight', 'normal')
			.css('font-size', '100%');
		elem.css('font-weight', 'bolder')
			.css('font-size', '150%');
		
		// Remove selection highlight of all triples in the table
		jQuery('tr.lodRatingHighlightSelection').removeClass('lodRatingHighlightSelection');
		mCurrentlySelectedTriple = null;

		// Show the div that contains the selected result set
		var id = elem.attr('id');
		id = id.substring('"lodRatingPathway_'.length-1);
		$("#"+mCurrentResultSet).hide();
		mCurrentResultSet = 'lodRatingResultSet_' + (id*1+mCurrentResultSetBase);
		$("#"+mCurrentResultSet).show();
		
		// Hide the link for opening other comments
		jQuery("#lodRatingShowOtherComments").hide();
		// Hide other comments
		jQuery("#lodRatingOtherComments").hide();
		that.updateShowCommentsLink();
		
	};
	
	/**
	 * The action link "Rate/Hide related triples" was clicked. 
	 * Open the table of related triples and change the label to 
	 * "Hide/Rate related triples" and vice versa.
	 * @param elem
	 * 		The clicked action link
	 */
	that.relatedTriplesClicked = function (elem) {
		// Open the section with related triples
		var id = elem.attr('id');
		id = id.substring('"lodRatingRateOthers_'.length-1);
		jQuery("#lodRatingRelated_"+id).toggle();
		
		// Toggle the content of the action link
		var toggleText = elem.attr('toggleText');
		var currentText = elem.text();
		elem.text(toggleText)
			.attr('toggleText', currentText);

	};
	
	/**
	 * The link for showing/hiding comments was clicked. 
	 * Toggle the text of the link and show/hide comments.
	 * 
	 * @param elem
	 * 		The clicked link
	 */
	that.showCommentsClicked = function (elem) {
		
		jQuery("#lodRatingOtherComments").toggle();
		if (jQuery("#lodRatingOtherComments").is(":visible")) {
			// Comments are visible => load the comments
			that.showOtherComments();
		}
		
		that.updateShowCommentsLink();
	};
	
	/**
	 * This function is called when a result row is clicked. The "comments" section
	 * for this triple is opened.
	 * 
	 * @param elem
	 * 		The <tr> element of the result row.
	 */
	that.resultRowClicked = function (elem) {
		//Show the link for opening other comments
		jQuery("#lodRatingShowOtherComments").show();

		// Remove highlights of all triples in the table
		jQuery('tr.lodRatingHighlightSelection').removeClass('lodRatingHighlightSelection');
		// Highlight the row of the selected triple
		elem.addClass('lodRatingHighlightSelection');
		
		// Store the currently selected triple
		var subj = elem.attr('subject');
		var pred = elem.attr('predicate');
		var obj  = elem.attr('object');
		mCurrentlySelectedTriple = LOD.classes.Triple(subj, pred, obj);

		that.showOtherComments();
		that.updateShowCommentsLink();
	};
	
	/**
	 * This function is called when a rating flag is clicked. 
	 * The area for adding a comment is opened. 
	 */
	that.ratingFlagClicked = function (elem) {
		var $ = jQuery;
		// Disable the previously selected rating flag
		if (mActiveFlag && mActiveFlag != elem) {
			that.showDisabledFlag(mActiveFlag);
		}
		// Swap the images which indicate the current state
		that.showSelectedFlag(elem);

		// Store the currently clicked image for toggling it later
		mActiveFlag = elem;
		
		// As soon as a triple is chosen, the rating area is opened
		$("div.lodDivRatingRateAndComment").show();
		//Show the link for opening other comments
		$("#lodRatingShowOtherComments").show();

		// Remove highlights in all triple table
		$('tr.lodRatingHighlightSelection').removeClass('lodRatingHighlightSelection');
		// Highlight the row of the selected triple
		elem.parents().eq(1).addClass('lodRatingHighlightSelection');
		
		// Store the current rating in mCurrentRating
		var selectedTriple = elem.parents().eq(1);
		var subj = selectedTriple.attr('subject');
		var pred = selectedTriple.attr('predicate');
		var obj  = selectedTriple.attr('object');
		mCurrentlySelectedTriple = LOD.classes.Triple(subj, pred, obj);
		mCurrentRating.triple(mCurrentlySelectedTriple);
		var rating = elem.attr('value');
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
		
		// Update the other comments if they are visible
		if ($('#lodRatingOtherComments').is(':visible')) {
			that.showOtherComments();
		}

		// Show the save/cancel area
		$('div.lodRatingSaveArea').show();

	}

	/**
	 * Saves the current rating on the server.
	 */
	that.saveRating = function () {
		mCurrentRating.comment(jQuery('#lodRatingCommentTA').val());

		var json = encodeURIComponent(JSON.stringify(mCurrentRating));

		var url = wgServer + wgScriptPath + "/index.php?action=ajax";

		jQuery.ajax({ url:  url, 
			data: "rs=lodafSaveRating&rsargs[]=" + json,
			success: function (data) {
				// close the overlay
				jQuery.fancybox(data);
				window.setTimeout(function() {
					jQuery.fancybox.close();
					}, 2000);
				;
			},
			error: function (request, status, error) {
				jQuery.fancybox(request.responseText, 
						{
							showCloseButton: true
						});
			},
			type: 'POST',
		});
		
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
	
	/**
	 * Serializes this object to JSON
	 * @return string
	 * 		This object as JSON
	 */
	that.toJSON = function () {
		var json = {
			triple	: mTriple,
			rating	: mRating,
			comment	: mComment
		};
		return json;
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
	
	/**
	 * Serializes this object to JSON
	 * @return string
	 * 		This object as JSON
	 */
	that.toJSON = function () {
		var json = {
			subject		: mSubject,
			predicate	: mPredicate,
			object		: mObject
		};
		return json;
	}
	
	return that;
}


LOD.ratingEditor = LOD.classes.RatingEditor();

jQuery(document).ready( function ($) {
	var metadataSpans = $("span.lodMetadata:has(.lodRatingKey)");
	
	// Open the rating editor when a value is clicked
	metadataSpans.click(function () {
		LOD.ratingEditor.initRatingEditor($(this));
		return false;
	});
	
	// Show a pointing hand when hovering a value
	metadataSpans.css("cursor", "pointer");
	
});
