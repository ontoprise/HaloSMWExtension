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

require_once($srefgIP . '/includes/operations/SRF_InstanceLevelOperation.php');
require_once($srefgIP . '/includes/operations/SRF_TouchpageOperation.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeCategoryValue.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeTemplate.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeTemplateName.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeTemplateParameter.php');
require_once($srefgIP . '/includes/operations/SRF_ChangeValue.php');
require_once($srefgIP . '/includes/operations/SRF_DeleteCategory.php');
require_once($srefgIP . '/includes/operations/SRF_DeleteProperty.php');
require_once($srefgIP . '/includes/operations/SRF_RenameOperation.php');
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
		return ""; // does not appear on Special:Gardening so never mind.
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function isVisible() {
		return false;
	}
	
    public function runParallel() {
        return false;
    }

	/**
	 * Returns an array
	 */
	public function createParameters() {
		return array();
	}

	/**
	 * Creates a comment depending on the given parameters.
	 *
	 * @param mixed $parameters
	 * @return string
	 */
	public function getComment($parameters) {

		$operation = array();
		if (is_array($parameters)) {

			// normal bot call
			return $this->getMessageText($parameters);

		} else {
			$result = "";
			// user defined paramArray
			$commands = $parameters->commands;
			foreach($commands as $c) {
				$paramArray = GardeningBot::convertParamStringToArray($c);
				$result .= "\n".$this->getMessageText($paramArray);
			}
		}

		return trim($result);
	}

	private static function createLink($name, $ns = NS_MAIN, $showNamespace = true) {
		$title = Title::newFromText( $name, $ns );
		$url = $title->getFullURL();
		$displayText = $showNamespace ? $title->getPrefixedText() : $title->getText();
		return '<a href="'.$url.'">'.$displayText.'</a>';
	}

	/**
	 * Returns a human-readible message for an operation
	 * description as key-value pairs.
	 *
	 * @param array $paramArray
	 * @return string|Ambigous <String:, string, mixed>
	 */
	private function getMessageText($paramArray) {
		$op = $paramArray['SRF_OPERATION'];
		$msg = 'sref_comment_'.strtolower($op);
		switch($op) {
			case 'touchPages':
				return wfMsg($msg);
				break;
			case 'renameInstance':
				if (!array_key_exists('oldInstance', $paramArray)) {
					return "Old instance missing";
				}
				$oldInstance = $paramArray['oldInstance'];

				if (!array_key_exists('newInstance', $paramArray)) {
					return "New instance missing";
				}
				$newInstance = $paramArray['newInstance'];

				return wfMsg($msg, self::createLink($oldInstance), self::createLink($newInstance));
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
				return wfMsg($msg, self::createLink($oldProperty, SMW_NS_PROPERTY), self::createLink($newProperty, SMW_NS_PROPERTY));
					

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
				return wfMsg($msg, self::createLink($oldCategory, NS_CATEGORY), self::createLink($newCategory, NS_CATEGORY));

				break;
			case 'deleteCategory' :
				if (!array_key_exists('category', $paramArray)) {
					return "Category missing";
				}
				$category = $paramArray['category'];

				return wfMsg($msg,  self::createLink($category, NS_CATEGORY));

				break;
			case 'deleteProperty' :
				if (!array_key_exists('property', $paramArray)) {
					return "Property missing";
				}
				$property = $paramArray['property'];
					
				return wfMsg($msg, self::createLink($property, SMW_NS_PROPERTY));
				break;

			case 'addCategory':
				if (!array_key_exists('category', $paramArray)) {
					return '';
				}
				$category = $paramArray['category'];
				return wfMsg($msg,  self::createLink($category, NS_CATEGORY));
				break;
			case 'removeCategory':
				if (!array_key_exists('category', $paramArray)) {
					return '';
				}
				$category = $paramArray['category'];
				return wfMsg($msg,  self::createLink($category, NS_CATEGORY));
				break;
			case 'replaceCategory':
				if (!array_key_exists('old_category', $paramArray)) {
					return '';
				}
				$old_category = $paramArray['old_category'];
				if (!array_key_exists('new_category', $paramArray)) {
					return '';
				}
				$new_category = $paramArray['new_category'];
				return wfMsg($msg,  self::createLink($old_category, NS_CATEGORY), self::createLink($new_category, NS_CATEGORY));
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

				return wfMsg($msg,  self::createLink($property, SMW_NS_PROPERTY, false), $value);
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
				return wfMsg($msg,  self::createLink($property, SMW_NS_PROPERTY, false), $value);

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
				return wfMsg($msg,  self::createLink($property, SMW_NS_PROPERTY, false), $old_value, $new_value);

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
				return wfMsg($msg,  self::createLink($property, SMW_NS_PROPERTY, false), $value);

				break;
			case 'addValueOfTemplate':
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

				return wfMsg($msg,  self::createLink($template, NS_TEMPLATE), $parameter, $value);

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

				return wfMsg($msg,  self::createLink($template, NS_TEMPLATE), $parameter, $value);

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
				return wfMsg($msg,  self::createLink($template, NS_TEMPLATE), $parameter, $old_value, $new_value);
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

				return wfMsg($msg,  self::createLink($template, NS_TEMPLATE), $old_parameter, $new_parameter);
				break;

			case 'renameTemplate' :
				if (!array_key_exists('old_template', $paramArray)) {
					return "old template missing";
				}
				$old_template = $paramArray['old_template'];

				if (!array_key_exists('new_template', $paramArray)) {
					return "new template missing";
				}
				$new_template = $paramArray['new_template'];

				return wfMsg($msg,  self::createLink($old_template, NS_TEMPLATE), self::createLink($new_template, NS_TEMPLATE));

				break;
		}
	}

	private function getOperation($operation, $titles, $paramArray) {
		switch($operation) {
			case 'touchPages':
					
				$op = new SRFTouchpageOperation($titles);

				break;
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
					

				$op = new SRFChangeCategoryValueOperation($titles, NULL, $category);

				break;

			case 'removeCategory' :
				if (!array_key_exists('category', $paramArray)) {
					return "Category missing";
				}
				$category = $paramArray['category'];
					

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


				$op = new SRFChangeValueOperation($titles, $property, NULL, $value, true);

				break;

			case 'addValueOfTemplate' :
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


				$op = new SRFChangeTemplateParameterOperation($titles, $template, $parameter, NULL, $value, false);

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


				$op = new SRFChangeTemplateOperation($titles, $template, $old_parameter, $new_parameter);

				break;

			case 'renameTemplate' :
				if (!array_key_exists('old_template', $paramArray)) {
					return "old template missing";
				}
				$old_template = $paramArray['old_template'];

				if (!array_key_exists('new_template', $paramArray)) {
					return "new template missing";
				}
				$new_template = $paramArray['new_template'];

				$op = new SRFChangeTemplateNameOperation($titles, $old_template, $new_template);

				break;
		}
		return $op;
	}

	public function run($paramArray, $isAsync, $delay) {
			
		// do not allow to start synchronously.
		if (!$isAsync) {
			return "RefactoringBot should not be executed synchronously!";
		}

		$ops = array();
		if (is_array($paramArray)) {

			// normal bot call, $paramArray is a hash array
			$operation = $paramArray['SRF_OPERATION'];
			$op = $this->getOperation($operation, NULL, $paramArray);
			$num = $op->getWork();
			$op->setBot($this);
			$this->setNumberOfTasks(1);
			$this->addSubTask($num);

			$logMessages=array();
			$op->refactor(true, $logMessages);

		} else {

			// user defined, paramArray is an object
			$commands = $paramArray->commands;
			$titles = $paramArray->titles;

			// assuming that operations are ALL instance level operations!
			foreach($commands as $c) {
				$paramArray = GardeningBot::convertParamStringToArray($c);
				$operation = $paramArray['SRF_OPERATION'];
				$ops[] = $this->getOperation($operation, $titles, $paramArray);
			}

			if (count($ops) == 0) {
				return "No operations specified.";
			}

			// use first operation to indicate bot progress
			$op = $ops[0];
			$num = $op->getWork();
			$op->setBot($this);
			$this->setNumberOfTasks(1);
			$this->addSubTask($num);

			$logMessages=array();
			SRFRefactoringOperation::applyOperations(true, $titles, $ops, $logMessages);
		}

		// print messages
		ksort($logMessages);
		$log = "";
		foreach($logMessages as $prefixedTitle => $lm_array) {
			$log .= "\n*".SRFLog::titleAsWikiText(Title::newFromText($prefixedTitle));
			foreach($lm_array as $lm) {
				$log .= "\n**".$lm->asWikiText();
			}
		}

		if (trim($log) == '') {
			$log = "Nothing done.";
		}
		return $log;
	}
}

