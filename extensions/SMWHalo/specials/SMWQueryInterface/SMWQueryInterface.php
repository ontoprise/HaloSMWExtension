<?php

/**
 * @file
 * @ingroup SMWHaloQueryInterface
 * 
 * @defgroup SMWHaloQueryInterface SMWHalo Query Interface
 * @ingroup SMWHaloSpecials
 * @author Markus Nitsche
 */
if (!defined('MEDIAWIKI'))
  die();



global $IP, $smgJSLibs;
require_once( $IP . "/includes/SpecialPage.php" );
require_once( "SMW_QIAjaxAccess.php" );

// need json lib to parse Query string -> can be removed once we use jQuery 1.4.1
$smgJSLibs[] = 'json';

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */

class SMWQueryInterface extends SpecialPage {

  private $imagepath;     // image path for all QI icons
  private $datasources;

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

    $html = '<div id="qicontent"><div id="sparqlQI" style="display:none">';

    $html .= $this->createSparqlQI();

    $html .= '</div><div id="askQI"><div id="shade" style="display:none"></div>';

    $html .= $this->addMainTab();

    $html .= '<div id="qiMaintabQueryCont">';
//    $html .= $this->addQueryOption();

    $html .= $this->addQueryDefinition();

    $html .= $this->addResultPart();

    $html .= $this->addAdditionalStuff();
    $html .= '</div>';

    $html .= '<div id="qiMaintabLoadCont" style="display:none">';
    $html .= $this->addLoadQuery();
    $html .= '</div>';

    $html .= '</div></div></div>';
    $wgOut->addHTML($html);
  }

  private function createSparqlQI() {
    $result = $this->addMainTab()
            . '<div id="qiMaintabQueryCont">'
//            . $this->addQueryOption()
            . $this->addQueryDefinitionSparql()
            . $this->addResultPartSparql()
            . $this->addAdditionalStuff()
            . '</div>'
            . '<div id="qiMaintabLoadCont" style="display:none">'
            . $this->addLoadQuery()
            . '</div>';
    return $result;
  }

  private function addMainTab() {
    return '<div id="qiMainTab"><table>
                 <tr>
                 <td id="qiMainTab1" class="qiDefTabActive" onclick="qihelper.switchMainTab();"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_maintab_query') . '\')"><span class="qiMainTabLabel">' . wfMsg('smw_qi_maintab_query') . '</span></td>
                 <td class="qiDefTabSpacer"> </td>
                 <td id="qiMainTab2" class="qiDefTabInactive" onclick="qihelper.switchMainTab();"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_maintab_load') . '\')"><span class="qiMainTabLabel">' . wfMsg('smw_qi_maintab_load') . '</span></td>
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
      $html.= '<option value="' . $key . '">' . $val . '</option>';
    }
    $html.= '</select>' .
            '<input type="text" size="40" id="qiLoadConditionTerm" class="wickEnabled" constraints="namespace: 14,102,0" />' .
            '<input type="submit" name="qiLoadConditionSubmit" value="' . wfMsg('smw_qi_button_search') . '" onclick="qihelper.searchQueries();" />' .
            '&nbsp; | &nbsp;<a href="javascript:void(0);" onclick="qihelper.resetSearch();">' . wfMsg('smw_qi_link_reset_search') . '</a>' .
            '<hr/>' .
            '<div id="qiLoadTabResult">' .
            '<table class="qiLoadTabResultHead">' .
            '<tr>' .
            '<td class="qiLoadTabResultHeadLeft">' .
            wfMsg('smw_qi_loader_result') .
            '</td><td class="qiLoadTabResultHeadRight">' .
            '</td>' .
            '</tr>' .
            '</table>' .
            '<div class="dragboxFloat">' .
            '<table id="qiLoadTabResultTable" class="qiLoadTabResultTable">' .
            '<tr class="qiLoadTabResultTableFirstRow">' .
            '<th>' . wfMsg('smw_qi_loader_qname') . '</th>' .
            '<th>' . wfMsg('smw_qi_loader_qprinter') . '</th>' .
            '<th>' . wfMsg('smw_qi_loader_qpage') . '</th>' .
            '</tr>' .
            '</table>' .
            '</div>' .
            '<div id="qiDefTabInLoad"></div>' .
            '<input type="submit" id="qiLoadQueryButton" value="' . wfMsg('smw_qi_button_load') . '" onclick="qihelper.loadSelectedQuery();" />' .
            '</div>';
    return $html;
  }

  private function addQueryOption() {
    global $smwgHaloWebserviceEndpoint, $smwgHaloTripleStoreGraph;
    $useTS = "";
    $useLodDatasources = "";
    $useLodTrustpolicy = "";
    $DS_SELECTED = 0;
    $TPEE_SELECTED = 1;

    if (defined('LOD_LINKEDDATA_VERSION')) { // LinkedData extension is installed
      $useLodDatasources = $this->getLodDatasources();
      $useLodTrustpolicy = $this->getLodTrustpolicy();
    }
    // check if triple store is availabe, and offer option do deselect
    if (isset($smwgHaloWebserviceEndpoint)) {
      $useTS = '<input type="checkbox" id="usetriplestore" checked="checked">' . wfMsg('smw_qi_usetriplestore') . '</input>';
    }
    // check if there are any options that will be displayed, If this is not the case
    // then ommit this section
    if (strlen($useLodDatasources) == 0 &&
            strlen($useLodTrustpolicy) == 0 &&
            strlen($useTS) == 0)
      return "";

    if (strlen($useLodTrustpolicy) > 0 && strlen($useLodDatasources) > 0) {
      // wiki graph and user namespace are needed for TPEE PAR_USER field for AC
      $tsn = TSNamespaces::getInstance();
      $user_ns = $tsn->getNSPrefix(NS_USER);

      $DataSourceTpeeSelector = '&nbsp;&nbsp;&nbsp;' .
              '<input type="radio" name="qiDsTpeeSelector" value="' . $DS_SELECTED . '" onchange="qihelper.selectDsTpee(' . $DS_SELECTED . ')" checked="checked"/>' .
              wfMsg('smw_qi_dstpee_selector_' . $DS_SELECTED) .
              '<input type="radio" name="qiDsTpeeSelector" value="' . $TPEE_SELECTED . '" onchange="qihelper.selectDsTpee(' . $TPEE_SELECTED . ')"/>' .
              wfMsg('smw_qi_dstpee_selector_' . $TPEE_SELECTED) .
              '<span id="qi_tsc_wikigraph" style="display:none">' . $smwgHaloTripleStoreGraph . '</span>' .
              '<span id="qi_tsc_userns" style="display:none">' . $user_ns . '</span>';
    }
    else
      $DataSourceTpeeSelector = '';

//    $html = '<div id="qioptiontitle"><span onclick="qihelper.switchOption()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_option') . '\')"><a id="qioptiontitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_section_option') . '</span></div>' .
//            '<div id="qioptionlayout">' .
    $html = '<div id="qioptioncontent">' .
            $useTS .
            $DataSourceTpeeSelector .
            $useLodDatasources .
            $useLodTrustpolicy .
//            '</div>' .
            '</div>';
    return $html;
  }

  private function addQueryDefinition() {
    /*
     * <span class="'.(($collapsed) ? 'qiSectionClosed' : 'qiSectionOpen').'"
      onclick="qihelper.sectionCollapse(\'querylayout\')>'.wfMsg('smw_qi_layout_manager').'</span>
     */
    $html = '<table id="qiquerydefinition">' .
//            '<tr><td class="qiaddbuttons">' .
//            wfMsg('smw_qi_queryname') . ' <input id="qiQueryName" type="text" size="40" />' .
//            '</td></tr>' .
//            '<tr><td id="qiaddbuttons" class="qiaddbuttons">' .
//            '<button onclick="qihelper.newCategoryDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addCategory') . '\')">' . wfMsg('smw_qi_add_category') . '</button>' .
//            '<button onclick="qihelper.newPropertyDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addProperty') . '\')">' . wfMsg('smw_qi_add_property') . '</button>' .
//            '<button onclick="qihelper.newInstanceDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addInstance') . '\')">' . wfMsg('smw_qi_add_instance') . '</button>' .
//            '</td></tr>
            '<tr><td>' .
            $this->addDragbox() .
            $this->addTabHeaderForQIDefinition() .
            '</td></tr>' .
            '</table>';
    return $html;
  }

  private function addDefinitionTitleSparql() {
    return '<div id="definitiontitle"><span onclick="qihelper.switchDefinition()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_qdef') . '\')"><a id="definitiontitle-link" class="minusplus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_section_definition') . '</span></div>
                 <table id="qiquerydefinition">';
//            '<tr><td class="qiaddbuttons">' .
//            wfMsg('smw_qi_queryname') . ' <input id="qiQueryName" type="text" size="40" />' .
//            '</td></tr>';
  }

  private function addQueryDefinitionSparql() {
    $html = $this->addDefinitionTitleSparql() .
            '<tr><td id="qiaddbuttons" class="qiaddbuttons">' .
            '<button id="qiAddSubjectBtn" title="' . wfMsg('smw_qi_tt_addSubject') . '">' . wfMsg('smw_qi_add_subject') . '</button>' .
            '</td></tr><tr><td>' .
            $this->addDragboxSparql() .
            $this->addTabHeaderForQIDefinitionSparql() .
            '</td></tr>' .
            '</table>';
    return $html;
  }

  private function addTabHeaderForQIDefinition() {
    $html = '<div id="qiDefTab"><table>
                 <tr>
                 <td id="qiDefTab1" class="qiDefTabActive" onclick="qihelper.switchTab(1);"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_treeview') . '\')">' . wfMsg('smw_qi_queryastree') . '</td>
                 <td class="qiDefTabSpacer"> </td>' .
            /*
              <td id="qiDefTab2" class="qiDefTabInactive" onclick="qihelper.switchTab(2);"
              onmouseover="Tip(\'' . wfMsg('smw_qi_tt_textview') . '\')">'.wfMsg('smw_qi_queryastext').'</td>
              <td class="qiDefTabSpacer" width="100%">&nbsp;</td> */
            '
                 <td id="qiDefTab3" class="qiDefTabInactive" onclick="qihelper.switchTab(3);"
                     onmouseover="Tip(\'' . wfMsg('smw_qi_tt_showAsk') . '\')">' . wfMsg('smw_qi_querysource') . '</td>
                 <td class="qiDefTabSpacer" width="100%">&nbsp;</td>
                 </tr>
                 </table>
                 <div class="qiDefTabContent">' .
            $this->addTreeView() .
            '<div id="qitextview">Query as text</div>
                 <div id="qisource"><textarea id="fullAskText" onchange="qihelper.sourceChanged=1"></textarea>' .
            '<div id="qisourceButtons">' .
            '<button id="qiLoadFromSourceButton" onclick="qihelper.loadFromSource(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_update') . '\')">' . wfMsg('smw_qi_update') . '</button>' .
            '&nbsp;<span class="qibutton" id="qiDiscardChangesButton" onclick="qihelper.discardChangesOfSource();">' . wfMsg('smw_qi_discard_changes') . '</span>&nbsp;' .
            '</div>' .
            '</div>' .
            '</div></div>
        ';
    return $html;
  }

  private function addTabHeaderForQIDefinitionSparql() {
    $html = '<div id="qiDefTab"><table>
                 <tr>
                 <td id="qiDefTab1" class="qiDefTabActive" title="' . wfMsg('smw_qi_tt_treeview') . '">' . wfMsg('smw_qi_queryastree') . '</td>
                 <td class="qiDefTabSpacer"> </td>' .
            '<td id="qiDefTab3" class="qiDefTabInactive" title="' . wfMsg('smw_qi_tt_showAsk') . '">' . wfMsg('smw_qi_querysource') . '</td>
                 <td class="qiDefTabSpacer" width="100%">&nbsp;</td>
                 </tr>
                 </table>
                 <div class="qiDefTabContent">' .
            $this->addTreeViewSparql() .
            '<div id="qitextview">Query as text</div>
                 <div id="qisource"><textarea id="sparqlQueryText"></textarea>' .
            '<div id="qisourceButtons">' .
            '<button id="qiUpdateSourceBtn" title="' . wfMsg('smw_qi_tt_update') . '">' . wfMsg('smw_qi_update') . '</button>' .
            '&nbsp;<span class="qibutton" id="discardChangesLink">' . wfMsg('smw_qi_discard_changes') . '</span>&nbsp;' .
            '</div>' .
            '</div>' .
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

  private function addTreeViewSparql() {
    return '<div id="treeview">' .
            '<div id="qiTreeDiv">' .
            '</div>' .
            '</div>';
  }

  private function addDragbox() {
    return '<div id="dragbox" class="dragbox">' .
            '<div id="treeviewbreadcrumbs">
              <div id="breadcrumbsDiv"></div>
            <div id="qiaddbuttons" class="qiaddbuttons">' .
            '<button onclick="qihelper.newCategoryDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addCategory') . '\')">' . wfMsg('smw_qi_add_category') . '</button>' .
            '<button onclick="qihelper.newPropertyDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addProperty') . '\')">' . wfMsg('smw_qi_add_property') . '</button>' .
            '<button onclick="qihelper.newInstanceDialogue(true)" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_addInstance') . '\')">' . wfMsg('smw_qi_add_instance') . '</button>' .
            '</div>
            </div>' .
            '<div id="qistatus"></div>' .
            '<div id="boxcontent"><table><tbody id="dialoguecontent"></tbody></table></div>' .
            '<div id="dialoguebuttons" style="display:none; width: 100%">' .
            '<button id="qiDialogButtonAdd" onclick="qihelper.add()">' . wfMsg('smw_qi_add') . '</button>&nbsp;' .
            '<span style="text-align:right">' .
            '<span class="qibutton" onclick="qihelper.emptyDialogue(); qihelper.updateTree();">' . wfMsg('smw_qi_cancel') . '</span>&nbsp;' .
            '<span id="qidelete" style="display:none" class="qibutton" onclick="qihelper.deleteActivePart()">' . wfMsg('smw_qi_delete') . '</span>' .
            '</span>' .
            '</div>' .
            '</div>';
  }

  private function addDragboxSparql() {
    return '<table id="qiDetailsTable"><tr><td id="qiTopToolbar">' .
            '<button id="qiAddCategoryBtn" title="' . wfMsg('smw_qi_tt_addCategory') . '">' . wfMsg('smw_qi_add_category') . '</button>' .
            '<button id="qiAddPropertyBtn" title="' . wfMsg('smw_qi_tt_addProperty') . '">' . wfMsg('smw_qi_add_property') . '</button>' .
            '</td></tr>' .
            '<tr><td id="qiBoxcontent">' .
            '<div id="qiSubjectDialog" style="display:none">' . $this->addSubjectDialog() . '</div>' .
            '<div id="qiCategoryDialog" style="display:none"/>' . $this->addCategoryDialog() . '</div>' .
            '<div id="qiPropertyDialog" style="display:none"/>' . $this->addPropertyDialog() . '</div>' .
            '</td></tr>' .
            '<tr><td id="qiBottomToolbar">' .
            '<button id="qiUpdateButton">' . wfMsg('smw_qi_update') . '</button>&nbsp;' .
            '<a id="qiDeleteLink" href="#" title="' . wfMsg('smw_qi_tt_delete') . '">' . wfMsg('smw_qi_delete') . '</a>' .
            '<a id="qiCancelLink" href="#" title="' . wfMsg('smw_qi_tt_cancel') . '">' . wfMsg('smw_qi_cancel') . '</a>' .
            '</td></tr>' .
            '</table>';
  }

  private function addValueDialog($nameInputLabel, $tableId, $nameInputId, $showInResultsChkBoxId, $typeLabelId, $columnLabelId, $drawTopLine = false) {
    return '<table ' . ($drawTopLine ? 'style="border-top: 1px solid gray;"' : '') . ($tableId ? "id=\"$tableId\"" : "") . '><tr>' .
            '<td>' . $nameInputLabel . '</td>' .
            '<td><input ' . ($nameInputId ? "id=\"$nameInputId\"" : "") . ' class="wickEnabled" type="text" autocomplete="OFF" constraints=""/></td>' .
            '<td><input ' . ($showInResultsChkBoxId ? "id=\"$showInResultsChkBoxId\"" : "") . ' type="checkbox" checked="checked"/>' .
            '<label ' . ($showInResultsChkBoxId ? "for=\"$showInResultsChkBoxId\"" : "") . '>' . wfMsg('smw_qi_show_in_results') . '</label></td></tr>' .
            '<tr><td></td><td ' . ($typeLabelId ? "id=\"$typeLabelId\"" : "") . ' class="typeLabelTd"></td><td></td></tr>' .
            '<tr><td>' . wfMsg('smw_qi_column_label') . '</td>' .
            '<td><input ' . ($columnLabelId ? "id=\"$columnLabelId\"" : "") . ' type="text"/></td>' .
            '<td></td></tr></table>';
  }

  private function addFiltersDialog($tableId) {
    return '<table ' . ($tableId ? "id=\"$tableId\"" : "") . '><tr><td>' . wfMsg('smw_qi_filters') . '</td></tr>' .
            '<tr><td><a href="" id="qiAddAndFilterLink">' . wfMsg('smw_qi_add_and_filter') . ' (AND)</a></td></tr>' .
            '</table>';
  }

  private function addCategoryDialog() {
    return '<table id="qiCategoryDialogTable"><tr>' .
            '<td>' . wfMsg('smw_qi_category_name') . '</td>' .
            '<td><input id="qiCategoryNameInput" class="wickEnabled" type="text" autocomplete="OFF" constraints="namespace: 14"/></td>' .
            '<td></td></tr>' .
            '<tr><td></td><td id="qiCategoryTypeLabel" class="typeLabelTd"></td><td></td></tr>' .
            '<tr><td></td>' .
            '<td><a href="" id="qiAddOrCategoryLink">' . wfMsg('smw_qi_add_another_category') . '</a></td>' .
            '<td></td></tr></table>';
  }

  private function addSubjectDialog() {
    return $this->addValueDialog(wfMsg('smw_qi_subject_name'), 'qiSubjectDialogTable', 'qiSubjectNameInput', 'qiSubjectShowInResultsChkBox', 'qiSubjectTypeLabel', 'qiSubjectColumnLabelInput') .
            $this->addFiltersDialog('qiSubjectFiltersTable');
  }

  private function addPropertyDialog() {
    return '<table id="qiPropertyDialogTable"><tr>' .
            '<td>' . wfMsg('smw_qi_property_name') . '</td>' .
            '<td><input id="qiPropertyNameInput" class="wickEnabled" type="text" autocomplete="OFF" constraints="namespace: 102"/></td>' .
            '<td><input id="qiPropertyValueMustBeSetChkBox" type="checkbox" checked="checked"/>' .
            '<label for="qiPropertyValueMustBeSetChkBox">' . wfMsg('smw_qi_value_must_be_set') . '</label></td>' .
            '</tr><tr>' .
            '<td></td><td id="qiPropertyTypeLabel" class="typeLabelTd"></td><td></td>' .
            '</tr></table>' .
            $this->addValueDialog(wfMsg('smw_qi_value_name'), 'qiPropertyValueTable', 'qiPropertyValueNameInput', 'qiPropertyValueShowInResultsChkBox', 'qiPropertyValueTypeLabel', 'qiPropertyColumnLabelInput') .
            $this->addFiltersDialog('qiPropertyFiltersTable');
  }

  private function addResultPartSparql() {
    $html = '<div id="qiresulttitle"><span onclick="qihelper.switchResult()" onmouseover="Tip(\''
            . wfMsg('smw_qi_tt_previewres')
            . '\')"><a id="qiresulttitle-link" class="minusplus" href="javascript:void(0)"></a>'
            . wfMsg('smw_qi_section_result')
            . '</span><button id="switchToSparqlBtn">' . wfMsg('smw_qi_switch_to_sparql') . '</button></div>' .
            '<div id="qiresultcontent">' .
            $this->addQueryLayoutSparql() .
            $this->addPreviewResults() .
            '</div>';
    return $html;
  }

  private function addResultPart() {
//    $html = '<div id="qiresulttitle">' .
//      <span onclick="qihelper.switchResult()" onmouseover="Tip(\''
//            . wfMsg('smw_qi_tt_previewres')
//            . '\')"><a id="qiresulttitle-link" class="minusplus" href="javascript:void(0)"></a>'
//            . wfMsg('smw_qi_section_result')
//            . '</span><button id="switchToSparqlBtn" style="display:none">' . wfMsg('smw_qi_switch_to_sparql') . '</button>
//            '</div>' .
   $html = '<div id="qiresultcontent">' .
            $this->addQueryLayout() .
            $this->addPreviewResults() .
            '</div>';
    return $html;
  }

  private function addQueryLayout() {

    global $smwgResultFormats;

    $blacklist = array("rss", "json", "exceltable", "icalendar", "vcard", "calendar", "debug", "template", "aggregation",
        "tixml", "transposed", "simpletable", "gallery", "timeline", "eventline", "live");

    $resultoptionshtml = "";
    $resultPrinters = array();

    reset($smwgResultFormats);
    while (current($smwgResultFormats)) {
      if (!in_array(key($smwgResultFormats), $blacklist)) {
        $className = $smwgResultFormats[key($smwgResultFormats)];
        $class = new $className(key($smwgResultFormats), null);
        // format the OFC printer names a bit, the rest comes with a real name
        $label = (substr(key($smwgResultFormats), 0, 4) == "ofc-") ? 'OFC ' . str_replace(array('_', '-'), ' ', substr(key($smwgResultFormats), 4)) : ucfirst($class->getName());
        $label .= ' (' . key($smwgResultFormats) . ')';
        $resultPrinters[$label] = key($smwgResultFormats);
      }
      next($smwgResultFormats);
    }
    ksort($resultPrinters);
    foreach ($resultPrinters as $k => $v) {
      $selected = ($v == "table") ? 'selected="selected"' : '';
      $resultoptionshtml .= '<option value="' . $v . '"' . $selected . '>' . $k . '</option>';
    }
    $fullPreviewLink = '';
    global $smwgQIResultPreview;
    if (isset($smwgQIResultPreview) && $smwgQIResultPreview === false)
      $fullPreviewLink = '&nbsp;|&nbsp; <a href="javascript:void(0);" onclick="qihelper.previewQuery()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_fullpreview') . '\')">' . wfMsg('smw_qi_fullpreview') . '</a>';
    return '<div id="querylayout">
					<div id="layouttitle">
                        <span onclick="qihelper.switchlayout()" title="' . wfMsg('smw_qi_tt_option') . '"><a id="layouttitle-link" class="plusminus" href="javascript:void(0)"></a>' . wfMsg('smw_qi_section_option') . '</span>
                        ' . $fullPreviewLink . '
					</div>
					<div id="layoutcontent" style="display:none">
                        <table summary="Layout Manager for query">
                           <tr>
                            <td>' .	wfMsg('smw_qi_queryname') . '</td>' .
                            '<td><input id="qiQueryName" type="text" size="40" /></td>
                             <td colspan="2">' . $this->addQueryOption() . '</td>
                           </tr>
                           <tr>
                            <td title="' . wfMsg('smw_qi_tt_format') . '">Format: </td>
                            <td><select id="layout_format" onchange="qihelper.checkFormat()">
                                    ' . $resultoptionshtml . '
                                    </select>
                            </td>
                            <td title="' . wfMsg('smw_qi_tt_sort') . '">Sort by: </td>
                            <td><select id="layout_sort" onchange="qihelper.updateSrcAndPreview()"></select></td>
                            </tr>
                        </table>
                    </div>
                    <div id="queryprinteroptions" style="display:none"></div>
                </div>';
  }

  private function addQueryLayoutSparql() {

    global $smwgResultFormats;

    $blacklist = array("rss", "json", "exceltable", "icalendar", "vcard", "calendar", "debug", "template", "aggregation",
        "tixml", "transposed", "simpletable", "gallery", "timeline", "eventline");

    $resultoptionshtml = "";
    $resultPrinters = array();

    reset($smwgResultFormats);
    while (current($smwgResultFormats)) {
      if (!in_array(key($smwgResultFormats), $blacklist)) {
        $className = $smwgResultFormats[key($smwgResultFormats)];
        $class = new $className(key($smwgResultFormats), null);
        // format the OFC printer names a bit, the rest comes with a real name
        $label = (substr(key($smwgResultFormats), 0, 4) == "ofc-") ? 'OFC ' . str_replace(array('_', '-'), ' ', substr(key($smwgResultFormats), 4)) : ucfirst($class->getName());
        $label .= ' (' . key($smwgResultFormats) . ')';
        $resultPrinters[$label] = key($smwgResultFormats);
      }
      next($smwgResultFormats);
    }
    ksort($resultPrinters);
    foreach ($resultPrinters as $k => $v) {
      $selected = ($v == "table") ? 'selected="selected"' : '';
      $resultoptionshtml .= '<option value="' . $v . '"' . $selected . '>' . $k . '</option>';
    }
    $fullPreviewLink = '';
    global $smwgQIResultPreview;
    if (isset($smwgQIResultPreview) && $smwgQIResultPreview === false)
      $fullPreviewLink = '&nbsp;|&nbsp; <a href="#;" onclick="qihelper.previewQuery()" title="' . wfMsg('smw_qi_tt_fullpreview') . '">' . wfMsg('smw_qi_fullpreview') . '</a>';
    return '<div id="qiQueryFormatDiv" class="querylayout">
					<div id="qiQueryFormatTitle" class="layouttitle">
                        <span title="' . wfMsg('smw_qi_tt_qlm') . '"><a id="layouttitle-link" class="plusminus" href="#"></a>' . wfMsg('smw_qi_layout_manager') . '</span>
                        ' . $fullPreviewLink . '
					</div>
					<div id="qiQueryFormatContent" style="display:none" class="layoutcontent">
                        <table summary="Layout Manager for query">
                            <tr>
        						<td width="50%" title="' . wfMsg('smw_qi_tt_format') . '">
                					Format: <select id="layout_format">
                                    ' . $resultoptionshtml . '
                                    </select>
                                </td>
                                <td title="' . wfMsg('smw_qi_tt_sort') . '">
                                    Sort by: <select id="layout_sort">
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
    wfRunHooks("QI_AddButtons", array(&$buttons));

    $imagepath = $smwgHaloScriptPath . '/skins/QueryInterface/images/';
    $isIE = (isset($_SERVER['HTTP_USER_AGENT']) &&
            (preg_match('/MSIE \d+\.\d+/', $_SERVER['HTTP_USER_AGENT']) ||
            stripos($_SERVER['HTTP_USER_AGENT'], 'Excel Bridge') !== false)
            );
    return '<div id="qimenubar">' .
            (($isIE) ? '<button onclick="qihelper.copyToClipboard()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_clipboard') . '\')">' . wfMsg('smw_qi_clipboard') . '</button>' : '') .
            $buttons .
            '<span><button id="qiResetQueryButton" onclick="qihelper.resetQuery()" onmouseover="Tip(\'' . wfMsg('smw_qi_tt_reset') . '\')">' . wfMsg('smw_qi_reset') . '</button></span>' .
            '</div>' .
//            '<div id="fullpreview" style="display:none">' .
//            '<table id="fullpreviewboxTable">' .
//            '<tr><td><div id="fullpreview"/></td></tr>' .
//            '<tr><td class="qibutton" onclick="$$(\'#askQI #fullpreviewbox\')[0].toggle(); $$(\'#askQI #shade\')[0].toggle(); qihelper.reloadOfcPreview()"><img src="' . $imagepath . 'delete.png"/>' . wfMsg('smw_qi_close_preview') . '</td></tr>' .
//            '</table>' .
//            '</div>' .
            '</div>' .
            '<div id="resetdialogue" class="topDialogue" style="display:none">' .
            'Do you really want to reset your query?<br/>' .
            '<span class="qibutton" onclick="qihelper.doReset()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$$(\'#askQI #resetdialogue\')[0].toggle(); $$(\'#askQI #shade\')[0].toggle()">' . wfMsg('smw_qi_cancel') . '</span>' .
            '</div>' .
            '<div id="showAsk" class="topDialogue" style="display:none;">' .
            '<span id="showParserAskButton" class="qibutton">' . wfMsg('smw_qi_parserask') . '</span><br/><hr/>' .
            '<div><textarea id="fullAskTextOld" rows="10" readonly></textarea></div>' .
            '<span class="qibutton" onclick="$$(\'#askQI #showAsk\')[0].toggle(); $$(\'#askQI #shade\')[0].toggle()">' . wfMsg('smw_qi_close') . '</span>' .
            '</div>' .
            '<div id="savedialogue" class="topDialogue" style="display:none">' .
            'Please enter a query name:<br/>' .
            '<input type="text" id="saveName"/><br/>' .
            '<span class="qibutton" onclick="qihelper.doSave()">' . wfMsg('smw_qi_confirm') . '</span>&nbsp;<span class="qibutton" onclick="$$(\'#askQI #savedialogue\')[0].toggle(); $$(\'#askQI #shade\')[0].toggle()">' . wfMsg('smw_qi_cancel') . '</span>' .
            '</div>' .
            '<div id="query4DiscardChanges" style="display:none"></div>';
  }

  private function getLodDatasources() {
    $this->datasources = array();
    $sourceOptions = '<option selected="selected">-Wiki-</option>'; // default fist option is the wiki itself
    $lodDatasources = '<hr />' . wfMsg('smw_qi_datasource_select_header') . ':';
    // Check if the triples store is propertly connected.
    $tsa = new LODTripleStoreAccess();
    if (!$tsa->isConnected()) {
      $lodDatasources .= " <span class=\"qiConnectionError\">" . wfMsg("smw_ob_ts_not_connected") . "</span>";
    } else {
      $ids = LODAdministrationStore::getInstance()->getAllSourceDefinitionIDsAndLabels();
      foreach ($ids as $tuple) {
        list($sourceID, $sourceLabel) = $tuple;
        $label = trim($sourceLabel);
        $this->datasources[$sourceID] = strlen($label) > 0 ? $label : $sourceID;
        $sourceOptions .= "<option value=\"$sourceID\">$label</option>";
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
            '<div id="qio_showdatasource_div" style="display:none">' .
            '<input type="checkbox" id="qio_showdatasource" onchange="qihelper.clickMetadata();" />' .
            wfMsg('smw_qi_showdatasource') . '<br/></div>' .
            '</td></tr></table>';
    return '<div id="qiDsSelected">' . $lodDatasources . '</div>';
  }

  private function getLodTrustpolicy() {
    global $smwgHaloScriptPath;
    $tps = LODPolicyStore::getInstance();
    $policyIds = $tps->getAllPolicyIDs();
    $is = count($policyIds);
    if ($is > 0) {
      $paramText = '';
      $text = '<select id="qitpeeselector" size="5" style="width:400px" onchange="qihelper.clickTpee();">';
      for ($i = 0; $i < $is; $i++) {
        $policy = $tps->loadPolicy($policyIds[$i]);
        $params = $policy->getParameters();
        $paramText .= '<div id="qitpeeparams_' . $policyIds[$i] . '" style="display:none"><table>';
        foreach ($params as $param) {
          if (!$param->getName())
            continue;
          $paramText .=
                  '<tr><td>' .
                  ( ($param->getLabel()) ? '<span name="qitpeeparams_' . $policyIds[$i] . '_' . $param->getName() . '">' . $param->getLabel() . '</span>' : '<span>' . $param->getName() . '</span>'
                  ) .
                  '</td><td>' .
                  $this->getInputFieldTpeeParam($policyIds[$i], $param->getName()) .
                  '</td>' .
                  ( ($param->getDescription()) ? '<td><img src="' . $smwgHaloScriptPath . '/skins/QueryInterface/images/help.gif" onmouseover="Tip(\'' .
                          str_replace("'", "\'", $param->getDescription()) . '\');" /></td>' : '<td> </td>' ) .
                  '</td></tr>';
        }
        $paramText .= '</table></div>';
        $text .= '<option value="' . $policyIds[$i] . '">' . $policy->getDescription() . '</option>';
      }
      $text .= '</select>';
      return '<div id="qiTpeeSelected" style="display:none"><hr />' . wfMsg('smw_qi_tpee_header') . ':<br/>' .
              '<table><tr><td>' . $text . '</td><td>' . $paramText . '</td></tr></table></div>';
    }

    return '';
  }

  private function getInputFieldTpeeParam($policyId, $paramName) {
    global $smwgHaloScriptPath;
    if ($paramName == 'PAR_USER') {
      return '<input id="qitpeeparamval_' . $policyId . '_' . $paramName . '" type="text" size="20" ' .
              'class="wickEnabled" constraints="namespace: 2" autocomplete="OFF" onblur="qihelper.updateSrcAndPreview();"/>';
    }
    if ($paramName == 'PAR_ORDER') {
      $html = '<div class="qitpeeparamval">' .
              '<table id="qitpeeparamval_' . $policyId . '_' . $paramName . '">';
      foreach (array_keys($this->datasources) as $ds) {
        $html .= '<tr><td _sourceid="' . $ds . '" onclick="qihelper.tpeeOrderSelect(this);">' . $this->datasources[$ds] . '</td></tr>';
      }
      $html.='</table>' .
              '</div>' .
              '<span style="vertical-align: center">' .
              '<img src="' . $smwgHaloScriptPath . '/skins/QueryInterface/images/up.png" alt="up" onclick="qihelper.tpeeOrder(\'up\');" />' .
              '<img src="' . $smwgHaloScriptPath . '/skins/QueryInterface/images/down.png" alt="down" onclick="qihelper.tpeeOrder(\'down\');" />' .
              '</span>';
      return $html;
    }
    return '<input id="qitpeeparamval_' . $policyId . '_' . $paramName . '" type="text" size="20"/>';
  }

}

