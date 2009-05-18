<?php

define ('DEPLOY_MSG_NOTHING_TODO', 1);

/**
 * @author: Kai Kühn / ontoprise / 2009
 *
 * This class works as a parser (and serializer) of the general
 * description of a deployable entity (aka deploy descriptor).
 *
 *
 */
class DeployDescriptorParser {

	var $globalElement;
	var $codefiles;
	var $wikidumps;
	var $resources;
	var $configs;

	function __construct($dd_file_location) {
		$contents = file_get_contents($dd_file_location);

		// parse xml results
		$dom = simplexml_load_string($contents);

		$this->globalElement = $dom->xpath('/deploydescriptor/global');
		$this->codeFiles = $dom->xpath('/deploydescriptor/codefiles');
		$this->wikidumps = $dom->xpath('/deploydescriptor/wikidumps');
		$this->resources = $dom->xpath('/deploydescriptor/resources');
		$this->configs = $dom->xpath('/deploydescriptor/configs');
	}

	// global properties
	function getVersion() {
		return trim((string) $this->globalElement[0]->version);
	}

	function getID() {
		return trim((string) $this->globalElement[0]->id);
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
		$deps = array();
		foreach($this->globalElement[0]->dependencies as $dep) {
			$deps[] = trim((string) $dep->dependency);
		}
		return $deps;
	}

	function getCodefiles() {
		$loc = array();
		foreach($this->codefiles[0]->file as $file) {
			$loc = (string) $file->attributes()->loc;
		}
		return $loc;
	}

	function getWikipages() {
		$loc = array();
		foreach($this->wikidumps[0]->file as $file) {
			$loc = (string) $file->attributes()->loc;
		}
		return $loc;
	}

	function getResources() {
		$loc = array();
		foreach($this->resources[0]->file as $file) {
			$loc = (string) $file->attributes()->loc;
		}
		return $loc;
	}



	/**
	 * Validates the code files.
	 *
	 * @return Mixed. True if all files are valid, otherwise array of invalid files.
	 */
	function validatecode() {
		$warnings = array();
		foreach($this->codefiles[0]->file as $file) {
			$loc = (string) $file->attributes()->loc;
			$exp_hash = (string) $file->attributes()->hash;

			if (file_exists($loc)) {
				$contents = file_get_contents($loc);
				$actual_hash = md5($contents);
				if ($actual_hash !== $exp_hash) {
					$warnings[] = "$loc is invalid.\n";
				}
			}
		}
		return (count($warnings) == 0 ? true : $warnings);
	}

	function applyConfigurations() {
		if ($this->configs === false) {
			// no configs, nothing to do
			return DEPLOY_MSG_NOTHING_TODO;
		}

		$dp = new DeployDescriptionProcessor("../../LocalSettings.php", $this->getID(), $this->configs);
		$dp->makeChanges();
		$dp->writeFile;


	}
}

/**
 * Makes changes to the the LocalSettings.php or other configuration files.
 * Modifications are specified in the deploy descriptor.
 *
 */
class DeployDescriptionProcessor {
	private $localSettingsContent;
	private $ext_id;
	private $cfg_element;
	private $ls_loc;
	private $insertions;

	function __contruct($ls_loc, $ext_id, $cfg_element) {
		$this->ext_id = $ext_id;
		$this->cfg_element = $cfg_element;
		$this->ls_loc = $ls_loc;
		if (file_exists($ls_loc)) {
			$this->localSettingsContent = file_get_contents($ls_loc);
			return;
		}
		throw new IllegalArgument("$ls_loc does not exist!");
	}

	private function applyVariable($vartag) {
		$name = $vartag->attributes()->name;
		$value = $vartag->attributes()->value;
		$value = is_numeric($value) ? $value : "'$value'";
		$res = changeVariable($this->localSettingsContent, $name, $value);
		if ($res === true) return;
		$this->insertions .= "\n$$name = $value;";

	}

	private function applyRequireonce($e) {
		 

	}

	private function applyFunctionCall($e) {
		 

	}

	private function applyPHP($e) {
		 

	}

	function makeChanges() {
		// calculate changes
		$this->insertions = ""; // reset

		foreach($this->cfg_element->children() as $child) {
			switch($child->getName()) {
				case "variable": $this->applyVariable($child); break;
				case "require-once": $this->applyRequireonce($child); break;
				case "function-call": $this->applyFunctionCall($child); break;
				case "php": $this->applyPHP($child); break;

			}
		}

		$insertpos = $this->getInsertPosition($this->cfg_element);
		$prefix = substr($this->localSettingsContent, 0 , $insertpos);
		$suffix = substr($this->localSettingsContent, $insertpos + 1);

		$startTag = "/*\nstart-".$this->ext_id."*/";
		$endTag = "/*\nend-".$this->ext_id."*/";
		$this->localSettingsContent = $prefix . $startTag . $this->insertions . $endTag . $suffix;
	}

	function writeFile() {
		$handle = fopen($this->ls_loc, "w");
		fwrite($handle, $this->localSettingsContent);
		fclose($handle);
	}

	private function getInsertPosition($cfgElement) {
		$max = 0;
		// get maximum index of all preceding extensions
		foreach($cfgElement->precedes as $pe) {
			$extensionID = (string) $pe;
			$pos = strpos($this->localSettingsContent, "/*end-$extensionID*/");
			$max = $pos > $max ? $pos : $max;
		}
		return strpos($this->localSettingsContent, "\n", $max) + 1;
	}
	 
	function changeVariable(& $content, $name, $value, $notquote=false) {
		if ($value == '') {
			// remove it
			$content = preg_replace('/\$'.preg_quote($name).'\s*=.*/', "", $content);
			return true;
		}
		$value = is_numeric($value) || $value == 'true' || $value == 'false' || $notquote ? $value : "\"".$value."\"";
		if (preg_match('/\$'.preg_quote($name).'\s*=.*/', $content) > 0) {
			$content = preg_replace('/\$'.preg_quote($name).'\s*=.*/', "\$$name=$value;", $content);
			return true;
		}
		return false;
	}
}

$dd = new DeployDescriptorParser("test_deploy.xml");
print_r($dd->getVersion());
print_r($dd->getDependencies());
?>