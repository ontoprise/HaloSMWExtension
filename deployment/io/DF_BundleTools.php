<?php
/*  Copyright 2011, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file
 * @ingroup DFIO
 *
 * Utility methods for handling bundles.
 *
 * @author Kai Kühn / ontoprise / 2011
 *
 */
class DFBundleTools {

	/**
	 * Returns the pages with external artifacts for all bundles and the bundle's ontology URI.
	 *
	 * @param $bundleID
	 *
	 * @return array of tuple ($title, $uri)
	 */
	public static function getExternalArtifacts($bundleName = '+') {
		global $wgLang, $dfgLang;

		$results = array();

		$fileNsText = $wgLang->getNsText(NS_FILE);
		$partOfBundleName = $dfgLang->getLanguageString('df_partofbundle');
		$ontologyURIProperty = $dfgLang->getLanguageString('df_ontologyuri');
		$rawparams = array();
		$rawparams[] = "[[$fileNsText:+]][[$partOfBundleName::".ucfirst($bundleName)."]]";
		$rawparams[] = "?$partOfBundleName";

		SMWQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
		$query  = SMWQueryProcessor::createQuery( $querystring, $params, SMWQueryProcessor::INLINE_QUERY, '', $printouts );
		$res = smwfGetStore()->getQueryResult( $query );

		while ( $row = $res->getNext() ) {

			$field = reset($row);

			$object = $field->getNextObject();
			$fileTitle = $object->getTitle();

			$field = next($row);

			$object = $field->getNextObject();
			$bundleTitle = $object->getTitle();

			$externalGraphs = array();
			$values = smwfGetStore()->getPropertyValues($bundleTitle, SMWPropertyValue::makeUserProperty($ontologyURIProperty));
			if (count($values) > 0) {
				$value = reset($values);
				$dbkeys = $value->getDBkeys();
				$ontologyURI = reset($dbkeys);
				$results[] = array($fileTitle, $ontologyURI);
			}

		}
		return $results;
	}

	public static function getOntologyURI($bundleID) {
		global $dfgLang;
		$ontologyURI = $dfgLang->getLanguageString('df_ontologyuri');
		$bundleTitle = Title::newFromText($bundleID);
		$values = smwfGetStore()->getPropertyValues($bundleTitle, SMWPropertyValue::makeUserProperty($ontologyURI));
		if (count($values) > 0) {
			$value = reset($values);
			$dbkeys = $value->getDBkeys();
			$ontologyURI = reset($dbkeys);
			return $ontologyURI;
		}

		return NULL;
	}

	/**
	 * Returns the ontology prefix (if any). Otherwise an empty string.
	 *
	 * @param $bundleID
	 */
	public static function getOntologyPrefix($bundleID) {
		global $dfgLang;
		$instDirProperty = $dfgLang->getLanguageString('df_instdir');
		$bundleTitle = Title::newFromText($bundleID);
		$values = smwfGetStore()->getPropertyValues($bundleTitle, SMWPropertyValue::makeUserProperty($instDirProperty));
		if (count($values) > 0) {
			$value = reset($values);
			$dbkeys = $value->getDBkeys();
			$installationDir = reset($dbkeys);
			global $IP;

			$handle = @opendir("$IP/$installationDir");
			if (!$handle) {
				return ''; // TODO: should not happen
			}

			while ($entry = readdir($handle) ){
				if ($entry[0] == '.'){
					continue;
				}
				
				$parts = explode(".", $entry);
				if ($parts[count($parts)-1] == 'prefix') {
					$prefix = file_get_contents("$IP/$installationDir/$entry");
					return trim($prefix);
				}
			}

		}

		return '';
	}

	/**
	 * Guesses the Ontology file format.
	 *
	 * @param string $basename Filename
	 */
	public static function guessOntologyFileType($basename) {
		$parts = explode(".", $basename);
		foreach($parts as $p) {
			$p = strtolower($p);
			if ($p == 'obl') return 'OBL';
			if ($p == 'rdf') return 'RDF';
			if ($p == 'owl') return 'RDF';
			if ($p == 'ntriple') return 'NTRIPLE';
			if ($p == 'ntriples') return 'NTRIPLE';
			if ($p == 'nt') return 'NTRIPLE';
			if ($p == 'n3') return 'N3';
			if ($p == 'turtle') return 'TURTLE';
			if ($p == 'ttl') return 'TURTLE';
		}
		return 'OBL'; // assume ObjectLogic per default.
	}
}