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
 *  @file
*  @ingroup SMWHaloLanguage
 * @author Ontoprise
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageFr extends SMW_HaloLanguage {

    protected $smwContentMessages = array(
    
    'smw_derived_property'  => 'Ceci est une propriété dérivée.',
    'smw_sparql_disabled'=> 'Aucun support SPARQL n\'est actif.',
    'smw_viewinOB' => 'Ouvrir dans le navigateur d\'ontologies',
    'smw_wysiwyg' => 'Ce que vous voyez est ce que vous obtenez',
    'smw_att_head' => 'Valeurs de l\'attribut',
    'smw_rel_head' => 'Relations aux autres pages',
    'smw_predefined_props' => 'Ceci est la propriété prédéfinie "$1"',
    'smw_predefined_cats' => 'Ceci est la catégorie prédéfinie "$1"',

    'smw_noattribspecial' => 'La propriété spéciale "$1" n\'est pas un attribut (utilisez "::" à  la place de ":=").',
    'smw_notype' => 'Aucun type n\'a été défini pour l\'attribut.',
    /*Messages for Autocompletion*/
    'tog-autotriggering' => 'Autocomplétion déclanchée automatiquement',
    'smw_ac_typehint'=> 'Type: $1',
    'smw_ac_typerangehint'=> 'Type: $1 | Range: $2',

    // Messages for SI unit parsing
    'smw_no_si_unit' => 'Aucune unité n\'a été spécifiée dans la représentation du SI. ',
    'smw_too_many_slashes' => 'Trop de slashs dans la représentation du SI. ',
    'smw_too_many_asterisks' => '"$1" contient plusieurs * à la suite. ',
    'smw_denominator_is_1' => "Le dénominateur ne doit pas être 1.",
    'smw_no_si_unit_remains' => "Il ne reste aucune unité du SI aprà¨s l\'optimisation.",
    'smw_invalid_format_of_si_unit' => 'Format invalide pour l\'unité du SI : $1 ',
    // Messages for the chemistry parsers
    'smw_not_a_chem_element' => '"$1" n\'est pas un élément chimique.',
    'smw_no_molecule' => 'Il n\'y a aucune molécule dans la formule chimique "$1".',
    'smw_chem_unmatched_brackets' => 'Le nombre de crochets ouverts ne correspond pas avec le nombre de crochets fermés dans "$1".',
    'smw_chem_syntax_error' => 'Erreur de syntaxe dans la formule chimique "$1".',
    'smw_no_chemical_equation' => '"$1" n\'est pas une équation chimique.',
    'smw_no_alternating_formula' => '"$1" contient un opérateur inutile ou un opérateur manque dans "$1".',
    'smw_too_many_elems_for_isotope' => 'Un seul élément peut être donné pour un isotope. Une molécule a été fournie à  la place : "$1".',
    // Messages for attribute pages
    'smw_attribute_has_type' => 'Cet attribut a comme type de donnée ',
    // Messages for help
    'smw_help_askown' => 'Posez vos propres questions',
    'smw_help_askownttip' => 'Ajoutez vos propres questions aux pages d\'aide du wiki oà¹ d\'autres utilisateurs pourront y répondre',
    'smw_help_pageexists' => "Cette question est déjà  présente dans notre systà¨me d'aide.\nCliquez sur 'plus' pour voir toutes les questions.",
    'smw_help_error' => "Une erreur semble s'être produite.\nVotre question ne peut pas être ajoutée à  notre systà¨me. Veuillez nous en excuser.",
    'smw_help_question_added' => "Votre question a été ajoutée à  notre systà¨me d'aide\net d'autres utilisateurs du wiki peuvent dà¨s à  présent y répondre.",
    // Messages for CSH
    'smw_csh_icon_tooltip' => 'Click here if you need help or if you want to send feeback to the SMW+ developers.'
    );


    protected $smwUserMessages = array(
    'specialpages-group-smwplus_group' => 'Sémantique Mediawiki+',
    'smw_devel_warning' => 'Cette caractéristique est actuellement en développement, et peut ne pas être complà¨tement fonctionnelle. Veuillez sauvegarder vos données avant de l\'utiliser.',
    // Messages for pages of types, relations, and attributes

    'smw_relation_header' => 'Ces pages utilisent la propriété "$1"',
    'smw_subproperty_header' => 'Sous-propriété de "$1"',
    'smw_subpropertyarticlecount' => '<p>Affichant les sous-propriétés de $1.</p>',

    // Messages for category pages
    'smw_category_schemainfo' => 'Schéma d\'informations pour la catégorie "$1"',
    'smw_category_properties' => 'Propriétés',
    'smw_category_properties_range' => 'Propriétés dont le cahmp de valeurs est "$1"',

    'smw_category_askforallinstances' => 'Demande toutes les instances de "$1" et toutes les instances de ses sous-catégories',
    'smw_category_queries' => 'Requêtes pour les catégories',

    'smw_category_nrna' => 'Pages contenant un domaine mal assigné "$1".',
    'smw_category_nrna_expl' => 'Ces pages contiennent une indication de domaine mais ne sont pas une propriété.',
    'smw_category_nrna_range' => 'Pages contenant un champ de valeurs mal assigné "$1".',
    'smw_category_nrna_range_expl' => 'Ces pages contiennent une indication de champ de valeurs mais ne sont pas une propriété.',


    'smw_exportrdf_all' => 'Exporter toutes les données sémantiques',

    // Messages for Search Triple Special
    'searchtriple' => 'Recherche sémantique simple', //name of this special
    'smw_searchtriple_docu' => "<p>Remplir la ligne du haut, respectivement du bas, du formulaire pour effectuer une recherche de relations, respectivement une recherche d'attributs. Certains champs peuvent être laissés vides afin d'obtenir plus de résultats. Cependant, si une valeur d'attribut est donnée, le nom de l'attribut doit être spécifié. Comme d'habitude, les valeurs d'attribut peuvent contenir une unité de mesure.</p>\n\n<p>Sachez qu'il vous faut cliquer sur le bouton droit pour obtenir des résultats. En cliquant sur <i>Return</i>, la recherche désirée ne sera pas effectuée.</p>",
    'smw_searchtriple_subject' => 'Sujet de la page:',
    'smw_searchtriple_relation' => 'Nom de la relation:',
    'smw_searchtriple_attribute' => 'Nom de l\'attribut:',
    'smw_searchtriple_object' => 'But de la page:',
    'smw_searchtriple_attvalue' => 'Valeur de l\'attribut:',
    'smw_searchtriple_searchrel' => 'Recherche de relations',
    'smw_searchtriple_searchatt' => 'Recherche d\'attributs',
    'smw_searchtriple_resultrel' => 'Résultats de la recherche (relations)',
    'smw_searchtriple_resultatt' => 'Résultats de la recherche (attributs)',

    // Messages for Relations Special
    'relations' => 'Relations',
    'smw_relations_docu' => 'Les relations suivantes existent dans le wiki.',
    // Messages for WantedRelations Special
    'wantedrelations' => 'Relations recherchées',
    'smw_wanted_relations' => 'Les relations suivantes ne possà¨dent pas encore de pages explicatives, bien qu\'elles soient déjà  utilisées pour décrire d\'autres pages.',
    // Messages for Properties Special
    'properties' => 'Propriétés',
    'smw_properties_docu' => 'Les propriétés suivantes existent dans le wiki.',
    'smw_attr_type_join' => ' avec $1',
    'smw_properties_sortalpha' => 'Trier par ordre alphabétique',
    'smw_properties_sortmoddate' => 'Trier par date de modification',
    'smw_properties_sorttyperange' => 'Trier par type/champ de valeurs',

    'smw_properties_sortdatatype' => 'Propriétés du type de données',
    'smw_properties_sortwikipage' => 'Propriétés de la page wiki',
    'smw_properties_sortnary' => 'Propriétés d\'arité N',
    // Messages for Unused Relations Special
    'unusedrelations' => 'Relations inutilisées',
    'smw_unusedrelations_docu' => 'Les pages suivantes de relations existent bien qu\'aucune autre page ne les utilise.',
    // Messages for Unused Attributes Special
    'unusedattributes' => 'Attributs inutilisés',
    'smw_unusedattributes_docu' => 'Les pages suivantes d\'attributs existent bien qu\'aucune autre page ne les utilise.',


    /*Messages for OntologyBrowser*/
    'ontologybrowser' => 'Navigateur d\'ontologies',
    'smw_ac_hint' => 'Appuyez sur les touches Ctrl+Alt+Espace pour utiliser l\'auto-completion. (Ctrl+Espace sous IE)',
    'smw_ob_categoryTree' => 'Arbre des catégories',
    'smw_ob_attributeTree' => 'Arbre des propriétés',

    'smw_ob_instanceList' => 'Instances',

    'smw_ob_att' => 'Propriétés',
    'smw_ob_relattValues' => 'Valeurs',
    'smw_ob_relattRangeType' => 'Type/Champ de valeurs',
    'smw_ob_filter' => 'Filtre',
    'smw_ob_filterbrowsing' => 'Filtre de navigation',
    'smw_ob_reset' => 'Mise à  zéro',
    'smw_ob_cardinality' => 'Cardinalité',
    'smw_ob_transsym' => 'Transitivité/Symétrie',
    'smw_ob_footer' => '',
    'smw_ob_no_categories' => 'Aucune catégorie disponible.',
    'smw_ob_no_instances' => 'Aucune instance disponible.',
    'smw_ob_no_attributes' => 'Aucun attribut disponible.',
    'smw_ob_no_relations' => 'Aucune relation disponible.',
    'smw_ob_no_annotations' => 'Aucune annotation disponible.',
    'smw_ob_no_properties' => 'Aucune propriété disponible.',
    'smw_ob_help' => "Le navigateur d'ontologies vous permet de parcourir les ontologies afin de trouver et d'identifier
facilement les articles dans le wiki. Utilisez le mécanisme de filtre en haut à  gauche
pour chercher des entités spécifiques dans l'ontologie, et les filtres au bas de chaque
colonnes pour limiter les résultats obtenus.
Initialement, la navigation s'effectue de gauche à  droite. Il vous est possible de faire basculer ce sens de navigation 
en cliquant sur les grosses flà¨ches situées entre les colonnes.",
    'smw_ob_undefined_type' => '*range indéfini*',
    'smw_ob_hideinstances' => 'Cacher les instances',
    'smw_ob_onlyDirect' => 'Uniquement les propriétés directes',
    'smw_ob_showRange' => 'properties of range',
    'smw_ob_hasnumofsubcategories' => 'Nombre de sous-catégories',
    'smw_ob_hasnumofinstances' => 'Nombre d\'instances',
    'smw_ob_hasnumofproperties' => 'Nombre de propriétés',
    'smw_ob_hasnumofpropusages' => 'La propriété est annotée $1 fois',
    'smw_ob_hasnumoftargets' => 'L\'instance est reliée $1 fois.',
    'smw_ob_hasnumoftempuages' => 'Le modà¨le est utilisé $1 fois',
    'smw_ob_invalidtitle' => '!!!invalid title!!!',
    
    /* Commands for ontology browser */
    'smw_ob_cmd_createsubcategory' => 'Ajouter une sous-catégorie',
    'smw_ob_cmd_createsubcategorysamelevel' => 'Ajouter une catégorie au même niveau',
    'smw_ob_cmd_renamecategory' => 'Renommer',
    'smw_ob_cmd_createsubproperty' => 'Ajouter une sous-propriété',
    'smw_ob_cmd_createsubpropertysamelevel' => 'Ajouter une propriété au même niveau',
    'smw_ob_cmd_renameproperty' => 'Renommer',
    'smw_ob_cmd_renameinstance' => 'Renommer l\'instance',
    'smw_ob_cmd_deleteinstance' => 'Supprimer l\'instance',
    'smw_ob_cmd_addpropertytodomain' => 'Ajouter la propriété au domaine: ',

//TODO: Translate strings
	/* Advanced options in the ontology browser */
	'smw_ob_source_wiki' => "-Wiki-" ,
	'smw_ob_advanced_options' => "Advanced options" ,
	'smw_ob_select_datasource' => "Select the data source to browse:" ,
	'smw_ob_select_multiple' => "To select <b>multiple</b> data sources hold down <b>CTRL</b> and select the items with a <b>mouse click</b>.",
	'smw_ob_ts_not_connected' => "No triple store found. Please ask your wiki administrator!",
    

    /* Combined Search*/
    'smw_combined_search' => 'Recherche combinée',
    'smw_cs_entities_found' => 'Les entités suivantes ont été trouvées dans l\'ontologie du wiki:',
    'smw_cs_attributevalues_found' => 'Les instances suivantes contiennent des valeurs de propriété correspondantes à  votre recherche:',
    'smw_cs_aksfor_allinstances_with_annotation' => 'Demandez toutes les instances de \'$1\' qui ont une annotation de \'$2\'',
    'smw_cs_askfor_foundproperties_and_values' => 'Demandez l\'intance \'$1\' pour toutes les propriétés trouvées.',
    'smw_cs_ask'=> 'Afficher',
    'smw_cs_noresults' => 'Aucune entité dans l\'ontologie ne correspond aux termes de votre recherche.',
    'smw_cs_searchforattributevalues' => 'Rechercher des valeurs de propriétés qui correspondent à  votre recherche',
    'smw_cs_instances' => 'Articles',
    'smw_cs_properties' => 'Propriétés',
    'smw_cs_values' => 'Valeurs',
    'smw_cs_openpage' => 'Ouvrir la page',
    'smw_cs_openpage_in_ob' => 'Ouvrir dans le navigateur d\'ontologies',
    'smw_cs_openpage_in_editmode' => 'Editer la page',
    'smw_cs_no_triples_found' => 'Aucun triplet n\'a été trouvé!',

    'smw_autogen_mail' => 'Cet email a été généré automatiquement. Veulliez ne pas y répondre.',

    

    /*Messages for ContextSensitiveHelp*/
    'contextsensitivehelp' => 'Aide sensible au contexte',
    'smw_contextsensitivehelp' => 'Aide sensible au contexte',
    'smw_csh_newquestion' => 'Ceci est une nouvelle question d\'aide. Cliquez pour y répondre !',
    'smw_csh_nohelp' => 'Aucune question d\'aide relevante n\'a été ajoutée au systà¨me.',
    'smw_csh_refine_search_info' => 'Il vous est possible d\'affiner votre recherche selon le type de page et/ou l\'action sur lequel/laquelle vous désirez avoir plus de détails:',
    'smw_csh_page_type' => 'Type de page',
    'smw_csh_action' => 'Action',
    'smw_csh_ns_main' => 'Main (Page Wiki ordinaire)',
    'smw_csh_all' => 'TOUT',
    'smw_csh_search_special_help' => 'Il vous est aussi possible de chercher de l\'aide sur les caractéristiques spéciales de ce wiki:',
    'smw_csh_show_special_help' => 'Rechercher de l\'aide sur:',
    'smw_csh_categories' => 'Catégories',
    'smw_csh_properties' => 'Propriétés',
    'smw_csh_mediawiki' => 'Aide MediaWiki',
    /* Messages for the CSH discourse state. Do NOT edit or translate these
     * otherwise CSH will NOT work correctly anymore
     */
    'smw_csh_ds_ontologybrowser' => 'Navigateur d\'ontologies',
    'smw_csh_ds_queryinterface' => 'Interface de requêtes',
    'smw_csh_ds_combinedsearch' => 'Recherche',

    /*Messages for Query Interface*/
    'queryinterface' => 'Interface de requêtes',
    'smw_queryinterface' => 'Interface de requêtes',
    'smw_qi_add_category' => 'Ajouter une catégorie',
    'smw_qi_add_instance' => 'Ajouter une instance',
    'smw_qi_add_property' => 'Ajouter une propriété',
    'smw_qi_add' => 'Ajouter',
    'smw_qi_confirm' => 'OK',
    'smw_qi_cancel' => 'Annuler',
    'smw_qi_delete' => 'Supprimer',
    'smw_qi_close' => 'Fermer',
    'smw_qi_update' => 'Update',
    'smw_qi_discard_changes' => 'Discard changes',
    'smw_qi_preview' => 'Voir les résultats',
    'smw_qi_fullpreview' => 'Show full result',
    'smw_qi_no_preview' => 'Aucune visualisation possible',
    'smw_qi_clipboard' => 'Copier dans le presse-papier',
    'smw_qi_reset' => 'Réinitialiser la requête',
    'smw_qi_usetriplestore' => 'Utiliser triplestore',
    'smw_qi_reset_confirm' => 'àŠtes-vous sà»r de vouloir réinitialiser votre requête ?',
    'smw_qi_querytree_heading' => 'Navigation dans l\'arbre des requêtes',
    'smw_qi_main_query_name' => 'Main',
    'smw_qi_section_definition' => 'Query Definition',
    'smw_qi_section_result' => 'Result',
    'smw_qi_preview_result' => 'Aperàçu des résultats', 
    'smw_qi_layout_manager' => 'Gestionnaire de mise en page des requêtes',
    'smw_qi_table_column_preview' => 'Aperàçu des colonnes du tableau',
    'smw_qi_article_title' => 'Titre de l\'article',
    'smw_qi_load' => 'Charger la requête',
    'smw_qi_save' => 'Sauvegarder la requête',
    'smw_qi_close_preview' => 'Fermer l\'aperàçu',
    'smw_qi_querySaved' => 'Nouvelle requête sauvegardée par l\'interface des requêtes',
    'smw_qi_exportXLS' => 'Exporter les résultats dans Excel',
    'smw_qi_showAsk' => 'Afficher la requête dans son intégralité',
    'smw_qi_ask' => '&lt;demander&gt; la syntaxe',
    'smw_qi_parserask' => '{{#demander la syntaxe',
    'smw_qi_queryastree' => 'Query as tree',
    'smw_qi_queryastext' => 'Query as text',
    'smw_qi_querysource' => 'Query source',
    'smw_qi_printout_err1' => 'The selected format for the query result needs at least one additional property that is shown in the result.',
    'smw_qi_printout_err2' => 'The selected format for the query result needs at least one property of the type date that is shown in the result.',
    'smw_qi_printout_err3' => 'The selected format for the query result needs at least one property of a numeric type that is shown in the result.',
    'smw_qi_printout_err4' => 'Your query did not return any results.',
    'smw_qi_printout_notavailable' => 'The result of this query printer cannot be displayed in the query interface.',

    /*Tooltips for Query Interface*/
    'smw_qi_tt_addCategory' => 'En ajoutant une catégorie, seuls les articles de cette catégorie sont inclus',
    'smw_qi_tt_addInstance' => 'En ajoutant une instance, seul un unique article est inclu',
    'smw_qi_tt_addProperty' => 'En ajoutant une propriété, il vous est possible soit de lister tous les résultats, soit de chercher des valeurs spécifiques',
    'smw_qi_tt_tcp' => 'L\'aperàçu des colonnes du tableau vous permet de visualiser les colonnes qui vont apparaà®tre dans le tableau de résultats',
    'smw_qi_tt_prp' => 'L\'aperàçu du résultat affiche immédiatement le résultat de la requête',    
    'smw_qi_tt_qlm' => 'Le gestionnaire de mise en page des requêtes vous permet de définir la mise en page des résultats de vos requêtes',
    'smw_qi_tt_qdef' => 'Define your query',
    'smw_qi_tt_previewres' => 'Preview query results and format query',
    'smw_qi_tt_update' => 'Update the result preview of the query',
    'smw_qi_tt_preview' => 'Montre un aperàçu des résultats de vos requêtes, comprenant les configurations de mise en page',
    'smw_qi_tt_fullpreview' => 'Montre un aperàçu complet des résultats de vos requêtes, comprenant les configurations de mise en page',
    'smw_qi_tt_clipboard' => 'Copie le texte de votre requête dans le presse-papier afin de faciliter son insertion dans un article',
    'smw_qi_tt_showAsk' => 'Ceci affiche la requête effectuée',
    'smw_qi_tt_reset' => 'Réinitialise la requête entià¨re',
    'smw_qi_tt_format' => 'Format de sortie de votre requête',
    'smw_qi_tt_link' => 'Définit quelles parties du tableau de résultats apparaà®tront comme des liens',
    'smw_qi_tt_intro' => 'Texte qui est ajouté devant les résultats de la requête',
    'smw_qi_tt_outro' => 'Texte qui est ajouté derrière les résultats de la requête',
    'smw_qi_tt_sort' => 'Colonne qui va être utilisée pour le tri',
    'smw_qi_tt_limit' => 'Nombre maximum de résultats affichés',
    'smw_qi_tt_mainlabel' => 'Nom de la premià¨re colonne',
    'smw_qi_tt_order' => 'Order ascendant ou descendant',
    'smw_qi_tt_headers' => 'Afficher ou non les en-têtes du tableau',
    'smw_qi_tt_default' => 'Texte qui va être affiché s\'il n\'y a aucun résultat',
    'smw_qi_tt_treeview' => 'Display your query in a tree',
    'smw_qi_tt_textview' => 'Describe the query in stylized English',

    /* Annotation */
    'smw_annotation_tab' => 'annoter',
    'smw_annotating'     => 'Annotant $1',
    'annotatethispage'   => 'Annoter cette page',


    /* Refactor preview */
    'refactorstatistics' => 'Statistiques refactorisées',
    'smw_ob_link_stats' => 'Ouvrir les statistiques refactorisées',

    /* SMWFindWork */
    'findwork' => 'Trouver un mot',
    'smw_findwork_docu' => 'Cette page suggà¨re des articles qui sont en quelque sorte problématiques mais que vous apprécirez éditer/corriger.',
    'smw_findwork_user_not_loggedin' => 'Vous n\'êtes pas loggué. Il est possible d\'utiliser la page de manià¨re anonyme, mais il est conseillé de vous identifier.',  
    'smw_findwork_header' => 'Les articles suggérés reflà¨tent vos intérêts basés sur votre historique des éditions. Si vous ne savez que choisir, cliquer sur $1. Le systà¨me sélectionnera un sujet pour vous.<br /><br />Si vous voulez travailler sur un problà¨me spécifique, vous pouvez choisir un des suivants: ',
    'smw_findwork_rateannotations' => '<h2>Evaluer les annotations</h2>Vous pouvez contribuer à  améliorer la qualité de ce wiki en évaluant la qualité des affirmations suivantes. Est-ce que les affirmations suivantes sont correctes ?<br><br>',
    'smw_findwork_yes' => 'Correct.',
    'smw_findwork_no' => 'Incorrect.',
    'smw_findwork_dontknow' => 'Je ne sais pas.',
    'smw_findwork_sendratings' => 'Envoyer l\'évaluation',
    'smw_findwork_getsomework' => 'Donnez moi du travail !',
    'smw_findwork_show_details' => 'Montrer les détails',
    'smw_findwork_heresomework' => 'Ici, il y a du travail',

    'smw_findwork_select' => 'Sélectionner',
    'smw_findwork_generalconsistencyissues' => 'Problà¨mes de consistance générale',
    'smw_findwork_missingannotations' => 'Annotations manquantes',
    'smw_findwork_nodomainandrange' => 'Propriétés sans type/domaine',
    'smw_findwork_instwithoutcat' => 'Instances sans catégorie',
    'smw_findwork_categoryleaf' => 'Feuilles de catégorie',
    'smw_findwork_subcategoryanomaly' => 'Anomalies de sous-catégorie',
    'smw_findwork_undefinedcategory' => 'Catégories non définies',
    'smw_findwork_undefinedproperty' => 'Propriétés non définies',
    'smw_findwork_lowratedannotations' => 'Pages contenant des annotations faiblement notées',


    /* Gardening Issue Highlighting in Inline Queries */
    'smw_iqgi_missing' => 'manquant',
    'smw_iqgi_wrongunit' => 'unité incorrecte',

    'smw_deletepage_nolinks' => 'Il n\'existe aucun lien vers cette page !',
    'smw_deletepage_linkstopage'=> 'Pages contenant des liens vers cette page',
    'smw_deletepage_prev' => 'Précédent',
    'smw_deletepage_next' => 'Suivant',
    
    // Triple Store Admin
    'tsa' => 'Administration Triple Store',
    'smw_tsa_welcome' => 'Cette page spéciale vous aide à  administrer le wiki/la connection triplestore.',
    'smw_tsa_couldnotconnect' => 'La connection vers le triple store est impossible.',
    'smw_tsa_notinitalized' => 'Votre wiki n\'est pas initialisé au triplestore.',
    'smw_tsa_waitsoemtime'=> 'Veuillez patienter quelques secondes et suivre ce lien.',
    'smw_tsa_wikiconfigured' => 'Votre wiki est correctement connecté avec le triplestore à  $1',
    'smw_tsa_initialize' => 'Initialiser',
    'smw_tsa_pressthebutton' => 'Veuillez cliquer sur le bouton ci-dessous.',
    'smw_tsa_addtoconfig' => 'Veulliez ajouter les lignes suivantes dans votre LocalSettings.php et vérifier si le connecteur triplestore est en marche.',
    'smw_tsa_addtoconfig2' => 'Assurez-vous que le driver triplestore est activé. Si nécessaire, modifier enableSMWHalo avec',
    'smw_tsa_addtoconfig3' => 'Assurez-vous aussi que l\'URL du graphe (dernier paramà¨tre de enableSMWHalo) est valide et qu\'il ne contient pas de dià¨se (#).',
    'smw_tsa_addtoconfig4' => 'If this does not help, please check out the online-help in the $1.',
    'smw_tsa_driverinfo' => 'Informations sur le driver',
    'smw_tsa_status' => 'Statut',
    'smw_tsa_rulesupport'=> 'Le driver triplestore supporte les rà¨gles ; il vous est conseillé d\'ajouter <pre>$smwgEnableObjectLogicRules=true;</pre> à  votre LocalSettings.php. Sinon, les rà¨gles ne fonctionneront pas.',
    'smw_tsa_norulesupport'=> 'Le driver triplestore ne supporte pas les rà¨gles, bien qu\'elles soient activées dans le wiki. Veuillez supprimer <pre>$smwgEnableObjectLogicRules=true;</pre> de votre LocalSettings.php, afin de ne pas obtenir d\'erreurs.',
    'smw_ts_notconnected' => 'TSC not accessible. Check server: $1',
    'asktsc' => 'Ask triplestore',
    
    // Simple Rules formula parser
    'smw_srf_expected_factor' => 'Une fonction, variable, constante ou accolade est attendue prà¨s de $1',
    'smw_srf_expected_comma' => 'Une virgule est attendue prà¨s de $1',
    'smw_srf_expected_(' => 'Une accolade ouvrante est attendue prà¨s de $1',
    'smw_srf_expected_)' => 'Une accolade fermante est attendue prà¨s de $1',
    'smw_srf_expected_parameter' => 'Un paramà¨tre est attendu prà¨s de $1',
    'smw_srf_missing_operator' => 'Un opérateur est attendu prà¨s de $1',
    
    // Explanations
    'smw_explanations' => 'Explications',
    'explanations' => 'Explications',
    'smw_expl_not_all_inputs' => 'Veuillez compléter chaque champs ci-dessous.',
    'smw_expl_and' => 'ET',
    'smw_expl_because' => 'PARCE QUE',
    'smw_expl_value' => 'Valeur',
    'smw_expl_img' => 'Déclencher une explication',
    'smw_expl_explain_category' => 'Expliquer la tà¢che de la catégorie:',
    'smw_expl_explain_property' => 'Expliquer la tà¢che de la propriété:',
    'smw_expl_error' => 'Malheureusement, des erreurs sont survenues pendant la demande d\'explications:',
    
    // Derived facts
    'smw_df_derived_facts_about' => 'Faits dérivés de $1',

    //skin
    'smw_search_this_wiki' => 'Search this wiki',
    'more_functions' => 'more',
    'smw_treeviewleft' => 'Open treeview to the left side',
    'smw_treeviewright' => 'Open treeview to the right side'

    );


    protected $smwSpecialProperties = array(
    //always start upper-case
    "___cfsi" => array('_siu', 'Correspond au SI'),
		"___CREA" => array('_wpg', 'Creator'),
		"___CREADT" => array('_dat', 'Creation date'),
		"___MOD" => array('_wpg', 'Last modified by')
    );


    var $smwSpecialSchemaProperties = array (
    SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT => 'A pour domaine et champ de valeur',
    SMW_SSP_HAS_MAX_CARD => 'A pour cardinalité max',
    SMW_SSP_HAS_MIN_CARD => 'A pour cardinalité min',
    SMW_SSP_IS_INVERSE_OF => 'Est l\'inverse de',
    SMW_SSP_IS_EQUAL_TO => 'Est égal à '
    );

    var $smwSpecialCategories = array (
    SMW_SC_TRANSITIVE_RELATIONS => 'Propriétés transitives',
    SMW_SC_SYMMETRICAL_RELATIONS => 'Propriétés symétriques'
    );

    var $smwHaloDatatypes = array(
    'smw_hdt_chemical_formula' => 'Formule chimique',
    'smw_hdt_chemical_equation' => 'Equation chimique',
    'smw_hdt_mathematical_equation' => 'Equation mathématique'
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
        SMW_NS_RELATION_TALK  => 'Relation_discussion',
        SMW_NS_PROPERTY       => 'Property',
        SMW_NS_PROPERTY_TALK  => 'Property_discussion',
        SMW_NS_TYPE           => 'Type',
        SMW_NS_TYPE_TALK      => 'Type_discussion'
        );
    }


}


