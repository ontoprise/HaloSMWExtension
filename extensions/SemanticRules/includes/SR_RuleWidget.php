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
 * @file
 * @ingroup SemanticRules
 *
 * @author Kai K�hn
 */
/**
 * Rule widget
 *
 * @author Kai Kuehn
 *
 */
class SRRuleWidget {

	private $mRuleName;
	private $mContainingPage;
	private $mRuleURI;
	private $mRuletext;
	private $mActive;
	private $mNative;
	private $mStatus;

	private static $index = 0;

	/**
	 *
	 * @param Title $containingPage Page which contains the rule
	 * @param string $ruleURI rule URI in format http:://..../Page$$rulename
	 * @param string $ruletext Text of rule
	 * @param boolean $active rule is active
	 * @param boolean $native rule is native
	 */
	public function __construct($ruleURI, $ruletext, $active, $native) {

		$this->mRuleURI = $ruleURI;
		$this->mRuletext = $ruletext;
		$this->mActive = $active;
		$this->mNative = $native;
			
		list($containingPageURI, $this->mRuleName) = explode("$$", $this->mRuleURI);
		$this->mContainingPage = TSHelper::getTitleFromURI($containingPageURI);
	}

	/**
	 * Returns the HTML code of the rule widget.
	 *
	 * @return string HTML
	 */
	public function asHTML() {

		global $wgScriptPath, $wgTitle;

		$headline = !is_null($wgTitle) && $wgTitle->getNamespace() != NS_SPECIAL ? '<h2>'.wfMsg('sr_rulesdefinedfor').' '.$this->mContainingPage->getPrefixedText().'</h2>'  : "";

		$onOffSwitch = $this->onOffSwitch($this->mActive, self::$index);
		$ruleFormatSelector = '<span style="float:right;margin-right:5px;">'.wfMsg('sr_ruleselector').
                       '<select style="margin-top: 5px;" name="rule_content_selector'.self::$index.'" onchange="sr_rulewidget.selectMode(event)">'.
                          '<option mode="easyreadible">'.wfMsg('sr_easyreadible').'</option>'.
                          '<option mode="stylized">'.wfMsg('sr_stylizedenglish').'</option>'.
                       '</select></span> ';
		$resultHTML = $headline.'<div id="rule_content_'.self::$index.'" ruleID="'.htmlspecialchars($this->mRuleURI).'" class="ruleWidget"><img style="margin-top: 5px;margin-left: 5px;" src="'.$wgScriptPath.'/extensions/SemanticRules/skins/images/rule.gif"/><span style="margin-left: 5px;font-weight:bold;">
                         '.htmlspecialchars($this->mRuleName).'</span>'.$ruleFormatSelector.'<span style="float:right; margin-right: 10px;">'.$onOffSwitch.'</span> <span style="float:right;margin-right: 10px;margin-top: 5px;">'.wfMsg('sr_rulestatus').':</span><hr style="clear:both;"/>'. // tab container
                         '<div id="rule_content_'.self::$index.'_easyreadible" class="ruleSerialization">'.htmlspecialchars($this->mRuletext).
                         '</div>'. // tab 1
                         '<div id="rule_content_'.self::$index.'_stylized" class="ruleSerialization" style="display:none;">Stylized english</div>'.
                         '<div id="'.htmlspecialchars($this->mRuleURI).'" native="'.($this->mNative?"true":"false").'" class="ruleSerialization" style="display:none;">'.htmlspecialchars($this->mRuletext).'</div>'.
                         '<div class="ruleLegend"><div class="rule_legend_property"></div><span>'.wfMsg('sr_prop').'</span>'.
                         '<div class="rule_legend_class"></div><span>'.wfMsg('sr_cat').'</span>'.
                         '<div class="rule_legend_inst"></div><span>'.wfMsg('sr_inst').'</span>'. //end legend
                         '</div></div>';  
		self::$index++;
	
		return $resultHTML;
	}

	/**
	 * Returns a on/off switch for as HTML
	 *
	 * @param boolean $defaultOn On/off as default value
	 * @param int $i rule index (n-th rule on page starting with 0)
	 */
	private function onOffSwitch($defaultOn, $i) {

		if ($defaultOn) {
			return '<select id="rule_content_'.$i.'_switch" style="background-color: lightgreen; margin-top: 5px;" onchange="sr_rulewidget.changeRuleState(event, this, \''.$this->mContainingPage->getPrefixedDBkey().'\', \''.$this->mRuleName.'\', '.$i.')"><option selected="true" value="true">'.wfMsg('sr_rule_isactive_state').'</option><option value="false">'.wfMsg('sr_rule_isinactive_state').'</option></select>';
		} else {
			return '<select id="rule_content_'.$i.'_switch" style="background-color: red; margin-top: 5px;" onchange="sr_rulewidget.changeRuleState(event, this, \''.$this->mContainingPage->getPrefixedDBkey().'\', \''.$this->mRuleName.'\', '.$i.')"><option value="true">'.wfMsg('sr_rule_isactive_state').'</option><option selected="true" value="false">'.wfMsg('sr_rule_isinactive_state').'</option></select>';
		}

	}

}
