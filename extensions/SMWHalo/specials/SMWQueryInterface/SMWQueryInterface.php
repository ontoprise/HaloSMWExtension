<?php
/**
 * @file
 * @ingroup SMWHaloQueryInterface
 * 
 * @defgroup SMWHaloQueryInterface SMWHalo Query Interface
 * @ingroup SMWHaloSpecials
 * @author Markus Nitsche
 */
if (!defined('MEDIAWIKI')) die();



global $IP;
require_once( $IP . "/includes/SpecialPage.php" );
require_once( "SMW_QIAjaxAccess.php" );

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWQueryInterface extends SpecialPage {
    private $imagepath;     // image path for all QI icons
    
	public function __construct() {
		parent::__construct('QueryInterface');
	}
	/*
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute($par) {

		global $wgRequest, $wgOut, $smwgHaloScriptPath, $smwgDeployVersion;

		$this->imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';

		$wgOut->setPageTitle(wfMsg('smw_queryinterface'));

		$html = '<div id="qicontent">' .
				'<div id="shade" style="display:none"></div>';

		$html .= $this->addQueryDefinition();

        $html .= $this->addResultPart();

		$html .= $this->addAdditionalStuff();
        if ($smwgDeployVersion)
		      $html .= '<script type="text/javascript" src="' . $smwgHaloScriptPath .  '/scripts/QueryInterface/deploy_qi_tooltip.js"></script>';
		else
              $html .= '<script type="text/javascript" src="' . $smwgHaloScriptPath .  '/scripts/QueryInterface/qi_tooltip.js"></script>';		
		$html .= '</div>';
		$wgOut->addHTML($html);
	}

    private function addQueryDefinition() {
        /*
         * <span class="'.(($collapsed) ? 'qiSectionClosed' : 'qiSectionOpen').'"
                      onclick="qihelper.sectionCollapse(\'querylayout\')>'.wfMsg('smw_qi_layout_manager').'</span>
         */
		$html = '<div id="definitiontitle"><span onclick="qihelper.switchDefinition()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_qdef') . '\')"><a id="definitiontitle-link" class="minusplus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_section_definition') . '</span></div>
                 <table id="qiquerydefinition"><tr><td id="qiaddbuttons" class="qiaddbuttons">' .
      				'<button onclick="qihelper.newCategoryDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addCategory') . '\')">' . wfMsg('smw_qi_add_category') . '</button>'.
          			'<button onclick="qihelper.newPropertyDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addProperty') . '\')">' . wfMsg('smw_qi_add_property') . '</button>'.
                    '<button onclick="qihelper.newInstanceDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addInstance') . '\')">' . wfMsg('smw_qi_add_instance') . '</button>'.
                '</td></tr><tr><td>'.
                    $this->addDragbox().
                    $this->addTabHeaderForQIDefinition().
                '</td></tr></table>';
        return $html;
    }

    private function addTabHeaderForQIDefinition() {
        $html = '<div id="qiDefTab"><table>
                 <tr>
                 <td id="qiDefTab1" class="qiDefTabActive" onclick="qihelper.switchTab(1);"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_treeview') . '\')">'.wfMsg('smw_qi_queryastree').'</td>
                 <td class="qiDefTabSpacer"> </td>'.
                 /*
                 <td id="qiDefTab2" class="qiDefTabInactive" onclick="qihelper.switchTab(2);"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_textview') . '\')">'.wfMsg('smw_qi_queryastext').'</td>
                 <td class="qiDefTabSpacer" width="100%">&nbsp;</td> */
                 '
                 <td id="qiDefTab3" class="qiDefTabInactive" onclick="qihelper.switchTab(3);"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_showAsk') . '\')">'.wfMsg('smw_qi_querysource').'</td>
                 <td class="qiDefTabSpacer" width="100%">&nbsp;</td>
                 </tr>
                 </table>
                 <div class="qiDefTabContent">'.
                 $this->addTreeView().
                '<div id="qitextview">Query as text</div>
                 <div id="qisource"><textarea id="fullAskText" onchange="qihelper.sourceChanged=1"></textarea>'.
                '<button onclick="qihelper.loadFromSource(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_update') . '\')">' . wfMsg('smw_qi_update') . '</button>'.
                '&nbsp;<span class="qibutton" onclick="qihelper.discardChangesOfSource();">' . wfMsg('smw_qi_discard_changes') . '</span>&nbsp;' .
                '</div>'.
                '</div></div>
        ';
        return $html;
    }
    /**
     * Return html code for treeview of query.
     * 
     * @return string html
     */
	private function addTreeView() {
		return '<div id="treeview">' .
                    '<div id="treeanchor">' .
                        '<div id="qitreedummy"></div>' .
                    '</div>' .
				'</div>';		
	}

	private function addDragbox() {
		global $smwgHaloScriptPath;

		return '<div id="dragbox" class="dragbox">' .
                    '<div id="treeviewbreadcrumbs"></div>' .
                    '<div id="qistatus"></div>' .
                    '<div id="boxcontent"><table><tbody id="dialoguecontent"></tbody></table></div>' .
                    '<div id="dialoguebuttons" style="display:none; width: 100%">' .
						'<button onclick="qihelper.add()">' . wfMsg('smw_qi_add') . '</button>&nbsp;' .
                        '<span style="text-align:right">' .
                             '<span class="qibutton" onclick="qihelper.emptyDialogue(); qihelper.updateTree();">' . wfMsg('smw_qi_cancel') . '</span>&nbsp;' .
                             '<span id="qidelete" style="display:none" class="qibutton" onclick="qihelper.deleteActivePart()">' . wfMsg('smw_qi_delete') . '</span>' .
                        '</span>' .
                    '</div>' .
               '</div>';
	}

    private function addResultPart() {
        $html = '<div id="qiresulttitle"><span onclick="qihelper.switchResult()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_previewres') . '\')"><a id="qiresulttitle-link" class="minusplus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_section_result') . '</span></div>'.
                '<div id="qiresultcontent">'.
                $this->addQueryLayout().
                $this->addPreviewResults().
                '</div>';
        return $html;
    }

	private function addQueryLayout() {

		global $smwgResultFormats;

		$blacklist = array("rss", "json", "exceltable", "icalendar", "vcard", "calendar", "ofc", "exhibit", "debug", "template", "aggregation");

		$resultoptionshtml = "";
        $resultPrinters = array();
        
		reset($smwgResultFormats);
		while (current($smwgResultFormats)) {
			if (!in_array(key($smwgResultFormats), $blacklist)) {
                $className = $smwgResultFormats[key($smwgResultFormats)];
                $class = new $className(key($smwgResultFormats), null);
                // format the OFC printer names a bit, the rest comes with a real name
                $label = (substr(key($smwgResultFormats), 0, 4) == "ofc-")
                    ? 'OFC '.str_replace(array('_', '-'), ' ', substr(key($smwgResultFormats), 4))
                    : ucfirst($class->getName());
                $label .= ' ('.key($smwgResultFormats).')';
                $resultPrinters[$label] = key($smwgResultFormats);
			}
			next($smwgResultFormats);
		}
        ksort($resultPrinters);
        foreach ($resultPrinters as $k => $v) {
            $selected = ($v == "table") ? 'selected="selected"' : '';
			$resultoptionshtml .= '<option value="'.$v.'"'.$selected.'>'.$k.'</option>';
        }
        $fullPreviewLink = '';
        global $smwgQIResultPreview;
        if (isset($smwgQIResultPreview) && $smwgQIResultPreview === false)
            $fullPreviewLink = '&nbsp;|&nbsp; <a href="javascript:void(0);" onclick="qihelper.previewQuery()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_fullpreview') . '\')">' . wfMsg('smw_qi_fullpreview') . '</a>';
        return '<div id="querylayout">
					<div id="layouttitle">
                        <span onclick="qihelper.switchlayout()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_qlm') . '\')"><a id="layouttitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_layout_manager') . '</span>
                        '.$fullPreviewLink.'
					</div>
					<div id="layoutcontent" style="display:none">
                        <table summary="Layout Manager for query">
                            <tr>
        						<td width="50%" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_format') . '\')">
                					Format: <select id="layout_format" onchange="qihelper.checkFormat()">
                                    '. $resultoptionshtml .'
                                    </select>
                                </td>
                                <td onmouseover="Tip(\'' . wfMsg('smw_qi_tt_sort') . '\') ">
                                    Sort by: <select id="layout_sort" onchange="qihelper.updateSrcAndPreview()">
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div id="queryprinteroptions" style="display:none"></div>
                </div>';
	}

	private function addPreviewResults() {
	    global $smwgQIResultPreview;
	    if (isset($smwgQIResultPreview) && $smwgQIResultPreview === false)
            return '<div id="previewcontent" style="display:none"></div>';
		return '<div id="previewlayout">
    	       		<div id="previewtitle"><span onclick="qihelper.switchpreview()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_prp') . '\')"><a id="previewtitle-link" class="minusplus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_preview_result') . '</span>
                    &nbsp;|&nbsp; <a href="javascript:void(0);" onclick="qihelper.previewQuery()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_fullpreview') . '\')">' . wfMsg('smw_qi_fullpreview') . '</a></div>
    				<div id="previewcontent"></div>
                </div>';
		
	}

	private function addAdditionalStuff() {
		global $smwgHaloScriptPath, $smwgDefaultStore;
		wfRunHooks("QI_AddButtons", array (&$buttons));

		$imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';
		$useTS = "";
		$isIE = (isset($_SERVER['HTTP_USER_AGENT']) &&
                 (preg_match('/MSIE \d+\.\d+/', $_SERVER['HTTP_USER_AGENT']) ||
                  stripos($_SERVER['HTTP_USER_AGENT'], 'Excel Bridge') !== false)
                );
		if (isset($smwgDefaultStore) && $smwgDefaultStore == "SMWTripleStore") {
			$useTS = '<input type="checkbox" id="usetriplestore" checked="checked">' . wfMsg('smw_qi_usetriplestore') . '</input>';
		}
		return '<div id="qimenubar">' .
		//'<span class="qibutton" onclick="qihelper.showLoadDialogue()">' . wfMsg('smw_qi_load') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
		//'<span class="qibutton" onclick="qihelper.showSaveDialogue()">' . wfMsg('smw_qi_save') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
		//'<span class="qibutton" onclick="qihelper.exportToXLS()">' . wfMsg('smw_qi_exportXLS') . '</span>' .
		      (($isIE) ? '<button onclick="qihelper.copyToClipboard()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_clipboard') . '\')">' . wfMsg('smw_qi_clipboard') . '</button>' : '').
		$buttons.
		$useTS.
						'<span><button onclick="qihelper.resetQuery()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_reset') . '\')">' . wfMsg('smw_qi_reset') . '</button></span>'.
					'</div>'.

				'<div id="fullpreviewbox" style="display:none">'.
				'<div id="fullpreview"></div>'.
				'<span class="qibutton" onclick="$(\'fullpreviewbox\', \'shade\').invoke(\'toggle\'); qihelper.reloadOfcPreview()"><img src="'. $imagepath. 'delete.png"/>' . wfMsg('smw_qi_close_preview') . '</span></div>'.
				'</div>'.

				'<div id="resetdialogue" class="topDialogue" style="display:none">' .
						'Do you really want to reset your query?<br/>' .
						'<span class="qibutton" onclick="qihelper.doReset()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$(\'resetdialogue\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_cancel') . '</span>' .
						'</div>'.

				'<div id="showAsk" class="topDialogue" style="display:none;">' .
						'<span id="showParserAskButton" class="qibutton">' . wfMsg('smw_qi_parserask') . '</span><br/><hr/>' .
						'<div><textarea id="fullAskTextOld" rows="10" readonly></textarea></div>' .
						'<span class="qibutton" onclick="$(\'showAsk\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_close') . '</span>' .
						'</div>'.

				'<div id="savedialogue" class="topDialogue" style="display:none">' .
						'Please enter a query name:<br/>' .
						'<input type="text" id="saveName"/><br/>' .
						'<span class="qibutton" onclick="qihelper.doSave()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$(\'savedialogue\', \'shade\').invoke(\'toggle\')">' . wfMsg('smw_qi_cancel') . '</span>' .
						'</div>' .
				'<div id="query4DiscardChanges" style="display:none"></div>';
	}

}

