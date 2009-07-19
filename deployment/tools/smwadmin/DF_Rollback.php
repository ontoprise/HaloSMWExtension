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

	/**
	 * Acquires a new rollback operation. The user has to confirm to
	 * overwrite exisiting rollback data.
	 *
	 */
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
	 * @param string $id of extension
	 */
	private function saveResources($id) {
		$this->acquireNewRollback();
		$localPackages = PackageRepository::getLocalPackages($this->inst_dir."/extensions");
		if (array_key_exists($id, $localPackages)) {
			$localExt = $localPackages[$id];
			foreach($localExt->getResources() as $file) {
				// save all resources of $dd
				$im_file = wfLocalFile(Title::newFromText(basename($this->rootDir."/".$file), NS_IMAGE));
				Tools::mkpath($this->tmpDir."/resources/".$im_file->getHashPath());
				copy($this->inst_dir."/images/".$im_file->getHashPath().$im_file->getName(), $this->tmpDir."/resources/".$im_file->getHashPath().$im_file->getName());
			}
		}
	}

	/**
	 * Save the extension to the rollback directory
	 *
	 * @param string $id of extension
	 */
	public function saveExtension($id) {
		$this->acquireNewRollback();
		// get installed extension
		$localPackages = PackageRepository::getLocalPackages($this->inst_dir."/extensions");
		if (array_key_exists($id, $localPackages)) {
			$localExt = $localPackages[$id];
			print "\nSaving extension... ".$localExt->getID();
			Tools::mkpath($this->tmpDir."/stored/".$localExt->getInstallationDirectory());
			Tools::copy_dir($this->inst_dir."/".$localExt->getInstallationDirectory(), $this->tmpDir."/stored/".$localExt->getInstallationDirectory());
			$this->saveResources($localExt->getID());
			print "done.";
		}
		$this->extToRestore[] = $dd->getID();
	}

	/**
	 * Save the database to the rollback directory
	 *
	 * @return unknown
	 */
	public function saveDatabase() {
		$this->acquireNewRollback();
		// make sure to save only once
		static $savedDataBase = false;
		if ($savedDataBase) return true;
		global $wgDBadminuser, $wgDBadminpassword, $wgDBname;
		$savedDataBase = true;
		print "\nSaving database...";
		//print "\nmysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/dump.sql";
		exec("mysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not save database for rollback"; else print "done.";
		return $ret == 0;
	}

	/**
	 * Save the local settings file to the rollback directory.
	 *
	 */
	public function saveLocalSettings() {
		$this->acquireNewRollback();
		static $saveLocalSettings = false;
		if ($saveLocalSettings) return;
		$saveLocalSettings = true;
		print "Saving LocalSettings.php";
		copy($this->inst_dir."/LocalSettings.php", $this->tmpDir."/LocalSettings.php");

	}

	/**
	 * Restore the database dump from the rollback directory.
	 *
	 * @return boolean
	 */
	private function restoreDatabase() {

		global $wgDBadminuser, $wgDBadminpassword, $wgDBname;
		if (!file_exists($this->tmpDir."/dump.sql")) return false; // nothing to restore
		print "\nRestore database...";
		//print "\nmysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/dump.sql";
		exec("mysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not restore database."; else print "done.";
		return ($ret == 0);
	}

	/**
	 * Restore resource files from the rollback dir
	 *
	 * @return unknown
	 */
	private function restoreResourceFiles() {
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

		// 1. remove patches of the extension to restore
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

		// 2. deleting the resources of the extension to restore
		print "\nDeleting resources...";
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRestore)) {

				$res_installer->deleteResources($dd);

			}
		}
		print "done.";

		// 3. Restore the database (if available)
		$databaseRestored = $this->restoreDatabase();

		// if it was not restored, delete the wiki dumps of all extensions to restore
		if (!$databaseRestored) {
			foreach($localPackages as $dd) {
				if (in_array($dd->getID(), $this->extToRestore)) {

					$res_installer->deinstallWikidump($dd);

				}
			}

		}

		// 4. remove installed or updated extensions
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRestore)) {
				Tools::remove_dir($this->inst_dir."/".$dd->getInstallationDirectory());
			}
		}
		// 5. copy old (updated) extensions
		Tools::copy_dir($this->tmpDir."/stored", $this->inst_dir);

		// 6. reload local packages, because they have changed
		$restoredLocalPackages = PackageRepository::getLocalPackages($this->inst_dir.'/extensions', true);

		if (!$databaseRestored) {
			// 7. restore wiki pages if the database was not restored
			foreach($localPackages as $dd) {
				if (in_array($dd->getID(), $this->extToRestore)) {
					if (array_key_exists($dd->getID(), $restoredLocalPackages)) {
						$res_installer->installOrUpdateWikidumps($restoredLocalPackages[$dd->getID()], $dd->getVersion(), DEPLOYWIKIREVISION_FORCE);
					}
				}
			}
		}

		if (!$databaseRestored) {
			// 8. restore resources if the database was not restored
			foreach($localPackages as $dd) {
				if (in_array($dd->getID(), $this->extToRestore)) {
					if (array_key_exists($dd->getID(), $restoredLocalPackages)) {
						$res_installer->installOrUpdateResources($restoredLocalPackages[$dd->getID()], $dd->getVersion());
					}
				}
			}
		} else {
			// otherwise just restore resource files
			$this->restoreResourceFiles();

		}
			
		// 9. restore LocalSettings.php
		if (file_exists($this->tmpDir."/LocalSettings.php")) {
			copy($this->tmpDir."/LocalSettings.php", $this->inst_dir."/LocalSettings.php");
		}

		// 10. clear rollback data
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