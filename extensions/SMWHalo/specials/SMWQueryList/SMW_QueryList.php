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
		global $wgOut;
		
		$html = '';
		
		$queryMetadata = new SMWQMQueryMetadata();
		$results = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadata);
		
		$html .= '<table class="smwtable" width="100%" id="list_of_all_queries">';
		$html .= '<tr><th>Name</th><th>Page</th><th>Categories</th><th>Properties</th></tr>';
		
		$linker = new Linker();
		
		foreach($results as $result){
			$html .= '<tr>';
			
			//Add query name
			$html .= '<td>'.$result->queryName.'</td>';
			
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
				$sortkey = array_key_exists(0, $result->categoryConditions) ? $result->categoryConditions[0] : '';
				foreach($result->categoryConditions as $key => $category){
					$title = Title::newFromText($category, NS_CATEGORY);
					$result->categoryConditions[$key] = $linker->makeLink($title->getFullText(), $title->getText());
				}
				$html .= implode('; ', $result->categoryConditions);
			}
			$html .= '</td>';
			
			//Add properties
			$html .= '<td>';
			$properties = array();
			if(is_array($result->propertyConditions)) $properties = $result->propertyConditions;
			if(is_array($result->propertyPrintRequests)) $properties = array_merge($properties, $result->propertyPrintRequests);
			ksort($properties);
			$properties = array_keys($properties);
			$sortkey = array_key_exists(0, $properties) ? $properties[0] : '';
			$html .= '<span style="display: none">'.$sortkey.'</span>';
			foreach($properties as $key => $property){
				$title = Title::newFromText($property, SMW_NS_PROPERTY);
				$properties[$key] = $linker->makeLink($title->getFullText(), $title->getText());
			}
			$html .= implode('; ', $properties);
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		global $smwgScriptPath;
		$html .= '<script type="text/javascript" src="' . $smwgScriptPath . '/skins/SMW_sorttable.js"></script>';
		
		$wgOut->addHTML($html);
		
		return true;
	}
	
	
	
	
	
	
	
}