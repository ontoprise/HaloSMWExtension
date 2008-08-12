<?php

/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWSQLStore2 {

	/**
	 * Modified to store ratings.
	 */
	function updateData(SMWSemanticData $data, $newpage) {
		wfProfileIn("SMWHaloStore::updateData (SMW)");
		
		$annotations = smwfGetSemanticStore()->getRatedAnnotations($data->getSubject());
		parent::updateData($data, $newpage);
		if ($annotations !== NULL) {
			foreach($annotations as $pa) {
				smwfGetSemanticStore()->rateAnnotation($data->getSubject()->getDBkey(), $pa[0], $pa[1], $pa[2] );
			}
		}
		wfProfileOut("SMWHaloStore::updateData (SMW)");
	}
	
	function setup($verbose = true) {
		global $wgDBtype;
		$this->reportProgress("Setting up standard database configuration for SMW ...\n\n",$verbose);
		if ($wgDBtype === 'postgres') {
			$this->reportProgress("For Postgres, please import the file SMW_Postgres_Schema_2.sql manually\n",$verbose);
			return;
		}
		$db =& wfGetDB( DB_MASTER );
		extract( $db->tableNames('smw_ids','smw_rels2','smw_atts2','smw_text2',
                                 'smw_spec2','smw_subs2','smw_redi2','smw_inst2',
                                 'smw_conc2') );

		$this->setupTable($smw_ids, // internal IDs used in this store
		array('smw_id'        => 'INT(8) UNSIGNED NOT NULL KEY AUTO_INCREMENT',
                            'smw_namespace' => 'INT(11) NOT NULL',
                            'smw_title'     => 'VARCHAR(255) binary NOT NULL',
                            'smw_iw'        => 'CHAR(32)',
                            'smw_sortkey'   => 'VARCHAR(255) binary NOT NULL'
                            ), $db, $verbose);
                            $this->setupIndex($smw_ids, array('smw_id','smw_title,smw_namespace,smw_iw', 'smw_sortkey'), $db);

                            $this->setupTable($smw_redi2, // fast redirect resolution
                            array('s_title'     => 'VARCHAR(255) binary NOT NULL',
                            's_namespace' => 'INT(11) NOT NULL',
                            'o_id'        => 'INT(8) UNSIGNED NOT NULL',), $db, $verbose);
                            $this->setupIndex($smw_redi2, array('s_title,s_namespace','o_id'), $db);

                            $this->setupTable($smw_rels2, // properties with other pages as values ("relations")
                            array('s_id' => 'INT(8) UNSIGNED NOT NULL',
                            'p_id' => 'INT(8) UNSIGNED NOT NULL',
                            'o_id' => 'INT(8) UNSIGNED NOT NULL',
                             'rating'            => 'INT(8)'), $db, $verbose);
                            $this->setupIndex($smw_rels2, array('s_id','p_id','o_id'), $db);

                            $this->setupTable($smw_atts2, // most standard properties ("attributes")
                            array('s_id'        => 'INT(8) UNSIGNED NOT NULL',
                            'p_id'        => 'INT(8) UNSIGNED NOT NULL',
                            'value_unit'        => 'VARCHAR(63) binary',
                            'value_xsd'         => 'VARCHAR(255) binary NOT NULL',
                            'value_num'         => 'DOUBLE',
                            'rating'            => 'INT(8)'), $db, $verbose);
                            $this->setupIndex($smw_atts2, array('s_id','p_id','value_num','value_xsd'), $db);

                            $this->setupTable($smw_text2, // properties with long strings as values
                            array('s_id'        => 'INT(8) UNSIGNED NOT NULL',
                            'p_id'        => 'INT(8) UNSIGNED NOT NULL',
                            'value_blob'        => 'MEDIUMBLOB'), $db, $verbose);
                            $this->setupIndex($smw_text2, array('s_id','p_id'), $db);

                            $this->setupTable($smw_spec2, // (generic builtin) special properties
                            array('s_id'        => 'INT(8) UNSIGNED NOT NULL',
                            'sp_id'       => 'SMALLINT(6) NOT NULL',
                            'value_string'      => 'VARCHAR(255) binary NOT NULL'), $db, $verbose);
                            $this->setupIndex($smw_spec2, array('s_id', 'sp_id', 's_id,sp_id'), $db);

                            $this->setupTable($smw_subs2, // subproperty/subclass relationships
                            array('s_id'        => 'INT(8) UNSIGNED NOT NULL',
                            'o_id'        => 'INT(8) UNSIGNED NOT NULL',), $db, $verbose);
                            $this->setupIndex($smw_subs2, array('s_id', 'o_id'), $db);

                            $this->setupTable($smw_inst2, // class instances (s_id the element, o_id the class)
                            array('s_id'        => 'INT(8) UNSIGNED NOT NULL',
                            'o_id'        => 'INT(8) UNSIGNED NOT NULL',), $db, $verbose);
                            $this->setupIndex($smw_inst2, array('s_id', 'o_id'), $db);

                            $this->setupTable($smw_conc2, // concept descriptions
                            array('s_id'        => 'INT(8) UNSIGNED NOT NULL KEY',
                            'concept_txt' => 'MEDIUMBLOB',
                            'concept_docu'=> 'MEDIUMBLOB'), $db, $verbose);
                            $this->setupIndex($smw_conc2, array('s_id'), $db);

                            $this->reportProgress("Database initialised successfully.\n",$verbose);
                            return true;
	}
}
?>