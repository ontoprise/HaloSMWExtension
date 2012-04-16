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
 * Utility methods for handling bundles.
 *
 * @author Kai Kühn
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
		$rawparams[] = "source=wiki";

		SMWQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
		SMWQueryProcessor::addThisPrintout(  $printouts, $params );
		$params = SMWQueryProcessor::getProcessedParams($params, $printouts);
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
			$values = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($bundleTitle), SMWDIProperty::newFromUserLabel($ontologyURIProperty));
			if (count($values) > 0) {
				$value = reset($values);
				$ontologyURI = $value->getURI();
				$results[] = array($fileTitle, $ontologyURI);
			}

		}
		return $results;
	}

	public static function getOntologyURI($bundleID) {
		global $dfgLang;
		$ontologyURI = $dfgLang->getLanguageString('df_ontologyuri');
		$bundleTitle = Title::newFromText($bundleID);
		$values = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($bundleTitle), SMWDIProperty::newFromUserLabel($ontologyURI));
		if (count($values) > 0) {
			$value = reset($values);
			$ontologyURI = $value->getURI();
			return $ontologyURI;
		}

		return NULL;
	}

	/**
	 * Returns all pages of a bundle
	 *
	 * @param string $bundleID
	 *
	 * @return Title[]
	 */
	public static function getBundlePages($bundleID) {
		global $dfgLang;
		$partOfBundle = $dfgLang->getLanguageString('df_partofbundle');
		$titles = array();
		$bundleTitle = Title::newFromText($bundleID);
		$values = smwfGetStore()->getPropertySubjects(SMWDIProperty::newFromUserLabel($partOfBundle), SMWDIWikiPage::newFromTitle($bundleTitle));
		foreach($values as $title) {
			$titles[] = $title->getTitle();
		}
		return $titles;
	}

	/**
	 * Returns bundleID of given page title.
	 *
	 * Note: If more than one bundle ID exist, the result is not defined.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public static function getBundleID($title) {
		global $dfgLang;
		$partOfBundle = $dfgLang->getLanguageString('df_partofbundle');
		$bundleTitle = $title;
		$values = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($bundleTitle), SMWDIProperty::newFromUserLabel($partOfBundle));
		if (count($values) > 0) {
			$value = reset($values);
			$bundleID = $value->getTitle()->getDBkey();
			return $bundleID;
		}
		return NULL;
	}



	/**
	 * Returns prefix/namespace URI mappings.
	 *
	 *
	 * @return array($prefix => $namespace URI)
	 */
	public static function getRegisteredPrefixes() {
		$dbw = wfGetDB( DB_SLAVE );
		global $dfgLang;
		$nsMappingPage = $dfgLang->getLanguageString('df_namespace_mappings_page');
		$nsMappingPageTitle = Title::newFromText($nsMappingPage, NS_MEDIAWIKI);
		if (!$nsMappingPageTitle->exists()) {
			return array();
		}
		$rev = Revision::loadFromTitle( $dbw, $nsMappingPageTitle );
		$text = $rev->getRawText();

		return self::parseRegisteredPrefixes($text);
	}

	/**
	 * Parses prefix/namespace URI mappings from wiki text.
	 *
	 * @param string $wikiText
	 *
	 * @return array($prefix => $namespace URI)
	 */
	public static function parseRegisteredPrefixes($text) {
		$lines = explode("\n", $text);
		$results = array();
		foreach($lines as $l) {
			if (strpos($l, ":") !== false) {
				$prefix = trim(substr($l, 0, strpos($l, ":")));
				if (substr($prefix,0,1) == '*') {
					$prefix = substr($prefix, 1);
					$uri = trim(substr($l, strpos($l, ":")+1));
					$results[$prefix] = $uri;
				}
			}
		}
		return $results;
	}

	/**
	 * Stores prefix/namespace URI mappings.
	 *
	 * @param array($prefix => $namespace URI)
	 *
	 */
	public static function storeRegisteredPrefixes($namespaceMappings, $bundleID) {
		$dbw = wfGetDB( DB_SLAVE );
		global $dfgLang;

		// read content of Mediawiki:NamespaceMappings
		$nsMappingPage = $dfgLang->getLanguageString('df_namespace_mappings_page');
		$nsMappingPageTitle = Title::newFromText($nsMappingPage, NS_MEDIAWIKI);
		$a = new Article($nsMappingPageTitle);
		$text = $a->getRawText();

		// create mappings
		$mappingsText = "";
		foreach($namespaceMappings as $prefix => $uri) {
			$mappingsText .= "\n*$prefix : $uri";
		}

		// merge mapping into the bundle section
		$om = new OntologyMerger();
		$content = $om->getBundleContent($bundleID, $text);
		if (!is_null($content)) {
			$text = $om->removeBundle($bundleID, $text);
		} else {
			$content = "";
		}
		$text = $om->addBundle($bundleID, $text, $content.$mappingsText);

		// save page
		$article = new Article($nsMappingPageTitle);
		$article->doEdit($text, "auto-generated namespace mappings");
	}

	/**
	 * Checks if there are double prefixes.
	 *
	 * @param string $text
	 *
	 * @return boolean False if there are doubles.
	 */
	public static function checkPrefixList($text) {
		$lines = explode("\n", $text);
		$prefixes = array();
		$uris = array();
		foreach($lines as $l) {
			if (strpos($l, ":") !== false) {
				$prefix = trim(substr($l, 0, strpos($l, ":")));
				if (substr($prefix,0,1) == '*') {
					$prefix = substr($prefix, 1);
					$uri = trim(substr($l, strpos($l, ":")+1));
					if (array_key_exists($prefix,$prefixes) && $prefixes[$prefix] != $uri) {
						return false;
					}
					if (array_key_exists($uri,$uris) && $uris[$uri] != $prefix) {
						return false;
					}
					$prefixes[$prefix] = $uri;
					$uris[$uri] = $prefix;
				}
			}
		}
		return true;
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

	/**
	 * Checks if $title is part of bundle $bundleID.
	 *
	 * @param Title $title
	 * @param string $bundleID
	 *
	 * @return boolean
	 */
	public static function isPartOfBundle($title, $bundleID) {
		global $dfgLang;
		$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString("df_partofbundle")));
		$bundleID = ucfirst($bundleID);
		$bundleSMWID = smwfGetStore()->getSMWPageID($bundleID, NS_MAIN, "");
		$db =& wfGetDB( DB_SLAVE );
		$smw_ids = $db->tableName('smw_ids');
		$smw_rels2 = $db->tableName('smw_rels2');
		$bundleSql = 'SELECT smw_id FROM '.$smw_ids.' JOIN '.$smw_rels2.' ON s_id = smw_id AND p_id = '.$partOfBundlePropertyID.' AND o_id = '.$bundleSMWID.' WHERE smw_title = '.$db->addQuotes($title->getDBkey()).' AND smw_namespace = '.$title->getNamespace();
		$res = $db->query($bundleSql);
		return $db->numRows( $res ) > 0;
	}

	/**
	 * Removes articles belonging to a bundle. If $removeReferenced == true, it is assumed that everything other than instances of categories of a bundle
	 * and templates used by such is marked with the 'Part of bundle' annotation. Otherwise _everything_ must be marked with 'Part of bundle'.
	 * If $keepStillRequiredTemplates == true, templates which are used by pages other than those of the bundle are kept.
	 *
	 * @param string $ext_id
	 * @param Logger $logger
	 * @param boolean $removeReferenced
	 * @param boolean $keepStillRequiredTemplates
	 */
	public static function deletePagesOfBundle($ext_id, $logger = NULL, $removeTemplates = false, $removeInstances = false, $keepStillRequiredTemplates = true) {
		global $dfgLang;
		global $wgUser;
		global $dfgOut;
		$db =& wfGetDB( DB_MASTER );
		$smw_ids = $db->tableName('smw_ids');
		$smw_rels2 = $db->tableName('smw_rels2');
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$templatelinks = $db->tableName('templatelinks');
		$db->query( 'CREATE TEMPORARY TABLE df_page_of_bundle (id INT(8) NOT NULL)
                    ENGINE=MEMORY', 'SMW::createVirtualTableForPagesOfBundle' );

		$db->query( 'CREATE TEMPORARY TABLE df_page_of_templates_used (title  VARCHAR(255) NOT NULL)
                    ENGINE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );
		$db->query( 'CREATE TEMPORARY TABLE df_page_of_templates_must_persist (title  VARCHAR(255) NOT NULL)
                    ENGINE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );

		$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString("df_partofbundle")));
		$ext_id = strtoupper(substr($ext_id, 0, 1)).substr($ext_id, 1);
		$partOfBundleID = smwfGetStore()->getSMWPageID($ext_id, NS_MAIN, "", "");

		// put all pages belonging to a bundle (all except templates, ie. categories, properties, instances of categories and all other pages denoted by
		// the 'part of bundle' annotation like Forms, Help pages, etc..) in df_page_of_bundle
		$db->query('INSERT INTO df_page_of_bundle (SELECT page_id FROM '.$page.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');
		if ($removeInstances) {
			$db->query('INSERT INTO df_page_of_bundle (SELECT cl_from FROM '.$categorylinks.' JOIN '.$page.' ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');
		}

		// get all templates used on these pages
		$db->query('INSERT INTO df_page_of_templates_used (SELECT tl_title FROM '.$templatelinks.' WHERE tl_from IN (SELECT * FROM df_page_of_bundle))');

		// get all templates which are also used on other pages and must therefore persist
		$db->query('INSERT INTO df_page_of_templates_must_persist (SELECT title FROM df_page_of_templates_used JOIN '.$templatelinks.' ON title = tl_title AND tl_from NOT IN (SELECT * FROM df_page_of_bundle))');

		// delete those from the table of used templates
		if ($keepStillRequiredTemplates) {
			$db->query('DELETE FROM df_page_of_templates_used WHERE title IN (SELECT * FROM df_page_of_templates_must_persist)');
		}

		// select all templates which can be deleted
		$res = $db->query('SELECT DISTINCT title FROM df_page_of_templates_used');

		// DELETE templates
		if(($db->numRows( $res ) > 0) && $removeTemplates) {
			$logger->info("Removing referenced templates");
			$dfgOut->outputln("\t[Removing referenced templates...");
			while($row = $db->fetchObject($res)) {

				$title = Title::newFromText($row->title, NS_TEMPLATE);

				$a = new Article($title);
				$id = $title->getArticleID( GAID_FOR_UPDATE );
				if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
					if( $a->doDeleteArticle( "ontology removed: ".$ext_id ) ) {
						if (!is_null($logger)) $logger->info("Removing page: ".$title->getPrefixedText());
						$dfgOut->outputln("\t\t[Removing page]: ".$title->getPrefixedText()."...");
						wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, "ontology removed: ".$ext_id, $id));
						$dfgOut->output("done.]");
					}
				}

			}
			$dfgOut->outputln("\tdone.]");
		}
		$db->freeResult($res);

		// DELETE pages of bundle
		$res = $db->query('SELECT DISTINCT id FROM df_page_of_bundle');

		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {

				$title = Title::newFromID($row->id);

				if (is_null($title)) {
					if (!is_null($logger)) $logger->error("Invalid page ID: ".$row->id);
					continue;
				}
				// DELETE
				$a = new Article($title);
				$id = $row->id;
				if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
					if( $a->doDeleteArticle( "ontology removed: ".$ext_id ) ) {
						if (!is_null($logger)) $logger->info("Removing page: ".$title->getPrefixedText());
						$dfgOut->outputln("\t[Removing page]: ".$title->getPrefixedText()."...");

						wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, "ontology removed: ".$ext_id, $id));
						$dfgOut->output( "done.]");
					}
				}

			}
		}
		$db->freeResult($res);

		$db->query('DROP TEMPORARY TABLE df_page_of_bundle');
		$db->query('DROP TEMPORARY TABLE df_page_of_templates_used');
		$db->query('DROP TEMPORARY TABLE df_page_of_templates_must_persist');
	}

	/**
	 * Returns all pages which are used by the given bundle *and* at least
	 * one other.
	 *
	 * @param string $ext_id
	 * @param Logger $logger
	 *
	 * @return array of Title
	 */
	public static function getBundleOverlaps($ext_id, $logger = NULL) {
		global $dfgLang;
		global $dfgOut;
		$db =& wfGetDB( DB_SLAVE );
		$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString("df_partofbundle")));
		$ext_id = strtoupper(substr($ext_id, 0, 1)).substr($ext_id, 1);
		$partOfBundleID = smwfGetStore()->getSMWPageID($ext_id, NS_MAIN, "", "");

		$smw_ids = $db->tableName('smw_ids');
		$smw_rels2 = $db->tableName('smw_rels2');

		$result = array();
		$res = $db->query( 'SELECT s.smw_title AS partOfBundleTitle, s.smw_namespace AS partOfBundleNamespace,  GROUP_CONCAT(o.smw_title) AS bundleTitle, GROUP_CONCAT(o.smw_namespace) AS bundleNamespace'.
                            ' FROM '.$smw_ids.' s JOIN '.$smw_rels2.' ON s.smw_id = s_id JOIN '.$smw_ids.' o ON o.smw_id = o_id  WHERE p_id = '.$partOfBundlePropertyID.
                            ' GROUP BY partOfBundleTitle, partOfBundleNamespace HAVING count(o.smw_title) > 1');

		if($db->numRows( $res ) > 0) {

			while($row = $db->fetchObject($res)) {

				$bundleTitles = explode(",", $row->bundleTitle);

				if (in_array(ucfirst($ext_id), $bundleTitles)) {
					$title = Title::newFromText($row->partOfBundleTitle, $row->partOfBundleNamespace);
					$result[] = $title;
				}

			}

		}
		$db->freeResult($res);
		return $result;
	}
	/**
	 * Removes referenced images of a bundle (ie. images which are used on bundle pages). If $keepStillRequiredImages
	 * is true, image used by pages other than those of the bundle are kept.
	 *
	 * @param string $ext_id
	 * @param Logger $logger
	 * @param boolean $keepStillRequiredImages
	 */
	public static function deleteReferencedImagesOfBundle($ext_id, $logger = NULL, $keepStillRequiredImages) {
		global $dfgLang;
		global $wgUser;
		global $dfgOut;

		$db =& wfGetDB( DB_MASTER );
		$smw_ids = $db->tableName('smw_ids');
		$smw_rels2 = $db->tableName('smw_rels2');
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$imagelinks = $db->tableName('imagelinks');
		$db->query( 'CREATE TEMPORARY TABLE df_page_of_bundle (id INT(8) NOT NULL)
                    ENGINE=MEMORY', 'SMW::createVirtualTableForPagesOfBundle' );

		$db->query( 'CREATE TEMPORARY TABLE df_page_of_images_used (title  VARCHAR(255) NOT NULL)
                    ENGINE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );
		$db->query( 'CREATE TEMPORARY TABLE df_page_of_images_must_persist (title  VARCHAR(255) NOT NULL)
                    ENGINE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );

		$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString("df_partofbundle")));
		$ext_id = strtoupper(substr($ext_id, 0, 1)).substr($ext_id, 1);
		$partOfBundleID = smwfGetStore()->getSMWPageID($ext_id, NS_MAIN, "", "");

		// put all pages belonging to a bundle (all except templates, ie. categories, properties, instances of categories and all other pages denoted by
		// the 'part of bundle' annotation like Forms, Help pages, etc..) in df_page_of_bundle
		$db->query('INSERT INTO df_page_of_bundle (SELECT page_id FROM '.$page.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');
		$db->query('INSERT INTO df_page_of_bundle (SELECT cl_from FROM '.$categorylinks.' JOIN '.$page.' ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');

		// get all images used on these pages
		$db->query('INSERT INTO df_page_of_images_used (SELECT il_to FROM '.$imagelinks.' WHERE il_from IN (SELECT * FROM df_page_of_bundle))');

		// get all images which are also used on other pages and must therefore persist
		$db->query('INSERT INTO df_page_of_images_must_persist (SELECT title FROM df_page_of_images_used JOIN '.$imagelinks.' ON title = il_to AND il_from NOT IN (SELECT * FROM df_page_of_bundle))');

		// delete those from the table of used images
		if ($keepStillRequiredImages) {
			$db->query('DELETE FROM df_page_of_images_used WHERE title IN (SELECT * FROM df_page_of_images_must_persist)');
		}

		// select all images which can be deleted
		$res = $db->query('SELECT DISTINCT title FROM df_page_of_images_used');

		// DELETE referenced images
		if($db->numRows( $res ) > 0) {
			$logger->info("Removing referenced images");
			$dfgOut->outputln("\t[Removing referenced images...");
			while($row = $db->fetchObject($res)) {

				$title = Title::newFromText($row->title, NS_FILE);

				$a = new Article($title);
				$id = $title->getArticleID( GAID_FOR_UPDATE );
				if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
					if( $a->doDeleteArticle( "ontology removed: ".$ext_id ) ) {
						if (!is_null($logger)) $logger->info("Removing page: ".$title->getPrefixedText());
						$dfgOut->outputln("\t\t[Removing page]: ".$title->getPrefixedText()."...");
						wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, "ontology removed: ".$ext_id, $id));
						$dfgOut->output( "done.]");
					}
				}

			}
			$dfgOut->outputln("\tdone.]");
		}
		$db->freeResult($res);



		$db->query('DROP TEMPORARY TABLE df_page_of_bundle');
		$db->query('DROP TEMPORARY TABLE df_page_of_images_used');
		$db->query('DROP TEMPORARY TABLE df_page_of_images_must_persist');
	}

	/**
	 * Checks if all bundle properties exist and if they have correct types.
	 *
	 * @param DFPrintoutStream $dfgOut (may be null)
	 */
	public static function checkBundleProperties($dfgOut = NULL) {
		global $dfgLang;

		// indicates if everything is as expected
		$check = true;

		// Property:Dependecy
		$pDependencyTitle = Title::newFromText($dfgLang->getLanguageString('df_dependencies'), SMW_NS_PROPERTY);
		$pDependency = SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_dependencies'));
		$pDependencyType = $pDependency->findPropertyTypeID();

		if ($pDependencyType != '_rec') {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pDependencyTitle->getPrefixedText()."' is not a record type.");
			$check = false;
		}

		$di = smwfGetStore()->getPropertyValues( $pDependency->getDiWikiPage(), SMWDIProperty::newFromUserLabel( '_LIST' ) );
		if (count($di) > 0) {
			$list = reset($di)->getString();
			$typeIDs = explode(";",$list);
			if (count($typeIDs) < 3 || count($typeIDs) > 4) {
				if (!is_null($dfgOut)) $dfgOut->outputln("'".$pDependencyTitle->getPrefixedText()."' wrong number of fields.");
				$check = false;
			}
		} else {
			$check = false;
		}

		// check sub object types of Dependency
		$propertyDiWikiPage = $pDependency->getDiWikiPage();
		$partOfBundlePropertyType=NULL;
		$minversionType=NULL;
		$maxversionType=NULL;
		$optionalType=NULL;
		if ( !is_null( $propertyDiWikiPage ) ) {
			$listDiProperty = new SMWDIProperty( '_LIST' );
			$dataItems = smwfGetStore()->getPropertyValues( $propertyDiWikiPage, $listDiProperty );

			if ( count( $dataItems ) == 1 ) {
				$propertyListValue = new SMWPropertyListValue( '__pls' );
				$propertyListValue->setDataItem( reset($dataItems) );

				if ( $propertyListValue->isValid() ) {
					$subObjectProperties = $propertyListValue->getPropertyDataItems();
					$partOfBundlePropertyType = reset($subObjectProperties)->findPropertyTypeID();
					$minversionType = next($subObjectProperties)->findPropertyTypeID();
					$maxversionType = next($subObjectProperties)->findPropertyTypeID();
					$optionalPart = next($subObjectProperties);
					$optionalType = $optionalPart !== false ? $optionalPart->findPropertyTypeID() : NULL;
				}
			}
		}

		if ($partOfBundlePropertyType != '_str' || $minversionType != '_str' || $maxversionType != '_str') {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pDependencyTitle->getPrefixedText()."' property has wrong field properties. They must have the types (String, String, String [, Boolean]). The last is optional.");
			$check = false;
		}

		if (!is_null($optionalType) && $optionalType != '_boo') {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pDependencyTitle->getPrefixedText()."' property has wrong field properties. The 4th type can be a boolean type or empty.");
			$check = false;
		}

		// Ontology version
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_ontologyversion'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_str");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type String.");
			$check = false;
		}

		// Patchlevel
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_patchlevel'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_num");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type Number.");
			$check = false;
		}

		// Installation dir
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_instdir'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_str");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type String.");
			$check = false;
		}

		// Vendor
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_vendor'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_str");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type String.");
			$check = false;
		}

		// Rationale
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_rationale'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_str");
		$correct |= self::checkPropertyType($pTitle->getText(), "_txt");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type String.");
			$check = false;
		}

		// Maintainer
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_maintainer'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_str");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type String.");
			$check = false;
		}

		// Help URL
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_helpurl'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_uri");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type URL.");
			$check = false;
		}

		// License
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_license'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_wpg");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type Page.");
			$check = false;
		}


		// Ontology URI
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_ontologyuri'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_uri");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type URL.");
			$check = false;
		}

		// Part of bundle URI
		$pTitle = Title::newFromText($dfgLang->getLanguageString('df_partofbundle'), SMW_NS_PROPERTY);
		$correct = self::checkPropertyType($pTitle->getText(), "_wpg");
		if (!$correct) {
			if (!is_null($dfgOut)) $dfgOut->outputln("'".$pTitle->getPrefixedText()."' is not of type Page.");
			$check = false;
		}

		return $check;
	}

	public static function createBundleProperties() {
		global $smwgContLang, $dfgLang;

		$propertyLabels = $smwgContLang->getPropertyLabels();
		$namespaces = $smwgContLang->getNamespaces();
		$datatypeLabels = $smwgContLang->getDatatypeLabels();

		$property = Title::newFromText($dfgLang->getLanguageString('df_minversion'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_maxversion'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_mwextension'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_isoptional'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_boo"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		// Property:Dependecy
		$property = Title::newFromText($dfgLang->getLanguageString('df_dependencies'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_rec"]."]]";
		$text .= "\n\n[[".$propertyLabels['_LIST']."::".
		$dfgLang->getLanguageString('df_mwextension')."; ".$dfgLang->getLanguageString('df_minversion')."; ".$dfgLang->getLanguageString('df_maxversion')."; ".$dfgLang->getLanguageString('df_isoptional')."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}


		$property = Title::newFromText($dfgLang->getLanguageString('df_partofbundle'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_wpg"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_ontologyversion'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_patchlevel'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_num"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_instdir'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_vendor'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_maintainer'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_rationale'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_str"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_helpurl'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_uri"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}

		$property = Title::newFromText($dfgLang->getLanguageString('df_license'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_wpg"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}



		$property = Title::newFromText($dfgLang->getLanguageString('df_ontologyuri'), SMW_NS_PROPERTY);
		$text = "\n\n[[".$propertyLabels['_TYPE']."::".$datatypeLabels["_uri"]."]]";
		$article = new Article($property);
		if ($property->exists()) {
			$article->doEdit($text, "", EDIT_UPDATE | EDIT_FORCE_BOT);
			print "\n ...edited ".$property->getPrefixedText();
		} else {
			$article->insertNewArticle($text, "", false, false);
			print "\n ...created ".$property->getPrefixedText();
		}
	}

	/**
	 * Checks if property with name $propertyName has the expected type
	 *
	 * @param string $propertyName
	 * @param string $expectedType (e.g. _str, _num, _boo, ...)
	 */
	private static function checkPropertyType($propertyName, $expectedType) {
		$pProperty = SMWDIProperty::newFromUserLabel($propertyName);
		$pType = $pProperty->findPropertyTypeID();
		return ($pType == $expectedType);
	}

}
