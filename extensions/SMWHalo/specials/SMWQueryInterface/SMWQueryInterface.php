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

        $html .= $this->addMainTab();

        $html .= '<div id="qiMaintabQueryCont">';
        $html .= $this->addQueryOption();
        
		$html .= $this->addQueryDefinition();

        $html .= $this->addResultPart();

		$html .= $this->addAdditionalStuff();
        $html .= '</div>';

        $html .= '<div id="qiMaintabLoadCont" style="display:none">';
        $html .= $this->addLoadQuery();
        $html .= '</div>';
        
        if ($smwgDeployVersion) {
		      $html .= '<script type="text/javascript" src="' . $smwgHaloScriptPath .  '/scripts/QueryInterface/deployQueryInterface.js"></script>';
        } else {
              $html .= '<script type="text/javascript" src="' . $smwgHaloScriptPath .  '/scripts/QueryInterface/qi_tooltip.js"></script>';
        }
		$html .= '</div></div>';
		$wgOut->addHTML($html);
	}

    private function addMainTab() {
        return  '<div id="qiMainTab"><table>
                 <tr>
                 <td id="qiMainTab1" class="qiDefTabActive" onclick="qihelper.switchMainTab();"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_maintab_query') . '\')"><span class="qiMainTabLabel">'.wfMsg('smw_qi_maintab_query').'</span></td>
                 <td class="qiDefTabSpacer"> </td>
                 <td id="qiMainTab2" class="qiDefTabInactive" onclick="qihelper.switchMainTab();"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_maintab_load') . '\')"><span class="qiMainTabLabel">'.wfMsg('smw_qi_maintab_load').'</span></td>
                 <td class="qiDefTabSpacer" width="100%">&nbsp;</td>
                 </tr>
                 </table>';
    }

    private function addLoadQuery() {
        $selection = array(
            '*' => wfMsg('smw_qi_load_selection_*'),
            'i' => wfMsg('smw_qi_load_selection_i'),
            'q' => wfMsg('smw_qi_load_selection_q'),
            'c' => wfMsg('smw_qi_load_selection_c'),
            'p' => wfMsg('smw_qi_load_selection_p'),
            's' => wfMsg('smw_qi_load_selection_s'),
            'r' => wfMsg('smw_qi_load_selection_r')
        );
        $html = wfMsg('smw_qi_load_criteria') . '<br/><select id="qiLoadCondition" onchange="qihelper.updateSearchAc();">';
        foreach ($selection as $key => $val) {
            $html.= '<option value="'.$key.'">'.$val.'</option>';
        }
        $html.= '</select>' .
                '<input type="text" size="40" id="qiLoadConditionTerm" class="wickEnabled" constraints="namespace: 14,102,0" />'.
                '<input type="submit" name="qiLoadConditionSubmit" value="'.wfMsg('smw_qi_button_search').'" onclick="qihelper.searchQueries();" />'.
                '&nbsp; | &nbsp;<a href="javascript:void(0);" onclick="qihelper.resetSearch();">'.wfMsg('smw_qi_link_reset_search').'</a>'.
                '<hr/>'.
                '<div id="qiLoadTabResult">'.
                    '<table class="qiLoadTabResultHead">'.
                        '<tr>'.
                            '<td class="qiLoadTabResultHeadLeft">'.
                                wfMsg('smw_qi_loader_result') .
                            '</td><td class="qiLoadTabResultHeadRight">'.
                            '</td>'.
                        '</tr>'.
                    '</table>'.
                    '<div class="dragboxFloat">'.
                        '<table id="qiLoadTabResultTable" class="qiLoadTabResultTable">'.
                            '<tr class="qiLoadTabResultTableFirstRow">'.
                                '<th>'.wfMsg('smw_qi_loader_qname').'</th>'.
                                '<th>'.wfMsg('smw_qi_loader_qprinter').'</th>'.
                                '<th>'.wfMsg('smw_qi_loader_qpage').'</th>'.
                            '</tr>'.
                        '</table>'.
                    '</div>'.
                    '<div id="qiDefTabInLoad"></div>'.
                    '<input type="submit" id="qiLoadQueryButton" value="'.wfMsg('smw_qi_button_load').'" onclick="qihelper.loadSelectedQuery();" />'.
                '</div>';
        return $html;
    }

    private function addQueryOption() {
        global $smwgDefaultStore, $smwgTripleStoreGraph;
        $useTS = "";
        $useLodDatasources = "";
        $useLodTrustpolicy = "";

   		if (defined('LOD_LINKEDDATA_VERSION')) { // LinkedData extension is installed
            $useLodDatasources = $this->getLodDatasources();
            $useLodTrustpolicy = $this->getLodTrustpolicy();
        }
        // check if triple store is availabe, and offer option do deselect
        if (isset($smwgDefaultStore) && strpos($smwgDefaultStore, "SMWTripleStore") !== false) {
			$useTS = '<input type="checkbox" id="usetriplestore" checked="checked">' . wfMsg('smw_qi_usetriplestore') . '</input>';
		}
        // check if there are any options that will be displayed, If this is not the case
        // then ommit this section
        if (strlen($useLodDatasources) == 0 &&
            strlen($useLodTrustpolicy) == 0 &&
            strlen($useTS) == 0) return "";

        $tsn = TSNamespaces::getInstance();
        $user_ns = $tsn->getNSPrefix(NS_USER);
        $html = '<div id="qioptiontitle"><span onclick="qihelper.switchOption()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_option') . '\')"><a id="qioptiontitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_section_option') . '</span></div>' .
                '<div id="qioptionlayout">' .
                '<div id="qioptioncontent" style="display:none">' .
                '<span id="qi_tsc_wikigraph" style="display:none">'.$smwgTripleStoreGraph.'</span>'.
                '<span id="qi_tsc_userns" style="display:none">'.$user_ns.'</span>'.
                $useTS .
                $useLodDatasources .
                $useLodTrustpolicy .
                '</div>'.
                '</div>';
        return $html;
    }

    private function addQueryDefinition() {
        /*
         * <span class="'.(($collapsed) ? 'qiSectionClosed' : 'qiSectionOpen').'"
                      onclick="qihelper.sectionCollapse(\'querylayout\')>'.wfMsg('smw_qi_layout_manager').'</span>
         */
		$html = '<div id="definitiontitle"><span onclick="qihelper.switchDefinition()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_qdef') . '\')"><a id="definitiontitle-link" class="minusplus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_section_definition') . '</span></div>
                 <table id="qiquerydefinition">'.
                    '<tr><td class="qiaddbuttons">'.
                        wfMsg('smw_qi_queryname') . ' <input id="qiQueryName" type="text" size="40" />'.
                    '</td></tr>'.
                    '<tr><td id="qiaddbuttons" class="qiaddbuttons">' .
                        '<button onclick="qihelper.newCategoryDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addCategory') . '\')">' . wfMsg('smw_qi_add_category') . '</button>'.
                        '<button onclick="qihelper.newPropertyDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addProperty') . '\')">' . wfMsg('smw_qi_add_property') . '</button>'.
                        '<button onclick="qihelper.newInstanceDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addInstance') . '\')">' . wfMsg('smw_qi_add_instance') . '</button>'.
                    '</td></tr><tr><td>'.
                        $this->addDragbox().
                        $this->addTabHeaderForQIDefinition().
                    '</td></tr>'.
                '</table>';
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
                '<div id="qisourceButtons">'.
                    '<button onclick="qihelper.loadFromSource(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_update') . '\')">' . wfMsg('smw_qi_update') . '</button>'.
                    '&nbsp;<span class="qibutton" onclick="qihelper.discardChangesOfSource();">' . wfMsg('smw_qi_discard_changes') . '</span>&nbsp;' .
                '</div>'.
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

		$blacklist = array("rss", "json", "exceltable", "icalendar", "vcard", "calendar", "debug", "template", "aggregation",
                           "tixml", "transposed", "simpletable" );

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
		global $smwgHaloScriptPath;
		wfRunHooks("QI_AddButtons", array (&$buttons));

		$imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';
		$isIE = (isset($_SERVER['HTTP_USER_AGENT']) &&
                 (preg_match('/MSIE \d+\.\d+/', $_SERVER['HTTP_USER_AGENT']) ||
                  stripos($_SERVER['HTTP_USER_AGENT'], 'Excel Bridge') !== false)
                );
		return '<div id="qimenubar">' .
		//'<span class="qibutton" onclick="qihelper.showLoadDialogue()">' . wfMsg('smw_qi_load') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
		//'<span class="qibutton" onclick="qihelper.showSaveDialogue()">' . wfMsg('smw_qi_save') . '</span><span style="color:#C0C0C0">&nbsp;|&nbsp;</span>' .
		//'<span class="qibutton" onclick="qihelper.exportToXLS()">' . wfMsg('smw_qi_exportXLS') . '</span>' .
		      (($isIE) ? '<button onclick="qihelper.copyToClipboard()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_clipboard') . '\')">' . wfMsg('smw_qi_clipboard') . '</button>' : '').
		$buttons.
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

    private function getLodDatasources() {
        $sourceOptions = '<option selected="selected">-Wiki-</option>'; // default fist option is the wiki itself
        $lodDatasources = '<hr />'.wfMsg('smw_qi_datasource_select_header') . ':';
		// Check if the triples store is propertly connected.
		$tsa = new LODTripleStoreAccess();
		if (!$tsa->isConnected()) {
			$lodDatasources .= " <span class=\"qiConnectionError\">".wfMsg("smw_ob_ts_not_connected")."</span>";
		}
        else {
            $ids = LODAdministrationStore::getInstance()->getAllSourceDefinitionIDs();
            foreach ($ids as $sourceID) {
                $sourceOptions .= "<option>$sourceID</option>";
        	}
        }
        $lodDatasources .= '<br/><table><tr><td>' .
            '<select id="qidatasourceselector" size="5" multiple="true" onchange="qihelper.clickUseTsc();">' .
                $sourceOptions .
            '</select>' .
            '</td><td>' .
            /*
                '<input type="checkbox" id="qio_showrating" onchange="qihelper.clickUseTsc();" />' .
                wfMsg('smw_qi_showdatarating') . '<br/>' .
             */
            '<input type="checkbox" id="qio_showmetadata" value="*" onchange="qihelper.clickMetadata();" />' .
            wfMsg('smw_qi_showmetadata') . '<br/>' .
            '<div id="qio_showdatasource_div" style="display:none">'.
            '<input type="checkbox" id="qio_showdatasource" onchange="qihelper.clickMetadata();" />' .
            wfMsg('smw_qi_showdatasource') . '<br/></div>' .
            '</td></tr></table>';
        return $lodDatasources;
    }
    private function getLodTrustpolicy() {
        global $smwgHaloScriptPath;
        $tps = LODPolicyStore::getInstance();
        $policyIds = $tps->getAllPolicyIDs();
        $is = count($policyIds);
		if ($is > 0) {
            $paramText = '';
            $text = '<select id="qitpeeselector" size="5" style="width:400px" onchange="qihelper.clickTpee();">'.
                    '<option value="__NONE__" selected="selected">'.wfMsg('smw_qi_tpee_none').'</option>';
            for ($i = 0; $i < $is; $i++) {
                $policy = $tps->loadPolicy($policyIds[$i]);
                $params = $policy->getParameters();
                $paramText .= '<div id="qitpeeparams_'.$policyIds[$i].'" style="display:none"><table>';
                foreach ($params as $param) {
                    if (! $param->getName() ) continue;
                    $paramText .= 
                            '<tr><td>'.
                            ( ($param->getLabel())
                                ? '<span name="qitpeeparams_'.$policyIds[$i].'_'.$param->getName().'">'.$param->getLabel().'</span>'
                                : '<span>'.$param->getName().'</span>'
                            ).
                            '</td><td>'.
                            $this->getInputFieldTpeeParam($policyIds[$i], $param->getName()).
                            '</td>'.
                            ( ($param->getDescription())
                                ? '<td><img src="'.$smwgHaloScriptPath . '/skins/QueryInterface/images/help.gif" onmouseover="Tip(\''.
                                  str_replace("'", "\'", $param->getDescription()).'\');" /></td>'
                                : '<td> </td>' ).
                            '</td></tr>';
                }
                $paramText .= '</table></div>';
                $text .= '<option value="'.$policyIds[$i].'">'.$policy->getDescription().'</option>';
            }
            $text .= '</select>';
            return '<hr />'.wfMsg('smw_qi_tpee_header') .':<br/><table><tr><td>'.$text.'</td><td>'.$paramText.'</td></tr></table>';
        }
        
        return '';
    }
    private function getInputFieldTpeeParam($policyId, $paramName) {
        global $smwgHaloScriptPath;
        if ($paramName == 'PAR_USER') {
            return '<input id="qitpeeparamval_'.$policyId.'_'.$paramName.'" type="text" size="20" '.
                   'class="wickEnabled" constraints="namespace: 2" autocomplete="OFF"/>';
        }
        if ($paramName == 'PAR_ORDER') {
            return '<div class="qitpeeparamval">'.
                   '<table id="qitpeeparamval_'.$policyId.'_'.$paramName.'"></table>'.
                   '</div>'.
                   '<span style="vertical-align: center">'.
                   '<img src="'.$smwgHaloScriptPath.'/skins/QueryInterface/images/up.png" alt="up" onclick="qihelper.tpeeOrder(\'up\');" />'.
                   '<img src="'.$smwgHaloScriptPath.'/skins/QueryInterface/images/down.png" alt="down" onclick="qihelper.tpeeOrder(\'down\');" />'.
                   '</span>';
        }
        return '<input id="qitpeeparamval_'.$policyId.'_'.$paramName.'" type="text" size="20"/>';    
    }

}

