<?php
/**
 * @file
 * @ingroup LinkedData_Language
 */

/*  Copyright 2010, ontoprise GmbH
*   This file is part of the LinkedData-Extension.
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

/*
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $tscgIP;
include_once($tscgIP . '/languages/TSC_Language.php');


/**
 * German language labels for important LinkedData labels (namespaces, ,...).
 *
 * @author Thomas Schweitzer
 */
class TSCLanguageDe_formal extends TSCLanguage {

    

    protected $mParserFunctions = array(
        
        TSCLanguage::PF_LSD             => 'quelldefinition'
        
        
    );
    
    protected $mParserFunctionsParameters = array(
    
        
        TSCLanguage::PFP_LSD_ID                     => "id",
        TSCLanguage::PFP_LSD_CHANGEFREQ             => "änderungsrate",
        TSCLanguage::PFP_LSD_DATADUMPLOCATION       => "dumport",
        TSCLanguage::PFP_LSD_DESCRIPTION            => "Beschreibung",
        TSCLanguage::PFP_LSD_HOMEPAGE               => "homepage",
        TSCLanguage::PFP_LSD_LABEL                  => "Bezeichnung",
        TSCLanguage::PFP_LSD_LASTMOD                => "letzteänderung",
        TSCLanguage::PFP_LSD_LINKEDDATAPREFIX       => "linkeddatapräfix",
        TSCLanguage::PFP_LSD_SAMPLEURI              => "beispieluri",
        TSCLanguage::PFP_LSD_SPARQLENDPOINTLOCATION => "sparqlendpunkt",
        TSCLanguage::PFP_LSD_SPARQLGRAPHNAME        => "sparqlgraphname",
        TSCLanguage::PFP_LSD_SPARQLGRAPHPATTERN     => "sparqlgraphpattern",
        TSCLanguage::PFP_LSD_URIREGEXPATTERN        => "uriregexmuster",
        TSCLanguage::PFP_LSD_VOCABULARY             => "vokabular",
        TSCLanguage::PFP_LSD_PREDICATETOCRAWL       => "zufolgendesprÃ¤dikat"
        
        
        
    );
        
}


