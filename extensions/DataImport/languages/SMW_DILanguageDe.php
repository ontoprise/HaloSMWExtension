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
 * @author Markus Krötzsch
 */

global $smwgDIIP;
include_once($smwgDIIP . '/languages/SMW_DILanguage.php');

class SMW_DILanguageDe extends SMW_DILanguage {

	protected $smwUserMessages = array(
    'specialpages-group-di_group' => 'Data Import',

	/* Messages of the Thesaurus Import */
	'smw_ti_selectDAM' => 'Bitte w&auml;hle ein DAM aus.',
	'smw_ti_firstselectTLM' => 'W&auml;hle zuerst das TLM aus.',
	'smw_ti_selectImport-heading' => 'Import Sets',
	'smw_ti_selectImport-label' => 'Auswählen:&nbsp&nbsp',
	'smw_ti_help' => 'Hilfe:&nbsp&nbsp',
	'smw_ti_selectImport-help' => 'Wenn Sie nur Terme aus einem der verfügbaren Import Sets auswählen möchten, dann können Sie dieses hier auswählen.',
	'smw_ti_inputpolicy-heading' => 'Input Policy',
	'smw_ti_inputpolicy-help' => 'Mit der Input Policy kann definiert werden, welche Informationen importiert werden sollen. Dabei k&ouml;nnen (<a href="http://www.opengroup.org/onlinepubs/007908799/xbd/re.html" target="_blank">regul&auml;re Ausdr&uuml;cke</a>) verwendet werden.<br/>Beispiel: Benutze "^Em.*" um nur Terme zu importieren, die mit "Em" beginnen.<br/>Alle daten werden importiert, wenn keine Import Policy angegeben wird.',
	'smw_ti_inputpolicy-label' => 'Filter hinzufügen:',
		'smw_ti_inputpolicy-defined' => 'Definierte Filter:&nbsp;&nbsp;',
	'smw_ti_mappingPage-heading' => 'Mapping Policy',
	'smw_ti_mappingPage-label' => 'Artikelnamen angeben:',
	'smw_ti_mappingPage-help' => 'Bitte geben Sie den Namen des Artikels an, der die Mapping Policy enth&auml;lt. Dieser Artikel stellt eine Art Template für die neu zu erzeugenden Artikel dar.',
	'smw_ti_viewMappingPage' => 'Anzeigen',
	'smw_ti_editMappingPage' => 'Editieren',
	'smw_ti_conflictpolicy-heading' => 'Conflict Policy',
	'smw_ti_conflictpolicy-label' => 'Auswählen:&nbsp;&nbsp;',
	'smw_ti_conflictpolicy-help' => 'Bitte definiere eine Conflict Policy. Diese definiert, was passiert wenn ein Artikel importiert werden soll, der bereits existiert:',
	'smw_ti_ti_name-heading' => 'Term Import Name',
	'smw_ti_ti_name-label' => 'Namen eingeben:&nbsp;&nbsp;',
	'smw_ti_ti_name-help' => 'Bitte geben Sie einen Namen für diesen Term Import an. Ein Artikel mit diesem Namen wird im Namensraum TermImport erstellt. Dieser wird alle Informationen, die Sie hier angeben enthalten.',
	'smw_ti_update_policy-heading' => 'Update Policy',
	'smw_ti_update_policy-help' => 'Wählen Sie die Update Policy "once" wenn die Ergebnisse dieses Term Imports nicht durch den Term Import Update bot aktualisiert werden sollen. Geben Sie ansonsten einen "max age" Wert in Minuten an.',
	'smw_ti_nomappingpage' => 'Der angegebene Artikel, der die Mapping Policy enthalten soll, existiert nicht.',
	'smw_ti_properties-heading' => 'Verfügbare Attribute',
	'smw_ti_properties-label' => 'Gewünschte Attribute auswählen:',
	'smw_ti_properties-help' => 'Wählen sie Attribute in der Tabelle unten aus, die bei diesem Term Import berücksichtigt werden sollen.',
	'smw_ti_articles-heading' => 'Zu importierende Artikel',
	'smw_ti_articles-label1' => 'Die folgenden ',
	'smw_ti_articles-label2' =>  ' Artikel werden importiert:',
	'smw_ti_articles-help' =>  'Die Tabelle unten zeigt, welche Artikel bei diesem Term Import importiert werden.',

	'smw_ti_succ_connected' => 'Erfolgreich mit "$1" verbunden.',
	'smw_ti_class_not_found' => 'Klasse "$1" nicht gefunden.',
	'smw_ti_no_tl_module_spec' => 'Die Spezifikation des TL-Moduls mit der ID "$1" konnte nicht gefunden werden.',
	'smw_ti_xml_error' => 'XML Fehler: $1 in Zeile $2',
	'smw_ti_filename'  => 'Dateiname:',
	'smw_ti_articlename'  => 'Artikel Name:',
	'smw_ti_articleerror' => 'Der Artikel "$1" existiert nicht.',
	'smw_ti_fileerror' => 'Die Datei "$1" existiert nicht oder ist leer.',
	'smw_ti_pop3error' => 'Es war nicht möglich, eine Verbindung zum Server aufzubauen.',
	'smw_ti_no_article_names' => 'In der angegebenen Datenquelle gibt es keine Artikelnamen.',
	'smw_ti_termimport' => 'Vokabular importieren',
	'termimport' => 'Vokabular importieren',
	'smw_ti_botstarted' => 'Der Bot zum Importieren eines Vokabulars wurde erfolgreich gestartet.',
	'smw_ti_botnotstarted' => 'Der Bot zum Importieren eines Vokabulars konnte nicht gestartet werden.',
	'smw_ti_couldnotwritesettings' => 'Die Einstellungen f&uuml;r den Vokabelimportbot konnten nicht gespeichert werden.',
	'smw_ti_missing_articlename' => 'Ein Artikel konnte nicht erzeugt werden, da der "articleName" in der Beschreibung des Begriffs fehlt.',
	'smw_ti_invalid_articlename' => 'Der Artikelname "$1" ist ung&uuml;ltig.',
	'smw_ti_articleNotUpdated' => 'Der existierende Artikel "$1" wurde nicht durch eine neue Version ersetzt.',
	'smw_ti_mappingpolicy_missing' => 'Die Mapping Policy "$1" existiert nicht.',
	'smw_ti_creationComment' => 'Dieser Artikel wurde vom Vokalbelimport-Framework erzeugt bzw. aktualisiert.',
	'smw_ti_creationFailed'  => 'Der Artikel "$1" konnte nicht erzeugt bzw. aktualisiert werden.',
	'smw_ti_missing_mp' => 'Die Mapping Policy fehlt.',
	'smw_ti_import_error' => 'Importfehler',
	'smw_ti_added_article' => '$1 wurde zum Wiki hinzugef&uuml;gt.',
	'smw_ti_updated_article' => '$1 wurde aktualisiert.',
	'smw_ti_import_errors' => 'Einige Begriffe wurden nicht korrekt importiert. Bitte schauen Sie sich das Gardening Log an!',
	'smw_ti_import_successful' => 'Alle Begriffe wurden erfolgreich importiert.',

	'smw_gardissue_ti_class_added_article' => 'Importierte Artikel',
	'smw_gardissue_ti_class_updated_article' => 'Aktualisierte Artikel',
	'smw_gardissue_ti_class_system_error' => 'Importsystemfehler',
	'smw_gardissue_ti_class_update_skipped' => '&Uuml;bersprungene Aktualisierungen',

	/* Messages for the wiki web services */
	'smw_wws_articles_header' => 'Seiten, die den Web-Service "$1" benutzen',
	'smw_wws_properties_header' => 'Eigenschaften, die von "$1" gesetzt werden',
	'smw_wws_articlecount' => '<p>Zeige $1 Seiten, die diesen Web-Service benutzen.</p>',
	'smw_wws_propertyarticlecount' => '<p>Zeige $1 Eigenschaften, die ihren Wert von diesem Web-Service erhalten.</p>',
	'smw_wws_invalid_wwsd' => 'Die Wiki Web Service Definition ist ungültig oder existiert nicht.',
	'smw_wws_wwsd_element_missing' => 'Das Element "$1" fehlt in der Wiki Web Service Definition.',
	'smw_wws_wwsd_attribute_missing' => 'Das Attribut "$1" fehlt im Element "$2" der Wiki Web Service Definition.',
	'smw_wws_too_many_wwsd_elements' => 'Das Element "$1" erscheint mehrmals in der Wiki Web Service Definition.',
	'smw_wws_wwsd_needs_namespace' => 'Bitte beachten Sie: Wiki Web-Service Definitionen werden nur in Artikeln mit dem Namensraum "WebService" berücksichtigt!',
	'smw_wws_wwsd_errors' => 'Die Wiki Web Service Definition ist fehlerhaft:',
	'smw_wws_invalid_protocol' => 'Das in der Wiki Web Service Definition benutzte Protokoll wird nicht unterstützt.',
	'smw_wws_invalid_operation' => 'Die Operation "$1" wird vom Web Service nicht unterstützt.',
	'smw_wws_parameter_without_name' => 'Ein Parameter der Wiki Web Service Definition hat keinen Namen.',
	'smw_wws_parameter_without_path' => 'Das Attribut "path" des Parameters "$1" fehlt.',
	'smw_wws_duplicate_parameter' => 'Der Parameter "$1" erscheint mehrmals.',
	'smw_wwsd_undefined_param' => 'Die Operation braucht den Parameter "$1". Bitte definieren Sie ein Kürzel.',
	'smw_wwsd_obsolete_param' => 'Die Operation benutzt den definierten Parameter "$1" nicht. Sie können ihn entfernen.',
	'smw_wwsd_overflow' => 'Die Struktur "$1" kann endlos fortgeführt werden. Parameter dieses Typs werden von der Wiki-Web-Service-Erweiterung nicht unterstützt.',
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
	'smw_wsuse_prop_error' => 'Es ist nicht erlaubt, mehr als ein Result Part als Wert f&uuml;r ein semantisches Property zu verwenden',
	'smw_wsuse_type_mismatch' => 'Der Web Service hat nicht den erwarteten Typ f&uuml;r diesen Result Part zur&uuml;ckgeliefert. Bitte &auml;ndern Sie die zugeh&ouml;rige WWSD. Ein Variablen Dump wird dargestellt.',
	'webservicerepository' => 'Data Import Repository',
	'smw_wws_ns_without_prefix' => 'Das Attribut "prefix" einer Namespace Definition fehlt.',
	'smw_wws_ns_without_uri' => 'Das Attribut "uri" einer Namespace Definition fehlt.',
	'smw_wws_duplicate_select' => 'Die Auswahl "$1" kommt mehrmals im Ergebnis "$2" vor.',
	'smw_wws_need_confirmation' => 'Die WWSD f&uuml;r diesen Web Service muss von einem Administrator freigegeben werden, bevor sie erneut verwendet werden kann.',
	'definewebservice' => 'Definiere Web Service',
	'smw_wws_s1-help' => '<h4>Hilfe</h4><br/>Diese GUI hilft Ihnen dabei eine Wiki Web Service Definition GUI (<b>WWSD</b>) zu definieren.Eine WWSD definiert, wie externe Web Services in Wiki Artikeln benutzt werden k&ouml;nnen. Als erstes m&uuml;ssen Sie w&auml;hlen, ob Sie einen <b>SOAP</b> oder einen <b>RESTful</b> Web Service verwenden wollen. Als n&auml;chstes m&uuml;ssen Sie die <b>URI</b> des Web Services angeben. Wenn Sie einen SOAP Web Service verwenden, dann muss die URI auf eine WSDL Beschreibung des Web Services zeigen. Optional k&ouml;nnen auch Zugangsdaten f&uuml;r den Web Service definiert werden. Bisher wird nur eine HTTP Basisautjentifizierung unterst&uuml;tzt.',
	'smw_wws_s2-help' => '<h4>Hilfe</h4><br/>Jeder Web Service kann unterschiedliche <b>Methoden</b> bereitstellen, die unterschiedliche Ergebnisse liefern. Eine WWSD unterst&uuml;tzt nur eine Methode. Wenn mehrere Methoden eines Web Services verwendet werden sollen, dann muss f&uuml;r jede eine eigene WWSD bereitgestellt werden.',
	'smw_wws_s3-help' => '<h4>Hilfe</h4><br/>Jetzt m&uuml;ssen die Parameter f&uuml;r den Web Service Aufruf definiert werden. Jeder Parameter wird durch einen <b>Pfad</b> eindeutig in der Parameter-Typ-Hierarchie identifiziert. Sie k&ouml;nnen die m&ouml;glichen Parameter des Web Services durchst&ouml;bern, indem Sie Teile der Parameter Typ Hierarchie in der Baumansicht oben ein- und ausklappen. Manchmal ist es nicht n&ouml;tig, dass beim Aufruf des Web Services alle Parameter verwendet werden. Sie m&uuml;ssen die Auswhlbox eines Parameters in der <b>Benutze</b>-Spalte aktivieren, wenn Benutzer sp&auml;ter beim Aufruf des Web Services einen Wert f&uuml;r den Parameter angeben k&ouml;nnen sollen. (Meistens m&uuml;ssen alle Parameter eines Web Services verwendet werden.) F&uuml;r jeden Parameter der verwendet werden soll muss ein <b>Alias</b> angegeben werden. Dieser wird sp&auml;ter anstelle des langen Pfades dazu verwendet, um den Parameter zu addressieren. Sie k&ouml;nnen ebenfalls angeben, ob ein Parameter <b>optional</b> ist, wenn Benutzer sp&auml;ter den Web Service verwenden. Wenn ein Parameter nicht optional ist und Benutzer sp&auml;ter keinen Wert f&uuml;r ihn angeben, dann erhalten sie eine Fehlermeldung. Als letztes k&ouml;nnen Sie noch einen <b>Standardwert</b> f&uuml;r einen Parameter angeben. Dieser wird verwendet, wenn ein Parameter nicht optional ist und Anwender keinen Wert f&uuml;r ihn angeben. <p><b>Subparameter</b> sind Kindknoten von Parametern, sie k&ouml;nnen bei Parametern mit komplexen Inhalten hilfreich sein. Im Normalfall werden sie jedoch nicht ben&ouml;tigt. Besuchen Sie die <a href="http://smwforum.ontoprise.com/smwforum/index.php/Help:Defining_a_web_service_using_the_WWSD_syntax#Subparameters_to_reduce_complexity_of_web_services">Online-Hilfe</a> f&uuml;r weitere Informationen.</p>',
	'smw_wws_s4-help' => '<h4>Hilfe</h4><br/>Jetzt m&uuml;ssen Sie definieren, welche Teile des Ergebnisses (<b>Result Parts</b>), das vom Web Service zur&uuml;ckgeliefert wird, Benutzer sp&auml;ter in Wiki Artikeln anzeigen k&ouml;nnen. Jeder Result Part wird durch einen <b>Pfad</b> identifiziert, der ihn in der Ergebnis Typ Hierarchie eindeutig addressiert. Sie k&ouml;nnen die m&ouml;glichen Result Parts durchst&ouml;bern, indem Sie Teile der Ergebnis Typ Hierarchie in der Baumansicht oben ein- und ausklappen. Sie m&uuml;ssen die Auswahlbox in der <b>Benutze</b>-Spalte bei allen Result Parts aktivieren, die sp&auml;ter von Benutzern angezeigt werden k&ouml;nnen sollen. F&uuml;r jeden verwendeten Result Part muss ein <b>Alias</b> angegeben werden. Manchmal beinhalten Result Parts strukturierte Information, die in einem Format wie JSON oder XPath kodiert sind. Oftmals macht es keinen Sinn diese kodierten Informationen in Wiki Artikeln darzustellen. Daher k&ouml;nnen Sie <b>Subpfade</b> zu Result Parts hinzuf&uuml;gen. Diese helfen Ihnen dabei, relevante Informationen aus dem Result Part zu extrahieren. Ein Subpfad kann das <b>JSON</b> oder das <b>XPath</b> format haben. (Die aktuelle Version der Data Import Extension unterst&uuml;tzt nur XPath). In der <b>Pfad</b>-Spalte m&uuml;ssen Sie den Pfad angeben, der verwendet werden soll, um die Informationen zu extrahieren. Sie m&uuml;ssen auch f&uuml;r jeden Subpfad einen Alias angeben. Wenn Sie in der GUI eine existierende WWSD editieren, dann kann es sein, dass einige Result Parts, die Sie manuell definiert haben, nicht in der Baumansicht dargestellt werden k&ouml;nnen. Diese werden dann unterhalb der <b>grauen Linie</b> separat dargestellt. Der Grund daf&uuml;r ist, dass nicht alle XPath-Ausdr&uuml;cke in einer Baumansicht dargestellt werden k&ouml;nnen.',
	'smw_wws_s5-help' => '<h4>Hilfe</h4><br/>Die <b>Update Policy</b> definiert nach welcher Zeitperiode das Ergebnis eines Web Service Aufrufs beraltet ist und aktualisiert werden muss. Die <b>Display Policy</b> ist immer dann relevant, wenn das Ergebnis eines Web Service Aufrufs in einem Artikel dargestellt wird. Die <b>Query Policy</b> hingegen kommt nur bei Zuweisung von Web Service Ergebnissen an semantische Properties zum Tragen. Der <b>Delay Value</b> (in Sekunden) wird zwischen zwei direkt aufeinanderfolgenden Web Service Aufrufen verwendet. Dadurch kann verhindert werden, dass ein Web Service zu oft in einem kurzen Zeitintervall aufgerufen wird. Der <b>Span Of Life</b> gibt an, wie lange das Ergebnis eines Web Service Aufrufs im Cache behalten werden soll. Wird kein Span Of Life angegeben, dann werden Web Service Ergebnisse unbegrenzt lange im Cache aufgehoben. Zuletzt kann noch angegeben werden, ob das Alter eines Cache-Eintrags auf Null zurueckgesetzt wird, wenn der Cache-Eintrag verwendet wird , oder wenn er aktualisiert wird (<b>expires after update</b>).',
	'smw_wws_s6-help' => '<h4>Hilfe</h4><br/>Um den We Service benutzen zu k&ouml;nnen, m&uuml;ssen Sie ihm jetzt noch einen <b>Namen</b> geben.',
	'smw_wws_s1-menue' => '1. Definiere Zugangsdaten',
	'smw_wws_s2-menue' => '2. W&auml;hle Methode',
	'smw_wws_s3-menue' => '3. Definiere Parameter',
	'smw_wws_s4-menue' => '4. W&auml;hle Result Parts',
	'smw_wws_s5-menue' => '5. Definiere Update Policy',
	'smw_wws_s6-menue' => '6. Speichere WWSD',
	'smw_wws_s1-intro' => '1. Definiere Zugangsdaten: ',
	'smw_wws_s1-uri' => 'Definiere URI: ',
	'smw_wws_s2-intro' => '2. W&auml;hle Methode',
	'smw_wws_s2-method' => 'W&auml;hle Methode',
	'smw_wws_s3-intro' => '3. Definiere Parameter',
	'smw_wws_duplicate' => '<table><tr><td style="vertical-align: top"><b>Note:</b><td><td><b>Einige Typ-Definitionen</b> in dieser WSDL sind <b>mehrdeutig</b>.<br/> Diese werden <b style="color: darkred">dunkelrot</b> hervorgehoben.<br/> Es wird empfohlen diese Typ-Definitionen sp&auml;ter in der <b>textuellen Repr&auml;sentation</b> der WWSD zu <b>bearbeiten</b>.</td></tr></table><br/>',
	'smw_wws_s4-intro' => '4. W&auml;hle Result Parts',
	'smw_wws_s5-intro' => '5. Definiere Update Policy',
	'smw_wws_s6-intro' => '6. Speichere WWSD',
	'smw_wws_s6-name' => 'W&auml;hle einen Namen',
	'smw_wws_s7-intro-pt1' => 'Der Web Service ',
	'smw_wws_s7-intro-pt2' => ' wurde erfolgreich erstellt. Um den Web Service in einem Artikel zu verwenden, muss die folgende Syntax angegeben werden:',
	'smw_wws_s7-intro-pt3' => 'Die erstellte WWSD wird von jetzt ab im Web Service Repository angezeigt.',
	'smw_wws_s1-error' => 'Es war nicht m&ouml;glich eine Verbindung zu der angegebenen URI aufzubauen. Bitte &auml;ndern Sie diese oder versuchen es erneut.',
	'smw_wws_s2a-error' => 'Es war nicht m&ouml;glich eine Verbindung zu der angegebenen URI aufzubauen. Bitte &auml;ndern Sie diese oder versuchen es erneut.',
	'smw_wws_s2b-error' => 'Die Parameter Definition dieser Methode ist rekursiv definiert. Bitte w&auml;hlen Sie einen anderen Web Service oder eine andere Methode',
	'smw_wws_s3-error' => 'Es war nicht m&ouml;glich eine Verbindung zu der angegebenen URI aufzubauen. Bitte &auml;ndern Sie diese oder versuche es erneut.',
	'smw_wws_s4-error' => 'Es war nicht m&ouml;glich eine Verbindung zu der angegebenen URI aufzubauen. Bitte &auml;ndern Sie diese oder versuchen es erneut.',
	'smw_wws_s5-error' => 'Es war nicht m&ouml;glich eine Verbindung zu der angegebenen URI aufzubauen. Bitte &auml;ndern Sie diese oder versuche es erneut.',
	'smw_wws_s6-error' => 'Bevor fortgefahren werden kann muss ein Name f&uuml;r den Web Service angegeben werden',
	'smw_wws_s6-error2' => 'Ein Artikel mit diesem Namen existiert bereits. Bitte w&auml;hlen Sie einen anderen.',
	'smw_wws_s6-error3' => 'Ein Fehler ist beim Speichern der WWSD aufgetreten. Bitte versuchen Sie es erneut!',
	'smw_wscachebot' => 'Leere den Web Service Cache',
	'smw_ws_cachebothelp' => 'Dieser Bot entfernt veralltete Eintr&auml;ge aus dem Cache.',
	'smw_ws_cachbot_log' => 'Veraltete Cache Eintr&auml;ge f&uuml;r diesen Web Service wurden gel&ouml;scht.',
	'smw_wsupdatebot' => 'Aktualisiere den Web Service-Cache',
	'smw_ws_updatebothelp' => 'Dieser Bot aktualisiert den Web Service Cache.',
	'smw_ws_updatebot_log' => 'Cache Eintr&auml;ge f&uuml;r diesen Web Service wurden aktualisiert.',
	'smw_ws_updatebot_callerror' => 'Beim Updaten der Cache Eintr&auml;ge f&uuml;r diesen Web Service ist ein Fehler aufgetreten',
	'smw_ws_updatebot_confirmation' => 'Es war nicht m&ouml;glich die Cache Eintr&auml;ge f&uuml;r diesen Web Service zu aktualisieren, da dieser zuvor aktiviert werden muss.',
	'smw_wsgui_nextbutton' => 'Weiter',
	'smw_wsgui_savebutton' => 'Speichern',

	'usewebservice' => 'Benutze Web Service',

'smw_wws_client_connect_failure' => 'Es war nicht m&ouml;glich, eine Verbindung herzustellen zu: ',
'smw_wws_client_connect_failure_display_cache' => 'Das letzte gecachte Web Service Ergebnis wird dargestellt.',
	'smw_wws_client_connect_failure_display_default' => 'Default Werte werden anstatt eines Web Service Ergebnisses angezeigt.',	

'smw_wws_s2-REST-help' => '<h4>Hilfe</h4><br/>Jetzt m&uuml;ssen Sie angeben, ob Sie die HTTP-<b>get</b> oder die HTTP-<b>post</b> Methode f&uuml;r den Web Service Aufruf verwenden m&ouml;chten. (In den meisten F&auml;llen k&ouml;nnen Sie sich f&uuml;r HTTP-get entscheiden.) ',
	'smw_wws_s3-REST-help' => '<h4>Hilfe</h4><br/>Jetzt m&uuml;ssen Sie die Parameter definieren, die f&uuml;r den Web Service Aufruf verwendet werden solen. Als erstes m&uuml;ssen Sie auf den <b>F&uuml;ge Parameter hinzu</b> Button klicken, so dass die Tabelle zum definieren von Parametern angezeigt wird. Jetzt m&uuml;ssen Sie den <b>Pfad</b> des Parameters angeben. Wenn Sie keinen Pfad f&uuml;r einen Parameter angeben, dann wird der Parameter nicht in die WWSD mitaufgenommen. Zus&auml;tzlich k&ouml;nnen Sie noch einen <b>Alias</b> f&uuml;r den Parameter angeben. Wenn Sie keinen angeben, dann wird der Pfad als Alias verwendet. Sie k&ouml;nnen auch angeben, ob ein Parameter <b>optional</b> ist, wenn Benutzer sp&auml;ter den Web Service aufrufen. Wenn ein Parameter nicht optional ist, dann erhalten Benutzer sp&auml;ter eine Fehlermeldung, falls Sie keinen Wert f&uuml;r den Parameter &uuml;bergeben. Als letztes k&ouml;nnen Sie noch einen <b>Standartwert</b> angeben. Dieser wird verwendet, falls der Parameter nicht optional ist und Anwender keinen Wert f&uuml;r den Parameter beim Web Service aufruf &uuml;bergeben.</p><p><b>Subparameter</b> sind Kindknoten von Parametern, sie k&ouml;nnen bei Parametern mit komplexen Inhalten hilfreich sein. Im Normalfall werden sie jedoch nicht ben&ouml;tigt. Besuchen Sie die <a href="http://smwforum.ontoprise.com/smwforum/index.php/Help:Defining_a_web_service_using_the_WWSD_syntax#Subparameters_to_reduce_complexity_of_web_services">Online-Hilfe</a> f&uuml;r weitere Informationen.</p>',
	'smw_wws_s4-REST-help' => '<h4>Hilfe</h4><br/>Jetzt m&uuml;ssen Sie definieren, welche Teile des Web Service Ergebnisses (<b>Result Parts</b> Benutzer sp&auml;ter in Wiki Artikeln darstellen k&ouml;nnen. RESTful Web Services geben einen String als Ergebnis zur&uuml;ck. Dieser String kann ein einfacher Text sein oder er kann strukturierte Informationen, die in einem Format wie etwa JSON oder XML kodiert sind, enthalten. Sie k&ouml;nnen das <b>komplette</b> Anfrageergebnis als Result Part definieren, indem Sie die entsprechende Auswahlbox aktivieren. Wenn Sie weitere Result Parts definieren m&ouml;chten, dann m&uuml;ssen Sie auf den <b>F&uuml;ge Result Parts hinzu</b> Button klicken. Jetzt m&uuml;ssen Sie f&uuml;r den neuen Result Part einen <b>Alias</b> vergeben. Dieser Alias wird sp&auml;ter dazu verwendet, um den Result Part zu addressieren, wenn der Web Service in einem Artikel verwendet wird. Wenn Sie keinen Alias angeben, dann wird automatisch einer generiert. Jetzt m&uuml;ssen Sie angeben, ob der Pfad zum extrahieren des Result Parts das <b>JSON</b> oder das <b>XPath</b> format besitzt. (Bisher wird nur das XPath Format unterst&uuml;tzt.) Jetzt m&uuml;ssen Sie noch den <b>Pfad</b> selbst angeben.',
	'smw_wws_help-button-tooltip' => 'Hilfe ein- oder ausblenden.',
	'smw_wws_selectall-tooltip' => 'Alle aktivieren oder deaktivieren.',
	'smw_wws_autogenerate-alias-tooltip-parameter' => 'Aliase automatisch generieren. Das geht nur, wenn bereits Parameter für die Verwendung ausgewählt wurden.',
	'smw_wws_autogenerate-alias-tooltip-resultpart' => 'Aliase automatisch generieren. Das geht nur, wenn bereits Result Parts oder Subpfade ausgewählt wurden, für die Aliase erzeugt werden können.',

	'smw_wsuse_s1-help' => '<h4>Hilfe</h4><br/>Diese Spezialseite erlaubt Ihnen die #ws-Syntax f&uuml;r das Aufrufen eines Web Services aus einem Wiki Artikel heraus, zu erstellen. Als erstes m&uuml;ssen Sie einen der <b>verf&uuml;gbaren Web Services</b> im Drop-down Menue oben ausw&auml;hlen.',
	'smw_wsuse_s2-help' => '<h4>Hilfe</h4><br/>Jetzt m&uuml;ssen Sie w&auml;hlen, welche der Parameter, die in der Tabelle oben angezeigt werden, Sie fr den Web Service Aufruf verwenden m&ouml;chten. Wenn Sie einen Parameter verwenden m&ouml;chten, dann m&uuml;ssen Sie die Auswahlbox in der <b>Benutze</b>-Spalte aktivieren. Parameter, die nicht optional sind, sind vorausgew&auml;hlt. Wenn Sie einen Parameter verwenden m&ouml;chten, dann m&uuml;ssen Sie einen <b>Wert</b> f&uuml;r den Parameter angeben. Manche Parameter stellen einen <b>Standardwert</b> bereit. Wenn Sie anstatt eines eigenen Wertes den Standardwert verwenden m&ouml;chten, dann m&uuml;ssen Sie die zugeh&ouml;rige Auswahlbox aktivieren.',
	'smw_wsuse_s3-help' => '<h4>Hilfe</h4><br/>Jetzt m&uuml;ssen Sie w&auml;hlen, welche Teile des Web Service Ergebnisses (<b>Result Parts</b>) Sie in Ihrem Artikel anzeigen m&ouml;chten. Bitte aktivieren Sie die Auswahlbox in der <b>Benutze</b>-Spalte jedes Result Parts, den Sie verwenden m&ouml;chten.',
	'smw_wsuse_s4-help' => '<h4>Hilfe</h4><br/>Hier k&ouml;nnen Sie ausw&auml;hlen, welches <b>Format</b> Sie zur Darstellung des Anfrageergebnisses verwenden m&ouml;chten. Einige Formate erlauben die Verwendung von <b>Templates</b>, die dazu verwendet werden, jede Zeile Ihres Anfrageergebnisses darzustellen.',
	'smw_wsuse_s5-help' => '<h4>Hilfe</h4><br/>Im letzten Schritt haben Sie drei M&ouml;glichkeiten. Sie k&ouml;nnen sich eine <b>Vorschau</b> auf das Web Service Ergebnis anzeigen lassen. Sie k&ouml;nnen sich die erzeugte <b>#ws-Syntax</b>, die zum Aufrufen des Web Services verwendet wird, anzeigen lassen. Wenn Sie zu dieser GUI aus der Semantischen Toolbar heraus navigiert sind, dann k&ouml;nnen Sie den Web Service Aufruf direkt zu dem eben editierten Wiki Artikel <b>hinzuf&uuml;gen</b>.',
	
	'smw_wws_spec_protocol' => 'W&auml;hle Protokoll:',
	'smw_wws_spec_auth' => 'Authentifizierung n&ouml;tig? ',
	'smw_wws_yes' => 'Ja',
	'smw_wws_no' => 'Nein',
	'smw_wws_username' => 'Benutzername: ',
	'smw_wws_password' => 'Passwort: ',
	'smw_wws_error_headline' => 'Fehler',
	'smw_wws_path' => 'Pfad:',
	'smw_wws_alias' => 'Alias: ',
	'smw_wws_optional' => 'Optional:',
	'smw_wws_defaultvalue' => 'Standardwert:',
	'smw_wws_use' => 'Benutze: ',
	'smw_wws_format' => 'Format: ',
	'smw_wws_days' => ' Tage ',
	'smw_wws_hours' => ' Stunden ',
	'smw_wws_minutes' => ' Minuten ',
	'smw_wws_inseconds' => 'in Sekunden',
	'smw_wws_indays' => 'in Tagen',
	'smw_wws_yourws' => 'Ihr Web Service <b>',
	'smw_wws_succ_created' => '</b> wurde erfolgreich erstellt.<br/><br/> Jetzt können Sie den Web Service in einem Artikel verwenden. Das ist möglich, indem Sie entweder manuell die #ws Parser Funktion oder die GUI zur Verwendung eines Web Services verwenden. Die GUI ist im Editier Modus über die Semantische Toolbar erreichbar.<br/><br/>Ihr Web Service wird von jetzt an in der ',
	'smw_wws_succ_created-3' => ' Liste der verf&uuml;gbaren Web Services', 
	'smw_wws_succ_created-4' => ' verf&uuml;gbar sein.<br/><br/> Sie k&ouml;nnen jetzt fortfahren und eine neue WWSD f&uuml;r einen anderen Web Service erstellen.',
	'smw_wws_new' => 'Neuen Web Service erstellen',
	
	'smw_wwsr_intro' => 'Verf&uuml;gbare Wiki Web Service Definitionen',
	'smw_tir_intro' => 'Verfügbare Term Import Definitionen',
	'smw_wwsr_name' => 'Name',
	'smw_wwsr_lastupdate' => 'Letzte Aktualisierung',
	'smw_wwsr_update_manual' => 'Manuell aktualisieren',
	'smw_wwsr_update' => 'Aktualisieren',
	'smw_wwsr_rep_edit' => 'Editieren',
	'smw_wwsr_confirm' => 'Best&auml;tigen',
	'smw_wwsr_rep_intro' => 'Das Web Service Repository listet alle korrekt im Wiki definierten Wiki Web Service Definitionen (WWSD) auf. Sie können diese hier best&auml;tigen, in der graphischen Benutzeroberfläche editieren und manuell die Ergebnisse eines Web Services aktualisieren. (Zum aktualisieren und bestätigen einer WWSD benötigen Sie administrative Benutzerrechte.)',
	'smw_tir_rep_intro' => 'Das Term Import Repository listet alle korrekt im Wiki definierten Term Import Definitionen auf. Sie können Term Imports hier in der graphischen Benutzeroberfläche editieren oder manuell die Ergebnisse eines Term Imports aktualisieren. (Zum aktualisieren und editieren eines Term Imports benötigen Sie administrative Benutzerrechte.)',
	'smw_wwsr_noconfirm' => 'Wenn Sie hier keine Buttons zum Aktualisieren und Best&auml;tigen von Web Services sehen, dann sind Sie nicht eingeloggt oder Sie haben nicht die erforderlichen Berechtigungen.',
	'smw_wwsr_confirmed' => 'Best&auml;tigt',
	'smw_wwsr_updating' => 'Wird aktualisiert',
	
	'smw_wwsu_menue-s1' => '1. W&auml;hle Web Service',
	'smw_wwsu_menue-s2' => '2. Definiere Parameter',
	'smw_wwsu_menue-s3' => '3. W&auml;hle Result Parts',
	'smw_wwsu_menue-s4' => '4. Wähle Format',
	'smw_wwsu_menue-s5' => '5. Ergebnis',
	
	'smw_wwsu_availablews' => 'Verf&uuml;gbare Web Services: ',
	'smw_wwsu_noparameters' => 'Dieser Web Service ben&ouml;tigt keine Parameter.',
	'smw_wwsu_alias' => 'Alias:',
	'smw_wwsu_use' => 'Benutze: ',
	'smw_wwsu_value' => 'Wert:',
	'smw_wwsu_defaultvalue' => 'Benutze Standardwert:',
	'smw_wwsu_availableformats' => 'Verf&uuml;gbare Formate: ',
	'smw_wwsu_displaypreview' => 'Zeige Vorschau',
	'smw_wwsu_displaywssyntax' => 'Zeige #ws-syntax',
	'smw_wwsu_addcall' => 'F&uuml;ge Aufruf zu <articlename> hinzu',
	'smw_wwsu_noresults' => 'Dieser Web Service stellt keine Result Parts zur Verf&uuml;gung',
	'smw_wwsu_copytoclipboard' => 'In Zwischenablage kopieren',
	
	'smw_wwsm_update_msg' => 'Die Quelle der letzten Materialisierung wurde geändert.',
	
	'smw_termimportbot' => 'Begriffe aus einem Vokabular importieren',
	'smw_gard_termimportbothelp' => 'Startet den Bot zum Importieren der Begriffe eines Vokabulars.',
	'smw_termimportupdatebot' => 'Update Definierte Term Imports aktualisieren.',
	'smw_gard_termimportupdatebothelp' => 'Startet den Bot zum aktualisieren von definierten Term Imports',
	'smw_ti_def_allready_exists' => 'Eine Term Import Definition mit diesem Namen existiert bereits.',
	'smw_ti_def_not_creatable' => 'Es war nicht möglich eine Term Import Definition mit diesem Namen zu erstellen.',
	'smw_ti_update_not_necessary' => 'Eine Aktualisierung dieses Term Imports war nicht nötig.',
	'smw_ti_updated_successfully' => 'Dieser Term Import wurde erfolgreich aktualisiert.',
	'smw_ti_update_failure' => 'Beim Aktualisieren dieses Term Imports ist ein Fehler aufgetreten.',
	'smw_gardissue_ti_class_ignored' => 'Ignorierte Term Imports',
	'smw_gardissue_ti_class_success' => 'Aktualisierte Term Imports',
	'smw_gardissue_ti_class_failure' => 'Fehlerhafte Term Imports',
	
	'smw_ti_menuestep1' => '1. Modulspezifische Daten angeben',
	'smw_ti_menuestep2' => '2. Term Import konfigurieren und ausführen',
	'smw_ti_tl-heading' => 'Transport Layer Modul wählen:',
	'smw_ti_dam-heading' => 'Data Acccess Modul wählen:',
	'smw_ti_module-data-heading' => 'Modulspezifische Daten angeben:',
	
	'smw_wwsr_update_tooltip' => 'Update Bot für diese WWSD starten.',
	'smw_wwsr_rep_edit_tooltip' => 'Diese WWSD in der GUI editieren.',
	'smw_wwsr_confirm_tooltip' => 'Die Benutzung dieser WWSD erlauben.',
	'smw_wwsr_update_tooltip_ti' => 'Update Bot für diesen Term Import starten.',
	'smw_wwsr_rep_edit_tooltip_ti' => 'Diesen Term Import in der GUI editieren.',
	);

	protected $smwDINamespaces = array(
		SMW_NS_WEB_SERVICE       => 'WebService',
		SMW_NS_WEB_SERVICE_TALK  => 'WebService_talk',
		SMW_NS_TERM_IMPORT => 'TermImport',
		SMW_NS_TERM_IMPORT_TALK => 'TermImport_talk',
	);

	protected $smwDINamespaceAliases = array(
		'WebService'       => SMW_NS_WEB_SERVICE,
		'WebService_talk'  => SMW_NS_WEB_SERVICE_TALK,
	'TermImport'       => SMW_NS_TERM_IMPORT,
		'TermImport_talk'  => SMW_NS_TERM_IMPORT_TALK 
	);

}


