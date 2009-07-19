<?php

define('DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST', 1);

if (defined('DEBUG_MODE') && DEBUG_MODE == true) {
	require_once 'deployment/io/DF_HttpDownload.php';
	require_once 'deployment/tools/smwadmin/DF_Tools.php';
	require_once 'deployment/descriptor/DF_DeployDescriptor.php';
} else {
	require_once '../io/DF_HttpDownload.php';
	require_once '../tools/smwadmin/DF_Tools.php';
	require_once '../descriptor/DF_DeployDescriptor.php';
}

// default repository
// this URL is supposed to be fix forever
define("SMWPLUS_REPOSITORY", "http://dailywikibuilds.ontoprise.com/repository/");


/**
 * Allows access package repositories.
 *
 * @author: Kai Kühn / ontoprise / 2009
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

	/**
	 * Downloads all package repositories from remote.
	 *
	 * @return PackageRepository
	 */
	private static function getPackageRepository() {
		if (!empty(self::$repo_dom)) return self::$repo_dom;
		$rep_urls = array();
		if (file_exists("repositories")) {
			print "\nReadling from repository file...";
			$content = file_get_contents("repositories");
			$rep_file_lines = array_unique(explode("\n", $content));
			$repo_urls = array();
			foreach($rep_file_lines as $u) {
				if (trim($u) == "" || substr(trim($u),0,1) == "#") continue;
				list($rawurl, $user, $pass) = explode(" ", $u);
				$url = (substr(trim($rawurl), -1) == "/" ? $rawurl : $rawurl."/"); //add trailing / if necessary
				$repo_urls[] = $url;
				if ((is_null($user) || empty($user)) && (is_null($pass) || empty($pass))) {
					self::$repo_credentials[$url] = "";
				} else {
					self::$repo_credentials[$url] = "$user:$pass";
				}
			}
			print "done.";
		} else {
			print "\nNo repository file. Using default repository.";
			self::$repo_credentials[SMWPLUS_REPOSITORY] = "" ; // default repo
			$repo_urls[] = SMWPLUS_REPOSITORY;
		}
		$d = new HttpDownload();
		foreach($repo_urls as $url) {
			$url = trim($url);
			if (substr($url, -1) != '/') $url .= '/';
			$partsOfURL = parse_url($url. 'repository.xml');

			$path = $partsOfURL['path'];
			$host = $partsOfURL['host'];
			$port = array_key_exists("port", $partsOfURL) ? $partsOfURL['port'] : 80;
			try {
				$res = $d->downloadAsString($path, $port, $host, self::$repo_credentials[$url], NULL);
				self::$repo_dom[$url] = simplexml_load_string($res);
			} catch(HttpError $e) {
				print "\n".$e->getMsg();
				print "\n";
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
    	return self::$repo_credentials[$repo_url];
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

		foreach(self::getPackageRepository() as $url => $repo) {
			$versions = $repo->xpath("/root/extensions/extension[@id='$ext_id']/version");
			if (is_null($versions) || $versions == false || count($versions) == 0) continue;
			foreach($versions as $v) {
				$results[$url] = (string) $v->attributes()->ver;
			}
		}
		asort($results, SORT_NUMERIC);
		$results = array_reverse($results, true);
		$url = reset(array_keys($results));
        
		
		if ($url === false) throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST, "Can not find package: $ext_id. Missing repository?");

		// download descriptor
		$d = new HttpDownload();
		$credentials = self::$repo_credentials[$url];
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
	 * Returns deploy descriptor of package $ext_id in version $version
	 *
	 * @param string $ext_id
	 * @param int $version
	 * @return DeployDescriptor
	 */
	public static function getDeployDescriptor($ext_id, $version) {
		if (is_null($ext_id) || is_null($version)) throw new IllegalArgument("version or ext must not null");
		if (array_key_exists($ext_id.$version, self::$deploy_descs)) return self::$deploy_descs[$ext_id.$version];

		// get latest version in the available repositories
		$results = array();
		foreach(self::getPackageRepository() as $url => $repo) {
			$versions = $repo->xpath("/root/extensions/extension[@id='$ext_id']/version[@ver='$version']");
			if (is_null($versions) || $versions == false || count($versions) == 0) continue;
			$v = reset($versions);
			$repourl = $url;
			break;
		}
		if (!isset($repourl)) throw new RepositoryError(DEPLOY_FRAMEWORK_REPO_PACKAGE_DOES_NOT_EXIST, "Can not find package: $ext_id-$version");
    
		// download descriptor
		$d = new HttpDownload();
		$credentials = self::$repo_credentials[$repourl];
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
			$versions = $repo->xpath("/root/extensions/extension[@id='$packageID']/version");

			foreach($versions as $v) {
				$results[] = (string) $v->attributes()->ver;
			}
		}

		sort($results, SORT_NUMERIC);

		return array_reverse(array_unique($results));
	}

	/**
	 * Returns all available packages and their versions
	 *
	 * @return array of (package ids => array of ascending versions)
	 */
	public static function getAllPackages() {
		$results = array();
		foreach(self::getPackageRepository() as $repo) {
			$packages = $repo->xpath("/root/extensions/extension");
			foreach($packages as $p) {
				$id = (string) $p->attributes()->id;
				if (!array_key_exists($id, $results)) $results[$id] = array();
				$versions = $p->xpath("version");
				foreach($versions as $v) {
					$results[$id][] = (string) $v->attributes()->ver;
				}

			}
		}
		$sortedResults = array();
		foreach($results as $id => $versions) {
			sort($versions, SORT_NUMERIC);
			$sortedResults[$id] = array_unique($versions);
		}

		return $sortedResults;
	}
	/**
	 * Returns latest available version of a package
	 *
	 * @param string $packageID The package ID
	 * @return array (URL (as string), version, repo_url)
	 */
	public static function getLatestVersion($packageID) {
		$results = array();
		foreach(self::getPackageRepository() as $url => $repo) {
			
			$package = $repo->xpath("/root/extensions/extension[@id='$packageID']/version[position()=last()]");
			if (count($package) == 0) continue;
			$download_url = (string) $package[0]->attributes()->url;
			$version = (string) $package[0]->attributes()->ver;
			$results[$version] = array($download_url, $url);

		}
		if (count($results) == 0) return NULL;
		ksort($results, SORT_NUMERIC); // sort for versions
		$results = array_reverse($results, true); // highest version on top
		$version = reset(array_keys($results)); // get highest version
		list($download_url, $repo_url) = reset(array_values($results)); // get its download url and repo
		return array($download_url, $version, $repo_url);
	}

	/**
	 * Returns the URL of the requested version of the package if available or NULL if not.
	 *
	 * @param string $packageID
	 * @param number $version
	 * @return array (url, repo_url)
	 */
	public static function getVersion($packageID, $version) {
		$results = array();
		foreach(self::getPackageRepository() as $url => $repo) {
			$package = $repo->xpath("/root/extensions/extension[@id='$packageID']/version[@ver='$version']");

			if (is_null($package) || $package === false || count($package) == 0) continue;
			$repo_url = $url;
			$download_url = (string) $package[0]->attributes()->url;
			break;
		}
		if (!isset($download_url)) return NULL;

		return array($download_url, $repo_url);
	}

	/**
	 * Checks if the package with the given version exists or not.
	 *
	 * @param string $packageID
	 * @param number $version Optional
	 * @return boolean
	 */
	public static function existsPackage($packageID, $version = 0) {
		$results = array();
		foreach(self::getPackageRepository() as $repo) {
			if ($version > 0) {
				$package = $repo->xpath("/root/extensions/extension[@id='$packageID']/version[@ver='$version']");
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
					$dd = new DeployDescriptor(file_get_contents($ext_dir.$entry.'/deploy.xml'));
					self::$localPackages[$dd->getID()] = $dd;

				}
			}

		}
		// create special deploy descriptor for Mediawiki itself
		self::$localPackages['MW'] = self::createMWDeployDescriptor(realpath($ext_dir."/.."));
		return self::$localPackages;
	}

	private static function createMWDeployDescriptor($rootDir) {
		$version = Tools::getMediawikiVersion($rootDir);
		$version = intval(str_replace(".","", $version));
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
				<deploydescriptor>
				    <global>
				        <version>'.$version.'</version>
				        <id>MW</id>
				        <vendor>Ontoprise GmbH</vendor>
				        <instdir/>
				        <description>Mediawiki software</description>
				       
    			    </global>
				    <codefiles/>
				    <wikidumps/>
				    <resources/>
				    <configs/>
				    </deploydescriptor>';

		return new DeployDescriptor($xml);
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
?>