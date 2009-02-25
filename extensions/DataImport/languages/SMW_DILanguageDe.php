<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the Data Import-Extension.
*
*   The Data Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @author Markus KrÃ¶tzsch
 */

global $smwgDIIP;
include_once($smwgDIIP . '/languages/SMW_DILanguage.php');

class SMW_DILanguageDe extends SMW_DILanguage {

protected $smwUserMessages = array(
    'specialpages-group-di_group' => 'Data Import',
	
	/* Messages of the Thesaurus Import */
	'smw_ti_welcome' => 'Bitte wähle zuerst ein Transport Layer Module (TLM) und danach ein Data Access Module (DAM) aus:',
	'smw_ti_selectDAM' => 'Bitte wähle ein DAM aus.',
	'smw_ti_firstselectTLM' => 'Wähle zuerst das TLM aus.',
	'smw_ti_selectImport' => 'Bitte wähle eines der verfügbaren Import Sets aus:&nbsp;&nbsp;',
	'smw_ti_inputpolicy' => 'Mit der input policy kann definiert werden, welche Informationen importiert werden sollen. Dabei können (<a href="http://www.opengroup.org/onlinepubs/007908799/xbd/re.html" target="_blank">reguläre Ausdrücke</a>) verwendet werden. 
							Beispiel: Benutze "^Em.*" um nur Terme zu importieren, die mit "Em" beginnen. 
							Alle daten werden importiert, wenn keine Import Policy angegeben wird.',
	'smw_ti_define_inputpolicy' => 'Bitte definiere eine Input Policy:',
	'smw_ti_mappingPage' => 'Bitte gib den Namen des Artikels an, der die Mapping Policy enthält:',
	'smw_ti_viewMappingPage' => 'Anzeigen',
	'smw_ti_editMappingPage' => 'Editieren',
	'smw_ti_conflictpolicy' => 'Bitte definiere eine Conflict Policy. Diese definiert, was passiert wenn ein Artikel importiert werden soll, der bereits existiert:',
	'smw_ti_nomappingpage' => 'Der angegebene Artikel, der die Mapping Policy enthalten soll, existiert nicht.',

	'smw_ti_succ_connected' => 'Erfolgreich mit "$1" verbunden.',
	'smw_ti_class_not_found' => 'Klasse "$1" nicht gefunden.',
	'smw_ti_no_tl_module_spec' => 'Die Spezifikation des TL-Moduls mit der ID "$1" konnte nicht gefunden werden.',
	'smw_ti_xml_error' => 'XML Fehler: $1 in Zeile $2',
	'smw_ti_filename'  => 'Dateiname:',
	'smw_ti_articlename'  => 'Artikel Name:',
	'smw_ti_articleerror' => 'Der Artikel "$1" existiert nicht.',
	'smw_ti_fileerror' => 'Die Datei "$1" existiert nicht oder ist leer.',
	'smw_ti_no_article_names' => 'In der angegebenen Datenquelle gibt es keine Artikelnamen.',
	'smw_ti_termimport' => 'Vokabular importieren',
	'termimport' => 'Vokabular importieren',
	'smw_ti_botstarted' => 'Der Bot zum Importieren eines Vokabulars wurde erfolgreich gestartet.',
	'smw_ti_botnotstarted' => 'Der Bot zum Importieren eines Vokabulars konnte nicht gestartet werden.',
	'smw_ti_couldnotwritesettings' => 'Die Einstellungen für den Vokabelimportbot konnten nicht gespeichert werden.',
	'smw_ti_missing_articlename' => 'Ein Artikel konnte nicht erzeugt werden, da der "articleName" in der Beschreibung des Begriffs fehlt.',
	'smw_ti_invalid_articlename' => 'Der Artikelname "$1" ist ungültig.',
	'smw_ti_articleNotUpdated' => 'Der existierende Artikel "$1" wurde nicht durch eine neue Version ersetzt.',
	'smw_ti_creationComment' => 'Dieser Artikel wurde vom Vokalbelimport-Framework erzeugt bzw. aktualisiert.',
	'smw_ti_creationFailed'  => 'Der Artikel "$1" konnte nicht erzeugt bzw. aktualisiert werden.',
	'smw_ti_missing_mp' => 'Die Mapping Policy fehlt.',
	'smw_ti_import_error' => 'Importfehler',
	'smw_ti_added_article' => '$1 wurde zum Wiki hinzugefügt.',
	'smw_ti_updated_article' => '$1 wurde aktualisiert.',
	'smw_ti_import_errors' => 'Einige Begriffe wurden nicht korrekt importiert. Bitte schauen Sie sich das Gardening Log an!',
	'smw_ti_import_successful' => 'Alle Begriffe wurden erfolgreich importiert.',

	'smw_gardissue_ti_class_added_article' => 'Importierte Artikel',
	'smw_gardissue_ti_class_updated_article' => 'Aktualisierte Artikel',
	'smw_gardissue_ti_class_system_error' => 'Importsystemfehler',
	'smw_gardissue_ti_class_update_skipped' => 'Übersprungene Aktualisierungen',

	/* Messages for the wiki web services */
	'smw_wws_articles_header' => 'Seiten, die den Web-Service "$1" benutzen',
	'smw_wws_properties_header' => 'Eigenschaften, die von "$1" gesetzt werden',
	'smw_wws_articlecount' => '<p>Zeige $1 Seiten, die diesen Web-Service benutzen.</p>',
	'smw_wws_propertyarticlecount' => '<p>Zeige $1 Eigenschaften, die ihren Wert von diesem Web-Service erhalten.</p>',
	'smw_wws_invalid_wwsd' => 'Die Wiki Web Service Definition ist ungÃ¼ltig oder existiert nicht.',
	'smw_wws_wwsd_element_missing' => 'Das Element "$1" fehlt in der Wiki Web Service Definition.',
	'smw_wws_wwsd_attribute_missing' => 'Das Attribut "$1" fehlt im Element "$2" der Wiki Web Service Definition.',
	'smw_wws_too_many_wwsd_elements' => 'Das Element "$1" erscheint mehrmals in der Wiki Web Service Definition.',
	'smw_wws_wwsd_needs_namespace' => 'Bitte beachten Sie: Wiki Web-Service Definitionen werden nur in Artikeln mit dem Namensraum "WebService" berÃ¼cksichtigt!',
	'smw_wws_wwsd_errors' => 'Die Wiki Web Service Definition ist fehlerhaft:',
	'smw_wws_invalid_protocol' => 'Das in der Wiki Web Service Definition benutzte Protokoll wird nicht unterstÃ¼tzt.',
	'smw_wws_invalid_operation' => 'Die Operation "$1" wird vom Web Service nicht unterstÃ¼tzt.',
	'smw_wws_parameter_without_name' => 'Ein Parameter der Wiki Web Service Definition hat keinen Namen.',
	'smw_wws_parameter_without_path' => 'Das Attribut "path" des Parameters "$1" fehlt.',
	'smw_wws_duplicate_parameter' => 'Der Parameter "$1" erscheint mehrmals.',
	'smw_wwsd_undefined_param' => 'Die Operation braucht den Parameter "$1". Bitte definieren Sie ein KÃ¼rzel.',
	'smw_wwsd_obsolete_param' => 'Die Operation benutzt den definierten Parameter "$1" nicht. Sie kÃ¶nnen ihn entfernen.',
	'smw_wwsd_overflow' => 'Die Struktur "$1" kann endlos fortgefÃ¼hrt werden. Parameter dieses Typs werden von der Wiki-Web-Service-Erweiterung nicht unterstÃ¼tzt.',
	'smw_wws_result_without_name' => 'Ein Resultat in der  Wiki Web Service Definition hat keinen Namen.',
	'smw_wws_result_part_without_name' => 'Das Resultat "$1" beinhaltet ein &lt;part&gt; ohne Namen.',
	'smw_wws_result_part_without_path' => 'Das Attribut "path" des &lt;part&gt;s "$1" des Resultats "$2" fehlt.',
	'smw_wws_duplicate_result_part' => 'Das &lt;part&gt; "$1" erscheint mehrmals im Resultat "$2".',
	'smw_wws_duplicate_result' => 'Das Resultat "$1" erscheint mehrmals.',
	'smw_wwsd_undefined_result' => 'Der Pfad des Resultats "$1" kann nicht im Resultat des Services gefunden werden.',
	'smw_wws_edit_in_gui' => 'Bitte hier klicken um die WWSD in der GUI zu editieren.',
	'smw_wwsd_array_index_missing' => 'Ein Array Index fehlt im Pfad: "$1"',
	'smw_wwsd_array_index_incorrect' => 'Ein Array Index ist fehlerhaft im Pfad: "$1"',
	'smw_wsuse_wrong_parameter' => 'Der Parameter "$1" existiert nicht in der Wiki Web Service Definition.',
	'smw_wsuse_parameter_missing' => 'Der Parameter "$1" ist nicht optional und kein Default wurde in der Wiki Web Service Definition definiert.',
	'smw_wsuse_wrong_resultpart' => 'Der Result Part "$1" existiert nicht in der Wiki Web Service Definition.',
	'smw_wsuse_wwsd_not_existing' => 'Eine Wiki Web Service Definition mit dem Namen "$1" existiert nicht.',
	'smw_wsuse_wwsd_error' => 'Die Benutzung des Web Services war fehlerhaft:',
	'smw_wsuse_getresult_error' => 'Ein Fehler ist beim Aufruf des Web Services aufgetreten.',
	'smw_wsuse_old_cacheentry' => ' Deshalb wurde ein veraltetes Ergebnis aus dem Cache anstatt eines neuen verwendet.',
	'smw_wsuse_prop_error' => 'Es ist nicht erlaubt, mehr als ein Result Part als Wert für ein semantisches Property zu verwenden',
	'smw_wsuse_type_mismatch' => 'Der Web Service hat nicht den erwarteten Typ für diesen Result Part zurückgeliefert. Bitte ändern Sie die zugehörige WWSD. Ein Variablen Dump wird dargestellt.',
	'webservicerepository' => 'Web Service Repository',
	'smw_wws_select_without_object' => 'Das Attribut "object" der Auswahl "$1" des Ergebnisses "$2" fehlt.',
	'smw_wws_select_without_value' => 'Das Attribut "value" der Auswahl "$1" des Ergebnisses "$2" fehlt.',
	'smw_wws_duplicate_select' => 'Die Auswahl "$1" kommt mehrmals im Ergebnis "$2" vor.',
	'smw_wws_need_confirmation' => 'Die WWSD für diesen Web Service muss von einem Administrator freigegeben werden, bevor sie erneut verwendet werden kann.',
	'definewebservice' => 'Definiere Web Service',
	'smw_wws_s1-help' => 'Bitte geben Sie die URI eines Web Services ein. Die URI muss auf eine WSDL (Web Service Description Language) verweisen.',
	'smw_wws_s2-help' => 'Jeder Web Service kann unterschiedliche Methoden bereitstellen, die unterschiedliche Ergebnisse liefern. Eine WWSD unterstützt nur eine Methode. Wenn mehrere Methoden eines Web Services verwendet werden sollen, dann muss für jede eine eigene WWSD bereitgestellt werden.',
	'smw_wws_s3-help' => 'Jetzt müssen die Parameter für den Web Service Aufruf definiert werden. Jeder Parameter wird durch einen eindeutigen Pfad innerhalb der Typ Hierarchie definiert. Die Typhierarchie kann in der dargestellten Baumstruktur durchstöbert werden. Für jeden Parameter der beim Web Service Aufruf verwendet werden soll muss ein Alias angegeben werden. Das Bleistift-Symbol kann dazu verwendet werden, um automatisch für jeden Parameter einen Alias zu erzeugen. Es kann auch angegeben werden, ob ein Parameter optional beim Web Service Aufruf ist. Zusätzlich kann ein Default Wert für einen Parameter angegeben werden. Dieser wird beim Web Service Aufruf verwendet, falls ein Parameter nicht optional ist und kein Wert für ihn angegeben wurde.',
	'smw_wws_s4-help' => 'Jetzt müssen die Result Parts definiert werden, die später in einem Artikel nach Verwendung des Web Services dargestellt werden sollen. Das funktioniert wie das Definieren von Parametern.',
	'smw_wws_s5-help' => 'Die Update Policy definiert nach welcher Zeitperiode das Ergebnis eines Web Service Aufrufs abgelaufen ist. Die Display Policy ist immer dann relevant, wenn das Ergebnis eines Web Service Aufrufs in einem Artikel dargestellt wird. Die Query Policy hingegen kommt nur bei Zuweisung von Web Service Ergebnissen an semantische Properties zum tragen. Der Delay Value (in Sekunden) wird zwischen zwei direkt aufeinanderfolgenden Web Service Aufrufen verwendet. Dadurch kann verhindert werden, dass ein Web Service zu oft in einem kurzen Zeitintervall aufgerufen wird. Der Span Of Life gibt an, wie lange das Ergebnis eines Web Service Aufrufs im Cache behalten werden soll. Wird kein Span Of Life angegeben, dann werden Web Service Ergebnisse unbegrenzt lange im Cache aufgehoben. Zuletzt kann noch angegeben werden, ob das Alter eines Cache-Eintrags auf Null zurueckgesetzt wird, wenn der Cache-Eintrag verwendet wird, oder wenn er aktualisiert wird. ',
	'smw_wws_s6-help' => 'Damit der Web Service verwendet werden kann muss er einen aussagekräftigen Namen erhalten.',
	'smw_wws_s1-menue' => '1. Definiere URI',
	'smw_wws_s2-menue' => '2. Wähle Methode',
	'smw_wws_s3-menue' => '3. Definiere Parameter',
	'smw_wws_s4-menue' => '4. Definiere Result Parts',
	'smw_wws_s5-menue' => '5. Definiere eine Update Policy',
	'smw_wws_s6-menue' => '6. Wähle einen Namen',
	'smw_wws_s1-intro' => '1. Definiere Zugangsdaten: ',
	'smw_wws_s1-uri' => 'Definiere URI: ',
	'smw_wws_s2-intro' => '2. Wähle Methode',
	'smw_wws_s2-method' => 'Wähle Methode',
	'smw_wws_s3-intro' => '3. Definiere Parameter.',
	'smw_wws_duplicate' => 'Einige Typ-Definitionen in dieser WSDL sind mehrdeutig. Diese werden rot hervorgehoben. Es wird empfohlen diese Typ-Definitionen später in der textuellen Repräsentation der WWSD zu bearbeiten!',
	'smw_wws_s4-intro' => '4. Wähle result parts',
	'smw_wws_s5-intro' => '5. Definiere eine update policy',
	'smw_wws_s6-intro' => '6. Speichere WWSD',
	'smw_wws_s6-name' => 'Wähle einen name',
	'smw_wws_s7-intro-pt1' => 'Der Web Service ',
	'smw_wws_s7-intro-pt2' => ' wurde erfolgreich erstellt. Um den Web Service in einem Artikel zu verwenden, muss die folgende Syntax angegeben werden:',
	'smw_wws_s7-intro-pt3' => 'Die erstellte WWSD wird von jetzt ab im Web Service Repository angezeigt.',
	'smw_wws_s1-error' => 'Es war nicht möglich eine Verbindung zu der angegebenen URI aufzubauen. Bitte ändern Sie diese oder versuchen es erneut.',
	'smw_wws_s2a-error' => 'Es war nicht möglich eine Verbindung zu der angegebenen URI aufzubauen. Bitte ändern Sie diese oder versuchen es erneut.',
	'smw_wws_s2b-error' => 'Die Parameter Definition dieser Methode ist rekursiv definiert. Bitte wählen Sie einen anderen Web Service oder eine andere Methode',
	'smw_wws_s3-error' => 'Es war nicht möglich eine Verbindung zu der angegebenen URI aufzubauen. Bitte ändern Sie diese oder versuche es erneut.',
	'smw_wws_s4-error' => 'Es war nicht möglich eine Verbindung zu der angegebenen URI aufzubauen. Bitte ändern Sie diese oder versuchen es erneut.',
	'smw_wws_s5-error' => 'Es war nicht möglich eine Verbindung zu der angegebenen URI aufzubauen. Bitte ändern Sie diese oder versuche es erneut.',
	'smw_wws_s6-error' => 'Bevor fortgefahren werden kann muss ein Name für den Web Service angegeben werden',
	'smw_wws_s6-error2' => 'Ein Artikel mit diesem Namen existiert bereits. Bitte wählen Sie einen anderen.',
	'smw_wws_s6-error3' => 'Ein Fehler ist beim Speichern der WWSD aufgetreten. Bitte versuchen Sie es erneut!',
	'smw_wscachebot' => 'Leere den Web Service Cache',
	'smw_ws_cachebothelp' => 'Dieser Bot entfernt veralltete Einträge aus dem Cache.',
	'smw_ws_cachbot_log' => 'Veraltete Cache Einträge für diesen Web Service wurden gelöscht.',
	'smw_wsupdatebot' => 'Aktualisiere den Web Service-Cache',
	'smw_ws_updatebothelp' => 'Dieser Bot aktualisiert den Web Service Cache.',
	'smw_ws_updatebot_log' => 'Cache Einträge für diesen Web Service wurden aktualisiert.',
	'smw_ws_updatebot_callerror' => 'Beim Updaten der Cache Einträge für diesen Web Service ist ein Fehler aufgetreten',
	'smw_ws_updatebot_confirmation' => 'Es war nicht möglich die Cache Einträge für diesen Web Service zu aktualisieren, da dieser zuvor aktiviert werden muss.'
);

protected $smwDINamespaces = array(
	SMW_NS_WEB_SERVICE       => 'WebService',
	SMW_NS_WEB_SERVICE_TALK  => 'WebService_talk'
);

protected $smwDINamespaceAliases = array(
	'WebService'       => SMW_NS_WEB_SERVICE,
	'WebService_talk'  => SMW_NS_WEB_SERVICE_TALK 
);

}


