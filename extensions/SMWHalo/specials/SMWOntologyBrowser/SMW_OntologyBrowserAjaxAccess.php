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
 * Created on 26.02.2007
 *
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloDataExplorer
 *
 * @author Kai K�hn
 *
 * Delegates AJAX calls to database and encapsulate the results as XML.
 * This allows easy transformation to HTML on client side.
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $smwgHaloIP, $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ob_OntologyBrowserAccess';

function smwf_ob_OntologyBrowserAccess($method, $params, $dataSource = '', $bundleID = '') {

    $browseWiki = wfMsg("smw_ob_source_wiki");
    global $smwgHaloQuadMode, $smwgHaloWebserviceEndpoint;
    if (isset($smwgHaloWebserviceEndpoint) && $smwgHaloQuadMode && !empty($dataSource) && $dataSource != $browseWiki) {
        // dataspace parameter. so assume quad driver is installed
        $storage = new OB_StorageTSQuad($dataSource, $bundleID);
    } else if (isset($smwgHaloWebserviceEndpoint)) {
        // assume normal (non-quad) TSC is running
        $storage = new OB_StorageTS($dataSource, $bundleID);
    } else {
        // no TSC installed
        $storage = new OB_Storage($dataSource, $bundleID);
    }


    $p_array = explode("##", $params);
    $method = new ReflectionMethod(get_class($storage), $method);
    return $method->invoke($storage, $p_array, $dataSource);

}



