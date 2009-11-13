<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Interwiki-Article-Import module (IAI) of the 
*  Data-Import-Extension.
*
*   The Data-Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data-Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $iaigIP;
include_once($iaigIP . '/languages/IAI_Language.php');


/**
 * English language labels for important IAI labels (namespaces, ,...).
 *
 * @author Thomas Schweitzer
 */
class IAILanguageEn extends IAILanguage {

	protected $mNamespaces = array(
		IAI_NS_IAI       => 'IAI',
		IAI_NS_IAI_TALK  => 'IAI_talk'
	);
}


