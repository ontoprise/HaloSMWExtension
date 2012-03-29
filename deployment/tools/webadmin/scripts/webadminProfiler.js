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
 * webadmin scripts for DFProfilerTab
 * 
 * @author: Kai Kühn
 * 
 */
$(document).ready(
		function(e) {

			var profilingEnabled = false;
			$('#df_enableprofiling').attr(
					"value",
					dfgWebAdminLanguage
							.getMessage('df_webadmin_enableprofiling'));
			

			$('#df_enableprofiling').click(
					function() {
						$('#df_enableprofiling').attr("value",
								profilingEnabled ? dfgWebAdminLanguage
										.getMessage('df_webadmin_enableprofiling') : dfgWebAdminLanguage
										.getMessage('df_webadmin_disableprofiling'));
						$('#df_webadmin_profiler_content textarea').attr("disabled", profilingEnabled);
						$('#df_webadmin_profiler_content input').attr("disabled", profilingEnabled);
						profilingEnabled = !profilingEnabled;
					});
		});