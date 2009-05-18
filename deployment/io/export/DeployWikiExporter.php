<?php
#
# @author: Kai Kühn Ontoprise 2009
#
# derived from
# Copyright (C) 2003, 2005, 2006 Brion Vibber <brion@pobox.com>
# http://www.mediawiki.org/
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
# http://www.gnu.org/copyleft/gpl.html

/**
 * @defgroup Dump Dump
 */

/**
 * @ingroup Dump Maintenance
 */
class DeployBackupDumper extends BackupDumper {


	function dump( $history, $text = MW_EXPORT_TEXT ) {
		# Notice messages will foul up your XML output even if they're
		# relatively harmless.
		if( ini_get( 'display_errors' ) )
		ini_set( 'display_errors', 'stderr' );

		$this->initProgress( $history );

		$db = $this->backupDb();
		$exporter = new DeployWikiExporter( $db, $history, WikiExporter::STREAM, $text );
		$exporter->dumpUploads = $this->dumpUploads;

		$wrapper = new ExportProgressFilter( $this->sink, $this );
		$exporter->setOutputSink( $wrapper );

		if( !$this->skipHeader )
		$exporter->openStream();

		if( is_null( $this->pages ) ) {
			if( $this->startId || $this->endId ) {
				$exporter->pagesByRange( $this->startId, $this->endId );
			} else {
				$exporter->allPages();
			}
		} else {
			$exporter->pagesByName( $this->pages );
		}

		if( !$this->skipFooter )
		$exporter->closeStream();

		$this->report( true );
	}


}
/**
 * Special Halo version of Wiki exporter
 */
class DeployWikiExporter extends WikiExporter {

	/**
	 * If using WikiExporter::STREAM to stream a large amount of data,
	 * provide a database connection which is not managed by
	 * LoadBalancer to read from: some history blob types will
	 * make additional queries to pull source data while the
	 * main query is still running.
	 *
	 * @param $db Database
	 * @param $history Mixed: one of WikiExporter::FULL or WikiExporter::CURRENT,
	 *                 or an associative array:
	 *                   offset: non-inclusive offset at which to start the query
	 *                   limit: maximum number of rows to return
	 *                   dir: "asc" or "desc" timestamp order
	 * @param $buffer Int: one of WikiExporter::BUFFER or WikiExporter::STREAM
	 */
	function __construct( &$db, $history = WikiExporter::CURRENT,
	$buffer = WikiExporter::BUFFER, $text = WikiExporter::TEXT ) {

		parent::__construct($db, $history, $buffer, $text);
		$this->writer  = new DeployXmlDumpWriter();
			
	}


}


/**
 * @ingroup Dump
 */
class DeployXmlDumpWriter extends XmlDumpWriter {


	var $db;
	var $currentTitle;
	var $semstore;

	function __construct() {

		$this->db = wfGetDB(DB_SLAVE);
		$this->semstore = smwfGetStore();
	}
	
    function schemaVersion() {
        return "1.0";
    }

	function openStream() {
		global $wgContLanguageCode;
		$ver = $this->schemaVersion();
		return wfElement( 'mediawiki', array(
            'xmlns'              => "http://www.ontoprise.de/halowikiexport/",
            'xmlns:xsi'          => "http://www.w3.org/2001/XMLSchema-instance",
            'xsi:schemaLocation' => "http://www.ontoprise.de/halowikiexport-$ver.xsd",
            'version'            => $ver,
            'xml:lang'           => $wgContLanguageCode ),
		null ) .
            "\n" .
		$this->siteInfo();
	}

	function openPage( $row ) {
		$out = "  <page>\n";
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		$out .= '    ' . wfElementClean( 'title', array(), $title->getPrefixedText() ) . "\n";
		$out .= '    ' . wfElement( 'id', array(), strval( $row->page_id ) ) . "\n";
		if( '' != $row->page_restrictions ) {
			$out .= '    ' . wfElement( 'restrictions', array(),
			strval( $row->page_restrictions ) ) . "\n";
		}
		$this->currentTitle = $title;
		return $out;
	}
	/**
	 * Dumps a <revision> section on the output stream, with
	 * data filled in from the given database row.
	 *
	 * @param $row object
	 * @return string
	 * @access private
	 */
	function writeRevision( $row ) {
		$fname = 'WikiExporter::dumpRev';
		wfProfileIn( $fname );

		$out  = "    <revision>\n";
		$out .= "      " . wfElement( 'id', null, strval( $row->rev_id ) ) . "\n";

		$out .= $this->writeTimestamp( $row->rev_timestamp );

		if( $row->rev_deleted & Revision::DELETED_USER ) {
			$out .= "      " . wfElement( 'contributor', array( 'deleted' => 'deleted' ) ) . "\n";
		} else {
			$out .= $this->writeContributor( $row->rev_user, $row->rev_user_text );
		}

		if( $row->rev_minor_edit ) {
			$out .=  "      <minor/>\n";
		}
		if( $row->rev_deleted & Revision::DELETED_COMMENT ) {
			$out .= "      " . wfElement( 'comment', array( 'deleted' => 'deleted' ) ) . "\n";
		} elseif( $row->rev_comment != '' ) {
			$out .= "      " . wfElementClean( 'comment', null, strval( $row->rev_comment ) ) . "\n";
		}

		if( $row->rev_deleted & Revision::DELETED_TEXT ) {
			$out .= "      " . wfElement( 'text', array( 'deleted' => 'deleted' ) ) . "\n";
		} elseif( isset( $row->old_text ) ) {
			// Raw text from the database may have invalid chars
			$text = strval( Revision::getRevisionText( $row ) );
			$out .= "      " . wfElementClean( 'text',
			array( 'xml:space' => 'preserve' ),
			strval( $text ) ) . "\n";
		} else {
			// Stub output
			$out .= "      " . wfElement( 'text',
			array( 'id' => $row->rev_text_id ),
                "" ) . "\n";
		}

		$this->addHaloProperties($out, $text, $row);

		$out .= "    </revision>\n";

		wfProfileOut( $fname );
		return $out;
	}
    
	/**
	 * Adds additional XML printputs which are relevant for Halo deployment infrastructure.
	 *
	 * @param string $out
	 * @param string $text
	 * @param StdClass $row
	 */
	protected function addHaloProperties(& $out, & $text, $row) {
		global $dumper;
		$version_p = SMWPropertyValue::makeUserProperty("Ontology version");

		$partOfBundle_p = SMWPropertyValue::makeUserProperty("Part of bundle");

		if (is_null($this->currentTitle)) {
			$dumper->progress("severe: no title");
			return;
		}

		// export version
	    $value = $this->getPropertyValue($this->currentTitle, $version_p);
        if (!is_null($value)) {
            $version = $value->getXSDValue();         
        } else {
            $version = "0.0";
        }
		$out .= "    <oversion>".$version."</oversion>\n";


		// export partOfBundle
		$value = $this->getPropertyValue($this->currentTitle, $partOfBundle_p);
		if (!is_null($value)) {
            $partOfBundle = $value->getTitle()->getPrefixedDBkey();			
		} else {
			$partOfBundle = "none";
		}
	    $out .= "    <partofbundle>".$partOfBundle."</partofbundle>\n";
		

		// export hash
		$out .= "    <hash>".md5($text)."</hash>\n";
	}
    
	/**
	 * Returns exactly one property value or null if $title does not have any
	 * or more than 1 annotation of the given property.
	 *
	 * @param Title $title
	 * @param SMWPropertyValue $property
	 * @return SMWDataValue
	 */
    private function getPropertyValue($title, $property) {
    	global $dumper;
        $values = $this->semstore->getPropertyValues($title, $property);
        if (count($values) === 0) {
            $dumper->progress($title->getText()." contains no annotation of ".$property->getDBkey());
        } else if (count($values) > 1) {
            $dumper->progress($title->getText()." contains more than 1 annotation of ".$property->getDBkey());
        } else {
            return reset($values);
        }
        return NULL;
    }
}

