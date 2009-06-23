<?php

if (defined('DEBUG_MODE') && DEBUG_MODE == true) {
	require_once 'deployment/io/HttpDownload.php';
	require_once 'deployment/descriptor/DeployDescriptorParser.php';
} else {
	require_once '../io/HttpDownload.php';
    require_once '../descriptor/DeployDescriptorParser.php';
}

// this URL is supposed to be fix forever
define("SMWPLUS_REPOSITORY", "http://localhost/mediawiki/deployment/tests/testcases/resources/installer/");

/**
 * Allows access on the global HALO package repository.
 *
 * @author: Kai Kühn
 *
 */
class PackageRepository {

	// repository DOM
	static $repo_dom = NULL;
	static $deploy_descs = array();

	/**
	 * Downloads the package repository from remote.
	 *
	 * @return PackageRepository
	 */
	private static function getPackageRepository() {
		if (!is_null(self::$repo_dom)) return self::$repo_dom;

		$d = new HttpDownload();
		$partsOfURL = parse_url(SMWPLUS_REPOSITORY. 'repository.xml');

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$res = $d->downloadAsString($path, $port, $host, NULL);

		self::$repo_dom = simplexml_load_string($res);

		return self::$repo_dom;
	}

	/*
	 * Loads package repository from string (for testing)
	 */
	public static function initializePackageRepositoryFromString($repo_xml) {
		self::$repo_dom = simplexml_load_string($repo_xml);
	}

	public static function getLatestDeployDescriptor($ext_id) {
		if (is_null($ext_id)) throw new IllegalArgument("ext must not null");
		if (array_key_exists($ext_id, self::$deploy_descs)) return self::$deploy_descs[$ext_id];

		// download descriptor
		$d = new HttpDownload();
		$partsOfURL = parse_url(SMWPLUS_REPOSITORY. "extensions/$ext_id/deploy.xml");

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$res = $d->downloadAsString($path, $port, $host, NULL);

		$dd =  new DeployDescriptorParser($res);
		self::$deploy_descs[] = $dd;
		return $dd;
	}

	public static function getDeployDescriptor($ext_id, $version) {
		if (is_null($ext_id) || is_null($version)) throw new IllegalArgument("version or ext must not null");
		if (array_key_exists($ext_id.$version, self::$deploy_descs)) return self::$deploy_descs[$ext_id.$version];

		// download descriptor
		$d = new HttpDownload();
		$partsOfURL = parse_url(SMWPLUS_REPOSITORY. "extensions/$ext_id/deploy-$version.xml");

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$res = $d->downloadAsString($path, $port, $host, NULL);

		$dd =  new DeployDescriptorParser($res);
		self::$deploy_descs[] = $dd;
		return $dd;
	}

    /**
     * Returns all available versions in descendant order.
     *
     * @param string $packageID
     * @return array of results
     */
	public static function getAllVersions($packageID) {
		$versions = self::getPackageRepository()->xpath("/root/extensions/extension[@id='$packageID']/version");
        if (count($versions) == 0) return NULL;
        $results = array();
        foreach($versions as $v) {
            $results[] = (string) $v->attributes()->ver;
        }
        sort($results, SORT_NUMERIC);
        return array_reverse($results);
	}
	/**
	 * Returns URL of latest available version of a package
	 *
	 * @param string $packageID The package ID
	 * @return URL (as string)
	 */
	public static function getLatestVersion($packageID) {
		$package = self::getPackageRepository()->xpath("/root/extensions/extension[@id='$packageID']/version[position()=last()]");
		if (count($package) == 0) return NULL;
		$download_url = (string) $package[0]->attributes()->url;
		return $download_url;
	}

	/**
	 * Returns the URL of the requested version of the package if available or NULL if not.
	 *
	 * @param string $packageID
	 * @param number $version
	 * @return URL string
	 */
	public static function getVersion($packageID, $version) {
		$package = self::getPackageRepository()->xpath("/root/extensions/extension[@id='$packageID']/version[@ver='$version']");
		if (count($package) == 0) return NULL;
		$download_url = (string) $package[0]->attributes()->url;
		return $download_url;
	}

	/**
	 * Checks if the package with the given version exists or not.
	 *
	 * @param string $packageID
	 * @param number $version Optional
	 * @return boolean
	 */
	public static function existsPackage($packageID, $version = 0) {
		if ($version > 0) {
			$package = self::getPackageRepository()->xpath("/root/extensions/extension[@id='$packageID']/version[@ver='$version']");
		} else {
			$package = self::getPackageRepository()->xpath("/root/extensions/extension[@id='$packageID']");
		}
		return count($package) > 0;
	}

	/**
	 * Returns the local package deploy descriptors.
	 *
	 * @param string $ext_dir Extension directory
	 * @return array of DeployDescriptorParser
	 */
	public static function getLocalPackages($ext_dir) {
		$packages = array();
		// add trailing slashes
		if (substr($ext_dir,-1)!='/'){
			$ext_dir .= '/';
		}

		$handle = @opendir($ext_dir);
		if (!$handle) {
			throw new IllegalArgument('Extension directory does not exist: '.$ext_dir);
		}

		while ($entry = readdir($handle) ){
			if ($entry[0] == '.'){
				continue;
			}

			if (is_dir($ext_dir.$entry)) {
				// check if there is a deploy.xml
				if (file_exists($ext_dir.$entry.'/deploy.xml')) {
					$packages[] = new DeployDescriptorParser(file_get_contents($ext_dir.$entry.'/deploy.xml'));

				}
			}

		}
		return $packages;
	}
}
?>