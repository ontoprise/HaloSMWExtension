<?php
/**
 * @author Markus Krötzsch
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageDe extends SMW_HaloLanguage {

protected $smwContentMessages = array(
	'smw_edithelp' => 'Bearbeitungshilfe für Relationen und Attribute',
	'smw_helppage' => 'Relationen und Attribute',
	'smw_viewasrdf' => 'RDF-Feed',
	'smw_viewinOB' => 'Im Ontology-Browser öffnen',
	'smw_finallistconjunct' => ' und', //used in "A, B, and C"
	'smw_factbox_head' => 'Fakten zu $1',
	'smw_att_head' => 'Attribute',
	'smw_rel_head' => 'Relationen zu anderen Seiten',
	'smw_spec_head' => 'Spezielle Eigenschaften',
	'smw_predefined_props' => 'Das ist das vordefinierte Property "$1"',
	'smw_predefined_cats' => 'Das ist die vordefinierte Kategorie "$1"',
	/*URIs that should not be used in objects in cases where users can provide URIs */
	'smw_uri_blacklist' => " http://www.w3.org/1999/02/22-rdf-syntax-ns#\n http://www.w3.org/2000/01/rdf-schema#\n http://www.w3.org/2002/07/owl#",
	'smw_baduri' => 'URIs aus dem Bereich „$1“ sind an dieser Stelle leider nicht verfügbar.',
	/*Messages and strings for inline queries*/
	'smw_iq_disabled' => "<span class='smwwarning'>Anfragen innerhalb einzelner Seiten sind in diesem Wiki leider nicht erlaubt.</span>",
	'smw_iq_moreresults' => '&hellip; weitere Ergebnisse',
	'smw_iq_nojs' => 'Der Inhalt dieses Elementes kann mit einem Browser mit JavaScript-Unterstützung oder als
	<a href="$1">Liste einzelner Suchergebnisse</a> betrachtet werden.',
	/*Messages and strings for ontology resued (import) */
	'smw_unknown_importns' => 'Für den Namensraum „$1“ sind leider keine Importfunktionen verfügbar.',
	'smw_nonright_importtype' => 'Das Element „$1“ kann nur für Seiten im Namensraum „$2“ verwendet werden.',
	'smw_wrong_importtype' => 'Das Element „$1“ kann nicht für Seiten im Namensraum „$2“ verwendet werden.',
	'smw_no_importelement' => 'Das Element „$1“ steht leider nicht zum Importieren zur Verfügung.',
	/*Messages and strings for basic datatype processing*/
	'smw_decseparator' => ',',
	'smw_kiloseparator' => '.',
	'smw_unknowntype' => 'Dem Attribut wurde der unbekannte Datentyp „$1“ zugewiesen.',
	'smw_noattribspecial' => 'Die spezielle Eigenschaft „$1“ ist kein Attribut (bitte „::“ anstelle von „:=“ verwenden).',
	'smw_notype' => 'Dem Attribut wurde kein Datentyp zugewiesen.',
	'smw_manytypes' => 'Dem Attribut wurden mehrere Datentypen zugewiesen.',
	'smw_emptystring' => 'Leere Zeichenfolgen werden nicht akzeptiert.',
	'smw_maxstring' => 'Die Zeichenkette „$1“ ist für diese Website zu lang.',
	'smw_nopossiblevalues' => 'Für dieses Attribut wurden keine möglichen Werte angegeben.',
	'smw_notinenum' => '„$1“ gehört nicht zu den möglichen Werten dieses Attributs ($2).',
	'smw_noboolean' => '„$1“ ist kein Boolescher Wert (wahr/falsch).',
	'smw_true_words' => 'wahr,ja',	// comma-separated synonyms for boolean TRUE besides 'true' and '1'
	'smw_false_words' => 'falsch,nein',	// comma-separated synonyms for boolean FALSE besides 'false' and '0'
	'smw_nointeger' => '„$1“ ist keine ganze Zahl.',
	'smw_nofloat' => '„$1“ ist keine Dezimalzahl.',
	'smw_infinite' => 'Die Zahl $1 ist zu lang.',
	'smw_infinite_unit' => 'Die Umrechnung in Einheit $1 ist nicht möglich: die Zahl ist zu lang.',
	// Currently unused, floats silently store units.  'smw_unexpectedunit' => 'dieses Attribut unterstützt keine Umrechnung von Einheiten',
	'smw_unsupportedprefix' => 'Vorangestellte Zeichen bei Dezimalzahlen („$1“) werden nicht unterstützt.',
	'smw_unsupportedunit' => 'Umrechnung der Einheit „$1“ nicht unterstützt.',
	/*Messages for geo coordinates parsing*/
	'smw_err_latitude' => 'Angaben zur Geographischen Breite (N, S) müssen zwischen 0 und 90 liegen. „$1“ liegt nicht in diesem Bereich!',
	'smw_err_longitude' => 'Angaben zur Geographischen Länge (O, W) müssen zwischen 0 und 180 liegen. „$1“ liegt nicht in diesem Bereich!',
	'smw_err_noDirection' => 'Irgendwas stimmt nicht mit dem angegebenen Wert „$1“. Ein Beispiel für eine gültige Angabe ist „1°2′3.4′′ W“.',
	'smw_err_parsingLatLong' => 'Irgendwas stimmt nicht mit dem angegebenen Wert „$1“. Ein Beispiel für eine gültige Angabe ist „1°2′3.4′′ W“.',
	'smw_err_wrongSyntax' => 'Irgendwas stimmt nicht mit dem angegebenen Wert „$1“. Ein Beispiel für eine gültige Angabe ist „1°2′3.4′′ W, 5°6′7.8′′ N“.',
	'smw_err_sepSyntax' => 'Die Werte für geographische Breite und Länge sollten durch Komma oder Semikolon getrennt werden.',
	'smw_err_notBothGiven' => 'Es muss ein Wert für die geographische Breite (N, S) <i>und</i> die geographische Länge (O, W) angegeben werden.',
	/* additionals ... */
	'smw_label_latitude' => 'Geographische Breite:',
	'smw_label_longitude' => 'Geographische Länge:',
	'smw_abb_north' => 'N',
	'smw_abb_east' => 'O',
	'smw_abb_south' => 'S',
	'smw_abb_west' => 'W',
	/* some links for online maps; can be translated to different language versions of services, but need not*/
	'smw_service_online_maps' => " Landkarten|http://tools.wikimedia.de/~magnus/geo/geohack.php?language=de&params=\$9_\$7_\$10_\$8\n Google&nbsp;maps|http://maps.google.com/maps?ll=\$11\$9,\$12\$10&spn=0.1,0.1&t=k\n Mapquest|http://www.mapquest.com/maps/map.adp?searchtype=address&formtype=latlong&latlongtype=degrees&latdeg=\$11\$1&latmin=\$3&latsec=\$5&longdeg=\$12\$2&longmin=\$4&longsec=\$6&zoom=6",
	/*Messages for datetime parsing */
	'smw_nodatetime' => 'Das Datum „$1“ wurde nicht verstanden. Die Unterstützung von Kalenderdaten ist zur Zeit noch experimentell.',
	/*Messages for Autocompletion*/
	'tog-autotriggering' => 'Automatische auto-completion',


	// Messages for SI unit parsing
	'smw_no_si_unit' => 'Einheit nicht in SI-Representation. ',
	'smw_too_many_slashes' => 'Zu viele Slashes in SI-Representation. ',
	'smw_too_many_asterisks' => '"$1" contains several *\'s in sequence. ',
	'smw_denominator_is_1' => "The denominator must not be 1.",
	'smw_no_si_unit_remains' => "There remains no SI unit after optimization.",
	'smw_invalid_format_of_si_unit' => 'Invalid format of SI unit: $1 ',
	// Messages for the chemistry parsers
	'smw_not_a_chem_element' => '"$1" is not a chemical element.',
	'smw_no_molecule' => 'There is no molecule in the chemical formula "$1".',
	'smw_chem_unmatched_brackets' => 'The number of opening brackets does not match the closing ones in "$1".',
	'smw_chem_syntax_error' => 'Syntax error in chemical formula "$1".',
	'smw_no_chemical_equation' => '"$1" is not a chemical equation.',
	'smw_no_alternating_formula' => 'There is a missing or needless operator in "$1".',
	'smw_too_many_elems_for_isotope' => 'Only one element can be given for an isotope. A molecule was provided instead: "$1".',
	// Messages for attribute pages
	'smw_attribute_has_type' => 'This attribute has the datatype ',
	// Messages for help
	'smw_help_askown' => 'Ask your own question',
	'smw_help_askownttip' => 'Add your own question to the wiki helppages where it can be answered by other users',
	'smw_help_pageexists' => "This question is already in our helpsystem.\nClick 'more' to see all questions.",
	'smw_help_error' => "Oops. An error seems to have occured.\nYour question could not be added to the system. Sorry.",
	'smw_help_question_added' => "Your question has been added to our help system\nand can now be answered by other wiki users."
	
);

protected $smwUserMessages = array(
	'smw_devel_warning' => 'Diese Funktion befindet sich zur Zeit in Entwicklung und ist eventuell noch nicht voll einsatzfähig. Eventuell ist es ratsam, den Inhalt des Wikis vor der Benutzung dieser Funktion zu sichern.',
	// Messages for article pages of types, relations, and attributes
	'smw_type_header' => 'Attribute mit dem Datentyp „$1“',
	'smw_typearticlecount' => 'Es werden $1 Attribute mit diesem Datentyp angezeigt.',
	'smw_attribute_header' => 'Seiten mit dem Attribut „$1“',
	'smw_attributearticlecount' => '<p>Es werden $1 Seiten angezeigt, die dieses Attribut verwenden.</p>',
	'smw_relation_header' => 'Seiten mit der Relation „$1“',
	'smw_relationarticlecount' => '<p>Es werden $1 Seiten angezeigt, die diese Relation verwenden.</p>',
	/*Messages for Export RDF Special*/
	'exportrdf' => 'Seite als RDF exportieren', //name of this special
	'smw_exportrdf_docu' => '<p>Hier können Informationen über einzelne Seiten im RDF-Format abgerufen werden. Bitte geben Sie die Namen der gewünschten Seiten <i>zeilenweise</i> ein.</p>',
	'smw_exportrdf_recursive' => 'Exportiere auch alle relevanten Seiten rekursiv. Diese Einstellung kann zu sehr großen Ergebnissen führen!',
	'smw_exportrdf_backlinks' => 'Exportiere auch alle Seiten, die auf exportierte Seiten verweisen. Erzeugt RDF, das leichter durchsucht werden kann.',
	/*Messages for Search Triple Special*/
	'searchtriple' => 'Einfache semantische Suche', //name of this special
	'smw_searchtriple_header' => '<h1>Suche nach Relationen und Attributen</h1>',
	'smw_searchtriple_docu' => "<p>Benutzen Sie die Eingabemaske um nach Seiten mit bestimmten Eigenschaften zu suchen. Die obere Zeile dient der Suche nach Relationen, die untere der Suche nach Attributen. Sie können beliebige Felder leer lassen, um nach allen möglichen Belegungen zu suchen. Lediglich bei der Eingabe von Attributwerten (mit den entsprechenden Maßeinheiten) verlangt die Angabe des gewünschten Attributes.</p>\n\n<p>Beachten Sie, dass es zwei Suchknöpfe gibt. Bei Druck der Eingabetaste wird vielleicht nicht die gewünschte Suche durchgeführt.</p>",
	'smw_searchtriple_subject' => 'Seitenname (Subjekt):',
	'smw_searchtriple_relation' => 'Name der Relation:',
	'smw_searchtriple_attribute' => 'Name des Attributs:',
	'smw_searchtriple_object' => 'Seintenname (Objekt):',
	'smw_searchtriple_attvalue' => 'Wert des Attributs:',
	'smw_searchtriple_searchrel' => 'Suche nach Relationen',
	'smw_searchtriple_searchatt' => 'Suche nach Attributen',
	'smw_searchtriple_resultrel' => 'Suchergebnisse (Relationen)',
	'smw_searchtriple_resultatt' => 'Suchergebnisse (Attribute)',
	// Messages for Properties Special
	'properties' => 'Attribute',
	'smw_properties_docu' => 'In diesem Wiki gibt es die folgenden Attribute:',
	'smw_property_template' => '$1 mit Datentyp $2 ($3)', // <propname> of type <type> (<count>)
	'smw_propertylackspage' => 'Alle Attribute sollten durch eine Seite beschrieben werden!',
	'smw_propertylackstype' => 'Für dieses Attribut wurde kein Datentyp angegeben ($1 wird vorläufig als Typ angenommen).',
	'smw_propertyhardlyused' => 'Dieses Attribut wird im Wiki kaum verwendet!',
	// Messages for Unused Properties Special
	'unusedproperties' => 'Verwaiste Attribute',
	'smw_unusedproperties_docu' => 'Die folgenden Attributseiten existieren, obwohl sie nicht verwendet werden.',
	'smw_unusedproperty_template' => '$1 mit Datentyp $2', // <propname> of type <type>
	// Messages for Wanted Properties Special
	'wantedproperties' => 'Gewünschte Attribute',
	'smw_wantedproperties_docu' => 'Folgende Attribute haben bisher keine erläuterende Seite, obwohl sie bereits für die Beschreibung anderer Seiten verwendet werden.',
	'smw_wantedproperty_template' => '$1 ($2 Vorkommen)', // <propname> (<count> uses)
	/*Messages for Relation Special*/
	'relations' => 'Relationen',
	'smw_relations_docu' => 'In diesem Wiki gibt es die folgenden Relationen:',
	/*Messages for WantedRelations*/
	'wantedrelations' => 'Gewünschte Relationen',
	'smw_wanted_relations' => 'Folgende Relationen haben bisher keine erläuterende Seite, obwohl sie bereits für die Beschreibung anderer Seiten verwendet werden.',
	/*Messages for Properties Special*/
	'properties' => 'Properties',
	'smw_properties_docu' => 'In diesem Wiki gibt es die folgenden Properties:',
	'smw_attr_type_join' => ' with $1',
	'smw_properties_sortalpha' => 'Sort alphabetically',
	'smw_properties_sortmoddate' => 'Sort by modification date',
	'smw_properties_sorttyperange' => 'Sort by type/range',
	
	'smw_properties_sortdatatype' => 'Datatype properties',
	'smw_properties_sortwikipage' => 'Wikipage properties',
	'smw_properties_sortnary' => 'N-ary properties',
	/*Messages for Unused Relations Special*/
	'unusedrelations' => 'Verwaiste Relationen',
	'smw_unusedrelations_docu' => 'Die folgenden Relationenseiten existieren, obwohl sie nicht verwendet werden.',
	/*Messages for Unused Attributes Special*/
	'unusedattributes' => 'Verwaiste Attribute',
	'smw_unusedattributes_docu' => 'Die folgenden Attributseiten existieren, obwohl sie nicht verwendet werden.',
	/* Messages for the refresh button */
	'tooltip-purge' => 'Alle Anfrageergebnisse und Vorlagen auf dieser Seite auf den neuesten Stand bringen.',
	'purge' => 'aktualisieren',
	/*Messages for Import Ontology Special*/
	'ontologyimport' => 'Importiere Ontologie',
	'smw_ontologyimport_docu' => 'Diese Spezialseite erlaubt es, Informationen aus einer externen Ontologie zu importieren. Die Ontologie sollte in einem vereinfachten RDF-Format vorliegen. Weitere Informationen sind in der englischsprachigen <a href="http://wiki.ontoworld.org/index.php/Help:Ontology_import">Dokumentation zum Ontologieimport</a> zu finden.',
	'smw_ontologyimport_action' => 'Importieren',
	'smw_ontologyimport_return' => 'Zurück zum <a href="$1">Ontologieimport</a>.',
	/*Messages for (data)Types Special*/
	'types' => 'Datentypen',
	'smw_types_docu' => 'Die folgenden Datentypen können Attributen zugewiesen werden. Jeder Datentyp hat eine eigene Seite, auf der genauere Informationen eingetragen werden können.',
	'smw_types_units' => 'Standardumrechnung: $1; gestützte Umrechnungen: $2',
	'smw_types_builtin' => 'Eingebaute datatypen',
	/*Messages for ExtendedStatistics Special*/
	'extendedstatistics' => 'Erweiterte Statistiken',
	'smw_extstats_general' => 'Allgemeine Statistiken',
	'smw_extstats_totalp' => 'Anzahl der Seiten:',
	'smw_extstats_totalv' => 'Anzahl der Besuche:',
	'smw_extstats_totalpe' => 'Anzahl der Seiteneditierungen:',
	'smw_extstats_totali' => 'Anzahl der Bilder:',
	'smw_extstats_totalu' => 'Anzahl der Benutzer:',
	'smw_extstats_totalr' => 'Anzahl der Relationen:',
	'smw_extstats_totalri' => 'Anzahl der Instanzen von Relationen:',
	'smw_extstats_totalra' => 'Durchschnittliche Anzahl der Instanzen pro Relation:',
	'smw_extstats_totalpr' => 'Anzahl der Relationsseiten:',
	'smw_extstats_totala' => 'Anzahl der Attribute:',
	'smw_extstats_totalai' => 'Anzahl der Instanzen von Attributen:',
	'smw_extstats_totalaa' => 'Durchschnittliche Anzahl der Instanzen pro Attribut:',
	/*Messages for Flawed Attributes Special --disabled--*/
	'flawedattributes' => 'Fehlerhafte Attribute',
	'smw_fattributes' => 'Die unten aufgeführten Seiten enthalten fehlerhafte Attribute. Die Anzahl der fehlerhaften Attribute ist in den Klammern angegeben.',
	// Name of the URI Resolver Special (no content)
	'uriresolver' => 'URI-Auflöser',
	'smw_uri_doc' => '<p>Der URI-Auflöser setzt die Empfehlungen »<a href="http://www.w3.org/2001/tag/issues.html#httpRange-14">W3C TAG finding on httpRange-14</a>« um. Er sorgt dafür, dass Menschen nicht zu Webseiten werden.</p>',
	/*Messages for ask Special*/
	'ask' => 'Semantische Suche',
	'smw_ask_docu' => '<p>Bitte geben Sie eine Suchanfrage ein. Weitere Informationen sind auf der <a href="$1">Hilfeseite für die semantische Suche</a> zu finden.</p>',
	'smw_ask_doculink' => 'Semantische Suche',
	'smw_ask_sortby' => 'Sortiere nach Spalte',
	'smw_ask_ascorder' => 'Aufsteigend',
	'smw_ask_descorder' => 'Absteigend',
	'smw_ask_submit' => 'Finde Ergebnisse',
	// Messages for the search by property special
	'searchbyproperty' => 'Suche mittels Eigenschaft',
	'smw_sbv_docu' => '<p>Diese Spezialseite findet alle Seiten, die einen bestimmten Wert für die angegebene Eigenschaft haben.</p>',
	'smw_sbv_noproperty' => '<p>Bitte den Namen einer Eigenschaft eingeben</p>',
	'smw_sbv_novalue' => '<p>Bitte den gewünschten Wert eingeben oder alle Werte für die Eingenschaft $1 ansehen.</p>',
	'smw_sbv_displayresult' => 'Eine Liste aller Seiten, die eine Eigenschaft $1 mit dem Wert $2 haben.',
	'smw_sbv_property' => 'Eigenschaft',
	'smw_sbv_value' => 'Wert',
	'smw_sbv_submit' => 'Finde Ergebnisse',
	// Messages for the browsing system
	'browse' => 'Wiki browsen',
	'smw_browse_article' => 'Bitte geben Sie den Titel einer Seite ein.',
	'smw_browse_go' => 'Los',
	'smw_browse_more' => '&hellip;',
	// Generic messages for result navigation in all kinds of search pages
	'smw_result_prev' => 'Zurück',
	'smw_result_next' => 'Vorwärts',
	'smw_result_results' => 'Ergebnisse',
	'smw_result_noresults' => 'Keine Ergebnisse gefunden.',
	/*Messages for OntologyBrowser*/
	'ontologybrowser' => 'OntologyBrowser',
	'smw_ac_hint' => 'Drücken Sie Ctrl+Alt+Space für die Auto-Vervollständigung. (Ctrl+Space im IE)',
	'smw_ob_categoryTree' => 'Kategorie-Baum',
	'smw_ob_attributeTree' => 'Property-Baum',
	
	'smw_ob_instanceList' => 'Instanzen',
	'smw_ob_rel' => 'Relationen',
	'smw_ob_att' => 'Attribute',
	'smw_ob_relattValues' => 'Werte',
	'smw_ob_relattRangeType' => 'Wertebereich',
	'smw_ob_filter' => 'Filter',
	'smw_ob_filterbrowsing' => 'Filtere',
	'smw_ob_reset' => 'Zurücksetzen',
	'smw_ob_cardinality' => 'Kardinalität',
	'smw_ob_transsym' => 'Transitivität/Symmetrie',
	'smw_ob_footer' => '',
	'smw_ob_no_categories' => 'Keine Kategorien verfügbar.',
	'smw_ob_no_instances' => 'Keine Instanzen verfügbar.',
	'smw_ob_no_attributes' => 'Keine Attribute verfügbar.',
	'smw_ob_no_relations' => 'Keine Relationen verfügbar.',
	'smw_ob_no_annotations' => 'Keine Annotation verfügbar.',
	'smw_ob_no_properties' => 'Keine Properties verfügbar.',
	'smw_ob_help' => 'Der Ontology-Browser hilft ihnen sich im Wiki zurechtzufinden, das Schema zu untersuchen und ganz allgemein Seiten zu finden.
			Benutzen Sie den Filter-Mechanismus oben links um bestimmte Elemente der Ontologie zu finden und schränken sie das Ergebnis mit Hilfe der
			Filter unter jeder Spalte ein. Der Selektionsfluss ist anfangs von rechts nach links. Sie können die Pfeile durch Anklicken aber umdrehen.',
	
	'smw_ob_undefined_type' => '*undefinierter Typ*',
	'smw_ob_hideinstances' => 'Verstecke Instanzen',
	/* Messages for Gardening */
	'gardening' => 'Gardening',
	'smw_gardening' => 'Gardening',
	'smw_gardening_log' => 'GardeningLog',
	'smw_gardening_log_exp' => 'Das ist die Gardening Log Kategorie.',
	'smw_gard_welcome' => 'Das ist der Gardening-Werkzeugkasten. Er enthält einige Werkzeuge, mit deren Hilfe Sie das Wiki sauber und konsistent halten können.',
	'smw_gard_notools' => 'Wenn Sie hier keine Werkzeuge angezeigt bekommen, sind Sie entweder nicht eingeloggt oder haben nicht das Recht Gardening-Werkzeuge zu benutzen.',
	'smw_no_gard_log' => 'Kein Gardening-Log vorhanden',
	'smw_gard_abortbot' => 'Bot beenden',
	'smw_gard_unknown_bot' => 'Unbekannter Gardening-Bot',
	'smw_gard_no_permission' => 'Sie haben nicht das Recht diesen Bot zu benutzen.',
	'smw_gard_missing_parameter' => 'Fehelender Parameter',
	'smw_unknown_value' => 'Unbekannter Wert',
	'smw_out_of_range' => 'Außerhalb des Wertebereichs',
	'smw_gard_value_not_numeric' => 'Wert muss eine Zahl sein',
	'smw_gard_choose_bot' => 'Wähle Sie ein Werkzeug auf der linken Seite aus.',
	'smw_templatematerializerbot' => 'Materialisiere semantischen Inhalt der Templates',
	'smw_consistencybot' => 'Untersuche Konsistenz des Wikis',
	'smw_similaritybot' => 'Finde ähnliche Elemente',
	'smw_undefinedentitiesbot' => 'Finde undefinierte Elemente',
	'smw_missingannotationsbot' => 'Finde Seiten ohne Annotationen',
	'smw_anomaliesbot' => 'Finde Anomalien',
	'smw_renamingbot' => 'Benenne Seiten um',
	'smw_importontologybot' => 'Importiere eine Ontologie',

	/* Messages for Gardening Bot: ConsistencyBot */
	'smw_gard_consistency_docu'  => 'Der Konsistenz-Bot prüft auf Zyklen in der Taxonomie und finden Properties ohne Domäne und Wertebereich. Er prüft außerdem die korrekte Verwendung eines Properties auf der Instanz-Ebene.',
	'smw_gard_domains_not_covariant' => 'Domäne von [[$2:$1]] muss eine Subkategorie der Domäne des Super-Properties sein.',
	'smw_gard_domain_not_defined' => 'Domäne von [[$2:$1]] ist nicht definiert.',
	'smw_gard_ranges_not_covariant' => 'Wertebereichskategorie von [[$2:$1]] muss eine Subkategorie der Wertebereichskategorie des Super-Properties sein.',
	'smw_gard_range_not_defined' => 'Wertebereichskategorie von [[$2:$1]] ist nicht definiert.',
	'smw_gard_types_not_covariant' => 'Typ von [[$2:$1]] muss gleich dem Typ des Super-Properties sein.',
	'smw_gard_types_is_not_defined' => 'Typ von [[$2:$1]] ist nicht definiert.',
	'smw_gard_more_than_one_type' => 'Mehr als ein Typ definiert.',
	'smw_gard_mincard_not_covariant' => 'Mininamle Kardinalität von [[$2:$1]] ist geringer als im Super-Property definiert.',
	'smw_gard_maxcard_not_covariant' => 'Maximale Kardinalität von [[$2:$1]] ist höher als im Super-Property definiert.',
	'smw_gard_maxcard_not_null' => 'Maximale Kardinalität von [[$2:$1]] darf nicht 0 sein.',
	'smw_gard_mincard_not_below_null' => 'Mininamle Kardinalität von [[$2:$1]] darf nicht kleiner 0 sein.',
	'smw_gard_symetry_not_covariant' => 'Property [[$2:$1]] muss auch symmetrisch sein.',
	'smw_gard_trans_not_covariant' => 'Property [[$2:$1]] muss auch transitiv sein.',
	'smw_gard_doublemaxcard' => 'Warnung: Mehr als ein Property "maximale Kardinaltät" [[$3:$1]] gefunden. Benutze nur ersten Wert, nämlich $2.',
	'smw_gard_doublemincard' => 'Warnung: Mehr als ein Property "minimale Kardinaltät" [[$3:$1]] gefunden. Benutze nur ersten Wert, nämlich $2.',
	'smw_gard_wrongcardvalue' => 'Warnung: Kardinaltät of [[$2:$1]] hat falschen Wert. Muss eine positive Ganzzahl or * sein (unendlich). Wird interpretiert als 0.',
	'smw_gard_missing_param' => 'Warnung: Fehlender Parameter $4 in n-ärem Property [[$3:$2]] in Artikel [[$1]].',

	'smw_gard_domain_is_not_range' => 'Domäne von [[$2:$1]] passt nicht zur Wertbereichskategorie von [[$2:$2]].',
	'smw_gard_wrong_target' => 'Ziel-Artikel [[$1]] ist Element der falschen Kategorie, wenn es mit Property [[$3:$2]] benutzt wird.',
	'smw_gard_wrong_domain' => 'Artikel [[$1]] ist Element der falschen Kategorie, wenn es mit Property [[$3:$2]] benutzt wird.',
	'smw_gard_incorrect_cardinality' => 'Artikel [[$1]] benutzt Property [[$3:$2]] zu häufig oder zu wenig.',
	'smw_gard_no_errors' => 'Gratulation! Das Wiki ist konsistent.',
	'smw_gard_incomp_entities_equal' => 'Der Artikel [[$2:$1]] ist inkompatibel zu [[$4:$3]].',

	'smw_gard_errortype_categorygraph_contains_cycles' => 'Der Kategorie-Graph enthält Zyklen.',
	'smw_gard_errortype_propertygraph_contains_cycles' => 'Der Property-Graph enthält Zyklen.',
	
	'smw_gard_errortype_relation_problems' => 'Property-Schema-Probleme',
	'smw_gard_errortype_attribute_problems' => 'Property-Schema-Probleme',
	'smw_gard_errortype_inconsistent_relation_annotations' => 'Inkonsistente Anotationen',
	'smw_gard_errortype_inconsistent_attribute_annotations' => 'Inkonsistente Anotationen',
	'smw_gard_errortype_inconsistent_cardinalities' => 'Inkonsistente Anzahl von Annotationen',
	'smw_gard_errortype_inverse_relations' => 'Inkonsistente inverse Properties',
	'smw_gard_errortype_equality' => 'Inkonsistente äquivalente Elemente',

	/* SimilarityBot*/
	'smw_gard_sharecategories' => 'Beide sind Mitglied der folgenden Kategorien.',
	'smw_gard_sharedomains' => 'Beide sind Mitglied der folgenden Domänen-Kategorien.',
	'smw_gard_shareranges' => 'Beide sind Mitglied der folgenden WertbereichsKategorien.',
	'smw_gard_disticntbycommonfix' => 'Unterscheiden sich durch ein Präfix oder Suffix',
	'smw_gard_sharetypes' => 'Beide verwenden den folgenden Typ',
	'smw_gard_issimilarto' => 'ist ähnlich zu',
	'smw_gard_degreeofsimilarity' => 'Limit der Editierdistanz',
	'smw_gard_similarityscore' => 'Ähnlichkeitspunktzahl',
	'smw_gard_limitofresults' => 'max. Anzahl der Ergebnisse',
	'smw_gard_limitofsim' => 'Zeige nur Element die ähnlich sind zu',
	'smw_gard_similarityterm' => 'Suche nur nach Element die ähnlich zu diesem Term sind (kann weggelassen werden)',
	'smw_gard_similaritybothelp' => 'Diese Bot findet Elemente der Knowledgebase, die möglicherweise redundant sein und vereinigt werden können. Wenn Sie einen Term eingeben wird das System Elemente suchen, die ihm ähnlich sind. Wenn Sie keinen Term eingeben versucht das System alle potentiellen Redundanzen zu finden.',
	'smw_gard_similarannotation' => '$1 von Artikel $2 könnte fälschlicherweise als Annotation von $3 gemeint sein.',
	
	/*Undefined entities bot */
	'smw_gard_undefinedentities_docu' => 'Dieser Bot sucht nach Kategorien und Properties im Wiki, die zwar an irgendeiner Stelle verwendet wurden, aber nie definiert. Des weiteren findet er Instanzen ohne Kateogorien.',
	'smw_gard_property_undefined' => '[[$2:$1]] wird benutzt auf: $3',
	'smw_gard_category_undefined' => '[[:Category:$1]] wird benutzt auf: $2',
	'smw_gard_relationtarget_undefined' => '[[$1]] undefiniert und benutzt mit: $2',
	'smw_gard_instances_without_category' => '[[$1]]',

	'smw_gard_errortype_undefined_categories' => 'Undefinierte Kategorie (Entferne oder definiere)',
	'smw_gard_errortype_undefined_properties' => 'Undefiniertes Property (Entferne oder definiere)',
	'smw_gard_errortype_undefined_relationtargets' => 'Undefiniertes Ziel-Artikel',
	'smw_gard_errortype_instances_without_category' => 'Instanzen ohne Kategorie (füge sie zu einer Kategorie hinzu)',

	/* Missing annotations */
	'smw_gard_missingannot_docu' => 'Dieser Bot identifiziert Seiten im Wiki, die noch nicht annotiert wurden.',
	'smw_gard_missingannot_titlecontaining' => '(Optional) Nur Seiten deren Titel folgendes enthält',
	'smw_gard_missingannot_restricttocategory' => 'Nur unterhalb folgender Kategorien suchen',
	
	/* Anomalies */
	'smw_gard_anomaly_checknumbersubcat' => 'Überprüfe Anzahl der Subkategorien',
	'smw_gard_anomaly_checkcatleaves' => 'Prüfe auf Kategorie-Blätter',
	'smw_gard_anomaly_restrictcat' => 'Nur unterhalb der Kategorie(n)',
	'smw_gard_anomaly_deletecatleaves' => 'Lösche Kategorie-Blätter',
	'smw_gard_anomaly_docu' => 'Dieser Bot identifiziert Anomalien im semantischen Modell. Anomalien sind derzeit: Kategorien-Blätter (Kategorien, die weder Subkategorien noch Instanzen enthalten), sowie ungewühnliche Anzahlen von Subkategorien (Kateogorien mit nur einer oder mehr als 8 Subkategorien).',
	'smw_gard_anomalylog' => 'Folgende Anomalien konnten im Wiki gefunden werden.',
	'smw_gard_category_leafs' => 'Kategorie-Blätter (Kategorien, die weder Subkategorien noch Instanzen enthalten)',
	'smw_gard_subcategory_number_anomalies' => 'Anzahl der Subkategorien ungewöhnlich (Kateogorien mit nur einer oder mehr als 8 Subkategorien)',
	'smw_gard_subcategory' => 'Subkategorie',
	'smw_gard_subcategories' => 'Subkategorien',
	'smw_gard_all_category_leaves_deleted' => 'Alle Kategorie-Blätter wurden gelöscht.',
	'smw_gard_category_leaves_deleted' => 'Kategorie-Blätter unterhalb von [[$1:$2]] wurden gelöscht.',
	'smw_gard_category_leaf_deleted' => '$1 war ein Kategorie-Blatt. Entfernt vom Anomalie-Bot.',

	/* Combined Search*/
	'smw_cs_entities_found' => 'Die folgenden Elemente wurden in der Ontologie gefunden:',
	'smw_cs_attributevalues_found' => 'Die folgenden Instanzen enthalten Property-Werte die ihrer Suche entsprechen.',
	'smw_cs_aksfor_allinstances_with_annotation' => 'Frage nach allen Instanzen von \'$1\' die einen Annoatation von \'$2\' haben.',
	'smw_cs_askfor_foundproperties_and_values' => 'Frage Instanz \'$1\' nach allen gefunden Properties.',
	'smw_cs_ask'=> 'Zeige',
	'smw_cs_noresults' => 'Kein Element der Ontologie entspricht ihren Suchwörtern',
	'smw_cs_searchforattributevalues' => 'Suche nach Propertywerten, die ihren Suchwörtern entsprechen',
	'smw_cs_instances' => 'Artikel',
	'smw_cs_properties' => 'Properties',
	'smw_cs_values' => 'Werte',
	'smw_cs_openpage' => 'Öffne Seite',
	'smw_cs_openpage_in_ob' => 'Öffne Seite im Ontology Browser',
	'smw_cs_openpage_in_editmode' => 'Editiere Seite',
	'smw_cs_no_triples_found' => 'Keine Tripel gefunden!',

	'smw_autogen_mail' => 'Das ist eine automatisch generierte E-Mail. Nicht antworten!',
	
	/*Message for ImportOntologyBot*/
	'smw_gard_import_docu' => 'Importiert eine OWL-Datei.',
	'smw_gard_import_locationowl' => 'Ort der OWL-Datei',
	
	/*Message for TemplateMaterializerBot*/
	'smw_gard_templatemat_docu' => 'Dieser Bot aktualisiert alle Seiten, die Templates verwenden welche seit der letzten Materialisierung geändert wurden. Dies ist notwendig damit ASK-Queries in allen Fällen korrekte Ergebnisse liefern.',
	'smw_gard_templatemat_applytotouched' => 'Nur geänderte Tempates berücksichtigen',
	
	/*Messages for ContextSensitiveHelp*/
	'contextsensitivehelp' => 'Context Sensitive Help',
	'smw_contextsensitivehelp' => 'Context Sensitive Help',
	'smw_csh_newquestion' => 'Das ist eine neue Hilfe-Frage. Anklicken um sie zu beantworten!',

	/*Messages for Query Interface*/
	'queryinterface' => 'Query Interface',
	'smw_queryinterface' => 'Query Interface'

);

protected $smwDatatypeLabels = array(
	'smw_wikipage' => 'Seite', // name of page datatype
	'smw_string' => 'Zeichenkette',  // name of the string type
	'smw_text' => 'Text',  // name of the text type
	'smw_enum' => 'Aufzählung',  // name of the enum type
	'smw_bool' => 'Wahrheitswert',  // name of the boolean type
	'smw_int' => 'Ganze Zahl',  // name of the int type
	'smw_float' => 'Dezimalzahl',  // name of the floating point type
	'smw_length' => 'Länge',  // name of the length type
	'smw_area' => 'Fläche',  // name of the area type
	'smw_geolength' => 'Geografische Länge',  // OBSOLETE name of the geolength type
	'smw_geoarea' => 'Geografische Fläche',  // OBSOLETE name of the geoarea type
	'smw_geocoordinate' => 'Geografische Koordinaten', // name of the geocoord type
	'smw_mass' => 'Masse',  // name of the mass type
	'smw_time' => 'Zeit',  // name of the time type
	'smw_temperature' => 'Temperatur',  // name of the temperature type
	'smw_datetime' => 'Datum',  // name of the datetime (calendar) type
	'smw_email' => 'Email',  // name of the email (URI) type
	'smw_url' => 'URL',  // name of the URL type (string datatype property)
	'smw_uri' => 'URI',  // name of the URI type (object property)
	'smw_annouri' => 'URI-Annotation',  // name of the annotation URI type (annotation property)
	'smw_chemicalformula' => 'Chemical formula', // name of the type for chemical formulas
	'smw_chemicalequation' => 'Chemical equation', // name of the type for chemical equations
	'smw_mathematicalequation' => 'Mathematical equation' // name of the type for mathematical equations
	
);

protected $smwSpecialProperties = array(
	//always start upper-case
	SMW_SP_HAS_TYPE  => 'Hat Datentyp',
	SMW_SP_HAS_URI   => 'Gleichwertige URI',
	SMW_SP_SUBPROPERTY_OF => 'Untereigenschaft von',
	SMW_SP_MAIN_DISPLAY_UNIT => 'Erste Ausgabeeinheit',
	// SMW_SP_MAIN_DISPLAY_UNIT => 'Primärmaßeinheit für Schirmanzeige', // Great! We really should keep this wonderful translation here! Still, I am not fully certain about my versions either. -- mak
	SMW_SP_DISPLAY_UNIT => 'Ausgabeeinheit',
	SMW_SP_IMPORTED_FROM => 'Importiert aus',
	SMW_SP_CONVERSION_FACTOR => 'Entspricht',
	SMW_SP_CONVERSION_FACTOR_SI => 'Entspricht SI',
	SMW_SP_SERVICE_LINK => 'Bietet Service',
	SMW_SP_POSSIBLE_VALUE => 'Erlaubt Wert'
);

var $smwSpecialSchemaProperties = array (
	SMW_SSP_HAS_DOMAIN_HINT => 'Has domain hint',
	SMW_SSP_HAS_RANGE_HINT  => 'Has range hint',
	SMW_SSP_HAS_MAX_CARD => 'Has max cardinality',
	SMW_SSP_HAS_MIN_CARD => 'Has min cardinality',
	SMW_SSP_IS_INVERSE_OF => 'Is inverse of',
	SMW_SSP_IS_EQUAL_TO => 'Is equal to'
	);

var $smwSpecialCategories = array (
		SMW_SC_TRANSITIVE_RELATIONS => 'Transitive relations',
		SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrical relations'
);

	/**
	 * Function that returns the namespace identifiers.
	 */
	public function getNamespaceArray() {
		return array(
			SMW_NS_RELATION       => "Relation",
			SMW_NS_RELATION_TALK  => "Relation_Diskussion",
			SMW_NS_PROPERTY       => "Eigenschaft",
			SMW_NS_PROPERTY_TALK  => "Eigenschaft_Diskussion",
			SMW_NS_TYPE           => "Datentyp",
			SMW_NS_TYPE_TALK      => "Datentyp_Diskussion"
		);
	}

}


