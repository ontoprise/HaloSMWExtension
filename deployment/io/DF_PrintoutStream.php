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
 * @ingroup DFIO
 *
 * Configurable output stream. Prints message in differnt formats
 * to the PHP output stream.
 *
 * @author: Kai Kühn
 *
 */
define ("DF_OUTPUT_FORMAT_TEXT", 0);
define ("DF_OUTPUT_FORMAT_HTML", 1);

define ("DF_OUTPUT_TARGET_STDOUT", 0);
define ("DF_OUTPUT_TARGET_FILE", 1);


define('DF_PRINTSTREAM_TYPE_INFO', 0);
define('DF_PRINTSTREAM_TYPE_WARN', 1);
define('DF_PRINTSTREAM_TYPE_ERROR', 2);
define('DF_PRINTSTREAM_TYPE_FATAL', 4);

//require_once($rootDir."/io/DF_Log.php");

class DFPrintoutStream {

	/*
	 * printout mode
	 */
	private $mode;
	private $target;
	private $tmpfile;

	static $instance = NULL; // singleton

	public static function getInstance($mode = DF_OUTPUT_FORMAT_TEXT) {
		if (!is_null(self::$instance)) {
			self::$instance->mode = $mode;
			return self::$instance;	
		}
		self::$instance = new DFPrintoutStream($mode);
		return self::$instance;
	}

	/**
	 * Creates new Installer.
	 *
	 * @param string $rootDir Explicit root dir. Only necessary for testing
	 */
	private function __construct($mode = DF_OUTPUT_FORMAT_TEXT) {
		$this->mode = $mode;
		$this->verbose = true;
		$this->target = DF_OUTPUT_TARGET_STDOUT;
	}

	public function start($target = DF_OUTPUT_TARGET_STDOUT, $data = NULL) {
		$this->target = $target;
		if ($target == DF_OUTPUT_TARGET_STDOUT) return;
		if ($target == DF_OUTPUT_TARGET_FILE) {
			$logger = Logger::getInstance();
			$logdir = $logger->getLogDir();
			$local = $data;
			$file = "$logdir/$local";
			Tools::mkpath(dirname($file));
			$this->tmpfile = $file;
            return $local;
		}
	}

	public function end() {
		if ($this->target == DF_OUTPUT_TARGET_STDOUT) return;

		if ($this->target == DF_OUTPUT_TARGET_FILE) {

			$this->tmpfile = NULL;
		}
	}

	/**
	 * Returns output mode.
	 *
	 * @return int
	 */
	public function getMode() {
		return $this->mode;
	}
	
    /**
     * Sets output mode.
     *
     * @param string $mode (text, html)
     */
    public function setMode($mode) {
    	if ($mode == "text") {
    	    $this->mode = DF_OUTPUT_FORMAT_TEXT;	
    	} else if ($mode == "html") {
    		$this->mode = DF_OUTPUT_FORMAT_HTML;
    	} else {
    		$this->mode = DF_OUTPUT_FORMAT_TEXT;
    	}
        
    }

	public function setVerbose($verbose) {
		$this->verbose = $verbose;
	}

	/**
	 * Print some output to indicate progress. The output message is given by
	 * $msg, while $verbose indicates whether or not output is desired at all.
	 */
	public function output($msg, $type = DF_PRINTSTREAM_TYPE_INFO) {
		if (!$this->verbose) {
			return;
		}
		if ($this->target == DF_OUTPUT_TARGET_FILE) {
			$handle = fopen($this->tmpfile, "a");
			fwrite($handle, $this->formatText($msg, $type));
			fclose($handle);
		}
		
		print $this->formatText($msg, $type);
	
	}

	/**
	 * Print some output to indicate progress. The output message is given by
	 * $msg, while $verbose indicates whether or not output is desired at all.
	 */
	public function outputln($msg = '', $type = DF_PRINTSTREAM_TYPE_INFO) {
		if (!$this->verbose) {
			return;
		}

		if ($this->target == DF_OUTPUT_TARGET_FILE) {
			$handle = fopen($this->tmpfile, "a");
            fwrite($handle, $this->formatText($msg, $type, "\n"));
            fclose($handle);
		}
		
		print $this->formatText($msg, $type, "\n");
		
	}

	/**
	 * Print some output to indicate progress. The output message is given by
	 * $msg, while $verbose indicates whether or not output is desired at all.
	 */
	public function getln($msg = '', $type = DF_PRINTSTREAM_TYPE_INFO, $verbose = true) {
		if (!$verbose) {
			return;
		}
			
		return $this->formatText($msg, $type, "\n");

	}

	private function formatText($text, $type, $preLined = '') {
		global $dfgLang;
		switch($this->mode) {
			default:
			case DF_OUTPUT_FORMAT_TEXT:
				$prefix = "";
				switch($type) {
					case DF_PRINTSTREAM_TYPE_WARN:
						$prefix .= "[".$dfgLang->getLanguageString('df_warn'). "]\n";
						break;
					case DF_PRINTSTREAM_TYPE_ERROR:
						$prefix .= "[".$dfgLang->getLanguageString('df_error'). "]\n";
						break;
					case DF_PRINTSTREAM_TYPE_FATAL:
						$prefix .= "[".$dfgLang->getLanguageString('df_fatal'). "]\n";
				}
				if (is_array($text)) {
					$result = "";
					foreach($text as $t) {
						$result .= $prefix . $t;
					}
					return $preLined.$result;
				}
				return $preLined.$prefix .$text;
				break;
			case DF_OUTPUT_FORMAT_HTML:
				if ($preLined == "\n") {
					$preLined = "<br/>";
				}
				if (is_array($text)) {
					$implodedText = "";
					foreach($text as $t) {
						$t = str_replace("\n", "<br>", $t);
						$t = str_replace("\t", '<div style="display: inline; margin-left: 10px;"></div>', $t);
						$t = str_replace("[FAILED]", '<span class="df_checkinst_error">['.$dfgLang->getLanguageString('df_failed').']</span>', $t);
						$t = str_replace("[OK]", '<span class="df_checkinst_ok">['.$dfgLang->getLanguageString('df_ok').']</span>', $t);
							
						$implodedText .= $t;
					}
					switch($type) {
						case DF_PRINTSTREAM_TYPE_WARN:
							$implodedText = '<span class="df_checkinst_error">['.$dfgLang->getLanguageString('df_warn').']</span>' . " " . $implodedText;
							break;
						case DF_PRINTSTREAM_TYPE_ERROR:
							$implodedText = '<span class="df_checkinst_error">['.$dfgLang->getLanguageString('df_error').']</span>'. " " .$implodedText;
							break;
						case DF_PRINTSTREAM_TYPE_FATAL:
							$implodedText = '<span class="df_checkinst_error">['.$dfgLang->getLanguageString('df_fatal').']</span>'. " " . $implodedText;
					}
					return $preLined.$implodedText;
				} else return $preLined.$text;
				break;

		}
	}

}
