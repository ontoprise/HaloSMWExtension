<?php

/*  Copyright 2010, MES GmbH
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
 * @ingroup LODSpecialPage
 * @ingroup SpecialPage
 */
class LODLinkSpecsPage extends SpecialPage {

    var $editorUrl = "http://localhost:30300/";

    public function __construct() {
        parent::__construct('LODLinkSpecs');
        wfLoadExtensionMessages('LODLinkSpecs');
    }

    function execute($p) {
        global $wgOut;
        global $wgScript;
        global $lodgScriptPath;

        SMWOutputs::commitToOutputPage($wgOut);
        $this->setHeaders();
        
        $html = "<iframe src=\"".$this->editorUrl."\" height=\"500\" width=\"1100px\">You need a Frames Capable browser to view this content.</iframe>";

        $wgOut->addHTML($html);

       }



}