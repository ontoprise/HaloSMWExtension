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
		if(@mkdir($path) || file_exists($path)) return true;
		return (self::mkpath(dirname($path)) && @mkdir($path));
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
					self::remove_dir($current_dir.$f);
				}
			}
			closedir($dir);
			rmdir($current_dir);
		}
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
    public static function copy_dir($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
    {
        $result=false;
       
        if (is_file($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if (!file_exists($dest)) {
                    cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
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
                    @mkdir($dest);
                    chmod($dest,$options['filePermission']);
                }
            } else {
                if ($source[strlen($source)-1]=='/') {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                }
            }

            $dirHandle=opendir($source);
            while($file=readdir($dirHandle))
            {
                if($file!="." && $file!="..")
                {
                    if(!is_dir($dirsource."/".$file)) {
                        $__dest=$dest."/".$file;
                    } else {
                        $__dest=$dest."/".$file;
                    }
                    //echo "$source/$file ||| $__dest<br />";
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
		if (Tools::isWindows()) {
			exec("7z -version > $nullDevice", $out, $ret);
			$found_unzip = ($ret == 7);
			if (!$found_unzip) return("7-zip is missing or not in PATH. Please install");

		} else {
			exec("unzip > $nullDevice", $out, $ret);
            $found_unzip = ($ret == 0);
            if (!$found_unzip) return("7-zip is missing or not in PATH. Please install");
		}
		

		// check for GNU-patch tool
		
		exec("patch -version > $nullDevice", $out, $ret);
		$found_patch = ($ret == 0);
		if (!$found_patch) return("GNU-Patch is missing or not in PATH. Please install");

		// check if PHP is in path
		
		exec("php -version > $nullDevice", $out, $ret);
		$phpInPath = ($ret == 0);
		if (!$phpInPath) return("PHP is not in PATH. Please install");
        
		// check for mysql, mysqldump
		exec("mysql --version > $nullDevice", $out, $ret);
		$mysql_binaries = ($ret == 0);
		if (!$mysql_binaries) return("MySQL binaries are not on path. Please install.");
		
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
		$version = trim($version);
		if (strlen($version) == 3) {
			return substr($version, 0, 1).".".substr($version, 1);
		} else {
			return substr($version, 0, 1).".".substr($version, 1,2).".".substr($version,3);
		}
	}
	
	/**
	 * Removes trailing whitespaces at the end (LF, CR, TAB, SPACE)
	 * and adds a single CR at the end.
	 * 
	 * @param string $ls
	 * @return string
	 */
	public static function removeTrailingWhitespaces($ls) {
		for($i=strlen($ls); $i > 0; $i--) {
			$c = ord($ls[$i]);
			if ($c !== 0 && $c !== 9 && $c !== 10 &&  $c !== 13 && $c !== 32) break;
		}
		return substr($ls, 0, $i+1)."\n";
	}
}
?>