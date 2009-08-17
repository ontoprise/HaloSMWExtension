<?php
/**
 * @author: Kai Kühn
 * 
 * Created on: 16.03.2009
 *
 */
class SGA_LanguageDe {
    
    public $contentMessages = array();
    
    public $userMessages = array (
/* Messages for Gardening */
    'gardening' => 'Gardening', // name of special page 'Gardening'
    'gardeninglog' => 'GardeningLog', // name of special page 'GardeningLog'
    'smw_gard_param_replaceredirects' => 'Ersetze Redirects',
    'smw_gardening_log_cat' => 'GardeningLog',
    'smw_gardeninglogs_docu' => 'Diese Seite ermöglicht den Zugriff auf die Gardening Logs.',
    'smw_gardening_log_exp' => 'Das ist die Gardening Log Kategorie.',
    'smw_gardeninglog_link' => 'Suchen Sie auch auf $1 für weitere Logging-Einträge.',
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
    'smw_gardissue_class_all' => 'Alle',
    
    'smw_gard_import_choosefile' => 'Die folgenden $1-Dateien sind auf dem Server verf�gbar.',
    'smw_gard_import_addfiles' => 'Weitere $2-Dateien k�nnen �ber $1 hinzugef�gt werden.',
    'smw_gard_import_nofiles' => 'Keine Dateien des Typs $1 auf dem Server verf�gbar.',
    'smw_gard_issue_local' => 'dieser Artikel',
    
    /* Messages for Gardening Bot: ConsistencyBot */
    'smw_gard_consistency_docu'  => 'Der Konsistenz-Bot prüft auf Zyklen in der Taxonomie und finden Attribute ohne Domäne und Wertebereich. Er prüft außerdem die korrekte Verwendung eines Attribute auf der Instanz-Ebene.',
    'smw_gardissue_domains_not_covariant' => 'Domäne $2 von $1 muss eine Subkategorie der Domäne des Super-Attribute sein.',
    'smw_gardissue_domains_not_defined' => 'Domäne von $1 ist nicht definiert.',
    'smw_gardissue_ranges_not_covariant' => 'Wertebereichskategorie $2 von $1 muss eine Subkategorie der Wertebereichskategorie des Super-Attribute sein.',
    'smw_gardissue_ranges_not_defined' => 'Wertebereichskategorie von $1 ist nicht definiert.',
    'smw_gardissue_domains_and_ranges_not_defined' => 'Please define the domain and/or range of $1.',
    'smw_gardissue_types_not_covariant' => 'Typ von $1 muss gleich dem Typ des Super-Attribute sein.',
    'smw_gardissue_types_not_defined' => 'Typ von $1 ist nicht definiert. Typ Wikipage intendiert? Bitte explizit machen.',
    'smw_gardissue_double_type' => 'Mehr als ein Typ definiert.',
    'smw_gardissue_mincard_not_covariant' => 'Minimale Kardinalität von $1 ist geringer als im Super-Attribut definiert.',
    'smw_gardissue_maxcard_not_covariant' => 'Maximale Kardinalität von $1 ist höher als im Super-Attribut definiert.',
    'smw_gardissue_maxcard_not_null' => 'Maximale Kardinalität von $1 darf nicht 0 sein.',
    'smw_gardissue_mincard_below_null' => 'Mininamle Kardinalität von $1 darf nicht kleiner 0 sein.',
    'smw_gardissue_symetry_not_covariant1' => 'Super-Attribut von $1 muss auch symmetrisch sein.',
    'smw_gardissue_symetry_not_covariant2' => 'Attribut $1 muss auch symmetrisch sein.',
    'smw_gardissue_transitivity_not_covariant1' => 'Super-Attribut von $1 muss auch transitiv sein.',
    'smw_gardissue_transitivity_not_covariant2' => 'Attribut $1 muss auch transitiv sein.',
    'smw_gardissue_double_max_card' => 'Warnung: Mehr als ein Attribut "maximale Kardinaltät" $1 gefunden. Benutze nur ersten Wert, nämlich $2.',
    'smw_gardissue_double_min_card' => 'Warnung: Mehr als ein Attribut "minimale Kardinaltät" $1 gefunden. Benutze nur ersten Wert, nämlich $2.',
    'smw_gardissue_wrong_mincard_value' => 'Warnung: Min-Kardinaltät of $1 hat falschen Wert. Wird interpretiert als 0.',
    'smw_gardissue_wrong_maxcard_value' => 'Warnung: Max-Kardinaltät of $1 hat falschen Wert. Muss eine positive Ganzzahl or * sein (unendlich). Wird interpretiert als 0.',
    'smw_gard_issue_missing_param' => 'Warnung: Fehlender Parameter $3 in n-ärem Attribut $2 in Artikel $1.',

    'smw_gard_issue_domain_not_range' => 'Domäne von $1 passt nicht zur Wertbereichskategorie von $2.',
    'smw_gardissue_wrong_target_value' => '$1 benutzt Attribut $2 mit einer Instanz der falschen Kategorie: $3.',
    'smw_gardissue_wrong_domain_value' => '$1 ist Element der falschen Kategorie, wenn es mit Attribut $2 benutzt wird.',
    'smw_gardissue_too_low_card' => '$1 benutzt Attribut $2 (oder eines seiner Subproperties) $3-mal zu wenig.',
    'smw_gardissue_missing_annotations' => 'Fehlende Annotationen. $1 benutzt Attribut $2 (oder eines seiner Subproperties) $3-mal zu wenig.',
    'smw_gardissue_too_high_card' => '$1 benutzt Attribut $2 (oder eines seiner Subproperties) $3-mal zu häufig.',
    'smw_gardissue_wrong_unit' => '$1 benutzt Attribut $2 mit falches Einheit $3.',
    'smw_gard_no_errors' => 'Gratulation! Das Wiki ist konsistent.',
    'smw_gard_issue_incompatible_entity' => 'Der Artikel $1 ist inkompatibel zu $2.',
    'smw_gard_issue_incompatible_type' => 'Das Attribut $1 hat einen inkompatiblen Typ zum Attribut $2.',
    'smw_gard_issue_incompatible_supertypes' => 'Das Attribut $1 hat Superproperties mit inkomptiblen Typen.',
    
    'smw_gard_issue_cycle' => 'Zyklus bei: $1',
    'smw_gard_issue_contains_further_problems' => 'Enthält weitere Probleme',
    
    'smw_gardissue_class_covariance' => 'Covariance Probleme',
    'smw_gardissue_class_undefined' => 'Invollständiges Schema',
    'smw_gardissue_class_missdouble' => 'Doubletten',
    'smw_gardissue_class_wrongvalue' => 'Falsche/Fehlende Werte',
    'smw_gardissue_class_incomp' => 'Inkompatible Entities',
    'smw_gardissue_class_cycles' => 'Zyklen',
    
    /* SimilarityBot*/
    'smw_gard_degreeofsimilarity' => 'Limit der Editierdistanz',
    'smw_gard_similarityscore' => 'Ähnlichkeitspunktzahl',
    'smw_gard_limitofresults' => 'max. Anzahl der Ergebnisse',
    'smw_gard_limitofsim' => 'Zeige nur Element die ähnlich sind zu',
    'smw_gard_similarityterm' => 'Suche nur nach Element die ähnlich zu diesem Term sind (kann weggelassen werden)',
    'smw_gard_similaritybothelp' => 'Diese Bot findet Elemente der Knowledgebase, die möglicherweise redundant sein und vereinigt werden können. Wenn Sie einen Term eingeben wird das System Elemente suchen, die ihm ähnlich sind. Wenn Sie keinen Term eingeben versucht das System alle potentiellen Redundanzen zu finden.',
    
    'smw_gardissue_similar_schema_entity' => '$1 and $2 sind sich ähnlich.',
    'smw_gardissue_similar_annotation' => '$1 von Artikel $2 könnte fälschlicherweise als Annotation von $3 gemeint sein.',
    'smw_gardissue_similar_term' => '$1 ist ähnlich zum Term $2',
    'smw_gardissue_share_categories' => '$1 und $2 sind Element derselben Kategorie(n): $3',
    'smw_gardissue_share_domains' => '$1 und $2 sind Element derselben Domäne(n): $3',
    'smw_gardissue_share_ranges' =>  '$1 und $2 sind Element derselben Zielkategorie: $3',
    'smw_gardissue_share_types' => '$1 und $2 haben den gleichen Typ: $3',
    'smw_gardissue_distinctby_prefix' => '$1 und $2 unterscheiden sich durch ein gemeinsames Präfix/Suffix.',

    'smw_gardissue_class_similarschema' => 'Ähnliche Schema Elemente',
    'smw_gardissue_class_similarannotations' => 'Ähnliche Annotationen',
    /*Undefined entities bot */
    'smw_gard_undefinedentities_docu' => 'Dieser Bot sucht nach Kategorien und Attribute im Wiki, die zwar an irgendeiner Stelle verwendet wurden, aber nie definiert. Des weiteren findet er Instanzen ohne Kateogorien.',
    'smw_gard_remove_undefined_categories' => 'Entferne Annotation undefinierter Kategorien',
    
    'smw_gardissue_property_undefined' => '$1 wird benutzt auf: $2',
    'smw_gardissue_category_undefined' => '$1 wird benutzt auf: $2',
    'smw_gardissue_relationtarget_undefined' => '$1 undefiniert und benutzt mit: $2',
    'smw_gardissue_instance_without_cat' => '$1',
    
    'smw_gardissue_class_undef_categories' => 'Undefinierte Kategorien',
    'smw_gardissue_class_undef_properties' => 'Undefinierte Attribute',
    'smw_gardissue_class_undef_relationtargets' => 'Undefinierte Relationsziele',
    'smw_gardissue_class_instances_without_cat' => 'Instanzen ohne Kategorie',

    /* Missing annotations */
    'smw_gard_missingannot_docu' => 'Dieser Bot identifiziert Seiten im Wiki, die noch nicht annotiert wurden.',
    'smw_gard_missingannot_titlecontaining' => '(Optional) Nur Seiten deren Titel folgendes enthält',
    'smw_gard_missingannot_restricttocategory' => 'Nur unterhalb folgender Kategorien suchen',
    'smw_gardissue_notannotated_page' => '$1 hat keine Annotationen',
    /* Anomalies */
    'smw_gard_anomaly_checknumbersubcat' => 'Überprüfe Anzahl der Subkategorien',
    'smw_gard_anomaly_checkcatleaves' => 'Prüfe auf Kategorie-Blätter',
    'smw_gard_anomaly_restrictcat' => 'Nur unterhalb der Kategorie(n)',
    'smw_gard_anomaly_deletecatleaves' => 'Lösche Kategorie-Blätter',
    'smw_gard_anomaly_docu' => 'Dieser Bot identifiziert Anomalien im semantischen Modell. Anomalien sind derzeit: Kategorien-Blätter (Kategorien, die weder Subkategorien noch Instanzen enthalten), sowie ungewühnliche Anzahlen von Subkategorien (Kateogorien mit nur einer oder mehr als 8 Subkategorien).',
    'smw_gard_anomalylog' => 'Folgende Anomalien konnten im Wiki gefunden werden.',
    
    'smw_gard_all_category_leaves_deleted' => 'Alle Kategorie-Blätter wurden gelöscht.',
    'smw_gard_was_leaf_of' => 'war Blattkategorie von',
    'smw_gard_category_leaf_deleted' => '$1 war ein Kategorie-Blatt. Entfernt vom Anomalie-Bot.',
    'smw_gardissue_category_leaf' => '$1 ist ein Kategorie-Blatt.',
    'smw_gardissue_subcategory_anomaly' => '$1 hat $2 Subkategorien.',
    
    'smw_gardissue_class_category_leaves' => 'Kategorie-Blätter',
    'smw_gardissue_class_number_anomalies' => 'Subkategorie Anomalien',
    
    /*Message for ImportOntologyBot*/
    'smw_gard_import_docu' => 'Importiert eine OWL-Datei.',
    
    /*Message for ExportOntologyBot*/
    'smw_exportontologybot' => 'Exportiere Ontologie',  
    'smw_gard_export_docu' => 'Dieser Bot exportiert die Wiki-Ontologie im OWL-Format.',
    'smw_gard_export_enterpath' => 'Exportdatei/-pfad',
    'smw_gard_export_onlyschema' => 'Exportiere nur das Schema',
    'smw_gard_export_ns' => 'Exportiere in Namensraum',
    'smw_gard_export_download' => 'Der Export war erfolgreich! Klicke $1 um den Wiki-Export als OWL-Datei herunterzuladen.',
    'smw_gard_export_here' => 'hier',

    /*Message for TemplateMaterializerBot*/
    'smw_gard_templatemat_docu' => 'Dieser Bot aktualisiert alle Seiten, die Templates verwenden welche seit der letzten Materialisierung geändert wurden. Dies ist notwendig damit ASK-Queries in allen Fällen korrekte Ergebnisse liefern.',
    'smw_gard_templatemat_applytotouched' => 'Nur geänderte Tempates berücksichtigen',
    'smw_gardissue_updatearticle' => 'Artikel $1 wurde neu geparst.',
    
     /*Message for Check referential integrity bot*/
      'smw_checkrefintegritybot' => "Überprüft referentielle Integrität",
    'smw_gard_checkrefint_docu' => "Dieser Bot prüft die referentielle Integrität externer Ressourcen.",
    'smw_gardissue_resource_not_exists' => '<a href=\"$1\">$2</a> exisitiert nicht.',
    'smw_gardissue_resource_moved_permanantly' => '<a href=\"$1\">$2</a> wurde verschoben.',
    'smw_gardissue_resource_not_accessible' => 'Auf <a href=\"$1\">$2</a> kann nicht zugegriffen werden.',
    'smw_gardissue_thisresource' => "Diese Ressource",
    
    /* SMWFindWork */
    'findwork' => 'Suche Arbeit',
    'smw_findwork_docu' => 'Diese Seite zeigt Ihnen Artikel die wahrscheinlich in ihr Interessensgebiet fallen. Viel Spass!',
    'smw_findwork_user_not_loggedin' => 'Sie sind NICHT eingeloggt. Es ist möglich die Seite anonym zu nutzen, aber sie bringt bessere Ergebnisse wenn Sie eingeloggt sind.',
    'smw_findwork_header' => 'Die Artikel-Liste enthält Artikel basierend auf ihrer Editier-History und offenen Gardening-Problemen. Wenn Sie nicht wissen was sie auswählen sollen, drücken Sie einfach $1. Das Wiki wählt dann etwas aus.<br>Wenn Sie wollen konnen Sie das Ergebnis auch genauer eingrenzen: ',
    'smw_findwork_rateannotations' => '<h2>Bewerten Sie Annotationen</h2>Sind diese Annotationen korrekt? Bitte nehmen Sie sich einen Moment Zeit.<br><br>',
    'smw_findwork_yes' => 'Ja',
    'smw_findwork_no' => 'Nein',
    'smw_findwork_dontknow' => 'Weiss nicht',
    'smw_findwork_sendratings' => 'Sende Bewertung',
    'smw_findwork_getsomework' => 'Irgendwelche Arbeit',
    'smw_findwork_show_details' => 'Zeige Details',
    'smw_findwork_heresomework' => 'Zufällig ausgewählte Arbeit',
    
    'smw_findwork_select' => 'Wähle',
    'smw_findwork_generalconsistencyissues' => 'Allgemeine Konsistenz-Probleme',
    'smw_findwork_missingannotations' => 'Fehlende Annotationen',
    'smw_findwork_nodomainandrange' => 'Attribute ohne Domain/Range',
    'smw_findwork_instwithoutcat' => 'Instanzen ohne Kategorie',
    'smw_findwork_categoryleaf' => 'Kategorie-Blätter',
    'smw_findwork_subcategoryanomaly' => 'Subkategorie-Anomalien',
    'smw_findwork_undefinedcategory' => 'Undefinierte Kategorien',
    'smw_findwork_undefinedproperty' => 'Undefinierte Attribute'
    
      );
}
