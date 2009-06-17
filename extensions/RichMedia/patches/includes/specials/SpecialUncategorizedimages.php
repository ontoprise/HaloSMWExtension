<?php
/**
 * Special page lists images which haven't been categorised
 *
 * @file
 * @ingroup SpecialPage
 * @author Rob Church <robchur@gmail.com>
 */

/**
 * @ingroup SpecialPage
 */
class UncategorizedImagesPage extends ImageQueryPage {

	function getName() {
		return 'Uncategorizedimages';
	}

	function sortDescending() {
		return false;
	}

	function isExpensive() {
		return true;
	}

	function isSyndicated() {
		return false;
	}

	function getSQL() {
		$dbr = wfGetDB( DB_SLAVE );
		list( $page, $categorylinks ) = $dbr->tableNamesN( 'page', 'categorylinks' );
		/*op-patch|BL|2009-06-17|RichMedia|AdditionalNamespaceCheck|start*/
		// NS_IMAGE is not the only Namespace now, so get them all
		// content was:
		// $ns = NS_IMAGE;
		//	return "SELECT 'Uncategorizedimages' AS type, page_namespace AS namespace,
		//			page_title AS title, page_title AS value
		//			FROM {$page} LEFT JOIN {$categorylinks} ON page_id = cl_from
		//			WHERE cl_from IS NULL AND page_namespace = {$ns} AND page_is_redirect = 0";
		$ns = NS_IMAGE;
		$ns2 = NS_DOCUMENT;
		$ns3 = NS_PDF;
		$ns4 = NS_AUDIO;
		$ns5 = NS_VIDEO;
		
		return "SELECT 'Uncategorizedimages' AS type, page_namespace AS namespace,
				page_title AS title, page_title AS value
				FROM {$page} LEFT JOIN {$categorylinks} ON page_id = cl_from
				WHERE cl_from IS NULL AND ( page_namespace = {$ns} OR page_namespace = {$ns2}
				OR page_namespace = {$ns3} OR page_namespace = {$ns4} OR page_namespace = {$ns5} )
				AND page_is_redirect = 0";
		/*op-patch|BL|2009-06-17|end*/
	}
}

function wfSpecialUncategorizedimages() {
	$uip = new UncategorizedImagesPage();
	list( $limit, $offset ) = wfCheckLimits();
	return $uip->doQuery( $offset, $limit );
}
