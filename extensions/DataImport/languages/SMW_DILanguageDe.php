<?php
/*  Copyright 2007, ontoprise GmbH
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
*/
/**
 * @author Markus Krötzsch
 */

global $smwgDIIP;
include_once($smwgDIIP . '/languages/SMW_DILanguage.php');

class SMW_DILanguageDe extends SMW_HaloLanguage {

protected $smwUserMessages = array(
    /* Messages of the Thesaurus Import */
	'smw_ti_succ_connected' => 'Erfolgreich mit "$1" verbunden.',
	'smw_ti_class_not_found' => 'Klasse "$1" nicht gefunden.',
	'smw_ti_no_tl_module_spec' => 'Die Spezifikation des TL-Moduls mit der ID "$1" konnte nicht gefunden werden.',
	'smw_ti_xml_error' => 'XML Fehler: $1 in Zeile $2',
	'smw_ti_filename'  => 'Dateiname:',
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

	/* Messages for the wiki web services */
	'smw_wws_articles_header' => 'Seiten, die den Web-Service "$1" benutzen',
	'smw_wws_properties_header' => 'Eigenschaften, die von "$1" gesetzt werden',
	'smw_wws_articlecount' => '<p>Zeige $1 Seiten, die diesen Web-Service benutzen.</p>',
	'smw_wws_propertyarticlecount' => '<p>Zeige $1 Eigenschaften, die ihren Wert von diesem Web-Service erhalten.</p>',
	'smw_wws_invalid_wwsd' => 'Die Wiki Web Service Definition ist ungültig oder existiert nicht.',
	'smw_wws_wwsd_element_missing' => 'Das Element "$1" fehlt in der Wiki Web Service Definition.',
	'smw_wws_wwsd_attribute_missing' => 'Das Attribut "$1" fehlt im Element "$2" der Wiki Web Service Definition.',
	'smw_wws_too_many_wwsd_elements' => 'Das Element "$1" erscheint mehrmals Wiki Web Service Definition.',
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


