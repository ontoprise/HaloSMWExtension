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
 * webadmin scripts for general functionality
 *
 * @author: Kai Kühn
 *
 */
$(document).ready(function(e) {

	// make tables sortable
	smw_preload_images();
	smw_makeSortable($('#df_statustable')[0]);
	smw_makeSortable($('#df_bundlefilelist_table')[0]);
	smw_makeSortable($('#df_restorepoint_table')[0]);

});