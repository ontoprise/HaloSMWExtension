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

$(function() {
	// register in OB
	
	var launchBot = function(params) {
		
		var callBackOnRunBot = function() {
			alert("Bot started");
		}
		
		var callBackOnError = function() {
			alert("Error");
		}
		
		var url = wgScriptPath + "/index.php?action=ajax";
		$.ajax({
			type: 'POST',
			url: url,
			success: callBackOnRunBot,
			dataType: 'json',
			data:
				{
					"rs" : "smwf_ga_LaunchGardeningBot",
					"rsargs[]": SMWRFRefactoringBot,
					"rsargs[]": $.toJSON(params)
				},
			error: callBackOnError
		});
			
	}
	
	var showDialog = function(params) {
		// show a dialog and let the user set the options
	}
}
