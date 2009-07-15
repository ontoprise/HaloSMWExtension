<?php
/**
 * Rollback an installation
 *
 */
class Rollback {


	var $inst_dir;
	var $tmpDir;
	var $extToRemove;



	static $instance;

	public static function getInstance($inst_dir) {
		if (is_null(self::$instance)) {
			self::$instance = new Rollback($inst_dir);
		}
		return self::$instance;
	}

	private function __construct($inst_dir) {

		$this->inst_dir = $inst_dir;
		$this->extToRemove = array();

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

	public function saveExtension($dd) {
		$this->acquireNewRollback();
		$localPackages = PackageRepository::getLocalPackages($this->inst_dir."/extensions");
		if (array_key_exists($dd->getID(), $localPackages)) {
			print "\nSaving extension... ".$dd->getID();
			Tools::mkpath($this->tmpDir."/stored/".$dd->getInstallationDirectory());
			Tools::copy_dir($this->inst_dir."/".$dd->getInstallationDirectory(), $this->tmpDir."/stored/".$dd->getInstallationDirectory());
			print "done.";
		}
		$this->extToRemove[] = $dd->getID();
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


	/**
	 * Rolls back the current installation.
	 *
	 */
	public function rollback() {
		//remove patches
		if (!file_exists($this->tmpDir)) {
			print "\nNothing to restore.";
			return;
		}
		$this->extToRemove = explode(",", file_get_contents($this->tmpDir."/extToRemove"));

		$localPackages = PackageRepository::getLocalPackages($this->inst_dir.'/extensions');

		print "\nRemoving patches...";
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRemove)) {

				$dp = new DeployDescriptionProcessor($this->inst_dir.'/LocalSettings.php', $dd);

				$dp->unapplyPatches();

			}
		}

		$databaseRestored = $this->restoreDatabase();
		// remove installed or updated extensions
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRemove)) {
				Tools::remove_dir($this->inst_dir."/".$dd->getInstallationDirectory());
			}
		}
		// copy old (updated) extensions
		Tools::copy_dir($this->tmpDir."/stored", $this->inst_dir);
		
		// reload local packages, because they have changed
		$restoredLocalPackages = PackageRepository::getLocalPackages($this->inst_dir.'/extensions', true);
		
		// restore wiki pages and other resources 
		$res_installer = ResourceInstaller::getInstance($this->inst_dir);
		foreach($localPackages as $dd) {
	            if (in_array($dd->getID(), $this->extToRemove)) {
	            	if (array_key_exists($dd->getID(), $restoredLocalPackages)) {
	                   if (!$databaseRestored) $res_installer->installOrUpdateWikidumps($restoredLocalPackages[$dd->getID()], $dd->getVersion(), DEPLOYWIKIREVISION_FORCE);
	                   //FIXME: it is not really necessary to upload again, if the database is restored. The file must be copied from the archive.
	                   $res_installer->installOrUpdateResources($restoredLocalPackages[$dd->getID()], $dd->getVersion());
	            	}
	            }
	        }
			
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
		$handle = fopen($this->tmpDir."/extToRemove", "w");
		fwrite($handle, implode(",", $this->extToRemove));;
		fclose($handle);
	}
}
?>