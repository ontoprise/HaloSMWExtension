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
	'smw_sn_notification_limit' => 
		'You can\'t add further notifications. Your limit of $1 notification(s)'.
		' is exceeded. Please contact your wiki administrator to extend the limit.',
	'smw_sn_not_logged_in' =>
		'You are currently not logged in. Anonymous users can not receive semantic '.
		'notifications. Please <a href="$1">log in</a>, before you create one.',
	'smw_sn_no_email' =>
		'You have not provided a valid e-mail address yet. Please go to '.
		'<a href="$1">"$2"</a> and enter an address in order to enable semantic'.
		' notifications.',
	'smw_sn_special1' => 'Enter query (you can use either ask or SPARQL) or use the',
	'smw_sn_special2' => 'Query Interface',
	'smw_sn_special3' => 'Show Preview',
	'smw_sn_special4' => 'Notifications for your query will be gathered over a'.
						 ' certain time span before the notification mail is sent.'.
						 ' Please enter how often you would like to receive this'.
						 ' notification. If there were no changes, no notification'.
						 ' will be sent.',
	'smw_sn_special5' => 'I would like to receive this notification every',
	'smw_sn_special6' => 'day(s).',
	'smw_sn_special7' => 'Enter a name for your notification:',
	'smw_sn_special8' => 'Please check the preview first.',
	'smw_sn_special9' => 'Add notification',
	'smw_sn_special10' => 'My notifications',

	'smw_qi_insertNotification' => 'Insert as notification',
	'smw_qi_tt_insertNotification' => 'Inserts the query as semantic notification',
	'smw_sn_tt_addNotification' => 'Adds the new or changed query to your personal semantic notifications.',
	'smw_sn_tt_showPreview' => 'Shows a preview of the results of your query.',
	'smw_sn_tt_openQueryInterface' => 'Go to the query interface to define your query.',

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
	'smw_sn_notification_limit' => 
		'Es können keine weiteren Benachrichtigungen hinzugefügt werden. Ihre '.
		'Grenze von $1 Benachrichtigungen ist erreicht.'.
		' Bitten Sie Ihren Wiki-Administrator, die Grenze zu erhöhen.',
	'smw_sn_not_logged_in' =>
		'Sie sind momentan nicht angemeldet. Anonyme Benutzer können keine '.
		'semantischen Benachrichtigungen erhalten. Bitte <a href="$1">melden Sie'.
		' zuerst sich an.</a>',
	'smw_sn_no_email' =>
		'Sie haben noch keine E-Mail-Adresse angegeben. Bitte gehen Sie zu '.
		'<a href="$1">"$2"</a> und geben Sie eine gültige Adresse an, damit Sie '.
		'semantische Benachrichtigungen erhalten können.',
	'smw_sn_special1' => 'Geben Sie eine Query ein (ASK oder SPARQL) oder benutzen Sie das',
	'smw_sn_special2' => 'Query Interface',
	'smw_sn_special3' => 'Vorschau zeigen',
	'smw_sn_special4' => 'Benachrichtigungen für eine Query werden über einen '.
						 'bestimmten Zeitraum gesammelt bevor eine E-Mail gesendet wird. '.
						 'Bitte geben Sie ein, wie oft Sie diese Benachrichtigung '.
						 'erhalten möchten. Wenn es keine Änderungen gibt, werden '.
						 'keine Benachrichtigungen versendet.',
	'smw_sn_special5' => 'Ich möchte diese Benachrichtigung alle',
	'smw_sn_special6' => 'Tage erhalten.',
	'smw_sn_special7' => 'Geben Sie Ihrer Benachrichtigung einen Namen:',
	'smw_sn_special8' => 'Sehen Sie sich zuerst die Vorschau an.',
	'smw_sn_special9' => 'Benachrichtigung hinzufügen',
	'smw_sn_special10' => 'Meine Benachrichtigungen',

	'smw_qi_insertNotification' => 'Als Benachrichtigung einfügen',
	'smw_qi_tt_insertNotification' => 'Fügt die Query als semantische Benachrichtigung ein.',
	'smw_sn_tt_addNotification' => 'Fügt die neue oder geänderte Query zu Ihren persönlichen semantischen Benachrichtigungen hinzu.',
	'smw_sn_tt_showPreview' => 'Zeigt eine Vorschau der Query-Ergebnisse.',
	'smw_sn_tt_openQueryInterface' => 'Definieren Sie die Query im Query-Interface.',

);
?>