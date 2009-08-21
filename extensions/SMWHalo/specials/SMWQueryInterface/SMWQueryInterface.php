<?php

 if (!defined('MEDIAWIKI')) die();



global $IP;
require_once( $IP . "/includes/SpecialPage.php" );
require_once( "SMW_QIAjaxAccess.php" );

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWQueryInterface extends SpecialPage {
	public function __construct() {
		parent::__construct('QueryInterface');
	}
/*
 * Overloaded function that is responsible for the creation of the Special Page
 */
	public function execute() {

		global $wgRequest, $wgOut, $smwgHaloScriptPath;

		$imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';
				
		$wgOut->setPageTitle(wfMsg('smw_queryinterface'));
		  
		$html = '<div id="qicontent">' .
				'<div id="shade" style="display:none"></div>';

		$html .= $this->addTreeView();

		$html .= '<div id="qiaddbuttons" class="qiaddbuttons">' .
					'<button class="btn" onclick="qihelper.newCategoryDialogue(true)" onmouseover="this.className=\'btn btnhov\'; Tip(\'' . wfMsg('smw_qi_tt_addCategory') . '\')" onmouseout="this.className=\'btn\'"><img src="' . $imagepath . 'category.gif" alt="category" />&nbsp;' . wfMsg('smw_qi_add_category') . '</button>'.
					'<button class="btn" onclick="qihelper.newInstanceDialogue(true)" onmouseover="this.className=\'btn btnhov\'; Tip(\'' . wfMsg('smw_qi_tt_addInstance') . '\')" onmouseout="this.className=\'btn\'"><img src="' . $imagepath . 'instance.gif" alt="category" />&nbsp;' . wfMsg('smw_qi_add_instance') . '</button>'.
					'<button class="btn" onclick="qihelper.newPropertyDialogue(true)" onmouseover="this.className=\'btn btnhov\'; Tip(\'' . wfMsg('smw_qi_tt_addProperty') . '\')" onmouseout="this.className=\'btn\'"><img src="' . $imagepath . 'property.gif" alt="category" />&nbsp;' . wfMsg('smw_qi_add_property') . '</button>'.
				'</div>';

		$html .= $this->addDragbox();
		
		$html .= $this->addPreviewResults();
		
		$html .= $this->addQueryLayout();
		
		$html .= $this->addAdditionalStuff();

		$html .= '<script type="text/javascript" src="' . $smwgHaloScriptPath .  '/scripts/QueryInterface/qi_tooltip.js"></script>';

		$wgOut->addHTML($html);
	}
	
	private function addTreeView() {
		return '<div id="treeview">' .
				'<div id="treeviewheader" class="qiboxheader">' .
				'&nbsp;' . wfMsg('smw_qi_querytree_heading') .
				'</div>' .
				'<div id="treeviewbreadcrumbs"></div>' .
				'<div id="treeanchor"><div id="qitreedummy"></div></div>' .
				'</div>';		
	}
	
	private function addDragbox() {
		global $smwgHaloScriptPath;
		
		return '<div id="dragbox" class="dragbox">' .
					'<div id="boxcontent" class="boxcontent">' .
						'<table><tbody id="dialoguecontent"></tbody></table>' .
						'<div id="dialoguebuttons" style="display:none">' .
							'<span class="qibutton" onclick="qihelper.add()">' . wfMsg('smw_qi_add') . '</span>&nbsp;<span class="qibutton" onclick="qihelper.emptyDialogue()">' . wfMsg('smw_qi_cancel') . '</span>&nbsp;<span id="qidelete" style="display:none" class="qibutton" onclick="qihelper.deleteActivePart()">' . wfMsg('smw_qi_delete') . '</span>' .
						'</div>' .
						'<div id="qistatus"></div>' .
					'</div>' .
				'</div>' .
				'<div id="tablecolumnpreview">' .
					'<div class="tcp_boxheader" onclick="qihelper.switchtcp()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_tcp') . '\')"><a id="tcptitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_table_column_preview') . '</div>' .
					'<div id="tcp_boxcontent" class="tcp_boxcontent">' .
						'<div id="tcpcontent"><table id="tcp" summary="Preview of table columns">' .
							'<tr><td>' . wfMsg('smw_qi_no_preview') . '</td></tr>' .
						'</table></div>' .
					'</div>' .
				'</div>';
		
	}
	
	private function addQueryLayout() {
		
		global $smwgResultFormats;
		
		$blacklist = array("rss", "csv", "json", "exceltable", "icalendar", "vcard", "calendar");
		
		$resultoptionshtml = "";
		
		while (current($smwgResultFormats)) {
			if (!in_array(key($smwgResultFormats), $blacklist)) {
				$resultoptionshtml .= '<option value="'.key($smwgResultFormats).'">'.key($smwgResultFormats).'</option>';
			}
		    next($smwgResultFormats);
		}
		
		return '<div id="querylayout">
					<div id="layouttitle" onclick="qihelper.switchlayout()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_qlm') . '\')"><a id="layouttitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_layout_manager') . '</div>
					<div id="layoutcontent" style="display:none">
					<table summary="Layout Manager for query" style="width:100%">
					<tr>
						<td onmouseover="Tip(\'' . wfMsg('smw_qi_tt_format') . '\')">
							Format:
						</td><td onmouseover="Tip(\'' . wfMsg('smw_qi_tt_format') . '\')">
							<select id="layout_format" onchange="qihelper.checkFormat()">
							'. $resultoptionshtml .'
							</select>
						</td>
						<td onmouseover="Tip(\'' . wfMsg('smw_qi_tt_sort') . '\') ">
							Sort by:
						</td><td onmouseover="Tip(\'' . wfMsg('smw_qi_tt_sort') . '\')">
							<select id="layout_sort" onchange="qihelper.updatePreview()">
							</select>
						</td>
						
					</tr>
					
					
					
				</table>
			</div>
				<div id="queryprinteroptions" style="display:block">
		</div>';
	}
	
	private function addPreviewResults() {
		return '<div id="previewlayout">
					<div id="previewtitle" onclick="qihelper.switchpreview()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_prp') . '\')"><a id="previewtitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_preview_result') . '</div>
					<div id="previewcontent">
				</div>
			</div>';
	}	
	
	private function addAdditionalStuff() {
		global $smwgHaloScriptPath;
		wfRunHooks("QI_AddButtons", array (&$buttons));
		
		$imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';		
		
		return '<div id="qimenubar">' .
						//'<span class="qibutton" onclick="qihelper.showLoadDialogue()">' . wfMsg('smw_qi_load') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
						//'<span class="qibutton" onclick="qihelper.showSaveDialogue()">' . wfMsg('smw_qi_save') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
						//'<span class="qibutton" onclick="qihelper.exportToXLS()">' . wfMsg('smw_qi_exportXLS') . '</span>' .
						'<button class="btn" onclick="qihelper.previewQuery()" onmouseover="this.className=\'btn btnhov\'; Tip(\'' . wfMsg('smw_qi_tt_preview') . '\')" onmouseout="this.className=\'btn\'">' . wfMsg('smw_qi_preview') . '</button>'.
						'<button class="btn" onclick="qihelper.copyToClipboard()" onmouseover="this.className=\'btn btnhov\'; Tip(\'' . wfMsg('smw_qi_tt_clipboard') . '\')" onmouseout="this.className=\'btn\'">' . wfMsg('smw_qi_clipboard') . '</button>'.
						$buttons.
						'<button class="btn" onclick="qihelper.showFullAsk(\'parser\', true)" onmouseover="this.className=\'btn btnhov\'; Tip(\'' . wfMsg('smw_qi_tt_showAsk') . '\')" onmouseout="this.className=\'btn\'">' . wfMsg('smw_qi_showAsk') . '</button>'.
						'<span style="position:absolute; right:13px;"><button class="btn" onclick="qihelper.resetQuery()" onmouseover="this.className=\'btn btnhov\'; Tip(\'' . wfMsg('smw_qi_tt_reset') . '\')" onmouseout="this.className=\'btn\'">' . wfMsg('smw_qi_reset') . '</button></span>'.
					'</div>'.

				'<div id="fullpreviewbox" style="display:none">'.
				'<div id="fullpreview"></div>'.
				'<span class="qibutton" onclick="$(\'fullpreviewbox\', \'shade\').invoke(\'toggle\')"><img src="'. $imagepath. 'delete.png"/>' . wfMsg('smw_qi_close_preview') . '</span></div>'.
				'</div>'.
		
				'<div id="resetdialogue" class="topDialogue" style="display:none">' .
						'Do you really want to reset your query?<br/>' .
						'<span class="qibutton" onclick="qihelper.doReset()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$(\'resetdialogue\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_cancel') . '</span>' .
						'</div>'.
		
				'<div id="showAsk" class="topDialogue" style="display:none; width:350px">' .
						'<span id="showParserAskButton" class="qibutton" style="font-weight: bold; text-decoration: none; cursor: default;">' . wfMsg('smw_qi_parserask') . '</span><br/><hr/>' .
						'<div><textarea id="fullAskText" style="width:95%" rows="10" readonly></textarea></div>' .
						'<span class="qibutton" onclick="$(\'showAsk\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_close') . '</span>' .
						'</div>'.
		
				'<div id="savedialogue" class="topDialogue" style="display:none">' .
						'Please enter a query name:<br/>' .
						'<input type="text" id="saveName"/><br/>' .
						'<span class="qibutton" onclick="qihelper.doSave()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$(\'savedialogue\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_cancel') . '</span>' .
						'</div>';		
	}

}

