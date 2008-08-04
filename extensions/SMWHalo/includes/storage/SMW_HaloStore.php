<?php
/**
 * Modified version of SMW's old SMWSQLStore that incorporates some
 * modifications for Halo.
 *
 * @author Markus Krötzsch
 */

/**
 * Storage access class for using the standard MediaWiki SQL database
 * for keeping semantic data.
 */
class SMWHaloStore extends SMWSQLStore {

	/**
	 * Modified to store ratings.
	 */
	function updateData(SMWSemanticData $data, $newpage) {
		wfProfileIn("SMWHaloStore::updateData (SMW)");
		$dbkey = $data->getSubject()->getDBkey();
		$annotations = smwfGetSemanticStore()->getRatedAnnotations($dbkey);
		parent::updateData($data, $newpage);
		if ($annotations !== NULL) {
			foreach($annotations as $pa) {
				smwfGetSemanticStore()->rateAnnotation($dbkey, $pa[0], $pa[1], $pa[2] );
			}
		}
		wfProfileOut("SMWHaloStore::updateData (SMW)");
	}

	/**
	 * Modified to include "rating" column in relations and attributes table.
	 */
	function setup($verbose = true) {
		global $wgDBtype;
		$this->reportProgress("Setting up standard database configuration for SMW+Halo ...\n\n",$verbose);

		if ($wgDBtype === 'postgres') {
			$this->reportProgress("For Postgres, please import the file SMW_Postgres_Schema.sql manually\n",$verbose);
			return;
		}

		$db =& wfGetDB( DB_MASTER );

		extract( $db->tableNames('smw_relations', 'smw_attributes', 'smw_longstrings', 'smw_specialprops', 'smw_subprops', 'smw_nary', 'smw_nary_attributes', 'smw_nary_longstrings', 'smw_nary_relations') );

		// create relation table
		$this->setupTable($smw_relations,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'subject_namespace' => 'INT(11) NOT NULL',
		                    'subject_title'     => 'VARCHAR(255) binary NOT NULL',
		                    'relation_title'    => 'VARCHAR(255) binary NOT NULL',
		                    'object_namespace'  => 'INT(11) NOT NULL',
		                    'object_title'      => 'VARCHAR(255) binary NOT NULL',
		                    'object_id'         => 'INT(8) UNSIGNED',
		                    'rating'            => 'INT(8)'), $db, $verbose);
		$this->setupIndex($smw_relations, array('subject_id','relation_title','object_title,object_namespace','object_id'), $db);

		// create attribute table
		$this->setupTable($smw_attributes,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'subject_namespace' => 'INT(11) NOT NULL',
		                    'subject_title'     => 'VARCHAR(255) binary NOT NULL',
		                    'attribute_title'   => 'VARCHAR(255) binary NOT NULL',
		                    'value_unit'        => 'VARCHAR(63) binary',
		                    'value_xsd'         => 'VARCHAR(255) binary NOT NULL',
		                    'value_num'         => 'DOUBLE',
		                    'rating'            => 'INT(8)'), $db, $verbose);
		$this->setupIndex($smw_attributes, array('subject_id','attribute_title','value_num','value_xsd'), $db);

		// create table for long string attributes
		$this->setupTable($smw_longstrings,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'subject_namespace' => 'INT(11) NOT NULL',
		                    'subject_title'     => 'VARCHAR(255) binary NOT NULL',
		                    'attribute_title'   => 'VARCHAR(255) binary NOT NULL',
		                    'value_blob'        => 'MEDIUMBLOB'), $db, $verbose);
		$this->setupIndex($smw_longstrings, array('subject_id','attribute_title'), $db);

		// set up according tables for nary properties
		$this->setupTable($smw_nary,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'subject_namespace' => 'INT(11) NOT NULL',
		                    'subject_title'     => 'VARCHAR(255) binary NOT NULL',
		                    'attribute_title'   => 'VARCHAR(255) binary NOT NULL',
		                    'nary_key'          => 'INT(8) UNSIGNED NOT NULL'), $db, $verbose);
		$this->setupIndex($smw_nary, array('subject_id','attribute_title','subject_id,nary_key'), $db);
		$this->setupTable($smw_nary_relations,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'nary_key'          => 'INT(8) UNSIGNED NOT NULL',
		                    'nary_pos'          => 'INT(8) UNSIGNED NOT NULL',
		                    'object_namespace'  => 'INT(11) NOT NULL',
		                    'object_title'      => 'VARCHAR(255) binary NOT NULL',
		                    'object_id'         => 'INT(8) UNSIGNED'), $db, $verbose);
		$this->setupIndex($smw_nary_relations, array('subject_id,nary_key','object_title,object_namespace','object_id'), $db);
		$this->setupTable($smw_nary_attributes,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'nary_key'          => 'INT(8) UNSIGNED NOT NULL',
		                    'nary_pos'          => 'INT(8) UNSIGNED NOT NULL',
		                    'value_unit'        => 'VARCHAR(63) binary',
		                    'value_xsd'         => 'VARCHAR(255) binary NOT NULL',
		                    'value_num'         => 'DOUBLE'), $db, $verbose);
		$this->setupIndex($smw_nary_attributes, array('subject_id,nary_key','value_num','value_xsd'), $db);
		$this->setupTable($smw_nary_longstrings,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'nary_key'          => 'INT(8) UNSIGNED NOT NULL',
		                    'nary_pos'          => 'INT(8) UNSIGNED NOT NULL',
		                    'value_blob'        => 'MEDIUMBLOB'), $db, $verbose);
		$this->setupIndex($smw_nary_longstrings, array('subject_id,nary_key'), $db);

		// create table for special properties
		$this->setupTable($smw_specialprops,
		              array('subject_id'        => 'INT(8) UNSIGNED NOT NULL',
		                    'property_id'       => 'SMALLINT(6) NOT NULL',
		                    'value_string'      => 'VARCHAR(255) binary NOT NULL'), $db, $verbose);
		$this->setupIndex($smw_specialprops, array('subject_id', 'property_id', 'subject_id,property_id'), $db);

		// create table for subproperty relationships
		$this->setupTable($smw_subprops,
		              array('subject_title'     => 'VARCHAR(255) binary NOT NULL',
		                    'object_title'      => 'VARCHAR(255) binary NOT NULL'), $db, $verbose);
		$this->setupIndex($smw_subprops, array('subject_title', 'object_title'), $db);

		$this->reportProgress("Database initialised successfully.\n",$verbose);
		return true;
	}


}