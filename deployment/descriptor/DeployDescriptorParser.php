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

	var $globalElement;
	var $codefiles;
	var $wikidumps;
	var $resources;
	var $configs;
	var $precedings;

	function __construct($fileloc) {
			
		$contents = file_get_contents($fileloc);

		// parse xml results
		$dom = simplexml_load_string($contents);

		$this->globalElement = $dom->xpath('/deploydescriptor/global');
		$this->codefiles = $dom->xpath('/deploydescriptor/codefiles/file');
		$this->wikidumps = $dom->xpath('/deploydescriptor/wikidumps/file');
		$this->resources = $dom->xpath('/deploydescriptor/resources/file');
		$this->createConfigElements($dom);
	}

    public function getPrecedings() {
    	return $this->precedings;
    }
    
    public function getConfigs() {
    	return $this->configs;
    }

	private function createConfigElements(& $dom, $from = NULL) {
		if ($from == NULL) {
			$path = "/deploydescriptor/configs/new";
		} else {
			$path = "/deploydescriptor/configs/update[@from='$from']";
		}
		$precedings = $dom->xpath('/deploydescriptor/configs/precedes');
		$variables = $dom->xpath($path.'/variable');
		$function = $dom->xpath($path.'/function');

		$this->configs = array();
		$this->precedes = array();
		foreach($precedings as $p) {
			$this->precedings[] = (string) $p->attributes()->ext;
		}
		foreach($variables as $p) {
			$this->configs[] = new VariableConfigElement($p);
		}
	    foreach($function as $p) {
            $this->configs[] = new FunctionCallConfigElement($p);
        }	
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
			$depID = trim((string) $dep->dependency);
			$depFrom = intval((string) $dep->dependency->attributes()->from);
			$depTo = intval((string) $dep->dependency->attributes()->to);
			$deps[] = array($depID, $depFrom, $depTo);
		}
		return $deps;
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

	function applyConfigurations($ls_loc) {
		if ($this->configs === false) {
			// no configs, nothing to do
			return DEPLOY_MSG_NOTHING_TODO;
		}

		$dp = new DeployDescriptionProcessor($ls_loc, $this);
		return $dp->makeChanges();
		
	}

}



?>