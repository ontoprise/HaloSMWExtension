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
		
		$html .= '<p>This is a list of all queries.</p>';
		
		$queryMetadata = new SMWQMQueryMetadata();
		$results = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadata);
		
		$html .= '<table>';
		//$html = '<tr><th>Name</th><th>Page</th><th>Categories</th><th>Properties</th></tr>';
		
		foreach($results as $result){
			$html .= '<tr>';
			
			$html .= '<td>'.$result->queryName.'</td>';
			$html .= '<td>'.$result->usedInArticle.'</td>';
			$html .= '<td>'.$result->queryName.'</td>';
			$html .= '<td>'.$result->queryName.'</td>';
			
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		$wgOut->addHTML($html);
		
		return true;
	}
	
	
	
	
	
	
	
}