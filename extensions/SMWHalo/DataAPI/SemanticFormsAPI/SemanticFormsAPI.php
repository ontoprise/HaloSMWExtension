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
  * @ingroup DASemanticForms
  * 
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI that deal with the SemanticForms component
 * @defgroup DASemanticForms
 * @ingroup DataAPI
 */

if ( !defined( 'SF_VERSION' ) ){ 
	die("The Semantic Forms Data API requires the Semantic Forms extension");
}
	
$wgAPIModules['sfdata'] = 'SFDataAPI';
//todo: path anpassen
//todo: nur laden wenn semantic forms aktiv
$wgAutoloadClasses['SFDataAPI'] = $smwgHaloIP."/DataAPI/SemanticFormsAPI/WS/SF_DataAPI.php";
