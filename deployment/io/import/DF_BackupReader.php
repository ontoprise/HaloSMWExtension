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
 * @ingroup Maintenance
 */
class BackupReader {
	var $reportingInterval = 100;
	var $reporting = true;
	var $pageCount = 0;
	var $revCount  = 0;
	var $dryRun    = false;
	var $debug     = false;
	var $uploads   = false;
	var $mode = 0;
	var $bundleID;
	
	var $importedPages = array();

	function BackupReader($mode, $bundleID) {
		$this->stderr = fopen( "php://stderr", "wt" );
		$this->mode = $mode;
		$this->bundleID = $bundleID;
	}

	function reportPage( $page ) {
		$this->importedPages[] = $page;
		$this->pageCount++;
	}
	
	function getImportedPages() {
		return $this->importedPages;
	}

	function handleRevision( $rev ) {
		$title = $rev->getTitle();
		if (!$title) {
			$this->progress( "Got bogus revision with null title!" );
			return;
		}
		#$timestamp = $rev->getTimestamp();
		#$display = $title->getPrefixedText();
		#echo "$display $timestamp\n";

		$this->revCount++;
		$this->report();

		if( !$this->dryRun ) {
			@call_user_func( $this->importCallback, $rev );
		}
	}

	function handleUpload( $revision ) {
		if( $this->uploads ) {
			$this->uploadCount++;
			//$this->report();
			$this->progress( "upload: " . $revision->getFilename() );

			if( !$this->dryRun ) {
				// bluuuh hack
				//call_user_func( $this->uploadCallback, $revision );
				$dbw = wfGetDB( DB_MASTER );
				return $dbw->deadlockLoop( array( $revision, 'importUpload' ) );
			}
		}
	}

	function report( $final = false ) {
		if( $final xor ( $this->pageCount % $this->reportingInterval == 0 ) ) {
			$this->showReport();
		}
	}

	function showReport() {
		if( $this->reporting ) {
			$delta = wfTime() - $this->startTime;
			if( $delta ) {
				$rate = sprintf("%.2f", $this->pageCount / $delta);
				$revrate = sprintf("%.2f", $this->revCount / $delta);
			} else {
				$rate = '-';
				$revrate = '-';
			}
			$this->progress( "$this->pageCount ($rate pages/sec $revrate revs/sec)" );
		}
		wfWaitForSlaves(5);
	}

	function progress( $string ) {
		fwrite( $this->stderr, $string . "\n" );
	}

	function importFromFile( $filename ) {
		if( preg_match( '/\.gz$/', $filename ) ) {
			$filename = 'compress.zlib://' . $filename;
		}
		$file = fopen( $filename, 'rt' );
		return $this->importFromHandle( $file );
	}

	function importFromStdin() {
		$file = fopen( 'php://stdin', 'rt' );
		return $this->importFromHandle( $file );
	}

	function importFromHandle( $handle ) {
		$this->startTime = wfTime();

		$this->importPredefinedTemplates();

		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporter( $source, $this->mode, DFUserInput::getInstance(), $this->bundleID );

		$importer->setDebug( $this->debug );
		$importer->setPageCallback( array( &$this, 'reportPage' ) );
		$this->importCallback =  $importer->setRevisionCallback(
		array( &$this, 'handleRevision' ) );
		$this->uploadCallback = $importer->setUploadCallback(
		array( &$this, 'handleUpload' ) );

		return $importer->doImport();
	}

	

	/**
	 * Creates the content hash template if it does not exist.
	 *
	 */
	private function importPredefinedTemplates() {
		global $dfgLang;
		global $dfgOut;
					
		$t = Title::newFromText($dfgLang->getLanguageString('df_partofbundle'), NS_TEMPLATE);
		if (!$t->exists()) {
			$a = new Article($t);
			$dfgOut->outputln("\tCreating template '".$dfgLang->getLanguageString('df_partofbundle')."'...");
			$a->insertNewArticle("[[".$dfgLang->getLanguageString('df_partofbundle')."::{{{value|}}}| ]]", "auto-generated", false, false);
			$dfgOut->output("done.");
		}
	}
}
