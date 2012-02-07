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
 * Server tab
 *
 * @author: Kai Kühn
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}



class DFServersTab {

	/**
	 * Settings tab
	 *
	 */
	public function __construct() {

	}

	public function getTabName() {
		global $dfgLang;
		return $dfgLang->getLanguageString('df_webadmin_serverstab');
	}

	public function getHTML() {
		global $dfgLang;

		Tools::isWindows($os);
		if ($os == "Windows XP") {
			return "This feature is not available on Windows XP";
		}

		$executeText = $dfgLang->getLanguageString('df_webadmin_server_execute');
		$startActionText = $dfgLang->getLanguageString('df_webadmin_server_start');
		$endActionText = $dfgLang->getLanguageString('df_webadmin_server_end');

		$html = $dfgLang->getLanguageString('df_webadmin_configureservers');

		$html .= "<br/><br/>";
		$html .= "<table id=\"df_server_state_table\">";
		$html .= "<th>Service</th>";
		$html .= "<th>Status</th>";
		$html .= "<th>Operation</th>";
		$html .= "<th>Command</th>";
		$html .= "<tr>";
		$apacheStart = self::guessPaths("apache", "start");
		$apacheEnd = self::guessPaths("apache", "end");
		$html .= "<td>Apache</td>";


		$html .= "<td id=\"df_run_flag_apache\" class=\"df_process_unknown\">".$dfgLang->getLanguageString('df_webadmin_process_unknown')."</td>";

		$html .= "<td><select id=\"apache_selector\" class=\"df_action_selector\"><option value=\"$apacheStart\">restart</option></select></td>";
		$html .= "<td><input class=\"df_servers_command\" id=\"df_servers_apache_command\" type=\"text\" size=\"80\" value=\"$apacheStart\"/>";
		$html .= "<input id=\"df_servers_apache_execute\" class=\"df_servers_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$mysqlStart = self::guessPaths("mysql", "start");
		$mysqlEnd = self::guessPaths("mysql", "end");
		$html .= "<tr>";
		$html .= "<td>mySQL</td>";


		$html .= "<td id=\"df_run_flag_mysql\" class=\"df_process_unknown\">".$dfgLang->getLanguageString('df_webadmin_process_unknown')."</td>";

		$html .= "<td><select id=\"mysql_selector\" class=\"df_action_selector\"><option value=\"$mysqlStart\">$startActionText</option><option value=\"$mysqlEnd\">$endActionText</option></select></td>";
		$html .= "<td><input class=\"df_servers_command\" id=\"df_servers_mysql_command\" type=\"text\" size=\"80\" value=\"$mysqlStart\"/>";
		$html .= "<input id=\"df_servers_mysql_execute\" class=\"df_servers_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$solrStart = self::guessPaths("solr", "start");
		$solrEnd = self::guessPaths("solr", "end");
		$html .= "<tr>";
		$html .= "<td>solr</td>";


		$html .= "<td id=\"df_run_flag_solr\" class=\"df_process_unknown\">".$dfgLang->getLanguageString('df_webadmin_process_unknown')."</td>";

		$html .= "<td><select id=\"solr_selector\" class=\"df_action_selector\"><option value=\"$solrStart\">$startActionText</option><option value=\"$solrEnd\">$endActionText</option></select></td>";
		$html .= "<td><input class=\"df_servers_command\" id=\"df_servers_solr_command\" type=\"text\" size=\"80\" value=\"$solrStart\"/>";
		$html .= "<input id=\"df_servers_solr_execute\" class=\"df_servers_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$tscStart = self::guessPaths("tsc", "start");
		$tscEnd = self::guessPaths("tsc", "end");
		$html .= "<tr>";
		$html .= "<td>tsc</td>";


		$html .= "<td id=\"df_run_flag_tsc\" class=\"df_process_unknown\">".$dfgLang->getLanguageString('df_webadmin_process_unknown')."</td>";

		$html .= "<td><select id=\"tsc_selector\" class=\"df_action_selector\"><option value=\"$tscStart\">$startActionText</option><option value=\"$tscEnd\">$endActionText</option></select></td>";
		$html .= "<td><input class=\"df_servers_command\" id=\"df_servers_tsc_command\" type=\"text\" size=\"80\" value=\"$tscStart\"/>";
		$html .= "<input id=\"df_servers_tsc_execute\" class=\"df_servers_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$memcachedStart = self::guessPaths("memcached", "start");
		$memcachedEnd = self::guessPaths("memcached", "end");
		$html .= "<tr>";
		$html .= "<td>memcached</td>";


		$html .= "<td id=\"df_run_flag_memcached\" class=\"df_process_unknown\">".$dfgLang->getLanguageString('df_webadmin_process_unknown')."</td>";

		$html .= "<td><select id=\"memcached_selector\" class=\"df_action_selector\"><option value=\"$memcachedStart\">$startActionText</option><option value=\"$memcachedEnd\">$endActionText</option></select></td>";
		$html .= "<td><input class=\"df_servers_command\" id=\"df_servers_memcached_command\" type=\"text\" size=\"80\" value=\"$memcachedStart\"/>";
		$html .= "<input id=\"df_servers_memcached_execute\" class=\"df_servers_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$html .= "</table>";
		$html .= "<input id=\"df_servers_save_settings\" type=\"button\" value=\"Save settings\" disabled=\"true\"/>";
		return $html;
	}

	/**
	 * Guess the installation paths of common servers.
	 *
	 * @param string $program
	 * @param string $action
	 *
	 * @return string
	 */
	private static function guessPaths($program, $action = "start") {
		if (Tools::isWindows()) {
			global $smwgDFIP, $mwrootDir;
			$guessedInstallDir = realpath($smwgDFIP."/../../../");
			$nonPublicApps = Tools::getNonPublicAppPath($mwrootDir);
			if (array_key_exists("tsc", $nonPublicApps)) {
				$guessedTSCInstallDir = $nonPublicApps["tsc"];
			} else if (array_key_exists("tscprof", $nonPublicApps)) {
				$guessedTSCInstallDir = $nonPublicApps["tscprof"];
			} else {
				$opSoftware = Tools::getOntopriseSoftware();
				if (!is_null($opSoftware)) {
					// simply take first
					$first = reset($opSoftware);
					$guessedTSCInstallDir = $first[0];
				} else {
					$guessedTSCInstallDir = "";
				}
			}
			switch($program) {
				case "apache":
					return $action == "start" ?  "schtasks /run /tn stop_apache && schtasks /end /tn start_apache && schtasks /run /tn start_apache"
					: "should not be used!";
					break;
				case "mysql":
					return $action == "start" ?  "schtasks /run /tn start_mysql"
					: "schtasks /run /tn stop_mysql";
					break;
				case "solr":
					return $action == "start" ?  "schtasks /run /tn start_solr"
					: "schtasks /run /tn stop_solr";
					break;
				case "tsc":
					return $action == "start" ?  "schtasks /run /tn start_tsc"
					: "schtasks /run /tn stop_tsc";
					break;
				case "memcached":
					return $action == "start" ?  "schtasks /run /tn start_memcached"
					: "schtasks /run /tn stop_memcached";
					break;
			}
		} else {
			global $smwgDFIP, $mwrootDir;
			$guessedInstallDir = realpath($smwgDFIP."/../../../");
			$nonPublicApps = Tools::getNonPublicAppPath($mwrootDir);
			if (array_key_exists("tsc", $nonPublicApps)) {
				$guessedTSCInstallDir = $nonPublicApps["tsc"];
			} else if (array_key_exists("tscprof", $nonPublicApps)) {
				$guessedTSCInstallDir = $nonPublicApps["tscprof"];
			} else {
				$guessedTSCInstallDir = Tools::getProgramDir();
			}
			$initd = "/etc/init.d";
			switch($program) {
				case "apache":
					return $action == "start" ?  $initd."/apache2"
					: "apache2";
					break;
				case "mysql":
					return $action == "start" ?  "mysql"
					: "mysql";
					break;
				case "solr":
					return $action == "start" ?  $initd."/jetty"
					: $initd."/jetty";
					break;
				case "tsc":
					return $action == "start" ?  $initd."/tsc.sh"
					: $initd."/tsc";
					break;
				case "memcached":
					return $action == "start" ?  $initd."/memcached"
					: $initd."/memcached";
					break;
			}
		}
	}

	private static function quotePath($path) {
		return "&quot;$path&quot;";
	}

}
