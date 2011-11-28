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
 * @ingroup LinkedData_Language
 */
/*
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $tscgIP;
include_once($tscgIP . '/languages/TSC_Language.php');


/**
 * English language labels for important LinkedData labels (namespaces, ,...).
 *
 * @author Thomas Schweitzer
 */
class TSCLanguageEn extends TSCLanguage {

	

	protected $mParserFunctions = array(
	
		TSCLanguage::PF_LSD				=> 'sourcedefinition'
	
	);
	
	protected $mParserFunctionsParameters = array(
		
		TSCLanguage::PFP_LSD_ID						=> "id",
    	TSCLanguage::PFP_LSD_CHANGEFREQ				=> "changefreq",
		TSCLanguage::PFP_LSD_DATADUMPLOCATION		=> "datadumplocation",
		TSCLanguage::PFP_LSD_DESCRIPTION			=> "description",
		TSCLanguage::PFP_LSD_HOMEPAGE				=> "homepage",
		TSCLanguage::PFP_LSD_LABEL					=> "label",
		TSCLanguage::PFP_LSD_LASTMOD				=> "lastmod",
		TSCLanguage::PFP_LSD_LINKEDDATAPREFIX		=> "linkeddataprefix",
		TSCLanguage::PFP_LSD_SAMPLEURI				=> "sampleuri",
		TSCLanguage::PFP_LSD_SPARQLENDPOINTLOCATION	=> "sparqlendpointlocation",
		TSCLanguage::PFP_LSD_SPARQLGRAPHNAME		=> "sparqlgraphname",
		TSCLanguage::PFP_LSD_SPARQLGRAPHPATTERN		=> "sparqlgraphpattern",
		TSCLanguage::PFP_LSD_URIREGEXPATTERN		=> "uriregexpattern",
		TSCLanguage::PFP_LSD_VOCABULARY				=> "vocabulary",
		TSCLanguage::PFP_LSD_PREDICATETOCRAWL		=> "predicatetocrawl",
               	TSCLanguage::PFP_LSD_LEVELSTOCRAWL		=> "levelstocrawl"
		
	
		
	);
		
}


