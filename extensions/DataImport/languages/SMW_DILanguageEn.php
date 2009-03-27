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
	'smw_wws_s1-help' => '<h4>Help</h4><br/>This GUI helps you to define a Wiki Web Service Definition (<b>WWSD</b>). A WWSD defines how external web services can be used in Wiki articles. First you have to choose if you like to use a <b>SOAP</b> or a <b>REST</b>ful web service. After that you have to enter the <b>URI</b> of the web service. If you use a SOAP web service, then the URI must direct to a WSDL file. You can optionally define <b>authentication</b> credentials. At the moment only the basic HTTP authentication is supported.',
	'smw_wws_s2-help' => '<h4>Help</h4><br/>Each web service may provide different <b>methods</b> which in turn deliver different types of data. If you like to use several methods of a web service, then you have to define another WWSD for each method.',
	'smw_wws_s3-help' => '<h4>Help</h4><br/>Now you have to define the parameters which will be used for calling the web service. Each parameter is identified by a <b>path</b>, which addresses it in the parameter type hierarchy. You can browse the possible parameters by expanding and collapsing the type hierarchy in the tree view above. Sometimes it is not necessary to pass a value for each parameter when calling a web service. You have to activate the checkbox in the <b>use</b>-column of a parameter, if you like to include it into the WWSD, so that users later are able to provide a value for that parameter when calling the web service. (Usually you have to include all parameters.) For each parameter you like to use, you have to specify an <b>alias</b> which later will be used instead of the long path to address the parameter. You also can specify if a parameter will be <b>optional</b> when users later call the web service. If a parameter is not optional, then users will receive an error message if they call the web service without passing a value for that parameter. At last you can define a <b>default value</b> for a parameter. This default value will be used if a parameter is not optional and if no value for that parameter was passed to the web service call.',
	'smw_wws_s4-help' => '<h4>Help</h4><br/>Now you have to define which parts of the result returned by the web service users can display in Wiki articles. Each result part is identified by a <b>path</b>, which addresses it in the result type hierarchy. You can browse the possible result parts by expanding and collapsing the type hierarchy in the tree view above. You have to activate the checkbox in the <b>use</b>-column of a result part, if users later should be able to use it in a Wiki article when calling the web service. For each result part you like to use, you have to specify an <b>alias</b> which later will be used instead of the long path to address the result part. Sometimes result parts contain information encoded in XML or JSON. It often does not make sense to display the encoded information in Wiki articles. Therefore you can add <b>subpaths</b> to result parts that help you to extract the relevant information. A subpath can have the <b>JSON</b> or <b>XPath</b> format. (The actual version of the Data import extension only supports the XPath format.) In the <b>path</b> column you have to enter the path to the information you like to extract. You also have to provide an alias for each subpath.',
	'smw_wws_s5-help' => '<h4>Help</h4><br/>The <b>update policies</b> define the time period after which the result delivered by a web service will be updated again. The <b>display policy</b> will be relevant whenever a web service result is displayed in an article, whereas the <b>query policy</b> is only relevant for semantic queries. For example if you define a display policy with a <b>max age</b> value of two days, then an article that contains a corresponding web service call will display a cached web service result if the web service result was last updated less than two days ago. Otherwise the web service will be called again and the cached result will be replaced. The display policy <b>once</b> means that the web service result will never be updated. The <b>delay value</b> (in seconds) is applied between two subsequent calls of a web service. So you can prevent, that the web service is called too often in a short time, which may violate the terms of use of the web service provider. The <b>span of life</b> defines how long a result of a web service call will be kept in the cache. If you do not provide a value for the span of life, then the web service result will be kept in the cache for an unlimited time span. At last you can choose if the age of cached results will <b>expire</b> after their last update or after their last access (e.g. by viewing an article).',
	'smw_wws_s6-help' => '<h4>Help</h4><br/>In order to use the web service you need to <b>name</b> it.',
	'smw_wws_s1-menue' => '1. Define credentials',
	'smw_wws_s2-menue' => '2. Select method',
	'smw_wws_s3-menue' => '3. Define parameters',
	'smw_wws_s4-menue' => '4. Define result parts',
	'smw_wws_s5-menue' => '5. Define update policy',
	'smw_wws_s6-menue' => '6. Save WWSD',
	'smw_wws_s1-intro' => '1. Define credentials',
	'smw_wws_s1-uri' => 'Enter URI: ',
	'smw_wws_s2-intro' => '2. Select method',
	'smw_wws_s2-method' => 'Select method: ',
	'smw_wws_s3-intro' => '3. Define parameters',
	'smw_wws_duplicate' => 'Some of the type-definitions used in this WSDL are ambiguous. Those type definitions are highlighted in red. It is strongly recommended that you review and edit them later in the textual represantation of the WWSD!',
	'smw_wws_s4-intro' => '4. Choose result parts',
	'smw_wws_s5-intro' => '5. Define update policy',
	'smw_wws_s6-intro' => '6. Save WWSD',
	'smw_wws_s6-name' => 'Enter name: ',
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
	
	'smw_wws_s2-REST-help' => '<h4>Help</h4><br/>Now you have to decide if you like to use the HTTP-<b>get</b> or the HTTP-<b>post</b> method for calling the web service. (In most cases choosing HTTP-get is appropriate.) ',
	'smw_wws_s3-REST-help' => '<h4>Help</h4><br/>Now you have to define the parameters which will be used for calling the web service. At first you have to enter the <b>path</b> of the parameter. If you do not enter a path for a parameter, then this parameter will not be included into the WWSD. After that you can enter an <b>alias</b> for the parameter which later will be used to address the parameter when calling the web service in a Wiki article. If you do not provide an explicit alias, then the parameter path will be used as alias. You also can specify if a parameter will be <b>optional</b> when users later call the web service. If a parameter is not optional, then users will receive an error message if they call the web service without passing a value for that parameter. At last you can define a <b>default value</b> for a parameter. This default value will be used if a parameter is not optional and if no value for that parameter was passed to the web service call.',
	'smw_wws_s4-REST-help' => '<h4>Help</h4><br/>RESTful web services return a string as their result. This string can be a simple text or it can contain information encoded in a format like JSON or XML. Now you have to define which parts of this result users can display in Wiki articles.  At first you have to enter an <b>alias</b> for each result part. This alias will later be used to address the result part when the web service is used in an article. If you do not specify an alias, then a random one will be created. Now you have to define if the path you like to use for extracting result parts has the JSON or the XPath <b>format</b>. (Note: The Data import extension supports only the XPath format at the moment.)  Now you can enter the <b>path</b> which will be used to extract the result part from the complete result.',
	'smw_wws_help-button-tooltip' => 'Display or hide help.',
	'smw_wws_selectall-tooltip' => 'Select or deselect all.',
	'smw_wws_autogenerate-alias-tooltip' => 'Autogenerate aliases.',
	
	'smw_wsuse_s1-help' => '<h4>Help</h4><br/> This special page allows you to create the #ws-syntax for calling a web service from within a Wiki article. At first you have to choose one of the <b>available web services</b> in the dropdown-menu above.',
	'smw_wsuse_s2-help' => '<h4>Help</h4><br/> Now you have to choose which of the parameters displayed in the table above you like to use when calling the web service. If you like to use a parameter, then you have to activate the checkbox in the <b>use</b>-column of the parameter. Parameters that are not optional are activated by default. If you like to use a parameter, then you have to provide a <b>value</b> for that parameter. Some parameters provide <b>default values</b>. If you like to use a default value instead of providing your own value for the parameter, then you have to activate the checkbox in front of the default value.',
	'smw_wsuse_s3-help' => '<h4>Help</h4><br/> Now you have to choose which result parts you like to display in your Wiki article. Please activate the checkbox in the <b>use</b>-column of each result part you like to display.',
	'smw_wsuse_s4-help' => '<h4>Help</h4><br/> Here you can choose which <b>format</b> you like to use for displaying the web service result in your Wiki article.  Some formats also allow you to specify a <b>template</b> which will be used for formatting the rows of the result.',
	'smw_wsuse_s5-help' => '<h4>Help</h4><br/> In the last step you have three possibilities. You can view a <b>preview</b> of the web service result or you can display the <b>#ws-syntax</b> which was created by the GUI. If you navigated to this GUI from the semantic toolbar while you were editing an article then you can directly go back and the #ws-syntax will be automatically <b>added</b> into the editor.',
	
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


