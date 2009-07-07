<?php
/**
 * Rollback an installation 
 *
 */
class Rollback {
	
	var $alreadyInstalledExtensions;
	var $originalLocalSettings;
	var $inst_dir;
	
	static $instance;
	
	public static function getInstance($inst_dir) {
		if (is_null(self::$instance)) {
			self::$instance = new Rollback($inst_dir);
		}
		return self::$instance;
	}
	
	private function __construct($inst_dir) {
		$this->alreadyInstalledExtensions = array();
		$this->inst_dir = $inst_dir;
	}
	
	public function addExtension($dd) {
		$localPackages = PackageRepository::getLocalPackages($this->inst_dir."/extensions");
		$this->alreadyInstalledExtensions[] = $dd;
		if (array_key_exists($dd->getID(), $localPackages)) {
			// update, so rename existing installation directory
			rename($inst_dir."/".$localPackages[$dd->getID()]->getInstallationDirectory(), $inst_dir."/".$localPackages[$dd->getID()]->getInstallationDirectory().".bak");
		}
	}
	
	public function setLocalSettings($ls) {
		$this->originalLocalSettings = $ls;
	}
	
	/**
	 * Rolls back the current installation.
	 *
	 */
	public function rollback() {
		$localPackages = PackageRepository::getLocalPackages($this->inst_dir."/extensions");
		print "\n\nRollback installation...";
		 foreach($this->alreadyInstalledExtensions as $ext) {
		 	$dp = new DeployDescriptionProcessor($this->inst_dir."/LocalSettings.php", $ext);
		 	print "\nUnapply patches of $packageID...";
            $dp->unapplyPatches();
            print "done.";
            // FIXME: cannot unapply setup operations. Would need a database backup
            
		 	print "\nRemove code of $packageID...";
            Tools::remove_dir($this->instDir."/".$ext->getInstallationDirectory());
            print "done.";
            
            // rename old
            rename($inst_dir."/".$localPackages[$ext->getID()]->getInstallationDirectory().".bak", $inst_dir."/".$localPackages[$ext->getID()]->getInstallationDirectory());
		 }
		 
		 // restore LocalSettings.php
		 $handle = fopen($this->inst_dir."/LocalSettings.php", "w");
		 fwrite($handle, $this->originalLocalSettings);
		 fclose($handle);
	}
	
	/**
	 * Cleans up after a successful installation.
	 *
	 */
	public function cleanup() {
		print "\nCleanup rollback storage...";
		$localPackages = PackageRepository::getLocalPackages($this->inst_dir."/extensions");
		 foreach($this->alreadyInstalledExtensions as $ext) {
		 	if (file_exists($inst_dir."/".$ext->getInstallationDirectory().".bak")) {
		 		Tools::remove_dir($inst_dir."/".$localPackages[$ext->getID()]->getInstallationDirectory().".bak");
		 	}
		 }
		 print "done.";
	}
}
?>