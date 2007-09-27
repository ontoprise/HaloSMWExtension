<?php

 if (!defined('MEDIAWIKI')) die();



global $IP;
require_once( $IP . "/includes/SpecialPage.php" );
require_once( "SMW_QIAjaxAccess.php" );

/*
// standard functions for creating a new special page
function doSMWQueryInterface()  {
		SMWQueryInterface::execute();
}

SpecialPage::addPage( new SpecialPage('QueryInterface','',true,'doSMWQueryInterface',false)) ;

*/

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWQueryInterface extends SpecialPage {
	public function __construct() {
		parent::__construct('QueryInterface');
	}
/*
 * Overloaded function that is resopnsible for the creation of the Special Page
 */
	public function execute() {

		global $wgRequest, $wgOut, $smwgHaloScriptPath;

		$wgOut->setPageTitle("Query Interface");

		$imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';

		$html = '<div id="qicontent">' .
				'<div id="shade" style="display:none"></div>' .

		$html .= '<div id="treeview">' .
				'<div id="treeviewheader" class="qiboxheader">' .
				'&nbsp;' . wfMsg('smw_qi_querytree_heading') .
				'</div>' .
				'<div id="treeviewbreadcrumbs"></div>' .
				'<div id="treeanchor"><div id="qitreedummy"></div></div>' .
				'</div>';

		$html .= '<div id="qiaddbuttons" class="qiaddbuttons">' .
					'<button class="btn" onclick="qihelper.newCategoryDialogue(true)" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"><img src="' . $imagepath . 'category.gif" alt="category" />&nbsp;' . wfMsg('smw_qi_add_category') . '</button>'.
					'<button class="btn" onclick="qihelper.newInstanceDialogue(true)" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"><img src="' . $imagepath . 'instance.gif" alt="category" />&nbsp;' . wfMsg('smw_qi_add_instance') . '</button>'.
					'<button class="btn" onclick="qihelper.newPropertyDialogue(true)" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"><img src="' . $imagepath . 'property.gif" alt="category" />&nbsp;' . wfMsg('smw_qi_add_property') . '</button>'.
					//'<input type="button" value="' . wfMsg('smw_qi_add_instance') . '" class="btn" onclick="qihelper.newInstanceDialogue(true)" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"/>'.
					//'<input type="button" value="' . wfMsg('smw_qi_add_property') . '" class="btn" onclick="qihelper.newPropertyDialogue(true)" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"/>'.
					//'<span id="cat" class="qibutton" onclick="qihelper.newCategoryDialogue(true)"><img src="' . $imagepath . 'category.gif" alt="category" />' . wfMsg('smw_qi_add_category') . '</span>' .
					//'<span id="ins" class="qibutton" onclick="qihelper.newInstanceDialogue(true)"><img src="' . $imagepath . 'instance.gif" alt="instance" />' . wfMsg('smw_qi_add_instance') . '</span>' .
					//'<span id="prop" class="qibutton" onclick="qihelper.newPropertyDialogue(true)"><img src="' . $imagepath . 'property.gif" alt="property"/>' . wfMsg('smw_qi_add_property') . '</span>' .
				'</div>';

		$html .= '<div id="dragbox" class="dragbox">' .
					'<div id="boxcontent" class="boxcontent">' .
						'<table><tbody id="dialoguecontent"></tbody></table>' .
						'<div id="dialoguebuttons" style="display:none">' .
							'<span class="qibutton" onclick="qihelper.add()">' . wfMsg('smw_qi_add') . '</span>&nbsp;<span class="qibutton" onclick="qihelper.emptyDialogue()">' . wfMsg('smw_qi_cancel') . '</span>&nbsp;<span id="qidelete" style="display:none" class="qibutton" onclick="qihelper.deleteActivePart()">' . wfMsg('smw_qi_delete') . '</span>' .
						'</div>' .
						'<div id="qistatus"></div>' .
					'</div>' .
					'<div id="tablecolumnpreview">' .
						'<div class="tcp_boxheader" onclick="switchtcp()"><a id="tcptitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_table_column_preview') . '</div>' .
						'<div id="tcp_boxcontent" class="tcp_boxcontent" style="display:none">' .
							'<div id="tcpcontent"><table id="tcp" summary="Preview of table columns">' .
								'<tr><td>' . wfMsg('smw_qi_no_preview') . '</td></tr>' .
							'</table></div>' .
						'</div>' .
					'</div>' .
				'</div>';

		$html .= '<div id="querylayout">
					<div id="layouttitle" onclick="switchlayout()"><a id="layouttitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_layout_manager') . '</div>
					<div id="layoutcontent" style="display:none">
					<table summary="Layout Manager for query" style="width:100%">
					<tr>
						<td>
							Format:
						</td><td>
							<select id="layout_format">
							<option value="table">table</option>
							<option value="broadtable">broad table</option>
							<option value="ul">bullet list</option>
							<option value="ol">ordered list</option>
							<option value="list">list</option>
							<option value="count">count</option>
							</select>
						</td>
						<td>
							Sort by:
						</td><td>
							<select id="layout_sort">
							<option>Article title</option>
							</select>
						</td>
						<td>
							Order:
						</td><td>
							<select id="layout_order">
							<option>ascending</option>
							<option>descending</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Link:
						</td><td>
							<select id="layout_link">
							<option>subject</option>
							<option>all</option>
							<option>none</option>
							</select>
						</td>
						<td>
							Limit:
						</td><td>
							<input type="text" id="layout_limit" value="50"/>
						</td>
						<td>
							Headers:
						</td><td>
							show <input type="checkbox" checked="checked" id="layout_headers"/>
						</td>
					</tr>
					<tr>
						<td>
							Intro:
						</td><td>
							<input type="text" id="layout_intro"/>
						</td>
						<td>
							Mainlabel:
						</td><td>
							<input type="text" id="layout_label" value="Article title"/>
						</td>
						<td>
							Default:
						</td><td>
							<input type="text" id="layout_default"/>
						</td>
					</tr>
				</table>
			</div>
		</div>';
		$html .= 	'<div id="qimenubar">' .
						//'<span class="qibutton" onclick="qihelper.showLoadDialogue()">' . wfMsg('smw_qi_load') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
						//'<span class="qibutton" onclick="qihelper.showSaveDialogue()">' . wfMsg('smw_qi_save') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
						'<button class="btn" onclick="qihelper.previewQuery()" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'">' . wfMsg('smw_qi_preview') . '</button>'.
						'<button class="btn" onclick="qihelper.copyToClipboard()" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'">' . wfMsg('smw_qi_clipboard') . '</button>'.
						'<span style="position:absolute; right:13px;"><button class="btn" onclick="qihelper.resetQuery()" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'">' . wfMsg('smw_qi_reset') . '</button></span>'.

						//'<input type="button" value="' . wfMsg('smw_qi_preview') . '" class="btn" onclick="qihelper.previewQuery()" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"/>'.
						//'<input type="button" value="' . wfMsg('smw_qi_clipboard') . '" class="btn" onclick="qihelper.copyToClipboard()" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"/>'.
						//'<span class="qibutton" onclick="qihelper.exportToXLS()">' . wfMsg('smw_qi_exportXLS') . '</span>' .
						//'<span style="position:absolute; right:13px;"><input type="button" value="' . wfMsg('smw_qi_reset') . '" class="btn" onclick="qihelper.resetQuery()" onmouseover="this.className=\'btn btnhov\'" onmouseout="this.className=\'btn\'"/></span>'.
					'</div>';

		$html .= '<div id="fullpreviewbox" style="display:none">';
		$html .= '<div id="fullpreview"></div>';
		$html .= '<span class="qibutton" onclick="$(\'fullpreviewbox\', \'shade\').invoke(\'toggle\')"><img src="'. $imagepath. 'delete.png"/>' . wfMsg('smw_qi_close_preview') . '</span></div>';
		$html .= '</div>';

		$html .= '<div id="resetdialogue" style="display:none">' .
				'Do you really want to reset your query?<br/>' .
				'<span class="qibutton" onclick="qihelper.doReset()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$(\'resetdialogue\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_cancel') . '</span>' .
				'</div>';

		$html .= '<div id="savedialogue" style="display:none">' .
				'Please enter a query name:<br/>' .
				'<input type="text" id="saveName"/><br/>' .
				'<span class="qibutton" onclick="qihelper.doSave()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$(\'savedialogue\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_cancel') . '</span>' .
				'</div>';

		$wgOut->addHTML($html);
	}

}

?>