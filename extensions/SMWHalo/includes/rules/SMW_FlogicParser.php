<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

#require_once( "$smwgHaloIP/includes/rules/SMW_RuleObject.php");
require_once( "SMW_RuleObject.php");

if (!defined('MEDIAWIKI')) die();

class SMWFlogicParser {

	static private $_client;
	static private $_clientport = "8080";
	static private $_clientwsdl = "/flogic?wsdl";

    static private $instance = NULL;

	// implicitly set localhost if no messagebroker was defined.
    static public function getInstance() {
    	global $wgServer, $wgScript;

        if (self::$instance === NULL) {
            self::$instance = new self;

            ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
            self::$_client = new SoapClient("$wgServer$wgScript?action=get_flogic");
        }
        return self::$instance;
    }

    private function __construct(){}
    private function __clone(){}

    /**
     * Parses an FLogic rule and returns the corresponding RuleObject.
     *
     * @param string $ruleid
     * 		The id of the rule. If it is <null>, $flogicrule must contain the
     * 		id in for of: RULE #id:flogic text
     * @param string $flogicrule
     * 		The text of the rule.
     * @return SMWRuleObject
     * 		The rule object contains the parsed literals of the rule.
     */
	static public function parseFloRule($ruleid, $flogicrule) {
		$_flogicstring = $flogicrule;

		// initFlogic returns the sessionId
		$parseflogicinput=array();
//		$parseflogicinput['flogicString'] =
		$parseflogicinput =
			(($ruleid == null)
				? ''
				: "RULE #" . $ruleid . ": ")
			. $_flogicstring;
		
		$parseflogicinput = str_replace("|", "", $parseflogicinput); 
			
		$_parsedstring = self::$_client->parseFlogic($parseflogicinput);

		$_ruleObject = new SMWRuleObject();
		$_ruleObject->setAxiomId($ruleid);
		return $_ruleObject->parseRuleObject($_parsedstring);
	}
}

?>
