<?php
/*
 * Generates SPARQL queries
 * 
 * Usage:
 * 
 *  php createsSPARQLQuries [options]
 * 
 * Example: php createsSPARQLQuries 
 * 
 * Every options which is not specified is used with its default value.
 * 
 * Created on 25.03.2008
 * Author: kai
 */

define('USE_LEAF_QUERIES_NUM', 20);
define('USE_LEAF_PROPERTIES_NUM', 20);
define('MAX_PROPS_IN_QUERY', 4);
define('MAX_DEPTH', 3);

// **** main program begin *****
$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

if (array_key_exists("addOWL", $options)) {
	print "Adding OWL annotations...\n";
	//addSymmetricalProperties();
	addTransitiveProperties();
	//addInverseProperties();
	print "OWL annotations added";
}
 // set command line options 
foreach($options as $option => $value) {
   define($option, $value);
}

$queries = array();
// create Queries
//$queries['categoryQueries'] = createCategoryQueries(); printProgress(1);print "\n";
//$queries['categoryPropertyQueries'] = createCategoryPropertyQueries();printProgress(1);print "\n";
//$queries['categoryPropertyQueriesOptional'] = createCategoryPropertyQueriesOptional();printProgress(1);print "\n";
//$queries['categoryPropertyQueriesUnion'] = createCategoryPropertyQueriesUnion();printProgress(1);print "\n";
//$queries['propertyQueries'] = createPropertyQueries();printProgress(1);print "\n";
//$queries['categoryPropertyQueriesWithConstraint'] = createCategoryPropertyWithConstraintQueries();printProgress(1);print "\n";
//$queries['propertyQueriesWithConstraint'] = createPropertyWithConstraintQueries();printProgress(1);print "\n";
//$queries['literalMatchQuery'] = createLiteralMatchQuery();printProgress(1);print "\n";
//$queries['pathQuery'] = createPathQuery();printProgress(1);print "\n";
//$queries['symPropQueries'] = createSymPropQueries();printProgress(1);print "\n";
$queries['transPropQueries'] = createTransPropQueries();printProgress(1);print "\n";


// serialize to file
serializeQueriesToXML("d:\\testsets.xml", $queries);
// **** main program end *****


function printProgress($percentage) {
        $pro_str = number_format($percentage*100, 0);
        if ($percentage == 0) { 
            print $pro_str."%";
            return;
        } 
        switch(strlen($pro_str)) {
            case 4: print "\x08\x08\x08\x08\x08"; break;
            case 3: print "\x08\x08\x08\x08"; break;
            case 2: print "\x08\x08\x08"; break;
            case 1: print "\x08\x08"; break;
            case 0: print "\x08";
        }
        print $pro_str."%";
    }
    
/**
 * Serialize queries to use them in JAPEX
 *
 * @param string $filename File to write queries to
 * @param array $queries Array of array of queries
 */
function serializeQueriesToXML($filename, $queries) {
 $handle = fopen($filename,"wb");
 $xml_result = "<testCaseGroup>\n";
 $i = 0;
 foreach($queries as $desc => $q_array) {
 	foreach($q_array as $q) {
 		$q = str_replace("<", "&lt;", $q);
 		$q = str_replace(">", "&gt;", $q);
 		$q = str_replace("\n", " ", $q);
 		if ($q == "") continue;
 		$xml_result .= "<testCase name=\"Query".$i."\">\n";
 		$xml_result .= "  <param name=\"description\" value=\"".$desc."\"/>\n";
 		$xml_result .= "  <param name=\"query\" value=\"".$q."\"/>\n";
 		$xml_result .= "</testCase>\n";
 		$i++;
 	}
 }
 $xml_result .= "</testCaseGroup>";
 fwrite($handle, $xml_result);
 fclose($handle);
}

function getStandardPrefixes() {
	return "PREFIX cat:  <http://halowiki.org/category#>\n".
	       "PREFIX prop:  <http://halowiki.org/property#>\n".
	       "PREFIX a: <http://www.halowiki.org#>\n";
}

function getVar($j) {
	return "?".chr(65+$j);
}

function getVarString($from, $to) {
	$result = "";
	for($i = $from; $i <= $to; $i++) {
		$result .= "?".chr(65+$i)." ";
	}
    return $result;
}

function createCategoryQueries() {
	$prefixes = getStandardPrefixes();
    $categoryQueries = array();
    $leaves = getLeafCategories(USE_LEAF_QUERIES_NUM);
    $i = 0;
    $superCats = array();
    foreach($leaves as $leaf) {
            printProgress($i / (count($leaves)));
            
        do {
            $superCat = !empty($superCats) ? $superCats[0] : $leaf;
            $categoryQueries[] = $prefixes."SELECT ?x WHERE { ?x rdf:type cat:".$superCat->getDBkey().". }";
            
            $superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
        } while (!empty($superCats));
        $i++;
        
    }
    return $categoryQueries;
 }
 
 function createCategoryPropertyQueries() {
 	$prefixes = getStandardPrefixes();
    $categoryQueries = array();
    $leaves = getLeafCategories(USE_LEAF_QUERIES_NUM);
    $i=0;
    $superCats = array();
    foreach($leaves as $leaf) {
            printProgress($i / (count($leaves)));
            
        do {
            $superCat = !empty($superCats) ? $superCats[0] : $leaf;
            $properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($superCat);
            $property_restr = "";
            $j = 0;
            foreach($properties as $prop) {
                list($p, $minCard, $maxCard, $type, $symCat, $transCat, $range) = $prop;
                $property_restr .= "?x ".$p->getDBkey()." ".getVar($j).".\n";
                if ($j >= query_prop) break;
                $j++;
            }
            
            $categoryQueries[] = $prefixes."SELECT ?x ".getVarString(0, $j)." WHERE { ?x rdf:type cat:".$superCat->getDBkey().".\n ".$property_restr." }";
            $superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
        } while (!empty($superCats));
        $i++;
       
    }
    return $categoryQueries;
 }
 
 function createCategoryPropertyQueriesOptional() {
    $prefixes = getStandardPrefixes();
    $categoryQueries = array();
    $leaves = getLeafCategories(USE_LEAF_QUERIES_NUM);
    $i=0;
    $superCats = array();
    foreach($leaves as $leaf) {
            printProgress($i / (count($leaves)));
            
        do {
            $superCat = !empty($superCats) ? $superCats[0] : $leaf;
            $properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($superCat);
            $property_restr = "";
            $j = 0;
            foreach($properties as $prop) {
                list($p, $minCard, $maxCard, $type, $symCat, $transCat, $range) = $prop;
                $property_restr .= " OPTIONAL {?x ".$p->getDBkey()." ".getVar($j)."}. \n";
                if ($j >= query_prop) break;
                $j++;
            }
            
            $categoryQueries[] = $prefixes."SELECT ?x ".getVarString(0, $j)." WHERE { ?x rdf:type cat:".$superCat->getDBkey().".\n ".$property_restr." }";
            $superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
        } while (!empty($superCats));
        $i++;
       
    }
    return $categoryQueries;
 }
 
function createCategoryPropertyQueriesUnion() {
    $prefixes = getStandardPrefixes();
    $categoryQueries = array();
    $leaves = getLeafCategories(USE_LEAF_QUERIES_NUM);
    $i=0;
    $superCats = array();
    foreach($leaves as $leaf) {
            printProgress($i / (count($leaves)));
            
        do {
            $superCat = !empty($superCats) ? $superCats[0] : $leaf;
            $properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($superCat);
            $property_restr = "";
            $j = 0;
            foreach($properties as $prop) {
                list($p, $minCard, $maxCard, $type, $symCat, $transCat, $range) = $prop;
                $property_restr .= " UNION {?x ".$p->getDBkey()." ".getVar($j)."}. \n";
                if ($j >= query_prop) break;
                $j++;
            }
            
            $categoryQueries[] = $prefixes."SELECT ?x ".getVarString(0, $j)." WHERE { ?x rdf:type cat:".$superCat->getDBkey().".\n ".$property_restr." }";
            $superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
        } while (!empty($superCats));
        $i++;
       
    }
    return $categoryQueries;
 }
 
 function createCategoryPropertyWithConstraintQueries() {
 	 $prefixes = getStandardPrefixes();
    $categoryQueries = array();
    $leaves = getLeafCategories(USE_LEAF_QUERIES_NUM);
    $i=0;
    $superCats = array();
    foreach($leaves as $leaf) {
            printProgress($i / (count($leaves)));
            $maxTry = 10;
        do {
            
            $superCat = !empty($superCats) ? $superCats[0] : $leaf;
            $annotationValues = getSampleAnnotationValues($superCat, MAX_PROPS_IN_QUERY);
            $property_restr = "";
            $j = 0;
            foreach($annotationValues as $values) {
                list($smwValue, $p) = $values;
                if ($smwValue instanceof SMWNumberValue) {
                    $property_restr .= " ?x prop:".$p->getDBkey()." ".getVar($j).". FILTER(".getVar($j)." > 50)\n";
                } else {
                    $property_restr .= " ?x prop:".$p->getDBkey()." ".getVar($j).". FILTER(".getVar($j)." = \"".$smwValue->getXSDValue()."\")\n";
                   
                }
                $j++;
            }
            if ($property_restr == '') continue;
            $categoryQueries[] = $prefixes."SELECT ?x ".getVarString(0, $j-1)." WHERE { ?x rdf:type cat:".$superCat->getDBkey().".\n ".$property_restr." }";
            $superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
            $maxTry--;
        } while (!empty($superCats) && $maxTry > 0);
       
        $i++;
    }
    return $categoryQueries;
 }
 
function createPropertyWithConstraintQueries() {
    $prefixes = getStandardPrefixes();
    $categoryQueries = array();
    $leaves = getLeafCategories(USE_LEAF_QUERIES_NUM);
    $i=0;
    $superCats = array();
    foreach($leaves as $leaf) {
            printProgress($i / (count($leaves)));
            $maxTry = 10;
        do {
        	
            $superCat = !empty($superCats) ? $superCats[0] : $leaf;
            $annotationValues = getSampleAnnotationValues($superCat, MAX_PROPS_IN_QUERY);
            $property_restr = "";
            $j = 0;
            foreach($annotationValues as $values) {
                list($smwValue, $p) = $values;
                if ($smwValue instanceof SMWNumberValue) {
                    $property_restr .= " ?x prop:".$p->getDBkey()." ".getVar($j).". FILTER(".getVar($j)." > 50)\n";
                } else {
                    $property_restr .= " ?x prop:".$p->getDBkey()." ".getVar($j).". FILTER(".getVar($j)." = \"".$smwValue->getXSDValue()."\")\n";
                   
                }
                $j++;
            }
            if ($property_restr == '') continue;
            $categoryQueries[] = $prefixes."SELECT ?x ".getVarString(0, $j-1)." WHERE { ".$property_restr." }";
            $superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
            $maxTry--;
        } while (!empty($superCats) && $maxTry > 0);
       
        $i++;
    }
    return $categoryQueries;
 }
 
 function createPropertyQueries() {
 	$prefixes = getStandardPrefixes();
    $propertyQueries = array();
    $leaves = getLeafProperties(USE_LEAF_PROPERTIES_NUM);
    $i = 0;
    $superProps = array();
    foreach($leaves as $leaf) {
            printProgress($i / (count($leaves)));
            $superProp = $leaf;
            $maxTry = 10;
        do {
            $superProp = $superProp == NULL ? $superProps[0] : $superProp;
            $propertyQueries[] = $prefixes."SELECT ?x WHERE {?x prop:".$superProp->getDBkey()." ".getVar(0).". }";
            
            $superProps = smwfGetSemanticStore()->getDirectSuperProperties($superProp);
            $superProp = NULL;
            $maxTry--;
           
        } while (!empty($superProps) || $maxTry == 0);
        $i++;
    }
    
    return $propertyQueries;
 }
 
 
 function createLiteralMatchQuery() {
 	$prefixes = getStandardPrefixes();
 	$literalMatchingQueries = array();
 	for($i = 0; $i < 20; $i++) {
 	  printProgress($i / 20);
      list($a, $v) = getArbitraryAttributeAnnotation();
      if (is_numeric($v)) {
      	 $literalMatchingQueries[] = $prefixes."SELECT ?x WHERE {?x prop:".$a->getDBkey()." \"$v\"^^<http://www.w3.org/2001/XMLSchema#float>. }";
      } 
      else {
      	 $literalMatchingQueries[] = $prefixes."SELECT ?x WHERE {?x prop:".$a->getDBkey()." \"$v\". }";
      }
 	}
 	return $literalMatchingQueries;
 }
 
 function createPathQuery() {
 	$prefixes = getStandardPrefixes();
    $pathQueries = array();
    for($i = 0; $i < 20; $i++) {
      printProgress($i / 20);
      $graph = getRelationGraph(MAX_DEPTH);
      $q = $prefixes."SELECT ?x WHERE {";
      $j = 0;
      foreach($graph as $n) {
     	 list($s, $p, $o) = $n;
     	 $q .= getVar($j)." prop:".$p->getDBkey()." ".getVar($j+1).".";
     	 $j++;
      }
      $q .= "}";
      $pathQueries[] = $q;
    }
    return $pathQueries;
 }
 
 function createSymPropQueries() {
 	$prefixes = getStandardPrefixes();
    $smyPropQueries = array();
    $symAnnot = getSymmetricalAnnotations(20);
    $i = 0;
    foreach($symAnnot as $a) {
      list($s, $p, $o) = $a;
      printProgress($i / 20);
      
      $smyPropQueries[] = $prefixes."SELECT ?x WHERE { a:".$o->getDBkey()." prop:".$p->getDBkey()." ?x. }";
      $i++;
    }
    return $smyPropQueries;
 }
 
 function createTransPropQueries() {
 	$prefixes = getStandardPrefixes();
    $transPropQueries = array();
    $transAnnot = getTransitiveAnnotations(20);
    $i = 0;
    foreach($transAnnot as $a) {
      list($s, $p, $o) = $a;
      printProgress($i / 20);
      
      $transPropQueries[] = $prefixes."SELECT ?x WHERE { a:".$s->getDBkey()." prop:".$p->getDBkey()." ?x. }";
      $i++;
    }
    return $transPropQueries;
 }
 
 function createInvPropQueries() {
    $prefixes = getStandardPrefixes();
    $invPropQueries = array();
    $invAnnot = getInverseAnnotations(20);
    $i = 0;
    foreach($invAnnot as $a) {
      list($s, $p, $o, $inv_p) = $a;
      printProgress($i / 20);
      
      $invPropQueries[] = $prefixes."SELECT ?x WHERE { a:".$s->getDBkey()." prop:".$p->getDBkey()." ?x. }";
      $i++;
    }
    return $invPropQueries;
 }
 
 /*function getAnnoationValuesForProperty($category, $property, $minNumberOfValues) {
 	  $values = array();
 	  $prop_subjects = smwfGetStore()->getAllPropertySubjects($property);
 	  
 	  foreach($prop_subjects as $subject) {
 	  	$smwValues = smwfGetStore()->getPropertyValues($subject, $property);
 	  	foreach($smwValues as $v) {
 	  		if ($v instanceof SMWStringValue) {
 	  			$values[] = $v;
		 	  	if (count($values) >= $minNumberOfValues) return $values;
 	  		}
 	  	}
        	  	
 	  }
 	  return $values;
 }*/
 
/**
 * Chooses an arbitrary instance of $category and returns a (value,property) tuple array
 *
 * @param Title $category
 * @param int $minNumberOfValues
 * @return array of Tuples (SMWDataValue, Title)
 */ 
function getSampleAnnotationValues($category, $minNumberOfValues) {
      $values = array();
      $instances = smwfGetSemanticStore()->getInstances($category);
      $instances_num = count($instances);
      if ($instances_num == 0) return $values;
      $subject = $instances[intval(rand(0, $instances_num-1))];
      $properties = smwfGetStore()->getProperties($subject[0]);
      
      foreach($properties as $p) {
        $smwValues = smwfGetStore()->getPropertyValues($subject[0], $p);
        $v = $smwValues[intval(rand(0, count($smwValues)-1))];
            if ($v instanceof SMWStringValue) {
                $values[] = array($v,$p);
                if (count($values) >= $minNumberOfValues) return $values;
            }
        
       
      }
      return $values;
 }
 

 // ----------------- DB helper functions --------------------------------------------------------------------------
 /**
  * Returns $num category leaves.
  * 
  * @return array of Title
  */
 function getLeafCategories($num) {
    $results=array(); 
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT DISTINCT page_title FROM page p WHERE p.page_namespace =14 AND page_is_redirect = 0 AND NOT EXISTS (SELECT cl_from FROM page p2, categorylinks WHERE p2.page_namespace = 14 AND cl_from = p2.page_id AND cl_to = p.page_title) ORDER BY RAND() LIMIT '.$num);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $results[] = Title::newFromText($row->page_title, NS_CATEGORY);
                
            }
        }
    $db->freeResult($res);
    return $results; 
 }
 
  /**
  * Returns $num property leaves.
  * 
  * @return array of Title
  */
 function getLeafProperties($num) {
    $results=array(); 
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT DISTINCT page_title FROM page p WHERE p.page_namespace = 102 AND page_is_redirect = 0 AND NOT EXISTS (SELECT subject_title FROM page p2, smw_subprops WHERE p2.page_namespace = 102 AND subject_title = p2.page_title AND object_title = p.page_title) ORDER BY RAND() LIMIT '.$num);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $results[] = Title::newFromText($row->page_title, SMW_NS_PROPERTY);
                
            }
        }
    $db->freeResult($res);
    return $results; 
 }
 
 function getArbitraryAttributeAnnotation() {
 	$result=NULL; 
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT attribute_title, value_xsd FROM smw_attributes ORDER BY RAND() LIMIT 1');
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result = array(Title::newFromText($row->attribute_title, SMW_NS_PROPERTY), $row->value_xsd);
                
            }
        }
    $db->freeResult($res);
    return $result; 
 }
 
function getArbitraryInstances($limit) {
    $result=array(); 
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT page_title FROM page WHERE page_namespace = 0 AND page_is_redirect = 0 ORDER BY RAND() LIMIT '.$limit);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] =Title::newFromText($row->page_title);
                
            }
        }
    $db->freeResult($res);
    return $result; 
 }
 
function getArbitraryProperties($limit) {
   $result=array(); 
    $db = wfGetDB(DB_MASTER);
   $res = $db->query('SELECT page_title FROM page WHERE page_namespace = 102 AND page_is_redirect = 0 ORDER BY RAND() LIMIT '.$limit);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] =Title::newFromText($row->page_title, SMW_NS_PROPERTY);
                
            }
        }
    $db->freeResult($res);
    return $result; 
 }
 
function getArbitraryRelations($limit) {
    $result=array(); 
    $db = wfGetDB(DB_MASTER);
   $res = $db->query('SELECT DISTINCT relation_title FROM smw_relations ORDER BY RAND() LIMIT '.$limit);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] =Title::newFromText($row->relation_title, SMW_NS_PROPERTY);
                
            }
        }
    $db->freeResult($res);
    return $result; 
 }
 
 function getRelationGraph($depth) {
 	$result=NULL; 
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT subject_title, relation_title, object_title FROM smw_relations ORDER BY RAND() LIMIT 1');
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result = array($row->subject_title, Title::newFromText($row->relation_title, SMW_NS_PROPERTY), $row->object_title);
                
            }
        }
    $db->freeResult($res);
    $graph = array();
  
    _getRelationGraph($result, $graph, $depth);
    return $graph; 
 }
 
function _getRelationGraph($next, & $graph, $depth) {
	if ($depth == 0) return true;
	array_push($graph, $next);
    $result=NULL; 
    $db = wfGetDB(DB_MASTER);
    $maxTry = 10;
    do {
	    $res = $db->query('SELECT subject_title, relation_title, object_title FROM smw_relations WHERE subject_title = '.$db->addQuotes($next[2]).' ORDER BY RAND() LIMIT 1');
	    if($db->numRows( $res ) > 0) {
	            while($row = $db->fetchObject($res)) {
	                $result = array($row->subject_title, Title::newFromText($row->relation_title, SMW_NS_PROPERTY), $row->object_title);
	               
	            }
	        }
	    $db->freeResult($res);
	    $maxTry--;
	    
    } while ($result == NULL && $maxTry > 0);
    if ($maxTry == 0) {
        return NULL;
    }
    return (_getRelationGraph($result, $graph, $depth--));
   
 }
 
 function getSymmetricalAnnotations($limit) {
 	$result=array();
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT subject_title, relation_title, object_title FROM page JOIN categorylinks ON page_id = cl_from AND page_namespace = 102 JOIN smw_relations ON relation_title = page_title WHERE cl_to='.$db->addQuotes('Symmetrical_properties').' ORDER BY RAND() LIMIT '.$limit);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array(Title::newFromText($row->subject_title, NS_MAIN),Title::newFromText($row->relation_title, SMW_NS_PROPERTY),Title::newFromText($row->object_title, NS_MAIN));
                
            }
        }
    $db->freeResult($res);
    return $result; 
 }
 
 function getTransitiveAnnotations($limit) {
    $result=array();
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT subject_title, relation_title, object_title FROM page JOIN categorylinks ON page_id = cl_from AND page_namespace = 102 JOIN smw_relations ON relation_title = page_title WHERE cl_to='.$db->addQuotes('Transitive_properties').' ORDER BY RAND() LIMIT '.$limit);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array(Title::newFromText($row->subject_title, NS_MAIN),Title::newFromText($row->relation_title, SMW_NS_PROPERTY),Title::newFromText($row->object_title, NS_MAIN));
                
            }
        }
    $db->freeResult($res);
    return $result; 
 }
 
function getInverseAnnotations($limit) {
	$result=array();
	$db = wfGetDB(DB_MASTER);
    $invProps = getInverseProperties(20);
    foreach($invProps as $p) {
    	$res = $db->query('SELECT subject_title, relation_title, object_title FROM smw_relations WHERE relation_title='.$db->addQuotes($p->getDBkey()).' ORDER BY RAND() LIMIT 1');
        if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = array(Title::newFromText($row->subject_title, NS_MAIN),Title::newFromText($row->relation_title, SMW_NS_PROPERTY),Title::newFromText($row->object_title, NS_MAIN), $p);
                
            }
        }
    $db->freeResult($res);
    }
}

function getInverseProperties($limit) {
	$result=array(); 
    $db = wfGetDB(DB_MASTER);
    $res = $db->query('SELECT subject_title FROM smw_relations WHERE relation_title='.$db->addQuotes('Is_inverse_of').' ORDER BY RAND() LIMIT '.$limit);
    if($db->numRows( $res ) > 0) {
            while($row = $db->fetchObject($res)) {
                $result[] = Title::newFromText($row->relation_title, SMW_NS_PROPERTY);
                
            }
        }
    $db->freeResult($res);
    return $result; 
}
 
 // -------- modifies database! ----------------------
 // contains method to add the following annotations:
 // 1. Inverse properties
 // 2. Symetrical properties
 // 3. Transitive properties
 
 /**
  * Adds symetrical properties
  *
  */
 function addSymmetricalProperties() {
 	$properties = getArbitraryRelations(25);
 	foreach($properties as $p) {
	 	 $a = new Article($p);
	     $r = Revision::newFromTitle($p);
	        
	     $a->doEdit($r->getText()."\n[[category:Symmetrical properties]]", "", EDIT_UPDATE | EDIT_FORCE_BOT);
 	}    
 }
 
 /**
  * Adds transitive properties and annotations
  *
  */
 function addTransitiveProperties() {
 	$result[] = array();
 	$properties = getArbitraryRelations(25);
    foreach($properties as $p) {
    	addText($p, "\n[[category:Transitive properties]]");
    	$inst1 = getArbitraryInstances(1);
        $inst2 = getArbitraryInstances(1);
    	$start = $inst1[0];
        for($i = 0; $i < 3; $i++) {
         	addText($inst1[0], "\n[[".$p->getDBkey()."::".$inst2[0]->getDBkey()."]]");
         	$inst1 = $inst2;
         	$inst2 = getArbitraryInstances(1);
        }
        $end = $inst1[0];
        $result[] = array($start, $end, $p);
    }   
    return $result;
 	
 }
 
 /**
  * Adds inverse properties
  *
  */
 function addInverseProperties() {
 	$properties = getArbitraryRelations(25);
    foreach($properties as $p) {
         $someprop = getArbitraryProperties(1);   
    	 addText($p, "\n[[Is inverse of::Property:".$someprop[0]->getDBkey()."]]");
    }   
 }
 
function addText($title, $text) {
     $a = new Article($title);
     $r = Revision::newFromTitle($title);
     $a->doEdit($r->getText()."\n".$text, "", EDIT_UPDATE | EDIT_FORCE_BOT);    
}
?>