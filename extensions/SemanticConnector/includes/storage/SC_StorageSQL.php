<?php
/**
 * This file provides the access to the MediaWiki SQL database tables that are
 * used by the semantic connector extension.
 *
 * @author Ning Hu
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgConnectorIP;
require_once $smwgConnectorIP . '/includes/SC_DBHelper.php';

/**
 * This class encapsulates all methods that care about the database tables of
 * the web service extension.
 *
 */
class ConnectorStorageSQL {

	public function setup($verbose) {

		$db =& wfGetDB( DB_MASTER );

		SCDBHelper::reportProgress("Setting up Connector database ...\n",$verbose);
		extract( $db->tableNames('smw_sc_mapping', 'smw_sc_mapfield', 'smw_sc_pageformset') );

		// page_id, monitored page id

		SCDBHelper::setupTable($smw_sc_mapping,
		array('map_id' => 'INT(8) UNSIGNED NOT NULL KEY AUTO_INCREMENT',
				'function' => 'BLOB',
				'enable'   => 'TINYINT(1) NOT NULL default \'1\''), $db, $verbose);

		SCDBHelper::setupTable($smw_sc_mapfield,
		array('id' => 'INT(8) UNSIGNED NOT NULL KEY AUTO_INCREMENT',
				'map_id'    => 'INT(8) UNSIGNED NOT NULL',
				'idx'         => 'INT(8) UNSIGNED NOT NULL default \'0\'',
				'form' => 'VARCHAR(255) binary NOT NULL',
				'template'    => 'VARCHAR(255) binary NOT NULL', 
				'field' => 'VARCHAR(255) binary NOT NULL'), $db, $verbose);
		SCDBHelper::setupIndex($smw_sc_mapfield, array('form', 'template', 'map_id'), $db);

		SCDBHelper::setupTable($smw_sc_pageformset,
		array('page_id' => 'INT(8) UNSIGNED NOT NULL',
				'form' => 'VARCHAR(255) binary NOT NULL',
				'history'    => 'INT(8) NOT NULL default \'0\''), $db, $verbose);
		SCDBHelper::setupIndex($smw_sc_pageformset, array('page_id'), $db);

		SCDBHelper::reportProgress("... done!\n",$verbose);
	}
	
	public function deleteDatabaseTables() {
		$db =& wfGetDB( DB_MASTER );
		$verbose = true;
		SCDBHelper::reportProgress("Dropping Semantic Connector tables ...\n",$verbose);

		$tables = array('smw_sc_mapping', 'smw_sc_mapfield', 'smw_sc_pageformset');
		foreach ($tables as $table) {
			$name = $db->tableName($table);
			$db->query('DROP TABLE' . ($wgDBtype=='postgres'?'':' IF EXISTS'). $name, 'ConnectorStorageSQL::drop');
			SCDBHelper::reportProgress(" ... dropped table $name.\n", $verbose);
		}
		
		SCDBHelper::reportProgress("   ... done!\n",$verbose);
	}
	
	public function getAllForms() {
		$fname = 'Connector::getAllForms';
		wfProfileIn( $fname );

		$result = array();

		$db = wfGetDB( DB_SLAVE );
		$res = $db->select( $db->tableName('page'),
		array('page_title'),
		array('page_namespace'=>SF_NS_FORM, 'page_is_redirect'=>0), $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = str_replace('_', ' ', $row->page_title);
			}
		}
		$db->freeResult($res);
		wfProfileOut( $fname );
		return $result;
	}

	public function removeOldMappingData( $form_name ) {
		$fname = 'Connector::removeOldMappingData';
		wfProfileIn( $fname );

		$db =& wfGetDB( DB_SLAVE );
		$ms = array();
		$res = $db->select( $db->tableName('smw_sc_mapfield'),
		array('map_id'),
		array('form'=>$form_name), $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$ms[] = $row->map_id;
			}
		}
		$db->freeResult($res);

		if(count($ms) > 0) {
			$dbw =& wfGetDB( DB_MASTER );
			$dbw->delete( $db->tableName('smw_sc_mapfield'), array('map_id'=>$ms), $fname);
			$dbw->delete( $db->tableName('smw_sc_mapping'), array('map_id'=>$ms), $fname);
		}

		wfProfileOut( $fname );
	}

	public function saveMappingDataSameAs( $src, $map ) {
		$fname = 'Connector::saveMappingDataSameAs';
		wfProfileIn( $fname );

		$sftf = explode('.', $src);
		$mftf = explode('.', $map);

		$db =& wfGetDB( DB_SLAVE);
		$smw_sc_mapfield = $db->tableName('smw_sc_mapfield');
		$res = $db->query("SELECT COUNT(*) cnt FROM $smw_sc_mapfield WHERE
		form = '" . $sftf[0] . "' AND
			template = '" . $sftf[1] . "' AND
			field = '" . $sftf[2] . "' AND
		map_id IN (
		SELECT map_id FROM $smw_sc_mapfield WHERE
		form = '" . $mftf[0] . "' AND
					template='" . $mftf[1] . "' AND
					field='" . $mftf[2] . "'
			)", $fname);
		if($db->numRows( $res ) > 0) {
			if($db->fetchObject($res)->cnt > 0) {
				wfProfileOut( $fname );
				return;
			}
		}
		$db->freeResult($res);

		$dbw =& wfGetDB( DB_MASTER );
		$map_id = $dbw->nextSequenceValue('sc_mapping_id_seq');
		if($dbw->insert( 'smw_sc_mapping', array(
					'map_id' => $map_id,
					'function' => '=',
					'enable' => 1), $fname))
		{
			$map_id = $dbw->insertId();
		} else {
			wfProfileOut( $fname );
			return;
		}
		$id = $dbw->nextSequenceValue('sc_mapfield_id_seq');
		$dbw->insert( 'smw_sc_mapfield', array(
					'id' => $id,
					'map_id' => $map_id,
					'idx' => 0,
					'form' => $sftf[0],
					'template' => $sftf[1],
					'field' => $sftf[2]), $fname);
		$id = $dbw->nextSequenceValue('sc_mapfield_id_seq');
		$dbw->insert( 'smw_sc_mapfield', array(
					'id' => $id,
					'map_id' => $map_id,
					'idx' => 0,
					'form' => $mftf[0],
					'template' => $mftf[1],
					'field' => $mftf[2]), $fname);

		wfProfileOut( $fname );
	}

	/*
	 * get all possible form.template.fields mapping data
	 * return array(
	 *   array('src' => source form.template.field, 'map' => mapped form.template.fields )
	 * )
	 */
	public function getMappingData( $form_name, $mapped_form_name = NULL ) {
		$fname = 'Connector::getMappingData';
		wfProfileIn( $fname );

		$db =& wfGetDB( DB_SLAVE );
		$ms = array();
		$res = $db->select( $db->tableName('smw_sc_mapfield'),
		array('map_id', 'template', 'field'),
		array('form'=>$form_name), $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$ms[$row->map_id] = $form_name . '.' . $row->template . '.' . $row->field;
			}
		}
		$db->freeResult($res);

		$result = array();
		extract( $db->tableNames('smw_sc_mapfield', 'smw_sc_mapping') );
		// get 'same as' mapping data
		foreach($ms as $mid => $src) {
			$res = $db->query("SELECT form, template, field FROM $smw_sc_mapfield f LEFT JOIN $smw_sc_mapping m
			ON f.map_id = m.map_id
			WHERE m.map_id = $mid AND
			m.function = '=' AND
			m.enable = 1 AND ".
			($mapped_form_name == NULL ?
			("f.form != '" . mysql_real_escape_string($form_name) . "'") :
			("f.form = '" . mysql_real_escape_string($mapped_form_name) . "'")
			), $fname);
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = array('src' => $src, 'map' => $row->form . '.' . $row->template . '.' . $row->field);
				}
			}
			$db->freeResult($res);
		}

		wfProfileOut( $fname );
		return $result;
	}
	public function getMappingItem( $src, $mapped_form_name ) {
		$fname = 'Connector::getMappingItem';
		wfProfileIn( $fname );

		$db =& wfGetDB( DB_SLAVE );
		$ms = array();
		$ftf = explode('.', $src, 3);
		$res = $db->select( $db->tableName('smw_sc_mapfield'),
		array('map_id'),
		array('form'=>$ftf[0], 'template'=>$ftf[1], 'field'=>$ftf[2]), $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$ms[$row->map_id] = $src;
			}
		}
		$db->freeResult($res);

		$result = array();
		extract( $db->tableNames('smw_sc_mapfield', 'smw_sc_mapping') );
		// get 'same as' mapping data
		foreach($ms as $mid => $src) {
			$res = $db->query("SELECT form, template, field FROM $smw_sc_mapfield f LEFT JOIN $smw_sc_mapping m
			ON f.map_id = m.map_id
			WHERE m.map_id = $mid AND
			m.function = '=' AND
			m.enable = 1 AND
			f.form = '" . mysql_real_escape_string($mapped_form_name) . "'"
			, $fname);
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = array('src' => $src, 'map' => $row->form . '.' . $row->template . '.' . $row->field);
				}
			}
			$db->freeResult($res);
		}

		wfProfileOut( $fname );
		return $result;
	}
	/*
	 * get possible template.fields mapping data from specified form to target forms
	 * templates and fields only
	 * return array( mapped template => array( mapped field => source template.field ) )
	 */
	public function getMappingTFs( $form_name, $target_forms ) {
		$fname = 'Connector::getMappingTFs';
		wfProfileIn( $fname );
		if($form_name === NULL || count($target_forms) == 0) {
			wfProfileOut( $fname );
			return array();
		}

		$db =& wfGetDB( DB_SLAVE );
		$ms = array();
		$res = $db->select( $db->tableName('smw_sc_mapfield'),
		array('map_id', 'template', 'field'),
		array('form'=>$form_name), $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$ms[$row->map_id] = array('template' => $row->template, 'field' => $row->field);
			}
		}
		$db->freeResult($res);

		$fs = array();
		foreach($target_forms as $f) {
			$fs[] = mysql_real_escape_string($f);
		}

		$result = array();
		extract( $db->tableNames('smw_sc_mapfield', 'smw_sc_mapping') );
		// get 'same as' mapping data
		foreach($ms as $mid => $src) {
			$res = $db->query("SELECT form, template, field FROM $smw_sc_mapfield f LEFT JOIN $smw_sc_mapping m
			ON f.map_id = m.map_id
			WHERE m.map_id = $mid AND
			m.function = '=' AND
			m.enable = 1 AND
			f.form IN ('" . implode("','", $fs) . "')", $fname);
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					// cannot be multiple, otherwise, conflict. add check? tbd!
					if($row->template == $src['template']) continue;
					$result[$row->template][$row->field] = $src['template'] . '.' . $src['field'];
				}
			}
			$db->freeResult($res);
		}

		wfProfileOut( $fname );
		return $result;
	}

	public function getMappingForms( $form_names ) {
		$fname = 'Connector::getMappingForms';
		wfProfileIn( $fname );
		if(count($form_names) == 0) {
			wfProfileOut( $fname );
			return $form_names;
		}

		$db =& wfGetDB( DB_SLAVE );
		$ms = array();
		$res = $db->select( $db->tableName('smw_sc_mapfield'),
		array('map_id'),
		array('form'=>$form_names), $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$ms[] = $row->map_id;
			}
		}
		$db->freeResult($res);

		$result = $form_names;
		if(count($ms) > 0) {
			extract( $db->tableNames('smw_sc_mapfield', 'smw_sc_mapping') );
			$res = $db->query("SELECT form FROM $smw_sc_mapfield f LEFT JOIN $smw_sc_mapping m
			ON f.map_id = m.map_id
			WHERE m.map_id IN (" . implode(',', $ms) . ") AND
				m.enable = 1", $fname);
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = $row->form;
				}
			}
			$db->freeResult($res);
		}

		wfProfileOut( $fname );
		return $result;
	}

	public function getEnabledForms( $page_id ) {
		$fname = 'Connector::getEnabledForms';
		wfProfileIn( $fname );

		$db =& wfGetDB( DB_SLAVE );
		$result = array();
		try{
			$res = $db->select( $db->tableName('smw_sc_pageformset'),
			array('form', 'history'),
			array('page_id'=>$page_id), $fname, array('ORDER BY' => 'history ASC') );
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[$row->form] = $row->history;
				}
			}
			$db->freeResult($res);
		}catch(Exception $e){}

		wfProfileOut( $fname );
		return $result;
	}
	public function getCurrentForm( $page_id ) {
		$fname = 'Connector::getCurrentForm';
		wfProfileIn( $fname );
		$db =& wfGetDB( DB_SLAVE );
		$res = $db->select( $db->tableName('smw_sc_pageformset'),
		array('form'),
		array('page_id'=>$page_id), $fname, array('ORDER BY' => 'history DESC'));
		if($db->numRows( $res ) > 0) {
			$result = $db->fetchObject($res)->form;
		}
		$db->freeResult($res);

		wfProfileOut( $fname );
		return $result;
	}
	public function resetPageForms( $page_id ) {
		$fname = 'Connector::resetPageForms';
		wfProfileIn( $fname );
		$dbw =& wfGetDB( DB_MASTER );

		$dbw->delete( 'smw_sc_pageformset', array('page_id' => $page_id), $fname);

		wfProfileOut( $fname );
	}
	public function saveEnabledForms( $page_id, $current_form, $enabled_forms ) {
		$fname = 'Connector::saveEnabledForms';
		wfProfileIn( $fname );

		$old_enabled_forms = array();
		foreach($this->getEnabledForms( $page_id ) as $form => $history) {
			$old_enabled_forms[] = $form;
		}
		$enabled_forms[] = $current_form;
		$add_forms = array_diff($enabled_forms, $old_enabled_forms);
		$remove_forms = array_diff($old_enabled_forms, $enabled_forms);
		$dbw =& wfGetDB( DB_MASTER );
		foreach($add_forms as $form) {
			$dbw->insert( 'smw_sc_pageformset', array(
						'page_id' => $page_id,
						'form' => $form,
						'history' => 0), $fname);
		}
		foreach($remove_forms as $form) {
			$dbw->delete( 'smw_sc_pageformset', array(
						'page_id' => $page_id,
						'form' => $form), $fname);
		}

		$db =& wfGetDB( DB_SLAVE );
		$max_history = 0;
		$form = $current_form;
		$res = $db->select( $db->tableName('smw_sc_pageformset'),
		array('MAX(history) AS max_history', 'form'),
		array('page_id' => $page_id), $fname, array('GROUP BY' => 'history'));
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$max_history = $row->max_history;
				$form = $row->form;
			}
		}
		$db->freeResult($res);

		if( $max_history == 0 || $form != $current_form) {
			$max_history++;
			$dbw->update( 'smw_sc_pageformset',
			array('history' => $max_history),
			array('page_id' => $page_id, 'form' => $current_form), $fname);
		}

		wfProfileOut( $fname );
	}

	public function lookupFormMapping( $form_name ) {
		$fname = 'Connector::lookupFormMapping';
		wfProfileIn( $fname );
		$db =& wfGetDB( DB_SLAVE );
		$result = array();

		$smw_sc_mapfield = $db->tableName('smw_sc_mapfield');
		$res = $db->query("SELECT
		m1.form AS form1, m1.template AS template1, m1.field AS field1,
		m2.form AS form2, m2.template AS template2, m2.field AS field2
		FROM $smw_sc_mapfield m1 LEFT JOIN $smw_sc_mapfield m2
		ON m1.map_id = m2.map_id AND m1.id != m2.id
		WHERE m1.form = '" . mysql_real_escape_string($form_name) . "'
			ORDER BY m1.form, m1.template, m1.field, m2.form, m2.template", $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(
					'src' => $row->form1 . '.' . $row->template1 . '.' . $row->field1,
					'map' => $row->form2 . '.' . $row->template2 . '.' . $row->field2,
				);
			}
		}
		$db->freeResult($res);

		wfProfileOut( $fname );
		return $result;
	}

	public function lookupTemplateMapping( $template_name ) {
		$fname = 'Connector::lookupTemplateMapping';
		wfProfileIn( $fname );
		$db =& wfGetDB( DB_SLAVE );
		$result = array();

		$smw_sc_mapfield = $db->tableName('smw_sc_mapfield');
		$res = $db->query("SELECT
		m1.form AS form1, m1.template AS template1, m1.field AS field1,
		m2.form AS form2, m2.template AS template2, m2.field AS field2
		FROM $smw_sc_mapfield m1 LEFT JOIN $smw_sc_mapfield m2
		ON m1.map_id = m2.map_id AND m1.id != m2.id
		WHERE m1.template = '" . mysql_real_escape_string($template_name) . "'
			ORDER BY m1.form, m1.template, m1.field, m2.form, m2.template", $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(
					'src' => $row->form1 . '.' . $row->template1 . '.' . $row->field1,
					'map' => $row->form2 . '.' . $row->template2 . '.' . $row->field2,
				);
			}
		}
		$db->freeResult($res);

		wfProfileOut( $fname );
		return $result;
	}

	public function lookupFieldMapping( $template_name, $field_name ) {
		$fname = 'Connector::lookupFieldMapping';
		wfProfileIn( $fname );
		$db =& wfGetDB( DB_SLAVE );
		$result = array();

		$smw_sc_mapfield = $db->tableName('smw_sc_mapfield');
		$res = $db->query("SELECT
		m1.form AS form1, m1.template AS template1, m1.field AS field1,
		m2.form AS form2, m2.template AS template2, m2.field AS field2
		FROM $smw_sc_mapfield m1 LEFT JOIN $smw_sc_mapfield m2
		ON m1.map_id = m2.map_id AND m1.id != m2.id
		WHERE m1.template = '" . mysql_real_escape_string($template_name) . "' AND
				m1.field = '" . mysql_real_escape_string($field_name) . "'
			ORDER BY m1.form, m1.template, m1.field, m2.form, m2.template", $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(
					'src' => $row->form1 . '.' . $row->template1 . '.' . $row->field1,
					'map' => $row->form2 . '.' . $row->template2 . '.' . $row->field2,
				);
			}
		}
		$db->freeResult($res);

		wfProfileOut( $fname );
		return $result;
	}

	public function lookupMappedForm( $form_name ) {
		$fname = 'Connector::lookupMappedForm';
		wfProfileIn( $fname );
		$db =& wfGetDB( DB_SLAVE );
		$result = array();

		$smw_sc_mapfield = $db->tableName('smw_sc_mapfield');
		$res = $db->query("SELECT DISTINCT form FROM $smw_sc_mapfield WHERE
		map_id IN (
		SELECT map_id FROM $smw_sc_mapfield WHERE
		form='" . mysql_real_escape_string($form_name) . "'
				)", $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				if($row->form !== $form_name)
				$result[] = $row->form;
			}
		}
		$db->freeResult($res);

		wfProfileOut( $fname );
		return $result;
	}
}

?>