<?php
/*  Copyright 2011, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DFIO
 *
 * Configurable output stream. Prints message in differnt formats 
 * to the PHP output stream.
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */

define('DF_PRINTSTREAM_TYPE_INFO', 0);
define('DF_PRINTSTREAM_TYPE_WARN', 1);
define('DF_PRINTSTREAM_TYPE_ERROR', 2);
define('DF_PRINTSTREAM_TYPE_FATAL', 4);

class DFPrintStream {

	/*
	 * printout mode
	 */
	private $mode;

	static $instance = NULL; // singleton

	public static function getInstance($mode = DF_OUTPUT_FORMAT_TEXT) {
		if (!is_null(self::$instance)) return self::$instance;
		self::$instance = new DFPrintStream($mode);
		return self::$instance;
	}

	/**
	 * Creates new Installer.
	 *
	 * @param string $rootDir Explicit root dir. Only necessary for testing
	 */
	private function __construct($mode = DF_OUTPUT_FORMAT_TEXT) {
		$this->mode = $mode;
	}

	/**
	 * Print some output to indicate progress. The output message is given by
	 * $msg, while $verbose indicates whether or not output is desired at all.
	 */
	public function reportProgress($msg, $type, $verbose = true) {
		if (!$verbose) {
			return;
		}
		if (ob_get_level() == 0) { // be sure to have some buffer, otherwise some PHPs complain
			ob_start();
		}
		print $this->formatText($msg, $type);
		ob_flush();
		flush();
	}
	
	private function formatText($text, $type) {
		switch($this->mode) {
		case DF_OUTPUT_FORMAT_TEXT:
				return is_array($text) ? implode("", $text) : $text;
				break;
			case DF_OUTPUT_FORMAT_HTML:
				if (is_array($text)) {
					for($i = 0; $i < count($text); $i++){
						$text[$i] = str_replace("\n", "<br>", $text[$i]);
						$text[$i] = str_replace("\t", '<div style="display: inline; margin-left: 10px;"></div>', $text[$i]);
						$text[$i] = str_replace("[FAILED]", '<span class="df_checkinst_error">[FAILED]</span>', $text[$i]);
						$text[$i] = str_replace("[OK]", '<span class="df_checkinst_ok">[OK]</span>', $text[$i]);
					}
					return implode("", $text);
				} else return $text;
				break;
		
		}
	} 

}