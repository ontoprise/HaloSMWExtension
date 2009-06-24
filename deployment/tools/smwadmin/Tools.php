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
}
?>