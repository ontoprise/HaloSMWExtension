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
 * @ingroup TreeView_Language
 *
 * English version of the Treeview language class.
 * @author Thomas Schweitzer
*/
if (!defined('MEDIAWIKI')) die();

/**
 * English language labels for important Treeview labels (parser functions, ,...).
 *
 * @author Thomas Schweitzer
 */
class TVLanguageEn extends TVLanguage {

	protected $mParserFunctions = array(
		TVLanguage::PF_TREE				=> 'tree', 
		TVLanguage::PF_GENERATE_TREE	=> 'generateTree'
	);
	
	protected $mParserFunctionsParameters = array(
		TVLanguage::PFP_ROOT		=> 'root', 
		TVLanguage::PFP_ROOT_LABEL	=> 'rootlabel', 
		TVLanguage::PFP_THEME		=> 'theme',
		TVLanguage::PFP_PROPERTY	=> 'property',
		TVLanguage::PFP_SOLR_QUERY	=> 'solrquery',
		TVLanguage::PFP_FILTER		=> 'filter',
		TVLanguage::PFP_WIDTH		=> 'width',
		TVLanguage::PFP_HEIGHT		=> 'height'
	);
	
}


