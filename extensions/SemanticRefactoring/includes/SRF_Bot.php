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

/**
 * Exports object logic from TSC.
 *
 * @author kuehn
 *
 */
class SMWRFRefactoringBot extends GardeningBot {

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

	public function run($paramArray, $isAsync, $delay) {

		// do not allow to start synchronously.
		if (!$isAsync) {
			return "RefactoringBot should not be executed synchronously!";
		}
		
		if (!array_key_exists('operation', $paramArray)) {
			return "Refactoring operation not specified.";
		}
		
		$operation = $paramArray['operation'];
		
		$parameters = array();
	    if (!array_key_exists('parameters', $paramArray)) {
            $parameters = new stdClass();
        } else {
        	$parameters = json_decode($paramArray['parameters']);
        }
        
        switch($operation) {
        	case 'renameProperty': 
        		$oldProperty = $parameters->oldProperty;
        		$newProperty = $parameters->newProperty;
        		
        		$op = new SMWRFRenamePropertyOperation($oldProperty, $newProperty);
        		$num = $op->getNumberOfAffectedPages();
        		break;
        	case 'renameCategory' :
        		//TODO: add others
        		break;	
        }
        $op->setBot($this);
        $this->totalWork($num);
        
        $logMessages=array();
        $op->refactor(true, $logMessages);
        
        return implode("\n*", $logMessages);
	}
}