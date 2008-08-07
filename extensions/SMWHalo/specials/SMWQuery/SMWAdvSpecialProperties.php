<?php
/*
 * Created on 20.09.2007
 *
 * Author: kai
 */
 
if (!defined('MEDIAWIKI')) die();


// replace SMW Properties SpecialPage with advanced HALO Properties SpecialPage.
//SpecialPage::removePage(wfMsg('properties'));
//SpecialPage::addPage(new SpecialPage(wfMsg('properties'),'',true,'smwfDoSpecialProperties',false));

function smwfDoSpecialProperties() {
	wfProfileIn('smwfDoSpecialProperties (SMW)');
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
 		$html .= 	"<select name=\"type\">";
		$i = 0;
		foreach($propertyType as $option) {
			if ($i == $type) {
		 		$html .= "<option value=\"$i\" selected=\"selected\">$option</option>";
			} else {
				$html .= "<option value=\"$i\">$option</option>";
			}
			$i++;		
		}
 		$html .= 	"</select>";
 		
		// sort options
		$html .= 	"<select name=\"sort\">";
		$i = 0;
		foreach($sortOptions as $option) {
			if ($i == $sort) {
		 		$html .= "<option value=\"$i\" selected=\"selected\">$option</option>";
			} else {
				$html .= "<option value=\"$i\">$option</option>";
			}
			$i++;		
		}
 		$html .= 	"</select>";
 		
 		$html .= 	"<input type=\"submit\" value=\" Go \">";
 		$html .= "</form>";
 		return $html;
	}
	function getSQL() {
		
		// QueryPage uses the value from this SQL in an ORDER clause,
		// so return attribute title in value, and its type in title.
		global $wgRequest;
		$sort = $wgRequest->getVal("sort") == NULL ? 0 : $wgRequest->getVal("sort") + 0;
		$type = $wgRequest->getVal("type") == NULL ? 0 : $wgRequest->getVal("type") + 0;
		
		switch($type) {
			case 0: return $this->getDatatypeProperties($sort);
			case 1: return $this->getWikiPageProperties($sort);
			case 2: return $this->getNaryProperties($sort);
		}
		
	}
	
	function getDatatypeProperties($sort) {
		$NSatt = SMW_NS_PROPERTY;
		$dbr =& wfGetDB( DB_SLAVE );
		$attributes = $dbr->tableName( 'smw_attributes' );
		$specialprops = $dbr->tableName( 'smw_specialprops' );
		$pages = $dbr->tableName( 'page' );
		return "SELECT 'Attributes' as type, {$NSatt} as namespace, s.value_string as value, 
		                a.attribute_title as title, COUNT(*) as count, '-1' as obns
		        FROM $specialprops s JOIN $pages p ON p.page_id=s.subject_id 
		        JOIN $attributes a ON a.attribute_title=p.page_title AND s.property_id=".SMW_SP_HAS_TYPE."
		        GROUP BY a.attribute_title, s.value_string";
	}
	
	function getWikiPageProperties($sort) {
		$NSrel = SMW_NS_PROPERTY;
		$dbr =& wfGetDB( DB_SLAVE );
		global $smwgHaloContLang;
		$sspa = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		$nary_relations = $dbr->tableName( 'smw_nary_relations' );
		$nary = $dbr->tableName( 'smw_nary' );
		$relations = $dbr->tableName( 'smw_relations' );
		$pages = $dbr->tableName( 'page' );
		switch($sort) {
			
			case 0:	// fall through
					
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
	
	function getNaryProperties($sort) {
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
			$typeValues = $store->getSpecialValues($attrtitle, SMW_SP_HAS_TYPE);
			
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
			
			$typetitle = smwfGetStore()->getSpecialValues($attrtitle, SMW_SP_HAS_TYPE);
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
?>
