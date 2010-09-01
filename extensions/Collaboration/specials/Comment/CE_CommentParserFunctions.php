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
 * @file
 * @ingroup CEComment
 * 
 * This file contains takes care about parser functions 
 * for comment component of Collaboration extension.
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

	$wgParser->setFunctionHook('showcommentform', 'CECommentParserFunctions::showcommentform');
	return true;
}

function cefCommentLanguageGetMagic( &$magicWords, $langCode ) {
	global $cegContLang;
	$magicWords['showcommentform'] = array(
		0, $cegContLang->getParserFunction(CELanguage::CE_PF_SHOWFORM)
	);

	return true;
}


/**
 * The class CECommentParserFunctions contains all parser functions of the Comment component of
 * Collaboration extension. The following functions are parsed:
 * - show comment form
 *
 */
class CECommentParserFunctions {

	//--- Constants ---
	const SUCCESS = 0;
	
	const FORM_ALREADY_SHOWN = 1;
	const NOBODY_ALLOWED_TO_COMMENT = 2;
	const USER_NOT_ALLOWED_TO_COMMENT = 3;
	
	const COMMENTS_DISABLED = 4;
	const COMMENTS_FOR_NOT_DEF = 5;
	
	
	//--- Private fields ---
	// Title: The title to which the functions are applied
	private $mTitle = 0;

	// bool: Is the form already displayed?
	private $mCommentFormDisplayed = false;

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
	 * 			... if there's sthg wrong, that can not be caught by CE itself
	 */
	public static function showcommentform(&$parser) {
		global $cegContLang, $wgUser, $cegScriptPath, $cegEnableRatingForArticles, $wgJsMimeType;

		$jsText = <<<END
<script type="{$wgJsMimeType}">/* <![CDATA[ */ var cegScriptPath = '{$cegScriptPath}';
END;
		
		# do checks #
		$status = self::$mInstance->doInitialChecks($parser);

		switch ($status) {
			case self::SUCCESS:
				//continue
				break;
			case self::COMMENTS_DISABLED:
				return self::$mInstance->commentFormWarning(wfMsg('ce_cf_disabled'));
				break;
			case self::COMMENTS_FOR_NOT_DEF:
				return self::$mInstance->commentFormWarning(wfMsg('ce_var_undef', 'cegEnableCommentFor'));
			case self::NOBODY_ALLOWED_TO_COMMENT:
				return self::$mInstance->commentFormWarning(wfMsg('ce_cf_all_not_allowed'));
			case self::USER_NOT_ALLOWED_TO_COMMENT:
				return self::$mInstance->commentFormWarning(wfMsg('ce_cf_you_not_allowed')); 
				break;
			case self::FORM_ALREADY_SHOWN:
				return self::$mInstance->commentFormWarning(wfMsg('ce_cf_already_shown'));
				break;
			default:
				throw new CEException(CEException::INTERNAL_ERROR, __METHOD__ . ": Unknown value `{$status}` <br/>" );
		}

		$params = self::$mInstance->getParameters(func_get_args());
		// handle the (optional) parameter "ratingstyle".
		#list($style) = self::$mInstance->mStyle($params);
		
		$encPreComment = htmlspecialchars(wfMsg('ce_cf_predef'));
		$comment_disabled = '';

		#rating#
		$ratingValues = array( 0 => wfMsg('ce_ce_rating_0'),
			1 => wfMsg('ce_ce_rating_1'),
			2 => wfMsg('ce_ce_rating_2'));
		$ratingTitleBad = wfMsg('ce_cf_rating_title_b');
		$ratingTitleNeutral = wfMsg('ce_cf_rating_title_n');
		$ratingTitleGood = wfMsg('ce_cf_rating_title_g');
		
		#user#
		$currentUser = $wgUser->getName();
		$ns = MWNamespace::getCanonicalName(NS_USER);
		$jsText .= "var ceUserNS = '$ns';";

		if($wgUser->isAnon()) {
			$userImageTitle = Title::newFromText('defaultuser.gif', NS_FILE);
			$userIsSysopJSText = 'var cegUserIsSysop = false;';
			if($userImageTitle->exists()){
				$image = Image::newFromTitle($userImageTitle);
				$userImgSrc = $image->getURL();
			}
		} else {
			//user should be saved with Namespace!
			SMWQueryProcessor::processFunctionParams(array("[[".$ns.":".$wgUser->getName()."]]", "[[User_image::+]]", "?User_image=")
				,$querystring,$params,$printouts);
			$queryResult = explode("|",
			SMWQueryProcessor::getResultFromQueryString($querystring,$params,
				$printouts, SMW_OUTPUT_WIKI));

			unset($queryResult[0]);

			//just get the first property value and use this
			if(isset($queryResult[1])) {
				$userImageTitle = Title::newFromText($queryResult[1], NS_FILE);
				if($userImageTitle->exists()){
					$image = Image::newFromTitle($userImageTitle);
					$userImgSrc = $image->getURL();
				}
			}

			// Get users groups and check for Sysop-Rights
			$groups = $wgUser->getEffectiveGroups();
			$isAllowed = false;
			if (in_array( 'sysop', $wgUser->getEffectiveGroups() ) == 1) {
				//provide delete link for every existing comment
				$userIsSysopJSText = 'var cegUserIsSysop = true;';
			} else {
				$userIsSysopJSText = 'var cegUserIsSysop = false;';
			}
		}
		if(!isset($userImgSrc) || !$userImgSrc) {
			// We provide own icon, if there non in the wiki
			$userImgSrc = $cegScriptPath. '/skins/Comment/icons/defaultuser.gif';
		}

		$submitButtonID = 'collabComFormSubmitbuttonID';
		$resetButtonID = 'collabComFormResetbuttonID'; 

		$ratingHTML = '';
		if( isset($cegEnableRatingForArticles) && $cegEnableRatingForArticles ) {
			$ratingHTML = XML::openElement('div', array( 'id' => 'collabComFormRating')) .
					wfMsg('ce_cf_article_rating') .
					'<span class="collabComFormGrey">' . '&nbsp;' . 
						wfMsg('ce_cf_article_rating2') . 
					'</span>' . ":" .
					XML::openElement('span', array( 'id' => 'collabComFormRadiobuttons' )) .
						XML::Element('img', array( 'id' => 'collabComFormRating1',
							'class' => 'collabComFormRatingImg',
							'src' => $cegScriptPath . '/skins/Comment/icons/bad_inactive.png',
							'title' => $ratingTitleBad,
							'onClick' => 'ceCommentForm.switchRating(\'#collabComFormRating1\',-1);' )) .
						XML::Element('img', array( 'id' => 'collabComFormRating2',
							'class' => 'collabComFormRatingImg',
							'src' => $cegScriptPath . '/skins/Comment/icons/neutral_inactive.png',
							'title' => $ratingTitleNeutral,
							'onClick' => 'ceCommentForm.switchRating(\'#collabComFormRating2\',0);' )) .
						XML::Element('img', array( 'id' => 'collabComFormRating3',
							'class' => 'collabComFormRatingImg',
							'title' => $ratingTitleGood,
							'src' => $cegScriptPath . '/skins/Comment/icons/good_inactive.png',
							'onClick' => 'ceCommentForm.switchRating(\'#collabComFormRating3\',1);' )) .
					XML::closeElement('span') .
				XML::closeElement('div'); 
		}

		$html = XML::openElement( 'div', array( 'id' => 'collabComFormHeader' )) .
			XML::openElement( 'form', array( 'method' => 'post', 'id' => 'collabComForm',
			'style' => 'display:none',		
			'onSubmit' => 'return ceCommentForm.processForm()' ) ) . 
			XML::openElement('div', array('id' => 'collabComFormUserIcon')) .
				XML::Element( 'img', array( 'id' => 'collabComFormUserImg',
					'src' => $userImgSrc? $userImgSrc : '' )) .
			XML::closeElement('div') .
			XML::openElement('div', array('id' => 'collabComFormRight')) .
				XML::openElement( 'div', array( 'id' => 'collabComFormUser') ) .
					'<span class="userkey">' .wfMsg('ce_cf_author') . '</span>' . 
					'<span class="uservalue">' . $currentUser . '</span>' .
				XML::closeElement('div') .
				$ratingHTML .
				XML::openElement('div', array( 'id' => 'collabComFormHelp')) .
					wfMsg('ce_cf_comment') .
					XML::openElement('span', array('class' => 'red')) .
						'*' .
					XML::closeElement('span') . 
					XML::openElement('span') . ':' . XML::closeElement('span') .
				XML::closeElement('div') .
				XML::openElement('textarea', array( 'id' => 'collabComFormTextarea',
					'rows' => '5', 'defaultValue' => $encPreComment,
					'onClick' => 'ceCommentForm.selectTextarea();',
					'onKeyDown' => 'ceCommentForm.textareaKeyPressed();')) .
				$encPreComment .
				XML::closeElement('textarea') .
			XML::submitButton( wfMsg( 'ce_cf_submit_button_name' ), 
				array ( 'id' => $submitButtonID) ) .
			XML::element( 'input', array( 'type' => 'reset', 
				'value' => wfMsg( 'ce_cf_reset_button_name' ),
				'id' => $resetButtonID, 'onClick' => 'ceCommentForm.formReset();')) .
			XML::closeElement('div') . //end collabComFormRight
			XML::closeElement('form') .
			XML::openElement('div', array('id' => 'collabComFormMessage', 
				'style' => 'display:none')) .
			XML::closeElement('div') .
			XML::closeElement('div');

		$html .= $jsText . ($userIsSysopJSText? $userIsSysopJSText : '') . '/* ]]> */ </script>';

		self::$mInstance->mCommentFormDisplayed = true;
		return $parser->insertStripItem( $html, $parser->mStripState );
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
	 * @param $parser Parser object
	 * @return status code (see defines at top)
	 */
	private function doInitialChecks(&$parser) {
		global $cegContLang, $wgUser;
		
		$pfContLangName = $cegContLang->getParserFunction(CELanguage::CE_PF_SHOWCOMMENTS);

		
		# Check if titles fit #
		$title = $parser->getTitle();
		if (self::$mInstance->mTitle == null) {
			self::$mInstance->mTitle = $title;
		}
		// disabled to due problems when importing or updating articles.
//		 else if ($title->getArticleID() != self::$mInstance->mTitle->getArticleID()) {
//			throw new CEException(CEException::INTERNAL_ERROR,
//                "The parser functions " . $pfContLangName .
//                "are called for different articles.");
//		}

		global $cegEnableComment, $cegEnableCommentFor;
		# check if comments enabled #
		if ( !isset($cegEnableComment) || !$cegEnableComment )
			return self::COMMENTS_DISABLED;
		if ( !isset($cegEnableCommentFor) )
			return self::COMMENTS_FOR_NOT_DEF;

		# check authorization #
		if ($cegEnableCommentFor == CE_COMMENT_NOBODY) {
			return self::NOBODY_ALLOWED_TO_COMMENT;
		} elseif ( ($cegEnableCommentFor == CE_COMMENT_AUTH_ONLY) &&
			!($wgUser->isAnon()) ) {
			return self::USER_NOT_ALLOWED_TO_COMMENT;
		} else {
			//user is allowed
			/* leads to strange errors in IE. check that again.
			if( self::$mInstance->mCommentFormDisplayed ) {
				return self::FORM_ALREADY_SHOWN;
			} else {*/
				return self::SUCCESS;
			/*}*/
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
		
		//TODO: reformat!
		
		$html = '<h2>' . wfMsgHtml( 'ce_warning' ) . "</h2>\n";
		$html .= '<ul class="collabComWarning">' . $warning . "</ul>\n";
		
		return $html;
	}

} //end class CECommentParserFunctions