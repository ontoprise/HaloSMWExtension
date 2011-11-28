<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


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
		parent::__construct( 'AutomaticSemanticForms' );
	}

	/*
	 * Create the special page HTML
	 */
	function execute( $query ) {
		global $wgOut;
		
		global $wgOut;
		$wgOut->addModules( 'ext.automaticsemanticforms.admin' );
		
		SFUtils::addJavascriptAndCSS();
		
		global $sfgScriptPath;
		$wgOut->addExtensionStyle( $sfgScriptPath . '/skins/jquery-ui/base/jquery.ui.datepicker.css' );
		
		//todo: LANGUAGE
		
		$html = '<p><strong>Here, you can materialize automatically created Semantic Forms.</strong></p>';
		
		//the category input field
		$html .= '<span><small>Please enter category names separated by semicolons.</small></span>';
		$html .= '<textarea id="asf_category_input" class="wickEnabled" constraints="asf-ac:_"></textarea>';
		$html .= '<input type="button" value="Refresh" onclick="ASFAdmin.refreshTabs()"/>';
		
		
		//the tab container
		$html .= '<table class="asf_tab_container">';
		
		//the tabs
		$html .= '<tr class="asf_tabs">';
		$html .= '<td class="asf_selected_tab" onclick="ASFAdmin.displayPreview()">Preview</td>';
		$html .= '<td class="asf_spacer_tab"></td>';
		$html .= '<td class="asf_unselected_tab" onclick="ASFAdmin.displaySource()">Source</td>';
		$html .= '<td class="asf_spacer_tab"></td>';
		$html .= '<td class="asf_unselected_tab" onclick="ASFAdmin.displayCreate()">Create</td>';
		$html .= '<td class="asf_spacer_tab" width="100%"></td>';
		//$html .= '</div>';
		$html .= '</tr>';

		
		//the tab containers
		$initMSG = 'First, please choose some categories and click the refresh button.';
		$html .= '<tr>';
		$html .= '<td class="asf_tab_content" colspan="6">';
		//$html .= '<div class="asf_tab_window">';
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
		//$html .= '</div>';
		
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		
		$wgOut->addHTML($html);
		
		return true;
	}
	
	
	
	

	
}
