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

define('DEPLOY_FRAMEWORK_REPOSITORY_VERSION', 1);

define('DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST', 1);
define('DEPLOY_FRAMEWORK_REPO_INVALID_DESCRIPTOR', 2);
define('DEPLOY_FRAMEWORK_REPO_COULD_NOT_WRITE_EXT_APP_FILE', 3);

global $rootDir;
require_once $rootDir.'/io/DF_HttpDownload.php';
require_once $rootDir.'/tools/smwadmin/DF_Tools.php';
require_once $rootDir.'/descriptor/DF_DeployDescriptor.php';


// default repository
// this URL is supposed to be fix forever
define("SMWPLUS_REPOSITORY", "http://dailywikibuilds.ontoprise.com/repository/");
define('DF_REPOSITORY_LIST_LINK', 'http://dailywikibuilds.ontoprise.com/info/repository_list.html');


/**
 * @file
 * @ingroup DFInstaller
 *
 * Allows access package repositories.
 *
 * @author: Kai K�hn
 *
 */
class PackageRepository {

	// repository DOM
	static $repo_dom = array();
	// cache for deploy descriptors
	static $deploy_descs = array();
	// credentials for repositories
	static $repo_credentials = array();

	// cache for local packages
	static $localPackages = NULL;
	// cache for local packages
	static $localPackagesToInitialize = NULL;

	/**
	 * Returns registered list of repositories.
	 *
	 * @return array of URLs (string)
	 */
	public static function getRepositoryURLs() {
		global $smwgDFIP, $rootDir;
		if (isset($smwgDFIP)) {
			$repositoriesFile = "$smwgDFIP/config/repositories";
		} else if (isset($rootDir)) {
			$repositoriesFile = "$rootDir/config/repositories";
		} else {
			$repositoriesFile =  "repositories";
		}

		if (!file_exists($repositoriesFile)) {
			throw new Exception("No repository file found");
		}

		$content = file_get_contents($repositoriesFile);
		$rep_file_lines = array_unique(explode("\n", $content));
		$repo_urls = array();
		foreach($rep_file_lines as $u) {
			$u = trim($u);
			if (trim($u) == "" || substr(trim($u),0,1) == "#") continue;
			@list($rawurl, $user, $pass) = explode(" ", $u); // do not complain about missing credentials
			$url = (substr(trim($rawurl), -1) == "/") ? $rawurl : (trim($rawurl)."/"); //add trailing / if necessary

			$repo_urls[] = $url;
		}
		return $repo_urls;
	}
	/**
	 * Downloads all package repositories from remote.
	 *
	 * @return PackageRepository
	 */
	private static function getPackageRepository() {
		global $dfgOut;
		if (!empty(self::$repo_dom)) return self::$repo_dom;
		$rep_urls = array();
		global $smwgDFIP, $rootDir;
		if (isset($smwgDFIP)) {
			$repositoriesFile = "$smwgDFIP/config/repositories";
		} else if (isset($rootDir)) {
			$repositoriesFile = "$rootDir/config/repositories";
		} else {
			$repositoriesFile =  "repositories";
		}

		if (file_exists($repositoriesFile)) {
			$dfgOut->outputln("Connecting to repository...");
			$content = file_get_contents($repositoriesFile);
			$rep_file_lines = array_unique(explode("\n", $content));
			$repo_urls = array();
			foreach($rep_file_lines as $u) {
				$u = trim($u);
				if (trim($u) == "" || substr(trim($u),0,1) == "#") continue;
				@list($rawurl, $user, $pass) = explode(" ", $u); // do not complain about missing credentials
				$url = (substr(trim($rawurl), -1) == "/") ? $rawurl : (trim($rawurl)."/"); //add trailing / if necessary

				$repo_urls[] = $url;
				if ((is_null($user) || empty($user)) && (is_null($pass) || empty($pass))) {
					self::$repo_credentials[$url] = "";
				} else {
					self::$repo_credentials[$url] = "$user:$pass";
				}
			}
			$dfgOut->output( "done.");
		} else {
			$dfgOut->outputln("No repository file. Using default repository.", DF_PRINTSTREAM_TYPE_WARN);
			self::$repo_credentials[SMWPLUS_REPOSITORY] = "" ; // default repo
			$repo_urls[] = SMWPLUS_REPOSITORY;
		}
		$d = HttpDownload::getInstance();
		foreach($repo_urls as $url) {
			$url = trim($url);
			if (substr($url, -1) != '/') $url .= '/';
			$partsOfURL = parse_url($url. 'repository.xml');

			if (!array_key_exists('path', $partsOfURL)) {
				$dfgOut->outputln("Could not parse $url", DF_PRINTSTREAM_TYPE_WARN) ;
				continue;
			}
			$path = $partsOfURL['path'];

			if (!array_key_exists('host', $partsOfURL)) {
				$dfgOut->outputln("Could not parse $url", DF_PRINTSTREAM_TYPE_WARN) ;
				continue;
			}
			$host = $partsOfURL['host'];

			$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
			try {
				$res = $d->downloadAsString($path, $port, $host, array_key_exists($url, self::$repo_credentials) ? self::$repo_credentials[$url] : "", NULL);
				self::$repo_dom[$url] = simplexml_load_string($res);
				$repo_version = self::$repo_dom[$url]->xpath("/root[@version]");
				if (count($repo_version) > 0) {
					$repo_version_value = (string) $repo_version[0]->attributes()->version;
				} else {
					$repo_version_value = 1; // must be version 1 in this case
				}
				if ($repo_version_value != DEPLOY_FRAMEWORK_REPOSITORY_VERSION) {
					throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_INCOMPATIBLE_VERSION, "Try to access an incompatible repository at [$url]. Expected version was ".DEPLOY_FRAMEWORK_REPOSITORY_VERSION." but actually it was $repo_version_value. Please update DF manually.");
				}
			} catch(HttpError $e) {
				$dfgOut->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_ERROR);
				$dfgOut->outputln();
					
			} catch(RepositoryError $e) {
				if ($e->getErrorCode() == DEPLOY_FRAMEWORK_REPO_INCOMPATIBLE_VERSION) {
					// exit if repository is incompatible
					dffExitOnFatalError($e);
				}
			} catch(Exception $e) {
				$dfgOut->outputln($e->getMessage(), DF_PRINTSTREAM_TYPE_ERROR);
				$dfgOut->outputln();
			}

		}
		return self::$repo_dom;
	}


	/**
	 * Returns credentials for the given repository URL.
	 *
	 * @param string $repo_url
	 * @return string user:pass
	 */
	public static function getCredentials($repo_url) {
		return array_key_exists($repo_url, self::$repo_credentials) ? self::$repo_credentials[$repo_url] : "";
	}
	/*
	 * Loads package repository from string (for testing)
	 */
	public static function initializePackageRepositoryFromString($repo_xml, $url) {
		self::$repo_dom[$url] = simplexml_load_string($repo_xml);
	}
	/*
	 * Clears package repository (for testing)
	 */
	public static function clearPackageRepository() {
		self::$repo_dom = array();
	}

	/**
	 * Returns the number of the latest release the repository contains.
	 *
	 * @return int
	 */
	public static function getLatestRelease() {
		$latestreleases = array();
		foreach(self::getPackageRepository() as $url => $repo) {
			$node = $repo->xpath("/root[@latestrelease]");
			if (is_null($node) || $node == false || count($node) == 0) continue;
			$latestreleases[] = new DFVersion((string) $node[0]->attributes()->latestrelease);
		}
		DFVersion::sortVersions($latestreleases);
		return reset($latestreleases);
	}

	/**
	 * Returns deploy descriptor of package $ext_id in the latest version.
	 *
	 * @param string $ext_id
	 * @return DeployDescriptor
	 */
	public static function getLatestDeployDescriptor($ext_id) {
		if (is_null($ext_id)) throw new IllegalArgument("ext must not null");
		if (array_key_exists($ext_id, self::$deploy_descs)) return self::$deploy_descs[$ext_id];

		// get latest version in the available repositories
		$results = array();

		foreach(self::getPackageRepository() as $repo_url => $repo) {
			$versions = $repo->xpath("/root/extensions/extension[@id='$ext_id']/version");
			if (is_null($versions) || $versions == false || count($versions) == 0) continue;
			foreach($versions as $v) {
				$results[] = array(new DFVersion((string) $v->attributes()->version), (string) $v->attributes()->patchlevel, $repo_url);
			}
		}

		$max = DFVersion::getMaxVersion($results);
		if (is_null($max)) throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST, "Can not find bundle: $ext_id. Missing repository?");

		list($maxVersion, $maxPatchlevel, $url) = $max;


		// download descriptor
		$d = HttpDownload::getInstance();
		$credentials = array_key_exists($url, self::$repo_credentials) ? self::$repo_credentials[$url] : "";
		$partsOfURL = parse_url($url. "extensions/$ext_id/deploy.xml");

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$res = $d->downloadAsString($path, $port, $host, $credentials, NULL);

		$dd =  new DeployDescriptor($res);
		self::$deploy_descs[] = $dd;
		return $dd;
	}

	/**
	 * Gets a deploy descriptor in the given range.
	 *
	 * @param string $ext_id
	 * @param DFVersion $minversion
	 * @param DFVersion $maxversion
	 * @throws RepositoryError
	 * @return DeployDescriptor
	 */
	public static function getDeployDescriptorFromRange($ext_id, $minversion, $maxversion) {
		if ($minversion->isHigher($maxversion))  throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_INVALID_DESCRIPTOR, "Invalid range of versions: $minversion-$maxversion");
		$versions = self::getAllVersions($ext_id);
		for($o = reset($versions); $o !== false; $o = next($versions)) {
			list($i, $pl) = $o;
			if ($minversion->isLowerOrEqual($i) && $i->isLowerOrEqual($maxversion)) {
				return self::getDeployDescriptor($ext_id, $i);
			}
		}
		throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST, "Can not find bundle: $ext_id in version range ".$minversion->toVersionString()."-".$maxversion->toVersionString());
	}

	/**
	 * Returns deploy descriptor of package $ext_id in version $version
	 *
	 * @param string $ext_id
	 * @param DFVersion $version
	 * @return DeployDescriptor
	 */
	public static function getDeployDescriptor($ext_id, $version) {

		if (is_null($ext_id) || is_null($version)) throw new IllegalArgument("version or ext must not null");
		$version = $version->toVersionString();
		if (array_key_exists($ext_id.$version, self::$deploy_descs)) return self::$deploy_descs[$ext_id.$version];

		// get latest version in the available repositories
		$results = array();
		foreach(self::getPackageRepository() as $url => $repo) {
			$versions = $repo->xpath("/root/extensions/extension[@id='$ext_id']/version[@version='$version']");
			if (is_null($versions) || $versions == false || count($versions) == 0) continue;
			$v = reset($versions);
			$repourl = $url;
			break;
		}
		if (!isset($repourl)) throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST, "Can not find bundle: $ext_id-$version");

		// download descriptor
		$d = HttpDownload::getInstance();
		$credentials = array_key_exists($repourl, self::$repo_credentials) ? self::$repo_credentials[$repourl] : '';
		$partsOfURL = parse_url($url. "extensions/$ext_id/deploy-$version.xml");

		$path = $partsOfURL['path'];
		$host = $partsOfURL['host'];
		$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
		$res = $d->downloadAsString($path, $port, $host, $credentials, NULL);

		$dd =  new DeployDescriptor($res);

		self::$deploy_descs[] = $dd;
		return $dd;
	}

	/**
	 * Returns all available versions in descendant order.
	 *
	 * @param string $packageID
	 * @return array of versions (descendant)
	 */
	public static function getAllVersions($packageID) {

		$results = array();

		foreach(self::getPackageRepository() as $repo) {
			if ($repo === false) continue;
			$versions = $repo->xpath("/root/extensions/extension[@id='$packageID']/version");

			if ($versions !== false) {
				foreach($versions as $v) {
					$patchlevel = (string) $v->attributes()->patchlevel;
					$results[] = array(new DFVersion((string) $v->attributes()->version), $patchlevel == '' ? 0 : $patchlevel);
				}
			}
		}

		DFVersion::sortVersions($results);

		return $results;
	}

	/**
	 * Returns all available packages and their versions
	 *
	 * @return array of (package ids => array of (version, patchlevel, repo URL) sorted in descending order
	 */
	public static function getAllPackages() {
		$results = array();
		foreach(self::getPackageRepository() as $repo_url => $repo) {
			if ($repo === false) continue;
			$packages = $repo->xpath("/root/extensions/extension");
			foreach($packages as $p) {
				$id = (string) $p->attributes()->id;
				$title = (string) $p->attributes()->title;
				if (!array_key_exists($id, $results)) $results[$id] = array();
				$versions = $p->xpath("version");
				foreach($versions as $v) {
					$version = new DFVersion((string) $v->attributes()->version);
					$patchlevel = (string) $v->attributes()->patchlevel;
					$patchlevel = empty($patchlevel) ? 0 : $patchlevel;
					$description = (string) $v->attributes()->description;
					$results[$id][] = array($version, $patchlevel, $repo_url, $title, $description);
				}

			}
		}
		$sortedResults = array();
		foreach($results as $id => $versions) {
			DFVersion::sortVersions($versions);
			$sortedResults[$id] = $versions;
		}

		return $sortedResults;
	}

	/**
	 * Search for packages in all repositories containing the
	 * $searchvalue in its ID or description. $searchvalue can contain
	 * several words (separated by withspace). All must be contained.
	 *
	 * @param string $searchValue
	 * @return array (id => array($ver => $description))
	 */
	public static function searchAllPackages($searchValue) {
		$results = array();
		$searchValues = explode(" ", trim($searchValue));
		foreach(self::getPackageRepository() as $repo_url => $repo) {
			$packages = $repo->xpath("/root/extensions/extension");
			foreach($packages as $p) {
				$id = (string) $p->attributes()->id;
				$title = (string) $p->attributes()->title;
				$versions = $p->xpath("version");
				foreach($versions as $v) {
					$description = (string) $v->attributes()->description;
					$found = true;
					foreach($searchValues as $sv) {
						if (stripos($id, $sv) === false && stripos($description, $sv) === false ) {
							$found = false;
							break;
						}
					}
					if ($found || empty($searchValue)) {
						if (!array_key_exists($id, $results)) {
							$results[$id] = array();
						}
						foreach($versions as $v) {
							$ver = (string) $v->attributes()->version;
							$patchlevel = (string) $v->attributes()->patchlevel;
							if (empty($patchlevel)) $patchlevel = 0;
							$description = (string) $v->attributes()->description;
							$results[$id][$ver."_".$patchlevel] = array($title, $description);
						}
					}
				}

			}
		}
		ksort($results);
		return $results;
	}


	/**
	 * Returns the URL of the requested version of the package if available or NULL if not.
	 *
	 * @param string $packageID
	 * @param DFVersion $version
	 * @return array (url, repo_url)
	 */
	public static function getDownloadURL($packageID, $version) {
		$results = array();
		foreach(self::getPackageRepository() as $url => $repo) {
			$versionStr = $version->toVersionString();
			$package = $repo->xpath("/root/extensions/extension[@id='$packageID']/version[@version='$versionStr']");

			if (is_null($package) || $package === false || count($package) == 0) continue;
			$repo_url = $url;
			$download_url = trim((string) $package[0]->attributes()->url);
			break;
		}
		if (!isset($download_url)) throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST, "Can not find bundle: $packageID-".$version->toVersionString().". Missing repository?");

		return array($download_url, $repo_url);
	}

	/**
	 * Checks if the package with the given version exists or not.
	 *
	 * @param string $packageID
	 * @param DFVersion $version Optional
	 * @return boolean
	 */
	public static function existsPackage($packageID, $version = NULL) {
		$results = array();
		foreach(self::getPackageRepository() as $repo) {
			if (!is_null($version)) {
				$versionStr = $version->toVersionString();
				$package = $repo->xpath("/root/extensions/extension[@id='$packageID']/version[@version='$versionStr']");
			} else {
				$package = $repo->xpath("/root/extensions/extension[@id='$packageID']");
			}

			if (count($package) > 0) return true;
		}
		return false;
	}

	/**
	 * Returns the local package deploy descriptors.
	 *
	 * @param string $ext_dir Extension directory
	 * @return array of (id=>DeployDescriptor)
	 */
	public static function getLocalPackages($ext_dir, $forceReload = false) {
		if (!is_null(self::$localPackages) && !$forceReload) return self::$localPackages;
		self::$localPackages = array();
		// add trailing slashes
		if (substr($ext_dir,-1)!='/'){
			$ext_dir .= '/';
		}

		// read root dir and extensions dir for deploy descriptors
		self::readDirectoryForDD($ext_dir);
		self::readDirectoryForDD($ext_dir."extensions");
		//self::readDirectoryForDD(Tools::getProgramDir()."/Ontoprise");

		// add non public apps
		$nonPublicAppPaths = Tools::getNonPublicAppPath($ext_dir);
		foreach($nonPublicAppPaths as $id => $path) {
			if ($path == '') continue;
			$path = Tools::unquotePath($path);
			if (file_exists($path.'/deploy.xml')) {
				$dd = new DeployDescriptor(file_get_contents($path.'/deploy.xml'));
				if (!array_key_exists($dd->getID(), self::$localPackages)) {
					self::$localPackages[$dd->getID()] = $dd;
				}
			}
		}

		// create special deploy descriptor for Mediawiki itself
		self::$localPackages['mw'] = self::createMWDeployDescriptor(realpath($ext_dir));

		// FIX: smwplus has been renamed to Smwplus
		// so that the bundle ID matches the bundle page's name.
		// All content bundles now start with an uppercase letter
		if (array_key_exists('smwplus', self::$localPackages)) {
			$temp = self::$localPackages['smwplus'];
			self::$localPackages['Smwplus'] = $temp;
			unset(self::$localPackages['smwplus']);
		}

		if (array_key_exists('smwplussandbox', self::$localPackages)) {
			$temp = self::$localPackages['smwplussandbox'];
			self::$localPackages['Smwplussandbox'] = $temp;
			unset(self::$localPackages['smwplussandbox']);
		}

		return self::$localPackages;
	}

	private static function readDirectoryForDD($ext_dir) {
		if (substr($ext_dir,-1)!='/'){
			$ext_dir .= '/';
		}
		$handle = @opendir($ext_dir);
		if (!$handle) {

			return;
		}

		while ($entry = readdir($handle) ){
			if ($entry[0] == '.'){
				continue;
			}

			if (is_dir($ext_dir.$entry)) {
				// check if there is a deploy.xml
				if (file_exists($ext_dir.$entry.'/deploy.xml')) {
					$dd = new DeployDescriptor(file_get_contents($ext_dir.$entry.'/deploy.xml'));
					self::$localPackages[$dd->getID()] = $dd;

				}
			}

		}
		@closedir($handle);
	}

	/**
	 * Returns the deploy descriptors of packages which have not been initialized.
	 *
	 * @param string $ext_dir Extension directory
	 * @return array of (id=>DeployDescriptor)
	 */
	public static function getLocalPackagesToInitialize($ext_dir, $forceReload = false) {
		if (!is_null(self::$localPackagesToInitialize) && !$forceReload) return self::$localPackagesToInitialize;
		self::$localPackagesToInitialize = array();
		// add trailing slashes
		if (substr($ext_dir,-1)!='/'){
			$ext_dir .= '/';
		}

		self::readDirectoryForDDToInitialize($ext_dir);
		self::readDirectoryForDDToInitialize($ext_dir."extensions");
		//self::readDirectoryForDDToInitialize(Tools::getProgramDir()."/Ontoprise");

		// collect external apps
		self::readExternalAppsForDDToInitialize($ext_dir);

		// special handling for MW itself
		if (file_exists($ext_dir.'/init$.ext')) {
			$init_ext_file = trim(file_get_contents($ext_dir.'/init$.ext'));
			list($id, $fromVersion) = explode(",", $init_ext_file);
			$dd = self::createMWDeployDescriptor(realpath($ext_dir), new DFVersion($fromVersion));
			self::$localPackagesToInitialize[$id] = array($dd, $fromVersion);
		}

		return self::$localPackagesToInitialize;
	}

	private static function readDirectoryForDDToInitialize($ext_dir) {
		if (substr($ext_dir,-1)!='/'){
			$ext_dir .= '/';
		}
		$handle = @opendir($ext_dir);
		if (!$handle) {

			return;
		}

		while ($entry = readdir($handle) ){
			if ($entry[0] == '.'){
				continue;
			}

			if (is_dir($ext_dir.$entry)) {
				// check if there is a init$.ext
				if (file_exists($ext_dir.$entry.'/init$.ext')) {
					$init_ext_file = trim(file_get_contents($ext_dir.$entry.'/init$.ext'));
					list($id, $fromVersion) = explode(",", $init_ext_file);
					if (!file_exists($ext_dir.$entry.'/deploy.xml')) {
						// this should not happen but you never know.
						// anyway do nothing in this case.
						continue;
					}
					$dd = new DeployDescriptor(file_get_contents($ext_dir.$entry.'/deploy.xml'));
					self::$localPackagesToInitialize[$id] = array($dd, $fromVersion);

				}
			}

		}
		@closedir($handle);
	}

	/**
	 * Collects all external apps which must be initialized.
	 *
	 * @param $ext_dir
	 */
	private static function readExternalAppsForDDToInitialize($ext_dir) {
		$nonPublicAppPaths = Tools::getNonPublicAppPath($ext_dir);
		foreach($nonPublicAppPaths as $id => $path) {
			if ($path == '') continue;
			$path = Tools::unquotePath($path);
			if (file_exists($path.'/init$.ext')) {
				$init_ext_file = trim(file_get_contents($path.'/init$.ext'));
				list($id, $fromVersion) = explode(",", $init_ext_file);
				if (!file_exists($path.'/deploy.xml')) {
					// this should not happen but you never know.
					// anyway do nothing in this case.
					continue;
				}
				$dd = new DeployDescriptor(file_get_contents($path.'/deploy.xml'));
				self::$localPackagesToInitialize[$id] = array($dd, $fromVersion);
			}
		}

	}

	private static function createMWDeployDescriptor($rootDir, $fromVersion = NULL) {
		$xml = Tools::createMWDeployDescriptor($rootDir);
		return new DeployDescriptor($xml, $fromVersion);
	}



	/**
	 * Takes extension IDs and returns the order in which they have to be deleted. It considers
	 * all extensions which must be deleted additionally if necessary.
	 *
	 * @param array $extIDs
	 * @param string $rootDir
	 */
	public static function getDeletionOrder($extIDs, $rootDir = NULL) {
		$localpackages = PackageRepository::getLocalPackages($rootDir, false);
		$superExtensions = array();

		foreach($extIDs as $id) {
			// add the extension itself
			$superExtensions[$id] = $localpackages[$id];
			// and its super extensions
			self::getLocalSuperExtensions($id, $superExtensions, $rootDir);
		}

		// topological sorting according to the dependencies
		$resultExtensionIDs = self::sortForDependencies($superExtensions);

		// reverse so that top most come first
		$resultExtensionIDs = array_reverse($resultExtensionIDs);

		return $resultExtensionIDs;
	}



	/**
	 * Returns the local superextensions of an extension.
	 *
	 * @param string $extID
	 * @param (out) array $superExtensions
	 * @param string $rootDir
	 */
	private static function getLocalSuperExtensions($extID, & $superExtensions, $rootDir) {
		$localPackages = PackageRepository::getLocalPackages($rootDir, false);
		foreach($localPackages as $p) {

			// check if a local extension has $dd as a dependency
			$deps = $p->getDependencies();
			foreach($deps as $dep) {
				if (is_null($dep)) continue;

				if ($dep->isOptional()) continue;

				if ($dep->matchBundle($extID)) {
					// $p is a super package
					$superExtensions[$p->getID()] = $p;
					self::getLocalSuperExtensions($p->getID(), $superExtensions, $rootDir);
				}
			}
		}
	}



	/**
	 * Provides a topologic sorting based on the dependency graph.
	 *
	 * @param array(ID=>array($dd)) $extensions_to_update
	 * @return array(ID)
	 */
	public static function sortForDependencies(& $extensions_to_update) {
		$sortedPackages = array();
		$vertexes = array_keys($extensions_to_update);
		$descriptors = array_values($extensions_to_update);
		while (!empty($vertexes)) {
			$cycle = true;
			foreach($vertexes as $v) {
				$hasPreceding = false;
				foreach($descriptors as $e) {
					$dd = is_array($e) ? reset($e) : $e;
					if (in_array($dd->getID(), $vertexes) && !$dd->isOptionalDependency($v)) {
						if ($dd->hasDependency($v)) {
							$hasPreceding = true;
							break;
						}
					}
				}
				if (!$hasPreceding) {
					$cycle = false;
					break;
				}
			}
			if (!$hasPreceding) {
				// remove $v from $vertexes
				$vertexes = array_diff($vertexes, array($v));

			}
			$sortedPackages[] = $v;

			if ($cycle) throw new InstallationError(DEPLOY_FRAMEWORK_PRECEDING_CYCLE, "Cycle in the dependency graph.");
		}
		return array_reverse($sortedPackages);
	}

}

class RepositoryError extends Exception {

	var $msg;
	var $arg1;
	var $arg2;

	public function __construct($errCode, $msg = '', $arg1 = NULL, $arg2 = NULL) {
		$this->errCode = $errCode;
		$this->msg = $msg;
		$this->arg1 = $arg1;
		$this->arg2 = $arg2;
	}

	public function getMsg() {
		return $this->msg;
	}

	public function getErrorCode() {
		return $this->errCode;
	}

	public function getArg1() {
		return $this->arg1;
	}

	public function getArg2() {
		return $this->arg2;
	}
}
