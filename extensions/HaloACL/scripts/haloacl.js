/*  Copyright 2009, ontoprise GmbH
*   Author: Benjamin Langguth
*   This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var HaloACLSpecialPage = Class.create({
	initialize: function() {
		// do nothing special.
	},

	testMe: function() {
		alert('script loaded!');
	},

	/**
	 * Toggles element's display value
	 * Input: any number of element id's
	 * Output: none
	 */ 
	toggleDisp: function() {
		for (var i = 0; i < arguments.length; i++) {
			var d = $(arguments[i]);
			if ( d.style.display == 'block' )
				d.style.display = 'block';
			else
				d.style.display = 'none';
		}
	},

	/**
	 * Toggles tabs - Closes any open tabs, and then opens current tab
	 * Input:	1.The number of the current tab
	 *			2.The number of tabs
	 *			3.(optional)The number of the tab to leave open
	 *			4.(optional)Pass in true or false whether or not to animate the open/close of the tabs
	 * Output: none
	 */ 
	toggleTab: function( num, numelems, opennum, animate ) {
		if ( $('haclTabContent'+num).style.display == 'none' ) {
			for (var i = 1; i <= numelems; i++) {
				if ( ( opennum == null ) || ( opennum != i ) ) {
					var temph = 'haclTabHeader'+i;
					var h = $(temph);
					if ( !h ) {
						var h = $('haclTabHeaderActive');
						h.id = temph;
					}
					var tempc = 'haclTabContent'+i;
					var c = $(tempc);
					if( c.style.display != 'none' ) {
						if ( animate || typeof animate == 'undefined' ) {
							Effect.toggle( tempc, 'blind', {duration:0.5, queue:{scope:'menus', limit: 3}} );
						}
						else {
							this.toggleDisp(tempc);
						}
					}
				}
			}
			var h = $('haclTabHeader'+num);
			if ( h ) {
				h.id = 'haclTabHeaderActive';
				h.blur();
			}
			var c = $('haclTabContent'+num);
			c.style.marginTop = '2px';
			if ( animate || typeof animate == 'undefined' ) {
				Effect.toggle('haclTabContent'+num,'blind',{duration:1, queue:{scope:'menus', position:'end', limit: 3}});
			}
			else {
				this.toggleDisp('haclTabContent'+num);
			}
		}
	},
});

//------ Classes -----------

var haloACLSpecialPage = new HaloACLSpecialPage();