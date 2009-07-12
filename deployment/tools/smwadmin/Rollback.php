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
				if (strtolower($line) == 'n') die();
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
			Tools::mkpath($this->tmpDir."/extensions/".$dd->getInstallationDirectory());
			Tools::copy_dir($this->inst_dir."/".$dd->getInstallationDirectory(), $this->tmpDir."/extensions/".$dd->getInstallationDirectory());
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
		print "Saving database...";
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
		print "Restore database...";
		//print "\nmysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/dump.sql";
		exec("mysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not restore database."; else print "done.";

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

		$this->restoreDatabase();
		// remove installed or updated extensions
		foreach($localPackages as $dd) {
			if (in_array($dd->getID(), $this->extToRemove)) {
				Tools::remove_dir($this->inst_dir."/".$dd->getInstallationDirectory());
			}
		}
		// copy old (updated) extensions
		Tools::copy_dir($this->tmpDir."/extensions", $this->inst_dir);
			
		// restore LocalSettings.php
		copy($this->tmpDir."/LocalSettings.php", $this->inst_dir."/LocalSettings.php");

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