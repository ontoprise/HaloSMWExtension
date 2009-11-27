<?php
/*
 * Created on 23.04.2007
 *
 * Author: kai
 *
 * SMWOntologyBrowserFilter provides advanced filtering methods for OntologyBrowser
 *
 * FilterBrowsing means that the user may directly access segments of the semantic model,
 * no matter where they are or if they are expanded.
 * Thus, some methods are needed to expand the relevant part of the model.
 *
 * All methods return XML as results which can be directly rendered by the client browser.
 */
require_once("SMW_OntologyBrowserXMLGenerator.php");

class SMWAdvRequestOptions extends SMWRequestOptions {

	/**
	 * If true, all string constraints will be OR'ed instead of AND'ed.
	 * Default is false.
	 *
	 * @var boolean
	 */
	public $disjunctiveStrings = false;
}
/**
 * SMWOntologyBrowserFilter provides global search mechanisms to access the semantic model.
 * It returns XML data which can directly be displayed in the OntologyBrowser.
 */
class SMWOntologyBrowserFilter {

	/**
	 * Filters for categories containg the given hint as substring (case-insensitive)
	 * Returns the category tree from root to this found entities as xml string.
	 *
	 * @return xml string (category tree)
	 */
	function filterForCategories($categoryHints) {

		$reqfilter = new SMWAdvRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->disjunctiveStrings = true;
		if (count($categoryHints) == 0) {
			return "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>";
		}
		
		//$reqfilter->limit = MAX_RESULTS;
		foreach($categoryHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}
		$reqfilter->isCaseSensitive = false;
		$foundCategories = smwfGetSemanticStore()->getPages(array(NS_CATEGORY), $reqfilter, true);

		return $this->getCategoryTree($foundCategories);
	}
		
	/**
	 * Returns the category tree of all categories the given article is instance of.
	 *
	 * @param $articleTitle article title
	 * @return xml string (category tree)
	 */
	function filterForCategoriesWithInstance(Title $articleTitle, $reqfilter) {
		$categories = smwfGetSemanticStore()->getCategoriesForInstance($articleTitle, $reqfilter);
		return $this->getCategoryTree($categories);
	}
		
	/**
	 * Returns the category tree of all categories the given property has a domain of.
	 *
	 * @param $propertyTitle property title
	 * @return xml string (category tree)
	 */
	function filterForCategoriesWithProperty(Title $propertyTitle, $reqfilter) {
		$categories = smwfGetSemanticStore()->getDomainCategories($propertyTitle, $reqfilter);
		return $this->getCategoryTree($categories);
	}
		
	/**
	 * Filters for instances containg the given hint as substring (case-insensitive)
	 * Returns an instance list with all entities found
	 *
	 * @return xml string
	 */
	function filterForInstances($instanceHints) {
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		//$reqfilter->limit = MAX_RESULTS;
			
		if (count($instanceHints) == 0) {
			return "<instanceList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_instances')."\"/>";
		}
		foreach($instanceHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}
			
		$reqfilter->isCaseSensitive = false;
		$foundInstances = smwfGetSemanticStore()->getPages(array(-NS_CATEGORY), $reqfilter, true);

		$result = "";
		$id = uniqid (rand());
		$count = 0;
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($foundInstances as $instance) {
			$title_esc = htmlspecialchars($instance->getDBkey());
			$namespace = $instance->getNsText();
			$titleURLEscaped = htmlspecialchars(SMWOntologyBrowserXMLGenerator::urlescape($instance->getDBkey()));
			$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $instance);
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
			$result .= "<instance title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" namespace=\"$namespace\" id=\"ID_$id$count\">$gi_issues</instance>";
			$count++;
		}
		return $result == '' ? "<instanceList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_instances')."\"/>"  : '<instanceList>'.$result.'</instanceList>';
	}
		
	/*function filterForInstancesUsingProperty(Title $property) {
	 $instances = smwfGetStore()->getAllRelationSubjects($property);
	 $result = "";
	 foreach($instances as $instance) {
	 $result .= "<instance title=\"".$instance->getDBkey()."\" img=\"$type.gif\" id=\"ID_$id$count\"/>";
	 }
	 return '<instanceList>'.$result.'</instanceList>';
	 }*/
		
	/**
	 * Filters for attribute containg the given hint as substring (case-insensitive)
	 * Returns the attribute tree from root to this found entities as xml string.
	 *
	 * @return xml string (attribute tree)
	 */
	function filterForPropertyTree($propertyHints) {
		$reqfilter = new SMWAdvRequestOptions();
		$reqfilter->sort = true;
		$reqfilter->disjunctiveStrings = true;
			
		if (count($propertyHints) == 0) {
			return "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>";
		}
		foreach($propertyHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}
			
		$reqfilter->isCaseSensitive = false;
		$foundAttributes = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY), $reqfilter, false);


		// create root object
		$root = new TreeObject(null);

		// get all paths to the root
		$allPaths = array();
		$visitedNodes = array();
		foreach($foundAttributes as $cat) {
			$init_path = array();
			$this->getAllPropertyPaths($cat, $init_path, $allPaths, $visitedNodes);
		}

		// reverse paths
		$reversedPaths = array();
		foreach($allPaths as $path) {
			$reversedPaths[] = array_reverse($path);
		}
		 
		// build tree of TreeObjects
		foreach($reversedPaths as $path) {
			$node = $root;
			foreach($path as $c) {
				$node = $node->addChild($c);
			}
		}

		// sort first level
		$root->sortChildren();

		// serialize tree as XML
		$serializedXML = $root->serializeAsXML('propertyTreeElement');
		return $serializedXML == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_attributes')."\"/>" : '<result>'.$serializedXML.'</result>';
		 
	}
		

		
	/**
	 * Filters for properties containg the given hint as substring (case-insensitive)
	 * Returns an property list with all entities found
	 *
	 * @return xml string
	 */
	function filterForProperties($propertyHints) {
		$reqfilter = new SMWRequestOptions();
		$reqfilter->sort = true;
		//$reqfilter->limit = MAX_RESULTS;
			
		if (count($propertyHints) == 0) {
			return "<propertyList isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_properties')."\"/>";
		}
			
		foreach($propertyHints as $hint) {
			$reqfilter->addStringCondition($hint, SMWStringCondition::STRCOND_MID);
		}
			
		$reqfilter->isCaseSensitive = false;
		$foundProperties = smwfGetSemanticStore()->getPropertiesWithSchemaByName($reqfilter);

		return SMWOntologyBrowserXMLGenerator::encapsulateAsPropertyList($foundProperties);
	}
		
	/**
	 * Returns the category tree for the given array of categories.
	 */
	public function getCategoryTree($categories) {
		// create root object
		$root = new TreeObject(null);

		// get all paths to the root
		$allPaths = array();
		$vistedNodes = array(); // used internally to prevent infinite cycles
		foreach($categories as $cat) {
			$init_path = array();
			$this->getAllCategoryPaths($cat, $init_path, $allPaths, $vistedNodes);
		}

		// reverse paths
		$reversedPaths = array();
		foreach($allPaths as $path) {
			$reversedPaths[] = array_reverse($path);
		}
		 
		// build tree of TreeObjects
		foreach($reversedPaths as $path) {
			$node = $root;
			foreach($path as $c) {
				$node = $node->addChild($c);
			}
		}

		// sort first level
		$root->sortChildren();

		// serialize tree as XML
		$serializedXML = $root->serializeAsXML('conceptTreeElement');
		return $serializedXML == '' ? "<result isEmpty=\"true\" textToDisplay=\"".wfMsg('smw_ob_no_categories')."\"/>"  : '<result>'.$serializedXML.'</result>';
	}
		
		
	/**
	 * Detrmines all category paths from root to the given entity.
	 * May be more than one in case of multiple inheritance.
	 *
	 * @param $cat The category to determine path for
	 * @param $path Must be an empty array
	 * @param $allPaths Must be an empty array
	 */
	private function getAllCategoryPaths($cat, & $path, & $allPaths, & $visitedNodes) {
		$path[] = $cat;
		$superCats = smwfGetSemanticStore()->getDirectSuperCategories($cat);
		array_push($visitedNodes, $cat->getDBkey());
		$cycleFound = false;
		foreach($superCats as $superCat) {
			if (in_array($superCat->getDBkey(), $visitedNodes)) {
				$cycleFound = true;
				break;
			}
			$cloneOfPath = array_clone($path);
			$this->getAllCategoryPaths($superCat, $cloneOfPath, $allPaths, $visitedNodes);
		}
		if (count($superCats) == 0 || $cycleFound) $allPaths[] = $path;
		array_pop($visitedNodes);
	}
		
	/**
	 * Detrmines all attribute paths from root to the given entity.
	 * May be more than one in case of multiple inheritance.
	 *
	 * @param $cat The category to determine path for
	 * @param $path Must be an empty array
	 * @param $allPaths Must be an empty array
	 */
	private function getAllPropertyPaths($att, & $path, & $allPaths, & $visitedNodes) {
		$path[] = $att;
		$superProps = smwfGetSemanticStore()->getDirectSuperProperties($att);
		array_push($visitedNodes, $att->getDBkey());
		$cycleFound = false;
		foreach($superProps as $superProp) {
			if (in_array($superProp->getDBkey(), $visitedNodes)) {
				$cycleFound = true;
				break;
			}
			$cloneOfPath = array_clone($path);
			$this->getAllPropertyPaths($superProp, $cloneOfPath, $allPaths, $visitedNodes);
		}
		if (count($superProps) == 0 || $cycleFound) $allPaths[] = $path;
		array_pop($visitedNodes);
	}
		
		
		

}

/**
 * TreeObject represents a node in a tree.
 * It can have other TreeObjects as children.
 *
 * A tree can be serialized as XML.
 */
class TreeObject {
	private $title;
	private $children;

	public function __construct($title) {
		$this->title = $title;
		$this->children = array();
	}

	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns the children of the node.
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Adds a child node if it does not already exist.
	 */
	public function addChild($childTitle) {
		if (!array_key_exists($childTitle->getText(), $this->children)) {
			$this->children[$childTitle->getText()] = new TreeObject($childTitle);
		}
		return $this->children[$childTitle->getText()];
	}

	/**
	 * Serializes the tree structure (without root node)
	 */
	public function serializeAsXML($type) {
		$id = uniqid (rand());
		$count = 0;
		$result = "";
		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		foreach($this->children as $title => $treeObject) {
			$isExpanded = count($treeObject->children) == 0 ? "false" : "true";
			$title_esc = htmlspecialchars($treeObject->getTitle()->getDBkey());
			$titleURLEscaped = htmlspecialchars(SMWOntologyBrowserXMLGenerator::urlescape($treeObject->getTitle()->getDBkey()));
			$issues = $gi_store->getGardeningIssues('smw_consistencybot', NULL, NULL, $treeObject->getTitle());
			$gi_issues = SMWOntologyBrowserErrorHighlighting::getGardeningIssuesAsXML($issues);
			$result .= "<$type title_url=\"$titleURLEscaped\" title=\"".$title_esc."\" img=\"$type.gif\" id=\"ID_$id$count\" expanded=\"$isExpanded\">";
			$result .= $gi_issues;
			$result .= $treeObject->serializeAsXML($type);
			$result .= "</$type>";
			$count++;
		}
		return $result;
	}

	/**
	 * Sorts the children (possibly recursive!)
	 */
	public function sortChildren($recursive = true) {
		if ($recursive) {
			$this->_sortChildren($this);
		} else {
			ksort($this->children);
		}
	}

	private function _sortChildren($treeObject) {
		foreach($treeObject->children as $title => $to) {
			$this->_sortChildren($to);
		}
		ksort($treeObject->children);
	}

}

/**
 * Makes a shallow copy of the given source array.
 */
function array_clone(& $src) {
	$dst = array();
	foreach($src as $e) {
		$dst[] = $e;
	}
	return $dst;
}

