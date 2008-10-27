<?php
/*
 * Created on 13.06.2007
 *
 * Author: kai
 */

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_cs_Dispatcher';
$wgAjaxExportList[] = 'smwf_cs_SearchForTriples';
$wgAjaxExportList[] = 'smwf_cs_AskForAttributeValues';

// CombinedSearch: Simple Query interface

/**
 * Global CS dispatcher
 */
function smwf_cs_Dispatcher($searchString) {
    $cs = new CombinedSearch();
    /*STARTLOG*/
    smwLog("","CS","opened");
    /*ENDLOG*/
    $parts = $cs->explodeSearchTerm($searchString);

    // searches entities for all parts of the search term and render the result
    $allEntities = array();
    foreach($parts as $part) {
        $entities = $cs->searchEntity($part);
        $allEntities = array_merge($allEntities, $entities);
    }

    $resultHTML = "";

    // create attribute search link
    $partsAsJS = CombinedSearchHelper::convertStringAsJSArray($parts);
    $attributeSearchLink = "<div class=\"cbsrch-content\"><a class=\"askButton\" ".
    "onclick=\"csContributor.searchForAttributeValues(".$partsAsJS.")\">".wfMsg('smw_cs_searchforattributevalues')."</a></div>";

    // get all links as HTML
    if (count($allEntities) == 0) {
        $resultHTML = wfMsg('smw_cs_noresults')."<br>";
        $resultHTML .= $attributeSearchLink;
    } else {
        $resultHTML .= $cs->getIdentifiedEntitiesAsHTML($allEntities);
        $resultHTML .= $attributeSearchLink;
        $resultHTML .= $cs->getFurtherQueriesAsHTML($allEntities, $parts);
    }

    return $resultHTML;

}



function smwf_cs_AskForAttributeValues($parts) {
    // try to find $parts as attribute values
   
    $cs = new CombinedSearch();
    $parts = explode(",", $parts);
    $htmlResult = "<div class=\"cbsrch-content\">";
    $cs_storage = CombinedSearchStorage::getCombinedSearchStore();
    $titlesAndValues = $cs_storage->getInstancesWithAttributeValue($parts);
    if (count($titlesAndValues) > 0) {
        $htmlResult .= wfMsg('smw_cs_attributevalues_found');
        $htmlResult .= "<table class=\"cbsrch-table\">";
        foreach($titlesAndValues as $tav) {
            list($title, $attribute, $value) = $tav;
           
            $htmlResult .= "<tr><td><a href=\"".$title->getFullURL()."\">".$title->getText()."</a></td>" .
                                   "<td><a href=\"".$attribute->getFullURL()."\">".$attribute->getText()."</a></td>" .
                                   "<td>$value</td></tr>";
        }
        $htmlResult .= "</table>";
    } else {
        $htmlResult .= "No instances with matching values found!";
    }
    $htmlResult .= "</div>";
    return $htmlResult;
}

function smwf_cs_SearchForTriples($searchString) {
    $cs = new CombinedSearch();

    $parts = $cs->explodeSearchTerm($searchString);

    $allEntities = array();
    foreach($parts as $part) {
        $entities = $cs->searchEntity($part);
        $allEntities = array_merge($allEntities, $entities);
    }

    $properties = array();
    foreach($allEntities as $e2) {
        if ($e2->getNamespace() == SMW_NS_PROPERTY) {
            $properties[] = $e2;
        }

    }

    $htmlResult = "<table class=\"cbsrch-table\">";
    $tripleTableHeader = "<tr><th align=\"left\">".wfMsg('smw_cs_instances')."</th><th align=\"left\">".wfMsg('smw_cs_properties')."</th>" .
                                           "<th align=\"left\">".wfMsg('smw_cs_values')."</th></tr>";
    $tripleFound = false;
    $tripleTableData = "";
    // show instance property values (if some exist)
    foreach($allEntities as $e) {
        if ($e->getNamespace() == NS_MAIN) {

            foreach($properties as $a) {
                $values = smwfGetStore()->getPropertyValues($e, $a);
                if (count($values) > 0) {
                    $tripleFound = true;
                    /*STARTLOG*/
                    smwLog($e->getText().";".$a->getText().";".$values[0]->getXSDValue()." ".$values[0]->getUnit(),"CS","found_fact");
                    /*ENDLOG*/
                    $tripleTableData .= "<tr>";
                    $tripleTableData .= "<td rowspan=\"".count($values)."\">".$e->getText()."</td>";
                    $tripleTableData .= "<td rowspan=\"".count($values)."\">".$a->getText()."</td>";
                    $tripleTableData .= "<td>".$values[0]->getXSDValue()." ".$values[0]->getUnit()."</td>";
                    $tripleTableData .= "</tr>";
                    for($i = 1, $n = count($values); $i < $n; $i++) {
                        $tripleTableData .= "<tr>";
                        $tripleTableData .= "<td>".$values[$i]->getXSDValue()." ".$values[$i]->getUnit()."</td>";
                        $tripleTableData .= "</tr>";
                    }
                }

            }
        }
    }

    // show property value subjects (if some exist)
    foreach($parts as $term) {
        foreach($properties as $a) {
            $value = SMWDataValueFactory::newPropertyObjectValue($a, $term);
            $subjects = smwfGetStore()->getPropertySubjects($a, $value);
            if (count($subjects) > 0) {
                $tripleFound = true;
                /*STARTLOG*/
                smwLog($subjects[0]->getShortWikiText().";".$a->getText().";".$term,"CS","found_fact");
                /*ENDLOG*/
                $tripleTableData .= "<tr>";
                $tripleTableData .= "<td>".$subjects[0]->getText()."</td>";
                $tripleTableData .= "<td rowspan=\"".count($subjects)."\">".$a->getText()."</td>";
                $tripleTableData .= "<td rowspan=\"".count($subjects)."\">".$term."</td>";
                $tripleTableData .= "</tr>";
                for($i = 1, $n = count($subjects); $i < $n; $i++) {
                    $tripleTableData .= "<tr>";
                    $tripleTableData .= "<td>".$subjects[$i]->getText()."</td>";
                    $tripleTableData .= "</tr>";
                }
            }
        }
    }

    if ($tripleFound) {
        $htmlResult .= $tripleTableHeader.$tripleTableData;
    } else {
        $htmlResult .= wfMsg('smw_cs_no_triples_found');
    }
    $htmlResult .= "</table>";

    return $htmlResult;
}

class CombinedSearch {

    /**
     * Returns identified entities and html
     */
    public function getIdentifiedEntitiesAsHTML($entities) {
        global $wgServer, $wgScriptPath, $wgContLang, $wgScript;

        $resultHTML = "";

        foreach($entities as $page) {

            // HTML rendering
            $resultHTML .= "<tr>";

            // show page link
            $pageTitleUnescaped = $page->getPrefixedDBkey();
            $pageTitleEscaped = urlencode($pageTitleUnescaped);

            $page_exists = $page->exists() ? "" : "new";
            $resultHTML .= "<td><img src=\"".CombinedSearchHelper::getImageReference($page)."\"></td>";
            $resultHTML .= "<td><a class=\"navlink $page_exists\" href=\"".$page->getFullURL()."\" title=\"".wfMsg('smw_cs_openpage')."\">".$page->getText()."</a></td>";

            // show OB link
            if ($page->getNamespace() != NS_TEMPLATE && $page->getNamespace() != SMW_NS_TYPE) {
                $ob_spage = Title::newFromText($wgContLang->getNsText(NS_SPECIAL).":".wfMsg('ontologybrowser'));
                $resultHTML .= "<td><a class=\"navlink\" href=\"".$ob_spage->getFullURL("ns=".$page->getNsText().
                "&entitytitle=".urlencode($page->getDBkey()))."\" title=\"".wfMsg('smw_cs_openpage_in_ob')."\">".
                "<img src=\"$wgServer$wgScriptPath/extensions/SMWHalo/skins/OntologyBrowser/images/ontobrowser.gif\"/></a></td>";
            } else {
                // do NOT show OB link for templates, because it makes no sense.
                $resultHTML .= "<td></td>";
            }

            // show edit link
            $resultHTML .= "<td><a class=\"navlink\" href=\"".$page->getFullURL("action=edit")."\">".
            "<img src=\"$wgServer$wgScriptPath/extensions/SMWHalo/skins/edit.gif\" title=\"".wfMsg('smw_cs_openpage_in_editmode')."\"/></a></td>";
            $resultHTML .= "</tr>";

        }

        if ($resultHTML != '') {
            $resultHTML = "<div class=\"cbsrch-content\">".wfMsg('smw_cs_entities_found')."<table class=\"cbsrch-table\">".$resultHTML;
            $resultHTML .= "</table></div>";
        }

        return$resultHTML;
    }

    /**
     * Does some further search operations:
     *
     * 1. Search for occurences of term in attribute values.
     * 2. Pose some simple ASK query proposal depending of the found entities.
     */
    public function getFurtherQueriesAsHTML($entities, $searchTerms) {
        global $wgContLang;
        $htmlResult = "<div class=\"cbsrch-content\">";

        // combination category/instance <-> attribute/relation
        $htmlResult .= "<table class=\"cbsrch-table\">";

        // collect all found properties
        $properties = array();

        foreach($entities as $e2) {
            if ($e2->getNamespace() == SMW_NS_PROPERTY) {
                $properties[] = $e2;
            }

        }

        // if there are no properties at all, skip last section
        if (empty($properties)) {
            return $htmlResult;
        }

        // show ASK Queries (if at least one category and one property were found)
        foreach($entities as $e) {
            if ($e->getNamespace() == NS_CATEGORY) {
                foreach($entities as $c) {
                    if ($c->getNamespace() == SMW_NS_PROPERTY) {
                        //show ASK query link
                        $htmlResult .= "<tr>";
                        $htmlResult .= "<td>".wfMsg('smw_cs_aksfor_allinstances_with_annotation',$e->getText(), $c->getText())."</td>";
                        $askQuery = "[[".$e->getPrefixedText()."]]|?".$c->getText();
                        /*STARTLOG*/
                        smwLog($askQuery,"CS","produced_factlist");
                        /*ENDLOG*/
                        $ask_spage = Title::newFromText($wgContLang->getNsText(NS_SPECIAL).":Ask");
                        $htmlResult .= "<td><a class=\"askButton\" href=\"".$ask_spage->getFullURL("title=".
                        $ask_spage->getPrefixedDBkey()."&query=".urlencode($askQuery)."&order=ASC")."\">".wfMsg('smw_cs_ask')."</a></td>";
                        $htmlResult .= "</tr>";
                    }
                }
            }
        }
        $htmlResult .= "</table>";
        $htmlResult .= "</div>";
        return $htmlResult;
    }



    public function searchEntity($entityTitle) {
        $cs_store = CombinedSearchStorage::getCombinedSearchStore();
        $pages = $cs_store->getPage($entityTitle);
        $cs_store->replaceRedirects($pages);
        return $pages;
    }

    public function explodeSearchTerm($searchTerm) {
        $matches = array();
        preg_match_all('/([\w\d\_\-]+)|\"([\w\d\_\-\+]+)\"/', $searchTerm, $matches);

        $result = array();
        foreach($matches[0] as $m) {
            $help = preg_replace("/\"/", "", $m);
            $result[] = preg_replace("/\+/", "_", $help);
        }
        return $result;

    }




}

/**
 * Storage layer for combined search
 *
 */
abstract class CombinedSearchStorage {

    private static $INSTANCE = NULL;

    /**
     * Searches a page title. If it does not exist,
     * tries a substring match and then a near match with editdistance 1.
     *
     * @param String $entityTitle
     */
    public abstract function getPage($entityTitle);

    /**
     * Returns all instances which have annotations of the given $values.
     *
     * @param String[] $values
     *
     * @return array of (Title, Title)
     */
    public abstract function getInstancesWithAttributeValue($values);
    
    /**
     * Replaces all redirect titles by their target titles. (not transitive)
     * 
     * @param & $titles
     */
    public abstract function replaceRedirects(array & $titles);
    
    public static function getCombinedSearchStore() {
        global $smwgHaloIP;
        if (self::$INSTANCE == NULL) {
            global $smwgBaseStore;
            switch ($smwgBaseStore) {
                case (SMW_STORE_TESTING):
                    self::$INSTANCE = null; // not implemented yet
                    trigger_error('Testing store not implemented for HALO extension.');
                    break;
                case ('SMWHaloStore2'): default:
                    self::$INSTANCE = new CombinedSearchStorageSQL2();
                    break;
                case ('SMWHaloStore'): default:
                    self::$INSTANCE = new CombinedSearchStorageSQL();
                    break;
            }
        }
        return self::$INSTANCE;
    }
     
}

class CombinedSearchStorageSQL extends CombinedSearchStorage {

    
    public function replaceRedirects(array & $titles) {
        $result = array();
        $db =& wfGetDB( DB_SLAVE );

        for($i = 0, $n = count($titles); $i < $n; $i++) {
            $sql = "rd_from = ".$titles[$i]->getArticleID();
            $res = $db->select( $db->tableName('redirect'),
            array('rd_title','rd_namespace'),
            $sql, 'SMW::replaceRedirects', NULL );


            if($db->numRows( $res ) > 0) {
                while($row = $db->fetchObject($res)) {
                    $titles[$i] = Title::newFromText($row->rd_title, $row->rd_namespace);
                }
            }
            $db->freeResult($res);
        }

    }

    public function getPage($entityTitle) {
        $result = array();
        $db =& wfGetDB( DB_SLAVE );
        $allowedNamespaces = ' AND (page_namespace = '.NS_CATEGORY.' OR page_namespace = '.NS_TEMPLATE.' OR page_namespace = '.SMW_NS_PROPERTY.' OR page_namespace = '.NS_MAIN.' OR page_namespace = '.SMW_NS_TYPE.')';
        // try exact match
        $sql = 'UPPER(page_title) = UPPER('.$db->addQuotes($entityTitle).')'.$allowedNamespaces;
        $res = $db->select( $db->tableName('page'),
        array('DISTINCT page_title','page_namespace'),
        $sql, 'SMW::getPages', NULL );


        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = Title::newFromText($row->page_title, $row->page_namespace);
            }
        } else {
            // next try substring match
            $sql = 'UPPER(page_title) LIKE UPPER('.$db->addQuotes('%'.$entityTitle.'%').') AND UPPER(page_title) != UPPER('.$db->addQuotes($entityTitle).')'.$allowedNamespaces;
            $res2 = $db->select( $db->tableName('page'),
            array('DISTINCT page_title','page_namespace'),
            $sql, 'SMW::getPage', array('LIMIT' => '5') );


            if($db->numRows( $res2 ) > 0) {
                while($row = $db->fetchObject($res2)) {
                    $result[] = Title::newFromText($row->page_title, $row->page_namespace);
                }
            } else if (smwfDBSupportsFunction('halowiki')) {
                // if not found, try edit distance match
                $sql = 'EDITDISTANCE(UPPER(page_title), UPPER('.$db->addQuotes($entityTitle).')) <= 1 AND UPPER(page_title) != UPPER('.$db->addQuotes($entityTitle).')'.$allowedNamespaces;
                $res3 = $db->select( $db->tableName('page'),
                array('DISTINCT page_title','page_namespace'),
                $sql, 'SMW::getPage', array('LIMIT' => '5') );


                if($db->numRows( $res3 ) > 0) {
                    while($row = $db->fetchObject($res3)) {
                        $result[] = Title::newFromText($row->page_title, $row->page_namespace);
                    }
                }
                $db->freeResult($res3);
            }
            $db->freeResult($res2);
        }
        $db->freeResult($res);
        return $result;
    }

    public function getInstancesWithAttributeValue($values) {
        $result = array();
        $db =& wfGetDB( DB_SLAVE );
        $sql = "(";
        for($i = 0, $n = count($values); $i < $n; $i++) {
            if (is_numeric($values[$i])) {
                // allow deviance of 1 %.
                $sql .= '(value_xsd >= '.($values[$i]-(0.01*$values[$i])). ' AND value_xsd <= '.($values[$i]+(0.01*$values[$i])). ') OR ';
            } else {
                $sql .= 'UPPER(value_xsd) LIKE UPPER('.$db->addQuotes('%'.$values[$i].'%').') OR ';
                if (smwfDBSupportsFunction('halowiki')) {
                    $sql .= 'EDITDISTANCE(UPPER(value_xsd), UPPER('.$db->addQuotes($values[$i]).')) <= 1 OR ';
                }
            }
        }
        $sql .= "false)";

        $res = $db->select( $db->tableName('smw_attributes'),
        array('subject_title','subject_namespace', 'attribute_title', 'value_xsd'),
        $sql, 'SMW::getInstancesWithAttributeValue', NULL );

        $res2 = $db->select( array($db->tableName('smw_nary'), $db->tableName('smw_nary_attributes')),
        array('subject_title','subject_namespace', 'attribute_title', 'value_xsd'),
        $sql.' AND smw_nary.subject_id = smw_nary_attributes.subject_id', 'SMW::getInstancesWithAttributeValue', NULL );

        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array(Title::newFromText($row->subject_title, $row->subject_namespace), Title::newFromText($row->attribute_title, SMW_NS_PROPERTY), $row->value_xsd);
            }
        }
        if($db->numRows( $res2 ) > 0) {
            while($row = $db->fetchObject($res2)) {
                $result[] = array(Title::newFromText($row->subject_title, $row->subject_namespace), Title::newFromText($row->attribute_title, SMW_NS_PROPERTY), $row->value_xsd);
            }
        }
        $db->freeResult($res);
        $db->freeResult($res2);
        return $result;
    }
}

class CombinedSearchStorageSQL2 extends CombinedSearchStorageSQL {

    public function getPage($entityTitle) {
        $result = array();
        $db =& wfGetDB( DB_SLAVE );
        $allowedNamespaces = ' AND (smw_namespace = '.NS_CATEGORY.' OR smw_namespace = '.NS_TEMPLATE.' OR smw_namespace = '.SMW_NS_PROPERTY.' OR smw_namespace = '.NS_MAIN.' OR smw_namespace = '.SMW_NS_TYPE.')';
        // try exact match
        $sql = 'UPPER(smw_title) = UPPER('.$db->addQuotes($entityTitle).')'.$allowedNamespaces;
        $res = $db->select( $db->tableName('smw_ids'),
        array('DISTINCT smw_title','smw_namespace'),
        $sql, 'SMW::getPages', NULL );


        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = Title::newFromText($row->smw_title, $row->smw_namespace);
            }
        } else {
            // next try substring match
            $sql = 'UPPER(smw_title) LIKE UPPER('.$db->addQuotes('%'.$entityTitle.'%').') AND UPPER(smw_title) != UPPER('.$db->addQuotes($entityTitle).')'.$allowedNamespaces;
            $res2 = $db->select( $db->tableName('smw_ids'),
            array('DISTINCT smw_title','smw_namespace'),
            $sql, 'SMW::getPage', array('LIMIT' => '5') );


            if($db->numRows( $res2 ) > 0) {
                while($row = $db->fetchObject($res2)) {
                    $result[] = Title::newFromText($row->smw_title, $row->smw_namespace);
                }
            } else if (smwfDBSupportsFunction('halowiki')) {
                // if not found, try edit distance match
                $sql = 'EDITDISTANCE(UPPER(smw_title), UPPER('.$db->addQuotes($entityTitle).')) <= 1 AND UPPER(smw_title) != UPPER('.$db->addQuotes($entityTitle).')'.$allowedNamespaces;
                $res3 = $db->select( $db->tableName('smw_ids'),
                array('DISTINCT smw_title','smw_namespace'),
                $sql, 'SMW::getPage', array('LIMIT' => '5') );


                if($db->numRows( $res3 ) > 0) {
                    while($row = $db->fetchObject($res3)) {
                        $result[] = Title::newFromText($row->smw_title, $row->smw_namespace);
                    }
                }
                $db->freeResult($res3);
            }
            $db->freeResult($res2);
        }
        $db->freeResult($res);
        return $result;
    }

    public function getInstancesWithAttributeValue($values) {
        $result = array();
        $db =& wfGetDB( DB_SLAVE );

        $smw_atts2 = $db->tableName('smw_atts2');
        $smw_ids = $db->tableName('smw_ids');

        $sql = "(";
        for($i = 0, $n = count($values); $i < $n; $i++) {
            if (is_numeric($values[$i])) {
                // allow deviance of 1 %.
                $sql .= '(value_xsd >= '.($values[$i]-(0.01*$values[$i])). ' AND value_xsd <= '.($values[$i]+(0.01*$values[$i])). ') OR ';
            } else {
                $sql .= 'UPPER(value_xsd) LIKE UPPER('.$db->addQuotes('%'.$values[$i].'%').') OR ';
                if (smwfDBSupportsFunction('halowiki')) {
                    $sql .= 'EDITDISTANCE(UPPER(value_xsd), UPPER('.$db->addQuotes($values[$i]).')) <= 1 OR ';
                }
            }
        }
        $sql .= "false)";

        $res = $db->query('SELECT i.smw_title AS subject_title, i.smw_namespace AS subject_namespace, i2.smw_title AS attribute_title, value_xsd FROM '.$smw_atts2.
        ' JOIN '.$smw_ids.' i ON i.smw_id = s_id '.
        ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id '.
        'WHERE '.$sql );
            
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array(Title::newFromText($row->subject_title, $row->subject_namespace), Title::newFromText($row->attribute_title, SMW_NS_PROPERTY), $row->value_xsd);
            }
        }
            
        $db->freeResult($res);
            
        return $result;
    }
}

class CombinedSearchHelper {

    public static function convertEntitiyTitlesInJSArray($entityTitles) {
        $str = "[";
        for($i = 0, $n = count($entityTitles); $i < $n; $i++) {
            if ($i < $n-1) {
                $str .= "'".$entityTitles[$i]->getText()."',";
            } else {
                $str .= "'".$entityTitles[$i]->getText()."'";
            }
        }
        $str .= "]";
        return $str;
    }

    public static function convertStringAsJSArray($parts) {
        $partsAsJS = "[";
        for($i = 0, $n = count($parts); $i < $n; $i++) {
            if ($i < $n-1) {
                $partsAsJS .= "'$parts[$i]',";
            } else {
                $partsAsJS .= "'$parts[$i]'";
            }
        }
        return $partsAsJS."]";
    }


    public static function getImageReference($page) {
        global $wgServer, $wgScriptPath;
        $imagePath = "$wgServer$wgScriptPath/extensions/SMWHalo/skins/";
        switch($page->getNamespace()) {
            case NS_MAIN: { $imagePath .= "instance.gif"; break; }
            case NS_CATEGORY: { $imagePath .= "concept.gif"; break; }
            case NS_TEMPLATE: { $imagePath .= "template.gif"; break; }
            case SMW_NS_PROPERTY: { $imagePath .= "property.gif"; break; }
            case SMW_NS_TYPE: { $imagePath .= "template.gif"; break; }
        }
        return $imagePath;
    }

}
?>