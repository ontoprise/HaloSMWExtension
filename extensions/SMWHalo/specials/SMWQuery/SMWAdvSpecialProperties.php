<?php
/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloMiscellaneous
 * Created on 20.09.2007
 *
 * @author Kai K�hn
 */

if (!defined('MEDIAWIKI')) die();


// replace SMW Properties SpecialPage with advanced HALO Properties SpecialPage.
//SpecialPage::removePage(wfMsg('properties'));
//SpecialPage::addPage(new SpecialPage(wfMsg('properties'),'',true,'smwfDoSpecialProperties',false));

function smwfDoSpecialProperties() {
	wfProfileIn('smwfDoSpecialProperties (SMW)');
	global $wgOut;
	SMWOutputs::requireHeadItem( SMW_HEADER_TOOLTIP );
	SMWOutputs::commitToOutputPage( $wgOut );
	list( $limit, $offset ) = wfCheckLimits();
	$rep = new SMWPropertiesPage();
	$result = $rep->doQuery( $offset, $limit );
	wfProfileOut('smwfDoSpecialProperties (SMW)');
	return $result;
}

class SMWPropertiesPage extends SMWQueryPage {

	function getName() {
		return "Properties";
	}

	function isExpensive() {
		return false;
	}

	function isSyndicated() { return false; }

	function getPageHeader() {
		$html = '<p>' . wfMsg('smw_properties_docu') . "</p><br />\n";
		$specialAttPage = Title::newFromText("Properties", NS_SPECIAL);
		global $wgRequest;
		$sort = $wgRequest->getVal("sort") == NULL ? 0 : $wgRequest->getVal("sort") + 0;
		$type = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;

		$sortOptions = array(wfMsg('smw_properties_sortalpha'), wfMsg('smw_properties_sortmoddate'),wfMsg('smw_properties_sorttyperange'));
		$propertyType = array(wfMsg('smw_properties_sortdatatype'), wfMsg('smw_properties_sortwikipage'), wfMsg('smw_properties_sortnary'));

		$html .= "<form action=\"".$specialAttPage->getFullURL()."\">";
		$html .= '<input type="hidden" name="title" value="' . $specialAttPage->getPrefixedText() . '"/>';
		// type of property
		$html .=    "<select name=\"type\">";
		$i = 0;
		foreach($propertyType as $option) {
			if ($i == $type) {
				$html .= "<option value=\"$i\" selected=\"selected\">$option</option>";
			} else {
				$html .= "<option value=\"$i\">$option</option>";
			}
			$i++;
		}
		$html .=    "</select>";

		// sort options
		$html .=    "<select name=\"sort\">";
		$i = 0;
		foreach($sortOptions as $option) {
			if ($i == $sort) {
				$html .= "<option value=\"$i\" selected=\"selected\">$option</option>";
			} else {
				$html .= "<option value=\"$i\">$option</option>";
			}
			$i++;
		}
		$html .=    "</select>";

		$html .=    "<input type=\"submit\" value=\" Go \">";
		$html .= "</form>";
		return $html;
	}
	function getSQL() {

		// QueryPage uses the value from this SQL in an ORDER clause,
		// so return attribute title in value, and its type in title.
		global $wgRequest;
		$sort = $wgRequest->getVal("sort") == NULL ? 0 : $wgRequest->getVal("sort") + 0;
		$type = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;

		$advps_storage = AdvPropertySearchStorage::getAdvPropertySearchStorage();
		switch($type) {
			case 0: return $advps_storage->getDatatypeProperties($sort);
			case 1: return $advps_storage->getWikiPageProperties($sort);
			case 2: return $advps_storage->getNaryProperties($sort);
		}

	}



	function getOrder() {
		global $wgRequest;
		$sort = $wgRequest->getVal("sort") == NULL ? 0 : $wgRequest->getVal("sort") + 0;
		$type = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;
		switch($type) {
			case 0: { switch($sort) {
				case 0: return '';
				case 1: return ' ORDER BY page_touched';
				case 2: return ' ORDER BY s.value_string';
			}
			break;
			}
			case 1: { switch($sort) {

				case 0: return '';
				case 1: return ' ORDER BY touch';
				case 2: return ' ORDER BY value DESC';
			}
			break;
			}
			case 2: { switch($sort) {
				case 2: // fall through cause filtering for type makes no sense here
				case 0: return '';
				case 1: return ' ORDER BY page_touched';
			}
			break;
			}
		}

	}

	function linkParameters() {
		global $wgRequest;
		$sort = $wgRequest->getVal("sort") == NULL ? 0 : $wgRequest->getVal("sort") + 0;
		$type = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;
		return array('sort' => $sort, 'type' => $type);
	}

	function sortDescending() {
		return false;
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgExtraNamespaces, $wgRequest;
		$type = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;
		// The attribute title is in value, see getSQL().


		$errors = array();
		if ($result[5]<=5) {
			$errors[] = wfMsg('smw_propertyhardlyused');
		}
		if ($type == 2) { // n-ary
			$attrtitle = Title::makeTitle( SMW_NS_PROPERTY, $result[2] );
			if (!$attrtitle->exists()) {
				$errors[] = wfMsg('smw_propertylackspage');
			}
			$attrlink = $skin->makeLinkObj( $attrtitle, $attrtitle->getText() );

			$store = smwfGetStore();
			$hasTypeDV = SMWPropertyValue::makeProperty("_LIST");
			$typeValues = $store->getPropertyValues($attrtitle, $hasTypeDV);

			$typelink = array();
			foreach($typeValues as $tv) {
				$typelink[] = $tv->getLongHTMLText($skin);
			}

			if (count($typelink) == 0) { // no type defined
				$errors[] = wfMsg('smw_propertylackstype', "Type:Page");
				$typelink[] = "Page"; // default
			}

			return "$attrlink (".$result[5].")" . wfMsg('smw_attr_type_join', implode(";", $typelink)). ' ' . smwfEncodeMessages($errors);
		} if ($type == 1) {
			$attrtitle = Title::makeTitle( SMW_NS_PROPERTY, $result[2] );
			if (!$attrtitle->exists()) {
				$errors[] = wfMsg('smw_propertylackspage');
			}
			$attrlink = $skin->makeLinkObj( $attrtitle, $attrtitle->getText() );
			if ($result[3] != NULL) {
				$objecttitle = Title::newFromText($result[3]);
				$objectlink = $skin->makeLinkObj( $objecttitle);
			} else {
				$objectlink = '*range not defined*';
			}
			return "$attrlink (".$result[5].")" . wfMsg('smw_attr_type_join', $objectlink). ' ' . smwfEncodeMessages($errors);
		}else {
			$attrtitle = Title::makeTitle( SMW_NS_PROPERTY, $result[2] );
			if (!$attrtitle->exists()) {
				$errors[] = wfMsg('smw_propertylackspage');
			}
			$attrlink = $skin->makeLinkObj( $attrtitle, $attrtitle->getText() );
			$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
			$typetitle = smwfGetStore()->getPropertyValues($attrtitle, $hasTypeDV);
			if (count($typetitle) == 0) {
				$typelink = "Page"; // default
			} else {
				$typelink = $typetitle[0]->getLongHTMLText($skin);
			}
			return "$attrlink (".$result[5].")" . wfMsg('smw_attr_type_join', $typelink). ' ' . smwfEncodeMessages($errors);
		}
	}

	function getResults($options) {
		$db =& wfGetDB( DB_SLAVE );
		$res = $db->query($this->getSQL().$this->getOrder());
		$result = array();
		while($row = $db->fetchObject($res)) {

			$result[] = array($row->type, $row->namespace, $row->title, $row->value, $row->obns, $row->count);
		}
		$db->freeResult($res);
		return $result;
	}
}

abstract class AdvPropertySearchStorage {
	private static $INSTANCE = NULL;

	public static function getAdvPropertySearchStorage() {

		if (self::$INSTANCE == NULL) {
				
			self::$INSTANCE = new AdvPropertySearchStorageSQL2();
				

		}
		return self::$INSTANCE;
	}

	public abstract function getDatatypeProperties($sort);
	public abstract function getWikiPageProperties($sort);
	public abstract function getNaryProperties($sort);
}

class AdvPropertySearchStorageSQL extends AdvPropertySearchStorage {

	public function getDatatypeProperties($sort) {
		$NSatt = SMW_NS_PROPERTY;
		$dbr =& wfGetDB( DB_SLAVE );
		$attributes = $dbr->tableName( 'smw_attributes' );
		$specialprops = $dbr->tableName( 'smw_specialprops' );
		$pages = $dbr->tableName( 'page' );
		return "SELECT 'Attributes' as type, {$NSatt} as namespace, s.value_string as value,
                        a.attribute_title as title, COUNT(*) as count, '-1' as obns
                FROM $specialprops s JOIN $pages p ON p.page_id=s.subject_id 
                JOIN $attributes a ON a.attribute_title=p.page_title AND s.property_id="."_TYPE"."
                GROUP BY a.attribute_title, s.value_string";
	}

	public function getWikiPageProperties($sort) {
		$NSrel = SMW_NS_PROPERTY;
		$dbr =& wfGetDB( DB_SLAVE );
		global $smwgHaloContLang;
		$sspa = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$nary_relations = $dbr->tableName( 'smw_nary_relations' );
		$nary = $dbr->tableName( 'smw_nary' );
		$relations = $dbr->tableName( 'smw_relations' );
		$pages = $dbr->tableName( 'page' );
		switch($sort) {

			case 0: // fall through

			case 1: //fall through

			case 2: return "(SELECT 'Relations' as type,
			{$NSrel} as namespace,
                                n.subject_title as title,
                                r.object_title as value,
                                r.object_namespace as obns,     
                                COUNT(*) as count,
                                p.page_touched as touch
                            FROM $pages p JOIN $relations rel ON p.page_title = rel.relation_title JOIN $nary n ON rel.relation_title = n.subject_title JOIN $nary_relations r ON r.subject_id = n.subject_id 
                            WHERE  r.nary_pos = 1 AND n.attribute_title = ".$dbr->addQuotes(str_replace(" ","_",$sspa[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT]))."
                            GROUP BY title) 
                        UNION
                            (SELECT 'Relations' as type,
                            {$NSrel} as namespace,
                                relation_title as title,
                                NULL as value, 
                                '-1' as obns, 
                                COUNT(*) as count ,
                                p.page_touched as touch
                            FROM $pages p JOIN $relations r ON p.page_title = r.relation_title LEFT JOIN $nary n ON r.relation_title = n.subject_title WHERE n.subject_title IS NULL GROUP BY title) ";
		}
	}

	public function getNaryProperties($sort) {
		$NSrel = SMW_NS_PROPERTY;
		$dbr =& wfGetDB( DB_SLAVE );
		$nary = $dbr->tableName( 'smw_nary' );
		$pages = $dbr->tableName( 'page' );
		switch($sort) {
			case 2: // fall through cause filtering for type makes no sense here
			case 0: return "SELECT 'Nary' as type,
			{$NSrel} as namespace,
                    attribute_title as title,
                    
                    COUNT(*) as count,
                    '-1' as obns,
                    '' as value                 
                    FROM $nary
                    GROUP BY attribute_title";

			case 1: return "SELECT 'Nary' as type,
			{$NSrel} as namespace,
                    attribute_title as title,
                    
                    COUNT(*) as count,
                    '-1' as obns,
                    '' as value 
                    FROM $nary,$pages WHERE attribute_title = page_title
                    GROUP BY attribute_title";


		}
	}
}

class AdvPropertySearchStorageSQL2 extends AdvPropertySearchStorageSQL {

	public function getDatatypeProperties($sort) {
		$NSatt = SMW_NS_PROPERTY;
		$db =& wfGetDB( DB_SLAVE );

		$smw_ids = $db->tableName('smw_ids');
		$smw_spec2 = $db->tableName('smw_spec2');
		$smw_atts2 = $db->tableName('smw_atts2');
		$page = $db->tableName('page');
		$smw_text2 = $db->tableName('smw_text2');
		$hasTypePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeProperty("_TYPE"));
		return "SELECT 'Attributes' as type, {$NSatt} as namespace, s.value_string as value,
i.smw_title as title, COUNT(*) as count, '-1' as obns FROM $smw_atts2 a
JOIN $smw_spec2 s ON s.s_id = a.p_id AND s.p_id=1 AND s.value_string IN ('_str','_num','_boo','_dat','_uri','_ema','_anu','_tel','_tem')
JOIN $smw_ids i ON i.smw_id = a.p_id
JOIN $page p ON page_title = i.smw_title AND page_namespace = i.smw_namespace
GROUP BY i.smw_title, s.value_string
UNION
SELECT 'Attributes' as type, {$NSatt} as namespace, s.value_string as value,
i.smw_title as title, COUNT(*) as count, '-1' as obns FROM $smw_text2 t
JOIN $smw_spec2 s ON s.s_id = t.p_id AND s.p_id=1 AND s.value_string IN ('_txt','_cod')
JOIN $smw_ids i ON i.smw_id = t.p_id
JOIN $page p ON page_title = i.smw_title AND page_namespace = i.smw_namespace
GROUP BY i.smw_title, s.value_string";
	}

	public function getWikiPageProperties($sort) {
		global $smwgHaloContLang;
		$NSrel = SMW_NS_PROPERTY;
		$db =& wfGetDB( DB_SLAVE );
		$sspa = $smwgHaloContLang->getSpecialSchemaPropertyArray();

		$smw_ids = $db->tableName('smw_ids');
		$smw_spec2 = $db->tableName('smw_spec2');
		$smw_atts2 = $db->tableName('smw_atts2');
		$smw_rels2 = $db->tableName('smw_rels2');
		$page = $db->tableName('page');

		$domainAndRange = $db->selectRow($db->tableName('smw_ids'), array('smw_id'), array('smw_title' => SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getDBkey()) );
		if ($domainAndRange == NULL) {
			$domainAndRangeID = -1; // does never exist
		} else {
			$domainAndRangeID = $domainAndRange->smw_id;
		}

		switch($sort) {

			case 0: // fall through

			case 1: //fall through

			case 2: return "(SELECT 'Relations' as type,
			{$NSrel} as namespace,
                               q.smw_title as title,
                                 s.smw_title as value,
                                s.smw_namespace as obns,
                                COUNT(q.smw_title) as count,
                                p.page_touched as touch
                              FROM $smw_ids q
                            JOIN $smw_rels2 n ON q.smw_id = n.p_id
                            LEFT JOIN $page p ON n.p_id = p.page_id
                            LEFT JOIN $smw_rels2 m ON n.p_id = m.s_id AND m.p_id = $domainAndRangeID
                            LEFT JOIN $smw_ids r ON m.o_id = r.smw_id
                            LEFT JOIN $smw_rels2 o ON m.o_id = o.s_id AND m.p_id = $domainAndRangeID
                            LEFT JOIN $smw_ids s ON o.o_id = s.smw_id
                            LEFT JOIN $smw_ids t ON o.p_id = t.smw_id
                            WHERE q.smw_namespace = 102 AND q.smw_iw != \":smw\" AND t.smw_sortkey = \"_2\"
                            GROUP BY title) 
                        UNION DISTINCT
                            (SELECT 'Relations' as type,
                            {$NSrel} as namespace,
                                q.smw_title as title,
                                NULL as value, 
                                '-1' as obns, 
                                COUNT(*) as count ,
                                p.page_touched as touch
                              FROM $smw_ids q 
                            JOIN $smw_rels2 n ON q.smw_id = n.p_id
                            LEFT JOIN $page p ON n.s_id = p.page_id
                            LEFT JOIN $smw_rels2 m ON n.p_id = m.s_id AND m.p_id = $domainAndRangeID
                            WHERE q.smw_namespace = 102 AND q.smw_iw != \":smw\" AND m.s_id IS NULL
                            GROUP BY title)";
		}


	}

	public function getNaryProperties($sort) {
		$NSatt = SMW_NS_PROPERTY;
		$db =& wfGetDB( DB_SLAVE );

		$smw_ids = $db->tableName('smw_ids');
		$smw_spec2 = $db->tableName('smw_spec2');
		$smw_rels2 = $db->tableName('smw_rels2');
		$page = $db->tableName('page');
		$hasTypePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeProperty("_LIST"));
		// REGEXP '_[a-z]{1,3}(;_[a-z]{1,3})+' matches all n-ary properties in special property table. Is there a better way?
		return "SELECT 'Relations' as type, {$NSatt} as namespace, s.value_string as value,
                        i.smw_title as title, COUNT(*) as count, '-1' as obns FROM $smw_rels2 a 
                        JOIN $smw_spec2 s ON s.s_id = a.p_id AND s.p_id=$hasTypePropertyID AND s.value_string REGEXP '_[a-z]{1,3}(;_[a-z]{1,3})+' 
                        JOIN $smw_ids i ON i.smw_id = a.p_id
                        JOIN $page p ON page_title = i.smw_title AND page_namespace = i.smw_namespace  
                GROUP BY i.smw_title, s.value_string";
	}
}

