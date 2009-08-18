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

require_once 'DF_DeployUploadExporter.php';

class DeployBackupDumper extends BackupDumper {

	private $bundleToExport;

	function __construct($argv) {
		parent::__construct($argv);
		for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

			//-b => Bundle to export
			if ($arg == '-b') {
				$bundleToExport = next($argv);
				if ($package === false) fatalError("No bundle given.");
				$bundleToExport = strtoupper(substr($bundleToExport, 0,1)).substr($bundleToExport,1);
				$this->bundleToExport = $bundleToExport;
				continue;
			}
		}
	}

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

		if (isset($this->bundleToExport)) {
			$exporter->exportBundle($this->bundleToExport);
		} else {
			if( is_null( $this->pages ) ) {
				if( $this->startId || $this->endId ) {
					$exporter->pagesByRange( $this->startId, $this->endId );
				} else {
					$exporter->allPages();
				}
			} else {
				$exporter->pagesByName( $this->pages );
			}
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

	/**
	 * Export the given bundle and all its pages. A bundle is denoted by a bundle identifier.
	 * All pages belonging to that bundle have an annotation to it (Part of bundle). Instances of
	 * categories beloning to the bundle are exported automatically.
	 *
	 * @param string $bundleID
	 */
	function exportBundle($bundeID) {
		global $dfgLang;

		$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeProperty($dfgLang->getLanguageString("df_partofbundle")));
		$partOfBundleID = smwfGetStore()->getSMWPageID($bundeID, NS_MAIN, "");
		$smwids     = $this->db->tableName( 'smw_ids' );
		$smwrels     = $this->db->tableName( 'smw_rels2' );
		$categorylinks     = $this->db->tableName( 'categorylinks' );

		// export all pages of bundle
		$joint = "$smwids,$smwrels";
		$cond = "s_id = smw_id AND page_title = smw_title AND page_namespace = smw_namespace ".
                "AND p_id = ".$partOfBundlePropertyID." AND o_id = ".$partOfBundleID;
		$this->dumpFrom($joint, $cond);

		// export all instances of categories belonging to this bundle
		// (except if they are from cat or prop namespace)
		$joint = "$categorylinks";
		$cond = "page_id = cl_from AND page_namespace != ".NS_CATEGORY." AND page_namespace != ".SMW_NS_PROPERTY.
                " AND cl_to IN (SELECT smw_title FROM $smwids,$smwrels WHERE smw_id = s_id ".
                " AND p_id = $partOfBundlePropertyID AND o_id = $partOfBundleID)";
			
		$this->dumpFrom($joint, $cond);

	

		
	}

	

	function dumpFrom( $joint = '', $cond = '' ) {
		$fname = 'WikiExporter::dumpFrom';
		wfProfileIn( $fname );

		$page     = $this->db->tableName( 'page' );
		$revision = $this->db->tableName( 'revision' );
		$text     = $this->db->tableName( 'text' );


		$order = 'ORDER BY page_id';
		$limit = '';

		if( $this->history == WikiExporter::FULL ) {
			$join = 'page_id=rev_page';
		} elseif( $this->history == WikiExporter::CURRENT ) {
			if ( $this->list_authors && $cond != '' )  { // List authors, if so desired
				$this->do_list_authors ( $page , $revision , $cond );
			}
			$join = 'page_id=rev_page AND page_latest=rev_id';
		} elseif ( is_array( $this->history ) ) {
			$join = 'page_id=rev_page';
			if ( $this->history['dir'] == 'asc' ) {
				$op = '>';
				$order .= ', rev_timestamp';
			} else {
				$op = '<';
				$order .= ', rev_timestamp DESC';
			}
			if ( !empty( $this->history['offset'] ) ) {
				$join .= " AND rev_timestamp $op " . $this->db->addQuotes(
				$this->db->timestamp( $this->history['offset'] ) );
			}
			if ( !empty( $this->history['limit'] ) ) {
				$limitNum = intval( $this->history['limit'] );
				if ( $limitNum > 0 ) {
					$limit = "LIMIT $limitNum";
				}
			}
		} else {
			wfProfileOut( $fname );
			return new WikiError( "$fname given invalid history dump type." );
		}
		$where = ( $cond == '' ) ? '' : "$cond AND";
		$joinTables = ( $joint == '' ) ? '' : ", $joint ";

		if( $this->buffer == WikiExporter::STREAM ) {
			$prev = $this->db->bufferResults( false );
		}
		if( $cond == '' ) {
			// Optimization hack for full-database dump
			$revindex = $pageindex = $this->db->useIndexClause("PRIMARY");
			$straight = ' /*! STRAIGHT_JOIN */ ';
		} else {
			$pageindex = '';
			$revindex = '';
			$straight = '';
		}
		if( $this->text == WikiExporter::STUB ) {
			$sql = "SELECT $straight * FROM
			$page $pageindex,
			$revision $revindex
			$joinTables
			WHERE $where $join
			$order $limit";

		} else {

			$sql = "SELECT $straight * FROM
			$page $pageindex,
			$revision $revindex,
			$text
			$joinTables
			WHERE $where $join AND rev_text_id=old_id
			$order $limit";

		}

		$result = $this->db->query( $sql, $fname );
		$wrapper = $this->db->resultObject( $result );
		$this->outputStream( $wrapper );

		if ( $this->list_authors ) {
			$this->outputStream( $wrapper );
		}

		if( $this->buffer == WikiExporter::STREAM ) {
			$this->db->bufferResults( $prev );
		}

		wfProfileOut( $fname );
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

		if (is_null($this->currentTitle)) {
			$dumper->progress("severe: no title");
			return;
		}

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


