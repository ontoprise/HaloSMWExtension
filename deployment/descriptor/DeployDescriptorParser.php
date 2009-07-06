<?php


require_once ('DeployDescriptorProcessor.php');

/**
 * @author: Kai Kühn / ontoprise / 2009
 *
 * This class works as a parser (and serializer) of the general
 * description of a deployable entity (aka deploy descriptor).
 *
 *
 */
class DeployDescriptorParser {

	// extracted data from deploy descriptor
	var $globalElement;
	var $codefiles;
	var $wikidumps;
	var $resources;
	var $configs;
	var $precedings;
	var $userReqs;
	var $dependencies;
	var $install_scripts;
	var $uninstall_scripts;
	var $patches;
	var $uninstallpatches;

	// xml
	var $dom;
	var $wikidumps_xml;
	var $codefiles_xml;
	var $resources_xml;

	function __construct($xml, $fromVersion = NULL) {

		// parse xml results
		$this->dom = simplexml_load_string($xml);

		$this->globalElement = $this->dom->xpath('/deploydescriptor/global');
		$this->codefiles_xml = $this->dom->xpath('/deploydescriptor/codefiles/file');

		$this->wikidumps_xml = $this->dom->xpath('/deploydescriptor/wikidumps/file');
		$this->resources_xml = $this->dom->xpath('/deploydescriptor/resources/file');
		$this->createConfigElements($fromVersion); // assume new config, not update
	}


	public function getPrecedings() {
		return $this->precedings;
	}

	public function getConfigs() {
		return $this->configs;
	}

	public function getInstallScripts() {
		return $this->install_scripts;
	}

	public function getUninstallScripts() {
		return $this->uninstall_scripts;
	}

	private function createConfigElements($from = NULL) {

		$this->configs = array();
		$this->precedings = array();
		$this->install_scripts = array();
		$this->uninstall_scripts = array();
		$this->userReqs = array();
		$this->patches = array();
		$this->uninstallpatches = array();

		if ($from == NULL) {
			$path = "/deploydescriptor/configs/new";
		} else {

			$path = "//update[@from='$from']";

			$update = $this->dom->xpath($path);
			if (count($update) === 0) {
				// if update config missing, do not use anything
				// should work, otherwise the dd is false.
				return;
			}
		}


		$precedings = $this->dom->xpath('/deploydescriptor/configs/precedes');
		$configElements = $this->dom->xpath($path.'/child::node()');
		$install_scripts = $this->dom->xpath($path.'/script');
		$uninstall_scripts = $this->dom->xpath("/deploydescriptor/configs/uninstall/script");
		$uninstall_patches = $this->dom->xpath("/deploydescriptor/configs/uninstall/patch");
		$patches = $this->dom->xpath($path.'/patch');

		if (count($precedings) > 0 && $precedings != '') {
			foreach($precedings as $p) {
				$this->precedings[] = (string) $p->attributes()->ext;
			}
		}


		if (count($configElements) > 0 && $configElements != '') {

			foreach($configElements[0] as $p) {
				$this->userReqs = array_merge($this->userReqs, $this->extractUserRequirements($p));
				switch($p->getName()) {
					case 'variable': $this->configs[] = new VariableConfigElement($p);break;
					case 'function': $this->configs[] = new FunctionCallConfigElement($p);break;
					case 'require': $this->configs[] = new RequireConfigElement($p);break;
					case 'php': $this->configs[] = new PHPConfigElement($p);break;
				}
			}
		}
		if (count($install_scripts) > 0 && $install_scripts != '') {

			foreach($install_scripts as $p) {
				$script = (string) $p->attributes()->file;
				if (is_null($script) && $script == '') throw new IllegalArgument("Setup 'file'-attribute missing");
				$params = (string) $p->attributes()->params;
				if (is_null($params)) $params = "";
				$this->install_scripts[] = array('script'=>$script, 'params'=>$params);
			}
		}

		if (count($uninstall_scripts) > 0 && $uninstall_scripts != '') {

			foreach($uninstall_scripts as $p) {
				$script = (string) $p->attributes()->file;
				if (is_null($script) && $script == '') throw new IllegalArgument("Setup 'file'-attribute missing");
				$params = (string) $p->attributes()->params;
				if (is_null($params)) $params = "";
				$this->uninstall_scripts[] = array('script'=>$script, 'params'=>$params);
			}
		}

		if (count($patches) > 0 && $patches != '') {
			$this->patches = array();

			foreach($patches as $p) {
				$patchFile = trim((string) $p->attributes()->file);

				if (is_null($patchFile) || $patchFile == '') throw new IllegalArgument("Patch 'file'-atrribute missing");
				$this->patches[] = $patchFile;
			}
		}

		if (count($uninstall_patches) > 0 && $uninstall_patches != '') {
			$this->uninstallpatches = array();

			foreach($uninstall_patches as $p) {
				$patchFile = trim((string) $p->attributes()->file);

				if (is_null($patchFile) || $patchFile == '') throw new IllegalArgument("Patch 'file'-atrribute missing");
				$this->uninstallpatches[] = $patchFile;
			}
		}
	}

	function getUserRequirements() {
		return $this->userReqs;
	}


	// global properties
	function getVersion() {
		return trim((string) $this->globalElement[0]->version);
	}

	function getID() {
		return strtolower(trim((string) $this->globalElement[0]->id));
	}

	function getVendor() {
		return trim((string) $this->globalElement[0]->vendor);
	}

	function getInstallationDirectory() {
		return trim((string) $this->globalElement[0]->instdir);
	}

	function getDescription() {
		return trim((string) $this->globalElement[0]->description);
	}

	function getDependencies() {
		if (!is_null($this->dependencies)) return $this->dependencies;
		$this->dependencies = array();
		foreach($this->globalElement[0]->dependencies as $dep) {
			$depID = strtolower(trim((string) $dep->dependency));
			$depFrom = intval((string) $dep->dependency->attributes()->from);
			$depTo = intval((string) $dep->dependency->attributes()->to);
			$this->dependencies[] = array($depID, $depFrom, $depTo);
		}
		return $this->dependencies;
	}

	function getDependency($ext_id) {
		$ext_id = strtolower($ext_id);
		$dependencies = $this->getDependencies();
		foreach($dependencies as $d) {
			list($id, $from, $to) = $d;
			if ($ext_id === $id) return $d;
		}
		return NULL;
	}

	function getPatches() {

		return $this->patches;
	}

	function getUninstallPatches() {

		return $this->uninstallpatches;
	}

	function getCodefiles() {
		if (!is_null($this->codefiles)) return $this->codefiles;
		$this->codefiles = array();

		foreach($this->codefiles_xml as $file) {

			$this->codefiles[] = array((string) $file->attributes()->loc, (string) $file->attributes()->hash);
		}
		return $this->codefiles;
	}

	function getWikidumps() {
		if (!is_null($this->wikidumps)) return $this->wikidumps;
		$this->wikidumps = array();
		foreach($this->wikidumps_xml as $file) {
			$this->wikidumps[] = (string) $file->attributes()->loc;
		}
		return $this->wikidumps;
	}

	function getResources() {
		if (!is_null($this->resources)) return $this->resources;
		$this->resources = array();
		foreach($this->resources_xml as $file) {
			$this->resources[] = (string) $file->attributes()->loc;
		}
		return $this->resources;
	}

	private function extractUserRequirements($child) {
		$userReqs = array();
		$this->_extractUserRequirements($child, $userReqs);
		return $userReqs;
	}

	private function _extractUserRequirements($child, & $userReqs) {

		$children = $child->children();

		foreach($children as $ch) {
			switch($ch->getName()) {
				case "string": {
					$name = (string) $ch->attributes()->name;
					$userValueRequired = (string) $ch->attributes()->userValueRequired;
					if ($userValueRequired == true) {
						$userReqs[$name] = array("string", (string) $ch->attributes()->description);
					}
				}break;
				case "number": {
					$name = (string) $ch->attributes()->name;
					$userValueRequired = (string) $ch->attributes()->userValueRequired;
					if ($userValueRequired == true) {
						$userReqs[$name] = array("number", (string) $ch->attributes()->description);
					}
				}break;
				case "boolean": {
					$name = (string) $ch->attributes()->name;
					$userValueRequired = (string) $ch->attributes()->userValueRequired;
					if ($userValueRequired == true) {
						$userReqs[$name] = array("boolean", (string) $ch->attributes()->description);
					}
				}break;
				case "array": {
					$p = $this->_extractUserRequirements($ch, $mappings);

				}
			}
		}

	}
	/**
	 * Validates the code files.
	 *
	 * @return Mixed. True if all files are valid, otherwise array of invalid files.
	 */
	function validatecode($rootDir) {
		$invalids = array();
		$codeFiles = $this->getCodefiles();
		foreach($codeFiles as $file) {
			list($loc, $exp_hash) = $file;

			if (file_exists($rootDir."/".$loc)) {
				$contents = file_get_contents($rootDir."/".$loc);
				$actual_hash = md5($contents);
				if (!empty($exp_hash) && $actual_hash !== $exp_hash) {
					$invalids[] = array($loc);
				}
			} else {
				$missing[] = array($loc);
			}
		}
		return (count($invalids) == 0 && count($missing) == 0 ? true : array($invalids,$missing));
	}

	/**
	 * Applies all necessary configurations.
	 *
	 * @param string $rootDir Location of Mediawiki
	 * @param boolean $dryRun If true, nothing gets actually changed or asked.
	 * @param int $fromVersion Update from version or NULL if no update.
	 * @param callback $userCallback (see Installer.php)
	 * @return string updated LocalSettings.php
	 */
	function applyConfigurations($rootDir, $dryRun = false, $fromVersion = NULL, $userCallback = NULL) {
		if ($this->configs === false) {
			return;
		}
		if (!is_null($fromVersion)) {
			$this->createConfigElements($fromVersion);
		}
		$dp = new DeployDescriptionProcessor($rootDir.'/LocalSettings.php', $this);

		$content = $dp->applyLocalSettingsChanges($userCallback, $this->getUserRequirements(), $dryRun);
		if (!$dryRun) $dp->applyPatches($userCallback);
		if (!$dryRun) $dp->applySetups();
		return $content; // return for testing purposes.
	}

	/**
	 * Reverses configuration changes
	 *
	 * @param string $rootDir Location of Mediawiki
	 * @param boolean $dryRun
	 * @return
	 */
	function unapplyConfigurations($rootDir) {
		$dp = new DeployDescriptionProcessor($rootDir.'/LocalSettings.php', $this);
		$dp->unapplySetups();
		$dp->unapplyPatches();
		$content = $dp->unapplyLocalSettingsChanges();

		return $content; // return for testing purposes.
	}

}



?>