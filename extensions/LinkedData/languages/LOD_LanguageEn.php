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
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $lodgIP;
include_once($lodgIP . '/languages/LOD_Language.php');


/**
 * English language labels for important LinkedData labels (namespaces, ,...).
 *
 * @author Thomas Schweitzer
 */
class LODLanguageEn extends LODLanguage {

	protected $mNamespaces = array(
		LOD_NS_LOD       => 'LOD',
		LOD_NS_LOD_TALK  => 'LOD_talk',
		LOD_NS_MAPPING       => 'Mapping',
		LOD_NS_MAPPING_TALK  => 'Mapping_talk'
		);

	protected $mParserFunctions = array(
		LODLanguage::PF_RMAPPING			=> 'r2rMapping', 
		LODLanguage::PF_LSD				=> 'sourcedefinition',
		LODLanguage::PF_SMAPPING			=> 'silkMapping',  
	);
	
	protected $mParserFunctionsParameters = array(
		LODLanguage::PFP_MAPPING_TARGET		=> 'target', 
		LODLanguage::PFP_MAPPING_SOURCE		=> 'source', 
		
		LODLanguage::PFP_LSD_ID						=> "id",
    	LODLanguage::PFP_LSD_CHANGEFREQ				=> "changefreq",
		LODLanguage::PFP_LSD_DATADUMPLOCATION		=> "datadumplocation",
		LODLanguage::PFP_LSD_DESCRIPTION			=> "description",
		LODLanguage::PFP_LSD_HOMEPAGE				=> "homepage",
		LODLanguage::PFP_LSD_LABEL					=> "label",
		LODLanguage::PFP_LSD_LASTMOD				=> "lastmod",
		LODLanguage::PFP_LSD_LINKEDDATAPREFIX		=> "linkeddataprefix",
		LODLanguage::PFP_LSD_SAMPLEURI				=> "sampleuri",
		LODLanguage::PFP_LSD_SPARQLENDPOINTLOCATION	=> "sparqlendpointlocation",
		LODLanguage::PFP_LSD_SPARQLGRAPHNAME		=> "sparqlgraphname",
		LODLanguage::PFP_LSD_SPARQLGRAPHPATTERN		=> "sparqlgraphpattern",
		LODLanguage::PFP_LSD_URIREGEXPATTERN		=> "uriregexpattern",
		LODLanguage::PFP_LSD_VOCABULARY				=> "vocabulary",
		LODLanguage::PFP_LSD_PREDICATETOCRAWL		=> "predicatetocrawl",
               	LODLanguage::PFP_LSD_LEVELSTOCRAWL		=> "levelstocrawl",
		
		LODLanguage::PFP_SILK_MAPPING_MINT_NAMESPACE				=> 'mintNamespace',
		LODLanguage::PFP_SILK_MAPPING_MINT_LABEL_PREDICATE				=> 'mintLabelPredicate',
		
	);
		
}


