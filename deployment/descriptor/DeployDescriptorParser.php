<?php

define ('DEPLOY_MSG_NOTHING_TODO', 1);

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
    
	var $dom;
	var $globalElement;
	var $codefiles;
	var $patchfiles;
	var $wikidumps;
	var $resources;
	var $configs;
	var $precedings;
	var $userReqs;
	var $dependencies;
	var $setups;
	var $patches;

	function __construct($xml, $fromVersion = NULL) {
			
		// parse xml results
		$this->dom = simplexml_load_string($xml);
		print_r($xml);

		$this->globalElement = $this->dom->xpath('/deploydescriptor/global');
		$this->codefiles = $this->dom->xpath('/deploydescriptor/codefiles/file');
		$this->patchfiles = $this->dom->xpath('/deploydescriptor/codefiles/patch');
		$this->wikidumps = $this->dom->xpath('/deploydescriptor/wikidumps/file');
		$this->resources = $this->dom->xpath('/deploydescriptor/resources/file');
		$this->createConfigElements($fromVersion); // assume new config, not update
	}

	
	public function getPrecedings() {
		return $this->precedings;
	}

	public function getConfigs() {
		return $this->configs;
	}
	
	public function getSetups() {
		return $this->setups;
	}
	
   

	private function createConfigElements($from = NULL) {
		
		if ($from == NULL) {
			$path = "/deploydescriptor/configs/new";
		} else {
			// namespace is needed due to a bug in xpath processor
			$path = "//dd:update[@from='$from']";
			print_r($this->dom);
			$this->dom->registerXPathNamespace('dd', 'http://www.ontoprise.de/smwhalo/deploy');
			$update = $this->dom->xpath($path);
			if (count($update) === 0) {
				// if update config missing, use new 
				$path = "/deploydescriptor/configs/new";
			}
		}
		$precedings = $this->dom->xpath('/deploydescriptor/configs/precedes');
		$variables = $this->dom->xpath($path.'/variable');
		$function = $this->dom->xpath($path.'/function');
		$require = $this->dom->xpath($path.'/require');
		$php = $this->dom->xpath($path.'/php');
		$setup = $this->dom->xpath($path.'/setup');
		

		$this->configs = array();
		$this->precedings = array();
		$this->setups = array();
		foreach($precedings as $p) {
			$this->precedings[] = (string) $p->attributes()->ext;
		}
		
		$this->userReqs = array();
		foreach($variables as $p) {
			$this->userReqs = array_merge($this->userReqs, $this->extractUserRequirements($p));
			$this->configs[] = new VariableConfigElement($p);
		}
		foreach($function as $p) {
			$this->userReqs = array_merge($this->userReqs, $this->extractUserRequirements($p));
			$this->configs[] = new FunctionCallConfigElement($p);
		}
		foreach($require as $p) {
			$this->configs[] = new RequireConfigElement($p);
		}
		foreach($php as $p) {
			$this->configs[] = new PHPConfigElement($p);
		}
	   foreach($setup as $p) {
	   	    $script = (string) $p->attributes()->script;
	   	    if (is_null($script) && $script == '') throw new IllegalArgument("Setup 'script'-attribute missing");
	   	    $params = (string) $p->attributes()->params;
	   	    if (is_null($params)) $params = "";
            $this->setups[] = array('script'=>$script, 'params'=>$params);
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
		if (!is_null($this->dependencies)) return $this->dependencies;
		$this->dependencies = array();
		foreach($this->globalElement[0]->dependencies as $dep) {
			$depID = trim((string) $dep->dependency);
			$depFrom = intval((string) $dep->dependency->attributes()->from);
			$depTo = intval((string) $dep->dependency->attributes()->to);
			$this->dependencies[] = array($depID, $depFrom, $depTo);
		}
		return $this->dependencies;
	}
	
	function getDependency($ext_id) {
		$dependencies = $this->getDependencies();
		foreach($dependencies as $d) {
			list($id, $from, $to) = $d;
			if ($ext_id === $id) return $d;
		}
		return NULL;
	}
	
	function getPatches() {
		if (!is_null($this->patches)) return $this->patches;
        $this->patches = array();
        
        foreach($this->patchfiles as $p) {
            $patchFile = trim((string) $p->attributes()->file);
            if (is_null($patchFile) || $patchFile == '') throw new IllegalArgument("Patch 'file'-atrribute missing");
            $this->patches[] = $patchFile;
        }
        return $this->patches;
	}

	function getCodefiles() {
		$loc = array();

		foreach($this->codefiles as $file) {

			$loc[] = (string) $file->attributes()->loc;
		}
		return $loc;
	}

	function getWikidumps() {
		$loc = array();
		foreach($this->wikidumps as $file) {
			$loc[] = (string) $file->attributes()->loc;
		}
		return $loc;
	}

	function getResources() {
		$loc = array();
		foreach($this->resources as $file) {
			$loc[] = (string) $file->attributes()->loc;
		}
		return $loc;
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
		return $resultsStr;
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

	/**
	 * Applies all necessary configurations.
	 *
	 * @param string $ls_loc Location of LocalSettings.php
	 * @param boolean $dryRun If true, nothing gets actually changed.
	 * @param int $version Update from version or NULL if no update.
	 * @return string updated LocalSettings.php
	 */
	function applyConfigurations($ls_loc, $dryRun = false, $version = NULL) {
		if ($this->configs === false) {
			// no configs, nothing to do
			return DEPLOY_MSG_NOTHING_TODO;
		}
        if (!is_null($version)) {
        	$this->createConfigElements($version);
        }
		$dp = new DeployDescriptionProcessor($ls_loc, $this);
		$content = $dp->applyLocalSettingsChanges($userValues);
		$dp->applySetups($dryRun);
		$dp->applyPatches($dryRun);
		if (!$dryRun) $dp->writeLocalSettingsFile($content);
        return $content;
	}

}



?>