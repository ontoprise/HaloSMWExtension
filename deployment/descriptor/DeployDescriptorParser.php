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
    var $setups;
    var $patches;
    
    // xml  
    var $dom;
    var $wikidumps_xml;
    var $codefiles_xml;
    var $patches_xml;
    var $resources_xml;

    function __construct($xml, $fromVersion = NULL) {
            
        // parse xml results
        $this->dom = simplexml_load_string($xml);
    
    

        $this->globalElement = $this->dom->xpath('/deploydescriptor/global');
        $this->codefiles_xml = $this->dom->xpath('/deploydescriptor/codefiles/file');
        $this->patches_xml = $this->dom->xpath('/deploydescriptor/codefiles/patch');
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

    public function getSetups() {
        return $this->setups;
    }



    private function createConfigElements($from = NULL) {

        $this->configs = array();
        $this->precedings = array();
        $this->setups = array();
        $this->userReqs = array();
        
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
        
        $setup = $this->dom->xpath($path.'/setup');

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
     if (count($setup) > 0 && $setup != '') {
        foreach($setup as $p) {
            $script = (string) $p->attributes()->script;
            if (is_null($script) && $script == '') throw new IllegalArgument("Setup 'script'-attribute missing");
            $params = (string) $p->attributes()->params;
            if (is_null($params)) $params = "";
            $this->setups[] = array('script'=>$script, 'params'=>$params);
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

        foreach($this->patches_xml as $p) {
            $patchFile = trim((string) $p->attributes()->file);
            if (is_null($patchFile) || $patchFile == '') throw new IllegalArgument("Patch 'file'-atrribute missing");
            $this->patches[] = $patchFile;
        }
        return $this->patches;
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
     * @param int $version Update from version or NULL if no update.
     * @param function(array($name, $description)) $userValueCallback
     * @return string updated LocalSettings.php
     */
    function applyConfigurations($rootDir, $dryRun = false, $version = NULL, $userValueCallback = NULL) {
        if ($this->configs === false) {
            return;
        }
        if (!is_null($version)) {
            $this->createConfigElements($version);
        }
        $dp = new DeployDescriptionProcessor($rootDir.'/LocalSettings.php', $this);
        $userValueMappings = array();
        
        if (!is_null($userValueCallback)) {
           call_user_func(array(&$userValueCallback,"getUserReqParams"), $this->getUserRequirements(), $userValueMappings);
        }
        
        $content = $dp->applyLocalSettingsChanges($userValueMappings);
        $dp->applySetups($dryRun);
        $dp->applyPatches($dryRun);
        if (!$dryRun) {
            $dp->writeLocalSettingsFile($content);
        }
        return $content; // return for testing purposes.
    }

    /**
     * Reverses configuration changes
     *
     * @param string $rootDir Location of Mediawiki
     * @param boolean $dryRun
     * @return
     */
    function unapplyConfigurations($rootDir, $dryRun = false) {
        $dp = new DeployDescriptionProcessor($rootDir.'/LocalSettings.php', $this);
        $content = $dp->unapplyLocalSettingsChanges();
        $dp->unapplySetups($dryRun);
        $dp->unapplyPatches($dryRun);
        if (!$dryRun) $dp->writeLocalSettingsFile($content);
        return $content; // return for testing purposes.
    }

}



?>