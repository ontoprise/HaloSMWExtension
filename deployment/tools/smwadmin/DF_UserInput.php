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

/**
 * @file
 * @ingroup DFInstaller
 *
 * User input methods
 *
 * @author: Kai Kühn
 *
 */
class DFUserInput {

	private static $instance = NULL; // singleton

	public static function getInstance() {
		if (!is_null(self::$instance)) return self::$instance;
		self::$instance = new DFUserInput();
		return self::$instance;
	}
	/**
	 * Callback method. Reads user for required parameters.
	 *
	 *
	 * @param array($name=>(array($type, $description, $proposal)) $userParams
	 * @param out array($name=>$value) $mapping
	 *
	 */
	public function getUserReqParams($userParams, & $mapping) {
		global $dfgNoAsk;
		if ((isset($dfgNoAsk) && $dfgNoAsk == true) || count($userParams) == 0) {
			// copy proposals
			foreach($userParams as $name => $up) {
				list($type, $description, $proposal) = $up;
				if (!is_null($proposal) && $proposal != '') {
					$parts = explode(":", $proposal);
					if (count($parts) > 1) {
						switch($parts[0]) {
							case "search": {
								global $mwrootDir;
								$proposal = Tools::whereis(trim($parts[1]), $mwrootDir);
								$proposal = trim($proposal);
								break;
							}
							default:
								$proposal = '';
								break;
						}
					}
				}
				$mapping[$name] = $proposal;
			}
			return;
		}

		global $dfgOut;
		$dfgOut->outputln("\nRequired parameters:");
		foreach($userParams as $name => $up) {
			list($type, $desc, $proposal) = $up;
			if (!is_null($proposal) && $proposal != '') {
				$parts = explode(":", $proposal);
				if (count($parts) > 1) {
					switch($parts[0]) {
						case "search": {
							global $mwrootDir;
							$proposal = Tools::whereis(trim($parts[1]), $mwrootDir);
							$proposal = trim($proposal);
							break;
						}
						default:
							$proposal = '';
							break;
					}
				}
			}

			// use proposal if given
			if (!is_null($proposal) && $proposal != '') {
				$mapping[$name] = $proposal;
			} else {
				$dfgOut->outputln("$desc\n");
				$dfgOut->output( "$name ($type): ");
				$line = trim(fgets(STDIN));
				$line = str_replace("\\", "/", $line); // do not allow backslashes
				$mapping[$name] = $line;
			}
		}

	}

	/**
	 * Callback method. Requests a confirmation by the user.
	 *
	 * @param string $message
	 * @param out boolean $result
	 * @return unknown
	 */
	public function getUserConfirmation($message, & $result) {
		global $dfgNoAsk, $dfgOut;
		if ((isset($dfgNoAsk) && $dfgNoAsk == true)) return 'y';
		$dfgOut->outputln("\n$message [ (y)es/(n)o ]");
		$line = trim(fgets(STDIN));
		$result = strtolower($line);
	}



	/**
	 * Callback method which decides what to do on a modified page.
	 * Contains out parameters which is declared by the call_user_func()
	 *
	 * @param DeployWikiRevision $deployRevision
	 * @param int $mode
	 * @param out boolean $result
	 */
	public function modifiedPage($deployRevision, $mode, & $result) {
		global $dfgOut, $dfgNoAsk;
		if ((isset($dfgNoAsk) && $dfgNoAsk == true)) {
			$result = true;
			return;
		}
		static $overwrite = false;
		switch ($mode) {
			case DEPLOYWIKIREVISION_FORCE:
				$result = true;
				break;
			case DEPLOYWIKIREVISION_WARN:
				$result = true;
				if ($overwrite) break;
				$dfgOut->outputln("Page '".$deployRevision->title->getText()."' has been changed.");
				$dfgOut->output("Overwrite? [(y)es/(n)o/(a)ll]?");
				$line = trim(fgets(STDIN));
				$overwrite = (strtolower($line) == 'a');
				$result = (strtolower($line) != 'n');
				break;
			case DEPLOYWIKIREVISION_INFO:
				$result = false;
				$dfgOut->outputln("Page '".$deployRevision->title->getText()."' has been changed");
				break;
			default: $result = false;
		}
	}

	/**
	 * Callback method which decides what to do on a modified page.
	 * Contains out parameters which is declared by the call_user_func()
	 *
	 * @param DeployWikiRevision $deployRevision
	 * @param int $mode
	 * @param out boolean $result
	 */
	public function pageToBeMerged($deployRevision, $mode, & $result) {
		global $dfgOut, $dfgNoAsk;
		if ((isset($dfgNoAsk) && $dfgNoAsk == true)) {
			$result = true;
			return;
		}
		static $overwrite = false;
		switch ($mode) {
			case DEPLOYWIKIREVISION_FORCE:
				$result = true;
				break;
			case DEPLOYWIKIREVISION_WARN:
				$result = true;
				if ($overwrite) break;
				$dfgOut->outputln("Page '".$deployRevision->title->getText()."' belongs to another bundle.");
				$dfgOut->output("Merge? [(y)es/(n)o/(a)ll]?");
				$line = trim(fgets(STDIN));
				$overwrite = (strtolower($line) == 'a');
				$result = (strtolower($line) != 'n');
				break;
			case DEPLOYWIKIREVISION_INFO:
				$result = false;
				$dfgOut->outputln("Page '".$deployRevision->title->getText()."' belongs to another bundle.");
				break;
			default: $result = false;
		}
	}

	/**
	 * Asks for a confirmation.
	 */
	public static function consoleConfirm($msg = "") {
		global $dfgNoAsk;
		if ((isset($dfgNoAsk) && $dfgNoAsk == true)) return true;
		if ($msg !== '') print "\n$msg";
		$a = trim(fgets(STDIN));
		return strtolower($a) === 'y';
	}

	/**
	 * Selects an element from a list.
	 * In No-ask mode, it always returns the first.
	 *
	 * @param string[] $elements
	 *
	 * @return int index
	 */
	public static function selectElement($msg, $elements) {
		global $dfgNoAsk;
		if ((isset($dfgNoAsk) && $dfgNoAsk == true)) return 0;
		print "\n\n$msg\n";
		do {
			$i = 1;
			foreach($elements as $e) {
				print "\n($i) $e";
				$i++;
			}
			print "\n\nSelect (1-".count($elements)."): ";
			$a = trim(fgets(STDIN));
		} while(!is_numeric($a) || $a < 1 || $a > count($elements));
		return $a-1;
	}

	public static function askForRestorePoint(& $name, $restoreDir) {
		global $dfgNoAsk;
		if (isset($dfgNoAsk) && $dfgNoAsk == true) {
			return false;
		}

		global $dfgOut;
		static $calledOnce = false;
		static $answer;
		static $namedStored;
		$name = $namedStored;

		if ($calledOnce) return $answer;
		$calledOnce = true;
		 
		$dfgOut->outputln("Create new restore point (y/n)? ");
		$line = trim(fgets(STDIN));
		if (strtolower($line) == 'n') {
			$dfgOut->outputln("\nDo not create a restore point.\n\n");
			$answer = false;
			return $answer;
		}

		$namedStored = self::getRestorePointName($restoreDir);
		$name = $namedStored;


	}

	/**
	 * Asks for the name of a restore point.
	 * If it exists it asks for permission to overwrite.
	 *
	 * Note: REQUIRES user interaction
	 *
	 * @return string Name of restore point directory.
	 */
	protected function getRestorePointName($restoreDir) {
		global $dfgOut;
		$done = false;
		do {
			$dfgOut->outputln("Please enter a name for the restore point: ");
			$name = trim(fgets(STDIN));
			$name = str_replace(" ","_", $name);

			if (preg_match('/\w+/', $name, $matches) === false) continue;
			if ($name !== $matches[0]) {
				$dfgOut->outputln("Forbidden characters. Please use only alphanumeric chars and spaces");
				continue;
			}

			// clear if it already exists
			if (file_exists($restoreDir.$name)) {
				$dfgOut->outputln("A restore point with this name already exists. Overwrite? (y/n) ");
				$line = trim(fgets(STDIN));
				if (strtolower($line) == 'n') {
					continue;
				}
			}
			$done = true;
		} while(!$done);
		return $name;
	}

}
