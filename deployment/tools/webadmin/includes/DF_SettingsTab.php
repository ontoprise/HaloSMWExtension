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
 * @ingroup WebAdmin
 *
 * The WAT settings tab allows specifying options the user would be
 * asked interactively in the command line version.
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
    die();
}


class DFSettingsTab {

    /**
     * WAT settings tab
     *
     */
    public function __construct() {

    }

    public function getTabName() {
        global $dfgLang;
        return $dfgLang->getLanguageString('df_webadmin_watsettingstab');
    }

    public function getHTML() {
        global $dfgLang, $wgServer, $wgScriptPath;
        
        $html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_watsettingstab_description')."</div>";
               
        $html .= "<div id=\"df_watsettings\">";
        $html .= "<table>";
        $html .= "<tr>";
        $html .= "<td><input id=\"df_watsettings_overwrite_always\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_overwrite_always')."</input></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td><input id=\"df_watsettings_contain_to_other_bundle\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_contain_to_other_bundle')."</input></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td><input id=\"df_watsettings_apply_patches\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_apply_patches')."</input></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td><input id=\"df_watsettings_create_restorepoints\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_create_restorepoints')."</input></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td><input id=\"df_watsettings_hidden_annotations\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_hidden_annotations')."</input></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td><input id=\"df_watsettings_use_namespaces\" type=\"checkbox\">".$dfgLang->getLanguageString('df_watsettings_use_namespaces')."</input></td>";
        $html .= "</tr>";
        $html .= "</table>";
        $html .= "</div>";
        return $html;
    }

   
}
