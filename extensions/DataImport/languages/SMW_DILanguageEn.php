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

class SMW_DILanguageEn extends SMW_DILanguage {
	
	protected $smwUserMessages = array(
	'specialpages-group-di_group' => 'Data Import',
	
	/* Messages of the Thesaurus Import */
	'smw_ti_welcome' => 'Please select a transport layer module (TLM) and then an data access module (DAM):',
	'smw_ti_selectDAM' => 'Please select a DAM',
	'smw_ti_firstselectTLM' => 'first select TLM',
	'smw_ti_selectImport' => 'Please choose one of the available import sets:&nbsp;&nbsp;',
	'smw_ti_inputpolicy' => 'With the input policy, you can define which information should be imported, using regular expressions (<a href="http://www.opengroup.org/onlinepubs/007908799/xbd/re.html" target="_blank">help</a>). 
							For example, use "^Em.*" in order to import only data sets that start with "Em". 
							If no input policy is chosen, all data will be imported.',
	'smw_ti_define_inputpolicy' => 'Please define an input policy:',
	'smw_ti_mappingPage' => 'Please enter the name of the article that does contain the mapping policies:',
	'smw_ti_viewMappingPage' => 'View',
	'smw_ti_editMappingPage' => 'Edit',
	'smw_ti_conflictpolicy' => 'Please define a conflict policy. The conflict policy defines what happens if articles are imported that already exist in this wiki:',
	'smw_ti_nomappingpage' => 'The entered article that should contain the mapping policies does not exist',

	'smw_ti_succ_connected' => 'Successfully connected to "$1".',
	'smw_ti_class_not_found' => 'Class "$1" not found.',
	'smw_ti_no_tl_module_spec' => 'Could not find specification for TL module with ID "$1".',
	'smw_ti_xml_error' => 'XML error: $1 at line $2',
	'smw_ti_filename'  => 'Filename:',
	'smw_ti_articlename'  => 'Articlename:',
	'smw_ti_articleerror' => 'The article "$1" does not exist or is empty.',
	'smw_ti_fileerror' => 'The file "$1" does not exist or is empty.',
	'smw_ti_no_article_names' => 'There are no article names in the specified data source.',
	'smw_ti_termimport' => 'Import Vocabulary',
	'termimport' => 'Import Vocabulary',
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
	'smw_wws_edit_in_gui' => 'Please click here to edit the WWSD in the graphical user interface.',
	'smw_wws_invalid_wwsd' => 'The Wiki Web Service Definition is invalid or does not exist.',
	'smw_wws_wwsd_element_missing' => 'The element "$1" is missing in the Wiki Web Service Definition.',
	'smw_wws_wwsd_attribute_missing' => 'The attribute "$1" is missing in element "$2" of the Wiki Web Service Definition.',
	'smw_wws_too_many_wwsd_elements' => 'The element "$1" appears several times in the Wiki Web Service Definition.',
	'smw_wws_wwsd_needs_namespace' => 'Please note: Wiki web service definitions are only considered in articles with namespace "Web Service"!',
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
	'smw_wwsd_array_index_missing' => 'An array index is missing in the path: "$1"',
	'smw_wwsd_array_index_incorrect' => 'An array index is incorrect in the path: "$1"',
	'smw_wsuse_wrong_parameter' => 'The parameter "$1" does not exist in the Wiki Web Service Definition.',
	'smw_wsuse_parameter_missing' => 'The parameter "$1" is not optional and no default value was provided by the Wiki Web Service Definition.',
	'smw_wsuse_wrong_resultpart' => 'The result-part "$1" does not exist in the Wiki Web Service Definition.',
	'smw_wsuse_wwsd_not_existing' => 'A Wiki Web Service Definition with the name "$1" does not exist.',
	'smw_wsuse_wwsd_error' => 'The usage of the Web Service was erroneous:',
	'smw_wsuse_getresult_error' => 'An error occured while calling the Web Service.',
	'smw_wsuse_old_cacheentry' => ' Therefore an outdated cache entry is used instead of a current Web Service result.',
	'smw_wsuse_prop_error' => 'It is not allowed to use more than one result part as a value for a property',
	'smw_wsuse_type_mismatch' => 'The Web Service did not return the expected type for this result part. Please change the corresponding WWSD. A variable dump is printed.',
	'webservicerepository' => 'Web Service Repository',
	'smw_wws_select_without_object' => 'The attribute "object" of the select "$1" of the result "$2" is missing.',
	'smw_wws_select_without_value' => 'The attribute "value" of the select "$1" of the result "$2" is missing.',
	'smw_wws_duplicate_select' => 'The select "$1" appears several times in the result "$2".',
	'smw_wws_need_confirmation' => 'The WWSD for this Web Service has to be confirmed by an administrator before it can be used again',
	'definewebservice' => 'Define Web Service',
	'smw_wws_s1-help' => '<h4>Help</h4>Please enter the URI of a Web Service. The URI must direct towards the WSDL (Web Service Description Language) file of the Web Service. If you do not have an URI yet, you can search for different Web Service providers on the internet. Please take a look at Web Service Search Engines in order to find what you are looking for.',
	'smw_wws_s2-help' => '<h4>Help</h4>Each Web Service may provide different methods which in turn deliver different types of data. The Web Service support in this wiki does allow to collect results from only one method of the Web Service. If you would like to gather results from different methods of the same Web Service, you have to create one Web Service definition for each method.',
	'smw_wws_s3-help' => '<h4>Help</h4>Now you have to define the parameters which will be used for calling the method of the Web Service. Each parameter is addressed by a unique path within the type hierarchy. You can browse the type hierarchy by expanding respectively collapsing the subtypes in the tree view. For each parameter you wish to use, you have to specify an alias which you can later use to address the parameter. If you do not want to use a parameter for the method call, you have to leave the corresponding alias input field empty. If you like to save time you can press the pencil icon in the head of the alias column for automatic alias generation. You also can specify if a parameter will be optional when you later will call the Web Service. At last you have the possibility to define a default value for a parameter which will be used for the Web Service call in case you do not provide a value for the parameter.',
	'smw_wws_s4-help' => '<h4>Help</h4>Now you have to specify the parts of the result you would like to display in your wiki articles when you later will call the method of the Web Service. This works like defining parameters, except that you do not need to decide if the result parts are optional and you do not have to provide a default value.',
	'smw_wws_s5-help' => '<h4>Help</h4>The update policies define the time period after which a value delivered by a Web Service will be updated. The display policy will be relevant whenever a value is displayed in an article, whereas the query policy is only relevant for semantic queries. The delay value (in seconds) is applied between two calls of a Web Service. So you can prevent, that the service is called too often in a short time, which may violate the terms of use of the web service provider. The span of life defines how long a result of a Web Service call will be kept in the cache. If you do not provide a value for the span of life, then the value will be kept in the cache for an unlimited time span. At last you can choose if the age of cached results will be reset if they are updated or after their last access (e.g. by viewing an article). ',
	'smw_wws_s6-help' => '<h4>Help</h4>In order to use the Web Service you need to name it. Please use a meaningful name that makes it easy for other users to recognize the purpose of this Web Service.',
	'smw_wws_s1-menue' => '1. Define credentials',
	'smw_wws_s2-menue' => '2. Select method',
	'smw_wws_s3-menue' => '3. Define parameters',
	'smw_wws_s4-menue' => '4. Define result parts',
	'smw_wws_s5-menue' => '5. Define update policy',
	'smw_wws_s6-menue' => '6. Save WWSD',
	'smw_wws_s1-intro' => '1. Define credentials',
	'smw_wws_s1-uri' => 'Enter URI: ',
	'smw_wws_s2-intro' => '2. Select method',
	'smw_wws_s2-method' => 'Select method',
	'smw_wws_s3-intro' => '3. Define parameters',
	'smw_wws_duplicate' => 'Some of the type-definitions used in this WSDL are ambiguous. Those type definitions are highlighted in red. It is strongly recommended that you review and edit them later in the textual represantation of the WWSD!',
	'smw_wws_s4-intro' => '4. Choose result parts',
	'smw_wws_s5-intro' => '5. Define update policy',
	'smw_wws_s6-intro' => '6. Save WWSD',
	'smw_wws_s6-name' => 'Enter name',
	'smw_wws_s7-intro-pt1' => 'Your Web Service ',
	'smw_wws_s7-intro-pt2' => ' has been successfully created. In order to include this Web Service into a page, please use the following syntax:',
	'smw_wws_s7-intro-pt3' => 'Your Web Service will from now on be available in the list of available Web Services.',
	'smw_wws_s1-error' => 'It was not possible to connect to the specified URI. Please change the URI or try again.',
	'smw_wws_s2a-error' => 'It was not possible to connect to the specified URI. Please change the URI or try again.',
	'smw_wws_s2b-error' => 'The parameter definition of this method contains a recursive definition. Please choose another Web Service or another method',
	'smw_wws_s3-error' => 'It was not possible to connect to the specified URI. Please change the URI or try again.',
	'smw_wws_s4-error' => 'todo: write error message',
	'smw_wws_s5-error' => 'todo: write error message',
	'smw_wws_s6-error' => 'You have to specify a name for the WWSD before it is possible to proceed.',
	'smw_wws_s6-error2' => 'An article with the given name allready exists. Please choose another name and try again.',
	'smw_wws_s6-error3' => 'An error occurred when saving the WWSD. Please try again!',
	'smw_wscachebot' => 'Clean the Web Service-Cache',
	'smw_ws_cachebothelp' => 'This bot removes cached Web Service results, that are outdated.',
	'smw_ws_cachbot_log' => 'Outdated cache entries for this Web Service were removed.',
	'smw_wsupdatebot' => 'Update the Web Service-cache',
	'smw_ws_updatebothelp' => 'This bot updates the Web Service-cache.',
	'smw_ws_updatebot_log' => 'Cache entries for this Web Service were updated.',
	'smw_ws_updatebot_callerror' => 'An error occured while updating the cache entries of this Web Service',
	'smw_ws_updatebot_confirmation' => 'It was not possible to update results of this Web Service due to a missing confirmation of the WWSD',
	'smw_wsgui_nextbutton' => 'Next',
	'smw_wsgui_savebutton' => 'Save',
	
	'usewebservice' => 'Use Web Service',
	
	'smw_wws_client_connect_failure' => 'It was not possible to connect to ',
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


