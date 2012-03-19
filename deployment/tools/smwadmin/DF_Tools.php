<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup DFInstaller
 *
 * Some tools for file operations and other stuff.
 *
 * @author: Kai K�hn
 *
 */


class Tools {

	/*
	 * External programs which are known to the DF.
	 * (only relevant for Windows)
	 *
	 * Maps name as it appears in registry to DF id.
	 */
	public static $df_knownPrograms;

	/**
	 * Checks if script runs on a Windows machine or not.
	 *
	 * @return boolean
	 */
	public static function isWindows(& $version = '') {
		static $thisBoxRunsWindows;
		static $os;

		if (! is_null($thisBoxRunsWindows)) {
			$version = $os;
			return $thisBoxRunsWindows;
		}

		ob_start();
		@phpinfo();
		$info = ob_get_contents();
		ob_end_clean();
		//Get Systemstring
		preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
		//Check if it consists 'windows' as string
		preg_match('/[Ww]indows.*/',$ma[1],$os);
		$thisBoxRunsWindows= count($os) > 0;


		if ($thisBoxRunsWindows && (strpos($os[0], "6.1") !== false)) {
			$version = "Windows 7";
			$os = $version;
		} else if ($thisBoxRunsWindows) {
			$version = "Windows XP";
			$os = $version;
		}

		return $thisBoxRunsWindows;
	}

	/**
	 * Checks if an array item in $arr is contained in $text
	 *
	 * @param string[] $arr
	 * @param string $text
	 */
	public static function isContainedInText($arr, $text) {
		foreach($arr as $a){
			if (strpos($text, $a) !== false) return true;
		}
		return false;
	}

	/**
	 * Creates the given directory.
	 *
	 * @param string $path
	 * @return unknown
	 */
	public static function mkpath($path) {
		if(@mkdir($path) || file_exists($path)) return true;
		return (self::mkpath(dirname($path)) && @mkdir($path));
	}

	/**
	 * Removes a directory and all its subdirectories.
	 *
	 * @param string $current_dir
	 * @param array directories to exclude
	 */
	public static function remove_dir($current_dir, $excluded = "") {
		if (substr(trim($current_dir), -1) != '/') $current_dir = trim($current_dir)."/";
		if($dir = @opendir($current_dir)) {
			while (($f = readdir($dir)) !== false) {
				if ($f == "." || $f == "..") continue;
				if ($excluded != '' && strpos(Tools::normalizePath($current_dir.$f), $excluded) !== false) {
					print "\nSkip this: ".Tools::normalizePath($current_dir.$f);
					continue;
				}
				if(filetype($current_dir.$f) == "file") {
					unlink($current_dir.$f);
				} elseif(filetype($current_dir.$f) == "dir") {
					self::remove_dir($current_dir.$f, $excluded);
				}
			}
			closedir($dir);
			@rmdir($current_dir); // do not warn cause it may contain excluded files and dirs.
		}
	}

	/**
	 * Removes a directory using native OS commands.
	 *
	 * @param string $dir
	 */
	public static function remove_dir_native($dir) {
		if (Tools::isWindows()) {
			$dir = self::makeWindowsPath($dir);
			exec("rmdir $dir /S /Q", $out, $ret);
			if ($ret != 0) return false;
		} else {
			$dir = self::makeUnixPath($dir);
			if (substr(trim($dir), -1) != '/') $dir = trim($dir)."/";
			exec("rm -rf $dir*", $out, $ret);
			if ($ret != 0) return false;
			exec("rmdir $dir", $out, $ret);
			if ($ret != 0) return false;

		}
		return true;
	}


	/**
	 * Returns all subdirectories of the given dir.
	 *
	 * @param $current_dir
	 */
	public static function get_all_dirs($current_dir) {
		$dirs = array();
		if (substr(trim($current_dir), -1) != '/') $current_dir = trim($current_dir)."/";
		if($dir = @opendir($current_dir)) {
			while (($f = readdir($dir)) !== false) {
				if ($f == "." || $f == "..") continue;
				if (is_dir($current_dir.$f)) {
					$dirs[] = $current_dir.$f;
				}
			}
			closedir($dir);

		}
		return $dirs;
	}


	/**
	 * Copy file or folder from source to destination, it can do
	 * recursive copy as well and is very smart
	 * It recursively creates the dest file or directory path if there weren't exists
	 * Situtaions :
	 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
	 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
	 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest
	 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
	 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
	 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
	 * @todo
	 *     - Should have rollback technique so it can undo the copy when it wasn't successful
	 *  - Auto destination technique should be possible to turn off
	 *  - Supporting callback function
	 *  - May prevent some issues on shared enviroments : http://us3.php.net/umask
	 * @param $source //file or folder
	 * @param $dest ///file or folder
	 * @param $options //folderPermission,filePermission
	 * @return boolean
	 */
	public static function copy_dir($source, $dest, $exclude = array(), $options=array('folderPermission'=>0755,'filePermission'=>0755))
	{
		$result=true;
			
		if (is_file($source)) {
			if ($dest[strlen($dest)-1]=='/') {
				if (!file_exists($dest)) {
					Tools::mkpath($dest);
				}
				$__dest=$dest."/".basename($source);
			} else {
				$__dest=$dest;
			}
			$result = $result && copy($source, $__dest);
			chmod($__dest,$options['filePermission']);

		} elseif(is_dir($source)) {
			if ($dest[strlen($dest)-1]=='/') {
				if ($source[strlen($source)-1]=='/') {
					//Copy only contents
				} else {
					//Change parent itself and its contents
					$dest=$dest.basename($source);
					Tools::mkpath($dest);
					chmod($dest,$options['filePermission']);
				}
			} else {
				if ($source[strlen($source)-1]=='/') {
					//Copy parent directory with new name and all its content
					Tools::mkpath($dest);
					chmod($dest,$options['filePermission']);
				} else {
					//Copy parent directory with new name and all its content
					Tools::mkpath($dest);
					chmod($dest,$options['filePermission']);
				}
			}

			$dirHandle=opendir($source);
			while(false !== ($file=readdir($dirHandle)))
			{
				if($file!="." && $file!="..")
				{
					$__dest=$dest."/".$file;

					if (in_array($source."/".$file, $exclude)) continue;
					$result = $result && self::copy_dir($source."/".$file, $__dest, $options);
				}
			}
			closedir($dirHandle);

		} else {
			$result=false;
		}
		return $result;
	}

	public static function makeUnixPath($path) {
		return str_replace("\\", "/", $path);
	}

	public static function unquotePath($path) {
		$path = trim($path);
		if ($path == '') return '';
		if (substr($path,0, 1) == '"' && substr($path, -1) == '"') {
			return substr($path, 1, strlen($path) - 2);
		}
		return $path;
	}

	public static function inStringArray($arr, $needle) {
		foreach($arr as $a) {
			if (strpos($needle, $a) !== false) {
				return true;
			}
		}
		return false;
	}

	public static function makeWindowsPath($path) {
		return str_replace("/", "\\", $path);
	}

	/**
	 * Normalizes a path, ie. uses unix file separators (/) and removes a trailing slash.
	 *
	 * @param string $path
	 * @return string normalized path
	 */
	public static function normalizePath($path) {
		$path = trim(self::makeUnixPath($path));
		$path = (substr($path, -1) == '/') ? substr($path,0, strlen($path)-1) : $path;
		return $path;
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

		$nullDevice = Tools::isWindows() ? "null" : "/dev/null";

		if (!Tools::isWindows()) {

			// both tools are delivered with the Windows version.

			// check for unzipping tool
			$found_unzip = false;
			exec("unzip > $nullDevice", $out, $ret);
			$found_unzip = ($ret == 0);
			if (!$found_unzip) return("Cannot find GNU unzip. Please install and include path to unzip into PATH-variable.");

			// check for GNU-patch tool
			exec("patch -version > $nullDevice", $out, $ret);
			$found_patch = ($ret == 0);
			if (!$found_patch) return("Cannot find GNU patch. Please install and include path to patch into PATH-variable.");
		}
		// check if PHP is in path
		$phpExe = 'php';
		if (array_key_exists('df_php_executable', DF_Config::$settings) && !empty(DF_Config::$settings['df_php_executable'])) {
			$phpExe = DF_Config::$settings['df_php_executable'];
		}
		exec("\"$phpExe\" -version > $nullDevice", $out, $ret);
		$phpInPath = ($ret == 0);
		if (!$phpInPath) return("Cannot find php. Please include path to php executable into PATH-variable.");

		// check for mysql, mysqldump
		$mysqlExe = 'mysql';
		if (array_key_exists('df_mysql_dir', DF_Config::$settings) && !empty(DF_Config::$settings['df_mysql_dir'])) {
			$mysqlExe = DF_Config::$settings['df_mysql_dir']."/bin/mysql";
		}
		exec("\"$mysqlExe\" --version > $nullDevice", $out, $ret);
		$mysql_binaries = ($ret == 0);
		if (!$mysql_binaries) return("Cannot find mysql. Please include path to mysql executable into PATH-variable.");

		// check if socket functions are available
		if (!function_exists("socket_create")) {
			return("Cannot find socket function in your PHP installation. Check if 'php_sockets'-extension is loaded in php.ini.");
		}
		if (Tools::isWindows()) unlink($nullDevice);
		return true;
	}

	public static function checkPriviledges($mwrootDir) {

		// check for root/admin access
		$errorOccured = false;
		$result = "";
		if (self::isWindows()) {
			exec("fsutil", $output, $ret); // fsutil is only accessible as administrator
			if  ($ret == 0) return true;
			$errorOccured = true; // no admin, we require this for windows
		} else {
			exec('who am i', $out);
			if (count($out) > 0 && strpos(reset($out), "root") !== false) return true; // is (most likely) root, ok
		}
			
		// otherwise check relevant locations for write acess
		$homeDir = self::getHomeDir();
		$tmpDir = self::getTempDir();
			
		if (!is_writable($mwrootDir."/LocalSettings.php")) {
			$errorOccured = true;
			$result .= "\nCannot write to $mwrootDir/LocalSettings.php";
		}
		if (!is_writable($mwrootDir."/extensions")) {
			$errorOccured = true;
			$result .= "\nCannot write to $mwrootDir/extensions";
		}
		if (!is_writable($tmpDir)) {
			$errorOccured = true;
			$result .= "\nCannot write to $tmpDir";
		}
		if (!is_writable($homeDir)) {
			$errorOccured = true;
			$result .= "\nCannot write to $homeDir";
		}


		return !$errorOccured ? true : "\nPlease run as administrator/root or with appropriate rights.\n" . $result;
	}

	/**
	 * Returns Mediawiki version or NULL if it could not be determined.
	 *
	 * @param $rootDir
	 * @return string
	 */
	public static function getMediawikiVersion($rootDir) {
		$version = NULL;
		$defaultSettings = file_get_contents($rootDir.'/includes/DefaultSettings.php');
		preg_match('/\$wgVersion\s*=\s*([^;]+)/', $defaultSettings, $matches);
		if (isset($matches[1])) {
			$version = substr(trim($matches[1]),1,-1);

		}
		return $version;
	}



	/**
	 * Provides a shortend (non-functional) form of the URL
	 * for displaying purposes.
	 *
	 * @param string $s
	 */
	public static function shortenURL($s, $maxLength = 20) {
		$s = substr($s, 7); // cut off http://
		if (strlen($s) < $maxLength) return "[$s]";
		return "[".substr($s, 0, intval($maxLength/2))."...".substr($s, -intval($maxLength/2))."]";
	}

	/**
	 * Provides a shortend form of a path
	 * for displaying purposes.
	 *
	 * @param string $s
	 */
	public static function shortenPath($s) {
		if (strlen($s) < 20) return "$s";
		return substr($s, 0, 10)."...".substr($s, -12);
	}



	/**
	 * Removes trailing whitespaces at the end (LF, CR, TAB, SPACE)
	 * and adds a single CR at the end.
	 *
	 * @param string $ls
	 * @return string
	 */
	public static function removeTrailingWhitespaces($ls) {
		for($i=strlen($ls)-1; $i > 0; $i--) {
			$c = ord($ls[$i]);
			if ($c !== 0 && $c !== 9 && $c !== 10 &&  $c !== 13 && $c !== 32) break;
		}
		return substr($ls, 0, $i+1)."\n";
	}


	/**
	 * Converts an array of string to a string.
	 *
	 * @param array of string $arr
	 * @return string
	 */
	public static function arraytostring($arr) {
		$res = "";
		foreach($arr as $a) {
			$res .= "\n".$a;
		}
		return $res;
	}

	/**
	 * Returns the home directory.
	 * (path with slashes only also on Windows)
	 *
	 * @return string
	 */
	public static function getHomeDir() {
		if (self::isWindows()) {
			exec("echo %UserProfile%", $out, $ret);
			$homedir = str_replace("\\", "/", reset($out));
			if (empty($homedir)) return NULL;
			return $homedir;
		} else {
			exec('echo $HOME', $out, $ret);
			$homedir = reset($out);
			if (empty($homedir)) return NULL;
			return $homedir;
		}
	}

	/**
	 * Returns the temp directory.
	 * (path with slashes only also on Windows)
	 *
	 * @return string
	 */
	public static function getTempDir() {
		if (self::isWindows()) {
			exec("echo %TEMP%", $out, $ret);
			$tmpdir = str_replace("\\", "/", reset($out));
			if (empty($tmpdir)) return 'c:\temp'; // fallback
			$parts = explode(";", $tmpdir);
			$tmpdir = reset($parts);
			return trim($tmpdir);
		} else {
			@exec('echo $TMPDIR', $out, $ret);
			if ($ret == 0) {
				$val = reset($out);
				$tmpdir = ($val == '' || $val === false) ? '/tmp' : $val;
				if (empty($tmpdir)) return '/tmp'; // fallback
				$parts = explode(":", $tmpdir);
				$tmpdir = reset($parts);
				return trim($tmpdir);
			} else {
				return '/tmp'; // fallback if echo fails for some reason
			}
		}
	}

	/**
	 * Returns the program directory. On Linux it is simply /usr/local/share
	 * (path with slashes only also on Windows)
	 *
	 * @return string
	 */
	public static function getProgramDir() {
		if (self::isWindows()) {
			exec("echo %ProgramFiles%", $out, $ret);
			return str_replace("\\", "/", reset($out));
		} else {
			return "/usr/local/share";
		}
	}

	/**
	 * Reads a deploy descriptor from a bundle (zip file).
	 *
	 * @param string $filePath bundle as zip file (absolute or relative)
	 * @param string $tempFolder a (writeable) temporary folder
	 * @param string $mwrootPath (if omitted unzip is supposed to be in current path)
	 * @return DeployDescriptor or NULL if it could not be found.
	 */
	public static function unzipDeployDescriptor($filePath, $tempFolder, $mwrootPath = "") {
		global $dfgOut;
		$filePath = Tools::makeUnixPath($filePath);
		if (!file_exists($filePath)) return NULL;
		$unzipExe = empty($mwrootPath) ? 'unzip' : self::getUnzipPath($mwrootPath);
		exec($unzipExe.' -l "'.$filePath.'"', $output, $res);
		foreach($output as $o) {
			if (strpos($o, "deploy.xml") !== false) {
				$out = $o;
				break;
			}
		}
		if (!isset($out)) return NULL;
		$tempFile = $tempFolder."/".uniqid();
		$help1 = explode(" ", $out);
		$help2 = array_reverse($help1);
		$dd_path = reset($help2);
		exec($unzipExe.' -o -j "'.$filePath.'" "'.$dd_path.'" -d "'.$tempFile.'"', $output, $res);
		$dd = new DeployDescriptor(file_get_contents($tempFile."/deploy.xml"));
		return $dd;
	}

	/**
	 * Checks the integrity of a bundle.
	 *
	 *  (1) is the path structure correct? ie. is the install dir one of the root folders
	 *      of the bundle?
	 *
	 * @param string $filePath bundle as zip file (absolute or relative)
	 * @param DeployDescriptor $dd
	 * @param string $mwrootPath (if omitted unzip is supposed to be in current path)
	 */
	public static function checkBundleIntegrity($filePath, $dd, $mwrootPath = "") {
		global $dfgOut;
		$filePath = Tools::makeUnixPath($filePath);
		if (!file_exists($filePath)) return NULL;
		$unzipExe = empty($mwrootPath) ? 'unzip' : self::getUnzipPath($mwrootPath);
		exec($unzipExe.' -l "'.$filePath.'"', $output, $res);
		$instDir = $dd->getInstallationDirectory();
		$isValid = true;
		foreach($output as $o) {
			if (strpos($o, "$instDir") !== false) {
				if (strpos($o, " $instDir") === false) {
					$isValid = false;
					break;
				}
			}
		}
		return $isValid;
	}

	/**
	 * Unzips a file from a zip archive.
	 *
	 * @param $zipFile Full path to zip file
	 * @param $filePath File to extract from zip (may be partial if unique)
	 * @param $destDir Destination file dir
	 * @param string $mwrootPath (if omitted unzip is supposed to be in current path)
	 *
	 * @return boolean True, if succesfull
	 */
	public static function unzipFile($zipFile, $filePath, $destDir, $mwrootPath = "") {
		$zipFile = Tools::makeUnixPath($zipFile);
		if (!file_exists($zipFile)) return NULL;
		$unzipExe = empty($mwrootPath) ? 'unzip' : self::getUnzipPath($mwrootPath);
		exec($unzipExe.' -l "'.$zipFile.'"', $output, $res);
		foreach($output as $o) {
			if (strpos($o, $filePath) !== false) {
				$out = $o;
				break;
			}
		}
		if (!isset($out)) return false;
		$dd_path = reset(array_reverse(explode(" ", $out)));
		exec($unzipExe.' -o -j "'.$zipFile.'" "'.$dd_path.'" -d "'.$destDir.'"', $output, $res);
		return $res == 0;
	}

	/**
	 * Creates an archive of a given directory or file (recursively in case of dir)
	 *
	 * Note: Does not include file named unzip.exe.
	 *
	 * @param $source given directory or file (full path)
	 * @param $archiveFile archive file (full path)
	 * @param $mwrootPath
	 *
	 * @return boolean
	 */
	public function makeZip($source, $archiveFile, $mwrootPath = "") {
		$zipExe = empty($mwrootPath) ? 'zip' : self::getZipPath($mwrootPath);
		if (self::isWindows()) {
			// we have to make a distinction here because on Windows 7z is used to create archives.
			$command = "$zipExe a -tzip -r -x!unzip.exe \"$archiveFile\" \"$source/*\"";
		} else {
			$command = "$zipExe -r \"$archiveFile\" \"$source\" -x \\*unzip.exe";
		}
		exec($command, $out, $ret);
		return $ret == 0;
	}

	/**
	 * Unzips a zip archive into destination
	 * @param $zipFile
	 * @param $destination
	 *
	 * @return boolean
	 */
	public function unpackZip($zipFile, $destination, $mwrootPath = "") {
		$zipFile = Tools::makeUnixPath($zipFile);
		if (!file_exists($zipFile)) return NULL;
		$unzipExe = empty($mwrootPath) ? 'unzip' : self::getUnzipPath($mwrootPath);
		exec($unzipExe.' -o "'.$zipFile.'" -d "'.$destination.'"', $output, $res);
		return $ret == 0;
	}


	private static function getUnzipPath($mwrootPath) {
		if (self::isWindows()) {
			return '"'.$mwrootPath.'/deployment/tools/unzip.exe"';
		} else {
			return 'unzip'; // assume it is in path on Linux
		}
	}

	private static function getZipPath($mwrootPath) {
		if (self::isWindows()) {
			return '"'.$mwrootPath.'/deployment/tools/maintenance/export/7za.exe"';
		} else {
			return 'zip'; // assume it is in path on Linux
		}
	}



	/**
	 * Removes the (last) file ending.
	 *
	 * @param $filename
	 */
	public static function removeFileEnding($filename) {
		$index = strrpos($filename, ".");
		if ($index === false) return $filename;
		return substr($filename, 0, $index);
	}

	/**
	 * Returns the location of a file (first occurence if more than on exist).
	 *
	 * @param string $name
	 * @param string rootdir MW root folder
	 */
	public static function whereis($name, $rootDir = NULL) {
		if (self::isWindows()) {
			if (!is_null($rootDir)) chdir("$rootDir/deployment/tools");
			exec("whereis.bat $name", $out, $ret);
			return str_replace("\\", "/", reset($out));
		} else {
			//FIXME: can not handle whitespaces in path
			exec("whereis $name", $out, $ret);
			$result = reset($out);
			list($prg, $pathstr) = explode(":", $result);
			$paths = explode(" ", trim($pathstr));
			return reset($paths);
		}
	}

	/**
	 * Detects if a particular process is running.
	 *
	 * @param string $name Program name (e.g. php)
	 *
	 * @return boolean
	 */
	public static function isProcessRunning($name) {
		if (self::isWindows()) {
			@exec("tasklist /NH /V /FO CSV", $out, $ret);
			if ($ret != 0) return false;
			foreach($out as $l) {
				$parts = explode(",", trim($l));
				if (strpos($parts[0], "$name.exe") !== false) return true;
			}
			return false;
		} else {
			$path = self::whereis($name);
			@exec("ps ax | grep $path", $out, $ret);
			foreach($out as $l) {
				$l = preg_replace("/\\s+|\t+/", " ", $l);
				$parts = explode(" ", trim($l));
				if ($parts[4] == $path || strpos($parts[4], $name) !== false) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Checks if the given service is running.
	 *
	 * @param $processName Process name (Windows)
	 * @param $processScript Process script (Linux)
	 */
	public static function isServiceRunning($processName, $processScript) {
		if (self::isWindows()) {
			return self::isProcessRunning(self::translateProcessName($processName));
		} else {
			if (strpos($processScript, "/etc/init.d") !== false) {
				@exec("$processScript status", $out, $ret);
			} else {
				@exec("/sbin/status $processScript", $out, $ret);
				if (self::inStringArray($out, "$processScript stop")) {
					$ret = 1;
				}
			}
			return ($ret == 0);
		}
	}

	/**
	 * Checks if certain process/services are running.
	 *
	 * On Windows a list of process names is expected.
	 * On Linux a list of service scripts is expected.
	 *
	 * @param string $processNames Process names, comma separated
	 * @param string $processScripts Process scripts, comma separated
	 *
	 * @return boolean[]
	 */
	public static function areServicesRunning($processNames, $processScripts) {
		if (self::isWindows()) {
			$processNames = explode(",",$processNames);
			$translated=array();
			foreach($processNames as $name) {
				$translated[] = self::translateProcessName($name);
			}
			$doesRun = self::areProcessesRunning($translated);
		} else {
			$doesRun=array();
			$processScripts = explode(",",$processScripts);
			foreach($processScripts as $script) {
				if (strpos($script, "/etc/init.d") !== false) {
					@exec("$script status", $out, $ret);
					$doesRun[] = ($ret == 0);
				} else {
					@exec("/sbin/status $script", $out, $ret);
					if (self::inStringArray($out, "$script stop")) {
						$doesRun[] = false;
					} else {
						$doesRun[] = ($ret == 0);
					}
				}

			}
		}
		return $doesRun;
	}

	/**
	 * Translates the process names to the names (or paths) used by
	 * the actual platform.
	 *
	 * @param string $name Processname
	 * @return string
	 */
	private static function translateProcessName($name) {
		global $mwrootDir;
		if (Tools::isWindows()) {
			switch($name) {
				case "apache": return "httpd";
				case "mysql": return "mysqld";
				case "solr": return "solr";
				case "tsc": return array("tsc", "tsc-service");
				case "memcached": return "memcached";
				default: return $name;
			}
		} else {
			return $name; // not necessary for Linux, because it is checked via init.d scripts.
		}


	}

	public static function runProcess($commandLineToStart, $runAsUser = NULL, $password = NULL) {
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			if (!is_null($runAsUser)) {
				global $mwrootDir;
				$command = $mwrootDir."/deployment/tools/internal/pcwrunas/pcwRunAs4.exe ";
				$command .= "/u $runAsUser /p $password /app cmd /arg \"/c $commandLineToStart\"";

				$oExec = $wshShell->Run("$command 2>&1", 7, false);

			} else {
				@chdir(dirname($commandLineToStart));
				$oExec = $wshShell->Run("$commandLineToStart 2>&1", 7, false);
			}
		} else {
			$nullResult = `$commandLineToStart 2>&1 &`;
		}
		return "true";
	}

	/**
	 * Detects if a processes are running.
	 *
	 * @param string[] $names Program name (e.g. php)
	 *                 Single entries can be arrays of alternative process names.
	 *
	 *  Example:
	 *
	 *     array( "apache", "mysql", array("tsc","tsc-service"), "memcached")
	 *
	 *  Detects if "apache", "mysql" , "tsc" OR "tsc-service" and "memcached" are running.
	 *
	 * @return boolean
	 */
	public static function areProcessesRunning($names) {
		if (self::isWindows()) {
			@exec("tasklist /NH /V /FO CSV", $out, $ret);
			if ($ret != 0) return false;
			$results = array();
			foreach($names as $name) {
				$found = false;
				foreach($out as $l) {
					$parts = explode(",", trim($l));
					if (is_array($name)) {
						foreach($name as $n) {
							if (strpos($parts[0], "$n.exe") !== false) {
								$results[] = true;
								$found = true;
								break;
							}
						}
						if ($found) break;
					} else {
						if (strpos($parts[0], "$name.exe") !== false) {
							$results[] = true;
							$found = true;
							break;
						}
					}
				}
				if (!$found) $results[] = false;
			}
		} else {
			$results = array();
			foreach($names as $name) {
				$path = self::whereis($name);
				@exec("ps ax | grep $path", $out, $ret);
				$found = false;
				foreach($out as $l) {
					$l = preg_replace("/\\s+|\t+/", " ", $l);
					$parts = explode(" ", trim($l));
					if ($parts[4] == $path || strpos($parts[4], $name) !== false) {
						$results[] = true;
						$found = true;
						break;
					}
				}
				if (!$found) $results[] = false;
			}
		}
		return $results;
	}


	/**
	 * Returns file extension
	 *
	 * @param $filePath
	 * @return string
	 */
	public static function getFileExtension($filePath) {
		$parts = explode(".", $filePath);
		$rev_parts = array_reverse($parts);
		$extension = reset($rev_parts);
		return $extension;
	}

	/**
	 * Checks if the given filename has a common ontology ending.
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public static function checkIfOntologyFile($filename) {
		$ext = self::getFileExtension($filename);
		return ($ext == 'owl' || $ext == 'rdf' || $ext == 'obl'
		|| $ext == 'n3' || $ext == 'nt' || $ext == 'ttl');
	}

	/**
	 * Remove all non-alpanumeric characters to that it
	 * suits as a filename.
	 *
	 * @param string $s
	 *
	 * @return string
	 */
	public static function makeFileName($s) {
		return preg_replace('/[^\w_]/', "", $s);
	}

	/**
	 * Escapes XML attribute values
	 * @param $text
	 */
	public static function escapeForXMLAttribute($text) {
		return str_replace('"', "&quot;", $text);
	}

	/**
	 * Returns the installation directory of Ontoprise software on Windows.
	 * Note: Returns always NULL on linux.
	 *
	 * @param string $programname (Fragment of) program name or deploy ID. By default search for all.
	 *
	 * @return array( Programname => directory path )
	 */
	public static function getOntopriseSoftware($id = '') {
		if (!Tools::isWindows($os)) return NULL;

		exec("reg QUERY \"HKEY_CURRENT_USER\Software\Ontoprise\" /s 2>&1", $out, $res);

		if ($res != 0) return NULL;

		// convert IDs into program names (as far as known)
		// TSC is the only registered program for now.
		$knownPrograms = self::$df_knownPrograms;

		$knownPrograms = array_flip($knownPrograms);
		if (array_key_exists($id, $knownPrograms)) {
			$programs = array($knownPrograms[$id]);
		} else {
			$programs = $knownPrograms;
		}

		$result=array();
		$n = count($out);
		foreach($programs as $programname) {
			for($i = 0; $i < $n; $i++) {
				if (stripos($out[$i], "HKEY_CURRENT_USER\\Software\\Ontoprise\\") !== false
				&& (stripos($out[$i], $programname) !== false || $programname == '')) {
					while ($i+1 < count($out)
					&& stripos($out[$i+1], "(Standard)") === false
					&& stripos($out[$i+1], "<NO NAME>") === false
					&& stripos($out[$i+1], "HKEY_CURRENT_USER\\Software\\Ontoprise\\") === false
					) $i++;
					if ($i+1 == $n) break;
					if (stripos($out[$i+1], "HKEY_CURRENT_USER\\Software\\Ontoprise\\") !== false) continue;
					$defValue = $out[$i+1];
					$parts = explode("   ", $defValue);
					$prgName = substr($out[$i], strlen("HKEY_CURRENT_USER\\Software\\Ontoprise\\"));
					$pathAtFirst = array_reverse($parts);
					$result[$programname][] = reset($pathAtFirst);
				}
			}
		}
		return $result;
	}

	/**
	 * Shows a fatal error which aborts installation.
	 * Note: Requires the global output object $dfgOut to be set.
	 *
	 * @param string $e message
	 */
	public static function exitOnFatalError($e) {
		global $dfgOut;

		if (!isset($dfgOut)) {
			die(DF_TERMINATION_ERROR);
		}
		$dfgOut->outputln();

		if (is_string($e)) {
			if (!empty($e)) $dfgOut->outputln($e, DF_PRINTSTREAM_TYPE_FATAL);
		}
		$dfgOut->outputln();
		$dfgOut->outputln();

		// stop installation
		die(DF_TERMINATION_ERROR);
	}

	/**
	 * Creates a MW deploy descriptor analyzing the current installation.
	 *
	 * @param string $rootDir MW root directory
	 * @param DFVersion $ver Version (if missing if will be read from the underlying MW)
	 *
	 * @return string (xml)
	 */
	public static function createMWDeployDescriptor($rootDir, $ver = NULL) {
		$version = is_null($ver) ? new DFVersion(self::getMediawikiVersion($rootDir)) : $ver;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
                <deploydescriptor>
                    <global>
                        <version>'.$version->toVersionString().'</version>
                        <id>mw</id>
                        <vendor>Ontoprise GmbH</vendor>
                        <maintainer>Wikimedia foundation</maintainer>
                        <instdir/>
                        <description>MediaWiki is a free software open source wiki package written in PHP, originally for use on Wikipedia.</description>
                        <helpurl>http://www.mediawiki.org/wiki/MediaWiki</helpurl>
                        <license>GPL-v2</license>
                    </global>
                    <codefiles/>
                    <wikidumps/>
                    <resources/>
                    <configs>
                        <update>
                            <script file="maintenance/update.php"/>
                        </update>
                    </configs>
                    </deploydescriptor>';

		return $xml;
	}

	/**
	 * Reads the full paths of non-public apps.
	 *
	 * @param string $mwroot
	 *
	 * @return array($id => $path)
	 */
	public static function getNonPublicAppPath($mwroot) {
		$result = array();
		$content = @file_get_contents("$mwroot/deployment/config/externalapps");
		if ($content === false) return array();
		$lines = explode("\n", $content);
		foreach($lines as $l) {
			if (empty($l)) continue;
			list($id, $path) = explode("=", $l);
			$result[$id] = $path;
		}
		return $result;
	}

	/**
	 * Sets the full path of a non-public app.
	 *
	 * @param string $mwroot
	 * @param string $id
	 * @param string $path
	 *
	 * @return boolean True on success
	 */
	public static function setNonPublicAppPath($mwroot, $id, $path) {
		$result = self::getNonPublicAppPath($mwroot);
		$result[$id] = $path;
		$handle = fopen("$mwroot/deployment/config/externalapps", "w");
		if ($handle === false) return false;
		foreach($result as $id => $path) {
			fwrite($handle, "$id=$path\n");
		}
		fclose($handle);
		return true;
	}

	/**
	 * Changes a node in the global section of the deploy descriptor.
	 *
	 * @param DeployDescriptor $dd
	 * @param string $tag
	 * @param string $newValue
	 *
	 * @return DeployDescriptor
	 */
	public static function changeGlobalSection($dd, $tag, $newValue) {
		$xml = $dd->getXML();
		$dom = new DOMDocument("1.0");
		$dom->loadXML($xml);

		// replace $tag node
		$oldNode = $dom->getElementsByTagName($tag)->item(0);
		$newNode = $dom->createElement($tag);
		$newNode->appendChild($dom->createTextNode($newValue));
		$globalNode = $dom->getElementsByTagName("global")->item(0);
		if (is_null($oldNode)) {
			$globalNode->appendChild($newNode);
		} else {
			$globalNode->replaceChild($newNode, $oldNode);
		}

		return new DeployDescriptor($dom->saveXML());
	}

	/**
	 * Opens a pipe to a process executed by forking the $command.
	 *
	 * @param string $command
	 * @param PrintoutStream $out
	 */
	public static function outStream($command, & $out) {
		if (!($p=popen("($command)2>&1","r"))) {
			return 126;
		}

		while (!feof($p)) {
			$line=fgets($p,1000);
			$out->output($line);
		}
		pclose($p);
	}
}

Tools::$df_knownPrograms = array('Triplestore Connector Professional' => 'tscprof',
                                'Triplestore Connector Basic' => 'tsc' );
