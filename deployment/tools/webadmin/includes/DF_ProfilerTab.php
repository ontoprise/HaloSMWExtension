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
 * Profiler tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
    die();
}


class DFProfilerTab {

    /**
     * DFProfilerTab 
     *
     */
    public function __construct() {

    }

    public function getTabName() {
        global $dfgLang;
        return $dfgLang->getLanguageString('df_webadmin_profilertab');
    }

    public function getHTML() {
        global $dfgLang, $wgServer, $wgScriptPath;

        $html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_profilertab_description')."</div>";
        $html .= "<input type=\"button\"  id=\"df_enableprofiling\"></input>";
        $html .= "<div id=\"df_webadmin_profiler_content\">";
        $html .= "<table>";
        $html .= "<tr><td>";
        $html .= "<textarea rows=\"20\" cols=\"80\" id=\"df_webadmin_profilerlog\" disabled=\"true\"></textarea>";
        $html .= "<div><input type=\"button\" value=\"".$dfgLang->getLanguageString('df_webadmin_refresh')."\" id=\"df_refreshprofilinglog\"></input></div>";
        $html .= "</td><td>TODO: actions and aggregated data</td>";
        $html .= "</tr>";
        $html .= "</table>";
        $html .= "</div>";
       
        return $html;
    }

   
}
