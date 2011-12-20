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

define('SEMANTIC_REFACTORING_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

$srefgIP = $IP . '/extensions/SemanticRefactoring';
$wgExtensionMessagesFiles['SemanticRefactoring'] = $srefgIP . '/languages/SRF_Messages.php';
$smgJSLibs[] = 'fancybox';

$wgExtensionFunctions[] = 'sreffSetupExtension';
function sreffSetupExtension() {
	global $srefgIP, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups;
	
	$wgAutoloadClasses['SREFRefactor'] = $srefgIP . '/specials/refactor/SRF_SpecialRefactor.php';
    $wgSpecialPages['SREFRefactor'] = array('SREFRefactor');
    $wgSpecialPageGroups['SREFRefactor'] = 'smwplus_group';
    
    global $smwgResultFormats;
    $smwgResultFormats['_srftable'] = 'SRFTableSelectorResultPrinter';

	// autoload classes
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

	global $wgOut;
	sreffRegisterJSModules($wgOut);

	require_once($srefgIP . '/includes/SRF_Bot.php');
	new SRFRefactoringBot();
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
            'scripts/SRF_dialogs.js',
            'scripts/SRF_SpecialRefactor.js'
            ),
        'styles' => array(
            'skins/SRF_SpecialRefactor.css'
            ),
        'dependencies' => array(
            ),
         'messages' => array('sref_start_operation',
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
						    'sref_removeQueries_help',
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
                        
                            'sref_not_allowed_botstart'
    ),
    );

    
    // add modules
    $out->addModules(array('ext.semanticrefactoring.dialogs'));
    
   
}



