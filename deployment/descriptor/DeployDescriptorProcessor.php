<?php
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

    function __construct($ls_loc, $dd_parser) {
        $this->dd_parser = $dd_parser;
        
        $this->ls_loc = $ls_loc;
        if (file_exists($ls_loc)) {
            $this->localSettingsContent = file_get_contents($ls_loc);
            return;
        }
        throw new IllegalArgument("$ls_loc does not exist!");
    }
   

    function makeChanges() {
        // calculate changes
        $this->insertions = ""; // reset
       
        foreach($this->dd_parser->getConfigs() as $ce) {
            $this->insertions .= $ce->apply($this->localSettingsContent);
        }

        $insertpos = $this->getInsertPosition();
        $prefix = substr($this->localSettingsContent, 0 , $insertpos);
        $suffix = substr($this->localSettingsContent, $insertpos + 1);

        $startTag = "\n/*start-".$this->ext_id."*/";
        $endTag = "\n/*end-".$this->ext_id."*/";
        $this->localSettingsContent = $prefix . $startTag . $this->insertions . $endTag . $suffix;
        return $this->localSettingsContent;
    }

    function writeFile() {
        $handle = fopen($this->ls_loc, "w");
        fwrite($handle, $this->localSettingsContent);
        fclose($handle);
    }

    /*
     * Calculates the insert position
     */
    private function getInsertPosition() {
        $max = 0;
        // get maximum index of all preceding extensions
        foreach($this->dd_parser->getPrecedings() as $extensionID) {
            
            $pos = strpos($this->localSettingsContent, "/*end-$extensionID*/");
            $max = $pos > $max ? $pos : $max;
        }
        if ($max === 0) {
        	return $max = strlen($this->localSettingsContent);
        }
        return strpos($this->localSettingsContent, "\n", $max) + 1;
    }

    
}

abstract class ConfigElement {
    var $type;
    public function __construct($type) { $this->type = $type; }
    public function getType() { return $this->type; }
    public abstract function apply(& $ls);
}

class VariableConfigElement extends ConfigElement {
    var $name;
    var $value;
    var $remove;

    public function __construct($child) {
        parent::__construct("var");
        $this->name = $child->attributes()->name;
        $this->value = $child->attributes()->value;
        $this->remove = $child->attributes()->remove;
    }

    public function apply(& $ls) {
            
        $remove = ($this->remove == "true");
        $this->value = is_numeric($this->value) ? $this->value : "'".$this->value."'";
        $res = $this->changeVariable($ls, $this->name, $this->remove ? NULL : $this->value);
        if ($res === true) return;
        return "\n$$this->name = $this->value;";

    }

    /**
     * Changes a variable value in a text.
     *
     * @param $content
     * @param $name
     * @param $value
     * @param $notquote
     *
     */
    private function changeVariable(& $content, $name, $value, $notquote=false) {
        if ($value === NULL) {
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

class RequireConfigElement extends ConfigElement {
    var $file;
    var $remove;
    public function __construct($child) {
        parent::__construct("require");
        $this->file = $child->attributes()->file;
        $this->remove = $child->attributes()->remove;
        $this->remove = ($this->remove == "true");
    }

    public function apply(& $ls) {
        if ($this->remove) {
            $this->removeRequireonce($this->localSettingsContent, $file);
        } else {
            $this->insertions .= "\nrequire_once('".$file."');";
        }
    }

    private function removeRequireonce(& $content, $file) {

        $content = preg_replace('/require_once\s*\(\s*\''.preg_quote($file).'\'\s*\)\s*;', "", $content);
        return true;

    }
}

class PHPConfigElement extends ConfigElement {
	public function apply(& $ls) {
		
	}
}

class FunctionCallConfigElement extends ConfigElement {
	public function apply(& $ls) {
		
	}
}

class IllegalArgument extends Exception {
	
}
?>