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
/**
 * Internationalization file for LinkedData
 *
 */

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
    'lod_mapping_tag_ns'	 => 'The tag <mapping> is only evaluated in the namespace "Mapping".',
    'lod_no_mapping_in_ns'   => 'Articles in the namespace "Mapping" are supposed to contain mappings for linked data sources. You can add mapping descriptions in the tag &lt;mapping&gt;.',
	'lod_saving_mapping_failed' => '<b>The following mapping could not be saved:</b>',

);

/** 
 * German
 */
$messages['de'] = array(
    'lod_mapping_tag_ns'	 => 'Das Tag <zuordnung> wird nur im Namensraum "Mapping" ausgewertet.',
    'lod_no_mapping_in_ns'   => 'Artikel im Namensraum "Mapping" sollten Mappings für Linked Data Quellen beinhalten. Sie können Mapping-Beschreibungen im Tag &lt;zuordnung&gt; einfügen.',
	'lod_saving_mapping_failed' => '<b>Die folgende Zuordnung konnte nicht gespeichert werden:</b>',
);
