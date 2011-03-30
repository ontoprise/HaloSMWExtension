<?php

/**
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
			$values = smwfGetStore()->getPropertyValues($bundleTitle, SMWPropertyValue::makeUserProperty("Ontology URI"));
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
		$bundleTitle = Title::newFromText($bundleID);
		$values = smwfGetStore()->getPropertyValues($bundleTitle, SMWPropertyValue::makeUserProperty("Ontology URI"));
		if (count($values) > 0) {
			$value = reset($values);
			$dbkeys = $value->getDBkeys();
			$ontologyURI = reset($dbkeys);
			return $ontologyURI;
		}
		
		return NULL;
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