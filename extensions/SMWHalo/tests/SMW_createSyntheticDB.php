<?php
/*
 * Generates a synthetic DB for performance tests 
 * 
 * Usage:
 * 
 * 	php createSyntheticDB [options]
 * 
 * Example: php createSyntheticDB --dom_cov=0.8
 * 
 * Every options which is not specified is used with its default value.
 * 
 * Created on 14.02.2008
 * Author: kai
 */
 
 // constants which describe DB content (defaults, may be overidden at command line)
 
 // number of instances, categories and properties
 define('num_insts', 1000);
 define('num_cats', 100);
 define('num_props', 150);
 
 // balance factor for trees: 0 not balanced, 1 full balanced.
 define('bal_cat', 0.8);
 define('bal_props', 0.8);
 
 // depth of trees
 define('depth_cat', 4);
 define('depth_prop', 2);
 
 // instances distribution: 0 equal distribution over all categories, 
 // 1 all instances are members of leaf categories
 define('inst_dist', 0.8);  
 
 // percentage of properties which are datatype properties
 define('data_prop_freq', 0.3);
 
 // percentage of datatype properties which are strings.
 define('data_prop_string', 0.8);
 
 // max. number how often a property is annotated on a page
 define('prop_fac', 5);
 
 // percentage of properties which have domains, min cards, max cards, annotations, redirects 
 define('dom_cov', 0.7);  
 define('max_card_cov', 0.1);  
 define('min_card_cov', 0.1);  
 define('annot_cov', 0.2);  
 define('red_cov', 0.01);  
 
 // number of leaf categories selected for generating queries
 define('queries', 50);
 // max. number of properties used in a query
 define('query_prop', 4);
 
 // percentage of pages with blindtext (for every size)
 define('blindtext_cov', 0.05);

   
 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";
 
 // set command line options 
 foreach($options as $option => $value) {
 	define($option, $value);
 }
 
 $cat_counter = 0;
 $inst_counter = 0;
 $prop_counter = 0;
 
 $blindTexts = array();
 
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
 
 
 function createBlindText() {
 	global $blindTexts;
 	$bt = "= Heading 1 =\n" .
 		  "== Heading 2 ==\n" .
 		  "*Bullet-point 1\n" .
 		  "*Bullet-point 2\n" .
 		  "*Bullet-point 3\n" .
 		  "----\n" .
 		  "Deutschland ist ein in Mitteleuropa gelegener Bundesstaat, der aus den 16 deutschen Ländern gebildet wird. " .
 		  "Bundeshauptstadt ist Berlin. Die Bundesrepublik Deutschland ist Gründungsmitglied der Europäischen Union und " .
 		  "mit über 82 Millionen Einwohnern deren bevölkerungsreichster Staat, ferner unter anderem Mitglied der Vereinten Nationen, " .
 		  "der OECD, der NATO, der OSZE und der Gruppe der Acht (G8). " .
 		  "Gemessen am Bruttoinlandsprodukt ist Deutschland die drittgrößte Volkswirtschaft der Welt. " .
 		  "Die naturräumlichen Großregionen sind von Nord nach Süd Norddeutsches Tiefland, Mittelgebirgszone und Alpenvorland mit Alpen. " .
 		  "Deutschland hat insgesamt neun Nachbarstaaten: Dänemark, Polen, Tschechien, Österreich, die Schweiz, Frankreich, Luxemburg, Belgien und die Niederlande. " .
 		  "Während der wechselvollen Geschichte veränderte sich auch der Mittelpunkt Deutschlands.\n".
 		  "=== Heading 3 ===\n" .
 		  "#Enumeration 1\n" .
 		  "#Enumeration 2\n" .
 		  "#Enumeration 3\n" .
 		  "#Enumeration 4\n" .
 		  "----" .
 		  "ENDE\n"; // has 1024 chars (= 1kb)
 	for($i = 0; $i < 7; $i++) { // generate 1 kb to 64 kb of text
 		$bt .= $bt;
 		$blindTexts[] = $bt;
 	}
 	
 }
 
 function createID() {
 	return uniqid(rand());
 }
 
 
 
 function createCategory($superCat, $new_cat) {
 	
 	$title = Title::newFromText($new_cat, NS_CATEGORY);
 	if ($title->exists()) return; // should not happen
 	$a = new Article($title);
 	if ($superCat != NULL)  {
 		$a->insertNewArticle("[[category:$superCat]]", "", false, false);	
 	} else {
 		$a->insertNewArticle("Root category", "", false, false);
 	}
 	
 	//print "Insert Category:$new_cat as sub category of [[category:$superCat]].\n";
 }
 
 function createProperty($superProp, $new_prop) {
 	
 	$title = Title::newFromText($new_prop, SMW_NS_PROPERTY);
 	if ($title->exists()) return; // should not happen
 	$a = new Article($title);
 	$texttoinsert = "";
 	if ($superProp != NULL)  {
 		$texttoinsert .= "[[Subproperty of::Property:$superProp]]\n";
 	} else {
 		$texttoinsert .= "Root property\n";
 	}
 	$isBinary = false;
 	if (rand(0,1) < data_prop_freq) {
 		if (rand(0,1) < data_prop_string) {
 			$texttoinsert .= "[[has type::Type:String]]\n";
 		} else {
 			$texttoinsert .= "[[has type::Type:Number]]\n";
 		}
 		
 	} else {
 		$isBinary = true;
 		$texttoinsert .= "[[has type::Type:Page]]\n";
 	}
 	if (rand(0,1) < dom_cov) {
 		list($domain, $range) = getDomainAndRange();
 		if ($isBinary) {
 			$texttoinsert .= "[[has domain and range::".$domain->getPrefixedText().";".$range->getPrefixedText()."]]\n";
 		} else {
 			$texttoinsert .= "[[has domain and range::".$domain->getPrefixedText()."]]\n";
 		}
 	}
 	if (rand(0,1) < max_card_cov) {
 		$texttoinsert .= "[[has max cardinality::".intval(rand(0,5))."]]\n";
 	}
 	if (rand(0,1) < min_card_cov) {
 		$texttoinsert .= "[[has min cardinality::".intval(rand(0,5))."]]\n";
 	}
 	$a->insertNewArticle($texttoinsert, "", false, false);	
 	//print "Insert Property:$new_prop as sub property of Property:$superProp.\n";
 }
 
 function createInstance($category, $id) {
 	
 	$title = Title::newFromText($id, NS_MAIN);
 	if ($title->exists()) return; // should not happen
 	$a = new Article($title);
 	if ($category != NULL)  {
 		$a->insertNewArticle("[[category:$category]]", "", false, false);	
 	} 
 	//print "Insert instance: $id as member of category:$category.\n";
 }
 
 function createCategoryQueries() {
 	$categoryQueries = array();
 	$leaves = getLeafCategories(queries);
 	$i = 0;
 	$superCats = array();
 	foreach($leaves as $leaf) {
 			printProgress($i / (count($leaves)));
 			
	 	do {
	 		$superCat = !empty($superCats) ? $superCats[0] : $leaf;
	 		$categoryQueries[] = "<ask>[[Category:".$superCat->getText()."]]</ask>";
	 		
	 		$superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
	 	} while (!empty($superCats));
	 	$i++;
	 	$categoryQueries[] = NULL;
 	}
 	return $categoryQueries;
 }
 
 function createCategoryPropertyQueries() {
 	$categoryQueries = array();
 	$leaves = getLeafCategories(queries);
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
	 			$property_restr .= "[[".$p->getText()."::*]]";
	 			if ($j >= query_prop) break;
	 			$j++;
	 		}
	 		
	 		$categoryQueries[] = "<ask>[[Category:".$superCat->getText()."]]$property_restr</ask>";
	 		$superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
	 	} while (!empty($superCats));
	 	$i++;
	 	$categoryQueries[] = NULL;
 	}
 	return $categoryQueries;
 }
 
 function createCategoryPropertyWithConstraintQueries() {
 	$categoryQueries = array();
 	$leaves = getLeafCategories(queries);
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
	 			if ($type == '_num') {
	 				$property_restr .= "[[".$p->getText()."::>50]]";
	 			} else {
	 				
	 				$property_restr .= "[[".$p->getText()."::*]]";
	 			}
	 			if ($j >= query_prop) break;
	 			$j++;
	 		}
	 		
	 		$categoryQueries[] = "<ask>[[Category:".$superCat->getText()."]]$property_restr</ask>";
	 		$superCats = smwfGetSemanticStore()->getDirectSuperCategories($superCat);
	 	} while (!empty($superCats));
	 	$categoryQueries[] = NULL;
	 	$i++;
 	}
 	return $categoryQueries;
 }
 
 function createPropertyQueries() {
 	$propertyQueries = array();
 	$leaves = getLeafProperties(queries);
 	$i = 0;
 	$superProps = array();
 	foreach($leaves as $leaf) {
 			printProgress($i / (count($leaves)));
 			
	 	do {
	 		$superProp = !empty($superProps) ? $superProps[0] : $leaf;
	 		$propertyQueries[] = "<ask>[[".$superProp->getText()."::*]]</ask>";
	 		
	 		$superProps = smwfGetSemanticStore()->getDirectSuperProperties($superProp);
	 	} while (!empty($superProps));
	 	$i++;
 	}
 	$propertyQueries[] = NULL;
 	return $propertyQueries;
 }
 
 function addInstances($category, $depth) {
 	global $inst_counter;
 	if ($inst_counter > num_insts) return;
 	$lh = rand(0,1);
 	if ($depth == depth_cat-1) {
 		// category leaf
 		if ($lh < inst_dist) {
 			$num_inst = rand(0, (1/(1-inst_dist))*num_insts / num_cats);
 			for ($i = 0; $i < $num_inst; $i++) {
 				createInstance($category, createID());
 			}
 			$inst_counter += $num_inst;
 		}
 	} else {
 		if ($lh > inst_dist) {
 			createInstance($category, createID());
 			$inst_counter++;
 		}
 	}
 }
 
 function addCategoryTree($superCat, $depth) {
 	global $cat_counter, $inst_counter;
 	printProgress(($inst_counter + $cat_counter) / (num_cats + num_insts));
 	if ($depth >= depth_cat || $cat_counter >= num_cats) return;
 	$splitfactor = pow(num_cats, 1/depth_cat);
 	$cats = array();
 	if (rand(0,1) < bal_cat) {
 		for($i = 0; $i < $splitfactor; $i++) {
 			
 			$new_cat = createID();
 			$cats[] = $new_cat;
 			
 			createCategory($superCat, $new_cat);
 			addInstances($new_cat, $depth);
 			$cat_counter++;
 		}	
 		foreach($cats as $c) {
 			addCategoryTree($c, $depth+1);
 		}
 		
 	} else {
 		for($i = 0; $i < $splitfactor + rand(-2,2); $i++) {
 			
 			$new_cat = createID();
 			$cats[] = $new_cat;
 		
 			createCategory($superCat, $new_cat);
 			addInstances($new_cat, $depth);
 			$cat_counter++;
 		}	
 		foreach($cats as $c) {
 			addCategoryTree($c, $depth+1);
 		}
 	}
 }
 
  function addPropertyTree($superProp, $depth) {
  	global $prop_counter;
  	printProgress(($prop_counter) / (num_props));
 	if ($depth >= depth_prop || $prop_counter >= num_props) return;
 	$splitfactor = pow(num_props, 1/depth_prop);
 	$cats = array();
 	if (rand(0,1) < bal_cat) {
 		for($i = 0; $i < $splitfactor; $i++) {
 		
 			$new_prop = createID();
 			$cats[] = $new_prop;
 			
 			createProperty($superProp, $new_prop);
 			$prop_counter++;
 		}	
 		foreach($cats as $c) {
 			addPropertyTree($c, $depth+1);
 		}
 		
 	} else {
 		for($i = 0; $i < $splitfactor + rand(-2,2); $i++) {
 			
 			$new_prop = createID();
 			$cats[] = $new_prop;
 		
 			createProperty($superProp, $new_prop);
 			$prop_counter++;
 		}	
 		foreach($cats as $c) {
 			addPropertyTree($c, $depth+1);
 		}
 	}
 }
 
 
 function addAnnotations() {
 	global $smwgIP;
 	$db = wfGetDB(DB_MASTER);
 	require_once($smwgIP . '/includes/storage/SMW_Store.php');
 	$requestoptions = new SMWRequestOptions();
 	$requestoptions->limit = intval(rand(0,5));
 	$res = $db->query('SELECT page_title FROM page WHERE page_namespace = '.NS_MAIN.' ORDER BY RAND() LIMIT '.intval(num_insts * annot_cov));
 	$total = $db->numRows( $res );
 	if( $total > 0) {
 		$i = 0;
 		while ($row = $db->fetchObject($res)) {
 			if ($row->page_title == '' || $row->page_title == NULL) continue;
 			printProgress($i / ($total));
 			$annotationsToAdd = "";
 			$instance = Title::newFromText($row->page_title, NS_MAIN);
 			$categoriesForInstance = smwfGetSemanticStore()->getCategoriesForInstance($instance);
 			if (count($categoriesForInstance) == 0) continue;
 			
 			$propertiesOfCatgeory = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory($categoriesForInstance[0], $requestoptions);
 			
 			if (count($propertiesOfCatgeory) == 0)  {
 				$propertiesOfCatgeory = getRandomProperties();
 			
 			 			 			
	 			foreach($propertiesOfCatgeory as $p) {
		 				
		 			$type = smwfGetStore()->getSpecialValues($p, SMW_SP_HAS_TYPE);
					if (count($type) == 0) continue;
		 			for($j = 0; $j < prop_fac; $j++) {
		 				if ($type[0]->getXSDValue() == '_str') {
		 					$annotationsToAdd .= "[[".$p->getText()."::".getStringValue()."]]\n"; 
		 				} else if ($type[0]->getXSDValue() == '_num') {
		 					$annotationsToAdd .= "[[".$p->getText()."::".intval(rand(0,100))."]]\n"; 
		 				} else if ($type[0]->getXSDValue() == '_wpg') {
		 					$annotationsToAdd .= "[[".$p->getText()."::".getInstanceValue()->getText()."]]\n";
		 				}
		 			}
 				}
 			} else {
 				foreach($propertiesOfCatgeory as $prop) {
 					
		 			list($p, $minCard, $maxCard, $type, $symCat, $transCat, $range) = $prop;
		 			
		 			$type = smwfGetStore()->getSpecialValues($p, SMW_SP_HAS_TYPE);
					if (count($type) == 0) continue;
		 			for($j = 0; $j < prop_fac; $j++) {
		 				if ($type[0]->getXSDValue() == '_str') {
		 					$annotationsToAdd .= "[[".$p->getText()."::".getStringValue()."]]\n"; 
		 				} else if ($type[0]->getXSDValue() == '_num') {
		 					$annotationsToAdd .= "[[".$p->getText()."::".intval(rand(0,100))."]]\n"; 
		 				} else if ($type[0]->getXSDValue() == '_wpg') {
		 					$annotationsToAdd .= "[[".$p->getText()."::".getInstanceValue()->getText()."]]\n";
		 				}
		 			}
 				}
 				
 			}
 			
 			$a = new Article($instance);
 			$r = Revision::newFromTitle($instance);
 			$a->doEdit($r->getText()."\n".$annotationsToAdd, "", EDIT_UPDATE | EDIT_FORCE_BOT);
 			
 			$i++;
 		}
 	}
 	$db->freeResult($res);
 }
 
 
 
 function addRedirects() {
 	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT page_title, page_namespace FROM page ORDER BY RAND() LIMIT '.intval(num_insts * red_cov));
 	$total = $db->numRows( $res );
 	if( $total > 0) {
	 	$i = 0;
	 	while ($row = $db->fetchObject($res)) {
	 		printProgress($i / ($total));
	 		$newtitle = Title::newFromText(createID(), $row->page_namespace);
		 	if ($newtitle->exists()) return; // should not happen
		 	$a = new Article($newtitle);
		 	$toTitle = Title::newFromText($row->page_title, $row->page_namespace);
		 	if ($toTitle != NULL)  {
				$a->insertNewArticle("#REDIRECT[[".$toTitle->getPrefixedText()."]]", "", false, false);	
		 	} 
		 	$i++;
		}
 	}
 	$db->freeResult($res);
 }
 
 
 
 /**
  * Add blindtext to $pages with a size of 2^$size kb
  * 
  * @param $pages array of Title
  * @param $size size of page (2^$size kb)
  * @param $random random size if true.
  */
 function addBlindtext($pages, $size, $random = false) {
 	global $blindTexts;
 
 	$i = 0;
 	foreach($pages as $newtitle) {
 	 	printProgress($i / (count($pages)));
	     $size = $random ? rand(0,6) : $size;
		 addText($newtitle, $blindTexts[$size]);
		 $blindTextPages[] = $newtitle;
		 $i++;
	
 	}
  	
 }
 
 function addText($title, $text) {
 	
 	 $a = new Article($title);
	 $r = Revision::newFromTitle($title);
		
	 $a->doEdit($r->getText()."\n".$text, "", EDIT_UPDATE | EDIT_FORCE_BOT);	
	
 }
 
 
 
 function addQueryPages($queries) {
 	$results = array();
 	$i=0;
 	foreach($queries as $q) {
 		printProgress($i / (count($queries)));
	 	$titlename = createID();
	 	$title = Title::newFromText($titlename, NS_MAIN);
	 	$results[] = $q == NULL ? NULL : $title;
	 	if ($title->exists()) return; // should not happen
	 	$a = new Article($title);
	 	if ($q != NULL)  {
	 		$a->insertNewArticle($q."\n[[category:Query]]", "", false, false);	
	 	}
	 	$i++;
 	}
 	return $results; 
 }
 
  function addLinkPage($pages, $pagelistname) {
  	$links = "";
	 foreach($pages as $page) {
	 	if ($page == NULL) {
	 		$links .= "----\n";
	 	} else if ($page->getNamespace() == NS_CATEGORY) {
	 		$links .= "*Pagelink:[[:".$page->getPrefixedText()."]]\n";
	 	} else {
	 		$links .= "*Pagelink:[[".$page->getPrefixedText()."]]\n";
	 	}
	 }
	 $testTitle = Title::newFromText($pagelistname);
	 $testArticle = new Article($testTitle);
	 if ($testTitle->exists()) {
	 	$testArticle->doEdit($links, "", EDIT_UPDATE | EDIT_FORCE_BOT);
	 } else {
	 	$testArticle->insertNewArticle($links, "", false, false);
	 }
  } 
 /**
  * Returns all category leaves.
  * 
  * @return array of Title
  */
 function getLeafCategories($num) {
 	$results=array(); 
 	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT DISTINCT page_title FROM page p WHERE p.page_namespace =14 AND page_is_redirect = 0 AND NOT EXISTS (SELECT cl_from FROM page p2, categorylinks WHERE cl_from = p2.page_id AND cl_to = p.page_title) ORDER BY RAND() LIMIT '.$num);
    if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$results[] = Title::newFromText($row->page_title, NS_CATEGORY);
				
			}
		}
	$db->freeResult($res);
	return $results; 
 }
 
 function getLeafProperties($num) {
 	$results=array(); 
 	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT DISTINCT page_title FROM page p WHERE p.page_namespace = 102 AND page_is_redirect = 0 AND NOT EXISTS (SELECT subject_title FROM page p2, smw_subprops WHERE subject_title = p2.page_title AND object_title = p.page_title) ORDER BY RAND() LIMIT '.$num);
    if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$results[] = Title::newFromText($row->page_title, SMW_NS_PROPERTY);
				
			}
		}
	$db->freeResult($res);
	return $results; 
 }
 
 function getDomainAndRange() {
 	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT page_title FROM page WHERE page_namespace = '.NS_CATEGORY.' ORDER BY RAND() LIMIT 2');
    if($db->numRows( $res ) > 0) {
		$row1 = $db->fetchObject($res);
		$row2 = $db->fetchObject($res);
		$db->freeResult($res);
		return array(Title::newFromText($row1->page_title, NS_CATEGORY), Title::newFromText($row2->page_title, NS_CATEGORY));
	}
	$db->freeResult($res);
	return NULL; // should never happen.
  }
  
  function getInstanceValue() {
  	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT page_title FROM page WHERE page_namespace = '.NS_MAIN.' ORDER BY RAND() LIMIT 1');
    if($db->numRows( $res ) > 0) {
    	$row = $db->fetchObject($res);
    	$db->freeResult($res);
    	return Title::newFromText($row->page_title, NS_MAIN);
    }
    $db->freeResult($res);
    return NULL; // should never happen.
  }
  
  function getStringValue() {
  	 return chr(rand(0,25)+65).chr(rand(0,25)+65).rand(0,10);
  }
  
  /**
  * Returns $num arbitrary pages.
  * 
  * @return array of Title  
  */
 function getRandomPages($num) {
 	$results = array();
 	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT page_title, page_namespace FROM page ORDER BY RAND() LIMIT '.intval($num));
 	if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$results[] = Title::newFromText($row->page_title, $row->page_namespace);
				
			}
		}
		$db->freeResult($res);
 	
 	return $results;
 }
 
 function getRandomProperties() {
 	$db = wfGetDB(DB_MASTER);
 	$results = array();
 	$res = $db->query('SELECT page_title FROM page WHERE page_namespace = '.SMW_NS_PROPERTY.' ORDER BY RAND() LIMIT '.intval(rand(0,5)));
 	if($db->numRows( $res ) > 0) {
	 	while ($row = $db->fetchObject($res)) {
	 		$results[] = Title::newFromText($row->page_title, SMW_NS_PROPERTY);
	 	}
 	}
 	$db->freeResult($res);
 	return $results;
 }
 
 
 // main program
 
 // initialize
 createBlindText();
 
 print "Generating content...\n";
 
 // add category tree and instances
 print "Categories and instances...";
 addCategoryTree(NULL, 0);
 printProgress(1);
 print "\n";
 
 // add property tree
 print "Properties...";
 addPropertyTree(NULL, 0);
 printProgress(1);
 print "\n";
 
 // add annotations for instances
 print "Adding Annotations...";
 addAnnotations();
 printProgress(1);
 print "\n";
 
 // add redirects for arbitrary articles
 print "Redirects...";
 addRedirects();
 printProgress(1);
 print "\n";
 
 // get random pages for blindtexts
 $blindTextPages = getRandomPages(num_insts * blindtext_cov * 5);
 $partitions = array_chunk($blindTextPages, num_insts * blindtext_cov);
 // add blindtext for arbitrary articles
 print "Adding blind text 64kb...";
 $blindTextPages = addBlindtext($partitions[0], 5, false);
 printProgress(1);
 addLinkPage($partitions[0], "Pages with 64kb blind text"); 
 print "\n";
 
 print "Adding blind text 32kb...";
 $blindTextPages = addBlindtext($partitions[1], 4, false);
 printProgress(1);
 addLinkPage($partitions[1], "Pages with 32kb blind text"); 
 print "\n";
 
 print "Adding blind text 16kb...";
 $blindTextPages = addBlindtext($partitions[2], 3, false);
 printProgress(1);
 addLinkPage($partitions[2], "Pages with 16kb blind text"); 
 print "\n";
 
 print "Adding blind text 8kb...";
 $blindTextPages = addBlindtext($partitions[3], 2, false);
 printProgress(1);
 addLinkPage($partitions[3], "Pages with 8kb blind text"); 
 print "\n";
 
 print "Adding blind text 4kb...";
 $blindTextPages = addBlindtext($partitions[4], 1, false);
 printProgress(1);
 addLinkPage($partitions[4], "Pages with 4kb blind text"); 
 print "\n";
 
 print "Adding category query pages...";
 $categoryQueries = createCategoryQueries();
 printProgress(1);
 print "\n";
 
 $categoryQueryPages = addQueryPages($categoryQueries);
 addLinkPage($categoryQueryPages, "Pages with category queries"); 
 printProgress(1);
 print "\n";
 
 print "Adding property query pages...";
 $propertyQueries = createPropertyQueries();
 printProgress(1);
 print "\n";
 
 $propertyQueriesPages = addQueryPages($propertyQueries);
 addLinkPage($propertyQueriesPages, "Pages with property queries"); 
 printProgress(1);
 print "\n";
 
  print "Adding category/property query pages...";
 $categoryPropertyQueries = createCategoryPropertyQueries();
 printProgress(1);
 print "\n";
 $categoryQueryPropertyPages = addQueryPages($categoryPropertyQueries);
 addLinkPage($categoryQueryPropertyPages, "Pages with category/property queries"); 
 printProgress(1);
 print "\n";


 print "Adding category/property with constraints query pages...";
 $categoryPropertyQueries = createCategoryPropertyWithConstraintQueries();
 printProgress(1);
 print "\n";
 $categoryQueryPropertyConstraintPages = addQueryPages($categoryPropertyQueries);
 addLinkPage($categoryQueryPropertyConstraintPages, "Pages with category/property constraint queries"); 
 printProgress(1);
 print "\n\n";

 print "Inserted categories: ".$cat_counter."\n";
 print "Inserted properties: ".$prop_counter."\n";
 print "Inserted instances: ".$inst_counter."\n";
 
 print "Add user: LoadTest with password: lt\n";
 $user = User::newFromName("LoadTest");
 $user->setPassword("lt");
 $user->addToDatabase();
 $user->addGroup('sysop');
					
 print "\nTotal number of inserted articles: ".
 	($cat_counter+$prop_counter+$inst_counter+(num_insts*red_cov)+count($categoryQueryPages)+count($propertyQueriesPages)+count($categoryQueryPropertyPages)+count($categoryQueryPropertyConstraintPages));

 print "\nInvalidate all pages"; 
 smwfGetSemanticStore()->invalidateAllPages();
 print "\nFinished.";
 
?>
