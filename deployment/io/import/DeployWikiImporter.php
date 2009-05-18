<?php

/**
 * @author: Kai Kühn / ontoprise / 2009
 * 
 * derived from
 * MediaWiki page data importer
 * Copyright (C) 2003,2005 Brion Vibber <brion@pobox.com>
 * http://www.mediawiki.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 * 
 * Extends the default wiki import mechanism.
 *
 *  1. Version control
 *  2. Bundle control
 *  3. Hash values to check integrity
 *
 */
class DeployWikiImporter extends WikiImporter {

	function in_page( $parser, $name, $attribs ) {
		$this->debug( "in_page $name" );
		switch( $name ) {
			case "id":
			case "title":
			case "restrictions":
				$this->appendfield = $name;
				$this->appenddata = "";
				xml_set_element_handler( $parser, "in_nothing", "out_append" );
				xml_set_character_data_handler( $parser, "char_append" );
				break;
			case "revision":
				$this->push( "revision" );
				if( is_object( $this->pageTitle ) ) {
					$this->workRevision = new DeployWikiRevision;
					$this->workRevision->setTitle( $this->pageTitle );
					$this->workRevisionCount++;
				} else {
					// Skipping items due to invalid page title
					$this->workRevision = null;
				}
				xml_set_element_handler( $parser, "in_revision", "out_revision" );
				break;
			case "upload":
				$this->push( "upload" );
				if( is_object( $this->pageTitle ) ) {
					$this->workRevision = new DeployWikiRevision;
					$this->workRevision->setTitle( $this->pageTitle );
					$this->uploadCount++;
				} else {
					// Skipping items due to invalid page title
					$this->workRevision = null;
				}
				xml_set_element_handler( $parser, "in_upload", "out_upload" );
				break;
			default:
				return $this->throwXMLerror( "Element <$name> not allowed in a <page>." );
		}
	}


	function in_revision( $parser, $name, $attribs ) {
		$this->debug( "in_revision $name" );
		switch( $name ) {
			case "oversion":

				$this->appendfield = $name;
				xml_set_element_handler( $parser, "in_nothing", "out_append" );
				xml_set_character_data_handler( $parser, "char_append" );
				break;

			case "partofbundle":

				$this->appendfield = $name;
				xml_set_element_handler( $parser, "in_nothing", "out_append" );
				xml_set_character_data_handler( $parser, "char_append" );
				break;
			case "hash":

				$this->appendfield = $name;
				xml_set_element_handler( $parser, "in_nothing", "out_append" );
				xml_set_character_data_handler( $parser, "char_append" );
				break;
			default:
				return parent::in_revision($parser, $name, $attribs);
		}
	}

	function out_append( $parser, $name ) {
		$this->debug( "out_append $name" );
		if( $name != $this->appendfield ) {
			return $this->throwXMLerror( "Expected </{$this->appendfield}>, got </$name>" );
		}

		switch( $this->appendfield ) {
			case "oversion":
				if ( $this->parentTag() == 'revision' ) {
					if( $this->workRevision )
					$this->workRevision->setVersion( $this->appenddata );
				}
				break;
				break;
			case "partofbundle":
				if ( $this->parentTag() == 'revision' ) {
					if( $this->workRevision )
					$this->workRevision->setPartOfBundle( $this->appenddata );
				}
				break;
			case "hash":
				if ( $this->parentTag() == 'revision' ) {
					if( $this->workRevision )
					$this->workRevision->setHash( $this->appenddata );
				}
				break;
			default:
				parent::out_append($parser, $name);
		}
		$this->appendfield = "";
		$this->appenddata = "";

		$parent = $this->parentTag();
		xml_set_element_handler( $parser, "in_$parent", "out_$parent" );
		xml_set_character_data_handler( $parser, "donothing" );
	}
	private function push( $name ) {
		array_push( $this->tagStack, $name );
		$this->debug( "PUSH $name" );
	}

	private function pop() {
		$name = array_pop( $this->tagStack );
		$this->debug( "POP $name" );
		return $name;
	}
	private function parentTag() {
		$name = $this->tagStack[count( $this->tagStack ) - 1];
		$this->debug( "PARENT $name" );
		return $name;
	}

}

class DeployWikiRevision extends WikiRevision {
	var $oversion;
	var $partofbundle;
    var $md5_hash;
    
	public function setVersion($version) {
		$this->oversion = $version;
	}

	public function setPartOfBundle($partofbundle) {
		$this->partofbundle = $partofbundle;
	}
	
	public function setHash($md5_hash) {
		$this->md5_hash = $md5_hash;
	}

	function importOldRevision() {
		// check revision here
		echo "version: ".$this->oversion."\n";
		echo "version: ".$this->partofbundle."\n";
		echo "version: ".$this->md5_hash."\n";
	}
}

?>