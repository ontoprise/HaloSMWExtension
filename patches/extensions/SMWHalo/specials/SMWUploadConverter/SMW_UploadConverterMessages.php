<?php
/**
* Internationalization file for the Upload Converter.
* 
*   Copyright 2008, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
* 
*
* @addtogroup Extensions
*/

$messages = array();

/** 
 *  English
 *  @author Thomas Schweitzer
 */
$messages['en'] = array(
	'uc_not_converted' => '<b>Warning!</b><br/>'.
                          'The converter for the mime type "$1" did not generate an output file.<br/>'.
                          'The command line was:<br/>$2',
	'uc_edit_comment'  => "Content of original document added by the upload converter."
);

/** 
 *  German
 *  @author Thomas Schweitzer
 */
$messages['de'] = array(
	'uc_not_converted' => '<b>Warnung!</b><br/>'.
                          'Der Konverter für den Mime-Type "$1" erzeugte keine Ausgabedatei.<br/>'.
                          'Die Kommandozeile war:<br/>$2',
	'uc_edit_comment'  => "Der Inhalt des Originaldokuments wurde beim Hochladen hinzugefügt."
);
