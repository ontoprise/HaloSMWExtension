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
 * @ingroup WebAdmin
 *
 * Content bundle tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once("DF_HTMLTools.php");

class DFContentBundleTab {

	/**
	 * Content bundle tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_contentbundletab');
	}

	public function getHTML() {
		global $dfgLang, $wgServer, $wgScriptPath, $mwrootDir;

		$html = "<div style=\"margin-bottom: 10px;\">".$dfgLang->getLanguageString('df_webadmin_contentbundletab_description')."</div>";
		$html .= "<form action=\"$wgServer$wgScriptPath/deployment/tools/webadmin/index.php?action=ajax\" method=\"post\">";
		$html .= "<input type=\"hidden\" name=\"rs\" value=\"downloadBundle\"></input>";
		try {
			$localPackages = PackageRepository::getLocalPackages($mwrootDir);
			if (!array_key_exists("smwhalo", $localPackages)) {
				throw new Exception($dfgLang->getLanguageString('df_webadmin_contentbundle_nosmwhalo'));
			} else {
				$html .= "<div id=\"df_existingbundles_section\"><select style=\"margin-top:10px;margin-bottom:10px;width: 40%\" size=\"5\" id=\"df_bundles_list\" name=\"rsargs[]\">".$this->getContentBundlesHTML()."</select></div>";
			}
		} catch(Exception $e) {
			$message = $e->getMessage();
			$html .= "<div class=\"df_notice\">$message</div>";
			$html .= "<div id=\"df_existingbundles_section\"><input style=\"margin-top:10px;margin-bottom:10px\" size=\"35\" name=\"rsargs[]\"></input></div>";
		}
		
		$html .= "<input type=\"submit\" value=\"".$dfgLang->getLanguageString('df_webadmin_download_bundle')."\" id=\"df_downloadBundle\"></input>";
		$html .= "</form>";
		return $html;
	}

	private function getContentBundlesHTML() {
		$bundles = $this->queryForContentBundles();
		$html = "";
		foreach($bundles as $b) {
			$b = substr($b, 5); // remove "wiki:"
			$html .= "<option value=\"".DFHtmlTools::encodeAttribute($b)."\">".htmlspecialchars($b)."</option>";
		}
		return $html;
	}

	private function queryForContentBundles() {

		$res = "";
		$header = "";
		$payload="";
		global $wgScriptPath, $dfgLang;
		$proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '' ? "https" : "http";
		$hostname = $_SERVER['HTTP_HOST'];

		$query = '[['.$dfgLang->getLanguageString('category').':'.$dfgLang->getLanguageString('df_contentbundle').']]';

		// Access external query interface using xmlsimple query printer (does not print URIs with namespaces but only wiki:localname)
		$ch = curl_init("$proto://$hostname$wgScriptPath/index.php?action=ajax&rs=smwf_ws_callEQIXML&rsargs[]=".urlencode($query)."&rsargs[]=xmlsimple");
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
		$httpHeader = array (
        "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
        "Expect: "
        );

        curl_setopt($ch,CURLOPT_HTTPHEADER, $httpHeader);

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        	curl_setopt($ch,CURLOPT_USERPWD,trim($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']));
        }

        if ($proto == "https") {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // don't verify ssl
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Execute
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $res = curl_exec($ch);
         
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

         
        if ($status != 200) {
        	throw new Exception($dfgLang->getLanguageString('df_webadmin_querying_contentbundle_failed'), $status);
        }
         

        $dom = simplexml_load_string($res);
        $dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
        if($dom === FALSE) {
        	throw new Exception($dfgLang->getLanguageString('df_webadmin_querying_contentbundle_failed'));
        }

        $sources = $dom->xpath('//sparqlxml:uri');
        $sourcesSet = array();
        foreach($sources as $s) {
        	$sourcesSet[] = (string) $s;
        }
        return $sourcesSet;
	}


}
