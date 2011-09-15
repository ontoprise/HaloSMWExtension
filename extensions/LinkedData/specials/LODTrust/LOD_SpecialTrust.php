<?php

/*  Copyright 2010, MediaEvent Services GmbH & Co. KG
 *  This file is part of the LinkedData-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author Magnus Niemann
 *
 * @ingroup LODSpecialPage
 * @ingroup SpecialPage
 */
class LODTrustPage extends SpecialPage {

    var $lod_trust_defaultdescription = "A trust policy.";
    var $lod_trust_defaultpattern;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('LODTrust');
        wfLoadExtensionMessages('LODTrust');
        $this->lod_trust_defaultpattern = <<<EOT
{ # Do not forget opening and closing brackets
# The first graph pattern is used to retrieve data source and retrieval date
# metainformation
GRAPH smwGraphs:ProvenanceGraph {
    ?GRAPH swp:assertedBy ?warrant .
    ?warrant swp:authority ?dataSource .
    ?GRAPH smw-lde:created ?retrievalDate .
}

# In this graph, the constraints for the trust policy are defined.
GRAPH smwGraphs:UserGraph {
# The PAR_USER variable must be defined in the policy
    ?PAR_USER smw-lde:trusts ?trustStatement .
	?trustStatement smw-lde:authority ?dataSource .
}
}
EOT;
    }

    function execute($p) {
        global $wgOut;
        global $wgScript;
        global $lodgScriptPath, $lodgStyleVersion;

        $scriptFile = $lodgScriptPath . "/scripts/LOD_SpecialTrust.js";
        SMWOutputs::requireHeadItem("LOD_SpecialTrust.js",
                        '<script type="text/javascript" src="' . $scriptFile . $lodgStyleVersion . '"></script>');
        SMWOutputs::requireHeadItem("lod_trust.css",
                        '<link rel="stylesheet" type="text/css" href="' . $lodgScriptPath . '/skins/trust.css'.$lodgStyleVersion.'" />');

        SMWOutputs::commitToOutputPage($wgOut);
        $this->setHeaders();
        wfProfileIn('doLODTrust (LOD)');

        $action = NULL;
        if (array_key_exists("action", $_GET)) {
            $action = $_GET["action"];
        } elseif (array_key_exists("action", $_POST)) {
            $action = $_POST["action"];
        }
        if ($action) {
            $this->doAction($action);
        } else {
            $this->doAction("list");
        }

        wfProfileOut('doLODTrust (LOD)');
    }

    public function doAction($action) {
        switch ($action) {
            case 'new': $this->doNew();
                break;
            case 'edit': $this->doEdit();
                break;
            case 'remove': $this->doRemove();
                break;
            case 'save': $this->doSave();
                break;
            case 'duplicate': $this->doDuplicate();
                break;
            case 'list': $this->doList();
                break;
        }
    }

    public function doList() {
        global $wgOut;
        global $wgScript;
        $allPolicies = $this->getAllPolicies();
        if (!is_array($allPolicies)) {
            global $smwgWebserviceEndpoint;
            $wgOut->addHTML("Error: Triplestore not accessible at: " . $smwgWebserviceEndpoint);
            return;
        }
        $wgOut->addHTML($this->createPolicyTable($allPolicies));
        $wgOut->addHTML("<p>" . $this->linkButton("New policy", $wgScript . "/Special:LODTrust?action=new") . "</p>");
    }

    private static function linkButton($text, $link) {
        return "<input type=\"button\" value=\"$text\" onClick=\"window.location='$link'\">";
    }

    public function doEdit() {
        global $wgOut;
        $id = $_GET["id"];
        $policy = $this->getPolicy($id);
        if (!$policy) {
            $this->errorPage("The policy with the id $id could not be found.");
            return;
        }
        $wgOut->addHTML($this->createPolicyEditor($policy, false));
    }

    public function doSave() {
        // print_r($_POST);
        global $wgOut;
        $uri = $_POST["uri"];
        $id = $_POST["id"];
        $des = $_POST["description"];
        $heu = $_POST["heuristic"];
        $pat = $_POST["pattern"];
        $par = $this->extractParameters();
        $lodPolicyStore = LODPolicyStore::getInstance();
        $success = $lodPolicyStore->storePolicy($uri, $id, $des, $pat, $heu, $par);
        if ($success) {
            $wgOut->addHTML("<div id=\"tpee_message\">Policy $id saved.</div>");
        } else {
            $wgOut->addHTML("<div id=\"tpee_error\">Policy $id could not be saved.</div>");
        }
        $this->doList();
    }

    private function extractParameters() {
        global $wgOut;
        $pm = TSCPrefixManager::getInstance();
        $parPrefix = $pm->getNamespaceURI('smwGraphs') . "TrustGraph#Par";

        $parr = array();
        /// $wgOut->addHTML("HTTP:\n<pre>\n" . print_r($_POST, true) . "\n</pre>\n");
        foreach ($_POST as $key => $value) {
            if (preg_match("/[a-z]+_?[0-9]+/", $key)) {
                // $wgOut->addHTML("<p>Key $key</p>\n");
                $parkey = preg_replace("/[a-z]+(_?[0-9]+)/", '\\1', $key);
                $attr = preg_replace("/([a-z]+)_?[0-9]+/", '\\1', $key);
                $attr = $attr == "pdescription" ? "description" : $attr;
                $p = array();
                if (array_key_exists($parkey, $parr)) {
                    $p = $parr[$parkey];
                }
                $p[$attr] = $value;
                // set then uri of new parameters, but only once!
                if (!array_key_exists("uri", $p)) {
                    if ($parkey[0] != "_") {
                        $p["uri"] = $parPrefix . $parkey;
                    }
                }
                $parr[$parkey] = $p;
            }
        }
        // $wgOut->addHTML("Parameters:\n<pre>\n" . print_r($parr, true) . "\n</pre>\n");
        return $parr;
    }

    public function doDuplicate() {
        global $wgOut;
        $origid = $_GET["id"];
        $origpolicy = $this->getPolicy($origid);
        if (!$origpolicy) {
            $this->errorPage("The policy with the id $origid could not be found.");
            return;
        }
        $id = uniqid("P_");
        $uri = $origpolicy->getURI() . "_";
        $des = $origpolicy->getDescription();
        $heu = $origpolicy->getHeuristic()->getLabel();
        $pat = $origpolicy->getPattern();
        $pars = array();
        foreach($origpolicy->getParameters() as $p) {
            $pars[] = array("uri" => $p->getURI(),
                    "name" => $p->getName(),
                    "label" => $p->getLabel(),
                    "description" => $p->getDescription());
        }
        $lodPolicyStore = LODPolicyStore::getInstance();
        $success = $lodPolicyStore->storePolicy($uri, $id, $des, $pat, $heu, $pars);
        if ($success) {
            $wgOut->addHTML("<div id=\"tpee_message\">Policy $id saved.</div>");
        } else {
            $wgOut->addHTML("<div id=\"tpee_error\">Policy $id could not be saved.</div>");
        }
        $this->doList();
    }

    public function doNew() {
        global $wgOut;
        $id = uniqid("P_");
        $uri = LODPolicyStore::getTrustPolicyNS() . $id;
        $policy = new LODPolicy($id, $uri);
        $policy->setDescription($this->lod_trust_defaultdescription);
        $policy->setPattern($this->lod_trust_defaultpattern);
        $wgOut->addHTML($this->createPolicyEditor($policy, true));
    }

    public function doRemove() {
        global $wgOut;
        $id = $_GET["id"];
        if ($id) {
            $lodPolicyStore = LODPolicyStore::getInstance();
            $success = $lodPolicyStore->deletePolicy($id);
            if ($success) {
                $wgOut->addHTML("<div id=\"tpee_message\">Policy $id deleted.</div>");
            } else {
                $wgOut->addHTML("<div id=\"tpee_error\">Policy $id could not be deleted.</div>");
            }
        } else {
            $wgOut->addHTML("<div id=\"tpee_error\">No policy given.</div>");
        }
        $this->doList();
    }

    public function getPolicy($id) {
        $lodPolicyStore = LODPolicyStore::getInstance();
        $result = $lodPolicyStore->loadPolicy($id);
        return $result;
    }

    public function createPolicyEditor($policy, $isnew) {
        // print_r($policy);
        global $wgScript;
        $id = $policy->getID();
        $puri = $policy->getURI();
        $heuristic = $policy->getHeuristic();
        $parameters = $policy->getParameters();
        if (!$heuristic) {
            $heuristic = "NULL";
        } else {
            $heuristic = $heuristic->getLabel();
        }
        $remAction = wfMsg('lod_sp_policy_action_remove_par');
        $defAction = wfMsg('lod_sp_policy_action_define_par');
        $html = "";
        $html .= "<div id=\"tpee_policy\">\n";
        $html .= "<form action=\"" . $wgScript . "/Special:LODTrust\" method=\"post\" onsubmit=\"return validate();\">\n";
        $html .= "<input type=\"hidden\" name=\"uri\" value=\"$puri\" />";
        $html .= "<input type=\"hidden\" name=\"action\" value=\"save\" />";
        $html .= "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
        $html .= "<h4 id=\"tpee_policy_id\">$id</h4>\n";
        $html .= "<p id=\"tpee_policy_description\">Description: <input type=\"text\" size=\"50\" name=\"description\" value=\"" . $policy->getDescription() . "\"/></p>\n";
        $html .= $this->createHeuristicSelection($heuristic, $isnew);
        $html .= "<p id=\"tpee_policy_pattern\">Pattern: <textarea id=\"tpee_pattern\" name=\"pattern\" cols=\"50\" rows=\"30\">" . $policy->getPattern() . "</textarea></p>\n";
        $html .= $this->createParameterList($parameters);
        $html .= "<div id=\"tpee_parameter_insert\"><input type=\"button\" onclick=\"newParameter()\" value=\"" . wfMsg('lod_sp_policy_action_new_par') . "\" /></div>\n";
        $html .= "<div id=\"tpee_parameter_insert\"><input type=\"submit\" value=\"Save policy\" /></div>&nbsp;";
        $html .= $this->linkButton(wfMsg('lod_sp_policy_action_cancel_edit'), $wgScript . "/Special:LODTrust");
        $html .= "</form>\n";
        $html .= "</div>";
        $html .= $this->createEditButtons();
        return $html;
    }

    private function createEditButtons() {
        $txt = <<<HTML
<div id="tpee_policy_button">
<table>
		    <tr>
			<td>
		<select name="insertTemplate"
		     onchange="insertHere(this.options[this.selectedIndex].value)">
				    <option value="">-- Template --</option>
		<option value="GRAPH smwGraphs:ProvenanceGraph {
        ?GRAPH swp:assertedBy ?warrant .
        ?warrant swp:authority ?dataSource .
        ?GRAPH smw-lde:created ?retrievalDate .
        }">Metadata Graph</option>
				  </select>
		<select name="insertTemplate"
		     onchange="insertHere(this.options[this.selectedIndex].value)">
				    <option value="">-- Variables --</option>
		<option value="?warrant">Warrant</option>
		<option value="?dataSource">Datasource</option>
		<option value="?retrievalDate">Date of retrieval</option>
				  </select>

				</td>
		    </tr>
			<tr>
		<td style="width:120px" valign="top">
<!--
        <div class="button-subcontainer" style="margin-top: 0px">
		  <input type="button" class="button sort-table"
		         value="GRAPH"  onclick="insertHere('GRAPH')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
			</div>
-->
			<div class="button-subcontainer">
		  <input type="button" class="button sort-table"
		         value="Make Optional"  onclick="optional()"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
<!--
		  <input type="button" class="button sort-table"
		         value="UNION"  onclick="insertHere('UNION')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
	        </div>
-->
<!--
   			<div class="button-subcontainer">
		  <input type="button" class="button sort-table"
		         value="ORDER BY"  onclick="insertHere('ORDER BY')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		  <input type="button" class="button sort-table"
		         value="ORDER BY ASC()"  onclick="insertHere('ORDER BY ASC(?x)')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		  <input type="button" class="button sort-table"
		         value="ORDER BY DESC()"  onclick="insertHere('ORDER BY DESC(?x)')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		  <input type="button" class="button sort-table"
		         value="LIMIT"  onclick="insertHere('LIMIT 10')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		  <input type="button" class="button sort-table"
		         value="OFFSET"  onclick="insertHere('OFFSET 10')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		</div>
-->
					<div class="button-subcontainer">
<!--
          <input type="button" class="button sort-table"
		         value="Simple Filter"  onclick="insertHere('FILTER ( ?x &lt; 3 ) .')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
-->
		  <input type="button" class="button sort-table"
		         value="Regex Filter"  onclick="insertHere('FILTER regex( str(?name), &#34;Jane&#34;, &#34;i&#34; ) .')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />

<!--
		  <input type="button" class="button sort-table"
		         value="Bound Filter"  onclick="insertHere('FILTER ( bound(?x) ) .')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
-->
		  <input type="button" class="button sort-table"
		         value="Date Filter"  onclick="insertHere('FILTER ( ?retrievalDate &gt; &#34;2010-01-01T00:00:00Z&#34;^^xsd:dateTime ) .')"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		         </div>
		  <div class="button-subcontainer">
		         		  <input type="button" class="button sort-table"
		         value="Comment Region"  onclick="comment()"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		  <input type="button" class="button sort-table"
		         value="Uncomment Region"  onclick="unComment()"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />




		  <input type="button" class="button sort-table"
		         value="Indent Region"  onclick="indent()"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		         </div>
		         			<div class="button-subcontainer">
		  <input type="button" class="button sort-table"
		         value="Clear All"  onclick="clearAll()"
		         onmouseover="mouseOver(this)" onmouseout="mouseOut(this)"
		         onmousedown="mouseDown(this)" onmouseup="mouseUp(this)" />
		         </div>
		</td>
		</tr>
		</table>
</div>
<script type="text/javascript">
//<![CDATA[
init();
// ]]>
</script>
HTML;
        return $txt;
    }

    private function createHeuristicSelection($heuristic, $isnew) {
        // FIXME actual possible heuristics should be taken from the triple store
        $html = "<p id=\"tpee_heuristic\">Heuristic: ";
        $html .= "<select id=\"tpee_selected_heuristic\" name=\"heuristic\" size=\"1\">\n";
        if($isnew) {
            $html .= "<option value='000' selected>-- Select a heuristic --</option>\n";
        }
        foreach ($this->getHeuristics() as $h) {
            $html .= "<option" . ($h == $heuristic && !$isnew ? " selected" : "") . ">$h</option>\n";
        }
        $html .= "</select>\n";
        $html .= "</p>\n";
        return $html;
    }

    /*
     * Creates a HTML form for each existing policy parameter
     */

    private function createParameterList($list) {
        $remAction = wfMsg('lod_sp_policy_action_remove_par');
        $editAction = wfMsg('lod_sp_policy_action_edit_par');
        $defAction = wfMsg('lod_sp_policy_action_define_par');
        $html = <<<HTML
<div id="tpee_parameter_form" style="display: none">
    <input type="hidden" name="uri_" value="" />
    Name: <input type="text" name="name_" value="" /> </br>
    Label: <input type="text" name="label_" value="" /> </br>
    Description: <textarea name="pdescription_" cols="30" rows="3"></textarea> </br>
    <input id="tpee_parameter_form_button" type="button" value="Save parameter" onclick="setParameter(this.parentNode);"/>
</div>
<table id='tpee_parameters_table'>
<tr>
<th>Name</th><th>Label</th><th>Description</th><th colspan="3">&nbsp;</th>
</tr>
HTML;
        $count = 0;
        if (!is_array($list)) {
            return $html;
        }
        foreach ($list as $parameter) {
            $n = $parameter->getName();
            $d = $parameter->getDescription();
            $l = $parameter->getLabel();
            $uri = $parameter->getURI();
            $count++;
            $html .= <<<HTML
<tr id="tpee_parameter_$count" class="tpee_parameter_row">
    <input type="hidden" name="uri_$count" value="$uri" />
    <input type="hidden" name="name_$count" value="$n" />
    <input type="hidden" name="label_$count" value="$l" />
    <input type="hidden" name="pdescription_$count" value="$d" />
    <td id="name_$count">$n</td>
    <td id="label_$count">$l</td>
    <td id="pdescription_$count">$d</td>
    <td><input type="button" value="$editAction"
		onclick="editParameter(this.parentNode.parentNode);" /></td>
    <td><input type="button" value="$remAction"
		onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);" /></td>
</tr>
HTML;
        }
        $html .= <<<HTML
<tr id="tpee_parameter_template" style="display: none">
    <input type="hidden" name="uri_" value="" />
    <input type="hidden" name="name_" value="" />
    <input type="hidden" name="label_" value="" />
    <input type="hidden" name="pdescription_" value="" />
    <td id="name_"></td>
    <td id="label_"></td>
    <td id="pdescription_"></td>
    <td><input type="button" value="$editAction"
		onclick="editParameter(this.parentNode.parentNode);" /></td>
    <td><input type="button" value="$remAction"
		onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);" /></td>
</tr>
<input type="hidden" name="lastcount" value="$count" />
HTML;

        $html .= "</table>";
        return $html;
    }

    private function getHeuristics() {
        // FIXME this should retrieve the possible heuristic names form the triple store
        return array("PreferInternalInformation", "PreferCurrentInformation", "PreferInformationAccordingToGivenSourceSequence");
    }

    public function getAllPolicies() {
        $results = array();
        $lodPolicyStore = LODPolicyStore::getInstance();
        $results = $lodPolicyStore->loadAllPolicies();
        asort($results);
        return $results;
    }

    public function createPolicyTable($table) {
        global $wgScript;

        $html = "<table id=\"tpee_policies_table\">\n";
        $html .= "<th>" . wfMsg('lod_sp_policy_label') . "</th>\n";
        $html .= "<th>" . wfMsg('lod_sp_policy_description') . "</th>\n";
        $html .= "<th></th>\n";
        $html .= "<th></th>\n";

        foreach ($table as $ldPolicy) {
            $id = $ldPolicy->getID();
            $html .= "<tr>";
            $html .="<td>";
            $html .= $id;
            $html .="</td>\n";
            $html .="<td>";
            $html .= $ldPolicy->getDescription();
            $html .="</td>\n";
            $html .="<td>";
            $html .= $this->linkButton(wfMsg('lod_sp_policy_action_edit'), $wgScript . "/Special:LODTrust?id=$id&action=edit");
            $html .="</td>\n";
            $html .="<td>";
            $html .= $this->linkButton(wfMsg('lod_sp_policy_action_duplicate'), $wgScript . "/Special:LODTrust?id=$id&action=duplicate");
            $html .="</td>\n";
            $html .="<td>";
            $html .= $this->removeButton($id);
            $html .="</td>\n";
            $html .= "</tr>\n";
        }
        $html .= "</table>\n";
        return $html;
    }

    private static function removeButton($id) {
        global $wgScript;
        $html = "<input type=\"button\"";
        $html .= " value=\"" . wfMsg('lod_sp_policy_action_remove') . "\"";
        $linkYes = $wgScript . "/Special:LODTrust?id=$id&action=remove";
        $linkNo = $wgScript . "/Special:LODTrust";
        $html .= " onClick=\"removeOrNot('$id', '$linkYes', '$linkNo')\">\n";
        return $html;
    }

}