<?php
/**
 * @file
 * @ingroup DFMaintenance
 *
 * Creates content hashes for bundles pages.
 *
 * Usage:   php createContenthashes -b <bundle name>
 *
 * @author: Kai Kühn / ontoprise / 2010
 */

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");
$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
require_once($rootDir."/tools/smwadmin/DF_Tools.php");

/**
 * Initializes the language object
 *
 * Note: Requires wiki context
 */
global $wgLanguageCode, $dfgLang;
$langClass = "DF_Language_$wgLanguageCode";
if (!file_exists("../../languages/$langClass.php")) {
	$langClass = "DF_Language_En";
}
require_once("../../languages/$langClass.php");
$dfgLang = new $langClass();



for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-b => bundleID
	if ($arg == '-b') {
		$bundleID = next($argv);
		continue;
	}

}

$t = Title::newFromText($dfgLang->getLanguageString('df_contenthash'), NS_TEMPLATE);
if (!$t->exists()) {
	print "\n The template '".$dfgLang->getLanguageString('df_contenthash')."' does not exist.";
	die;
}

// iteratates over all bundle pages and add content hash if necessary.
$bundlePages = getBundlePages($bundleID);
foreach($bundlePages as $page) {
	if ($page->getText() === $dfgLang->getLanguageString('df_contenthash') && $page->getNamespace() == NS_TEMPLATE) continue;
	$contentHash= SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_contenthash'));
	$values = smwfGetStore()->getPropertyValues($page, $contentHash);
	if (count($values) > 0) {
		print "\n\t[Skip: ".$page->getPrefixedText()."] Already contains content hash.";
		continue; // annotation already exists
	}
	$a = new Article($page);
	$rev = Revision::newFromTitle($page);
	if (is_null($rev)) {
		print "\n\t[Skip: ".$page->getPrefixedText()."] Revision does not exist.";
		continue; // annotation already exists
	}
	$text = $rev->getRawText();
    $text = replaceOrAddContentHash($text);
	$a->doEdit($text, "added content hash");
	print "\n\t[Added or replaced content hash to: ".$page->getPrefixedText()."]";
}

function replaceOrAddContentHash($text) {
	global $dfgLang;
	$matchNums = preg_match('/\{\{\s*'.$dfgLang->getLanguageString('df_contenthash').'\s*\|\s*value\s*=\s*\w*(\s*\|)?[^}]*\}\}/', $text);
	if ($matchNums === 0) {
		$text .= "\n{{".$dfgLang->getLanguageString('df_contenthash')."|value=".md5($text)."}}";
	} else {
		$rawtext = preg_replace('/\{\{\s*'.$dfgLang->getLanguageString('df_contenthash').'\s*\|\s*value\s*=\s*\w*(\s*\|)?[^}]*\}\}/', "", $text);
		$text = preg_replace('/\{\{\s*'.$dfgLang->getLanguageString('df_contenthash').'\s*\|\s*value\s*=\s*\w*(\s*\|)?[^}]*\}\}/', "{{".$dfgLang->getLanguageString('df_contenthash')."|value=".md5($rawtext)."}}", $text);
	}
	return $text;
}

/**
 * Removes articles belonging to a bundle. It is assumed that everything other than instances of categories of a bundle
 * and templates used by such is marked with the 'Part of bundle' annotation. Templates which are used by pages other than
 * that are kept.
 *
 * @param string $ext_id
 */
function getBundlePages($ext_id) {

	global $dfgLang;
	$resultTitles = array();
	$db =& wfGetDB( DB_MASTER );
	$smw_ids = $db->tableName('smw_ids');
	$smw_rels2 = $db->tableName('smw_rels2');
	$page = $db->tableName('page');
	$categorylinks = $db->tableName('categorylinks');
	$templatelinks = $db->tableName('templatelinks');
	$db->query( 'CREATE TEMPORARY TABLE df_page_of_bundle (id INT(8) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableForPagesOfBundle' );

	$db->query( 'CREATE TEMPORARY TABLE df_page_of_templates_used (title  VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );
	$db->query( 'CREATE TEMPORARY TABLE df_page_of_templates_must_persist (title  VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );

	$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString("df_partofbundle")));
	$ext_id = strtoupper(substr($ext_id, 0, 1)).substr($ext_id, 1);
	$partOfBundleID = smwfGetStore()->getSMWPageID($ext_id, NS_MAIN, "");

	// put all pages belonging to a bundle (all except templates, ie. categories, properties, instances of categories and all other pages denoted by
	// the 'part of bundle' annotation like Forms, Help pages, etc..) in df_page_of_bundle
	$db->query('INSERT INTO df_page_of_bundle (SELECT page_id FROM '.$page.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');
	$db->query('INSERT INTO df_page_of_bundle (SELECT cl_from FROM '.$categorylinks.' JOIN '.$page.' ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');

	// get all templates used on these pages
	$db->query('INSERT INTO df_page_of_templates_used (SELECT tl_title FROM '.$templatelinks.' WHERE tl_from IN (SELECT * FROM df_page_of_bundle))');

	// get all templates which are also used on other pages and must therefore persist
	$db->query('INSERT INTO df_page_of_templates_must_persist (SELECT title FROM df_page_of_templates_used JOIN '.$templatelinks.' ON title = tl_title AND tl_from NOT IN (SELECT * FROM df_page_of_bundle))');

	// delete those from the table of used templates
	$db->query('DELETE FROM df_page_of_templates_used WHERE title IN (SELECT * FROM df_page_of_templates_must_persist)');

	// select all templates which can be deleted
	$res = $db->query('SELECT DISTINCT title FROM df_page_of_templates_used');

	// DELETE templates
	if($db->numRows( $res ) > 0) {
		while($row = $db->fetchObject($res)) {

			$template = Title::newFromText($row->title, NS_TEMPLATE);
			$resultTitles[] = $template;

		}
	}
	$db->freeResult($res);

	// DELETE pages of bundle
	$res = $db->query('SELECT DISTINCT id FROM df_page_of_bundle');

	if($db->numRows( $res ) > 0) {
		while($row = $db->fetchObject($res)) {

			$page = Title::newFromID($row->id);
			$resultTitles[] = $page;
		}
	}
	$db->freeResult($res);

	$db->query('DROP TEMPORARY TABLE df_page_of_bundle');
	$db->query('DROP TEMPORARY TABLE df_page_of_templates_used');
	$db->query('DROP TEMPORARY TABLE df_page_of_templates_must_persist');
	return $resultTitles;

}