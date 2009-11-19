<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Collaboration-Extension.
 *
 *   The Collaboration-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Collaboration-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file contains the implementation of parser functions for Collaboration.
 *
 * @author Benjamin Langguth
 * Date: 16.11.2009
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Collaboration extension. It is not a valid entry point.\n" );
}

### Includes ###
global $cegIP;

$wgExtensionFunctions[] = 'cefInitCommentParserfunctions';

$wgHooks['LanguageGetMagic'][] = 'cefCommentLanguageGetMagic';


function cefInitCommentParserfunctions() {
	global $wgParser;

	CECommentParserFunctions::getInstance();

	$wgParser->setFunctionHook('showcomments', 'CECommentParserFunctions::showcomments');
	$wgParser->setFunctionHook('showcommentform', 'CECommentParserFunctions::showcommentform');
	return true;
}

function cefCommentLanguageGetMagic( &$magicWords, $langCode ) {
	global $cegContLang;
	$magicWords['showcomments']	= array( 
		0, $cegContLang->getParserFunction(CELanguage::CE_PF_SHOWCOMMENTS)
	);
	$magicWords['showcommentform'] = array(
		0, $cegContLang->getParserFunction(CELanguage::CE_PF_SHOWFORM)
	);

	return true;
}


/**
 * The class CECommentParserFunctions contains all parser functions of the Comment component of
 * Collaboration extension. The following functions are parsed:
 * - show comments
 * - show comment form
 *
 * @author Benjamin Langguth
 *
 */
class CECommentParserFunctions {

	//--- Constants ---
	const SUCCESS = 0;
	const FORM_ALREADY_SHOWN = 1;
	const COMMENTS_ALREADY_SHOWN = 2;
	const USER_NOT_ALLOWED_TO_COMMENT = 3;
	const USER_NOT_ALLOWED_TO_QUERY = 4;
	const NO_COMMENTS_AVAILABLE = 5;
	const COMMENTS_DISABLED = 6;
	
	//--- Private fields ---
	// Title: The title to which the functions are applied
	private $mTitle = 0;

	// bool: Is the form already displayed?
	private $mCommentFormDisplayed = false;

	// bool: Are the related comments already displayed?
	private $mCommentsDisplayed = false;

	// array(HACLRight): All inline rights of the title
	private $mStyle = array();

	// bool: list of related comment titles
	private $mRelCommentTitles = array();
		
	// bool: true if all parser functions of an article are valid
	private $mDefinitionValid = true;

	// CECommentParserFunctions: The only instance of this class
	private static $mInstance = null;

	/**
	 * Constructor for CECommentParserFunctions. This object is a singleton.
	 */
	public function __construct() {
	}

	//--- Public methods ---

	public static function getInstance() {
		if (is_null(self::$mInstance)) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	/**
	 * Callback for parser function "#showcomments:".
	 * This parser function takes care of the related comments of an article.
	 * It can appear only once in an article.
	 * 
	 * @param Parser $parser
	 * 		The parser object
	 *
	 * @return string
	 * 		Wikitext
	 *
	 * @throws
	 * 		CEException(CEException::INTERNAL_ERROR)
	 * 			... if the parser function is called more than once for a single article
	 */
	public static function showcomments(&$parser) {
		global $cegContLang;

		$status = self::$mInstance->doInitialChecks(CELanguage::CE_PF_SHOWCOMMENTS, $parser);

		
		/*
		 * 		case self::COMMENTS_ALREADY_SHOWN:
				//display notice and do nothing
				break;
							case self::USER_NOT_ALLOWED_TO_QUERY:
				//display notice and do nothing
				break;
							case self::NO_COMMENTS_AVAILABLE:
				break;
					case self::SUCCESS:
				//continue
				break;

	
		 */
		if (count($errMsgs) == 0) {
			// create query
			self::$mInstance->mRelCommentTitles = array();
		} else {
			self::$mInstance->mDefinitionValid = false;
		}

		// foobar..
		
		self::$mInstance->mCommentsDisplayed = true;
		return $text;
	}
	
	/**
	 * Callback for parser function "#showcommentform:".
	 * This parser function displays the commentform.
	 * It can appear only once in an article and has the following parameters:
	 * ratingstyle:
	 *
	 * @param Parser $parser
	 * 		The parser object
	 *
	 * @return string
	 * 		Wikitext
	 *
	 * @throws
	 * 		CEException(CEException::INTERNAL_ERROR)
	 * 			... if there's sthg wrong
	 */
	public static function showcommentform(&$parser) {
		global $cegContLang;

		//TODO: into seperate function doChecks?
		
		$status = self::$mInstance->doInitialChecks(CELanguage::CE_PF_SHOWFORM, $parser);
		
		switch ($status) {
			case self::COMMENTS_DISABLED:
				return self::$mInstance->commentFormWarning(wfMsg('ce_cf_disabled'));
				break;
			case self::FORM_ALREADY_SHOWN:
				return self::$mInstance->commentFormWarning(wfMsg('ce_cf_already_shown'));
				break;
			case self::USER_NOT_ALLOWED_TO_COMMENT:
				return self::$mInstance->commentFormWarning(wfMsg('ce_cf_not_allowed')); 
				break;
			case self::SUCCESS:
				//continue
				break;
			default:
				throw new CEException(CEException::INTERNAL_ERROR, __METHOD__ . ": Unknown value `{$status}`" );
		}
		

		$params = self::$mInstance->getParameters(func_get_args());
		// handle the (optional) parameter "ratingstyle".
		#list($style) = self::$mInstance->mStyle($params);
		
		// => create the form now if user is allowed to comment
		$rows = 4;
		$cols = 4;
		
		$encPreComment = htmlspecialchars(wfMsgForContentNoTrans('ce_cf_predef'));
		
		$user_disabled = '';
		$comment_disabled = '';
		
		$userField = "<input type='text' size='30' id='ce_cf_user_field' '$user_disabled' />";
		
		$commentTextArea = "<textarea rows='$rows' cols='$cols'
			id='ce_cf_textarea' '$comment_disabled' defaultValue='$encPreComment' onClick=''></textarea>";
		
		$form = XML::openElement('form', array( 'method' => 'post', 'id' => 'ce_cf',
						'action' => '', 'onSubmit' => 'return ceCommentForm.processForm()' )) . 
			XML::openElement( 'fieldset' ).
			XML::element('legend', null, wfMsg( 'ce_cf_legend' )) .
			XML::openElement('table', array('border' =>'0', 'id' => 'ce_cf_table')).
			'<tr>' .
				'<td class="ce_cf_input">' .
					$userField .
				'</td>' .
			'</tr>' .
			'<tr>' .
				'<td class="ce_cf_textarea">' .
					$commentTextArea .
				'</td>' .
			'</tr>';
					
		$submitButtonName = 'ce_cf_submitbuttonName';
		$submitButtonID = 'ce_cf_submitbuttonID'; 
		
		$form .= '<tr>' .
				'<td class="ce_comment_form_input">' .
					XML::submitButton( wfMsg( 'ce_cf_submit_button_name' ), 
						array ( 'id' => $submitButtonID,
							'value' => wfMsg( 'ce_cf_submit_button_name' ),
							'onSubmit' => 'return ceCommentForm.processForm();' ) ) .
				'</td>' .
			'</tr>' .
			'<tr>';
		$form .=
			XML::closeElement('table') .
			XML::closeElement('fieldset');
			XML::closeElement('form');
		//$html = XML::buildForm();

		// foobar

		#if (!empty($name)) {
			// User is allowed to comment
		#$text .= wfMsgForContent('ce_collaboration_not_allowed');
			
		#} else {
			// not!
			//TODO: grey out form or dont even display?.
			$text = wfMsgForContent('ce_allowed');
		#}

		self::$mInstance->mCommentFormDisplayed = true;
		return $parser->insertStripItem( $form, $parser->mStripState );
	}

	
	
	/**
	 * This method is called, when an article is moved. If the article "has" comment article(s)
	 * they should be changed to, otherwise the relation is lost!
	 *
	 * @param unknown_type $specialPage
	 * @param Title $oldTitle
	 * @param Title $newTitle
	 */
	public static function articleMove(&$specialPage, &$oldTitle, &$newTitle) {
		$oldName = $oldTitle->getFullText();
		$newName = $newTitle->getFullText();
		// Check if the old title has comment article(s)
		if (empty($this->mRelCommentTitles))
			$this->mRelCommentTitles = CECommentQuery::getRelatedCommentArticles($oldTitle, '');
		if ($this->mRelCommentTitles !== false && is_array($this->mRelCommentTitles) &&
			!empty($this->mRelCommentTitles)) {
			// ok, we have to change all articles in the list!
			CECommentStorage::updateCommentArticles($this->mRelCommentTitles, $oldTitle, $newName); 
		}
		//do nothing otherwise. there are no related comments!

		return true;
	}
	
	/**
	 * This method is called, when an article is deleted. If the article "has" comment article(s)
	 * they should be also deleted to prevent article corps.
	 *
	 * @param unknown_type $specialPage
	 * @param Title $title
	 */
	public static function articleDelete(&$specialPage, &$title) {
		$name = $title->getFullText();
		// Check if the title has comment article(s)
		if (empty($this->mRelCommentTitles))
			$this->mRelCommentTitles = CECommentQuery::getRelatedCommentArticles($title, '');
		if ($this->mRelCommentTitles !== false && is_array($this->mRelCommentTitles) &&
			!empty($this->mRelCommentTitles)) {
			$msg = CECommentStorage::deleteRelatedCommentArticles($this->mRelCommentTitles);
		}
		return true;
	}
	
	/**
	 * This method is called, when an article is undeleted. 
	 * Well, wee need a DB Table that stores the article - comment - relation
	 * just for this functionality :(
	 *
	 * @param unknown_type $specialPage
	 * @param Title $title
	 */
	public static function articleUndelete(&$title, &$create) {
		$name = $title->getFullText();
		// Check if the old title has comment article(s)
		// -> we need check in the table!

		return true;
	}
	
	
	
	### Private Functions ###
	
	/**
	 * Returns the parser function parameters that were passed to the parser-function
	 * callback.
	 *
	 * @param array(mixed) $args
	 * 		Arguments of a parser function callback
	 * @return array(string=>string)
	 * 		Array of argument names and their values.
	 */
	private function getParameters($args) {
		$parameters = array();

		foreach ($args as $arg) {
			if (!is_string($arg)) {
				continue;
			}
			if (preg_match('/^\s*(.*?)\s*=\s*(.*?)\s*$/', $arg, $p) == 1) {
				$parameters[strtolower($p[1])] = $p[2];
			}
		}

		return $parameters;
	}
	
	/**
	 * does all the checks.
	 * 
	 * @param $pfCode Code of parser function taken from CELanguage
	 * @param $parser Parser object
	 * @return status code (see defines at top)
	 */
	private function doInitialChecks($pfCode, &$parser) {
		global $cegContLang, $cegEnableComment, $cegEnableCommentFor, $wgUser;
		
		$pfContLangName = $cegContLang->getParserFunction(CELanguage::CE_PF_SHOWCOMMENTS);

		# check parser functions #
		switch ($pfCode) {
			case CELanguage::CE_PF_SHOWFORM:
				# check if comments enabled #
				if (!isset($cegEnableComment) || !$cegEnableComment)
					return self::COMMENTS_DISABLED;

				# check authorization #
				if (!isset($cegEnableCommentFor) || !$cegEnableCommentFor || ($cegEnableCommentFor == CE_COMMENT_NOBODY)) {
					return self::USER_NOT_ALLOWED_TO_COMMENT;
				} elseif ((!$cegEnableCommentFor == CE_COMMENT_AUTH_ONLY) && !$wgUser->isLoggedIn()) {
					return self::USER_NOT_ALLOWED_TO_COMMENT;
				} else {
					//user is allowed
					if( self::$mInstance->mCommentFormDisplayed ) {
						return self::FORM_ALREADY_SHOWN;
					}
				}
				continue;
			case CELanguage::CE_PF_SHOWCOMMENTS:
				if ( self::$mInstance->mCommentsDisplayed ) {
					return self::COMMENTS_ALREADY_SHOWN;
				}
			default:
				//go on.
		}

		# Check if titles fit #
		$title = $parser->getTitle();
		if (self::$mInstance->mTitle == null) {
			self::$mInstance->mTitle = $title;
		} else if ($title->getArticleID() != self::$mInstance->mTitle->getArticleID()) {
			throw new CEException(CEException::INTERNAL_ERROR,
                "The parser functions " . $pfContLangName .
                "are called for different articles.");
		}

		return self::SUCCESS;
	}
	
	
	/**
	 * There's something wrong with the comment function.
	 *
	 * @param string $warning as HTML
	 * @access private
	 */
	private function commentFormWarning( $warning ) {
		
		$html = '<h2>' . wfMsgHtml( 'ce_warning' ) . "</h2>\n";
		$html .= '<ul class="ce_warning">' . $warning . "</ul>\n";
		
		return $html;
	}
	
	
} //end class CECommentParserFunctions