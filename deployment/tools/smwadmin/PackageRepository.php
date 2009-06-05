<?php

require_once '../io/HttpDownload.php';

// this URL is supposed to be fix forever
define("SMWPLUS_REPOSITORY", "http://localhost/mediawiki/repository.xml");

/**
 * Allows access on the global HALO package repository.
 * 
 * @author: Kai Kühn
 *
 */
class PackageRepository {

	// repository DOM
	static $repo_dom = NULL;

	public static function getPackageRepository() {
		if (!is_null(self::$repo_dom)) return self::$repo_dom;

		$d = new HttpDownload();
		$partsOfURL = parse_url(SMWPLUS_REPOSITORY);

		$path = $partsOfURL['path'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$res = $d->downloadAsString($path, $port, $host, NULL);

		self::$repo_dom = simplexml_load_string($res);

		return self::$repo_dom;
	}
	
	public static function getPackageRepositoryFromString($repo_xml) {
		self::$repo_dom = simplexml_load_string($repo_xml);

        return self::$repo_dom;
	}

	public static function getLatestVersion($packageName) {
		$package = self::$repo_dom->xpath("/root/extensions/extension[@id='$packageName']/version[position()=last()]");
		if (count($package) == 0) return NULL;
		$download_url = (string) $package[0]->attributes()->url;
		return $download_url;
	}

	public static function getVersion($packageName, $version) {
		$package = self::$repo_dom->xpath("/root/extensions/extension[@id='$packageName']/version[@ver='$version']");
		if (count($package) == 0) return NULL;
		$download_url = (string) $package[0]->attributes()->url;
		return $download_url;
	}

	public static function existsPackage($packageName, $version = 0) {
		if ($version > 0) {
			$package = self::$repo_dom->xpath("/root/extensions/extension[@id='$packageName']/version[@ver='$version']");
		} else {
			$package = self::$repo_dom->xpath("/root/extensions/extension[@id='$packageName']");
		}
		return count($package) > 0;
	}
}
?>