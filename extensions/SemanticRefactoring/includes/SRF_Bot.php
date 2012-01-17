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
 * @ingroup Refactoring
 *
 * @defgroup Refactoring
 * @ingroup Refactoring
 *
 * @author Kai Kühn
 *
 * Created on 16.02.2011
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

require_once( $srefgIP . '/includes/SRF_Bot.php');
require_once($srefgIP . '/includes/SRF_RefactoringOperation.php');
require_once($srefgIP . '/includes/SRF_Tools.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeCategoryValue.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeTemplate.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeTemplateParameter.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeValue.php');
require_once($srefgIP . '/includes/operations/SRF_DeleteCategory.php');
require_once($srefgIP . '/includes/operations/SRF_DeleteProperty.php');
require_once($srefgIP . '/includes/operations/SRF_RenameCategory.php');
require_once($srefgIP . '/includes/operations/SRF_RenameInstance.php');
require_once($srefgIP . '/includes/operations/SRF_RenameProperty.php');


/**
 * Exports object logic from TSC.
 *
 * @author kuehn
 *
 */
class SRFRefactoringBot extends GardeningBot {

	function __construct() {
		parent::GardeningBot("smw_refactoringbot");
	}

	public function getHelpText() {
		return wfMsg('smw_gard_exportobl_docu');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function isVisible() {
		return false;
	}

	/**
	 * Returns an array
	 */
	public function createParameters() {
		return array();
	}

	/**
	 * Creates a comment depending on the given parameters
	 *
	 * @param string parameters (comma-separated)
	 * @return string
	 */
	public function getComment($params) {
		$paramArray = GardeningBot::convertParamStringToArray($params);
		$operation = $paramArray['SRF_OPERATION'];

		$msg = 'sref_comment_'.strtolower($operation);
		switch($operation) {
			case 'addCategory':
				if (!array_key_exists('category', $paramArray)) {
					return '';
				}
				$category = $paramArray['category'];
				return wfMsg($msg, $category);
			case 'removeCategory':
				if (!array_key_exists('category', $paramArray)) {
					return '';
				}
				$category = $paramArray['category'];
				return wfMsg($msg, $category);
			case 'replaceCategory':
				if (!array_key_exists('old_category', $paramArray)) {
					return '';
				}
				$old_category = $paramArray['old_category'];
				if (!array_key_exists('new_category', $paramArray)) {
					return '';
				}
				$new_category = $paramArray['new_category'];
				return wfMsg($msg, $old_category, $new_category);
			case 'addAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				return wfMsg($msg, $property, $value);

				break;

			case 'removeAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				return wfMsg($msg, $property, $value);

				break;

			case 'replaceAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('old_value', $paramArray)) {
					return "Old Value missing";
				}
				$old_value = $paramArray['old_value'];
				if (!array_key_exists('new_value', $paramArray)) {
					return "New Value missing";
				}
				$new_value = $paramArray['new_value'];
				return wfMsg($msg, $property, $old_value, $new_value);

				break;

			case 'setValueOfAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				return wfMsg($msg, $property, $value);

				break;

			case 'setValueOfTemplate' :
				if (!array_key_exists('template', $paramArray)) {
					return "template missing";
				}
				$template = $paramArray['template'];
				if (!array_key_exists('parameter', $paramArray)) {
					return "parameter missing";
				}
				$parameter = $paramArray['parameter'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				return wfMsg($msg, $template, $parameter, $value);

				break;

			case 'replaceTemplateValue' :
				if (!array_key_exists('template', $paramArray)) {
					return "template missing";
				}
				$template = $paramArray['template'];
				if (!array_key_exists('parameter', $paramArray)) {
					return "parameter missing";
				}
				$parameter = $paramArray['parameter'];
				if (!array_key_exists('old_value', $paramArray)) {
					return "Old Value missing";
				}
				$old_value = $paramArray['old_value'];
				if (!array_key_exists('new_value', $paramArray)) {
					return "New Value missing";
				}
				$new_value = $paramArray['new_value'];
				return wfMsg($msg, $template, $parameter, $old_value, $new_value);

				break;

			case 'renameTemplateParameter' :
				if (!array_key_exists('template', $paramArray)) {
					return "template missing";
				}
				$template = $paramArray['template'];

				if (!array_key_exists('old_parameter', $paramArray)) {
					return "old_parameter missing";
				}
				$old_parameter = $paramArray['old_parameter'];
				if (!array_key_exists('new_parameter', $paramArray)) {
					return "new_parameter missing";
				}
				$new_parameter = $paramArray['new_parameter'];
				return wfMsg($msg, $template, $old_parameter, $new_parameter);
				break;
		}
		return '- unknown parameters -';
	}

	public function run($paramArray, $isAsync, $delay) {
			
		// do not allow to start synchronously.
		if (!$isAsync) {
			return "RefactoringBot should not be executed synchronously!";
		}

		if (!array_key_exists('SRF_OPERATION', $paramArray)) {
			return "Refactoring operation not specified.";
		}

		$operation = $paramArray['SRF_OPERATION'];

		switch($operation) {
			case 'renameInstance':
				if (!array_key_exists('oldInstance', $paramArray)) {
					return "Old instance missing";
				}
				$oldInstance = $paramArray['oldInstance'];

				if (!array_key_exists('newInstance', $paramArray)) {
					return "New instance missing";
				}
				$newInstance = $paramArray['newInstance'];

				if (!array_key_exists('sref_rename_annotations', $paramArray) || $paramArray['sref_rename_annotations'] == false) {
					return "Nothing done.";
				}

				$op = new SRFRenameInstanceOperation($oldInstance, $newInstance);

				break;
			case 'renameProperty':
				if (!array_key_exists('oldProperty', $paramArray)) {
					return "Old property missing";
				}
				$oldProperty = $paramArray['oldProperty'];

				if (!array_key_exists('newProperty', $paramArray)) {
					return "New property missing";
				}
				$newProperty = $paramArray['newProperty'];

				if (!array_key_exists('sref_rename_annotations', $paramArray) || $paramArray['sref_rename_annotations'] == false) {
					return "Nothing done.";
				}

				$op = new SRFRenamePropertyOperation($oldProperty, $newProperty);
					
				break;
			case 'renameCategory' :
				if (!array_key_exists('oldCategory', $paramArray)) {
					return "Old category missing";
				}
				$oldCategory = $paramArray['oldCategory'];

				if (!array_key_exists('newCategory', $paramArray)) {
					return "New property missing";
				}
				$newCategory = $paramArray['newCategory'];

				if (!array_key_exists('sref_rename_annotations', $paramArray) || $paramArray['sref_rename_annotations'] == false) {
					return "Nothing done.";
				}

				$op = new SRFRenameCategoryOperation($oldCategory, $newCategory);

				break;
			case 'deleteCategory' :
				if (!array_key_exists('category', $paramArray)) {
					return "Category missing";
				}
				$category = $paramArray['category'];

				$op = new SRFDeleteCategoryOperation($category, $paramArray);

				break;
			case 'deleteProperty' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				$op = new SRFDeletePropertyOperation($property, $paramArray);

				break;

			case 'addCategory' :
				if (!array_key_exists('category', $paramArray)) {
					return "Category missing";
				}
				$category = $paramArray['category'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeCategoryValueOperation($titles, NULL, $category);

				break;

			case 'removeCategory' :
				if (!array_key_exists('category', $paramArray)) {
					return "Category missing";
				}
				$category = $paramArray['category'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeCategoryValueOperation($titles, $category, NULL);

				break;
			case 'replaceCategory' :
				if (!array_key_exists('old_category', $paramArray)) {
					return "old_category missing";
				}
				$old_category = $paramArray['old_category'];
				if (!array_key_exists('new_category', $paramArray)) {
					return "new_category missing";
				}
				$new_category = $paramArray['new_category'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeCategoryValueOperation($titles, $old_category, $new_category);

				break;

			case 'addAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeValueOperation($titles, $property, NULL, $value);

				break;

			case 'removeAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeValueOperation($titles, $property, $value, NULL);

				break;

			case 'replaceAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('old_value', $paramArray)) {
					return "Old Value missing";
				}
				$old_value = $paramArray['old_value'];
				if (!array_key_exists('new_value', $paramArray)) {
					return "New Value missing";
				}
				$new_value = $paramArray['new_value'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeValueOperation($titles, $property, $old_value, $new_value);

				break;

			case 'setValueOfAnnotation' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeValueOperation($titles, $property, NULL, $value, true);

				break;

			case 'setValueOfTemplate' :
				if (!array_key_exists('template', $paramArray)) {
					return "template missing";
				}
				$template = $paramArray['template'];
				if (!array_key_exists('parameter', $paramArray)) {
					return "parameter missing";
				}
				$parameter = $paramArray['parameter'];
				if (!array_key_exists('value', $paramArray)) {
					return "Value missing";
				}
				$value = $paramArray['value'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeTemplateParameterOperation($titles, $template, $parameter, NULL, $value, true);

				break;

			case 'replaceTemplateValue' :
				if (!array_key_exists('template', $paramArray)) {
					return "template missing";
				}
				$template = $paramArray['template'];
				if (!array_key_exists('parameter', $paramArray)) {
					return "parameter missing";
				}
				$parameter = $paramArray['parameter'];
				if (!array_key_exists('old_value', $paramArray)) {
					return "Old Value missing";
				}
				$old_value = $paramArray['old_value'];
				if (!array_key_exists('new_value', $paramArray)) {
					return "New Value missing";
				}
				$new_value = $paramArray['new_value'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeTemplateParameterOperation($titles, $template, $parameter, $old_value, $new_value);

				break;

			case 'renameTemplateParameter' :
				if (!array_key_exists('template', $paramArray)) {
					return "template missing";
				}
				$template = $paramArray['template'];

				if (!array_key_exists('old_parameter', $paramArray)) {
					return "old_parameter missing";
				}
				$old_parameter = $paramArray['old_parameter'];
				if (!array_key_exists('new_parameter', $paramArray)) {
					return "new_parameter missing";
				}
				$new_parameter = $paramArray['new_parameter'];
				$titles = explode("%%", $paramArray['titles']);

				$op = new SRFChangeTemplateOperation($titles, $template, $old_parameter, $new_parameter);

				break;
		}

		$num = $op->getNumberOfAffectedPages();
		$op->setBot($this);
		$this->setNumberOfTasks(1);
		$this->addSubTask($num);

		$logMessages=array();
		$op->refactor(true, $logMessages);

		ksort($logMessages);
		$log = "";
		foreach($logMessages as $prefixedTitle => $lm_array) {
			$log .= "\n*".SRFLog::titleAsWikiText(Title::newFromText($prefixedTitle));
			foreach($lm_array as $lm) {
				$log .= "\n**".$lm->asWikiText();
			}
		}

		return $log;
	}
}

