<?php

/*
 * This class provides a public method for 
 * retrieving the category section structure.
 */
class ASFCategorySectionStructureProcessor {
	
	private static $instance;
	
	/*
	 * singleton
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private $categorySectionStructure = array();
	
	private $categoriesWithNoProperties = array();
	
	private $categoriesWithNoFormEdit = array();
	
	/*
	 * Public method for retrieving the category section structure
	 */
	public function getCategorySectionStructure($categories){
		
		$this->initCategorySectionStructure($categories);
		
		$this->removecategoriesWithNoProperties();
		
		if(count($this->categorySectionStructure) == 0){
			return array(false, $this->categoriesWithNoProperties, $this->categoriesWithNoFormEdit);
		}
		
		$this->removeUnnecesserayEdges();
		
		global $asfCombineCategorySectionsWherePossible;
		if($asfCombineCategorySectionsWherePossible){
			$this->computeVisibleSections();
		
			$this->addIncludedCategoriesToVisibleSections();
		} else {
			$this->makeAllCategorySectionsVisible();
		}
		
		$this->finalizeCategorySectionStructure();
		
		//echo('<pre>'.print_r($this->categorySectionStructure, true).'</pre>');
		
		return array($this->categorySectionStructure, $this->categoriesWithNoProperties, $this->categoriesWithNoFormEdit);
	}
	
	
	/*
	 * Initializes category section tree with data from ontology and 
	 * data about parents and children to each category section tree item
	 */
	private function initCategorySectionStructure($categories){
		$this->categorySectionStructure = array();
		
		$store = smwfNewBaseStore();
		
		//init category section structure with data from ontology
		global $wgLang;
		foreach($categories as $category){
			if(strpos($category, $wgLang->getNSText(NS_CATEGORY).':') === 0){
				$category = substr($category, strpos($category, ":") +1);
			}
			$categoryObject = Category::newFromName($category);
			$categoryTitle = $categoryObject->getTitle();
			
			if(!$categoryTitle->exists()){
				//also display sections for categories that do not exist
				//continue;
			}
			
			$semanticData = $store->getSemanticData($categoryTitle);
			$noASF = ASFFormGeneratorUtils::getPropertyValue($semanticData, ASF_PROP_NO_AUTOMATIC_FORMEDIT);
			if(strtolower($noASF) == 'true'){
				$this->categoriesWithNoFormEdit[$categoryTitle->getText()] = true;
				continue;
			}
			
			$categoryTree = ASFFormGeneratorUtils::getSuperCategories($categoryTitle, true);

			$this->fillCategorySectionStructureWithItems($category, $categoryTree[ucfirst($category)]);
			
			//echo('<pre>'.print_r($tree, true).'</pre>');
		}
		
		//add data about child categories to category section tree structure
		foreach($this->categorySectionStructure as $categoryName => $item){
			foreach($this->categorySectionStructure as $categoryNameChild => $itemChild){
				if(array_key_exists($categoryName, $itemChild->parents)){
					$item->children[$categoryNameChild] = true;
				}
			}
			$this->categorySectionStructure[$categoryName] = $item;
		}
		
		//echo('<pre>'.print_r($categorySectionTree, true).'</pre>');
	}

	/*
	 * Helper method for initializeCategorySectionStructure
	 * that fills the structure with ASFCategorySectionStructureItems.
	 */
	private function fillCategorySectionStructureWithItems($categoryName, $subTree){
		$categoryTreeItem = new ASFCategorySectionStructureItem();
		$categoryTreeItem->parents = array_flip(array_keys($subTree));
		$this->categorySectionStructure[$categoryName] = $categoryTreeItem;
		
		foreach($subTree as $categoryName => $subTree){
			$this->fillCategorySectionStructureWithItems($categoryName, $subTree);
		}
	}
	
	/*
	 * This method removes categories which are not used 
	 * by any category as the domain
	 */
	private function removecategoriesWithNoProperties(){
		//get emoty categories
		$emptyCategorySections = array();
		foreach($this->categorySectionStructure as $categoryName => $item){
			$title = Title::newFromText($categoryName, NS_CATEGORY);
			$properties = ASFFormGeneratorUtils::getPropertiesWithDomain(array($title));
			
			if(count($properties) == 0 && count($item->children) > 0){
				$emptyCategorySections[$categoryName] = true;
			}
		}
		
		//remove empty categories
		foreach($emptyCategorySections as $emptyCategoryName => $dontCare){
			$emptyItem = $this->categorySectionStructure[$emptyCategoryName];
			
			foreach($this->categorySectionStructure as $categoryName => $item){
				if(array_key_exists($emptyCategoryName, $item->parents)){
					unset($this->categorySectionStructure[$categoryName]->parents[$emptyCategoryName]);
					$this->categorySectionStructure[$categoryName]->parents =
						array_merge($item->parents, $emptyItem->parents);
				}
				
			if(array_key_exists($emptyCategoryName, $item->children)){
					unset($this->categorySectionStructure[$categoryName]->children[$emptyCategoryName]);
					$this->categorySectionStructure[$categoryName]->children =
						array_merge($item->children, $emptyItem->children);
				}
			}
			
			unset($this->categorySectionStructure[$emptyCategoryName]);
		}
		
		foreach($this->categorySectionStructure as $categoryName => $item){
			$title = Title::newFromText($categoryName, NS_CATEGORY);
			$properties = ASFFormGeneratorUtils::getPropertiesWithDomain(array($title));
			
			if(count($properties) == 0 && count($item->children) == 0 && count($item->parents) == 0){
				unset($this->categorySectionStructure[$categoryName]);
				$this->categoriesWithNoProperties[$categoryName] = false;
			}
		}
	}
	
	/*
	 * Removes unnecessary child and parent edges
	 * from the category section structure
	 */
	private function removeUnnecesserayEdges(){
		//remove all childs of a category, which the category also has 
		//as a descendant
		foreach($this->categorySectionStructure as $categoryName => $item){
			$descendants = $this->getDescendantsOfCategory($categoryName);
			foreach($descendants as $descendantName => $dontCare){
				if(array_key_exists($descendantName, $item->children)){
					unset($this->categorySectionStructure[$categoryName]->children[$descendantName]);
					unset($this->categorySectionStructure[$descendantName]->parents[$categoryName]);
				}
			}
		} 
	}
	
	/*
	 * Get all descendants of a category
	 */
	private function getDescendantsOfCategory($categoryName, $descendants = array()){
		$nextDescendants = $this->categorySectionStructure[$categoryName]->children;
		
		foreach($nextDescendants as $descendantName => $dontCare){
			$children = $this->categorySectionStructure[$descendantName]->children; 	
			$descendants = array_merge($descendants, $children);
			foreach($children as $childName => $dontCare){
				$descendants = array_merge($descendants, 
					$this->getDescendantsOfCategory($childName, $descendants));
			}
			
		}
		
		return $descendants;
	}
	
	
	/*
	 * This method computes visible sections:
	 * Each leaf is visible
	 * All nodes that have more than one visible leaf is visible
	 */
	private function computeVisibleSections($currentLeafs = false){
		
		//make leafs visible if this is the first call of this method
		if($currentLeafs === false){
			$currentLeafs = array();
			foreach($this->categorySectionStructure as $categoryName => $item){
				if(count($item->children) == 0){
					$item->visible = true;
					$this->categorySectionStructure[$categoryName] = $item;
					$currentLeafs[$categoryName] = true;
				}
			}
		}
		
		//do visibility processing
		$nextCurrentLeafs = array();
		foreach($currentLeafs as $categoryName => $dontCare){
			$item = $this->categorySectionStructure[$categoryName];
			
			if(!$item->visible){
				list($invisible, $dontCare) = $this->isInvisibleSection($categoryName);
				if(!$invisible){
					$this->categorySectionStructure[$categoryName]->visible = true;
				}
			}
			
			$nextCurrentLeafs = array_merge($nextCurrentLeafs, $item->parents);
		}
		
		//do visibility processing recursively for all parents
		if(count($nextCurrentLeafs) > 0){
			$this->computeVisibleSections($nextCurrentLeafs);
		}
	}
	
	
	/*
	 * Helper method for computeVisibleSections that computes if
	 * a certain section is visible or not
	 */
	private function isInvisibleSection($rootCategoryName, $categoryName = null, 
			$visibleChild = false, $descendants = null){
		
		if(is_null($categoryName)){
			//method was called the first time
			$categoryName = $rootCategoryName;
			$descendants = array_merge($this->categorySectionStructure[$categoryName]->children
				,$this->getDescendantsOfCategory($categoryName));
		}

		foreach($this->categorySectionStructure[$categoryName]->children as $childName => $dontCare){
			if($this->categorySectionStructure[$childName]->visible){
				
				//child categories, that are already included by another descendant of
				//this category do not count as visible child nodes
				$continue = false;
				foreach($this->categorySectionStructure[$childName]->visibleDescendantOf as $descendant => $dontCare){
					if(array_key_exists($descendant, $descendants)
							&& $this->categorySectionStructure[$descendant]->visible){
						$continue = true;
						break;
					}
				}
				
				if($continue) continue;
				
				$this->categorySectionStructure[$childName]->visibleDescendantOf[$rootCategoryName] = true;
				
				if($visibleChild && $visibleChild != $childName){
					return false;
				} else {
					$visibleChild = $childName;
					
				}
			} else {
				list($result, $visibleChild) = 
					$this->isInvisibleSection($rootCategoryName, $childName, $visibleChild, $descendants);
				if(!$result) return array(false, null);
			}
		}
		
		return array(true, $visibleChild);
	}
	
	/*
	 * This method adds invisible categories to visible sections
	 * Each invisible ancestor is added until the first visible ancestor
	 */
	private function addIncludedCategoriesToVisibleSections($currentRootCategories = false){
		//init root categories if method is called the first time
		if($currentRootCategories === false){
			$currentRootCategories = array();
			foreach($this->categorySectionStructure as $categoryName => $item){
				if(count($item->parents) == 0){
					$currentRootCategories[$categoryName] = true;
				}
			}
		}
		
		//process the included categories
		$nextCurrentRootCategories = array();
		foreach($currentRootCategories as $currentRootCategory => $dontCare){
			$item = $this->categorySectionStructure[$currentRootCategory];
			if($item->visible){
				$item->includesCategories = $this->computeIncludedInvisibleCategories($item->parents);
				$this->categorySectionStructure[$currentRootCategory] = $item;
			} 
			$nextCurrentRootCategories = array_merge($nextCurrentRootCategories, $item->children);
		}
	
		// recursively process included categories for visible children
		if(count($currentRootCategories) > 0){
			$this->addIncludedCategoriesToVisibleSections($nextCurrentRootCategories);
		}
	}
	
	/*
	 * Helper method for addIncludedCategoriesToVisibleSections
	 * that actually includes the invisible categories
	 */
	private function computeIncludedInvisibleCategories($parents, $dependencies = array()){
		foreach($parents as $parent => $dontCare){
			if(!$this->categorySectionStructure[$parent]->visible 
					&& !$this->categorySectionStructure[$parent]->allreadyIncluded){
				
				$this->categorySectionStructure[$parent]->allreadyIncluded = true;
				$dependencies[$parent] = true;
				$parents = $this->categorySectionStructure[$parent]->parents;
				$dependencies = $this->computeIncludedInvisibleCategories($parents, $dependencies);
			}
		}
		return $dependencies;
	}
	
	/*
	 * This method does some final processing:
	 * - sorting
	 * - removing invisible category section structure items
	 */
	private function finalizeCategorySectionStructure(){
		
		//sort the category section structure: Alphabetically sort all
		//root categories, then alphabetically sort all their children and so on
		$categorySectionStructure = $this->categorySectionStructure;
		$this->categorySectionStructure = array();
		
		$rootCategories = array();
		foreach($categorySectionStructure as $categoryName => $item){
			if(count($item->parents) == 0){
				$rootCategories[$categoryName] = true; 
			}
		}
		
		while(count($rootCategories) > 0){
			ksort($rootCategories);
			
			$newRootCategories = array();
			foreach($rootCategories as $categoryName => $dontCare){
				$this->categorySectionStructure[$categoryName] =
					$categorySectionStructure[$categoryName];
				$newRootCategories = array_merge($newRootCategories,
					$categorySectionStructure[$categoryName]->children);
			}
			
			$rootCategories = $newRootCategories;
		}
		
		//remove invisible category section structure items
		foreach($this->categorySectionStructure as $categoryName => $item){
			if(!$item->visible){
				unset($this->categorySectionStructure[$categoryName]);
			}
		}
	}
	
	/*
	 * Sets all category sections to visible
	 */
	private function makeAllCategorySectionsVisible(){
		foreach($this->categorySectionStructure as $categoryName => $instance){
			$this->categorySectionStructure[$categoryName]->visible = true;
		}
	}
}

/*
 * Objects of this class represent an item, respectively category,
 * in the CategorySectionStructure
 */
class ASFCategorySectionStructureItem {
	public $parents = array();
	public $children = array();
	public $visible = false; //is section visible in form))
	public $includesCategories = array(); //which categories are included in this section
	public $allreadyIncluded = false; //is this category already included in a section
	public $visibleDescendantOf = array();
	
}

