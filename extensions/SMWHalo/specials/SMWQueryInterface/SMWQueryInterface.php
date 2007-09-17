<?php

 if (!defined('MEDIAWIKI')) die();



global $IP, $wgHooks;
require_once( $IP . "/includes/SpecialPage.php" );
require_once($smwgHaloIP."/includes/SMW_ResourceManager.php");

require_once( "SMW_QIAjaxAccess.php" );
$wgHooks['BeforePageDisplay'][]='smwfQIAddHTMLHeader';

// standard functions for creating a new special page
function doSMWQueryInterface()  {
		SMWQueryInterface::execute();
}

SpecialPage::addPage( new SpecialPage('QueryInterface','',true,'doSMWQueryInterface',false)) ;

function smwfQIAddHTMLHeader(&$out){
	global $smwgScriptPath;


	$jsm = SMWResourceManager::SINGLETON();

	$jsm->addScriptIf($smwgScriptPath .  '/skins/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgScriptPath .  '/skins/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgScriptPath .  '/skins/QueryInterface/treeviewQI.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgScriptPath .  '/skins/QueryInterface/queryTree.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgScriptPath .  '/skins/QueryInterface/Query.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgScriptPath .  '/skins/QueryInterface/QIHelper.js', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addScriptIf($smwgScriptPath .  '/skins/QueryInterface/qi.js', "all", -1, NS_SPECIAL.":QueryInterface");

	$jsm->addCSSIf($smwgScriptPath . '/skins/QueryInterface/treeview.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgScriptPath . '/skins/QueryInterface/qi.css', "all", -1, NS_SPECIAL.":QueryInterface");

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

		global $wgRequest, $wgOut, $smwgScriptPath;

		$wgOut->setPageTitle("Query Interface");

		$imagepath = $smwgScriptPath . '/skins/QueryInterface/images/';

		$html = '<div id="qicontent">' .
				'<div id="shade" style="display:none"></div>' .
				'<div id="qimenubar">' .
					'<span class="qibutton" onclick="qihelper.previewQuery()">Preview Results</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
					'<span class="qibutton" onclick="qihelper.copyToClipboard()">Copy to clipboard</span>' .
					'<span style="padding-left:320px"><span class="qibutton" onclick="qihelper.resetQuery()">Reset Query</span></span>' .
					//'<span class="qibutton" onclick="export(\'xls\')">Export to Excel</span>' .
				//	'<span class="qibutton" onclick="export(\'ods\')">Export to ODS</span>' .
				'</div>';

		$html .= '<div id="treeview">' .
				'<div id="treeviewheader" class="qiboxheader">' .
				'&nbsp;Query Tree' .
				'</div>' .
				'<div id="treeviewbreadcrumbs"></div>' .
				'<div id="treeanchor"><div id="qitreedummy"></div></div>' .
				'</div>';

		$html .= '<div id="qiaddbuttons" class="qiaddbuttons">' .
					'<span id="cat" class="qibutton" onclick="qihelper.newCategoryDialogue(true)"><img src="' . $imagepath . 'category.gif" alt="category" />Add Category</span>' .
					'<span id="ins" class="qibutton" onclick="qihelper.newInstanceDialogue(true)"><img src="' . $imagepath . 'instance.gif" alt="instance" />Add Instance</span>' .
					'<span id="prop" class="qibutton" onclick="qihelper.newPropertyDialogue(true)"><img src="' . $imagepath . 'property.gif" alt="property"/>Add Property</span>' .
				'</div>';

		$html .= '<div id="dragbox" class="dragbox">' .
					'<div id="boxcontent" class="boxcontent">' .
						'<table><tbody id="dialoguecontent"></tbody></table>' .
						'<div id="dialoguebuttons" style="display:none">' .
							'<span class="qibutton" onclick="qihelper.add()">OK</span>&nbsp;<span class="qibutton" onclick="qihelper.emptyDialogue()">Cancel</span>&nbsp;<span id="qidelete" style="display:none" class="qibutton" onclick="qihelper.deleteActivePart()">Delete</span>' .
						'</div>' .
						'<div id="qistatus"></div>' .
					'</div>' .
					'<div id="tablecolumnpreview">' .
						'<div class="tcp_boxheader" onclick="switchtcp()"><a id="tcptitle-link" class="plusminus" href="javascript:void(0)"></a>Table Column Preview</div>' .
						'<div id="tcp_boxcontent" class="tcp_boxcontent" style="visibility:hidden">' .
							'<div id="tcpcontent"><table id="tcp" summary="Preview of table columns">' .
								'<tr><td>No preview available yet</td></tr>' .
							'</table></div>' .
						'</div>' .
					'</div>' .
				'</div>';

		$html .= '<div id="selectbox" class="selectbox" style="display:none">
					<div class="selectboxheader">Available Completions</div>
					<div class="selectboxcontent">
						Filter:&nbsp;<input type="text" id="qifilter" onkeyup="filter(this, \'selecttable\', 0)"/>
						&nbsp;<a href="javascript:update()"><img src="' . $smwgScriptPath . '/skins/redcross.gif" style="vertical-align: text-top;"/></a><hr>
						<div><table id="selecttable"><tr><td>No completions available yet</td></tr></table></div>
					</div>
				</div>';


		$html .= '<div id="querylayout">
					<div id="layouttitle" onclick="switchlayout()"><a id="layouttitle-link" class="plusminus" href="javascript:void(0)"></a>Query Layout Manager</div>
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
		$html .= '<span class="qibutton" onclick="$(\'fullpreviewbox\', \'shade\').invoke(\'toggle\')"><img src="'. $imagepath. 'delete.png"/>Close Preview</span></div>';
		$html.=	'</div>';

		$html .= '<div id="resetdialogue" style="display:none">' .
				'Do you really want to reset your query?<br/>' .
				'<span class="qibutton" onclick="qihelper.doReset()">Yes, reset</span>&nbsp;<span class="qibutton" onclick="$(\'resetdialogue\', \'shade\').invoke(\'toggle\')">No, cancel</span>' .
				'</div>';

		$wgOut->addHTML($html);
	}

}

?>