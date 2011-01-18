<?php

/*  Copyright 2009, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DFInstaller
 *
 * Some tools for file operations and other stuff.
 *
 * @author: Kai K�hn / ontoprise / 2009
 *
 */
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
		$thisBoxRunsWindows= count($os) > 0;
		return $thisBoxRunsWindows;
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
	public static function remove_dir($current_dir, $exclude_dirs = array()) {
		if (substr(trim($current_dir), -1) != '/') $current_dir = trim($current_dir)."/";
		if($dir = @opendir($current_dir)) {
			while (($f = readdir($dir)) !== false) {
				if ($f == "." || $f == "..") continue;
				if (in_array(Tools::normalizePath($current_dir.$f), $exclude_dirs)) continue;
				if(filetype($current_dir.$f) == "file") {
					unlink($current_dir.$f);
				} elseif(filetype($current_dir.$f) == "dir") {
					self::remove_dir($current_dir.$f);
				}
			}
			closedir($dir);
			@rmdir($current_dir); // do not warn cause it may contain excluded files and dirs.
		}
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
		$result=false;
			
		if (is_file($source)) {
			if ($dest[strlen($dest)-1]=='/') {
				if (!file_exists($dest)) {
					Tools::mkpath($dest);
				}
				$__dest=$dest."/".basename($source);
			} else {
				$__dest=$dest;
			}
			$result=copy($source, $__dest);
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
			while($file=readdir($dirHandle))
			{
				if($file!="." && $file!="..")
				{
					$__dest=$dest."/".$file;
					//echo "$source/$file ||| $__dest<br />";
					if (in_array($source."/".$file, $exclude)) continue;
					$result=self::copy_dir($source."/".$file, $__dest, $options);
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

		// check for unzipping tool
		$found_unzip = false;
		exec("unzip > $nullDevice", $out, $ret);
		$found_unzip = ($ret == 0);
		if (!$found_unzip) return("Cannot find GNU unzip.exe. Please install and include path to unzip.exe into PATH-variable.");

		// check for GNU-patch tool
		exec("patch -version > $nullDevice", $out, $ret);
		$found_patch = ($ret == 0);
		if (!$found_patch) return("Cannot find GNU patch.exe. Please install and include path to patch.exe into PATH-variable.");

		// check if PHP is in path
		exec("php -version > $nullDevice", $out, $ret);
		$phpInPath = ($ret == 0);
		if (!$phpInPath) return("Cannot find php.exe. Please include path to php.exe into PATH-variable.");

		// check for mysql, mysqldump
		exec("mysql --version > $nullDevice", $out, $ret);
		$mysql_binaries = ($ret == 0);
		if (!$mysql_binaries) return("Cannot find mysql.exe. Please include path to mysql.exe into PATH-variable.");

		if (Tools::isWindows()) unlink($nullDevice);
		return true;
	}

	public static function checkPriviledges() {
		if (self::isWindows()) {
			exec("fsutil", $output, $ret);
			if  ($ret == 0) return true;
		} else {
			exec('who am i', $out);
			if (count($out) > 0 && strpos(reset($out), "root") !== false) return true; // is root

			// try to create and delete a file in local dir and temp dir.
			$touched = touch("foo_bar_test");
			exec('rm foo_bar_test', $output, $ret);
			$touched2 = touch("/tmp/foo_bar_test");
			exec('rm /tmp/foo_bar_test', $output, $ret2);
			$removed = ($ret == 0) && ($ret2 == 0);

			// if true, we can assume that the user has proper rights
			if ($removed && $touched && $touched2) return true;
		}
		return "You have to run smwadmin as admin or root.";
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
	 * Add separators (.) to distinguish between minor and major version.
	 *
	 * @param string $version
	 * @return string
	 */
	public static function addVersionSeparators($version) {
		list($v, $patchlevel) = $version;
		$patchlevel = $patchlevel === 0 ? "" : "_".$patchlevel;
		$v = trim($v);
		if (strlen($v) == 3) {
			return substr($v, 0, 1).".".substr($v, 1).$patchlevel;
		} else {
			return substr($v, 0, 1).".".substr($v, 1,2).".".substr($v,3).$patchlevel;
		}
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
	 * Sorts and compacts versions. That means it filters out all doubles.
	 *
	 * @param array of tuples(version, patchlevel) $versions
	 */
	public static function sortVersions(& $versions) {

		// sort
		for($i = 0; $i < count($versions); $i++) {
			for($j = 0; $j < count($versions)-1; $j++) {

				list($ver1, $pl1) = $versions[$j];
				list($ver2, $pl2) = $versions[$j+1];
				if ($ver1 === $ver2) {
					if ($pl1 < $pl2) {
						$help = $versions[$j];
						$versions[$j] = $versions[$j+1];
						$versions[$j+1] = $help;
					}
				}
				if ($ver1 < $ver2) {
					$help = $versions[$j];
					$versions[$j] = $versions[$j+1];
					$versions[$j+1] = $help;
				}
			}
		}

		// remove doubles
		$result = array();
		$last = NULL;
		for($i = 0; $i < count($versions); $i++) {
			if (is_null($last)) {
				$last = $versions[$i];
				continue;
			}

			list($ver1, $pl1) = $last;
			list($ver2, $pl2) = $versions[$i];
			if($ver1 === $ver2 || $pl1 === $pl2) {
				$versions[$i] = NULL;
			} else {
				$last = $versions[$i];
			}

		}

		$versions = array_diff($versions, array(NULL));
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

	public static function getXSDValue($dataValue) {
		return array_shift($dataValue->getDBkeys());
	}

	public static function checkPackageProperties() {
		global $dfgLang;
		global $wgContLang;
		$propNSText = $wgContLang->getNsText(SMW_NS_PROPERTY);
		// check if the required properties exist
		$check = true;
		// Property:Dependecy
		$pDependencyTitle = Title::newFromText($dfgLang->getLanguageString('df_dependencies'), SMW_NS_PROPERTY);
		$pDependency = SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_dependencies'));
		$pDependencyTypeValue = $pDependency->getTypesValue();

		if (reset($pDependencyTypeValue->getDBkeys()) != '_rec') {
			print "\n'".$pDependencyTitle->getPrefixedText()."' is not a record type.";
			$check = false;
		}

		$pDependencyTypes = reset(smwfGetStore()->getPropertyValues( $pDependency->getWikiPageValue(), SMWPropertyValue::makeProperty( '_LIST' ) ));
		if ($pDependencyTypes !== false) {
			$typeIDs = explode(";",reset($pDependencyTypes->getDBkeys()));
			if (count($typeIDs) != 3) {
				print "\n'".$pDependencyTitle->getPrefixedText()."' wrong number of fields.";
				$check = false;
			}
		} else {
			print "\nCould not read fields of '".$pDependencyTitle->getPrefixedText();
			$check = false;
		}
		list($ext_id, $from, $to) = $typeIDs;
		if ($ext_id != '_str' || $from != '_num' || $to != '_num') {
			print "\n'".$pDependencyTitle->getPrefixedText()."' property has wrong field types.";
			$check = false;
		}

		// Ontology version
		$pOntologyVersionTitle = Title::newFromText($dfgLang->getLanguageString('df_ontologyversion'), SMW_NS_PROPERTY);
		$pOntologyVersion = SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_ontologyversion'));
		$pOntologyVersionValue = $pOntologyVersion->getTypesValue();
		if (reset($pOntologyVersionValue->getDBkeys()) != '_num') {
			print "\n'".$pOntologyVersionTitle->getPrefixedText()."' is not a number type.";
			$check = false;
		}
		// Installation dir
		$pInstallationDirTitle = Title::newFromText($dfgLang->getLanguageString('df_instdir'), SMW_NS_PROPERTY);
		$pInstallationDir = SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_instdir'));
		$pInstallationDirValue = $pInstallationDir->getTypesValue();
		if (reset($pInstallationDirValue->getDBkeys()) != '_str') {
			print "\n'".$pInstallationDirTitle->getPrefixedText()."' is not a string type.";
			$check = false;
		}
		// Vendor
		$pVendorTitle = Title::newFromText($dfgLang->getLanguageString('df_ontologyvendor'), SMW_NS_PROPERTY);
		$pVendor = SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_ontologyvendor'));
		$pVendorValue = $pVendor->getTypesValue();
		if (reset($pVendorValue->getDBkeys()) != '_str') {
			print "\n'".$pVendorTitle->getPrefixedText()."' is not a string type.";
			$check = false;
		}
		// Description
		$pDescriptionTitle = Title::newFromText($dfgLang->getLanguageString('df_description'), SMW_NS_PROPERTY);
		$pDescription = SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_description'));
		$pDescriptionValue = $pDescription->getTypesValue();
		if (reset($pDescriptionValue->getDBkeys()) != '_str') {
			print "\n'".$pDescriptionTitle->getPrefixedText()."' is not a string type.";
			$check = false;
		}
		return $check;
	}
}
