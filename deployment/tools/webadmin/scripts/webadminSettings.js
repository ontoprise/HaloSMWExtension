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
 * webadmin scripts for DF_SettingsTab
 * 
 * @author: Kai Kühn
 * 
 */
$(document).ready(function(e) {
	
	$.webAdmin.settings = {};
	$.webAdmin.settings.getSettings = function() {
		var settings = getSettingsFromCookie(document.cookie);
		return $.extend(settings_defaults, settings);
	}
	
	// default settings of WAT options
	var settings_defaults = { 
			df_watsettings_overwrite_always : true,
			df_watsettings_merge_with_other_bundle : true,
			df_watsettings_install_optionals : false,
			df_watsettings_deinstall_dependant : false,
			df_watsettings_apply_patches : true,
			df_watsettings_create_restorepoints: false,
			df_watsettings_hidden_annotations : true,
			df_watsettings_use_namespaces : true 
	};
	
	/**
	 * Applies the settings with the values given from a cookie string.
	 * 
	 * @param object with key-value pairs
	 */
	var applySettings = function(settings) {
		var cookies = [];
		for(id in settings) {
			var value = $.trim(settings[id]);
			$("#"+id).attr("checked", value == "true");
		}
	}
	
	/**
	 * Returns object with key value pairs from the given cookie.
	 * 
	 * @param string cookie 
	 * 
	 * @return object
	 */
	var getSettingsFromCookie = function(cookie) {
		var settings = "";
		var all_settings = cookie.split(";");
		$.grep(all_settings, function(e) { 
			if (e.substr(0, 12) == 'df_settings=') {
				settings = e.substr(12);
			}
		});
		var result = {};
		var dfSettingsArray = settings.split(",");
		$(dfSettingsArray).each(function(i, e) { 
			var keyValue = e.split("=");
			var id = $.trim(keyValue[0]);
			var value = $.trim(keyValue[1]) == "true";
			result[id] = value;
		});
		return result;
	}
	
	/**
	 * Serializes the object with as key-value pairs
	 * 
	 * key1=value1,key2=value2,...
	 * 
	 * @param object with key-value pairs
	 * 
	 * @return string
	 */
	var serializeSettings = function(settings) {
		var result=[];
		for(var s in settings) {
			var value = settings[s] ? "true" : "false";
			result.push(s+"="+value);
		}
		return result.join(",");
	}

	// expiration date in 30 days from now on
	var expirationDate = new Date();
	var thirtyDays = expirationDate.getTime() + (30 * 24 * 60 * 60 * 1000);
	expirationDate.setTime(thirtyDays);
		
	// read cookie and apply the settings 
	var cookie = document.cookie;
	if (cookie) {
		var settings = getSettingsFromCookie(cookie);
		settings = $.extend(settings_defaults, settings);
		applySettings(settings);
		document.cookie = "df_settings=" + serializeSettings(settings)+"; expires=" + expirationDate.toGMTString();
	} else {
		// no cookies available at all, use defaults
		applySettings(settings_defaults);
	}
	
	// set change listeners
	$('#df_watsettings input').change(function() {
		// update cookie
		var settings = [];
		$('#df_watsettings input').each(function(i, e) { 
			var id = $(e).attr("id");
			var value = e.checked ? "true" : "false";
			settings.push(id+"="+value);
		});
		document.cookie = "df_settings=" + settings.join(",")+"; expires=" + expirationDate.toGMTString();
	});
	
	// reset defaults button
	$('#df_resetdefault_settings').click(function() { 
		applySettings(settings_defaults);
		document.cookie = "df_settings=" + serializeSettings(settings)+"; expires=" + expirationDate.toGMTString();
	});
});