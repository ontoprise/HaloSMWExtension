<?php

/*  Copyright 2010, MediaEvent Services GmbH & Co. KG
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
 * @author Magnus Niemann
 *
 * @ingroup LODSpecialPage
 * @ingroup SpecialPage
 */
class LODMappingsPage extends SpecialPage {
	
	static $r2rEditScripts = array(
		/* jQuery UI */
		"lib/jquery-ui-1.8.11.custom.min.js",

		/* rdfQuery */
		"lib/jquery.curie.js",
		"lib/jquery.uri.js",
		"lib/jquery.datatype.js",
		"lib/jquery.xmlns.js",
		"lib/jquery.rdf.js",
		"lib/jquery.rdf.json.js",
		"lib/jquery.rdf.turtle.js",
		"lib/jquery.rdf.xml.js",

		/* other libs */
		"lib/jquery.inherit-1.3.2.js",
		"lib/jquery.qtip-1.0.0-rc3.min.js",
		"lib/jquery.treeview.js",
		"lib/jquery.treeview.edit.js",
		"lib/jquery.placeholder.js",

		/* R2Redit */
		"js/r2redit.util.js",
		"js/r2redit.env.js",
		"js/r2redit.ui.js",
		"js/r2redit.overview.js",
		"js/r2redit.editor.js",
		"js/r2redit.js",
	);

	static $r2rEditStyles = array(
		"css/smoothness/jquery-ui-1.8.11.custom.css",
		"css/jquery.treeview.css",
		"css/style.css",
	);
	
	/**
	 * Path to R2Redit, relative to $lodgScriptPath
	 */
	static $r2rEditPath = "/libs/R2REdit";

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('LODMappings');
        wfLoadExtensionMessages('LODMappings');
    }

    function execute($p) {
        global $wgOut;
        global $wgScript;
        global $lodgScriptPath;

        $scriptFile = $lodgScriptPath . "/scripts/LOD_SpecialMappings.js";
        SMWOutputs::requireHeadItem("LOD_SpecialMappings.js",
                        '<script type="text/javascript" src="' . $scriptFile . '"></script>');
        SMWOutputs::requireHeadItem("lod_mappings.css",
                        '<link rel="stylesheet" type="text/css" href="' . $lodgScriptPath . '/skins/mappings.css" />');
                        
		foreach(self::$r2rEditScripts as $script) {
	        SMWOutputs::requireHeadItem(basename($script),
	                        '<script type="text/javascript" src="' . $lodgScriptPath . self::$r2rEditPath . '/' . $script . '"></script>');
		}

		foreach(self::$r2rEditStyles as $style) {
	        SMWOutputs::requireHeadItem(basename($style),
	                       '<link rel="stylesheet" type="text/css" href="' . $lodgScriptPath . self::$r2rEditPath . '/' . $style . '"/>');
		}

        SMWOutputs::commitToOutputPage($wgOut);
        $this->setHeaders();
        wfProfileIn('doLODMappings (LOD)');

		$wgOut->addHTML('<script language="JavaScript">jQuery(document).ready(function() { new jQuery.lodMapping(jQuery("#r2redit-container")); });</script>'
				. '<div id="r2redit-container"></div>');

        wfProfileOut('doLODMappings (LOD)');
    }
}