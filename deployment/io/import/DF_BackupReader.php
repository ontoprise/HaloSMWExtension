<?php
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

    function BackupReader($mode) {
        $this->stderr = fopen( "php://stderr", "wt" );
        $this->mode = $mode;
    }

    function reportPage( $page ) {
        $this->pageCount++;
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
            call_user_func( $this->importCallback, $rev );
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

        $source = new ImportStreamSource( $handle );
        $importer = new DeployWikiImporter( $source, $this->mode, $this );

        $importer->setDebug( $this->debug );
        $importer->setPageCallback( array( &$this, 'reportPage' ) );
        $this->importCallback =  $importer->setRevisionCallback(
        array( &$this, 'handleRevision' ) );
        $this->uploadCallback = $importer->setUploadCallback(
        array( &$this, 'handleUpload' ) );

        return $importer->doImport();
    }

    function modifiedPage($deployRevision, $mode, & $result) {
        static $overwrite = false;
        switch ($mode) {
            case DEPLOYWIKIREVISION_FORCE:
                $result = true;
                break;
            case DEPLOYWIKIREVISION_WARN:
                $result = true;
                if ($overwrite) break;
                print "\n".$deployRevision->title->getText()." has been changed.";
                print "Overwrite? [(y)es/(n)o/(a)ll]?";
                $line = trim(fgets(STDIN));
                $overwrite = (strtolower($line) == 'a');
                $result = (strtolower($line) != 'n');
                break;
            case DEPLOYWIKIREVISION_INFO:
                $result = false;
                print "\n".$deployRevision->title->getText()." has been changed";
                break;
            default: $result = false;
        }
    }
}
