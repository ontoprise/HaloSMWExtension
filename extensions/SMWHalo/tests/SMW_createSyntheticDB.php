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
 
 // constants which describe DB content (defaults)
 define('num_insts', 1000);
 define('num_cats', 100);
 define('num_props', 200);
 define('bal_cat', 0.8);
 define('bal_props', 0.8);
 define('depth_cat', 5);
 define('depth_prop', 2);
 define('inst_dist', 0.7);  
 define('data_prop_freq', 0.3);
 define('dom_cov', 0.6);  
 define('max_card_cov', 0.1);  
 define('min_card_cov', 0.1);  
 define('annot_cov', 0.7);  
 define('red_cov', 0.1);  
 define('blindtext_cov', 0.01);
 define('blindtext', 2);  
   
 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";
 
 $dry_run = array_key_exists("dryrun", $options);
 
 foreach($options as $option => $value) {
 	define($option, $value);
 }
 
 $cat_counter = 0;
 $inst_counter = 0;
 $prop_counter = 0;
 
 $blindTexts = array();
 
 function createBlindText() {
 	$bt = "blind text nr 0 "; // has 16 chars
 	for($i = 4; $i < 15; $i++) { // generate 16 bytes to 32 kb of text
 		$bt .= $bt;
 		$blindTexts[] = $bt;
 	}
 }
 
 function createID() {
 	return uniqid(rand());
 }
 
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
 	if ($depth >= depth_cat || $prop_counter >= num_props) return;
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
 
 function createCategory($superCat, $new_cat) {
 	global $dry_run;
 	if ($dry_run) return;
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
 	global $dry_run;
 	if ($dry_run) return;
 	$title = Title::newFromText($new_prop, SMW_NS_PROPERTY);
 	if ($title->exists()) return; // should not happen
 	$a = new Article($title);
 	$texttoinsert = "";
 	if ($superProp != NULL)  {
 		$texttoinsert .= "[[subproperty of:$superProp]]\n";
 	} else {
 		$texttoinsert .= "Root property\n";
 	}
 	$isBinary = false;
 	if (rand(0,1) < data_prop_freq) {
 		$texttoinsert .= "[[has type::Type:String]]\n";
 		
 	} else {
 		$isBinary = true;
 		$texttoinsert .= "[[has type::Type:Page]]\n";
 	}
 	if (rand(0,1) < dom_cov) {
 		list($domain, $range) = getDomainAndRange();
 		if ($isBinary) {
 			$texttoinsert .= "[[has domain and range::".$domain->getText().";".$range->getText()."]]\n";
 		} else {
 			$texttoinsert .= "[[has domain and range::".$domain->getText()."]]\n";
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
 	global $dry_run;
 	if ($dry_run) return;
 	$title = Title::newFromText($id, NS_MAIN);
 	if ($title->exists()) return; // should not happen
 	$a = new Article($title);
 	if ($category != NULL)  {
 		$a->insertNewArticle("[[category:$category]]", "", false, false);	
 	} 
 	//print "Insert instance: $id as member of category:$category.\n";
 }
 
 function addInstances($category, $depth) {
 	global $inst_counter;
 	if ($inst_counter > num_insts) return;
 	$lh = rand(0,1);
 	if ($depth == depth_cat-1) {
 		// category leaf
 		if ($lh < inst_dist) {
 			$num_inst = rand(0, 2*num_insts / num_cats);
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
 
 function addAnnotations() {
 	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT page_title FROM page WHERE page_namespace = '.NS_MAIN.' ORDER BY RAND() LIMIT '.intval(num_insts * annot_cov));
 	$total = $db->numRows( $res );
 	if( $total > 0) {
 		$i = 0;
 		while ($row = $db->fetchObject($res)) {
 			if ($row->page_title == '' || $row->page_title == NULL) continue;
 			printProgress($i / ($total));
 			$annotationsToAdd = "";
 			$res2 = $db->query('SELECT page_title FROM page WHERE page_namespace = '.SMW_NS_PROPERTY.' ORDER BY RAND() LIMIT '.intval(rand(0,5)));
 			if($db->numRows( $res2 ) > 0) {
	 			while ($row2 = $db->fetchObject($res2)) {
	 				if ($row2->page_title == '' || $row2->page_title == NULL) continue;
	 				$property = Title::newFromText($row2->page_title, SMW_NS_PROPERTY);
	 				$type = smwfGetStore()->getSpecialValues($property, SMW_SP_HAS_TYPE);
	 				if (count($type) == 0) continue;
	 				if ($type[0]->getXSDValue() == '_str') {
	 					$annotationsToAdd .= "[[".$property->getText()."::".getStringValue()."]]\n"; 
	 				} else if ($type[0]->getXSDValue() == '_wpg') {
	 					$annotationsToAdd .= "[[".$property->getText()."::".getInstanceValue()->getText()."]]\n";
	 				}
	 			}
	 			
 			}
 			$db->freeResult($res2);
 			$instance = Title::newFromText($row->page_title, NS_MAIN);
 			$a = new Article($instance);
 			$r = Revision::newFromTitle($instance);
 			$a->updateArticle($r->getText()."\n".$annotationsToAdd, "", false, false);
 			//print "Update article: ".$instance->getText()."\n";
 			//print "with: ".$annotationsToAdd."\n";
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
 
 function addBlindtext() {
 	global $blindTexts;
 	$db = wfGetDB(DB_MASTER);
 	$res = $db->query('SELECT page_title, page_namespace FROM page ORDER BY RAND() LIMIT '.intval(num_insts * blindtext_cov));
 	$total = $db->numRows( $res );
 	if( $total > 0) {
	 	$i = 0;
	 	while ($row = $db->fetchObject($res)) {
	 		printProgress($i / ($total));
	 		$newtitle = Title::newFromText($row->page_title, $row->page_namespace);
		 	if ($newtitle->exists()) return; // should not happen
		 	$a = new Article($newtitle);
		 	$r = Revision::newFromTitle($newtitle);
		  	$a->updateArticle($r->getText()."\n".$blindTexts[9+blindtext], "", false, false);	
		 	 
		 	$i++;
		}
 	}
 	$db->freeResult($res);
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
 
 // add blindtext for arbitrary articles
 print "Adding blind text...";
 addBlindtext();
 printProgress(1);
 print "\n\n";

 print "Inserted categories: ".$cat_counter."\n";
 print "Inserted properties: ".$prop_counter."\n";
 print "Inserted instances: ".$inst_counter."\n";
 
 print "\nTotal number of inserted articles: ".
 	($cat_counter+$prop_counter+$inst_counter+(num_insts*red_cov));
 
?>
