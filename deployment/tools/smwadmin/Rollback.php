<?php
/**
 * Rollback an installation.
 * 
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
class Rollback {

    // installation directory of Mediawiki
	var $inst_dir;
	
	// temporary directory where rollback data is stored.
	var $tmpDir;
	
	// array of package IDs of extensions to restore
	var $extToRestore;



	static $instance;

	public static function getInstance($inst_dir) {
		if (is_null(self::$instance)) {
			self::$instance = new Rollback($inst_dir);
		}
		return self::$instance;
	}

	private function __construct($inst_dir) {

		$this->inst_dir = $inst_dir;
		$this->extToRestore = array();

		$this->tmpDir = Tools::isWindows() ? 'c:/temp/rollback_smwadmin' : '/tmp/rollback_smwadmin';

	}

	private function acquireNewRollback() {
		static $newRollback = true;
		if ($newRollback) { // initialize new rollback
			$newRollback = false;
			if (file_exists($this->tmpDir)) {
				print "\nRemove old rollback data (y/n) ?";
				$line = trim(fgets(STDIN));
				if (strtolower($line) == 'n') {
					print "\n\nAbort installation.";
					die();
				}
				Tools::remove_dir($this->tmpDir);
			}
			Tools::mkpath($this->tmpDir);
		}
	}

	/**
	 * Saves resources of an extension
	 *
	 * @param DeployDescriptorParser $dd
	 */
	private function saveResources($dd) {
		$this->acquireNewRollback();
		foreach($dd->getResources() as $file) {
			$im_file = wfLocalFile(Title::newFromText(basename($this->rootDir."/".$file), NS_IMAGE));
			Tools::mkpath($this->tmpDir."/resources/".$im_file->getHashPath());
			copy($this->inst_dir."/images/".$im_file->getHashPath().$im_file->getName(), $this->tmpDir."/resources/".$im_file->getHashPath().$im_file->getName());
		}

	}

	public function saveExtension($dd) {
		$this->acquireNewRollback();
		$localPackages = PackageRepository::getLocalPackages($this->inst_dir."/extensions");
		if (array_key_exists($dd->getID(), $localPackages)) {
			$localExt = $localPackages[$dd->getID()];
			print "\nSaving extension... ".$localExt->getID();
			Tools::mkpath($this->tmpDir."/stored/".$localExt->getInstallationDirectory());
			Tools::copy_dir($this->inst_dir."/".$localExt->getInstallationDirectory(), $this->tmpDir."/stored/".$localExt->getInstallationDirectory());
			$this->saveResources($localExt);
			print "done.";
		}
		$this->extToRestore[] = $dd->getID();
	}

	public function saveDatabase() {
		$this->acquireNewRollback();
		// make sure to save only once
		static $savedDataBase = false;
		if ($savedDataBase) return;
		global $wgDBadminuser, $wgDBadminpassword, $wgDBname;
		$savedDataBase = true;
		print "\nSaving database...";
		//print "\nmysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/dump.sql";
		exec("mysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not save database for rollback"; else print "done.";
	}

	public function saveLocalSettings() {
		$this->acquireNewRollback();
		static $saveLocalSettings = false;
		if ($saveLocalSettings) return;
		$saveLocalSettings = true;
		print "Saving LocalSettings.php";
		copy($this->inst_dir."/LocalSettings.php", $this->tmpDir."/LocalSettings.php");

	}

	private function restoreDatabase() {

		global $wgDBadminuser, $wgDBadminpassword, $wgDBname;
		if (!file_exists($this->tmpDir."/dump.sql")) return false; // nothing to restore
		print "\nRestore database...";
		//print "\nmysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/dump.sql";
		exec("mysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not restore database."; else print "done.";
		return ($ret == 0);
	}

	private function restoreResources() {
		if (!file_exists($this->tmpDir."/resources")) return false; // nothing to restore
		print "\nRestore resources...";
		Tools::copy_dir($this->tmpDir."/resources", $this->inst_dir."/images");
		print "done.";
	}


	/**
	 * Rolls back the current installation.
	 *
	 */
	public function rollback() {
		$res_installer = ResourceInstaller::getInstance($this->inst_dir);

		//remove patches
		if (!file_exists($this->tmpDir)) {
			print "\nNothing to restore.";
			return;
		}
		$this->extToRestore = explode(",", file_get_contents($this->tmpDir."/extToRestore"));

		$localPackages = PackageRepository::getLocalPackages($this->inst_dir.'/extensions');

		print "\nRemoving patches...";
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRestore)) {

				$dp = new DeployDescriptionProcessor($this->inst_dir.'/LocalSettings.php', $dd);

				$dp->unapplyPatches();

			}
		}
		print "done.";

		print "\nDeleting resources...";
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRestore)) {

				$res_installer->deleteResources($dd);

			}
		}
		print "done.";

		$databaseRestored = $this->restoreDatabase();
		if (!$databaseRestored) {
			foreach($localPackages as $dd) {
				if (in_array($dd->getID(), $this->extToRestore)) {

					$res_installer->deinstallWikidump($dd);

				}
			}
			
		}

		// remove installed or updated extensions
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRestore)) {
				Tools::remove_dir($this->inst_dir."/".$dd->getInstallationDirectory());
			}
		}
		// copy old (updated) extensions
		Tools::copy_dir($this->tmpDir."/stored", $this->inst_dir);

		// reload local packages, because they have changed
		$restoredLocalPackages = PackageRepository::getLocalPackages($this->inst_dir.'/extensions', true);

		// restore wiki pages
		if (!$databaseRestored) {
			foreach($localPackages as $dd) {
				if (in_array($dd->getID(), $this->extToRestore)) {
					if (array_key_exists($dd->getID(), $restoredLocalPackages)) {
						$res_installer->installOrUpdateWikidumps($restoredLocalPackages[$dd->getID()], $dd->getVersion(), DEPLOYWIKIREVISION_FORCE);
					}
				}
			}
		}

		// restore resources
		$this->restoreResources();
			
		// restore LocalSettings.php
		if (file_exists($this->tmpDir."/LocalSettings.php")) {
			copy($this->tmpDir."/LocalSettings.php", $this->inst_dir."/LocalSettings.php");
		}

		// clear rollback data
		if (file_exists($this->tmpDir)) Tools::remove_dir($this->tmpDir);

	}

	/**
	 * Cleans up after a successful installation.
	 *
	 */
	public function saveRollbackLog() {
		$this->acquireNewRollback();
		print "\nSave rollback storage...";
		$handle = fopen($this->tmpDir."/extToRestore", "w");
		fwrite($handle, implode(",", $this->extToRestore));;
		fclose($handle);
	}
}
?>