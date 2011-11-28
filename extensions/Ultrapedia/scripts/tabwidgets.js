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

var UltraPedia = {tabWidgets : []};
Ext.onReady(function(){
	for(var i=0;i<UltraPedia.tabWidgets.length;++i) {
	    new Ext.TabPanel({
	        renderTo : UltraPedia.tabWidgets[i].id,
	        activeTab : 0,
	        width : (UltraPedia.tabWidgets[i].width>0?UltraPedia.tabWidgets[i].width:600),
	        height : (UltraPedia.tabWidgets[i].height>0?UltraPedia.tabWidgets[i].height:250),
	        plain : true,
	        defaults : {autoScroll: true},
	        items : UltraPedia.tabWidgets[i].items
	    });
    }
});
