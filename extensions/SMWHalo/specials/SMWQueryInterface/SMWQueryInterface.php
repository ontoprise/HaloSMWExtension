<?php

 if (!defined('MEDIAWIKI')) die();



global $IP, $wgHooks;
require_once( $IP . "/includes/SpecialPage.php" );
require_once( "SMW_QIAjaxAccess.php" );
$wgHooks['BeforePageDisplay'][]='smwfQIAddHTMLHeader';

// standard functions for creating a new special page
function doSMWQueryInterface()  {
		SMWQueryInterface::execute();
}

SpecialPage::addPage( new SpecialPage('QueryInterface','',true,'doSMWQueryInterface',false)) ;

function smwfQIAddHTMLHeader(&$out){
	global $smwgHaloScriptPath;


	$jsm = SMWResourceManager::SINGLETON();

	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/treeviewQI.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/queryTree.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/Query.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/QIHelper.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/qi.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Language/SMW_Language.js',  "all", -1, NS_SPECIAL.":QueryInterface");

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/treeview.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/qi.css', "all", -1, NS_SPECIAL.":QueryInterface");

	return true;
}

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWQueryInterface{

/*
 * Overloaded function that is resopnsible for the creation of the Special Page
 */
	static function execute() {

		global $wgRequest, $wgOut, $smwgHaloScriptPath;

		$wgOut->setPageTitle("Query Interface");

		$imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';

		$html = '<div id="qicontent">' .
				'<div id="shade" style="display:none"></div>' .
				'<div id="qimenubar">' .
					'<span class="qibutton" onclick="qihelper.previewQuery()">' . wfMsg('smw_qi_preview') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
					'<span class="qibutton" onclick="qihelper.copyToClipboard()">' . wfMsg('smw_qi_clipboard') . '</span>' .
					'<span style="position:absolute; right:10px;"><span class="qibutton" onclick="qihelper.resetQuery()">' . wfMsg('smw_qi_reset') . '</span></span>' .
				'</div>';

		$html .= '<div id="treeview">' .
				'<div id="treeviewheader" class="qiboxheader">' .
				'&nbsp;' . wfMsg('smw_qi_querytree_heading') .
				'</div>' .
				'<div id="treeviewbreadcrumbs"></div>' .
				'<div id="treeanchor"><div id="qitreedummy"></div></div>' .
				'</div>';

		$html .= '<div id="qiaddbuttons" class="qiaddbuttons">' .
					'<span id="cat" class="qibutton" onclick="qihelper.newCategoryDialogue(true)"><img src="' . $imagepath . 'category.gif" alt="category" />' . wfMsg('smw_qi_add_category') . '</span>' .
					'<span id="ins" class="qibutton" onclick="qihelper.newInstanceDialogue(true)"><img src="' . $imagepath . 'instance.gif" alt="instance" />' . wfMsg('smw_qi_add_instance') . '</span>' .
					'<span id="prop" class="qibutton" onclick="qihelper.newPropertyDialogue(true)"><img src="' . $imagepath . 'property.gif" alt="property"/>' . wfMsg('smw_qi_add_property') . '</span>' .
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
						'<div id="tcp_boxcontent" class="tcp_boxcontent" style="visibility:hidden">' .
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
							<input type="text" size="18" id="layout_limit" value="50"/>
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
							<input type="text" size="18" id="layout_intro"/>
						</td>
						<td>
							Mainlabel:
						</td><td>
							<input type="text" size="18" id="layout_label" value="Article title"/>
						</td>
						<td>
							Default:
						</td><td>
							<input type="text" size="18" id="layout_default"/>
						</td>
					</tr>
				</table>
			</div>
		</div>';

		$html .= '<div id="fullpreviewbox" style="display:none">';
		$html .= '<div id="fullpreview"></div>';
		$html .= '<span class="qibutton" onclick="$(\'fullpreviewbox\', \'shade\').invoke(\'toggle\')"><img src="'. $imagepath. 'delete.png"/>' . wfMsg('smw_qi_close_preview') . '</span></div>';
		$html.=	'</div>';

		$html .= '<div id="resetdialogue" style="display:none">' .
				'Do you really want to reset your query?<br/>' .
				'<span class="qibutton" onclick="qihelper.doReset()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$(\'resetdialogue\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_cancel') . '</span>' .
				'</div>';

		$wgOut->addHTML($html);
	}

}

?>