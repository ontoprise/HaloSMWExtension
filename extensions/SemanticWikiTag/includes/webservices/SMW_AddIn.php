<?php
if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgHooks, $wgParser;
if( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
	$wgHooks['ParserFirstCallInit'][] = 'smwgWTregisterParserFunctions';
} else {
	if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
		$wgParser->_unstub();
	}
	smwgWTregisterParserFunctions( $wgParser );
}

function wfAddInStrencode( $s, $dbi = DB_LAST ) {
	$db = wfGetDB( $dbi );
	if ( $db !== false ) {
		return $db->strencode( $s );
	} else {
		return false;
	}
}

function smwfProcessInlineQueryParserFunctionGTP(&$parser) {
	global $smwgQEnabled, $smwgIQRunningNumber;
	if ($smwgQEnabled) {
		$smwgIQRunningNumber++;
		$rawparams = func_get_args();
		array_shift( $rawparams ); // we already know the $parser ...

		$querystring = '';
		$label = '';

		foreach ($rawparams as $name => $param) {
			if ( is_string($name) && ($name != '') ) { // accept 'name' => 'value' just as '' => 'name=value'
				$param = $name . '=' . $param;
			}
			if ( ($param == '') || ($param{0} == '?') ) continue;
			$parts = explode('=',$param,2);
			if (count($parts) >= 2) {
				$p = strtolower(trim($parts[0]));
				if($p == 'mainlabel') {
					$label = '{' . trim($parts[1]) . '}';
				}else if($p == 'limit') {
					$params['limit'] = trim($parts[1]);
				}
			} else {
				$querystring .= $param;
			}
		}
		$querystring = str_replace(array('&lt;','&gt;'), array('<','>'), $querystring);

		if($label == '') {
			$label = '{Query #'. AddIn::$queryId . '}';
			AddIn::$queryId ++;
		}
		if(!$params['limit']) {
			$params['limit'] = 20; // limit to 20 result by default
		}
		$query  = SMWQueryProcessor::createQuery($querystring, $params);
		$res = smwfGetStore()->getQueryResult($query);

		while ( $row = $res->getNext() ) {
			$firstcol = true;
			foreach ($row as $field) {
				$object = $field->getNextObject();
				if ($object->getTypeID() == '_wpg') {
					$text = $object->getLongText();
					AddIn::$queryProps[$label][$text] = true;
				}
			}
		}
	}
	return '';
}

function smwgWTregisterParserFunctions(&$parser) {
	global $wgAddinCalled;
	if($wgAddinCalled === true) {
		$parser->setFunctionHook( 'ask', 'smwfProcessInlineQueryParserFunctionGTP' );
	}
	return true; // always return true, in order not to stop MW's hook processing!
}

// max depth of category graph
define('SMW_MAX_CATEGORY_GRAPH_DEPTH', 10);

class ValidateType {
	/**
	 * Name of SMW site
	 * @param string $name
	 */
	var $name;
	/**
	 * Version of SMW AddIn
	 * @param string $version
	 */
	var $version;
}
class PropertyPair {
	var $name;
	var $value;
}
class PageInfo {
	var $author;
	var $lastUpdate;
	var $properties;
}


class AddIn {
	static $queryProps;
	static $queryId = 1;
	/**
	 * Validate SMW+ site
	 *
	 * @return ValidateType
	 */
	public function validate(){
		global $wgSitename;
		$addin = new ValidateType;
		$addin->name = $wgSitename;
		$addin->version = SMW_WT_VERSION;
		return $addin;
	}

	/**
	 * Get category names
	 *
	 * @return string[]
	 */
	public function getCategories() {
		$result = "";
		$db =& wfGetDB( DB_SLAVE );
		$page = $db->tableName('page');
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND page_is_redirect = 0';

		$res = $db->select( $page, 'page_title', $sql, 'SMW::getCategories' );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::makeTitle( NS_CATEGORY, $row->page_title )->getText();
			}
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * Get property names
	 *
	 * @return string[]
	 */
	public function getProperties() {
		$result = "";
		$db =& wfGetDB( DB_SLAVE );
		$smw_subprops = $db->tableName('smw_subprops');
		$page = $db->tableName('page');
		$sql = 'page_namespace=' . SMW_NS_PROPERTY .
			   ' AND page_is_redirect = 0';

		$res = $db->select( $page, 'page_title', $sql, 'SMW::getProperties');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::makeTitle( SMW_NS_PROPERTY, $row->page_title )->getText();
			}
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * Get SMW titles by specified categories
	 *
	 * @param string[] $categoryTitles
	 *
	 * @return string[]
	 */
	public function getTitles($categoryTitles) {
		$db =& wfGetDB( DB_SLAVE );

		$page = $db->tableName('page');

		if (($categoryTitles == NULL) || (count($categoryTitles) == 0)) {
			$sql = '(page_namespace='. NS_MAIN .' or page_namespace='.NS_CATEGORY.')';
			//$sql = '(page_namespace='. NS_MAIN .')';
			$res = $db->select( $page, 'page_title', $sql, 'SMW::getTitles');
		} else {
			$categorylinks = $db->tableName('categorylinks');
			$cates = '\'\'';
			foreach($categoryTitles as $cate){
				$cates .= ',\''.wfAddInStrencode(Title::makeTitle(NS_CATEGORY, $cate)->getDBkey()).'\'';
			}
			$res = $db->query('SELECT p.page_title from '.$page.' p LEFT JOIN '.$categorylinks.' c ON c.cl_from=p.page_id WHERE c.cl_to IN ('.$cates.') AND p.page_namespace=' . NS_MAIN);
		}
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::makeTitle( NS_MAIN, $row->page_title)->getText();
			}
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * Get all properties of specified title
	 *
	 * @param string $title
	 *
	 * @return PropertyPair[]
	 */
	public function getTitleProperties($title) {
		$db =& wfGetDB( DB_SLAVE );
		$fname = "SMW::getTitleProperties";

		$is_redirect = 1;
		while($is_redirect) {
			$res = $db->select( $db->tableName('page'), array( 'page_id', 'page_is_redirect', 'page_namespace'),
				'page_title=\''.wfAddInStrencode(Title::makeTitle( NS_MAIN, $title)->getDBkey()).'\' AND (page_namespace='. NS_MAIN .' or page_namespace='.NS_CATEGORY.')', 'SMW::getTitleProperties');
			if($db->numRows( $res ) <= 0) {
				return NULL;
			}
			$resobj =  $db->fetchObject($res);
			$page_id = $resobj->page_id;
			$is_redirect = $resobj->page_is_redirect;
			$page_namespace = $resobj->page_namespace;
			$db->freeResult($res);
			if($is_redirect) {
				$res = $db->select( $db->tableName('redirect'), 'rd_title',
					'rd_from=' . $page_id . ' AND (rd_namespace='. NS_MAIN .' or rd_namespace='.NS_CATEGORY.')', ' SMW::getTitleProperties');
				if($db->numRows( $res ) <= 0) {
					return NULL;
				}
				$title = $db->fetchObject($res)->rd_title;
			}
		}
		$temp_res = array();
		$result = array();
		if ($page_namespace == NS_MAIN)
		{
			// normal page
			$t_title = Title::newFromText( $title );
			global $wgTitle;
			$wgTitle = $t_title;
			$revision = Revision::newFromTitle( $t_title );// Title::makeTitle( NS_MAIN, $title ) );
			if ( $revision !== NULL ) {
				global $wgParser, $wgOut, $wgAddinCalled;
				$wgAddinCalled = true;
				//$wgParser->setFunctionHook( 'ask', 'smwfProcessInlineQueryParserFunctionGTP' );
				$popts = $wgOut->parserOptions();
				$popts->setTidy(true);
				$popts->enableLimitReport();

				AddIn::$queryProps = array();
				AddIn::$queryId = 1;
				$wgParser->parse( $revision->getText(), $t_title, $popts );
				
				$output = SMWParseData::getSMWdata($wgParser);
			}
			if (!isset($output)) {
				$semdata = smwfGetStore()->getSemanticData($t_title);
			} else {
				$semdata = $output;
			}

			foreach($semdata->getProperties() as $property) {
				if (!$property->isShown()) { // showing this is not desired, hide
					continue;
				} elseif ($property->isUserDefined()) { // user defined property
					$property->setCaption(preg_replace('/[ ]/u','&nbsp;',$property->getWikiValue(),2));
					/// NOTE: the preg_replace is a slight hack to ensure that the left column does not get too narrow
					$temp_res[$property->getWikiValue()] = array();
				} elseif ($property->isVisible()) { // predefined property
					$temp_res[$property->getWikiValue()] = array();
				} else { // predefined, internal property
					continue;
				}

				$propvalues = $semdata->getPropertyValues($property);
				foreach ($propvalues as $propvalue) {
					$temp_res[$property->getWikiValue()][] = $propvalue->getLongWikiText();
				}
			}

			foreach($temp_res as $propname => $propvals) {
				foreach($propvals as $propval) {
					$prop = new PropertyPair;
					$prop->name = $propname;
					$prop->value = $propval;
					$result[] = $prop;
				}
			}
			foreach(AddIn::$queryProps as $propname => $propvals) {
				foreach($propvals as $propval => $flag) {
					$prop = new PropertyPair;
					$prop->name = $propname;
					$prop->value = $propval;
					$result[] = $prop;
				}
			}
		}
		else if($page_namespace == NS_CATEGORY)
		{
			$t_title = Title::newFromText( $title );
			global $wgTitle;
			$wgTitle = $t_title;
			$category = Category::newFromTitle( $t_title );
			$pagecnt = $category->getPageCount() - $category->getSubcatCount() - $category->getFileCount();
			
			$categoryText = wfAddInStrencode(Title::makeTitle( NS_MAIN, $title)->getDBkey());

			// get default form id
			$resDefaultForm = $db->doQuery("SELECT s.value_string FROM smw_spec2 s INNER JOIN smw_ids c ON s.s_id=c.smw_id AND c.smw_namespace=14 AND c.smw_title='".$categoryText."' INNER JOIN smw_ids AS f ON f.smw_id=s.p_id WHERE f.smw_namespace=102 AND f.smw_title='Has_default_form'");
			if ($db->numRows( $resDefaultForm ) > 0)
			{
				$resDefaultFormObj = $db->fetchObject($resDefaultForm);
				$defaultForm = $resDefaultFormObj->value_string;
				$db->freeResult($resDefaultForm);

				$prop = new PropertyPair;
				$prop->name = "(category)Default form";
				$prop->value = $defaultForm;
				$result[] = $prop;
			}
			
			/*
			// get Number of articles in this category
			$resTotalArticle = $db->doQuery("select count(1) as TotalNumber from `categorylinks` where cl_to = '".$categoryText."'");
			$resTotalArticleCount =  $db->fetchObject($resTotalArticle);
			
			$prop = new PropertyPair;
			$prop->name = "(category)Number of articles";
			$prop->value = $pagecnt; //$resTotalArticleCount->TotalNumber;
			$db->freeResult($resTotalArticle);
			*/
			$prop = new PropertyPair;
			$prop->name = "(category)Number of articles";
			$prop->value = $pagecnt;
			$result[] = $prop;
			
			// get Top ten Recent Changes in this category
			$getRecentChangeSql = ' SELECT c.cl_sortkey
									FROM `categorylinks` c, `page` p,
										 ( SELECT REV_PAGE , MAX(REV_TIMESTAMP) AS REV_TIMESTAMP
			 							   FROM `revision` 
			 							   GROUP BY REV_PAGE ) r
									WHERE c.cl_to = \''.$categoryText.'\'
									AND p.page_title =REPLACE(c.cl_sortkey,\' \',\'_\')
									AND p.page_namespace = '.NS_MAIN.'
									AND p.page_id = r.rev_page
									Order by r.rev_timestamp desc
									LIMIT 0 , 10';
			$resRecentChange = $db->doQuery($getRecentChangeSql);
			while ($resRecentChangeAtricle =  $db->fetchObject($resRecentChange))
			{
				$prop = new PropertyPair;
				$prop->name = "(category)Recent Change";
				$prop->value = $resRecentChangeAtricle->cl_sortkey;
				$result[] = $prop;
			}
			$db->freeResult($resRecentChange);
		}

		return $result;
	}

	/**
	 * Add Wiki category
	 *
	 * @param string $categoryName
	 *
	 * @return boolean
	 */
	public function addCategory($categoryName) {
		$title = Title::newFromText( $categoryName, NS_CATEGORY );
		if(!$title->exists()) {
			$article = new Article($title);
			$article->doEdit("''This article is generated via Mail Upload extension by Microsoft Outlook Addin; ".
				"any edits on this page could be overwritten by future uploads under the same subject.''",'');
		}

		return true;
	}

	/**
	 * Save mail to Wiki
	 *
	 * @param string $subject
	 * @param string $sender
	 * @param string[] $receivers
	 * @param string[] $ccs
	 * @param dateTime $sentDate
	 * @param string $basedMailPage
	 * @param string $action
	 * @param string[] $categories
	 * @param string $body
	 * @param string[] $attachments
	 * 
	 * @return string
	 */

	public function saveNewMail($subject, $sender, $receivers, $ccs, $sentDate, $basedMailPage, $action, $categories, $body, $attachments) {
		define('MAIL_SUBJECT', "Mail subject");
		define('MAIL_SENDER', "Mail from");
		define('MAIL_RECEIVER', "Mail to");
		define('MAIL_CC', "Mail cc");
		define('MAIL_SENT_DATE', "Mail sent");
		define('MAIL_ACTION', "Mail action");
		define('MAIL_BASED', "Previous mail");
		define('MAIL_ATTACHMENT', "Mail attachment");

		$prop_subject = str_replace( ' ', '_', MAIL_SUBJECT ); // Title::makeTitle( SMW_NS_PROPERTY, MAIL_SUBJECT )->getDBkey();
		$prop_from = str_replace( ' ', '_', MAIL_SENDER );
		$prop_to = str_replace( ' ', '_', MAIL_RECEIVER );
		$prop_cc = str_replace( ' ', '_', MAIL_CC );

		$subject = ($subject === NULL) ? "" : ucfirst($subject);
		if($sender === NULL) $sender = "";
		$body = str_replace("</nowiki>", "</ nowiki>", $body); // trick here
		$body = str_replace("\n", "</nowiki>\n\n<nowiki>", $body);

		$content = "";
		$props = 0;
		//		$content .= "'''[[".MAIL_SUBJECT."::$subject]]'''\n\n";
		//		$props ++;

		$content .= "{{Wiki Mail\n";
		if($sender != NULL) {
			$content .= "|from=$sender\n";
			$props ++;
		}

		$mail_cates = array( strtolower($sender) );

		if($receivers !== NULL && isset($receivers) && count($receivers) > 0) {
			$content .= "|to=";
			$first = true;
			$to_sql = " OR (attribute='$prop_to' AND (";
			foreach(array_unique($receivers) as $receiver) {
				if($first) {
					$first = false;
				} else {
					$content .= ", ";
					$to_sql .= " OR ";
				}
				$content .= $receiver;
				$to_sql .= "UPPER(value)=UPPER('$receiver')";
				$props ++;
				$mail_cates[] = strtolower($receiver);
			}
			$content .= "\n";
			$to_sql .= ")) ";
		}

		if($ccs !== NULL && isset($ccs) && count($ccs) > 0) {
			$content .= "|cc=";
			$first = true;
			$cc_sql = " OR (attribute='$prop_cc' AND (";
			foreach(array_unique($ccs) as $cc) {
				if($first) {
					$first = false;
				} else {
					$content .= ", ";
					$cc_sql .= " OR ";
				}
				$content .= $cc;
				$props ++;
				$cc_sql .= "UPPER(value)=UPPER('$cc')";
				$mail_cates[] = strtolower($cc);
			}
			$content .= "\n";
			$cc_sql .= ")) ";
		}

		if($sentDate !== NULL) {
			$content .= "|sent=".strftime("%Y-%m-%d %H:%M:%S", strtotime($sentDate))."\n";
		}

		//		$content .= "|-\n";
		//		$content .= "! Previous\n";
		//		$content .= "| ".($basedMailPage?"[[".MAIL_BASED."::$basedMailPage]]":"")." (''[[".MAIL_ACTION."::$action]]'')\n";

		if($attachments !== NULL && isset($attachments) && count($attachments) > 0) {
			$content .= "|attachment=";
			$first = true;
			foreach(array_unique($attachments) as $attachment) {
				if($first) {
					$first = false;
				} else {
					$content .= ", ";
				}
				$content .= $attachment;
			}
			$content .= "\n";
		}
		
		$content .= "}}\n";

		$content .= "<nowiki>";
		$content .= $body;
		$content .= "</nowiki>\n\n";

		$fname = "SMW::saveNewMail";
		$db =& wfGetDB( DB_SLAVE );

		$cates = array();
		if($categories !== NULL && isset($categories) && count($categories) > 0) {
			foreach($categories as $category) {
				$cates[] = ucfirst($category);
			}
		}

		// get mail related category
		$mails = implode("','", array_unique($mail_cates));
		extract( $db->tableNames('smw_ids', 'smw_atts2', 'smw_rels2') );
		$res = $db->query("SELECT distinct i.smw_title FROM $smw_ids i INNER JOIN (
			SELECT a.s_id FROM $smw_ids i LEFT JOIN $smw_atts2 a ON a.p_id = i.smw_id WHERE 
				LOWER(a.value_xsd) IN ( '$mails' ) AND 
				i.smw_title = 'Has_user_mail' 
			) i2 ON i.smw_id = i2.s_id WHERE 
			i.smw_namespace = ".NS_CATEGORY." UNION
			
		SELECT distinct i.smw_title FROM $smw_ids i INNER JOIN (
			SELECT a.s_id FROM $smw_ids i LEFT JOIN $smw_rels2 a ON a.p_id = i.smw_id 
				LEFT JOIN $smw_ids i2 ON a.o_id = i2.smw_id WHERE 
				i.smw_title = 'Has_user_mail' AND LOWER(i2.smw_title) IN ( '$mails' ) AND i2.smw_namespace = ".NS_MAIN."
			) i2 ON i.smw_id = i2.s_id WHERE i.smw_namespace = ".NS_CATEGORY, $fname);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$cates[] = str_replace( '_', ' ', $row->smw_title );
			}
		}
		$db->freeResult($res);

		// just overwrite the subject
		$title = Title::newFromText( $subject );
		if($title->exists()) {
			// merge categories
			extract( $db->tableNames('categorylinks', 'page') );
			$res = $db->query("SELECT $categorylinks.cl_to FROM $categorylinks LEFT JOIN $page ON $categorylinks.cl_from = $page.page_id WHERE $categorylinks.cl_sortkey = '".wfAddInStrencode($subject)."' AND $page.page_namespace = ".NS_MAIN, $fname);

			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$cates[] = str_replace( '_', ' ', $row->cl_to );
				}
			}
			$db->freeResult($res);
		}

		foreach(array_unique($cates) as $c) {
			$content .= "[[Category:".ucfirst($c)."]]";
		}
		
		$content .= "\n\n''This article is generated via Mail Upload extension by Microsoft Outlook Addin; ".
			"any edits on this page could be overwritten by future uploads under the same subject.''";
		
		$revision = Revision::newFromTitle( $title );
		if (( $revision !== NULL ) && ($revision->getText() == $content)) {
			return $title->getText();
		}

		$article = new Article($title);
		$article->doEdit($content,'');

		return $title->getText();
	}

	/**
	 * Get all properties of specified title
	 *
	 * @param string $title
	 *
	 * @return PropertyPair[]
	 */
	public function getPageInfo($title) {

		$db =& wfGetDB( DB_SLAVE );
		$fname = "SMW::getPageInfo";

		$is_redirect = 1;
		while($is_redirect) {
			$res = $db->select( $db->tableName('page'), array( 'page_id', 'page_is_redirect'),
				'page_title=\''.wfAddInStrencode(Title::makeTitle( NS_MAIN, $title)->getDBkey()).'\' AND page_namespace='.NS_MAIN, 'SMW::getPageInfo');
			if($db->numRows( $res ) <= 0) {
				return NULL;
			}
			$resobj =  $db->fetchObject($res);
			$page_id = $resobj->page_id;
			$is_redirect = $resobj->page_is_redirect;
			$db->freeResult($res);
			if($is_redirect) {
				$res = $db->select( $db->tableName('redirect'), 'rd_title',
					'rd_from=' . $page_id . ' AND rd_namespace='.NS_MAIN, 'SMW::getPageInfo');
				if($db->numRows( $res ) <= 0) {
					return NULL;
				}
				$title = $db->fetchObject($res)->rd_title;
			}
		}

		$temp_res = array();

		$t_title = Title::newFromText( $title );
		global $wgTitle;
		$wgTitle = $t_title;
		$revision = Revision::newFromTitle( $t_title );// Title::makeTitle( NS_MAIN, $title ) );
		if ( $revision !== NULL ) {
			global $wgParser, $wgOut, $wgAddinCalled;
			$wgAddinCalled = true;
			//$wgParser->setFunctionHook( 'ask', 'smwfProcessInlineQueryParserFunctionGTP' );
			$popts = $wgOut->parserOptions();
			$popts->setTidy(true);
			$popts->enableLimitReport();

			AddIn::$queryProps = array();
			AddIn::$queryId = 1;
			$wgParser->parse( $revision->getText(), $t_title, $popts );

			$output = SMWParseData::getSMWdata($wgParser);
		}
		if (!isset($output)) {
			$semdata = smwfGetStore()->getSemanticData($t_title);
		} else {
			$semdata = $output;
		}
			
		foreach($semdata->getProperties() as $property) {
			if (!$property->isShown()) { // showing this is not desired, hide
				continue;
			} elseif ($property->isUserDefined()) { // user defined property
				$property->setCaption(preg_replace('/[ ]/u','&nbsp;',$property->getWikiValue(),2));
				/// NOTE: the preg_replace is a slight hack to ensure that the left column does not get too narrow
				$temp_res[$property->getWikiValue()] = array();
			} elseif ($property->isVisible()) { // predefined property
				$temp_res[$property->getWikiValue()] = array();
			} else { // predefined, internal property
				continue;
			}

			$propvalues = $semdata->getPropertyValues($property);
			foreach ($propvalues as $propvalue) {
				$temp_res[$property->getWikiValue()][] = $propvalue->getLongWikiText();
			}
		}

		$info = new PageInfo();
		$info->lastUpdate = $revision->getTimestamp();
		$info->author = $revision->getUserText();
		$info->properties = array();
		foreach($temp_res as $propname => $propvals) {
			foreach($propvals as $propval) {
				$prop = new PropertyPair;
				$prop->name = $propname;
				$prop->value = $propval;
				$info->properties[] = $prop;
			}
		}
		foreach(AddIn::$queryProps as $propname => $propvals) {
			foreach($propvals as $propval => $flag) {
				$prop = new PropertyPair;
				$prop->name = $propname;
				$prop->value = $propval;
				$info->properties[] = $prop;
			}
		}

		return $info;
	}
}

