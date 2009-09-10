<?php
/**
 * @author: Kai K�hn
 * 
 * Created on: 17.04.2009
 *
 */
class SGA_LanguageNl {
    
    public $contentMessages = array();
    
    public $userMessages = array (
     /* Messages for Gardening */
    'gardening' => 'Gardening', // name of special page 'Gardening'
    'gardeninglog' => 'GardeningLog', // name of special page 'GardeningLog'
    'smw_gard_param_replaceredirects' => 'Redirects vervangen',
    'smw_gardening_log_cat' => 'GardeningLog',
    'smw_gardeninglogs_docu' => 'Deze pagina geeft toegang tot de logs van de Gardening Bots. Je kan ze filteren op verschillende manieren.',
    'smw_gardening_log_exp' => 'Dit is de Gardening log categorie',
    'smw_gardeninglog_link' => 'Neem ook een kijkje op $1 voor andere loggings.',
    'smw_gard_welcome' => 'Dit is de Gardening gereedschapskist. Het biedt enkele tools die helpen om de wiki kennisbasis zuiver en consistent te houden.',
    'smw_gard_notools' => 'Indien je hier geen tools ziet, ben je waarschijnlijk niet ingelogd of heb je geen rechten om de gardening tools te gebruiken.',
    'smw_no_gard_log' => 'Geen Gardening log',
    'smw_gard_abortbot' => 'Bot beëindigen',
    'smw_gard_unknown_bot' => 'Ongekende bot',
    'smw_gard_no_permission' => 'Je hebt geen toelating om deze bot te gebruiken.',
    'smw_gard_missing_parameter' => 'Ontbrekende parameter',
    'smw_gard_missing_selection' => 'Ontbrekende selectie',
    'smw_unknown_value' => 'Ongekende waarde',
    'smw_out_of_range' => 'Buiten waardenbereik',
    'smw_gard_value_not_numeric' => 'Waarde moet een getal zijn',
    'smw_gard_choose_bot' => 'Kies links een gardening tool.',
    'smw_templatematerializerbot' => 'Template inhoud materialiseren',
    'smw_consistencybot' => 'Wiki consistentie controleren',
    'smw_similaritybot' => 'Gelijkaardige elementen zoeken',
    'smw_undefinedentitiesbot' => 'Ongedefinieerde elementen zoeken',
    'smw_missingannotationsbot' => 'Pagina´s zonder aantekeningen zoeken',
    'smw_anomaliesbot' => 'Anomalieën zoeken',
    'smw_renamingbot' => 'Pagina hernoemen',
    'smw_importontologybot' => 'Een ontologie importeren',
    'smw_gardissue_class_all' => 'Alle',
    
    /* Messages for Gardening Bot: ImportOntology Bot*/
    'smw_gard_import_choosefile' => 'De volgende $1 bestanden zijn beschikbaar.',
    'smw_gard_import_addfiles' => '$2 bestaan toevoegen door $1 te gebruiken.',
    'smw_gard_import_nofiles' => 'Er zijn geen bestanden van het type $1 beschikbaar',
    
    /* Messages for Gardening Bot: ConsistencyBot */
    'smw_gard_consistency_docu'  => 'De consistentiebot houdt toezicht op loops in de taxonomie en eigenschappen zonder domein of waardenbereik. Het controleert ook op het correct gebruik van eigenschappen overeenstemmend met domein en waardenbereik, alsook foutieve kardinaliteiten.',
    'smw_gard_no_errors' => 'Proficiat! De wiki is consistent.',
    'smw_gard_issue_local' => 'dit artikel',
    
    
    'smw_gardissue_domains_not_covariant' => 'Het domein $2 van $1 en de domeincategorie van de supereigenschap (of een subcategorie) komen niet overeen.',
    'smw_gardissue_domains_not_defined' => 'Definieer het domein van $1.',
    'smw_gardissue_ranges_not_covariant' => 'Het waardenbereik $2 van $1 en een subcategorie van de waardenbereiken van de supereigenschap komen niet overeen.',
    'smw_gardissue_domains_and_ranges_not_defined' => 'Definieer het domein en/of het waardenbereik van $1.',
    'smw_gardissue_ranges_not_defined' => 'Definieer het waardenbereik van $1',
    'smw_gardissue_types_not_covariant' => 'De datatypes van $1 en van de supereigenschap komen niet overeen.',
    'smw_gardissue_types_not_defined' => 'Het datatype van $1 is niet gedefinieerd. Specifieer dit expliciet. Type:pagina wordt als standaard genomen.',
    'smw_gardissue_double_type' => 'Kies het juiste datatype voor $1, momenteel zijn er $2 gedefinieerd.',
    'smw_gardissue_mincard_not_covariant' => 'Specifieer een minimum cardinaliteit voor $1 groter of gelijk aan die van de supereigenschap.',
    'smw_gardissue_maxcard_not_covariant' => 'Specifieer een maximum cardinaliteit voor $1 kleiner of gelijk aan die van de supereigenschap.',
    'smw_gardissue_maxcard_not_null' => 'Definieer een maximum cardinaliteit van $1 hoger dan 0.',
    'smw_gardissue_mincard_below_null' => 'Definieer een positieve maximum cardinaliteit van $1.',
    'smw_gardissue_symetry_not_covariant1' => 'De supereigenschap van $1 moet ook symmetrisch zijn.',
    'smw_gardissue_symetry_not_covariant2' => '$1 moet ook symmetrisch zijn, in overeenkomst met de supereigenschap.',
    'smw_gardissue_transitivity_not_covariant1' => 'De supereigenschap van $1 moet ook transitief zijn.',
    'smw_gardissue_transitivity_not_covariant2' => '$1 moet transitief zijn, in overeenkomst met de supereigenschap.',
    'smw_gardissue_double_max_card' => 'Specifieer slechts één maximum cardinaliteit voor $1. Neem eerste waarde, zijnde $2.',
    'smw_gardissue_double_min_card' => 'Specifieer slechts één minimum cardinaliteit voor $1. Neem eerste waarde, zijnde $2.',
    'smw_gardissue_wrong_mincard_value' => 'Corrigeer de minimum cardinaliteit van $1. Wordt gelezen als 0.',
    'smw_gardissue_wrong_maxcard_value' => 'Corrigeer de maximum cardinaliteit van $1. Dit moet een positief geheel getald zijn of *. Wordt gelezen als 0.',
    'smw_gard_issue_missing_param' => 'Ontbrekende parameter $3 in de n-tallige eigenschap $2 in $1.',

    'smw_gard_issue_domain_not_range' => 'Domein van $1 komt niet overeen met het waardenbereik van de inverse eigenschap $2.',
    'smw_gardissue_wrong_target_value' => '$1 kan eigenschap $2 met de waarde $3 niet gebruiken, in overeenkomst met de definitie van eigenschap.',
    'smw_gardissue_wrong_domain_value' => '$1 kan eigenschap $2 niet gebruiken, in overeenkomst met de definitie van eigenschap.',
    'smw_gardissue_too_low_card' => 'Voeg nog $3 aantekeningen van $2 (of van de subeigenschappen) toe aan $1.',
    'smw_gardissue_too_high_card' => 'Verwijder $3 aantekeningen van $2 (of van de subeigenschappen) bij $1.',
    'smw_gardissue_wrong_unit' => 'Corrigeer de onjuiste eenheid $3 voor eigenschap $2 in $1.',
    'smw_gard_issue_incompatible_entity' => 'Element $1 is niet compatibel met $2. Controleer dat ze in dezelfde naamruimte zijn.',
    'smw_gard_issue_incompatible_type' => 'De eigenschap $1 heeft een incompatibel datatype t.o.v. $2. Alhoewel deze hetzelfde zouden moeten zijn, zijn ze verschillend.',
    'smw_gard_issue_incompatible_supertypes' => 'De eigenschap $1 heeft supereigenschappen met verschillende datatypes. Zorg ervoor dat deze hetzelfde zijn.',
    
    'smw_gard_issue_cycle' => 'Loop bij: $1',
    'smw_gard_issue_contains_further_problems' => 'Bevat meerdere problemen',
    
    'smw_gardissue_class_covariance' => 'Covariantie problemen',
    'smw_gardissue_class_undefined' => 'Onvolledig schema',
    'smw_gardissue_class_missdouble' => 'Dubbels',
    'smw_gardissue_class_wrongvalue' => 'Verkeerde/ontbrekende waarden',
    'smw_gardissue_class_incomp' => 'Incompatibele elementen',
    'smw_gardissue_class_cycles' => 'Loops',

    /* SimilarityBot*/
    'smw_gard_degreeofsimilarity' => 'Pas afstandsgrens aan',
    'smw_gard_similarityscore' => 'Gelijkheidsscore (Aantal gelijkheden)',
    'smw_gard_limitofresults' => 'Aantal resultaten',
    'smw_gard_limitofsim' => 'Toon enkel elementen die lijken op',
    'smw_gard_similarityterm' => 'Zoek elementen die gelijkaardig zijn aan de volgende term (kan leeg zijn)',
    'smw_gard_similaritybothelp' => 'Deze bot identificeert elementen in de kennisbasis die samengebracht zouden kunnen worden. Na het ingeven van een term, zoekt het systeem gelijkaardige elementen. Indien er geen term ingegeven wordt, vindt het systeem mogelijk redundante elementen.',
        'smw_gardissue_similar_schema_entity' => '$1 en $2 zijn lexicaal gelijk.',
    'smw_gardissue_similar_annotation' => '$3 bevat twee zeer gelijkaardige aantekeningen: $1 en $2',
    'smw_gardissue_similar_term' => '$1 is gelijkaardig aan term $2',
    'smw_gardissue_share_categories' => '$1 en $2 delen de volgende categorieën: $3',
    'smw_gardissue_share_domains' => '$1 en $2 delen de volgende domeinen: $3',
    'smw_gardissue_share_ranges' =>  '$1 en $2 delen de volgende waardenbereiken: $3',
    'smw_gardissue_share_types' => '$1 en $2 delen de volgende types: $3',
    'smw_gardissue_distinctby_prefix' => '$1 en $2 zijn verschillend met gemeenschappelijke prefix of suffix',
    
    'smw_gardissue_class_similarschema' => 'Gelijkaardige schema elementen',
    'smw_gardissue_class_similarannotations' => 'Gelijkaardige aantekeningen',

    /*Undefined entities bot */
    'smw_gard_undefinedentities_docu' => 'De bot voor ongedefinieerde elementen zoekt categorieën en eigenschappen die gebruikt worden in de wiki maar niet gedefinieerd zijn, alsook entiteiten die geen categorie toegewezen hebben.',
    'smw_gard_remove_undefined_categories' => 'Verwijder aantekeningen van ongedefinieerde categorieën',
    
    'smw_gardissue_property_undefined' => '$1 gebruikt bij: $2',
    'smw_gardissue_category_undefined' => '$1 gebruikt bij: $2',
    'smw_gardissue_relationtarget_undefined' => '$1 ongedefinieerd indien gebruikt met: $2',
    'smw_gardissue_instance_without_cat' => '$1 behoort niet tot een categorie',
    
    'smw_gardissue_class_undef_categories' => 'Ongedefinieerde categorieën',
    'smw_gardissue_class_undef_properties' => 'Ongedefinieerde eigenschappen',
    'smw_gardissue_class_undef_relationtargets' => 'Ongedefinieerde relatietargets',
    'smw_gardissue_class_instances_without_cat' => 'Entiteiten zonder categorie',


    /* Missing annotations */
    'smw_gard_missingannot_docu' => 'Deze bot identificeert wiki-pagina´s die geen aantekeningen hebben.',
    'smw_gard_missingannot_titlecontaining' => '(Optionaal) Alleen pagina´s met een titel met',
    'smw_gard_restricttocategory' => 'Beperken tot categorieën',
    'smw_gardissue_notannotated_page' => '$1 heeft geen aantekeningen',

    /* Anomalies */
    'smw_gard_anomaly_checknumbersubcat' => 'Controleer het aantal subcategorieën',
    'smw_gard_anomaly_checkcatleaves' => 'Controleer categoriebladen',
    'smw_gard_anomaly_restrictcat' => 'Beperken tot categorieën (gescheiden door ;)',
    'smw_gard_anomaly_deletecatleaves' => 'Categoriebladen verwijderen',
    'smw_gard_anomaly_docu' => 'Deze bot zoekt categoriebladen (categorieën die geen subcategorieën of entiteiten bevatten) en anomalieën van subcategorie-aantallen (categorieën met slechts één of meer dan acht subcategorieën).',
    'smw_gard_anomalylog' => 'De anomaliebot verwijderde de volgende pagina´s',

    
    'smw_gard_all_category_leaves_deleted' => 'Alle categoriebladen werden verwijderd.',
    'smw_gard_was_leaf_of' => 'was een blad van',
    'smw_gard_category_leaf_deleted' => '$1 was een categorieblad. Werd verwijderd door de anomaliebot.',
    'smw_gardissue_category_leaf' => '$1 is een categorieblad.',
    'smw_gardissue_subcategory_anomaly' => '$1 heeft $2 subcategorieën.',
    
    'smw_gardissue_class_category_leaves' => 'Categoriebladen',
    'smw_gardissue_class_number_anomalies' => 'Subcategorie anomalie',
    
    /*Message for ImportOntologyBot*/
    'smw_gard_import_docu' => 'Importeer een OWL bestand.',
    'smw_gard_import_uselabels' => 'Use labels',
    
    /*Message for ExportOntologyBot*/
    'smw_exportontologybot' => 'Ontology exporteren',   
    'smw_gard_export_docu' => 'Deze bot exporteert de wiki ontology in OWL formaat.',
    'smw_gard_export_enterpath' => 'Bestand/pad exporteren',
    'smw_gard_export_onlyschema' => 'Enkel het schema exporteren',
    'smw_gard_export_ns' => 'Naar namespace exporteren',
    'smw_gard_export_download' => 'De export is succesvol verlopen! Klik op $1 om de wiki export te downloaden als OWL bestand.',
    'smw_gard_export_here' => 'hier',

    /*Message for TemplateMateriazerBot*/
    'smw_gard_templatemat_docu' => 'Deze bot actualiseert de wikipagina´s die templates gebruiken die sinds de laatste materialisering veranderd zijn. Dit is noodzakelijk om altijd juiste resultaten te krijgen van ASK queries.',
    'smw_gard_templatemat_applytotouched' => 'Enkel toepassen op veranderde templates',
    'smw_gardissue_updatearticle' => 'Artikel $1 werd opnieuw geparsed.',
    
    /* SMWFindWork */
    'findwork' => 'Werk vinde',
    'smw_findwork_docu' => 'Deze pagina stelt artikels voor die iet of wat problematisch zijn maar die je misschien graag zou willen aanpassen/corrigeren.',
    'smw_findwork_user_not_loggedin' => 'Je bent NIET aangemeld. Het is mogelijk om de pagina anoniem te gebruiken, maar het is veel beter om aangemeld te zijn.',  
    'smw_findwork_header' => 'De voorgestelde artikels reflecteren jouw interesses gebaseerd op je aanpassingsgeschiedenis. Indien je niet weet wat te kiezen, klik op $1. Het systeem zal dan in jouw plaats iets selecteren.<br /><br />Indien je aan iets specifiekers wil werken, kan je één van de volgende kiezen: ',
    'smw_findwork_rateannotations' => '<h2>Aantekeningen evalueren</h2>Je kan helpen de kwaliteit van de wiki te verbeteren door de kwaliteit van de volgende uitspraken te evalueren. Zijn de volgende uitspraken correct?<br><br>',
    'smw_findwork_yes' => 'Juist.',
    'smw_findwork_no' => 'Foutief.',
    'smw_findwork_dontknow' => 'Geen idee.',
    'smw_findwork_sendratings' => 'Evaluaties versturen',
    'smw_findwork_getsomework' => 'Geef me wat werk!',
    'smw_findwork_show_details' => 'Details tonen',
    'smw_findwork_heresomework' => 'Hier is wat werk',
    
    'smw_findwork_select' => 'Selecteren',
    'smw_findwork_generalconsistencyissues' => 'Algemene consistentie problemen',
    'smw_findwork_missingannotations' => 'Ontbrekende aantekeningen',
    'smw_findwork_nodomainandrange' => 'Eigenschappen zonder type/domein',
    'smw_findwork_instwithoutcat' => 'Entiteiten zonder categorie',
    'smw_findwork_categoryleaf' => 'Categoriebladen',
    'smw_findwork_subcategoryanomaly' => 'Subcategorie anomaliën',
    'smw_findwork_undefinedcategory' => 'Ongedefinieerde categorieën',
    'smw_findwork_undefinedproperty' => 'Ongedefinieerde eigenschappen'
   
        
    );
}
