<?php
/**
 * @author Markus Krötzsch
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageDe extends SMW_HaloLanguage {

protected $smwContentMessages = array(
	
	'smw_viewinOB' => 'Im Ontology-Browser öffnen',
	
	'smw_att_head' => 'Attribute',
	'smw_rel_head' => 'Relationen zu anderen Seiten',
	'smw_spec_head' => 'Spezielle Eigenschaften',
	'smw_predefined_props' => 'Das ist das vordefinierte Property "$1"',
	'smw_predefined_cats' => 'Das ist die vordefinierte Kategorie "$1"',
	
	'smw_noattribspecial' => 'Die spezielle Eigenschaft „$1“ ist kein Attribut (bitte „::“ anstelle von „:=“ verwenden).',
	'smw_notype' => 'Dem Attribut wurde kein Datentyp zugewiesen.',
	
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
	
	'smw_relation_header' => 'Seiten mit der Relation „$1“',
	'smw_relationarticlecount' => '<p>Es werden $1 Seiten angezeigt, die diese Relation verwenden.</p>',
	
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
	'smw_gard_missing_parameter' => 'Fehlender Parameter',
	'smw_gard_missing_selection' => 'Bitte etwas ausw�hlen',
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
	
	'smw_gard_import_choosefile' => 'Die folgenden $1-Dateien sind auf dem Server verf�gbar.',
	'smw_gard_import_addfiles' => 'Weitere $2-Dateien k�nnen �ber $1 hinzugef�gt werden.',
	'smw_gard_import_nofiles' => 'Keine Dateien des Typs $1 auf dem Server verf�gbar.',

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

protected $smwSpecialProperties = array(
	//always start upper-case
	SMW_SP_CONVERSION_FACTOR_SI => 'Entspricht SI'
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


var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Chemische Formel',
	'smw_hdt_chemical_equation' => 'Chemische Gleichung',
	'smw_hdt_mathematical_equation' => 'Mathematische Gleichung',
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


