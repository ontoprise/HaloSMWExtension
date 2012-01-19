<?php
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
 * @ingroup Collaboration
 * @author Patrick Barret
 */

/*
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $cegIP;
include_once($cegIP . '/languages/CELanguage.php');

class CELanguageFr extends CELanguage {
	
	protected $mUserMessages = array(
	);

	protected $mNamespaces = array(
		CE_COMMENT_NS		=> 'Commentaire',
		CE_COMMENT_NS_TALK	=> 'Discussion_commentaire',
	);

	protected $mNamespaceAliases = array(
		'Commentaire'		=> CE_COMMENT_NS,
		'Discussion_commentaire'	=> CE_COMMENT_NS_TALK,	
	);
	
	protected $mParserFunctions = array(
	);
	
	protected $mParserFunctionsParameters = array(
	);
}
