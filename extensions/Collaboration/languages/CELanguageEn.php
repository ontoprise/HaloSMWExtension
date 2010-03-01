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
 * @ingroup Collaboration
 * @author Benjamin Langguth
 */

/*
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $cegIP;
include_once($cegIP . '/languages/CELanguage.php');

class CELanguageEn extends CELanguage {
	
	protected $mUserMessages = array(
	);

	protected $mNamespaces = array(
		CE_COMMENT_NS		=> 'Comment',
		CE_COMMENT_NS_TALK	=> 'Comment_talk',
	);

	protected $mNamespaceAliases = array(
		'Comment'		=> CE_COMMENT_NS,
		'Comment_talk'	=> CE_COMMENT_NS_TALK,	
	);
	
	protected $mParserFunctions = array(
		CELanguage::CE_PF_SHOWCOMMENTS		=> 'showcomments', 
		CELanguage::CE_PF_SHOWFORM			=> 'showcommentform',
	);
	
	protected $mParserFunctionsParameters = array(
		CELanguage::CE_PFP_RATINGSTYLE	=> 'rating style',
	);
}