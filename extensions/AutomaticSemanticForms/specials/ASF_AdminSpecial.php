<?php

if ( !defined( 'MEDIAWIKI' ) ) die();

/*
 * Imnplements the Automatic Semantic Forms
 * Special Page.
 */
class ASFAdminSpecial extends SpecialPage {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( 'ASFSpecial' );
	}

	/*
	 * Create the special page HTML
	 */
	function execute( $query ) {
		global $wgOut;
		
		//todo: LANGUAGE
		
		$html = '<p><strong>Here, you can materialize automatically created Semantic Forms.</strong></p>';
		
		//the category input field
		$html .= '<span><small>Please enter category names separated by semicolons.</small></span>';
		$html .= '<textarea id="asf_category_input" class="wickEnabled" constraints="asf-ac:category"></textarea>';
		$html .= '<input type="button" value="Refresh" onclick="ASFAdmin.refreshTabs()"/>';
		
		//the tabs
		$html .= '<div class="asf_tab_container">';
		$html .= '<span class="asf_selected_tab" onclick="ASFAdmin.displayPreview()">Preview</span>';
		$html .= '<span class="asf_unselected_tab" onclick="ASFAdmin.displaySource()">Source</span>';
		$html .= '<span class="asf_unselected_tab" onclick="ASFAdmin.displayCreate()">Create</span>';
		$html .= '</div>';
		
		//the tab containers
		$initMSG = 'First, please choose some categories and click the refresh button.';
		$html .= '<div class="asf_tab_windoe">';
		$html .= '<div id="asf_preview_tab">'.$initMSG.'</div>';
		$html .= '<div id="asf_source_tab" style="display: none">'.$initMSG.'</div>';
		$html .= '<div id="asf_create_tab" style="display: none">';
		
		$html .= '<span>'.$initMSG.'</span>';
		$html .= '<span style="display: none"></span>';
		$html .= '<div style="display: none; width: 100%">';
		$html.= '<small>Please enter the name of the new form.</small><br/>';
		$html .= '<input type="text" id="asf_formname_input" class="wickEnabled" constraints="ask: [[:Form:+]]"'
			.' onkeyup="ASFAdmin.checkFormName()" onchange="ASFAdmin.checkFormName()"/>';
		$html .= '<small style="display: none" class="asf_warning"><br/>Warning: This form already exists and it will be overwritten.</small>';
		$html .= '<br/><input type="button" value="Create" onclick="ASFAdmin.saveForm()"/>';
		$html .= '</div>';
		$html .= '<span style="display: none">';
		$html .= '<strong>The form </strong><strong>Name</strong><strong> has been saved successfully.</strong>';
		$html .= '<strong class="asf_warning">Error: The form could not be saved. Please try again.</strong>';
		$html .= '<br/><input type="button" value="Ok" onclick="ASFAdmin.finishSaveRequest()"/>';
		$html .= '</span>';
		$html .= '</div>';
		$html .= '</div>';
		
		$wgOut->addHTML($html);
		
		$this->addHeaders();
		
		return true;
	}
	
	/*
	 * Add javascript and CSS files
	 */
	private function addHeaders(){
		global $smgJSLibs; 
		$smgJSLibs[] = 'jquery'; 
		$smgJSLibs[] = 'qtip';
		
		global $asfHeaders;
		$asfHeaders['asf.js'] = true;
		$asfHeaders['asf.css'] = true;		
	}
	
	
	
	
	
}