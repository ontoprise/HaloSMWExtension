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
	'smw_sn_special_page' => 'Semantic Notifications',
	'semanticnotifications' => 'Semantic Notifications',
	'smw_sn_explanation' => 'With semantic notifications, you can receive '.
							'notifications based on queries. Please enter a query'.
							' either in ask or SPARQL syntax into the query form or'.
							' use the query interface to create one. After you checked'.
							' the preview, you can add a semantic notification. This'.
							' means that you will receive a notification every time the'.
							' result set of your query changes. The toolbar on the'.
							' right shows your existing notifications. You can'.
							' use it to edit or delete existing notifications.',
);

/** 
 *  German
 *  @author Thomas Schweitzer
 */
$messages['de'] = array(
	'smw_sn_special_page' => 'Semantische Benachrichtigungen',
	'semanticnotifications' => 'Semantische Benachrichtigungen',
	'smw_sn_explanation' => 'Mit semantischen Benachrichtigen haben Sie die Möglichkeit, '.
	                         'Änderungsmitteilungen auf Basis semantischer Anfragen'.
							 ' zu erhalten. Geben Sie bitte eine Anfrage in ask- '.
							 'oder SPARQL-Syntax im Eingabefeld ein oder erzeugen Sie'.
							 'eine mit dem Query Interface. Nachdem Sie die Ergebnisse in'.
							 'der Vorschau überprüft haben, können Sie die semantische'.
							 ' Benachrichtigung hinzufügen. Sie werden dann eine Nachricht'.
							 ' erhalten wenn sich die Ergebnismenge der Anfrage ändert.'.
							 ' Die Werkzeugleiste auf der rechten Seite zeigt Ihre Benachrichtungen.'.
							 ' Sie können sie zum Verändern oder Löschen existierender'.
							 ' Benachrichtigungen verwenden.',

);
?>