<?php

if ( !defined( 'MEDIAWIKI' ) ) die();

/*
 * This special page displays a list of all queries
 */
class SMWQueryList extends SpecialPage {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( 'QueryList' );           
	}

	/*
	 * Create the special page HTML
	 */
	function execute( $query ) {
		global $wgOut, $wgHooks;		
                
                //add resource modules
                $this->addResourceModules();
                
		//todo: Use Language
		
		$html = '';
		
		$html .= '<span>Filter: </span>';
		$html .= '<input id="ql_filterstring-0" style="display: inline" type="text" size="50" class="wickEnabled" constraints="all" currentValue=""/> ';
		$html .= '<input id="ql_filterstring-1" style="display: none" type="text" size="50" currentValue=""/> ';
		$html .= '<input id="ql_filterstring-3" style="display: none" type="text" size="50" class="wickEnabled" constraints="ask: [[:Category:+]]" currentValue=""/> ';
		$html .= '<input id="ql_filterstring-4" style="display: none" type="text" size="50" class="wickEnabled" constraints="ask: [[:Property:+]]" currentValue=""/> ';
		$html .= '<select id="ql_filtercol" size="1" currentValue="0" onchange="window.queryList_updateAC()">';
		$html .= '<option value="0">All</optionY';
		$html .= '<option value="1">Name</option>';
		$html .= '<option value="2">Page</option>';
		$html .= '<option value="3">Categories</option>';
		$html .= '<option value="4">Properties</option>';
		$html .= '</select>';
		$html .= '  <input type="button" value="Ok" onclick="window.queryList_filter()"/>';
		$html .= '<br/><br/>';
		
		$queryMetadata = new SMWQMQueryMetadata();
		$results = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadata);
		
		$html .= '<table id="ql_list" class="smwtable" width="100%" id="list_of_all_queries">';
		$html .= '<tr><th>Name</th><th>Page</th><th>Categories</th><th>Properties</th></tr>';
		
		$linker = new Linker();
		
		foreach($results as $result){
			$html .= '<tr>';
			
			//Add query name
			$html .= '<td><span>'.$result->queryName.'</span></td>';
			
			//Add article name
			$html .= '<td>';
			$html .= '<span style="display: none">'.$result->usedInArticle.'</span>';
			$title = Title::newFromText($result->usedInArticle);
			$html .= $linker->makeLink($title->getFullText());
			$html .= '</td>';
			
			//Add categories
			$html .= '<td>';
			if(is_array($result->categoryConditions)){
				ksort($result->categoryConditions);
				$result->categoryConditions = array_keys($result->categoryConditions);
				$sortKey = implode(', ', $result->categoryConditions);
				foreach($result->categoryConditions as $key => $category){
					$title = Title::newFromText($category, NS_CATEGORY);
					$result->categoryConditions[$key] = $linker->makeLink($title->getFullText(), $title->getText());
				}
				$html .= '<span style="display: none">'.$sortKey.'</span>';
				$html .= implode('; ', $result->categoryConditions);
			} else {
				$html .= '<span></span>';
			}
			$html .= '</td>';
			
			//Add properties
			$html .= '<td>';
			$properties = array();
			if(is_array($result->propertyConditions)) $properties = $result->propertyConditions;
			if(is_array($result->propertyPrintRequests)) $properties = array_merge($properties, $result->propertyPrintRequests);
			ksort($properties);
			$properties = array_keys($properties);
			$sortkey = implode(', ', $properties);
			foreach($properties as $key => $property){
				$title = Title::newFromText($property, SMW_NS_PROPERTY);
				$properties[$key] = $linker->makeLink($title->getFullText(), $title->getText());
			}
			$html .= '<span style="display: none">'.$sortkey.'</span>';
			$html .= implode('; ', $properties);
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
                $wgOut->addModules(array('ext.smwhalo.queryList'));
		$wgOut->addHTML($html);
		
		return true;
	}
	
}