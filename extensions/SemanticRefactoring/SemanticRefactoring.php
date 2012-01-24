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
 *
 * Created on 08.12.2011
 *
 * @defgroup SemanticRefactoring Semantic Refactoring extension
 *
 * @author Kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SEMANTIC_REFACTORING_VERSION', '1.0.0_0 [B${env.BUILD_NUMBER}]');

$srefgIP = $IP . '/extensions/SemanticRefactoring';
$wgExtensionMessagesFiles['SemanticRefactoring'] = $srefgIP . '/languages/SRF_Messages.php';
$smgJSLibs[] = 'fancybox';

$wgExtensionFunctions[] = 'sreffSetupExtension';
function sreffSetupExtension() {
	global $srefgIP, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups;
	
	$wgAutoloadClasses['SemanticRefactoring'] = $srefgIP . '/specials/refactor/SRF_SpecialRefactor.php';
    $wgSpecialPages['SemanticRefactoring'] = array('SemanticRefactoring');
    $wgSpecialPageGroups['SemanticRefactoring'] = 'smwplus_group';
    
    global $smwgResultFormats;
    $smwgResultFormats['_srftable'] = 'SRFTableSelectorResultPrinter';

	// autoload classes
	$wgAutoloadClasses['SRFQuerySelector'] = $srefgIP . '/specials/refactor/SRF_QuerySelector.php';
	$wgAutoloadClasses['SRFTableSelectorResultPrinter'] = $srefgIP . '/specials/refactor/SRF_TableSelectorResultPrinter.php';
	
	$wgAutoloadClasses['SRFRefactoringBot'] = $srefgIP . '/includes/SRF_Bot.php';
	$wgAutoloadClasses['SRFRefactoringOperation'] = $srefgIP . '/includes/SRF_RefactoringOperation.php';
	$wgAutoloadClasses['SRFTools'] = $srefgIP . '/includes/SRF_Tools.php';
	$wgAutoloadClasses['SRFChangeValueOperation'] = $srefgIP . '/includes/operations/SRF_ChangeValue.php';
	$wgAutoloadClasses['SRFDeleteCategoryOperation'] = $srefgIP . '/includes/operations/SRF_DeleteCategory.php';
	$wgAutoloadClasses['SRFDeletePropertyOperation'] = $srefgIP . '/includes/operations/SRF_DeleteProperty.php';
	$wgAutoloadClasses['SRFRenameCategoryOperation'] = $srefgIP . '/includes/operations/SRF_RenameCategory.php';
	$wgAutoloadClasses['SRFRenameInstanceOperation'] = $srefgIP . '/includes/operations/SRF_RenameInstance.php';
	$wgAutoloadClasses['SRFRenamePropertyOperation'] = $srefgIP . '/includes/operations/SRF_RenameProperty.php';
    
	global $wgHooks;
	$wgHooks['ResourceLoaderGetConfigVars'][] = 'srefSetResourceLoaderConfigVars';
	
	global $wgOut;
	sreffRegisterJSModules($wgOut);

	require_once($srefgIP . '/includes/SRF_Bot.php');
	new SRFRefactoringBot();
}

function srefSetResourceLoaderConfigVars( &$vars ) {
	 // add groups having the 'gardening' right
    $groups = array();
    global $wgGroupPermissions;
    foreach($wgGroupPermissions as $group => $arr) {
        if (array_key_exists('gardening', $arr)) $groups[] = $group;
    }
    $vars['srefValidGroups']['groups'] = $groups;
    return true;
}

/**
 * Register JS modules
 *
 * @param $out
 */
function sreffRegisterJSModules(& $out) {
	global $wgResourceModules, $moduleTemplate, $wgScriptPath, $srefgIP;
	$moduleTemplate = array(
        'localBasePath' => $srefgIP,
        'remoteBasePath' => $wgScriptPath . '/extensions/SemanticRefactoring',
        'group' => 'ext.semanticrefactoring'
        );

         
        $wgResourceModules['ext.semanticrefactoring.dialogs'] = $moduleTemplate + array(
        'scripts' => array(
           // 'scripts/jquery.json-2.3.js',
            'scripts/SRF_dialogs.js',
            'scripts/SRF_SpecialRefactor.js'
            ),
        'styles' => array(
            'skins/SRF_SpecialRefactor.css'
            ),
        'dependencies' => array('ext.smw.style', 'ext.smw.tooltips', 'ext.smw.sorttable'
            ),
         'messages' => array('sref_start_operation',
                            'sref_warning_no_gardening',
                            'sref_rename_instance',
                            'sref_rename_instance_help',
                            'sref_rename_property',
                             'sref_rename_property',
						    'sref_rename_property_help',
						    'sref_rename_category',
						    'sref_rename_category_help',
						    'sref_rename_annotations',
						    'sref_rename_annotations_help',
						
						    'sref_deleteCategory',
						    'sref_deleteCategory_help',
						    'sref_removeInstances' ,
						    'sref_removeInstances_help',
						    'sref_removeCategoryAnnotations',
						    'sref_removeCategoryAnnotations_help',
						    'sref_removePropertyWithDomain',
						    'sref_removePropertyWithDomain_help',
						    'sref_removeQueriesWithCategories',
						    'sref_removeQueriesWithCategories_help',
						    'sref_includeSubcategories',
						    'sref_includeSubcategories_help',
						
						    'sref_deleteProperty' ,
						    'sref_deleteProperty_help',
						    'sref_removeInstancesUsingProperty',
						    'sref_removeInstancesUsingProperty_help',
						    'sref_removePropertyAnnotations',
						    'sref_removePropertyAnnotations_help',
						    'sref_removeQueriesWithProperties',
						    'sref_removeQueriesWithProperties_help',
						    'sref_includeSubproperties',
						    'sref_includeSubproperties_help',
                        
                            'sref_not_allowed_botstart',
                            'sref_no_instances_selected',
            
				             'sref_add',
				            'sref_remove',
				            'sref_replace' ,
				            'sref_setvalue',
				            'sref_rename',
				            'sref_category',
                            'sref_old_category',
                            'sref_new_category',
				            'sref_annotationproperty',
				            'sref_template',
                            'sref_property',
				            'sref_parameter',
						    'sref_old_parameter',
						    'sref_new_parameter',
						    'sref_value',
						    'sref_old_value',
						    'sref_new_value',
            
				            'sref_comment',
                            'sref_log',
						    'sref_starttime',
						    'sref_endtime',
						    'sref_progress',
						    'sref_status',
                            'sref_finished',
                            'sref_running',
                            'sref_page',
				            'sref_add_command',
				            'sref_remove_command',
            
				            'sref_help_addcategory',
						    'sref_help_removecategory' ,
						    'sref_help_replacecategory' ,
						    'sref_help_addcategory',
						    'sref_help_removecategory',
						    'sref_help_replacecategory',
						    'sref_help_addannotation',
						    'sref_help_removeannotation',
						    'sref_help_replaceannotation',
						    'sref_help_setvalueofannotation',
						    'sref_help_setvalueoftemplate',
						    'sref_help_replacetemplatevalue',
						    'sref_help_renametemplateparameter'
    )
    );

   
    // add modules
    $out->addModules(array('ext.semanticrefactoring.dialogs'));
    
   
}

global $wgAjaxExportList;
$wgAjaxExportList[] = 'sreff_query';

function sreff_query($query) {
    $qs = new SRFQuerySelector();
    $qresult = $qs->getQueryResult();
    $response = new AjaxResponse($qresult['html']);
    $response->setResponseCode(200);
    return $response;
}



