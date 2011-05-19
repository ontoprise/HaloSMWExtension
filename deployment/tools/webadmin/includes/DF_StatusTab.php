<?php
require_once ( $mwrootDir.'/deployment/tools/smwadmin/DF_PackageRepository.php' );

class DFStatusTab {

	/**
	 * Status tab
	 *
	 */
	public function __construct() {

	}

	public function getHTML() {
		global $mwrootDir;
		$html = "";
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		$html .= "<table>";
		$html .= "<th>";
		$html .= "Extension";
		$html .= "</th>";
		$html .= "<th>";
		$html .= "Description";
		$html .= "</th>";
		$html .= "<th>";
		$html .= "Action";
		$html .= "</th>";
		foreach($localPackages as $id => $p) {
			$html .= "<tr>";
			$html .= "<td>";
			$html .= $id;
			$html .= "</td>";
			$html .= "<td>";
			$html .= $p->getDescription();
			$html .= "</td>";
			$html .= "<td>";
			$html .= "Do";
			$html .= "</td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}


}