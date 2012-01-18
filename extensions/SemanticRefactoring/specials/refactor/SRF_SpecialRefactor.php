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

/**
 * Created on 19.12.2011
 *
 * @file
 * @ingroup SREFSpecials
 * @ingroup SREFRefactor
 *
 * @author Kai Kühn
 */
if (!defined('MEDIAWIKI')) die();

define("SRFF_HELP_URL", "http://www.smwplus.com");
define('SREF_QUERY_PAGE_LIMIT', 100);

global $IP;
require_once( "$IP/includes/SpecialPage.php" );

/**
 * Semantic Refactoring special page
 *
 * @author Kai Kühn
 *
 */
class SREFRefactor extends SpecialPage {

	public function __construct() {
		parent::__construct('SREFRefactor', 'delete');
	}

	public function execute($p) {
		global $wgOut, $wgRequest;
		$wgOut->setPageTitle(wfMsg('srefrefactor'));
		$adminPage = Title::newFromText(wfMsg('srefrefactor'), NS_SPECIAL);

		// description text
		$helpLink = '<a target="_blank" href="'.SRFF_HELP_URL.'">SMW-Forum</a>';
		$html = "<div>".wfMsg('sref_specialrefactor_description', $helpLink)."</div>";

		// query box
		$spectitle = $this->getTitleFor( 'SREFRefactor' );
		$query_val = $wgRequest->getVal( 'q' );
		$html .= '<h1>'.wfMsg('sref_select_instanceset').'</h1>';
		$html .= '<div>';
		$html .= wfMsg('sref_enter_query');
		$html .= '<form id="refactor_form" method="post" action="' . $spectitle->escapeLocalURL() . '" name="refactor">';
		$html .= '<div id="sref_query_container">';
		$html .= '<textarea id="sref_querybox_textarea" name="q">'.$query_val.'</textarea>';
		$html .= '<input type="submit" id="sref_run_query" value="'.wfMsg('sref_run_query').'"></input>';
		$html .= '<input type="button" id="sref_clear_query" value="'.wfMsg('sref_clear_query').'"></input>';
		$html .= '<input type="button" id="sref_open_qi" value="'.wfMsg('sref_open_qi').'"></input>';
		$html .= '</div>';

		// result box
		$html .= '<div id="sref_result_container"><div id="sref_resultbox">';
		$offset = $wgRequest->getVal( 'offset' );
		if ( $wgRequest->getCheck( 'q' ) ) {
			
			$qs = new SRFQuerySelector($wgRequest->getText( 'q' ));
			$qresult = $qs->getQueryResult();
			$html .= $qresult['html'];
		}
		$html .= '</div>';
		$html .= '<input type="button" id="sref_selectall" value="'.wfMsg('sref_selectall').'"></input>';
		$html .= '<input type="button" id="sref_deselectall" value="'.wfMsg('sref_deselectall').'"></input>';

		if (isset($qresult) && $qresult['result']->hasFurtherResults()) {
			$html .= '<span id="sref_further_results">'.wfMsg('sref_more_results').'</span>';
		}

		// arrows
		$tableRowsCount = isset($qresult) ? $qresult['result']->getCount() : 0;
		$pageNum = $tableRowsCount / SREF_QUERY_PAGE_LIMIT;
        $pageNum += $tableRowsCount % SREF_QUERY_PAGE_LIMIT === 0 ? 0 : 1;
		$html .= '<span id="sref_page_counter">'.wfMsg('sref_page').' 1 - '.$pageNum.'</span>';
		$html .= '<a id="sref_prev_page" class="sref_prev_page_element sref_pointer">('.wfMsg('sref_prev_page').' '.SREF_QUERY_PAGE_LIMIT.')</a><span class="sref_prev_page_element" id="sref_prev_page_disabled" style="display: none;">('.wfMsg('sref_prev_page').')</span>';
		$html .= '<a id="sref_next_page" class="sref_next_page_element sref_pointer">('.wfMsg('sref_next_page').' '.SREF_QUERY_PAGE_LIMIT.')</a><span class="sref_next_page_element" id="sref_next_page_disabled" style="display: none;">('.wfMsg('sref_next_page').')</span>';
		


		$html .= '</div>';
		$html .= '</form>';


		$html .= '</div>';


		// command box
		$html .= '<div style="float:left;width:99%" >';
		$html .= '<h1>'.wfMsg('sref_choose_commands').'</h1>';
		$html .= '<div id="sref_commandboxes">';
		$html .= '</div>';
		$html .= '<div style="float:left">';
		
		global $wgScriptPath;
		$html .= '<img title="'.wfMsg('sref_add_command').'" class="sref_pointer" id="sref_add_command" src="'.$wgScriptPath.'/extensions/SemanticRefactoring/skins/images/plus-act.gif"/>';
		$html .= '<br><input type="button" id="sref_start_operation" value="'.wfMsg('sref_start_operation').'"></input>';
		$html .= '</div>';
		$html .= '</div>';

		// running operations
		$html .= '<div style="float:left;width:99%">';
		$html .= '<h1>'.wfMsg('sref_running_operations').'</h1>';
		$html .= '<div id="sref_operations">';
		$html .= '</div>';

		$html .= '</div>';

		// add to page
		$wgOut->addHTML($html);
	}




}

