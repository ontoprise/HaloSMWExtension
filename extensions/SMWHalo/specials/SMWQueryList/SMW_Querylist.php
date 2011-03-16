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
		
		$html .= 'This is a list of all queries.';
		
		$wgOut->addHTML($html);
		
		return true;
	}
	
	
	
	
	
	
	
}