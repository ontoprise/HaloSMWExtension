<?php
/**
 * This file provides the access to the MediaWiki SQL database tables that are
 * used by the NotifyMe extension.
 *
 * @author dch
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $srfpgIP;
require_once $srfpgIP . '/includes/SRF_DBHelper.php';

/**
 * This class encapsulates all methods that care about the database tables of
 * the NotifyMe extension.
 *
 */
class SRFStorageSQL {

	public function setup($verbose) {

		$db =& wfGetDB( DB_MASTER );

		SRFDBHelper::reportProgress("Setting up Srf database ...\n",$verbose);

		extract( $db->tableNames('srf_geo') );

		// page_id, monitored page id
		SRFDBHelper::setupTable($srf_geo,
		array('location'    => 'VARCHAR(255) binary NOT NULL',
				'latlng'      => 'VARCHAR(255) binary NOT NULL'), $db, $verbose);
		SRFDBHelper::setupIndex($srf_geo, array('location'), $db);

		SRFDBHelper::reportProgress("... done!\n",$verbose);

	}

	public function addGeo($location, $latlng) {
		$fname = 'Srf::addGeo';
		wfProfileIn( $fname );

		if($this->lookupLatLng($location) === NULL && $latlng != '') {
			$dbw =& wfGetDB( DB_MASTER );
			$dbw->insert( 'srf_geo', array(
						'location' => $location,
						'latlng' => $latlng), $fname);
		}
		wfProfileOut( $fname );
	}

	public function lookupLatLng($location){
		$fname = 'Srf::lookupLatLng';
		wfProfileIn( $fname );

		$result = null;

		$db = wfGetDB( DB_SLAVE );
		$res = $db->select( $db->tableName('srf_geo'), array('latlng'), array('location'=>$location), $fname);
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			$result = $row->latlng;
		}
		$db->freeResult($res);
		wfProfileOut( $fname );
		return $result;
	}

	public function lookupLatLngs($locations){
		$fname = 'Srf::lookupLatLngs';
		wfProfileIn( $fname );

		$result = array();

		$db = wfGetDB( DB_SLAVE );
		$res = $db->select( $db->tableName('srf_geo'), array('location', 'latlng'), array('location'=>$locations), $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array('location' => $row->location, 'latlng'=>$row->latlng);
			}
		}
		$db->freeResult($res);
		wfProfileOut( $fname );
		return $result;
	}
}

?>