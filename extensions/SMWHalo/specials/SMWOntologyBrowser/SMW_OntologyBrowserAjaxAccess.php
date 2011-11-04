<?php
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



