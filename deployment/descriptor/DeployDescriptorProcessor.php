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
			// strip php endtag (if existing)
			$phpEndTag = strpos($this->localSettingsContent, "?>");
			if ($phpEndTag !== false) {
				$this->localSettingsContent = substr($this->localSettingsContent, 0, $phpEndTag);
			}
			return;
		}
		throw new IllegalArgument("$ls_loc does not exist!");
	}


	function makeChanges() {
		// calculate changes
		$this->insertions = ""; // reset
			
		foreach($this->dd_parser->getConfigs() as $ce) {
			$this->insertions .= $ce->apply($this->localSettingsContent, $this->dd_parser->getID());
		}
		list($insertpos, $ext_found) = $this->getInsertPosition($this->dd_parser->getID());
		$prefix = substr($this->localSettingsContent, 0 , $insertpos);
	
		$suffix = substr($this->localSettingsContent, $insertpos + 1);

		$startTag = $ext_found ? "" : "\n/*start-".$this->dd_parser->getID()."*/";
		$endTag = $ext_found ? "" : "\n/*end-".$this->dd_parser->getID()."*/";
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
	private function getInsertPosition($ext_id) {
		$max = 0;
		// get maximum index of all preceding extensions
		foreach($this->dd_parser->getPrecedings() as $extensionID) {

			$pos = strpos($this->localSettingsContent, "/*end-$extensionID*/");
			$max = $pos > $max ? $pos : $max;
		}
		$pos = strpos($this->localSettingsContent, "/*end-$ext_id*/", $max);
		
		if ($pos === false) {
			$ext_found = false;
			$pos = strlen($this->localSettingsContent);
			return $pos;
		}
		$ext_found = true;
		return array($pos - 2, $ext_found);
	}


}

abstract class ConfigElement {
	var $type;
	public function __construct($type) { $this->type = $type; }
	public function getType() { return $this->type; }
	public abstract function apply(& $ls, $ext_id);

	protected function getExtensionFragment($ext_id, & $ls) {
		$start = strpos($ls, "/*start-$ext_id*/");
		$end = strpos($ls, "/*end-$ext_id*/");
		if ($start === false || $end === false) {
			throw new IllegalArgument("$ext_id is not installed.");
		}
		return substr($ls, $start, $end-$start);
	}

	protected function replaceExtensionFragment($ext_id, $fragment, & $ls) {
		$start = strpos($ls, "/*start-$ext_id*/");
		$end = strpos($ls, "/*end-$ext_id*/");
		if ($start === false || $end === false) {
			throw new IllegalArgument("$ext_id is not installed.");
		}
		$ls = substr($ls, 0, $start). $fragment . substr($ls, $end);
	}

	protected function serializeParameters($child, $mappings = array()) {
		$resultsStr="";
		$children = $child->children();
		
		foreach($children as $ch) {
			switch($ch->getName()) {
				case "string": {
					$name = (string) $ch->attributes()->name;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$resultsStr .= ($resultsStr == "" ? "'$p'" : ", '$p'");break;
				}
				case "number": {
					$name = (string) $ch->attributes()->name;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$resultsStr .= ($resultsStr == "" ? "$p" : ", $p");break;
				}
				case "boolean": {
					$name = (string) $ch->attributes()->name;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$resultsStr .= ($resultsStr == "" ? "$p" : ", $p");break;
				}
				case "array": {
					$p = $this->serializeParameters($ch, $mappings);
					$resultsStr .=  $resultsStr == "" ? "array($p)" : ", array($p)";
				}
			}
		}
		return $resultsStr;
	}
}

class VariableConfigElement extends ConfigElement {
	var $name;
	var $value;
	var $remove;
	var $external;

	public function __construct($child) {
		parent::__construct("var");
		$this->name = $child->attributes()->name;
		$this->value = $this->serializeParameters($child);
		$this->remove = $child->attributes()->remove;
		$this->external = $child->attributes()->external;
	}

	public function apply(& $ls, $ext_id) {

		$remove = ($this->remove == "true");

		if ($this->external) {
			if ($remove) {
				$this->removeVariable($ls, $this->name);
			} else {
			    $this->changeVariable($ls, $this->name, $this->remove ? NULL : $this->value);
			}
			return;
		} else {
			$fragment = $this->getExtensionFragment($ext_id, $ls);
			$res = $this->changeVariable($fragment, $this->name, $this->remove ? NULL : $this->value);
			 
			if ($res === true) {
				 $this->replaceExtensionFragment($ext_id, $fragment, $ls); 
				
			     return;
			}
		}

		return "\n$$this->name = /*start-variable-$this->name*/ $this->value;/*end-variable-$this->name*/";

	}
	
	private function removeVariable(& $content, $name) {
		// remove it
        $content = preg_replace('/\$'.preg_quote($name).'\s*=.*/', "", $content);
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

	public function apply(& $ls, $ext_id) {
		if ($this->remove) {
			$this->removeRequireonce($this->localSettingsContent, $file);
		} else {
			return "\nrequire_once('".$file."');";
		}
	}

	private function removeRequireonce(& $content, $file) {

		$content = preg_replace('/require_once\s*\(\s*\''.preg_quote($file).'\'\s*\)\s*;', "", $content);
		return true;

	}
}

class PHPConfigElement extends ConfigElement {
	public function __construct($child) {
		parent::__construct("php");
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove == "true");
		$this->content = (string) $child[0];
	}

	public function apply(& $ls, $ext_id) {
		if ($remove) {
			$fragment = $this->getExtensionFragment($ext_id, $ls);
			$fragment = str_replace("\n".$this->content, "", $fragment);
			$this->replaceExtensionFragment($ext_id, $fragment, $ls);

		} else {
			return "\n".$this->content;
		}
	}
}

class FunctionCallConfigElement extends ConfigElement {
	public function __construct($child) {
		parent::__construct("function");
		$this->argumentsAsXML = $child;
		$this->functionname = $child->attributes()->name;
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove == "true");
		

	}

	public function apply(& $ls, $ext_id) {
		$arguments = $this->serializeParameters($this->argumentsAsXML);
		$appliedCommand = "\n".$this->functionname."(/*param-start-".$this->functionname."*/".$arguments."/*param-end-".$this->functionname."*/);";
		if ($this->remove) {

			$fragment = $this->getExtensionFragment($ext_id, $ls);
			$fragment = str_replace($appliedCommand, "", $fragment);
			$this->replaceExtensionFragment($ext_id, $fragment, $ls);

		} else {
			
			$fragment = $this->getExtensionFragment($ext_id, $ls);
			$start = strpos($fragment, '/*param-start-'.$this->functionname);
			$end = strpos($fragment, '/*param-end-'.$this->functionname);
			if ($start !== false && $end !== false) {
				
				$mappings = $this->deserialize($this->argumentsAsXML, substr($fragment, $start, $end-$start));
				
				$arguments = $this->serializeParameters($this->argumentsAsXML, $mappings);
				$appliedCommand = "\n".$this->functionname."(/*param-start-".$this->functionname."*/".$arguments."/*param-end-".$this->functionname."*/);";
			}
			return $appliedCommand;
		}
	}

	public function deserialize($child, $phpArgString) {
		$phpArg = eval('return array('.$phpArgString.');');
		
		$mappings = array();
        $this->_deserialize($child, $phpArg, $mappings);
        return $mappings;
	}

	private function _deserialize($child, $phpArg, & $mappings) {
		$children = $child->children();
		$i = 0;
		foreach($children as $ch) {
			switch($ch->getName()) {
				case "string": {
					$name = (string) $ch->attributes()->name;
					$p = $phpArg[$i];
					$mappings[$name] = $p;break;
				}
				case "number": {
					$name = (string) $ch->attributes()->name;
                    $p = $phpArg[$i];
                    $mappings[$name] = $p;break;
				}
				case "boolean": {
					$name = (string) $ch->attributes()->name;
                    $p = $phpArg[$i];
                    $mappings[$name] = $p;break;
				}
				case "array": {
                    $p = $phpArg[$i];
                    $this->_deserialize($ch, $p, $mappings);
				}
			}
		$i++;
		}
	}

}

class IllegalArgument extends Exception {

}
?>