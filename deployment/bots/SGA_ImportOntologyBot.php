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
 * @ingroup ImportOntologyBot
 *
 * @defgroup ImportOntologyBot
 * @ingroup SemanticGardeningBots
 *
 * @author Kai K�hn
 * Created on 03.07.2007
 *
 * Author: kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");

define('XML_SCHEMA_NS', 'http://www.w3.org/2001/XMLSchema#');

class ImportOntologyBot extends GardeningBot {

	function ImportOntologyBot() {
		parent::GardeningBot("smw_importontologybot");
	}

	public function getHelpText() {
		if ($this->canBeRun()) {
			return wfMsg('smw_gard_import_docu');
		} else {
			return "<div>".wfMsg('smw_df_missing')."<a title=\"Deployment Framework\"".
                    "href=\"http://smwforum.ontoprise.com/smwforum/index.php/Help:Installing_Deployment_Framework\"".
                    ">Deployment Framework</a></div>";

		}
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function createParameters() {
		$param1 = new GardeningParamFileList('GARD_IO_FILENAME', "", SMW_GARD_PARAM_REQUIRED, array('owl','obl'));
		return array($param1);
	}

	public function run($paramArray, $isAsync, $delay) {

		// do not allow to start synchronously.
		if (!$isAsync) {
			return "Can not start asynchronously.";
		}

		$fileName = urldecode($paramArray['GARD_IO_FILENAME']);
		$fileTitle = Title::newFromText($fileName, NS_FILE);
		$fileLocation = wfFindFile($fileTitle)->getPath();

		global $IP;
		chdir($IP.'/deployment/tools');
			
		if (isset(DF_Config::$settings['df_php_executable']) && DF_Config::$settings['df_php_executable'] != '') {
			$phpExe = '"'.DF_Config::$settings['df_php_executable'].'"';
		} else {
			$phpExe = "php";
		}
		print "\nImport file: $fileLocation";
		exec($phpExe.' '.$IP.'/deployment/tools/smwadmin/smwadmin.php -i "'.$fileLocation.'" --nocheck', $out, $ret);
		$statusText = implode("\n", $out);
		return $statusText;
	}

	public function canBeRun() {
		global $IP;
		$filename = "$IP/deployment/tools/onto2mwxml";
		return (file_exists($filename) && is_dir($filename));

	}

}
/*
 * Note: This bot filter has no real functionality. It is just a dummy to
 * prevent error messages in the GardeningLog. There are no gardening issues
 * about importing. Instead there's a textual log.
 *
 */
define('SMW_IMPORTONTOLOGY_BOT_BASE', 700);

class ImportOntologyBotFilter extends GardeningIssueFilter {



	public function __construct() {
		parent::__construct(SMW_IMPORTONTOLOGY_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'));

	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {

	}

	public function getData($options, $request) {
		parent::getData($options, $request, 0);
	}
}



// For importing an ontology please do not use the ImportBot any longer.
// Instead use the deployment framework: smwadmin -i <ontology-file>
// This will read the ontology and create appropriate wiki pages.
