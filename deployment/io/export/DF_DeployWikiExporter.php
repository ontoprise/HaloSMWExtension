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
 * derived from
 *   Copyright (C) 2003, 2005, 2006 Brion Vibber <brion@pobox.com>
 *   http://www.mediawiki.org/
 */

/**
 * @ingroup Dump Maintenance
 */

require_once 'DF_DeployUploadExporter.php';

class DeployBackupDumper extends BackupDumper {

	private $bundleToExport;

	function __construct($argv) {
		parent::__construct($argv);
		$this->includeInstances = false;
		$this->includeTemplates = false;
		for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

			//-b => Bundle to export
			if ($arg == '-b') {
				$bundleToExport = next($argv);
				if ($bundleToExport === false) Tools::exitOnFatalError("No bundle given.");
				$bundleToExport = strtoupper(substr($bundleToExport, 0,1)).substr($bundleToExport,1);
				$this->bundleToExport = $bundleToExport;
				continue;
			}
				
			// --includeInstances means: consider member of categories beloning to a bundle
			else if (strpos($arg, '--includeInstances') === 0) {
				if ($arg=='--includeInstances') {
					$this->includeInstances = true;
				} else {
					list($option, $value) = explode("=", $arg);
					$this->includeInstances = ($value == 'true' || $value == '1' || $value == 'yes');
				}
			}

			// --includeTemplates means: consider all templates used on pages of bundle
			else if (strpos($arg, '--includeTemplates') === 0) {
				if ($arg=='--includeTemplates') {
					$this->includeTemplates = true;
				} else {
					list($option, $value) = explode("=", $arg);
					$this->includeTemplates = ($value == 'true' || $value == '1' || $value == 'yes');
				}
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
		$exporter = new DeployWikiExporter( $db, $this->bundleToExport, $history, WikiExporter::STREAM, $text );
		$exporter->dumpUploads = $this->dumpUploads;

		$wrapper = new ExportProgressFilter( $this->sink, $this );
		$exporter->setOutputSink( $wrapper );

		if( !$this->skipHeader )
		$exporter->openStream();

		if (isset($this->bundleToExport)) {
			$exporter->exportBundle($this->bundleToExport, $this->includeInstances, $this->includeTemplates);
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
	function __construct( &$db, $bundleID, $history = WikiExporter::CURRENT,
	$buffer = WikiExporter::BUFFER, $text = WikiExporter::TEXT ) {

		parent::__construct($db, $history, $buffer, $text);
		$this->writer  = new DeployXmlDumpWriter($bundleID);


	}

	/**
	 * Export the given bundle and all its pages. A bundle is denoted by a bundle identifier.
	 * All pages belonging to that bundle have an annotation to it (Part of bundle). Instances of
	 * categories beloning to the bundle are exported automatically.
	 *
	 * @param string $bundleID
	 */
	function exportBundle($bundeID, $includeInstances, $includeTemplates) {
		global $dfgLang;

		$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString("df_partofbundle")));
		$partOfBundleID = smwfGetStore()->getSMWPageID($bundeID, NS_MAIN, "", "");
		$smwids     = $this->db->tableName( 'smw_ids' );
		$smwrels     = $this->db->tableName( 'smw_rels2' );
		$categorylinks     = $this->db->tableName( 'categorylinks' );
		$templatelinks     = $this->db->tableName( 'templatelinks' );
		$page     = $this->db->tableName( 'page' );

		// export all pages of bundle
		$joint = "$smwids,$smwrels";
		$cond = "s_id = smw_id AND page_title = smw_title AND page_namespace = smw_namespace ".
                "AND p_id = ".$partOfBundlePropertyID." AND o_id = ".$partOfBundleID;
		$this->dumpFrom($joint, $cond);

		// all templates used by pages of bundle
		if ($includeTemplates) {
			$joint = "$templatelinks";
			$cond = "page_title = tl_title AND page_namespace = tl_namespace ".
                "AND tl_from IN (SELECT tl_from FROM $page,$templatelinks,$smwids,$smwrels WHERE smw_title = page_title AND smw_namespace = page_namespace AND smw_id = s_id ".
                " AND p_id = $partOfBundlePropertyID AND o_id = $partOfBundleID AND page_id = tl_from)";
			$this->dumpFrom($joint, $cond);
		}

		if ($includeInstances) {
			// export all instances of categories belonging to this bundle
			// (except if they are from cat or prop namespace)
			$joint = "$categorylinks";
			$cond = "page_id = cl_from AND page_namespace != ".NS_CATEGORY." AND page_namespace != ".SMW_NS_PROPERTY.
                " AND cl_to IN (SELECT smw_title FROM $smwids,$smwrels WHERE smw_id = s_id ".
                " AND p_id = $partOfBundlePropertyID AND o_id = $partOfBundleID)";

			$this->dumpFrom($joint, $cond);

			if ($includeTemplates) {
				// export templates used by instances of category belonging to this bundle
				$joint = "$categorylinks,$templatelinks";
				$cond = "page_title = tl_title AND page_namespace = tl_namespace AND cl_from = tl_from ".
                " AND cl_to IN (SELECT smw_title FROM $smwids,$smwrels WHERE smw_id = s_id ".
                " AND p_id = $partOfBundlePropertyID AND o_id = $partOfBundleID)";

				$this->dumpFrom($joint, $cond);
			}
		}



	}



	function dumpFrom( $joint = '', $cond = '' ) {
		$fname = 'WikiExporter::dumpFrom';
		wfProfileIn( $fname );

		$page     = $this->db->tableName( 'page' );
		$revision = $this->db->tableName( 'revision' );
		$text     = $this->db->tableName( 'text' );


		$order = 'ORDER BY page_id';
		$groupby = '';
		$limit = '';

		if( $this->history == WikiExporter::FULL ) {
			$join = 'page_id=rev_page';
			$groupby = 'GROUP BY rev_id';
		} elseif( $this->history == WikiExporter::CURRENT ) {
			if ( $this->list_authors && $cond != '' )  { // List authors, if so desired
				$this->do_list_authors ( $page , $revision , $cond );
			}
			$join = 'page_id=rev_page AND page_latest=rev_id';
			$groupby = 'GROUP BY page_id';
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
			$groupby $order $limit";

		} else {

			$sql = "SELECT $straight * FROM
			$page $pageindex,
			$revision $revindex,
			$text
			$joinTables
			WHERE $where $join AND rev_text_id=old_id 
			$groupby $order $limit";

		}

		$result = $this->db->query( $sql, $fname );
		$wrapper = $this->db->resultObject( $result );
		$this->outputPageStream( $wrapper );

		if ( $this->list_authors ) {
			$this->outputPageStream( $wrapper );
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
	var $bundleID;

	function __construct($bundleID) {

		$this->db = wfGetDB(DB_SLAVE);
		$this->semstore = smwfGetStore();
		$this->bundleID = $bundleID;
	}

	function schemaVersion() {
		return "1.0";
	}

	// namespace must not be changed
	// otherwise MW import won't work.
	function openStream() {
		global $wgContLanguageCode;
		$ver = $this->schemaVersion();
		return Xml::element( 'mediawiki', array(
            'xmlns'              => "http://www.mediawiki.org/halowikiexport/",
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
		$out .= '    ' . Xml::elementClean( 'title', array(), $title->getPrefixedText() ) . "\n";
		$out .= '    ' . Xml::element( 'id', array(), strval( $row->page_id ) ) . "\n";
		if( '' != $row->page_restrictions ) {
			$out .= '    ' . Xml::element( 'restrictions', array(),
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
		//$out .= "      " . wfElement( 'id', null, strval( $row->rev_id ) ) . "\n";

		$out .= $this->writeTimestamp( $row->rev_timestamp );

		if( $row->rev_deleted & Revision::DELETED_USER ) {
			$out .= "      " . Xml::element( 'contributor', array( 'deleted' => 'deleted' ) ) . "\n";
		} else {
			$out .= $this->writeContributor( $row->rev_user, $row->rev_user_text );
		}

		if( $row->rev_minor_edit ) {
			$out .=  "      <minor/>\n";
		}
		if( $row->rev_deleted & Revision::DELETED_COMMENT ) {
			$out .= "      " . Xml::element( 'comment', array( 'deleted' => 'deleted' ) ) . "\n";
		} elseif( $row->rev_comment != '' ) {
			$out .= "      " . Xml::elementClean( 'comment', null, strval( $row->rev_comment ) ) . "\n";
		}

		if( $row->rev_deleted & Revision::DELETED_TEXT ) {
			$out .= "      " . wfElement( 'text', array( 'deleted' => 'deleted' ) ) . "\n";
		} elseif( isset( $row->old_text ) ) {
			// Raw text from the database may have invalid chars
			$text = strval( Revision::getRevisionText( $row ) );
			$this->removeOtherBundles($text);
			$out .= "      " . Xml::elementClean( 'text',
			array( 'xml:space' => 'preserve' ),
			strval( $text ) ) . "\n";
		} else {
			// Stub output
			$out .= "      " . Xml::element( 'text',
			array( 'id' => $row->rev_text_id ),
                "" ) . "\n";
		}

		$this->addHaloProperties($out, $text, $row);

		$out .= "    </revision>\n";

		wfProfileOut( $fname );
		return $out;
	}
	
	/**
     * Removes ontology sections from other bundles then the
     * exported one. 
     * 
     * @param $text (out)
     */
	protected function removeOtherBundles(& $text) {
		$om = new OntologyMerger();
		$allBundles = $om->getAllBundles($text);
		$keepText = false;
		if (in_array($this->bundleID, $allBundles) || in_array(lcfirst($this->bundleID), $allBundles)  || in_array(ucfirst($this->bundleID), $allBundles)) {
			$keepText = $om->getBundleContent($this->bundleID, $text);
		}
		foreach($allBundles as $id) {
			$text = $om->removeBundle($id, $text);
		}
		if ($keepText !== false) {
			$text = $om->addBundle($this->bundleID, $text, $keepText);
		}
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

		// no additional changes. can be removed??
	}

	
}


