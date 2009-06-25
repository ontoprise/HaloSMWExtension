<?php
class Tools {

	/**
	 * Checks if script runs on a Windows machine or not.
	 *
	 * @return boolean
	 */
	public static function isWindows() {
		static $thisBoxRunsWindows;

		if (! is_null($thisBoxRunsWindows)) return $thisBoxRunsWindows;

		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();
		//Get Systemstring
		preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
		//Check if it consists 'windows' as string
		preg_match('/[Ww]indows/',$ma[1],$os);
		if($os[0]=='' && $os[0]==null ) {
			$thisBoxRunsWindows= false;
		} else {
			$thisBoxRunsWindows = true;
		}
		return $thisBoxRunsWindows;
	}

	/**
	 * Creates the given directory.
	 *
	 * @param string $path
	 * @return unknown
	 */
	public static function mkpath($path) {
		if(mkdir($path) || file_exists($path)) return true;
		return (mkpath(dirname($path)) && mkdir($path));
	}

	/**
	 * Removes a directory and all its subdirectories.
	 *
	 * @param string $current_dir
	 */
	public static function remove_dir($current_dir) {
		if (strpos(trim($current_dir), -1) != '/') $current_dir = trim($current_dir)."/";
		if($dir = @opendir($current_dir)) {
			while (($f = readdir($dir)) !== false) {
				if($f > '0' and filetype($current_dir.$f) == "file") {
					unlink($current_dir.$f);
				} elseif($f > '0' and filetype($current_dir.$f) == "dir") {
					self::remove_dir($current_dir.$f."\\");
				}
			}
			closedir($dir);
			rmdir($current_dir);
		}
	}

	public static function makeUnixPath($path) {
		return str_replace("\\", "/", $path);
	}

	/**
	 * Checks if all needed tools are available.
	 *
	 * - 7zip (Windows), unzip (linux)
	 * - patch
	 * - php
	 *
	 * @return mixed. True if all tools are installed, a string is something is missing.
	 */
	public static function checkEnvironment() {

		// check for unzipping tool
		$found_unzip = false;
		if (Tools::isWindows()) {
			$ret = exec('7z', $out);
			foreach($out as $l) {
				if (strpos($l, "7-Zip") !== false) {
					$found_unzip = true;
					break;
				}
			}
			if (!$found_unzip) return("7-zip is missing or not in PATH. Please install");

		} else {
			$ret = exec('unzip', $out);
			foreach($out as $l) {
				if (strpos($l, "UnZip") !== false) {
					$found_unzip = true;
					break;
				}
			}
			if (!$found_unzip) return("unzip is missing or not in PATH. Please install");

		}

		// check for GNU-patch tool
		$found_patch = false;
		$ret = exec('patch -version', $out);
		foreach($out as $l) {
			if (strpos($l, "patch") !== false) {
				$found_patch = true;
				break;
			}
		}
		if (!$found_patch) return("GNU-Patch is missing or not in PATH. Please install");

		// check if PHP is in path
		$phpInPath = false;
		$ret = exec('php -version', $out);
		foreach($out as $l) {
			if (strpos($l, "PHP") !== false) {
				$phpInPath = true;
				break;
			}
		}
		if (!$phpInPath) return("PHP is not in PATH. Please install");

		return true;
	}

	public static function checkPriviledges() {
		if (self::isWindows()) {
			return true; // assume root priviledge. FIXME: Howto find out?
		} else {
			exec('who am i', $out);
			if (count($out) > 0 && strpos($out, "root") !== false) return true; // is root
				
			// try to create and delete a file.
			$success = touch("testpriv");
			$err = exec('rm testpriv');
			// if true, we can assume that the user has proper rights
			if ($success && $err == 0) return true;
		}
		return "Missing rights. Please start as admin or root.";
	}
}
?>