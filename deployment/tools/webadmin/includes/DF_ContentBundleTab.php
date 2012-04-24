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
		//$html .= "<form action=\"$wgServer$wgScriptPath/deployment/tools/webadmin/index.php?action=ajax\" method=\"post\">";
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
			$html .= "<div id=\"df_existingbundles_section\"><input id=\"df_bundlename\" style=\"margin-top:10px;margin-bottom:10px\" size=\"35\" name=\"rsargs[]\"></input></div>";
		}

		$html .= "<input type=\"button\" value=\"".$dfgLang->getLanguageString('df_webadmin_export_bundle')."\" id=\"df_createBundle\" disabled=\"true\"></input>";
		$html .= "<img id=\"df_bundleexport_progress_indicator\" src=\"skins/ajax-loader.gif\" style=\"display:none;margin-left: 10px;\"/>";
		$html .= "<div id=\"df_contentbundle_error\"></div>";
		
		$html .= $this->getContentBundleListHTML();
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
	
	private function getContentBundleListHTML() {
		global $dfgLang, $wgServer, $wgScriptPath;
		$html = "<table id=\"df_bundlelist_table\">";
         $html .= "<th>";
        $html .= $dfgLang->getLanguageString('df_webadmin_contentbundle_file');
        $html .= "</th>";
        $html .= "<th>";
        $html .= $dfgLang->getLanguageString('df_webadmin_contentbundle_creationdate');
        $html .= "</th>";
        
        $readLogLinkTemplate = '<a href="'.$wgServer.$wgScriptPath.'/deployment/tools/webadmin/index.php'.
                        '?action=ajax&rs=downloadBundleFile&rsargs[]=$1">$3</a>';
        
        $logs = $this->getBundleList();
        if (count($logs) == 0) {
        	$html = $dfgLang->getLanguageString('df_webadmin_contentbundle_nobundles');
        	return $html;
        }
        $i = 0;
        foreach($logs as $l) {
            list($name, $date) = $l;
            $j = $i % 2;
            $html .= "<tr class=\"df_row_$j\">";
           
            $html .= "<td class=\"df_log_link\">";
            $readLogLink = str_replace('$1', $name, $readLogLinkTemplate);
            $readLogLink = str_replace('$3', $name, $readLogLink);
            $html .= "$readLogLink";
           
            $html .= "</td>";
            $html .= "<td class=\"df_log_link\">";
            $html .= date ("F d Y H:i:s.", $date);
            $html .= "</td>";
            $html .= "</tr>";
            $i++;
        }
        $html .= "</table>";
        return $html;
	}

	private function getBundleList() {
		$result=array();
		$bundleDir = $this->getBundleExportDirectory();
		$handle = @opendir($bundleDir);
		if (!$handle) {

			return array();
		}

		while ($entry = readdir($handle) ){
			if ($entry[0] == '.'){
				continue;
			}

			$file = "$bundleDir/$entry";
			if (strpos($entry, ".zip") === false) {
				continue;
			}
			$date =  filemtime($file);
			$result[] = array($entry, $date);
		}
		@closedir($handle);
		usort($result, array($this, "cmpLogEntry"));
		return $result;
	}

	private function cmpLogEntry($a, $b) {
		list($file1, $ts1) = $a;
		list($file2, $ts2) = $b;
		return $ts2-$ts1;
	}

	public function getBundleExportDirectory() {
		if (array_key_exists('df_homedir', DF_Config::$settings)) {
			$homeDir = DF_Config::$settings['df_homedir'];
		} else {
			$homeDir = Tools::getHomeDir();
			if (is_null($homeDir)) throw new DF_SettingError(DEPLOY_FRAMEWORK_NO_HOME_DIR, "No homedir found. Please configure one in settings.php");
		}
		if (!is_writable($homeDir)) {
			throw new DF_SettingError(DF_HOME_DIR_NOT_WRITEABLE, "Homedir not writeable.");
		}
		$wikiname = DF_Config::$df_wikiName;
		return "$homeDir/$wikiname/df_bundle_export";
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
        	throw new Exception($dfgLang->getLanguageString('df_webadmin_querying_contentbundle_failed')." Reason: ".$res, $status);
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
