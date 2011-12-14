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

global $rootDir;
require_once $rootDir.'/tools/maintenance/maintenanceTools.inc';
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");
require_once($rootDir."/io/DF_PrintoutStream.php");




define ("DF_SMWFORUM_LINK_MISSING_DEPENDENCY", "http://smwforum.ontoprise.com/smwforum/index.php/Help:Troubleshooting_for_common_problems_with_the_Deployment_Framework#Missing_dependency_in_an_extension");
define ("DF_SMWFORUM_LINK_MISSING_LOCALSETTINGS", "http://smwforum.ontoprise.com/smwforum/index.php/Help:Troubleshooting_for_common_problems_with_the_Deployment_Framework#No_LocalSettings.php");
define ("DF_SMWFORUM_LINK_MISSING_ADMINSETTINGS", "http://smwforum.ontoprise.com/smwforum/index.php/Help:Troubleshooting_for_common_problems_with_the_Deployment_Framework#No_AdminSettings.php");
define ("DF_SMWFORUM_LINK_MISSING_NON_REGISTERED", "http://smwforum.ontoprise.com/smwforum/index.php/Help:Troubleshooting_for_common_problems_with_the_Deployment_Framework#An_extension_is_installed_but_not_referenced_in_the_LocalSettings.php");
define ("DF_SMWFORUM_LINK_MISSING_NON_EXISTING", "http://smwforum.ontoprise.com/smwforum/index.php/Help:Troubleshooting_for_common_problems_with_the_Deployment_Framework#An_extension_is_referenced_but_not_installed");
define ("DF_SMWFORUM_LINK_MISSING_DOUBLE_REGISTERED", "http://smwforum.ontoprise.com/smwforum/index.php/Help:Troubleshooting_for_common_problems_with_the_Deployment_Framework#An_extension_is_referenced_twice");
define ("DF_SMWFORUM_LINK_EXISTING_EXTENSION", "http://smwforum.ontoprise.com/smwforum/index.php/Help:Troubleshooting_for_common_problems_with_the_Deployment_Framework#Already_existing_extensions");



/**
 * @file
 * @ingroup DFMaintenance
 *
 * Utility class which contains various methods for maintenance operations.
 *
 * @author: Kai Kuehn
 */
class MaintenanceTools {

	/**
	 * Checks if there are unresolved dependencies. Returns false if there
	 * are unresolved dependencies and true if not. (Textual) details are returned
	 * in $out.
	 *
	 * @param array of DeployDescriptor $localPackages
	 * @param array of string & $out
	 * @return boolean
	 */
	public static function checkDependencies($localPackages, & $out) {
		$errorFound = false;
		$out = array();
		foreach($localPackages as $p) {
			$errorInExt = false;
			$out[$p->getID()]= array();
			$dependencies = $p->getDependencies();
			foreach($dependencies as $dep) {
				if ($dep->isOptional())  {
					// ignore optional extensions
					continue;
				}

				// check if dependant package exists
				$id = $dep->isContained($localPackages);
				if ($id === false) {
					$out[$p->getID()][]= "Extension missing: ".$dep->toString();
					$errorInExt = true;
					$errorFound |= $errorInExt;
					continue;
				}

				// check if dependant package has correct version
				$ext = $localPackages[$id];
				if ($ext->getVersion()->isLower($dep->getMinVersion()) || $ext->getVersion()->isHigher($dep->getMaxVersion())) {
					$out[$p->getID()][]=  "Dependency to '".$id."' has wrong version: ".$ext->getVersion()->toVersionString().". \n\t\tExpected range ".$dep->getMinVersion()->toVersionString()." - ".$dep->getMaxVersion()->toVersionString();
					$errorInExt = true;
					$errorFound |= $errorInExt;
					continue;
				}
			}
			if (!$errorInExt) $out[$p->getID()][]= NULL;
		}
		return $errorFound;
	}
}

/**
 * * Checks an installation for common consistency problems.
 *
 *  1. Unresolved dependencies
 *  2. Inconsistent LocalSettings.php entries
 *  	- existing extensions which are not registered
 *  	- registered extensions which do not exist
 *  	- double registered extension
 *  	- double require/include statements
 *  3. Special config checks
 *  	- If the rule extension is installed, the triplestore must be installed as well.
 *
 * @author: Kai Kuehn
 */
class ConsistencyChecker {

	var $rootDir;
	var $localPackages;
	var $statusLog;

	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
		$this->localPackages = PackageRepository::getLocalPackages($this->rootDir);
		$this->statusLog = array();
	}

	static $instance;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new ConsistencyChecker($rootDir);
		}
		return self::$instance;
	}

	public function getStatusLog() {
		return $this->statusLog;
	}

	public function checkInstallation($format, $addChecks = true) {
        global $dfgOut;
		$this->statusLog[] = $dfgOut->getln(array("\n","The checkInstallation tool helps you with identifying some common problems which may occur with the Wiki Administration Tool, e.g. missing dependencies. It verifies that your installation is stable and configured correctly. You should use this tool after the installation of SMW+, when installing a Halo extension and also if there are any problems with your installation.","\n"));
		$errorFound = false;
		$errorFound |= $this->checkDependencies($format);
		$errorFound |= $this->checkLocalSettings($format);
		$errorFound |= $this->checkSpecialConfigs();
		$errorFound |= $this->checkExistingExtensions($format, $addChecks);
		$this->statusLog[] = $dfgOut->getln("\n\n", $format);
		return $errorFound;
	}


	/**
	 * Checks for missing dependencies of deploy descriptors.
	 *
	 */
	public  function checkDependencies($format) {
   global $dfgOut;
		if (count($this->localPackages) == 0) {
			$this->statusLog[] = $dfgOut->getln(array("\n","NO extensions found!", "\n"));
		}

		$this->statusLog[] = $dfgOut->getln(array("\n","Are the dependencies of all installed extensions consistent?..."));
		$errorFound = MaintenanceTools::checkDependencies($this->localPackages, $out);


		if ($errorFound) {
			foreach($out as $ext => $line) {
				//if (!is_null(reset($line))) $this->statusLog[] = "\n\n$ext: ";
				foreach($line as $l) {
					if (is_null($l)) break; else $this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]", $ext.":".$l));
				}
			}
			$this->statusLog[] = $dfgOut->getln(array("\n"));
		} else {
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t", "none."));
		}
		if (!$errorFound) {
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));

		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_DEPENDENCY, "Read here how to resolve this error: Dependency issue"));
			}
		}
		return $errorFound;
	}

	/**
	 * Checks for available updates in the current installation.
	 * 
	 * @return array of DeployDescriptor
	 */
	public function checksForUpdates() {
		
		$localPackages = PackageRepository::getLocalPackages($this->rootDir);

		// iterate through all installed packages, check if new or patched versions
		// are available 
		$updatesNeeded = array();
		$latestPackages = PackageRepository::getAllPackages();
		foreach($localPackages as $tl_ext) {
            
			// ignore only-local packages
			if (!array_key_exists($tl_ext->getID(), $latestPackages)) continue;
			
			$tuple = reset($latestPackages[$tl_ext->getID()]);
			if ($tuple === false) continue;
			list($latestVersion, $latestPatchlevel) = $tuple;

			if ($latestVersion->isHigher($localPackages[$tl_ext->getID()]->getVersion())
			|| ($latestVersion->isEqual($localPackages[$tl_ext->getID()]->getVersion())
			&& $latestPatchlevel > $localPackages[$tl_ext->getID()]->getPatchlevel())) {
				
				$updatesNeeded[$tl_ext->getID()] = array($tl_ext->getID(), $latestVersion, $latestPatchlevel) ;
			}

		}
		
		return $updatesNeeded;

	}

	/**
	 * Checks LocalSettings.php for common problems.
	 *
	 */
	private function checkLocalSettings($format) {
   global $dfgOut;
		//
		// check if LocalSettings.php exists
		//
		$this->statusLog[] = $dfgOut->getln(array("\n","Does LocalSettings.php exist?...", "\n"));
		$errorFound = !file_exists($this->rootDir."/LocalSettings.php");
		if ($errorFound) {
			$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]", "LocalSettings.php does not exist", "\n"));
			return true;
		}
		$this->statusLog[] = $dfgOut->getln(array("\t", "done."));
		if (!$errorFound) {
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_LOCALSETTINGS, "Get help: Missing LocalSettings.php"));
			}
		}

		//
		// check if AdminSettings.php exists
		//
		if( !defined( 'MEDIAWIKI' ) ) {
			global $mwRootDir;
			$version = Tools::getMediawikiVersion($mwRootDir);
			$version = intval(str_replace(".","",$version));
		} else {
			global $wgVersion;
			$version = intval(str_replace(".","",$wgVersion));
		}

		// only check for AdminSettings.php if MW version is < 1.16
		if ($version < 1160) {
			$this->statusLog[] = $dfgOut->getln(array("\n","Does AdminSettings.php exist?...", "\n"));
			$errorFound =!file_exists($this->rootDir."/AdminSettings.php");
			if ($errorFound) {
				$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]", "AdminSettings.php does not exist", "\n"));

				return true;
			}
			$this->statusLog[] = $dfgOut->getln(array("\t", "done."));
			if (!$errorFound) {
				$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));
			}else {
				if ($format == DF_OUTPUT_FORMAT_HTML)    {
					// in case of HTML write a link to the SMW-Forum
					$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_ADMINSETTINGS, "Get help: Missing AdminSettings.php"));
				}
			}
		}
		//
		// check if LocalSettings.php is not empty
		//
		$this->statusLog[] = $dfgOut->getln(array("\n","Does LocalSettings.php have content?...", "\n"));
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		$errorFound = trim($ls) == '';
		if (trim($ls) == '') {
			$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]", "LocalSettings.php exists but is empty", "\n"));

			return true;
		}
		$this->statusLog[] = $dfgOut->getln(array("\t", "done."));
		if (!$errorFound) $this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));

		//
		// check if existing extensions are registered in LocalSettings.php
		//
		$this->statusLog[] = $dfgOut->getln(array("\n","Are all existing extensions also registered in LocalSettings.php?...","\n"));
		$errorFound1 = false;
		foreach($this->localPackages as $p) {
			if ($p->getID() == 'mw') continue;

			$start = strpos($ls, "/*start-".$p->getID()."*/");
			$end = strpos($ls, "/*end-".$p->getID()."*/");

			if ($start === false && $end === false) {
				$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED] ", $p->getID()." is not configured."));
				$errorFound1 = true;


			} else {
				if ($start === false) {
					$this->statusLog[] =  $dfgOut->getln(array("\n","\t","[FAILED]", "Start tag missing: ".$p->getID()));
					$errorFound1 = true;
				}
				if ($end === false) {
					$this->statusLog[] =  $dfgOut->getln(array("\n","\t","[FAILED]", "End tag missing: ".$p->getID()));
					$errorFound1 = true;
				}
			}
		}

		if (!$errorFound1) {
			$this->statusLog[] = "\n\tnone.";
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_NON_REGISTERED, "Read here how to resolve this error: register extensions"));
			}
		}

		//
		// check if there are registerings for non-existings extensions
		//
		$this->statusLog[] = $dfgOut->getln(array("\n","\n","Are there registerings for non-existings extensions?..."));
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		$errorFound2 = false;
		foreach ($matches[1] as $m) {
			if (!array_key_exists($m, $this->localPackages)) {
				if ($m === 'deploymentframework') continue; // ignore the DF itself
				$errorFound2 = true;
				$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","configuration for non-existing extension detected: $m"));


			}
		}

		if (!$errorFound2) {
			$this->statusLog[] = $dfgOut->getln(array("\n","\t","none."));
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_NON_EXISTING, "Read here how to resolve this error: remove configuration of a non-existing extension. "));
			}
		}

		//
		// check if there are double registered extensions
		//
		$this->statusLog[] = $dfgOut->getln(array("\n","\n","Are there double configuration blocks in the LocalSettings.php?..."));
		$errorFound3 = false;
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		$ext_counts = array_count_values($matches[1]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound3 = true;
				$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","double registered extension detected: $ext"));

			}
		}
		if (!$errorFound3) {
			$this->statusLog[] = $dfgOut->getln(array("\n","\t","none."));
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML) {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_DOUBLE_REGISTERED, "Read here how to resolve this error: remove configuration of a non-existing extension."));
			}
		}

		//
		// check if there are double require/include statements
		//
		$this->statusLog[] = $dfgOut->getln(array("\n","\n","Are there double references to an extension in the LocalSettings.php? ..."));
		$errorFound4 = false;
		preg_match_all('/(require|include)(_once)?\s*\(\s*["\']([^"\']*)["\']\s*\)/', $ls, $matches);
		for($i = 0; $i < count($matches[3]); $i++) $matches[3][$i] = trim($matches[3][$i]);
		$ext_counts = array_count_values($matches[3]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound4 = true;
				$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]"," double registered extension detected: $ext"));

			}
		}
		if (!$errorFound4) {
			$this->statusLog[] = $dfgOut->getln(array("\n","\t","none."));
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML) {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_DOUBLE_REGISTERED, "Read here how to resolve this error: Double registered extensions"));
			}
		}

		return $errorFound1 || $errorFound2 || $errorFound3 || $errorFound4;
	}

	/**
	 * Contains several specific tests on problems already occured. Can be extended in future.
	 *
	 */
	private function checkSpecialConfigs() {
		   global $dfgOut;
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		preg_match_all('/(require|include)(_once)?\s*\(\s*["\']([^"\']*)["\']\s*\)/', $ls, $matches);
		for($i = 0; $i < count($matches[3]); $i++) $matches[3][$i] = trim($matches[3][$i]);

		// check if semantic rule extension AND triplestore is installed.
		if (in_array("extensions/SemanticRules/includes/SR_Initialize.php", $matches[3])) {
			if (strpos($ls, "smwgHaloWebserviceEndpoint") === false) {
				$errorFound=true;
				$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Rule extension installed but no triplestore is active."));

			}
		}
	}

	/**
	 * Checks if there are Halo extensions installed which are not handled by the DF.
	 *
	 * @param int $format
	 * @param boolean $addChecks
	 */
	private function checkExistingExtensions($format, $addChecks = true) {
		   global $dfgOut;
		if (!$addChecks) return false;

		global $wgExtensionCredits;
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		$this->statusLog[] = $dfgOut->getln(array("\n","\n","check if there are already existing extensions..."));
		$errorFound=false;
		foreach($wgExtensionCredits as $extType) {
			foreach($extType as $ext) {

				if (array_key_exists('name', $ext)) {
					$name = $ext['name'];
					switch($name) {
						case 'Semantic MediaWiki':

							if (strpos($ls, '/*start-smw*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Semantic Mediawiki is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'SMWHalo&nbsp;Extension':

							if (strpos($ls, '/*start-smwhalo*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","SMWHalo Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'ScriptManager&nbsp;Extension':
							if (strpos($ls, '/*start-scriptmanager*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","ScriptManager Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Rich&nbsp;Media&nbsp;Extension':
							if (strpos($ls, '/*start-richmedia*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Richmedia Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Semantic Forms':
							if (strpos($ls, '/*start-semanticforms*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","SemanticForms Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Semantic Forms Input Types':
							if (strpos($ls, '/*start-semanticformsinputs*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","SemanticFormsInputs Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Semantic Notifications':
							if (strpos($ls, '/*start-semanticnotifications*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Semantic Notifications Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Semantic Gardening extension':
							if (strpos($ls, '/*start-semanticgardening*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Semantic Gardening Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Semantic Result Formats':
							if (strpos($ls, '/*start-semanticresultformats*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Semantic Result Formats Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'SemanticRules&nbsp;Extension':
							if (strpos($ls, '/*start-semanticrules*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Semantic Rules Formats Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Semantic Treeview':
							if (strpos($ls, '/*start-treeview*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Treeview Formats Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'WYSIWYG extension':
							if (strpos($ls, '/*start-wysiwyg*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","WYSIWYG Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Semantic&nbsp;UltraPedia&nbsp;Extension':
							if (strpos($ls, '/*start-ultrapedia*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Ultrapedia Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'LinkedData':
							if (strpos($ls, '/*start-linkeddata*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","LinkedData Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'HaloACL':
							if (strpos($ls, '/*start-haloacl*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","HaloACL Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Enhanced Retrieval extension':
							if (strpos($ls, '/*start-enhancedretrieval*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","UnifiedSearch Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Data&nbsp;Import&nbsp;Extension':
							if (strpos($ls, '/*start-dataimport*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","DataImport Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Collaboration':
							if (strpos($ls, '/*start-collaboration*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Collaboration Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'Automatic&nbsp;Semantic&nbsp;Forms':
							if (strpos($ls, '/*start-automaticsemanticforms*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Automatic Semanticforms Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'ARCLibrary':
							if (strpos($ls, '/*start-arclibrary*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","ARCLibrary Extension is already installed but not visible to DF."));
								$errorFound=true;
							}
							break;
						case 'ParserFunctions':
						case 'StringFunctions':
						case 'URL Arguments':
						case 'Variables':
							if (strpos($ls, '/*start-applicationprogramming*/') === false) {
								$this->statusLog[] = $dfgOut->getln(array("\n","\t","[FAILED]","Application programming Extension is already installed (or extensions which are contained in this) but not visible to DF."));
								$errorFound=true;
							}
							break;

					}
				}
			}
		}
		if (!$errorFound) {
			$this->statusLog[] = $dfgOut->getln(array("\n","\t","none."));
			$this->statusLog[] = $dfgOut->getln(array("\n", "\t[OK]"));
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML) {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_EXISTING_EXTENSION, "Get help: Existing extensions"));
			}
			$this->statusLog[] = $dfgOut->getln(array("\n\n", "\tTry to run: deployment/tools/maintenance/checkInstallation.php --lift"));
		}

	}




	/**
	 * To lift an installation means to make it fit for the DF. That means:
	 *
	 * 	- create a deploy descriptor for the extension
	 *  - change LocalSettings.php (add DF tags)
	 *
	 */
	public function liftInstallation($format = DF_OUTPUT_FORMAT_TEXT) {

		global $wgExtensionCredits;
   global $dfgOut;

		$lifted = false;
		foreach($wgExtensionCredits as $extType) {
			foreach($extType as $ext) {

				if (array_key_exists('name', $ext)) {
					$name = $ext['name'];
					if (!array_key_exists('version', $ext)) continue;
					list($version) = explode(" ", $ext['version']);
					$version = str_replace(".","", $version);
					$version = reset(explode("_", $version));

					if (stripos($name, "semantic") !== false && stripos($name, "mediawiki") !== false) {
						$lifted |= $this->liftSMW($version, $format);
					} else if (stripos($name, "semantic") !== false && stripos($name, "forms") !== false) {
						$lifted |= $this->liftSF($version, $format);
					}
				}
			}
		}
		if (!$lifted) {
			$this->statusLog[] = $dfgOut->getln(array("\nNothing to do.\n"));
		} else {
			$this->statusLog[] = $dfgOut->getln(array("\nPlease visit:\nhttp://smwforum.ontoprise.com/smwforum/index.php/Help:Lifting_installation_for_use_with_DF\n"));
		}
	}

	/**
	 * Lifts the Semantic Mediawiki (SMW) extension, ie. makes it compatible to DF.
	 *
	 * 	adds a deploy.xml
	 *  adds DF tags to LocalSettings.php
	 *
	 * @param int $version
	 * @param int $format
	 */
	private function liftSMW($version, $format) {
		global $smwgIP;
		   global $dfgOut;
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		if (strpos($ls, '/*start-smw*/') !== false) return false;
		// create deploy descriptor
		$dd = file_get_contents("resources/dd_templates/deploy_smw.xml");
		$dd = str_replace("{{version}}", $version, $dd);
		$this->statusLog[] = $dfgOut->getln(array("\n","\t[done]","Creating SMW deploy descriptor."));

		$handle = fopen($smwgIP."deploy.xml", "w");
		if ($handle !== false) {
			fwrite($handle, $dd);
			fclose($handle);
		}

		// change LocalSetings.php
		$startIndex = strpos($ls, "/SemanticMediaWiki/includes/SMW_Settings.php");
		if ($startIndex === false) {
			$startIndex = strpos($ls, "/SemanticMediaWiki/SemanticMediaWiki.php");
			if ($startIndex === false) {
				$this->statusLog[] = $dfgOut->getln(array("\n","\t[FAILED]","Adding DF tags to LocalSettings.php not possible.", "\n"));
				return false;
			}
		}
		$startIndex = strrpos(substr($ls,0, $startIndex), "\n");
		$endIndex = strpos($ls, "enableSemantics");
		if ($endIndex === false) {
			$this->statusLog[] = $dfgOut->getln(array("\n","\t[FAILED]","Adding DF tags to LocalSettings.php not possible.", "\n"));
			return false;
		}
		$endIndex = strpos(substr($ls,$endIndex), "\n")+$endIndex;

		$ls = substr($ls, 0, $startIndex) . "\n/*start-smw*/\n" . substr($ls, $startIndex, $endIndex-$startIndex) . "\n/*end-smw*/\n" . substr($ls, $endIndex);
		$handle = fopen($this->rootDir."/LocalSettings.php", "w");
		if ($handle !== false) {
			fwrite($handle, $ls);
			fclose($handle);
		}
		$this->statusLog[] = $dfgOut->getln(array("\n","\t[done]","Adding DF tags to LocalSettings.php for SMW.", "\n"));
		return true;
	}

	/**
	 * Lifts the SemanticForms (SF) extension, ie. makes it compatible to DF
	 *
	 *  adds a deploy.xml
	 *  adds DF tags to LocalSettings.php
	 *
	 * @param int $version
	 * @param int $format
	 */
	private function liftSF($version, $format) {
		global $sfgIP;
		   global $dfgOut;
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		if (strpos($ls, '/*start-semanticforms*/') !== false) return false;
		// create deploy descriptor
		$dd = file_get_contents("resources/dd_templates/deploy_sf.xml");
		$dd = str_replace("{{version}}", $version, $dd);
		$this->statusLog[] = $dfgOut->getln(array("\n","\t[done]","Creating SemanticForms deploy descriptor."));

		$handle = fopen($sfgIP."/deploy.xml", "w");
		if ($handle !== false) {
			fwrite($handle, $dd);
			fclose($handle);
		}
		// change LocalSetings.php
		$startIndex = strpos($ls, "/SemanticForms/includes/SF_Settings.php");
		if ($startIndex === false) {
			$startIndex = strpos($ls, "/SemanticForms/SemanticForms.php");
			if ($startIndex === false) {
				$this->statusLog[] = $dfgOut->getln(array("\n","\t[FAILED]","Adding DF tags to LocalSettings.php not possible.", "\n"));
				return false;
			}
		}
		$startIndex = strrpos(substr($ls,0, $startIndex), "\n");
		$endIndex = strpos($ls, "/SemanticForms/includes/SF_Settings.php");
		if ($endIndex === false) {
			$endIndex = strpos($ls, "/SemanticForms/SemanticForms.php");
			if ($endIndex === false) {
				$this->statusLog[] = $dfgOut->getln(array("\n","\t[FAILED]","Adding DF tags to LocalSettings.php not possible.", "\n"));
				return false;
			}
		}
		$endIndex = strpos(substr($ls,$endIndex), "\n")+$endIndex;

		$ls = substr($ls, 0, $startIndex) . "\n/*start-semanticforms*/\n" . substr($ls, $startIndex, $endIndex-$startIndex) . "\n/*end-semanticforms*/\n" . substr($ls, $endIndex);
		$handle = fopen($this->rootDir."/LocalSettings.php", "w");
		if ($handle !== false) {
			fwrite($handle, $ls);
			fclose($handle);
		}
		$this->statusLog[] = $dfgOut->getln(array("\n","\t[done]","Adding DF tags to LocalSettings.php for SemanticForms.", "\n"));
		return true;

	}

	

	/**
	 * Creates an HTML link.
	 *
	 * @param array $link
	 */
	private function linkout($link) {
		list($href, $label) = $link;
		return "<div class=\"df_checkinst_links\"><a href=\"".htmlspecialchars($href)."\">$label</a></div>";
	}
}