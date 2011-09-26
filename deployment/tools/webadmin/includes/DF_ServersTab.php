<?php
/*  Copyright 2011, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup WebAdmin
 *
 * Server tab
 *
 * @author: Kai Kühn / ontoprise / 2011
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
		
		$executeText = $dfgLang->getLanguageString('df_webadmin_server_execute');
		$startActionText = $dfgLang->getLanguageString('df_webadmin_server_start');
		$endActionText = $dfgLang->getLanguageString('df_webadmin_server_end');
		
		$html = $dfgLang->getLanguageString('df_webadmin_configureservers');
		
		$html .= "<br/><br/>";
		$html .= "<table>";

		$html .= "<tr>";
		$apacheStart = htmlspecialchars(self::guessPaths("apache", "start"));
		$apacheEnd = htmlspecialchars(self::guessPaths("apache", "end"));
		$html .= "<td>Apache</td>";
		
		if (Tools::isProcessRunning("httpd")) {
		  $html .= "<td id=\"df_run_flag_httpd\" class=\"df_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_runs')."</td>";
        } else {
            $html .= "<td id=\"df_run_flag_httpd\" class=\"df_not_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_doesnot_run')."</td>";
        }
		$html .= "<td><select id=\"httpd_selector\" class=\"df_action_selector\"><option value=\"$apacheStart\">$startActionText</option><option  value=\"$apacheEnd\">$endActionText</option></select>";
		$html .= "<input class=\"df_servers_command\" id=\"df_servers_httpd_command\" type=\"text\" size=\"80\" value=\"$apacheStart\"/>";
		$html .= "<input id=\"df_servers_httpd_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$mysqlStart = self::guessPaths("mysql", "start");
		$mysqlEnd = self::guessPaths("mysql", "end");
		$html .= "<tr>";
		$html .= "<td>mySQL</td>";
	
		if (Tools::isProcessRunning("mysqld")) {
		  $html .= "<td id=\"df_run_flag_mysqld\" class=\"df_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_runs')."</td>";
        } else {
            $html .= "<td id=\"df_run_flag_mysqld\" class=\"df_not_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_doesnot_run')."</td>";
        }
		$html .= "<td><select id=\"mysql_selector\" class=\"df_action_selector\"><option value=\"$mysqlStart\">$startActionText</option value=\"$mysqlEnd\"><option>$endActionText</option></select>";
		$html .= "<input class=\"df_servers_command\" id=\"df_servers_mysqld_command\" type=\"text\" size=\"80\" value=\"$mysqlStart\"/>";
		$html .= "<input id=\"df_servers_mysqld_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$solrStart = self::guessPaths("solr", "start");
		$solrEnd = self::guessPaths("solr", "end");
		$html .= "<tr>";
		$html .= "<td>solr</td>";
		
		if (Tools::isProcessRunning("solr")) {
		  $html .= "<td id=\"df_run_flag_solr\" class=\"df_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_runs')."</td>";
        } else {
            $html .= "<td id=\"df_run_flag_solr\" class=\"df_not_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_doesnot_run')."</td>";
        }
		$html .= "<td><select id=\"solr_selector\" class=\"df_action_selector\"><option value=\"$solrStart\">$startActionText</option value=\"$solrEnd\"><option>$endActionText</option></select>";
		$html .= "<input class=\"df_servers_command\" id=\"df_servers_solr_command\" type=\"text\" size=\"80\" value=\"$solrStart\"/>";
		$html .= "<input id=\"df_servers_solr_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$tscStart = self::guessPaths("tsc", "start");
		$tscEnd = self::guessPaths("tsc", "end");
		$html .= "<tr>";
		$html .= "<td>tsc</td>";
		
		if (Tools::isProcessRunning("tsc")) {
		  $html .= "<td id=\"df_run_flag_tsc\" class=\"df_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_runs')."</td>";
        } else {
            $html .= "<td id=\"df_run_flag_tsc\" class=\"df_not_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_doesnot_run')."</td>";
        }
		$html .= "<td><select id=\"tsc_selector\" class=\"df_action_selector\"><option value=\"$tscStart\">$startActionText</option value=\"$tscEnd\"><option>$endActionText</option></select>";
		$html .= "<input class=\"df_servers_command\" id=\"df_servers_tsc_command\" type=\"text\" size=\"80\" value=\"$tscStart\"/>";
		$html .= "<input id=\"df_servers_tsc_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$memcachedStart = self::guessPaths("memcached", "start");
		$memcachedEnd = self::guessPaths("memcached", "end");
		$html .= "<tr>";
		$html .= "<td>memcached</td>";

		if (Tools::isProcessRunning("memcached")) {
			$html .= "<td id=\"df_run_flag_memcached\" class=\"df_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_runs')."</td>";
		} else {
			$html .= "<td id=\"df_run_flag_memcached\" class=\"df_not_running_process\">".$dfgLang->getLanguageString('df_webadmin_process_doesnot_run')."</td>";
		}
		$html .= "<td><select id=\"memcached_selector\" class=\"df_action_selector\"><option value=\"$memcachedStart\">$startActionText</option value=\"$memcachedEnd\"><option>$endActionText</option></select>";
		$html .= "<input class=\"df_servers_command\" id=\"df_servers_memcached_command\" type=\"text\" size=\"80\" value=\"$memcachedStart\"/>";
		$html .= "<input id=\"df_servers_memcached_execute\" type=\"button\" value=\"$executeText\"/>";
		$html .= "</td></tr>";

		$html .= "</table>";
		$html .= "<input id=\"df_servers_save_settings\" type=\"button\" value=\"Save settings\"/>";
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
	private static function guessPaths($program, $action) {
		if (Tools::isWindows()) {
			global $smwgDFIP;
			$guessedInstallDir = realpath($smwgDFIP."/../../../");
			switch($program) {
				case "apache":
					return $action == "start" ?  $guessedInstallDir."\\apache_start.exe"
					: $guessedInstallDir."\\apache_stop.exe";
					break;
				case "mysql":
					return $action == "start" ?  $guessedInstallDir."\\mysql_start.exe"
					: $guessedInstallDir."\\mysql_stop.exe";
					break;
				case "solr":
					return $action == "start" ?  $guessedInstallDir."\\solr_start.exe"
					: $guessedInstallDir."\\solr_stop.exe";
					break;
				case "tsc":
					return $action == "start" ?  $guessedInstallDir."\\tsc.exe"
					: $guessedInstallDir."\\stop-triplestore.bat";
					break;
				case "memcached":
					return $action == "start" ?  $guessedInstallDir."\\memcached.exe -d start"
					: $guessedInstallDir."\\memcached.exe -d stop";
					break;
			}
		} else {
			$initd = "etc/init.d";
			switch($program) {
				case "apache":
					return $action == "start" ?  $initd."/apache start"
					: $initd."/apache stop";
					break;
				case "mysql":
					return $action == "start" ?  $initd."/mysql start"
					: $initd."/mysql stop";
					break;
				case "solr":
					return $action == "start" ?  $initd."/solr start"
					: $initd."/solr start";
					break;
				case "tsc":
					return $action == "start" ?  $guessedInstallDir."/tsc.exe"
					: $guessedInstallDir."/stop-triplestore.bat";
					break;
				case "memcached":
					return $action == "start" ?  $initd."/memcached start"
					: $initd."/memcached stop";
					break;
			}
		}
	}

}