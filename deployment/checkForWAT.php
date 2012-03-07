<?php
global $dfgTestfunctions;
$dfgTestfunctions[] = 'df_checkInstallation';

function df_checkInstallation() {
	global $dfgRequiredExtensions, $requiredPHPVersions, $dfgRequiredFunctions;

	$requiredPHPVersions[] = '5.3.2';

	$dfgRequiredExtensions['xml'][] = "Please install 'php_xml' if you want to use WAT.";

	if (array_key_exists('df_http_impl', DF_Config::$settings)) {
		$impl = DF_Config::$settings['df_http_impl'];
		if ($impl == 'HttpDownloadCurlImpl') {
			$dfgRequiredExtensions['curl'][] = "Please install 'php_curl' if you want to use WAT and especially WAT GUI-tool. ".
                                    "Instead, you might use the socket function implementation, but then only the command line tool is supported. ".
                                    "Take a look in the documentation (INSTALL file).";
		}else if ($impl == 'HttpDownloadSocketImpl') {
			$dfgRequiredFunctions['socket_create'][] = "Please install PHP's socket functions if you want to use WAT. ".
                                            "Instead, you might use 'php_curl'. Take a look in the documentation (INSTALL file).";
		}
	} else {
		$dfgRequiredExtensions['curl'][] = "Please install 'php_curl' if you want to use WAT and especially WAT GUI-tool. ".
                                    "Instead, you might use the socket function implementation, but then only the command line tool is supported. ".
                                    "Take a look in the documentation (INSTALL file).";
		$dfgRequiredFunctions['socket_create'][] = "Please install PHP's socket functions if you want to use WAT. ".
                                            "Instead, you might use 'php_curl'. Take a look in the documentation (INSTALL file).";
	}
}