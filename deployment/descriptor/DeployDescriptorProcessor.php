<?php
/**
 * Makes changes to the the LocalSettings.php or other configuration files.
 * Modifications are specified in the deploy descriptor.
 *
 *  @author: Kai Kühn / Ontoprise / 2009
 *
 */

class DeployDescriptionProcessor {

	private $ls_loc;
	private $dd_parser;


	private $localSettingsContent;

	/**
	 * Creates new DeployDescriptorProcessor.
	 *
	 * @param string $ls_loc Location of LocalSettings
	 * @param DeployDescriptorParser $dd_parser
	 *
	 */
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

	/**
	 * Reads the LocalSettings.php file, applies changes and return it as string.
	 *
	 * @param array $userValues Mappings for required values.
	 * @return string changed LocalSettings.php file
	 */
	function applyLocalSettingsChanges($userValues) {
		// calculate changes
		$insertions = ""; // reset
			
		foreach($this->dd_parser->getConfigs() as $ce) {
			$insertions .= $ce->apply($this->localSettingsContent, $this->dd_parser->getID(), $userValues);
		}
		list($insertpos, $ext_found) = $this->getInsertPosition($this->dd_parser->getID());

		$prefix = substr($this->localSettingsContent, 0 , $insertpos);


		$suffix = substr($this->localSettingsContent, $insertpos + 1);

		$startTag = $ext_found ? "" : "\n/*start-".$this->dd_parser->getID()."*/";
		$endTag = $ext_found ? "" : "\n/*end-".$this->dd_parser->getID()."*/";
		$this->localSettingsContent = $prefix . $startTag . $insertions . $endTag . $suffix;

		return $this->localSettingsContent;
	}

	function unapplyLocalSettingsChanges() {
		//TODO: external variables get not re-set. hard to fix.
		$fragment = ConfigElement::getExtensionFragment($this->dd_parser->getID(), $this->localSettingsContent);
		if (!is_null($fragment)) {
			$ls = str_replace($fragment, "", $this->localSettingsContent);
		}
		return $ls;
	}

	/**
	 * Runs the given setup scripts.
	 *
	 * Needs php interpreter in PATH
	 *
	 * @param boolean $dryRun
	 */
	function applySetups($dryRun = false) {
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		foreach($this->dd_parser->getSetups() as $setup) {
			$instDir = self::makeUnixPath($this->dd_parser->getInstallationDirectory());
			if (substr($instDir, -1) != '/') $instDir .= "/";
			$script = self::makeUnixPath($setup['script']);
			if (!$dryRun) {
				print "\n\nRun script:\nphp ".$rootDir."/".$instDir.$script." ".$setup['params'];
				exec("php ".$rootDir."/".$instDir.$script." ".$setup['params']);
			}
		}
	}
    
	/**
	 * Runs the given setup scripts in de-install mode.
	 * 
	 * Needs php interpreter in PATH
	 * 
	 * @param boolean $dryRun
	 */
	function unapplySetups($dryRun = false) {
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		foreach($this->dd_parser->getSetups() as $setup) {
			$instDir = self::makeUnixPath($this->dd_parser->getInstallationDirectory());
			if (substr($instDir, -1) != '/') $instDir .= "/";
			$script = self::makeUnixPath($setup['script']);
			if (!$dryRun) {
				print "\n\nRun script:\nphp ".$rootDir."/".$instDir.$script." ".$setup['params'];
				exec("php ".$rootDir."/".$instDir.$script." --deinstall");
			}
		}
	}

	/**
	 * Applies patches
	 *
	 * Needs php Interpreter and GNU-patch in PATH.
	 * 
	 * @param boolean $dryRun
	 */
	function applyPatches($dryRun = false) {
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		foreach($this->dd_parser->getPatches() as $patch) {
			$instDir = self::makeUnixPath($this->dd_parser->getInstallationDirectory());
			if (substr($instDir, -1) != '/') $instDir .= "/";
			$patch = self::makeUnixPath($patch);
			if (!$dryRun) {
			 print "\n\nApply patch:\nphp ".$rootDir."/patches/patch.php -p ".$rootDir."/".$instDir.$patch." -d ".$rootDir;
			 exec("php ".$rootDir."/patches/patch.php -p ".$rootDir."/".$instDir."/".$patch." -d ".$rootDir);
			}
		}
	}
	
    /**
     * Removes patches
     *
     * Needs php Interpreter and GNU-patch in PATH.
     * 
     * @param boolean $dryRun
     */
    function unapplyPatches($dryRun = false) {
        $rootDir = self::makeUnixPath(dirname($this->ls_loc));
        foreach($this->dd_parser->getPatches() as $patch) {
            $instDir = self::makeUnixPath($this->dd_parser->getInstallationDirectory());
            if (substr($instDir, -1) != '/') $instDir .= "/";
            $patch = self::makeUnixPath($patch);
            if (!$dryRun) {
             print "\n\nRemove patch:\nphp ".$rootDir."/patches/patch.php -r -p ".$rootDir."/".$instDir.$patch." -d ".$rootDir;
             exec("php ".$rootDir."/patches/patch.php -r -p ".$rootDir."/".$instDir."/".$patch." -d ".$rootDir);
            }
        }
    }

	/**
	 * Writes LocalSettings.php
	 *
	 */
	function writeLocalSettingsFile(& $content) {
		$handle = fopen($this->ls_loc, "wb");
		fwrite($handle, $content);
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
			return array($pos - 2, $ext_found);
		}
		$ext_found = true;
		return array($pos - 2, $ext_found);
	}

	private static function makeUnixPath($path) {
		return str_replace("\\", "/", $path);
	}
}



/**
 * Represents a configuration change in the localsettings file.
 *
 * @author: Kai Kühn / Ontoprise / 2009
 *
 */
abstract class ConfigElement {
	var $type;
	public function __construct($type) { $this->type = $type; }
	public function getType() { return $this->type; }

	/**
	 * Applies the changes described by the config element to the local settings.
	 *
	 * @param string $ls LocalSettings text
	 * @param string $ext_id Extension_ID
	 * @param array $userValues Hash arrays with user values for required variables.
	 */
	public abstract function apply(& $ls, $ext_id, $userValues);


	/**
	 * Returns the configuration fragement of the extension. The text between
	 *
	 *     start-$ext_id ... end-$ext_id
	 *
	 * @param string $ext_id extension ID
	 * @param string $ls localsettings text
	 * @return string Fragment or NULL if it does not exist.
	 */
	public static function getExtensionFragment($ext_id, & $ls) {
		$start = strpos($ls, "/*start-$ext_id*/");
		$end = strpos($ls, "/*end-$ext_id*/");
		if ($start === false || $end === false) {
			return NULL; // fragment does not exist
		}
		return substr($ls, $start, $end-$start);
	}

	/**
	 *  Replaces the configuration fragement of the extension by the given.
	 *
	 * @param striing $ext_id
	 * @param string $fragment new fragment
	 * @param string $ls localsettings text
	 */
	protected function replaceExtensionFragment($ext_id, $fragment, & $ls) {
		$start = strpos($ls, "/*start-$ext_id*/");
		$end = strpos($ls, "/*end-$ext_id*/");
		if ($start === false || $end === false) {
			throw new IllegalArgument("$ext_id is not installed.");
		}
		$ls = substr($ls, 0, $start). $fragment . substr($ls, $end);
	}

	/**
	 * Serializes arguments of a PHP function.
	 *
	 * @param SimpleXMLElement $child Config element as XML object
	 * @param array $mappings Values to be set (name=>value)
	 */
	protected function serializeParameters($child, $mappings = array()) {
		$resultsStr="";
		$children = $child->children();

		foreach($children as $ch) {
			switch($ch->getName()) {
				case "string": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$null = (string) $ch->attributes()->null;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$p = (strtolower($null) === "true") ? "NULL" : "'".$p."'";

					$key = $key != NULL ? "'$key'=>" : "";
					$resultsStr .= ($resultsStr == "" ? "$key$p" : ", $key$p");break;
				}
				case "number": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$null = (string) $ch->attributes()->null;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$p = (strtolower($null) === "true") ? "NULL" : $p;
					$key = $key != NULL ? "'$key'=>" : "";
					$resultsStr .= ($resultsStr == "" ? "$key $p" : ", $key $p");break;
				}
				case "boolean": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$null = (string) $ch->attributes()->null;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$p = (strtolower($null) === "true") ? "NULL" : $p;
					$key = $key != NULL ? "'$key'=>" : "";
					$resultsStr .= ($resultsStr == "" ? "$key $p" : ", $key $p");break;
				}
				case "array": {
					$p = $this->serializeParameters($ch, $mappings);
					$resultsStr .=  $resultsStr == "" ? "array($p)" : ", array($p)";
				}
			}
		}
		return $resultsStr;
	}

	/**
	 * Parses a PHP array content and interprete it using the XML representation.
	 *
	 * @param SimpleXMLElement $child XML represenation of a function argument list or a variable value
	 * @param string $phpArgString php argument string
	 * @return array $mappings Mappings name=>value
	 */
	protected function deserialize($child, $phpArgString) {
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
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$mappings[$name] = $p;break;
				}
				case "number": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$mappings[$name] = $p;break;
				}
				case "boolean": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$mappings[$name] = $p;break;
				}
				case "array": {
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$this->_deserialize($ch, $p, $mappings);
				}
			}
			$i++;
		}
	}




}

/**
 * Represents a variable setting.
 *
 * e.g. $smwgMySetting = true;
 *
 */
class VariableConfigElement extends ConfigElement {
	var $name;
	var $value;
	var $remove;
	var $external; // indicates that variable is defined elsewhere and not in the extensions's section
	var $requireUserValue;

	public function __construct($child) {
		parent::__construct("var");
		$this->name = $child->attributes()->name;
		$this->value = $this->serializeParameters($child);
		$this->value = count($child->children()) > 1 ? 'array('.$this->value.')' : $this->value;
		$this->remove = $child->attributes()->remove;
		$this->external = $child->attributes()->external;

	}

	public function apply(& $ls, $ext_id, $userValues) {

		$remove = ($this->remove == "true");

		if ($this->external) {
			if ($remove) {
				$this->removeVariable($ls, $this->name);
			} else {
				$this->changeVariable($ls, $this->name, $this->remove ? NULL : $this->value);
			}
			return;
		} else {
			$fragment = self::getExtensionFragment($ext_id, $ls);
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

/**
 * Represents a require/include statement in the settings.
 *
 * @author: Kai Kühn / Ontoprise / 2009
 *
 */
class RequireConfigElement extends ConfigElement {
	var $file;
	var $remove;
	public function __construct($child) {
		parent::__construct("require");
		$this->file = $child->attributes()->file;
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove == "true");
	}

	public function apply(& $ls, $ext_id, $userValues) {
		if ($this->remove) {
			$this->removeRequireonce($ls, $this->file);
		} else {
			return "\nrequire_once('".$this->file."');";
		}
	}

	private function removeRequireonce(& $content, $file) {
		$file = preg_quote($file);
		$file = str_replace("/", '\/', $file);
		$toRemove = '/(require|include)(_once)?\s*\(\s*\'\s*'.$file.'\s*\'\s*\)\s*;/';
		$content = preg_replace($toRemove, "", $content);
		return true;

	}
}

/**
 * Represents a arbitrary PHP statement in the settings.
 *
 * @author: Kai Kühn / Ontoprise / 2009
 *
 */
class PHPConfigElement extends ConfigElement {
	public function __construct($child) {
		parent::__construct("php");
		$this->name = (string) $child->attributes()->name;
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove == "true");
		$this->content = (string) $child[0];
	}

	public function apply(& $ls, $ext_id, $userValues) {
		if ($this->remove) {
			$fragment = self::getExtensionFragment($ext_id, $ls);
			$start = strpos($fragment, "/*php-start-$this->name*/");
			$end = strpos($fragment, "/*php-end-$this->name*/");
			if ($start !== false && $end !== false) {
				$part1 = substr($fragment, 0, $start);
				$part2 = substr($fragment, $end + strlen("/*php-end-$this->name*/"));
				$fragment =  $part1.$part2;
				$this->replaceExtensionFragment($ext_id, $fragment, $ls);
			}

		} else {
			return "\n/*php-start-$this->name*/\n".trim($this->content)."\n/*php-end-$this->name*/";
		}
	}
}

/**
 * Represents a function call in the settings.
 *
 * e.g. enableSemantics('http://localhost:8080', true);
 *
 * @author: Kai Kühn / Ontoprise / 2009
 *
 */
class FunctionCallConfigElement extends ConfigElement {
	public function __construct($child) {
		parent::__construct("function");
		$this->argumentsAsXML = $child;
		$this->functionname = $child->attributes()->name;
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove == "true");


	}

	public function apply(& $ls, $ext_id, $userValues) {
		$arguments = $this->serializeParameters($this->argumentsAsXML);
		$appliedCommand = "\n".$this->functionname."(/*param-start-".$this->functionname."*/".$arguments."/*param-end-".$this->functionname."*/);";
		if ($this->remove) {

			$fragment = self::getExtensionFragment($ext_id, $ls);
			$fragment = str_replace($appliedCommand, "", $fragment);
			$this->replaceExtensionFragment($ext_id, $fragment, $ls);

		} else {

			$fragment = self::getExtensionFragment($ext_id, $ls);
			if (is_null($fragment)) return $appliedCommand;
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



}

class IllegalArgument extends Exception {

}
?>