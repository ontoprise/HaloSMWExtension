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
* 
*/
/**
 * @author Markus Krötzsch
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageNl extends SMW_HaloLanguage {

protected $smwContentMessages = array(

	'smw_viewinOB' => 'Openen in de ontology-browser',

	'smw_att_head' => 'Attribuut-waarden',
	'smw_rel_head' => 'Relaties met andere pagina´s',
	'smw_predefined_props' => 'Dit is de voorgedefinieerde eigenschap "$1"',
	'smw_predefined_cats' => 'Dit is de voorgedefinieerde categorie "$1"',

	'smw_noattribspecial' => 'De speciale eigenschap "$1" is geen attribuut (gebruik "::" in plaats van ":=").',
	'smw_notype' => 'Er is geen type gedefinieerd voor dit attribuut.',
	/*Messages for Autocompletion*/
	'tog-autotriggering' => 'Automatische auto-completion',


	// Messages for SI unit parsing
	'smw_no_si_unit' => 'Geen eenheid in de SI voorstelling. ',
	'smw_too_many_slashes' => 'Teveel slashes in de SI voorstelling. ',
	'smw_too_many_asterisks' => '"$1" bevat teveel *\'s. ',
	'smw_denominator_is_1' => "De noemer mag niet 1 zijn.",
	'smw_no_si_unit_remains' => "Er is geen SI unit resterend na optimalisatie.",
	'smw_invalid_format_of_si_unit' => 'Ongeldig formaat van SI eenheid: $1 ',
	// Messages for the chemistry parsers
	'smw_not_a_chem_element' => '"$1" is geen chemisch element.',
	'smw_no_molecule' => 'Er is geen molecule in de chemische formule "$1".',
	'smw_chem_unmatched_brackets' => 'Het aantal openingshaakjes komt niet overeen met de sluitende haakjes in "$1".',
	'smw_chem_syntax_error' => 'De chemische formule "$1" is syntactisch niet juist.',
	'smw_no_chemical_equation' => '"$1" is geen chemische gelijkheid.',
	'smw_no_alternating_formula' => 'Er is een ontbrekende of onnodige operator in "$1".',
	'smw_too_many_elems_for_isotope' => 'Een isotoop kan slechts één element hebben. Daarom werd een molecule aangemaakt: "$1".',
	// Messages for attribute pages
	'smw_attribute_has_type' => 'Dit attribuut heeft het datatype ',
	// Messages for help
	'smw_help_askown' => 'Stel je eigen vraag',
	'smw_help_askownttip' => 'Voeg je eigen vragen toe aan de wiki help-pagina´s waar ze beantwoord kunnen worden door andere gebruikers',
	'smw_help_pageexists' => "Deze vraag is reeds in ons helpsysteem.\nKies 'more' om alle vragen te zien.",
	'smw_help_error' => "Oeps. Er blijkt een fout gebeurd te zijn.\nJe vraag kon niet toegevoegd worden aan het systeem. Sorry.",
	'smw_help_question_added' => "Je vraag werd toegevoegd aan ons helpsysteem\nen kan nu beantwoord worden door andere wiki gebruikers."

);


protected $smwUserMessages = array(
	'smw_devel_warning' => 'Dit feature wordt momenteel ontwikkeld, en is mogelijk niet volledig functioneel. Sla je gegevens op vooraleer je het gebruikt.',
	// Messages for pages of types, relations, and attributes

	'smw_relation_header' => 'Pagina´s met de eigenschap "$1"',
	'smw_relationarticlecount' => '<p>Toont $1 pagina´s die deze eigenschap gebruiken.</p>',
	'smw_subproperty_header' => 'Sub-eigenschappen van "$1"',
	'smw_subpropertyarticlecount' => '<p>Toont $1 sub-eigenschappen.</p>',

	// Messages for category pages
	'smw_category_schemainfo' => 'Schema informatie voor categorie "$1"',
	'smw_category_properties' => 'Eigenschappen',
	'smw_category_properties_range' => 'Eigenschappen met waardenbereik "$1"',
	
	'smw_category_askforallinstances' => 'Vraag alle entiteiten van "$1" en alle entiteiten von de sub-categorieën',
	'smw_category_queries' => 'Queries voor categorieën',
	
	'smw_category_nrna' => 'Pagina´s met verkeerd toegekend domein "$1".',
	'smw_category_nrna_expl' => 'Deze pagina´s hebben een domein maar zijn geen eigenschap.',
	'smw_category_nrna_range' => 'Pagina´s met verkeerd toegekende waardenbereik "$1".',
	'smw_category_nrna_range_expl' => 'Deze pagina´s hebben een waardenbereik maar zijn geen eigenschap.',


	'smw_exportrdf_all' => 'Alle semantische gegevens exporteren',

	// Messages for Search Triple Special
	'searchtriple' => 'Eenvoudige semantische zoekfunctie', //name of this special
	'smw_searchtriple_docu' => "<p>Vul de bovenste of onderste lijn van het invulformulier in om te zoeken naar respectievelijk relaties of attributen. 
	Sommige velden kunnen leeg gelaten worden of meerdere resultaten bevatten. Maar indien een attribuut-waarde gegeven is, moet de attribuut-naam ook ingevuld worden. Zoals gewoonlijk, kunnen attribuut-waarden ingevuld worden met een maateenheid. </p>\n\n<p>
Druk de rechter knop om resultaten te verkrijgen. Enkel <i>Return</i> drukken geeft eventueel niet de zoekfunctie weer die gewenst is.</p>",
	'smw_searchtriple_subject' => 'Onderwerp pagina:',
	'smw_searchtriple_relation' => 'Relatie-naam:',
	'smw_searchtriple_attribute' => 'Attribuut-naam:',
	'smw_searchtriple_object' => 'Object pagina:',
	'smw_searchtriple_attvalue' => 'Attribuut-waarde:',
	'smw_searchtriple_searchrel' => 'Zoek naar relaties',
	'smw_searchtriple_searchatt' => 'Zoek naar attributen',
	'smw_searchtriple_resultrel' => 'Zoekresultaten (relaties)',
	'smw_searchtriple_resultatt' => 'Zoekresultaten (attributen)',

	// Messages for Relations Special
	'relations' => 'Relaties',
	'smw_relations_docu' => 'De volgende relaties zijn aanwezig in de wiki.',
	// Messages for WantedRelations Special
	'wantedrelations' => 'Gewenste relaties',
	'smw_wanted_relations' => 'De volgende relaties hebben nog geen pagina met uitleg, alhoewel ze reeds gebruikt worden om andere pagina´s te beschrijven.',
	// Messages for Properties Special
	'properties' => 'Eigenschappen',
	'smw_properties_docu' => 'De volgende eigenschappen zijn aanwezig in de wiki.',
	'smw_attr_type_join' => ' met $1',
	'smw_properties_sortalpha' => 'Alfabetisch sorteren',
	'smw_properties_sortmoddate' => 'Sorteren op basis van aanpassingsdatum',
	'smw_properties_sorttyperange' => 'Sorteren op basis van type/waardenbereik',

	'smw_properties_sortdatatype' => 'Datatype eigenschappen',
	'smw_properties_sortwikipage' => 'Wikipagina eigenschappen',
	'smw_properties_sortnary' => 'N-tellige eigenschappen',
	// Messages for Unused Relations Special
	'unusedrelations' => 'Ongebruikte relaties',
	'smw_unusedrelations_docu' => 'De volgende relatie-pagina´s bestaan alhoewel geen enkele andere pagina er gebruik van maakt.',
	// Messages for Unused Attributes Special
	'unusedattributes' => 'Ongebruikte attributen',
	'smw_unusedattributes_docu' => 'De volgende attribuut-pagina´s bestaan alhoewel geen enkele andere pagina er gebruik van maakt.',


/*Messages for OntologyBrowser*/
	'ontologybrowser' => 'Ontologie-browser',
	'smw_ac_hint' => 'Druk Ctrl+Alt+Space voor auto-completion. (Ctrl+Space in IE)',
	'smw_ob_categoryTree' => 'Categorieënboom',
	'smw_ob_attributeTree' => 'Eigenschappenboom',

	'smw_ob_instanceList' => 'Entiteiten',
	
	'smw_ob_att' => 'Eigenschappen',
	'smw_ob_relattValues' => 'Waarden',
	'smw_ob_relattRangeType' => 'Type/waardenbereik',
	'smw_ob_filter' => 'Filter',
	'smw_ob_filterbrowsing' => 'Filteren',
	'smw_ob_reset' => 'Reset',
	'smw_ob_cardinality' => 'Kardinaliteit',
	'smw_ob_transsym' => 'Transitiviteit/symmetrie',
	'smw_ob_footer' => '',
	'smw_ob_no_categories' => 'Geen categorieën beschikbaar.',
	'smw_ob_no_instances' => 'Geen entiteiten beschikbaar.',
	'smw_ob_no_attributes' => 'Geen attributen beschikbaar.',
	'smw_ob_no_relations' => 'Geen relaties beschikbaar.',
	'smw_ob_no_annotations' => 'Geen aantekeningen beschikbaar.',
	'smw_ob_no_properties' => 'Geen eigenschappen beschikbaar.',
	'smw_ob_help' => 'De ontologie-browser maakt het mogelijk om doorheen de ontologie te navigeren en zo gemakkelijk items in de wiki te vinden en te identificeren. Gebruik het filter mechanisme	links bovenaan om naar specifieke elementen in de ontologie te zoeken. De filters beneden elke kolom laten toe om de zoekresultaten te beperken. Bij het begin gebeurt het browsen van links naar rechts. 	Deze richting kan veranderd worden door op de grote pijlen tussen de kolommen te klikken.',
	'smw_ob_undefined_type' => '*ongedefinieerd type*',
	'smw_ob_hideinstances' => 'Entiteiten verbergen',
	
	'smw_ob_hasnumofsubcategories' => 'Aantal subcategorieën',
	'smw_ob_hasnumofinstances' => 'Aantal entiteiten',
	'smw_ob_hasnumofproperties' => 'Aantal eigenschappen',
	'smw_ob_hasnumofpropusages' => 'Deze eigenschap is $1 maal geannoteerd',
	'smw_ob_hasnumoftargets' => 'Deze entiteit is $1 maal gelinkt.',
	'smw_ob_hasnumoftempuages' => 'Deze template is $1 maal gebruikt',
	
	/* Commands for ontology browser */
	'smw_ob_cmd_createsubcategory' => 'Subcategorie toevoegen',
	'smw_ob_cmd_createsubcategorysamelevel' => 'Categorie op hetzelfde niveau toevoegen',
	'smw_ob_cmd_renamecategory' => 'Hernoemen',
	'smw_ob_cmd_createsubproperty' => 'Subeigenschap toevoegen',
	'smw_ob_cmd_createsubpropertysamelevel' => 'Eigenschap op hetzelfde niveau toevoegen',
	'smw_ob_cmd_renameproperty' => 'Hernoemen',
	'smw_ob_cmd_renameinstance' => 'Entiteit hernoemen',
	'smw_ob_cmd_deleteinstance' => 'Entiteit verwijderen',
	'smw_ob_cmd_addpropertytodomain' => 'Eigenschappen toevoegen aan domein: ',
	
	
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
	'smw_gard_missingannot_restricttocategory' => 'Beperken tot categorieën',
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
	
	
	/* Combined Search*/
	'smw_combined_search' => 'Gecombineerde zoekfunctie',
	'smw_cs_entities_found' => 'De volgende elementen werden in de wiki ontologie gevonden:',
	'smw_cs_attributevalues_found' => 'De volgende entiteiten bevatten eigenschapwaarden die overeenkomen met je zoekaanvraag:',
	'smw_cs_aksfor_allinstances_with_annotation' => 'Geef alle entiteiten van \'$1\' die een aantekening van \'$2\' hebben',
	'smw_cs_askfor_foundproperties_and_values' => 'Geef entiteit \'$1\' voor alle gevonden eigenschappen.',
	'smw_cs_ask'=> 'Tonen',
	'smw_cs_noresults' => 'Sorry, er zijn geen elementen in de ontologie die overeenkomen met de opgegeven zoekterm.',
	'smw_cs_searchforattributevalues' => 'Zoek eigenschapwaarden die overeenkomen met de opgegeven zoekaanvraag',
	'smw_cs_instances' => 'Artikels',
	'smw_cs_properties' => 'Eigenschappen',
	'smw_cs_values' => 'Waarden',
	'smw_cs_openpage' => 'Pagina openen',
	'smw_cs_openpage_in_ob' => 'In de ontologie-browser openen',
	'smw_cs_openpage_in_editmode' => 'Pagina aanpassen',
	'smw_cs_no_triples_found' => 'Geen triples gevonden!',
	'smw_autogen_mail' => 'Deze email werd automatisch gegenereerd. Gelieve niet te antwoorden op deze email!',

	/*Message for ImportOntologyBot*/
	'smw_gard_import_docu' => 'Importeer een OWL bestand.',
	
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

	/*Messages for ContextSensitiveHelp*/
	'contextsensitivehelp' => 'Kontext-gevoelige helpfunctie',
	'smw_contextsensitivehelp' => 'Kontext-gevoelige helpfunctie',
	'smw_csh_newquestion' => 'Dit is een nieuwe helpvraag. Klik om ze te beantwoorden!',
	'smw_csh_nohelp' => 'Er werden nog geen relevante helpvragen toegevoegd aan het systeem.',
	'smw_csh_refine_search_info' => 'Je kan je zoekfunctie verfijnen wat betreft het paginatype en/of de actie waarover je meer wil weten:',
	'smw_csh_page_type' => 'Pagina-type',
	'smw_csh_action' => 'Actie',
	'smw_csh_ns_main' => 'Main (algemene wiki pagina)',
	'smw_csh_all' => 'Alle',
	'smw_csh_search_special_help' => 'Je kan ook hulp zoeken voor de speciale features van deze wiki:',
	'smw_csh_show_special_help' => 'Zoek hulp voor:',
	'smw_csh_categories' => 'Categorieën',
	'smw_csh_properties' => 'Eigenschappen',
	/* Messages for the CSH discourse state. Do NOT edit or translate these
	 * otherwise CSH will NOT work correctly anymore
	 */
	'smw_csh_ds_ontologybrowser' => 'Ontologie-browser',
	'smw_csh_ds_queryinterface' => 'Query-interface',
	'smw_csh_ds_combinedsearch' => 'Zoek',

	/*Messages for Query Interface*/
	'queryinterface' => 'Query-interface',
	'smw_queryinterface' => 'Query-interface',
	'smw_qi_add_category' => 'Categorie toevoegen',
	'smw_qi_add_instance' => 'Entiteit toevoegen',
	'smw_qi_add_property' => 'Eigenschap toevoegen',
	'smw_qi_add' => 'Toevoegen',
	'smw_qi_confirm' => 'OK',
	'smw_qi_cancel' => 'Annuleren',
	'smw_qi_delete' => 'Verwijderen',
	'smw_qi_close' => 'Sluiten',
	'smw_qi_preview' => 'Resultaten vooraf bekijken',
	'smw_qi_no_preview' => 'Er is nog geen preview beschikbaar',
	'smw_qi_clipboard' => 'Kopiëren op het clipboard',
	'smw_qi_reset' => 'Query terugzetten',
	'smw_qi_reset_confirm' => 'Ben je zeker dat je de query terug wil zetten?',
	'smw_qi_querytree_heading' => 'Queryboom navigatie',
	'smw_qi_main_query_name' => 'Main',
	'smw_qi_layout_manager' => 'Querylayout manager',
	'smw_qi_table_column_preview' => 'Tabelkolom preview',
	'smw_qi_article_title' => 'Artikel titel',
	'smw_qi_load' => 'Query laden',
	'smw_qi_save' => 'Query opslaan',
	'smw_qi_close_preview' => 'Preview sluiten',
	'smw_qi_querySaved' => 'Nieuwe query opgeslaan door de query-interface',
	'smw_qi_exportXLS' => 'Resultaten exporteren naar Excel',
	'smw_qi_showAsk' => 'Volledige query tonen',
	'smw_qi_ask' => '&lt;ask&gt; syntax',
	'smw_qi_parserask' => '{{#ask syntax',

	/*Tooltips for Query Interface*/
	'smw_qi_tt_addCategory' => 'Bij het toevoegen van een categorie, worden enkel artikels van deze categorie toegevoegd',
	'smw_qi_tt_addInstance' => 'Bij het toevoegen van een entiteit, wordt slechts één enkel artikel toegevoegd',
	'smw_qi_tt_addProperty' => 'Bij het toevoegen van een eigenschap, kan je ofwel alle resultaten tonen, ofwel specifieke waarden zoeken',
	'smw_qi_tt_tcp' => 'The tabelkolom preview geeft een preview van de kolommen die in de resulterende tabel voorkomen',
	'smw_qi_tt_qlm' => 'The querylayout manager laat toe om de layout van de queryresultaten te definiëren',
	'smw_qi_tt_preview' => 'Geeft een volledige preview van de queryresultaten, inclusief de layout settings',
	'smw_qi_tt_clipboard' => 'Kopieert je query tekst op het clipboard zodat het gemakkelijk in een artikel toegevoegd kan worden',
	'smw_qi_tt_showAsk' => 'Dit geeft de volledige ask query uit',
	'smw_qi_tt_reset' => 'Zet de volledige query terug',
	'smw_qi_tt_format' => 'Output formaat van je query',
	'smw_qi_tt_link' => 'Definieert welke delen van de resultatentabel zullen verschijnen als links',
	'smw_qi_tt_intro' => 'Tekst die vooraan aan de queryresultaten toegevoegd wordt',
	'smw_qi_tt_sort' => 'Kolom die zal gebruikt worden om te sorteren',
	'smw_qi_tt_limit' => 'Maximum aantal getoonde resultaten',
	'smw_qi_tt_mainlabel' => 'Naam van de eerste kolom',
	'smw_qi_tt_order' => 'Stijgende of dalende volgorde',
	'smw_qi_tt_headers' => 'De kop van de tabel tonen of niet',
	'smw_qi_tt_default' => 'Tekst die getoond wordt indien er geen resultaten zijn',
	
	/* Annotation */
 	'smw_annotation_tab' => 'aantekeningen maken',
	'smw_annotating'     => '$1 aantekenen',

 	
 	/* Refactor preview */
 	'refactorstatistics' => 'Refactor statistieken',
 	'smw_ob_link_stats' => 'Refactor statistieken openen',
 	
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
 	'smw_findwork_undefinedproperty' => 'Ongedefinieerde eigenschappen',
 	'smw_findwork_lowratedannotations' => 'Pagina´s met laag beoordeelde aantekeningen',
 	
 	
 	/* Gardening Issue Highlighting in Inline Queries */
	'smw_iqgi_missing' => 'ontbrekend',
	'smw_iqgi_wrongunit' => 'verkeerde eenheid',
	
	/* ACL */
	'acl' => 'Access Control Lists',
	'acl_welcome' => 'This special page allows to configure the Access Control Lists. They are used to restrict access to the wiki.',
	'smw_acl_rules' => 'Rules',
	'smw_acl_up' => 'up',
	'smw_acl_down' => 'down',
	'smw_acl_groups' => 'Groups',
	'smw_acl_namespaces' => 'Namespaces',
	'smw_acl_actions' => 'Actions',
	'smw_acl_permission' => 'Permission',
	'smw_acl_whitelist' => 'Whitelist (comma separated with namespace)',
	'smw_acl_superusers' => 'Superusers (comma separated)',
	'smw_acl_update' => 'Update rules',
	'smw_acl_remove' => 'Remove rule',
	'smw_acl_newrule' => 'New rule',
	'smw_acl_addrule' => 'Add rule',

	'smw_acl_*' => '*',
	'smw_acl_read' => 'read',
	'smw_acl_edit' => 'edit',
	'smw_acl_create' => 'create',
	'smw_acl_move' => 'move',
	'smw_acl_permit' => 'permit',
	'smw_acl_deny' => 'deny',
	
	/* Messages of the Thesaurus Import */
	'smw_ti_succ_connected' => 'Successfully connected to "$1".',
	'smw_ti_class_not_found' => 'Class "$1" not found.',
	'smw_ti_no_tl_module_spec' => 'Could not find specification for TL module with ID "$1".',
	'smw_ti_xml_error' => 'XML error: $1 at line $2',
	'smw_ti_filename'  => 'Filename:',
	'smw_ti_fileerror' => 'The file "$1" does not exist or is empty.',
	'smw_ti_no_article_names' => 'There are no article names in the specified data source.',
	'smw_ti_termimport' => 'Import vocabulary',
	'termimport' => 'Import vocabulary',
	'smw_ti_botstarted' => 'The bot for the import of vocabulary was successfully started.',
	'smw_ti_botnotstarted' => 'The bot for the import of vocabulary could not be started.',
	'smw_ti_couldnotwritesettings' => 'Could not write the settings for the vocabulary import bot.',
	'smw_ti_missing_articlename' => 'An article can not be created as the "articleName" is missing in the term\'s description.',
	'smw_ti_invalid_articlename' => 'The article name "$1" is invalid.',
	'smw_ti_articleNotUpdated' => 'The existing article "$1" was not overwritten with a new version.',
	'smw_ti_creationComment' => 'This article was created/updated by the vocabulary import framework.',
	'smw_ti_creationFailed' => 'The article "$1" could not be created or updated.',
	'smw_ti_missing_mp' => 'The mapping policy is missing.',
	'smw_ti_import_error' => 'Import error',
	'smw_ti_added_article' => '$1 was added to the wiki.',
	'smw_ti_updated_article' => '$1 was updated.',
	'smw_ti_import_errors' => 'Some terms were not properly imported. Please see the Gardening Log!',
	'smw_ti_import_successful' => 'All terms were successfully imported.',

	'smw_gardissue_ti_class_added_article' => 'Imported articles',
	'smw_gardissue_ti_class_updated_article' => 'Updated articles',
	'smw_gardissue_ti_class_system_error' => 'Import system errors',
	'smw_gardissue_ti_class_update_skipped' => 'Skipped updates',

	/* Messages for the wiki web services */
	'smw_wws_articles_header' => 'Pages using the web service "$1"',
	'smw_wws_properties_header' => 'Properties that are set by "$1"',
	'smw_wws_articlecount' => '<p>Showing $1 pages using this web service.</p>',
	'smw_wws_propertyarticlecount' => '<p>Showing $1 properties that get their value from this web service.</p>',
	'smw_wws_invalid_wwsd' => 'The Wiki Web Service Definition is invalid or does not exist.',
	'smw_wws_wwsd_element_missing' => 'The element "$1" is missing in the Wiki Web Service Definition.',
	'smw_wws_wwsd_attribute_missing' => 'The attribute "$1" is missing in element "$2" of the Wiki Web Service Definition.',
	'smw_wws_too_many_wwsd_elements' => 'The element "$1" appears several times in the Wiki Web Service Definition.',
	'smw_wss_wwsd_needs_namespace' => 'Please note: Wiki web service definitions are only considered in articles with namespace "WebService"!',
	'smw_wws_wwsd_errors' => 'The Wiki Web Service Definition is erroneous:',
	'smw_wws_invalid_protocol' => 'The protocol specified in the Wiki Web Service Definition is not supported.',
	'smw_wws_invalid_operation' => 'The operation "$1" is not provided by the web service.',
	'smw_wws_parameter_without_name' => 'A parameter of the Wiki Web Service Definition has no name.',
	'smw_wws_parameter_without_path' => 'The attribute "path" of the parameter "$1" is missing.',
	'smw_wws_duplicate_parameter' => 'The parameter "$1" appears several times.',
	'smw_wwsd_undefined_param' => 'The operation needs the parameter "$1". Please define an alias.',
	'smw_wwsd_obsolete_param' => 'The parameter "$1" is defined but not used by the operation. You can remove it.',
	'smw_wwsd_overflow' => 'The structure "$1" may be continued endlessly. Parameters of this type are not supported by the Wiki Web Service Extension.',
	'smw_wws_result_without_name' => 'A result of the Wiki Web Service Definition has no name.',
	'smw_wws_result_part_without_name' => 'The result "$1" contains a part without name.',
	'smw_wws_result_part_without_path' => 'The attribute "path" of the part "$1" of the result "$2" is missing.',
	'smw_wws_duplicate_result_part' => 'The part "$1" appears several times in the result "$2".',
	'smw_wws_duplicate_result' => 'The result "$1" appears several times.',
	'smw_wwsd_undefined_result' => 'The path of the result "$1" can not be found in the result of the service.',


);


protected $smwSpecialProperties = array(
	//always start upper-case
	SMW_SP_CONVERSION_FACTOR_SI => 'Komt overeen met SI'
);


var $smwSpecialSchemaProperties = array (
	SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT => 'Heeft domein en waardenbereik',
	SMW_SSP_HAS_MAX_CARD => 'Heeft max kardinaliteit',
	SMW_SSP_HAS_MIN_CARD => 'Heeft min kardinaliteit',
	SMW_SSP_IS_INVERSE_OF => 'Is het omgekeerde van',
	SMW_SSP_IS_EQUAL_TO => 'Is gelijk aan'
	);

var $smwSpecialCategories = array (
	SMW_SC_TRANSITIVE_RELATIONS => 'Transitieve eigenschappen',
	SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrische eigenschappen'
);

var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Chemische formule',
	'smw_hdt_chemical_equation' => 'Chemische gelijkheid',
	'smw_hdt_mathematical_equation' => 'Wiskundige gelijkheid',
);

protected $smwHaloNamespaces = array(
	SMW_NS_WEB_SERVICE       => 'WebService',
	SMW_NS_WEB_SERVICE_TALK  => 'WebService_talk'
);

protected $smwHaloNamespaceAliases = array(
	'WebService'       => SMW_NS_WEB_SERVICE,
	'WebService_talk'  => SMW_NS_WEB_SERVICE_TALK 
);

	/**
	 * Function that returns the namespace identifiers.
	 */
	public function getNamespaceArray() {
		return array(
			SMW_NS_RELATION       => 'Relatie',
			SMW_NS_RELATION_TALK  => 'Relatie_talk',
			SMW_NS_PROPERTY       => 'Eigenschap',
			SMW_NS_PROPERTY_TALK  => 'Eigenschap_talk',
			SMW_NS_TYPE           => 'Type',
			SMW_NS_TYPE_TALK      => 'Type_talk'
		);
	}


}


