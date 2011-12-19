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
 * Created on 19.12.2011
 *
 * @file
 * @ingroup SREFSpecials
 * @ingroup SREFRefactor
 *
 * @author Kai Kühn
 */
if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( "$IP/includes/SpecialPage.php" );

/**
 * Semantic Refactoring special page
 *
 * @author Kai Kühn
 *
 */
class SREFRefactor extends SpecialPage {


    public function __construct() {
        parent::__construct('SREFRefactor', 'delete');
    }

    public function execute($par) {
        global $wgOut;
        $wgOut->setPageTitle(wfMsg('sref_specialrefactor'));
        $adminPage = Title::newFromText(wfMsg('sref_specialrefactor'), NS_SPECIAL);
        
        $html = wfMsg('sref_specialrefactor_description');
      
        $wgOut->addHTML($html);
    }


}

