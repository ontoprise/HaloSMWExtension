<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
*  @ingroup SMWHaloLanguage
 * @author Patrick Barret
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageFr extends SMW_HaloLanguage {

	protected $smwContentMessages = array(
    
	'smw_derived_property'  => 'Ceci est un attribut dérivé.',
	'smw_sparql_disabled'=> 'Aucun support SPARQL n\'est actif.',
	'smw_viewinOB' => 'Ouvrir dans l\'explorateur de données',
    'smw_wysiwyg' => 'Ce que vous voyez est ce que vous obtenez',
	'smw_att_head' => 'Valeurs de l\'attribut',
	'smw_rel_head' => 'Relations aux autres pages',
	'smw_predefined_props' => 'Ceci est l\'attribut prédéfini "$1"',
	'smw_predefined_cats' => 'Ceci est la catégorie prédéfinie "$1"',

	'smw_noattribspecial' => 'L\'attribut spécial "$1" n\'est pas un attribut (utilisez "::" à la place de ":=").',
	'smw_notype' => 'Aucun type n\'a été défini pour l\'attribut.',
	/*Messages for Autocompletion*/
	'tog-autotriggering' => 'Auto-complétion déclanché manuellement',
    'smw_ac_typehint'=> 'Type: $1',
    'smw_ac_typerangehint'=> 'Type: $1 | Champ de valeurs: $2',
	'smw_ac_datetime_proposal'=>'<mois> <jour>, <année>|<année>-<mois>-<jour>',
	'smw_ac_geocoord_proposal'=>'<latitude>° N, <longitude>° W|<latitude>, <longitude>',
	'smw_ac_email_proposal'=>'somebody@somewhere.com',
	'smw_ac_temperature_proposal'=>'<nombre> K, <nombre> °C, <nombre> °F, <nombre> °R',
	'smw_ac_telephone_proposal'=>'tel:+1-201-555-0123',
	'smw_ac_category_has_icon' => 'La catégorie a l\'icône',
	'smw_ac_tls' => 'Liste des types',

	// Messages for SI unit parsing
	'smw_no_si_unit' => 'Aucune unité n\'a été spécifiée dans la représentation du SI. ',
	'smw_too_many_slashes' => 'Trop de slashs dans la représentation du SI. ',
	'smw_too_many_asterisks' => '"$1" contient plusieurs * à la suite. ',
	'smw_denominator_is_1' => "Le dénominateur ne doit pas être 1.",
	'smw_no_si_unit_remains' => "Il ne reste aucune unité du SI après l\'optimisation.",
	'smw_invalid_format_of_si_unit' => 'Format invalide pour l\'unité du SI : $1 ',
	// Messages for the chemistry parsers
	'smw_not_a_chem_element' => '$1" n\'est pas un élément chimique.',
	'smw_no_molecule' => 'Il n\'y a aucune molécule dans la formule chimique "$1".',
	'smw_chem_unmatched_brackets' => 'Le nombre de crochets ouverts ne correspond pas avec le nombre de crochets fermés dans "$1".',
	'smw_chem_syntax_error' => 'Erreur de syntaxe dans la formule chimique "$1".',
	'smw_no_chemical_equation' => '"$1" n\'est pas une équation chimique.',
	'smw_no_alternating_formula' => 'Il y a un opérateur inutile ou un opérateur manquant dans "$1".',
	'smw_too_many_elems_for_isotope' => 'Un seul élément peut être donné pour un isotope. Une molécule a été fournie à la place : "$1".',
	// Messages for attribute pages
	'smw_attribute_has_type' => 'Cet attribut a comme type de donnée ',
	// Messages for help
	'smw_help_askown' => 'Posez vos propres questions',
	'smw_help_askownttip' => 'Ajoutez vos propres questions aux pages d\'aide du wiki où d\'autres utilisateurs pourront y répondre',
	'smw_help_pageexists' => "Cette question est déjà présente dans notre système d'aide.\nCliquez sur 'plus' pour voir toutes les questions.",
	'smw_help_error' => "Une erreur semble s'être produite.\nVotre question ne peut pas être ajoutée à notre système. Veuillez nous en excuser.",
	'smw_help_question_added' => "Votre question a été ajoutée à notre système d'aide\net d'autres utilisateurs du wiki peuvent dès à présent y répondre.",
    // Messages for CSH
	'smw_csh_icon_tooltip' => 'Cliquez ici si vous avez besoin d\'aide ou si vous souhaitez envoyer un feedback à l\'équipe de developpeurs SMW+.'
	);


	protected $smwUserMessages = array(
	'specialpages-group-smwplus_group' => 'Sémantique Mediawiki+',
	'smw_devel_warning' => 'Cette caractéristique est actuellement en développement, et peut ne pas être complètement fonctionnelle. Veuillez sauvegarder vos données avant de l\'utiliser.',
	// Messages for pages of types, relations, and attributes

	'smw_relation_header' => 'Ces pages utilisent l\'attribut "$1"',
	'smw_subproperty_header' => 'Sous-attribut de "$1"',
	'smw_subpropertyarticlecount' => '<p>Affichant les sous-attributs de $1.</p>',

	// Messages for category pages
	'smw_category_schemainfo' => 'Schéma d\'informations pour la catégorie "$1"',
	'smw_category_properties' => 'Attributs',
	'smw_category_properties_range' => 'Attributs dont le champ de valeurs est "$1"',

	'smw_category_askforallinstances' => 'Demande toutes les instances de "$1" et toutes les instances de ses sous-catégories',
	'smw_category_queries' => 'Requêtes pour les catégories',

	'smw_category_nrna' => 'Pages contenant un domaine mal assigné "$1".',
	'smw_category_nrna_expl' => 'Ces pages contiennent une indication de domaine mais ne sont pas un attribut.',
	'smw_category_nrna_range' => 'Pages contenant un champ de valeurs mal assigné "$1".',
	'smw_category_nrna_range_expl' => 'Ces pages contiennent une indication de champ de valeurs mais ne sont pas un attribut.',


	'smw_exportrdf_all' => 'Exporter toutes les données sémantiques',

	// Messages for Search Triple Special
	'searchtriple' => 'Recherche sémantique simple', //name of this special
	'smw_searchtriple_docu' => "<p>Remplissez respectivement soit la ligne du haut, ou celle du bas, du formulaire pour effectuer une recherche de relations, ou d'attributs. Certains champs peuvent être laissés vides afin d'obtenir plus de résultats. Cependant, si une valeur d'attribut est donnée, le nom de l'attribut doit être spécifié. Comme d'habitude, les valeurs d'attribut peuvent contenir une unité de mesure.</p>\n\n<p>Sachez qu'il vous faut cliquer sur le bouton droit pour obtenir des résultats. En cliquant uniquement sur <i>Return</i>, la recherche désirée ne sera pas effectuée.</p>",
	'smw_searchtriple_subject' => 'Sujet de la page:',
	'smw_searchtriple_relation' => 'Nom de la relation:',
	'smw_searchtriple_attribute' => 'Nom de l\'attribut:',
	'smw_searchtriple_object' => 'But de la page:',
	'smw_searchtriple_attvalue' => 'Valeur de l\'attribut:',
	'smw_searchtriple_searchrel' => 'Recherche de relations',
	'smw_searchtriple_searchatt' => 'Recherche d\'attributs',
	'smw_searchtriple_resultrel' => 'Résultat de la recherche (relations)',
	'smw_searchtriple_resultatt' => 'Résultat de la recherche (attributs)',

	// Messages for Relations Special
	'relations' => 'Relations',
	'smw_relations_docu' => 'Les relations suivantes existent dans le wiki.',
	// Messages for WantedRelations Special
	'wantedrelations' => 'Relations recherchées',
	'smw_wanted_relations' => 'Les relations suivantes ne possèdent pas encore de pages explicatives, bien qu\'elles soient déjà utilisées pour décrire d\'autres pages.',
	// Messages for Properties Special
	'properties' => 'Attributs',
	'smw_properties_docu' => 'Les attributs suivants existent dans le wiki.',
	'smw_attr_type_join' => ' avec $1',
	'smw_properties_sortalpha' => 'Trier par ordre alphabétique',
	'smw_properties_sortmoddate' => 'Trier par date de modification',
	'smw_properties_sorttyperange' => 'Trier par type/champ de valeurs',

	'smw_properties_sortdatatype' => 'Attributs type de données',
	'smw_properties_sortwikipage' => 'Attributs page',
	'smw_properties_sortnary' => 'Attributs enregistrement',
	// Messages for Unused Relations Special
	'unusedrelations' => 'Relations inutilisées',
	'smw_unusedrelations_docu' => 'Les pages de relations suivantes existent, bien qu\'aucune autre page ne les utilise.',
	// Messages for Unused Attributes Special
	'unusedattributes' => 'Attributs inutilisés',
	'smw_unusedattributes_docu' => 'Les pages d\'attributs suivantes existent, bien qu\'aucune autre page ne les utilise.',


	/*Messages for DataExplorer*/
	'dataexplorer' => 'Explorateur de données',
	'smw_ac_hint' => 'Appuyez sur les touches Ctrl+Alt+Espace pour utiliser l\'auto-complétion. (Ctrl+Espace sous IE)',
	'smw_ob_categoryTree' => 'Arborescence Catégories',
	'smw_ob_attributeTree' => 'Arborescence Attributs',

	'smw_ob_instanceList' => 'Instances',
       
	'smw_ob_att' => 'Attributs',
	'smw_ob_relattValues' => 'Valeurs',
	'smw_ob_relattRangeType' => 'Type/Champ de valeurs',
	'smw_ob_filter' => 'Filtre',
	'smw_ob_filterbrowsing' => 'Rechercher',
	'smw_ob_reset' => 'Remise à zéro',
	'smw_ob_cardinality' => 'Cardinalité',
	'smw_ob_transsym' => 'Transitivité/Symétrie',
	'smw_ob_footer' => '',
	'smw_ob_no_categories' => 'Aucune catégorie disponible.',
	'smw_ob_no_instances' => 'Aucune instance disponible.',
	'smw_ob_no_attributes' => 'Aucun attribut disponible.',
	'smw_ob_no_relations' => 'Aucune relation disponible.',
	'smw_ob_no_annotations' => 'Aucune annotation disponible.',
	'smw_ob_no_properties' => 'Aucun attribut disponible.',
	'smw_ob_help' => 'L\'explorateur de données vous permet de parcourir les ontologies afin de trouver et d\'identifier
facilement les pages dans le wiki. Utilisez le mécanisme de filtre en haut à gauche
pour chercher des entités spécifiques dans l\'ontologie, et les filtres au bas de chaque
colonnes pour limiter les résultats obtenus.
Initialement, la navigation s\'effectue de gauche à droite. Il vous est possible de faire basculer ce sens de navigation 
en cliquant sur les grosses flèches situées entre les colonnes.',
	'smw_ob_undefined_type' => '*champ de valeurs indéfini*',
	'smw_ob_hideinstances' => 'Cacher les instances',
    'smw_ob_onlyDirect' => 'afficher les attributs hérités',
	'smw_ob_onlyAssertedCategories' => 'afficher les catégories déjà utilisées',
	'smw_ob_showRange' => 'Afficher les attributs ayant une catégorie sélectionnée comme champ de valeurs',
	'smw_ob_hasnumofsubcategories' => 'Nombre de sous-catégories',
	'smw_ob_hasnumofinstances' => 'Nombre d\'instances',
	'smw_ob_hasnumofproperties' => 'Nombre d\'attributs',
	'smw_ob_hasnumofpropusages' => 'L\'attribut est annotée $1 fois',
	'smw_ob_hasnumoftargets' => 'L\'instance est reliée $1 fois.',
	'smw_ob_hasnumoftempuages' => 'Le modèle est utilisé $1 fois',
    'smw_ob_invalidtitle' => '!!!Titre invalide!!!',

	/* Commands for Data Explorer */
	'smw_ob_cmd_createsubcategory' => 'Créer une sous-catégorie',
	'smw_ob_cmd_createsubcategorysamelevel' => 'Créer une catégorie',
	'smw_ob_cmd_editcategory' => 'Modifier la catégorie',
	'smw_ob_cmd_renamecategory' => 'Renommer',
	'smw_ob_cmd_createsubproperty' => 'Créer un sous-attribut',
	'smw_ob_cmd_editproperty' => 'Modifier l\'attribut',
	'smw_ob_cmd_createsubpropertysamelevel' => 'Créer un attribut',
	'smw_ob_cmd_renameproperty' => 'Renommer',
	'smw_ob_cmd_renameinstance' => 'Renommer',
	'smw_ob_cmd_editinstance'  => 'Modifier l\'instance',
	'smw_ob_cmd_deleteinstance' => 'Supprimer l\'instance',
	'smw_ob_cmd_createinstance' => 'Créer une instance',
	'smw_ob_cmd_addpropertytodomain' => 'Créer un attribut dans: ',
	
	/* Advanced options in the Data Explorer */
	'smw_ob_source_wiki' => "-Wiki- (tous les bundles)" ,
	'smw_ob_advanced_options' => "Options avancées" ,
	'smw_ob_select_datasource' => "Sélectionnez la source de données à parcourir:" ,
	'smw_ob_select_bundle' => "Sélectionnez le bundle à parcourir:" ,
	'smw_ob_select_multiple' => "Pour sélectionner <b>plusieurs</b> sources de données, maintenez la touche <b>CTRL</b> appuyée tout en sélectionant des éléments avec un <b>clic de souris</b>.",
	'smw_ob_ts_not_connected' => "Aucun TripleStore trouvé. Veuillez demander à votre administrateur wiki !",
	

	/* Combined Search*/
	'smw_combined_search' => 'Recherche combinée',
	'smw_cs_entities_found' => 'Les entités suivantes ont été trouvées dans l\'ontologie du wiki:',
	'smw_cs_attributevalues_found' => 'Les instances suivantes contiennent des valeurs d\'attributs correspondants à votre recherche:',
	'smw_cs_aksfor_allinstances_with_annotation' => 'Demandez toutes les instances de \'$1\' qui ont une annotation de \'$2\'',
	'smw_cs_askfor_foundproperties_and_values' => 'Demandez l\'intance \'$1\' pour tous les attributs trouvés.',
	'smw_cs_ask'=> 'Afficher',
	'smw_cs_noresults' => 'Désolé, aucune entité dans l\'ontologie ne correspond aux termes de votre recherche.',
	'smw_cs_searchforattributevalues' => 'Rechercher des valeurs d\'attributs qui correspondent à votre recherche',
	'smw_cs_instances' => 'Articles',
	'smw_cs_properties' => 'Attributs',
	'smw_cs_values' => 'Valeurs',
	'smw_cs_openpage' => 'Ouvrir la page',
	'smw_cs_openpage_in_ob' => 'Ouvrir dans l\'explorateur de données',
	'smw_cs_openpage_in_editmode' => 'Modifier la page',
	'smw_cs_no_triples_found' => 'Aucun <em>triplet</em> n\'a été trouvé !',

	'smw_autogen_mail' => 'Cet email a été généré automatiquement. Veulliez ne pas y répondre.',

	

	/*Messages for ContextSensitiveHelp*/
	'contextsensitivehelp' => 'Aide sensible au contexte',
	'smw_contextsensitivehelp' => 'Aide sensible au contexte',
	'smw_csh_newquestion' => 'Ceci est une nouvelle question d\'aide. Cliquez pour y répondre !',
	'smw_csh_nohelp' => 'Aucune question d\'aide pertinente n\'a encore été ajoutée au système.',
	'smw_csh_refine_search_info' => 'Il vous est possible d\'affiner votre recherche selon le type de page et/ou l\'action sur lequel/laquelle vous désirez avoir plus de détails:',
	'smw_csh_page_type' => 'Type de page',
	'smw_csh_action' => 'Action',
	'smw_csh_ns_main' => 'Principal (page wiki normal)',
	'smw_csh_all' => 'TOUT',
	'smw_csh_search_special_help' => 'Vous pouvez aussi chercher de l\'aide sur les caractéristiques spéciales de ce wiki:',
	'smw_csh_show_special_help' => 'Rechercher de l\'aide sur:',
	'smw_csh_categories' => 'Catégories',
	'smw_csh_properties' => 'Attributs',
	'smw_csh_mediawiki' => 'Aide MediaWiki',
	/* Messages for the CSH discourse state. Do NOT edit or translate these
	 * otherwise CSH will NOT work correctly anymore
	 */
	'smw_csh_ds_ontologybrowser' => 'Explorateur de données',
	'smw_csh_ds_queryinterface' => 'Interface de requêtes',
	'smw_csh_ds_combinedsearch' => 'Recherche',

	/*Messages for Query Interface*/
	'queryinterface' => 'Interface de requêtes',
	'smw_queryinterface' => 'Interface de requêtes',
	'smw_qi_add_category' => 'Ajouter une catégorie',
	'smw_qi_add_instance' => 'Ajouter une instance',
	'smw_qi_add_property' => 'Ajouter un attribut',
	'smw_qi_add' => 'Ajouter',
	'smw_qi_confirm' => 'OK',
	'smw_qi_cancel' => 'Annuler',
	'smw_qi_delete' => 'Supprimer',
	'smw_qi_close' => 'Fermer',
    'smw_qi_update' => 'Actualiser',
    'smw_qi_discard_changes' => 'Ignorer les modifications',
	'smw_qi_usetriplestore' => 'Inclure les résultats déduit via TripleStore',
	'smw_qi_preview' => 'Aperçu du résultat',
    'smw_qi_fullpreview' => 'Afficher l\'intégralité du résultat',
	'smw_qi_no_preview' => 'Aucun aperçu encore disponible',
	'smw_qi_clipboard' => 'Copier dans le presse-papier',
	'smw_qi_reset' => 'Réinitialiser la requête',
	'smw_qi_reset_confirm' => 'Voulez-vous vraiment réinitialiser votre requête ?',
	'smw_qi_querytree_heading' => 'Navigation dans l\'arborescence des requêtes',
	'smw_qi_main_query_name' => 'Principal',
    'smw_qi_section_option' => 'Options de la requête',
    'smw_qi_section_definition' => 'Définition de la requête',
    'smw_qi_section_result' => 'Résultat',
	'smw_qi_preview_result' => 'Aperçu du résultat',	
	'smw_qi_layout_manager' => 'Format du résultat',
	'smw_qi_table_column_preview' => 'Aperçu des colonnes du tableau',
	'smw_qi_article_title' => 'Titre de l\'article',
	'smw_qi_load' => 'Charger la requête',
	'smw_qi_save' => 'Sauvegarder la requête',
	'smw_qi_close_preview' => 'Fermer l\'aperçu',
	'smw_qi_querySaved' => 'Nouvelle requête sauvegardée par l\'interface de requêtes',
	'smw_qi_exportXLS' => 'Exporter le résultat dans Excel',
	'smw_qi_showAsk' => 'Afficher l\'intégralité de la requête',
	'smw_qi_ask' => 'Syntaxe lt;ask&gt;',
	'smw_qi_parserask' => '{{#ask syntaxe',
    'smw_qi_queryastree' => 'Requête sous forme d\'arborescence',
    'smw_qi_queryastext' => 'Requête sous forme de texte',
    'smw_qi_querysource' => 'Requête sous forme de code',
    'smw_qi_queryname' => 'Nom de la requête',
    'smw_qi_printout_err1' => 'Le format choisi pour la requête a besoin d\'au moins un attribut supplémentaire que ce qui est affiché dans le résultat.',
    'smw_qi_printout_err2' => 'Le format choisi pour la requête a besoin d\'au moins un attribut de type date que ce qui est affiché dans le résultat.',
    'smw_qi_printout_err3' => 'Le format choisi pour la requête a besoin d\'au moins un attribut de type numérique que ce qui est affiché dans le résultat.',
    'smw_qi_printout_err4' => 'Votre requête n\'a donné aucun résultat.',
    'smw_qi_printout_err4_lod' => 'Veuillez vérifier si vous avez bien sélectionné la source de données correctes.',
	'smw_qi_printout_notavailable' => 'Le résultat de cette requête d\'impression ne peut être affichée dans l\'interface de requêtes.',
    'smw_qi_datasource_select_header' => 'Sélectionnez la source de données à interroger (maintenez la touche CTRL appuyée pour sélectionner plusieurs éléments)',
    'smw_qi_showdatarating' => 'Activer l\évaluation des utilisateurs',
    'smw_qi_showmetadata' => 'Afficher les méta-informations des données',
    'smw_qi_showdatasource' => 'Afficher uniquement les informations sur la source des données',
    'smw_qi_maintab_query' => 'Créer une requête',
    'smw_qi_maintab_load' => 'Chargement d\'une requête',
    'smw_qi_load_criteria' => 'Rechercher ou Trouver des requêtes (existantes) avec la condition suivante:',
    'smw_qi_load_selection_*' => 'Contenu de la requête',
    'smw_qi_load_selection_i' => 'Nom de l\'article',
    'smw_qi_load_selection_q' => 'Nom de la requête',
    'smw_qi_load_selection_p' => 'Attribut utilisé',
    'smw_qi_load_selection_c' => 'Catégorie utilisée',
    'smw_qi_load_selection_r' => 'Requête d\'impression utilisée',
    'smw_qi_load_selection_s' => 'Impression utilisée',
    'smw_qi_button_search' => 'Rechercher',
    'smw_qi_button_load' => 'Chargement de la requête sélectionnée',
    'smw_qi_queryloaded_dlg' => 'Votre requête a été chargé dans l\'interface de requêtes',
    'smw_qi_link_reset_search' => 'Réinitialiser la recherche',
    'smw_qi_loader_result' => 'Résultat',
    'smw_qi_loader_qname' => 'Nom-Requête',
    'smw_qi_loader_qprinter' => 'Format du résultat',
    'smw_qi_loader_qpage' => 'Utilisé dans l\'article',
    'smw_qi_tpee_header' => 'Sélectionnez la politique de confiance pour le résultat de votre requête',
    'smw_qi_tpee_none' => 'Ne pas utiliser de politique de confiance',
    'smw_qi_dstpee_selector_0' => 'Sélectionnez par source de données',
    'smw_qi_dstpee_selector_1' => 'Sélectionnez par politique de confiance',
      'smw_qi_switch_to_sparql' => 'Passer à SPARQL',
      'smw_qi_add_subject' => 'Ajouter un sujet',
      'smw_qi_category_name' => 'Nom Catégorie',
      'smw_qi_add_another_category' => 'Ajouter une autre catégorie (OU)',
      'smw_qi_subject_name' => 'Nom Sujet',
      'smw_qi_column_label' => 'Etiquette Colonne',
    'smw_qi_add_and_filter' => 'Ajouter un nouveau filtre',
      'smw_qi_filters' => 'Filtres',
      'smw_qi_show_in_results' => 'Afficher dans le résultat',
      'smw_qi_property_name' => 'Nom Attribut',
      'smw_qi_value_must_be_set' => 'La valeur doit être définie',
      'smw_qi_value_name' => 'Nom Valeur',  

	/*Tooltips for Query Interface*/
	'smw_qi_tt_addCategory' => 'En ajoutant une catégorie, seuls les articles de cette catégorie sont inclus',
	'smw_qi_tt_addInstance' => 'En ajoutant une instance, seul un unique article est inclu',
	'smw_qi_tt_addProperty' => 'En ajoutant un attribut, il vous est possible soit de lister tous les résultats, soit de chercher des valeurs spécifiques',
	'smw_qi_tt_tcp' => 'L\'aperçu des colonnes du tableau vous permet de visualiser les colonnes qui vont apparaître dans le tableau des résultats',
	'smw_qi_tt_prp' => 'L\'aperçu du résultat affiche immédiatement le résultat de la requête',	
	'smw_qi_tt_qlm' => 'Le gestionnaire de mise en page des requêtes vous permet de définir la mise en page des résultats de vos requêtes',
    'smw_qi_tt_qdef' => 'Définissez votre requête',
    'smw_qi_tt_previewres' => 'Aperçu des résultats de la requête et de la mise en page de celle-ci',
    'smw_qi_tt_update' => 'Actualiser l\'aperçu des résultats de la requête',
	'smw_qi_tt_preview' => 'Affiche un aperçu des résultats de votre requête, comprenant les paramètres de mise en page',
    'smw_qi_tt_fullpreview' => 'Affiche un aperçu complet de tous les résultats de votre requête, comprenant les paramètres de mise en page',
	'smw_qi_tt_clipboard' => 'Copie le texte de votre requête dans le presse-papier afin de faciliter son insertion dans un article',
	'smw_qi_tt_showAsk' => 'Affiche l\'intégralité des résultats de la requête Ask',
	'smw_qi_tt_reset' => 'Réinitialise complétement la requête',
	'smw_qi_tt_format' => 'Format de sortie de votre requête',
	'smw_qi_tt_link' => 'Définit quelles parties du tableau de résultats apparaîtront comme des liens',
	'smw_qi_tt_intro' => 'Texte qui est ajouté devant les résultats de la requête',
    'smw_qi_tt_outro' => 'Texte qui est ajouté derrière les résultats de la requête',
	'smw_qi_tt_sort' => 'Colonne qui sera utilisée pour le tri',
	'smw_qi_tt_limit' => 'Nombre maximum de résultats affichés',
    'smw_qi_tt_offset' => 'Nombre de décalages avant de commencer l\'affichage des résultats',
	'smw_qi_tt_mainlabel' => 'Nom de la première colonne',
	'smw_qi_tt_order' => 'Ordre ascendant ou descendant',
	'smw_qi_tt_headers' => 'Afficher ou non les en-têtes du tableau',
	'smw_qi_tt_default' => 'Texte qui sera affiché s\'il n\'y a aucun résultat',
    'smw_qi_tt_treeview' => 'Afficher votre requête dans une arborescence',
    'smw_qi_tt_textview' => 'Décrire la requête en anglais stylisée',
    'smw_qi_tt_option' => 'Définir les paramètres généraux de la manière dont la requête sera exécutée',
    'smw_qi_tt_maintab_query' => 'Créer une nouvelle requête',
    'smw_qi_tt_maintab_load' => 'Charger une requête existante',
'smw_qi_tt_addSubject' => 'Ajouter Sujet',
      'smw_qi_tt_delete' => 'Supprimer le noeud sélectionné dans l\'arborescence',
      'smw_qi_tt_cancel' => 'Annuler tous les changements, retour au point de départ',

	/* Annotation */
 	'smw_annotation_tab' => 'annoter',
	'smw_annotating'     => 'Annotation de $1',
	'annotatethispage'   => 'Annoter cette page',


	/* Refactor preview */
 	'refactorstatistics' => 'Statistiques refactorisées',
 	'smw_ob_link_stats' => 'Ouvrir les statistiques refactorisées',

	


	/* Gardening Issue Highlighting in Inline Queries */
	'smw_iqgi_missing' => 'manquant',
	'smw_iqgi_wrongunit' => 'unité incorrecte',

	'smw_deletepage_nolinks' => 'Il n\'existe aucun lien vers cette page !',
    'smw_deletepage_linkstopage'=> 'Pages contenant des liens vers cette page',
    'smw_deletepage_prev' => 'Précédent',
    'smw_deletepage_next' => 'Suivant',
	
    // Triple Store Admin
    'tsa' => 'Administration TripleStore',
	'tsc_advertisment' => "'''Cette page spéciale vous aide à administrer ce wiki avec une connexion TripleStore.'''<br><br>''Vous n'avez pas de TripleStore attachés à ce wiki.''<br><br>Vous rendrez ce wiki intelligent en lui connectant un TripleStore !<br><br>Connectez les produits ontoprise '''TripleStoreConnector Basic''' (gratuit) ou '''TripleStoreConnector Professional''' conduit finalement à obtenir de meilleurs résultats de recherche et à rendre possible l'utilisation des données qui résident en dehors de ce wiki.<br><br>'''[http://smwforum.ontoprise.com/smwforum/index.php/List_of_Extensions/Triple_store_connector Cliquez ici pour lire qu'elles en seront les bénéfices et pour télécharger un TripleStore !]'''",
    'smw_tsa_welcome' => 'Cette page spéciale vous aide à administrer ce wiki avec une connexion TripleStore.',
    'smw_tsa_couldnotconnect' => 'La connection vers TripleStore est impossible.',
    'smw_tsa_notinitalized' => 'Votre wiki n\'est pas initialisé au TripleStore.',
    'smw_tsa_waitsoemtime'=> 'Veuillez patienter quelques secondes puis suivez ce lien.',
    'smw_tsa_wikiconfigured' => 'Votre wiki est correctement connecté au TripleStore à $1',
    'smw_tsa_initialize' => 'Initialiser',
	'smw_tsa_reinitialize' => 'Ré-Initialiser',
    'smw_tsa_pressthebutton' => 'Veuillez cliquer sur le bouton ci-dessous.',
    'smw_tsa_addtoconfig' => 'Veuillez ajouter les lignes suivantes dans votre LocalSettings.php et vérifiez si le connecteur TripleStore est en marche.',
	'smw_tsa_addtoconfig2' => 'Assurez-vous que le driver TripleStore est activé. Si nécessaire, modifier enableSMWHalo en',
	'smw_tsa_addtoconfig3' => 'Assurez-vous aussi que l\'URL (dernier paramètre de enableSMWHalo) est valable et qu\il ne contient pas un dièse (#).',
	'smw_tsa_addtoconfig4' => 'Si cela ne vous aide pas, Veuillez consulter l\'aide en ligne dans $1.',
    'smw_tsa_driverinfo' => 'Informations sur le driver',
    'smw_tsa_status' => 'Statut',
    'smw_tsa_rulesupport'=> 'Le driver TripleStore supporte les règles ; il vous est conseillé d\'ajouter <pre>$smwgEnableFlogicRules=true;</pre> à votre LocalSettings.php. Sinon, les règles ne fonctionneront pas.',
    'smw_tsa_norulesupport'=> 'Le driver TripleStore ne supporte pas les règles, bien qu\'elles soient activées dans le site. Veuillez supprimer <pre>$smwgEnableFlogicRules=true;</pre> de votre LocalSettings.php, afin de ne pas obtenir d\'erreurs.',
    'smw_tsa_tscinfo' => 'Informations sur le connecteur TripleStore',
	'smw_tsa_tscversion' => 'Version TSC',
	'smw_ts_notconnected' => 'TSC inaccessibles. Vérifiez le serveur: $1',
	'asktsc' => 'Ask TripleStore',
	'smw_tsc_query_not_allowed' => 'Les requêtes vide ne sont pas autorisées lors d\'interrogation TSC.',
	'smw_tsa_loadgraphs'=> 'Graphiques chargés',
	'smw_tsa_autoloadfolder'=> 'Auto-chargement du répertoire',
	'smw_tsa_tscparameters'=> 'Paramètres TSC',
	'smw_tsa_synccommands'=> 'Commandes de synchronisation',
	
	
	// SMWHaloAdmin
	'smwhaloadmin' => 'Administration SMWHalo',
	'smw_haloadmin_databaseinit' => 'Initialisation de la base de données',
	'smw_haloadmin_description' => 'Cette page spéciale vous aide lors de l\'installation et la mise à niveau de SMWHalo.', 
	'smw_haloadmin_databaseinit_description' => 'La fonction ci-dessous vous assure que votre base de données est correctement configuré. En appuyant sur "Re-Initialiser" vous mettrez à niveau le schéma de la base de données et installerez sur le wiki quelques pages requises pour l\'utilisation des données sémantiques. Alternativement, vous pouvez utiliser le script de maintenance SMW_setup.php qui se trouve dans le répertoire maintenance deSMWHalo.',
	'smw_haloadmin_ok' => 'L\'extension SMWHalo est correctement installé.',
	
	// Derived facts
	'smw_df_derived_facts_about' => 'Faits dérivés de $1',
	'smw_df_static_tab'			 => 'Faits statiques',
	'smw_df_derived_tab'		 => 'Faits dérivés',
	'smw_df_static_facts_about'  => 'Faits statiques pour cet article',
	'smw_df_derived_facts_about' => 'Faits dérivés pour cet article',
	'smw_df_loading_df'			 => 'Chargement des daits dérivés...',
	'smw_df_invalid_title'		 => 'Article invalide. Pas de faits dérivés disponibles.',
	'smw_df_no_df_found'		 => 'Aucun faits dérivés trouvés pour cet article.',
	'smw_df_tsc_advertisment'    => "''Vous n\'avez pas de TripleStore attachés à ce wiki.''\n\nVous rendrez ce wiki intelligent en lui connectant un TripleStore ! Connectez les produits ontoprise '''TripleStoreConnector Basic''' (gratuit) ou '''TripleStoreConnector Professional''' conduit finalement à obtenir de meilleurs résultats de recherche et à rendre possible l\'utilisation des données qui résident en dehors de ce wiki.\nCliquez ici pour lire qu\'elles en seront les bénéfices et pour télécharger un [http://smwforum.ontoprise.com/smwforum/index.php/List_of_Extensions/Triple_store_connector TripleStore] !",
	
	//skin
	'smw_search_this_wiki' => 'Rechercher sur ce wiki',
	'smw_last_visited' => 'Dernière visite:',
	'smw_pagecreation' => 'Créé par $1 le $2, à $3',
	'smw_start_discussion' => 'Démarrer $1',
	'more_functions' => 'Plus',
	'smw_treeviewleft' => 'Ouvrez l\'arborescence à gauche',
	'smw_treeviewright' => 'Ouvrez l\'arborescence à droite',
	
	// Geo coord data type
	'semanticmaps_lonely_unit'     => 'Pas de numéro trouvé avant le symbole "$1".', // $1 is something like �?°
	'semanticmaps_bad_latlong'     => 'Latitude et longitude ne doivent être donnée qu\'une seule fois, et avec des coordonnées valides.',
	'semanticmaps_abb_north'       => 'N',
	'semanticmaps_abb_east'        => 'E',
	'semanticmaps_abb_south'       => 'S',
	'semanticmaps_abb_west'        => 'W',
	'semanticmaps_label_latitude'  => 'Latitude:',
	'semanticmaps_label_longitude' => 'Longitude:',
	
	
	
	// Tabular Forms
	'smw_tf_paramdesc_add'		=> 'L\'utilisateur est autorisé à ajouter de nouvelles instances pour le résultat',
	'smw_tf_paramdesc_delete'	=> 'L\'utilisateur est autorisé à supprimer des instances du résultat',
	'smw_tf_paramdesc_use_silent_annotation' => "Les formulaires de tableaux utiliseront le modèle d\'annotations silencieuses pour créer de nouvelles annotations.",
	
	//Querylist Special Page
	'querylist' => "Requêtes sauvegardées",
	
	//Tabular forms
	'tabf_load_msg' => "Chargement des formulaires de tableaux.",

	'tabf_add_label' => "Ajouter une instance",
	'tabf_refresh_label' => "Actualiser",
	'tabf_save_label' => "Appliquer les modifications",
	
	'tabf_status_unchanged' => "Cette instance n'a pas encore été modifié.",
	'tabf_status_notexist_create' => "Cette instance n'existe pas. Elle sera créée.",
	'tabf_status_notexist' => "Cette instance n'existe pas.",
	'tabf_status_readprotected' => "Cette instance est protégée en lecture.",
	'tabf_status_writeprotected' => "Cette instance est protégée en écriture.",
	'tabf_status_delete' => "Cette instance sera supprimée.",
	'tabf_status_modified' => "Cette instance a été modifiée.",
	'tabf_status_saved' => "Cette instance a été enregistrée avec succès.",
	'tabf_status_pending' => "Application des changements.",
	
	'tabf_response_deleted' => "Cette instance a été supprimée entre-temps.",
	'tabf_response_modified' => "Cette instance a été modifiée entre-temps.",
	'tabf_response_readprotected' => "Cette instance a été protégée en lecture entre-temps.",
	'tabf_response_writeprotected' => "Cette instance a été protégée en écriture entre-temps.",
	'tabf_response_invalidname' => "Cette instance a un nom invalide.",
	'tabf_response_created' => "Cette instance a été créée entre-temps.",
	'tabf_response_nocreatepermission' => "Vous n'avez pas la permission de créer cette instance.",
	'tabf_response_nodeletepermission' => "Vous n'avez pas la permission de supprimer cette instance.",
	
	'tabf_update_warning' => "Des erreurs sont survenues lors de l'application des changements. Veuillez jeter un oeil à l'icône d'état.",
	
	'tabf_instancename_blank' => "Les noms d'instance ne peuvent être vide.",
	'tabf_instancename_invalid' => "'$1' n'est pas un nom d'instance valide.",
	'tabf_instancename_exists' => "'$1' existe déjà.",
	'tabf_instancename_permission_error' => "Vous n'avez pas l'autorisation de créer '$1'.",
	'tabf_annotationnamme_invalid' => "'$1' a une valeur invalide: La valeur '$2' de l'attribut '$3' n'est pas de type $4.",
	
	'tabf_lost_reason_EQ' => "est égale à '$1'",
	'tabf_lost_reason_NEQ' => "n'est pas égal à '$1'",
	'tabf_lost_reason_LEQ' => "est inférieur ou égal à '$1'",
	'tabf_lost_reason_GEQ' => "est supérieur ou égal à '$1'",
	'tabf_lost_reason_LESS' => "est inférieur à '$1'",
	'tabf_lost_reason_GRTR' => "est supérieur à '$1'",
	'tabf_lost_reason_EXISTS' => "est une valeur d'annotation valide",
	'tabf_lost_reason_introTS' => "'<span class=\"tabf_nin\">$1</span>', car aucune des valeurs de l'annotation '$2' ",
	
	'tabf_parameter_write_protected_desc' => "Annotations protégées en écriture",
	'tabf_parameter_instance_preload_desc' => "Valeur de préchargement du nom de l'instance",
	
	'tabf_ns_header_show' => "Notifications système",
	'tabf_ns_header_hide' => "Notifications système",
	'tabf_ns_warning_invalid_instance_name' => "Les changements ne peuvent pas être actuellement appliquée parce que certains nouveaux noms d'instance sont erronés:",
	'tabf_ns_warning_invalid_value' => "Les valeurs d'annotation suivantes sont invalides:",
	'tabf_ns_warning_lost_instance_otf' => "Les instances suivantes ne pourront plus être inclus dans le résultat de la requête après l'application des modifications:",
	'tabf_ns_warning_lost_instance' => "Les instances suivantes ne sont désormais plus partie du résultat de la requête",
	'tabf_ns_warning_save_error' => "Les instances suivantes n'ont pas pu être enregistrées car elles ont été modifiés par quelqu'un d'autre entre-temps.",
	'tabf_ns_warning_add_disabled' => "Le bouton 'Ajouter une instance' a été désactivée par le système. Veuillez fournir une valeur de préchargement pour les attributs suivants, marquez-les comme protégés en écriture, ou utilisez-les comme des états d'impression afin de permettre d'activer de nouveau le bouton 'Ajouter une instance':",
	'tabf_ns_warning_by_system' => "Notifications et avertissements du processeur de requêtes:",
	
	'tabf_nc_icon_title_lost_instance' => "Cette instance ne pourra plus être inclus de nouveau dans le résultat de la requête après l'application de modifications.",
	'tabf_nc_icon_title_invalid_value' => "Certaines valeurs d'annotation de cette instance sont invalides.",
	'tabf_nc_icon_title_save_error' => "Les changements de cette instance n'ont pas pu être appliqués parce qu'elle a été modifiée par quelqu'un d'autre entre-temps.",
	
	//--- fancy table result printer
	'ftrp_warning' => "Des déclarations de remplacement invalides avec des proprétés inconnes ont été trouvées:",
	);


	protected $smwSpecialProperties = array(
		//always start upper-case
		"___cfsi" => array('_siu', 'Correspond au SI'),
		"___CREA" => array('_wpg', 'Créateur'),
		"___CREADT" => array('_dat', 'Date de création'),
		"___MOD" => array('_wpg', 'Dernière modification par')
	);


	var $smwSpecialSchemaProperties = array (
	SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT => 'A pour domaine et champ de valeurs',
	SMW_SSP_HAS_DOMAIN => 'A pour domaine',
    SMW_SSP_HAS_RANGE => 'A pour champ de valeurs',
	SMW_SSP_HAS_MAX_CARD => 'A pour cardinalité max',
	SMW_SSP_HAS_MIN_CARD => 'A pour cardinalité min',
	SMW_SSP_IS_INVERSE_OF => 'Est l\'inverse de',
	SMW_SSP_IS_EQUAL_TO => 'Est égal à',
	SMW_SSP_ONTOLOGY_URI => 'URI ontologique'
	);

	var $smwSpecialCategories = array (
	SMW_SC_TRANSITIVE_RELATIONS => 'Attributs transitifs',
	SMW_SC_SYMMETRICAL_RELATIONS => 'Attributs symétriques'
	);

	var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Formule chimique',
	'smw_hdt_chemical_equation' => 'Equation chimique',
	'smw_hdt_mathematical_equation' => 'Equation mathématique',
	'smw_integration_link' => 'Lien d\'intégration'
	);

	protected $smwHaloNamespaces = array(
	);

	protected $smwHaloNamespaceAliases = array(
	);

	/**
	 * Function that returns the namespace identifiers. This is probably obsolete!
	 */
	public function getNamespaceArray() {
		return array(
		SMW_NS_RELATION       => 'Relation',
		SMW_NS_RELATION_TALK  => 'Discussion_relation',
		SMW_NS_PROPERTY       => 'Attribut',
		SMW_NS_PROPERTY_TALK  => 'Discussion_attribut',
		SMW_NS_TYPE           => 'Type', // @deprecated
		SMW_NS_TYPE_TALK      => 'Discussion_type', // @deprecated
		SMW_NS_CONCEPT        => 'Concept',
		SMW_NS_CONCEPT_TALK   => 'Discussion_concept'
		);
	}


}