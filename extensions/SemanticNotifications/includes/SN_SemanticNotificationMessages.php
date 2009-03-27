<?php
/*  Copyright 2009, ontoprise GmbH
* 
*   This file is part of the SemanticNotifications-Extension.
*
*   The SemanticNotifications-Extension is free software; you can redistribute 
*   it and/or modify it under the terms of the GNU General Public License as 
*   published by the Free Software Foundation; either version 3 of the License, 
*   or (at your option) any later version.
*
*   The SemanticNotifications-Extension is distributed in the hope that it will 
*   be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
$messages = array();

/** 
 *  English
 *  @author Thomas Schweitzer
 */
$messages['en'] = array(
	'semanticnotifications' => 'Semantic Notifications',
	'sn_special_page' => 'Semantic Notifications', 
	'sn_special_url_name' => 'SemanticNotifications',
	'sn_explanation' => 'With semantic notifications, you can receive '.
						'notifications based on queries. Please enter a query'.
						' either in ask or SPARQL syntax into the query form or'.
						' use the query interface to create one. After you checked'.
						' the preview, you can add a semantic notification. This'.
						' means that you will receive a notification every time the'.
						' result set of your query changes. The toolbar on the'.
						' right shows your existing notifications. You can'.
						' use it to edit or delete existing notifications.',
	'sn_notification_limit' => 
		'You can\'t add further notifications. Your limit of $1 notification(s)'.
		' is exceeded. Please contact your wiki administrator to extend the limit.',
	'sn_not_logged_in' =>
		'You are currently not logged in. Anonymous users can not receive semantic '.
		'notifications. Please <a href="$1">log in</a>, before you create one.',
	'sn_no_email' =>
		'You have not provided or confirmed a valid e-mail address yet. Please go to '.
		'<a href="$1">"$2"</a> and enter an address in order to enable semantic'.
		' notifications.',
	'sn_special1' => 'Enter query (you can use either ask or SPARQL) or use the',
	'sn_special2' => 'Query Interface',
	'sn_special3' => 'Show Preview',
	'sn_special4' => 'Notifications for your query will be gathered over a'.
					 ' certain time span before the notification mail is sent.'.
					 ' Please enter how often you would like to receive this'.
					 ' notification. If there were no changes, no notification'.
					 ' will be sent.',
	'sn_special5' => 'I would like to receive this notification every',
	'sn_special6' => 'day(s).',
	'sn_special7' => 'Enter a name for your notification:',
	'sn_special8' => 'Please check the preview first.',
	'sn_special9' => 'Add notification',
	'sn_special10' => 'My notifications',

	'sn_qi_insertNotification' => 'Insert as notification',
	'sn_qi_tt_insertNotification' => 'Inserts the query as semantic notification',
	'sn_tt_addNotification' => 'Adds the new or changed query to your personal semantic notifications.',
	'sn_tt_showPreview' => 'Shows a preview of the results of your query.',
	'sn_tt_openQueryInterface' => 'Go to the query interface to define your query.',

	'sn_semanticnotificationbot' => 'Semantic Notifications',
	'sn_gard_semanticnotificationhelp' => 'Sends notifications, if the result sets of watched queries have changed.',
	
	'sn_msg_salutation' =>  'Dear $1,<br />'.
							'you are receiving this e-mail, because your SMW notification "<b>$2</b>" detected '.
							'a change in the semantic database of your wiki.<br />',
	'sn_msg_query' =>  'The result of the following query changed:<br /><pre>$1</pre><br />',
	'sn_msg_changes_found' => '<br />The following changes were found:<br />'.
								  '(<span style="border:2px solid; border-color:#ff0000;">Removed</span> '.
								  '<span style="border:2px solid; border-color:#00ff00">Added</span> '.
								  '<span style="border:2px solid; border-color:#ffaa00">Changed (old values in parenthesis)</span>)<br /><br />',
	'sn_msg_numadded' => '$1 result(s) was/were added.<br />',
	'sn_msg_numremoved' => '$1 result(s) was/were removed.<br />',
	'sn_msg_limit' => '<br />This notification does not contain the details of the semantic changes, '.
                          'as the size of the result set exceeds your limit. Please contact your wiki\'s '.
						  'administrator if detailed change descriptions are essential for you.<br /><br />',
	'sn_msg_link' => '<br />To manage your notifications, please go to $1.<br /><br />    --Your SMW notification service.',

	'sn_processed_notification' => 'Processed notification "$1" of user "$2".',
	'sn_mail_title' => 'Semantic notification from $1',


);

/** 
 *  German
 *  @author Thomas Schweitzer
 */
$messages['de'] = array(
	'sn_special_page' => 'Semantische Benachrichtigungen',
	'sn_special_url_name' => 'SemantischeBenachrichtigungen',
	'sn_explanation' => 'Mit semantischen Benachrichtigen haben Sie die Möglichkeit, '.
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
	'sn_notification_limit' => 
		'Es können keine weiteren Benachrichtigungen hinzugefügt werden. Ihre '.
		'Grenze von $1 Benachrichtigungen ist erreicht.'.
		' Bitten Sie Ihren Wiki-Administrator, die Grenze zu erhöhen.',
	'sn_not_logged_in' =>
		'Sie sind momentan nicht angemeldet. Anonyme Benutzer können keine '.
		'semantischen Benachrichtigungen erhalten. Bitte <a href="$1">melden Sie'.
		' zuerst sich an.</a>',
	'sn_no_email' =>
		'Sie haben noch keine E-Mail-Adresse angegeben oder bestätigt. Bitte gehen Sie zu '.
		'<a href="$1">"$2"</a> und geben Sie eine gültige Adresse an, damit Sie '.
		'semantische Benachrichtigungen erhalten können.',
	'sn_special1' => 'Geben Sie eine Query ein (ASK oder SPARQL) oder benutzen Sie das',
	'sn_special2' => 'Query Interface',
	'sn_special3' => 'Vorschau zeigen',
	'sn_special4' => 'Benachrichtigungen für eine Query werden über einen '.
					 'bestimmten Zeitraum gesammelt bevor eine E-Mail gesendet wird. '.
					 'Bitte geben Sie ein, wie oft Sie diese Benachrichtigung '.
					 'erhalten möchten. Wenn es keine Änderungen gibt, werden '.
					 'keine Benachrichtigungen versendet.',
	'sn_special5' => 'Ich möchte diese Benachrichtigung alle',
	'sn_special6' => 'Tage erhalten.',
	'sn_special7' => 'Geben Sie Ihrer Benachrichtigung einen Namen:',
	'sn_special8' => 'Sehen Sie sich zuerst die Vorschau an.',
	'sn_special9' => 'Benachrichtigung hinzufügen',
	'sn_special10' => 'Meine Benachrichtigungen',

	'sn_qi_insertNotification' => 'Als Benachrichtigung einfügen',
	'sn_qi_tt_insertNotification' => 'Fügt die Query als semantische Benachrichtigung ein.',
	'sn_tt_addNotification' => 'Fügt die neue oder geänderte Query zu Ihren persönlichen semantischen Benachrichtigungen hinzu.',
	'sn_tt_showPreview' => 'Zeigt eine Vorschau der Query-Ergebnisse.',
	'sn_tt_openQueryInterface' => 'Definieren Sie die Query im Query-Interface.',

	'sn_semanticnotificationbot' => 'Semantische Benachrichtigungen',
	'sn_gard_semanticnotificationhelp' => 'Versendet Nachrichten, wenn sich die Ergebnisse von beobachteten Queries verändert haben.',

	'sn_msg_salutation' =>  'Liebe(r) $1,<br />'.
							'Sie erhalten diese E-Mail, weil sich in der semantischen Datenbank'.
							' Ihres Wikis Änderungen bezüglich Ihrer semantischen Benachrichtigung'.
							'"<b>$2</b>" ergeben haben.<br />',
	'sn_msg_query' =>  'Die Ergebnismenge der folgenden Query hat sich geändert:<br /><pre>$1</pre><br />',
	'sn_msg_changes_found' => '<br />Die folgenden Änderungen wurden gefunden:<br />'.
								  '(<span style="border:2px solid; border-color:#ff0000">Entfernt</span> '.
								  '<span style="border:2px solid; border-color:#00ff00">Hinzugefügt</span> '.
								  '<span style="border:2px solid; border-color:#ffaa00">Verändert (alte Werte in Klammern)</span>)<br /><br />',
	'sn_msg_numadded' => '$1 Ergebnis(se) wurde(n) hinzgefügt.<br />',
	'sn_msg_numremoved' => '$1 Ergebnis(se) wurde(n) entfernt.<br />',
	'sn_msg_limit' => '<br />Diese Benachrichtigung enthält keine Details zu den Veränderungen der semantischen Annotationen, '.
						  'da die Größe der Ergebnismenge Ihre Begrenzungen überschreitet. Bitte setzen Sie sich'.
						  ' mit Ihrem Wiki-Administrator in Verbindung.<br /><br />',
	'sn_msg_link' => '<br />Bitte gehen Sie zum Verwalten Ihrer Benachrichtigungen zu $1.<br /><br />    --Ihr SMW Benachrichtigungsdienst.',

	'sn_processed_notification' => 'Die Benachrichtigung $1 des Benutzers "$2" wurde bearbeitet.',
	'sn_mail_title' => 'Semantische Benachrichtigung von $1',

);
?>