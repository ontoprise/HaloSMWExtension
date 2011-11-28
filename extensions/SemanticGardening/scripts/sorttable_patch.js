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



/*
 * Overrides the function in SMW_sortable.js 
 */		
function smw_getInnerText( el ) {
	var spans = el.getElementsByTagName( 'span' );
	if( spans.length > 0 ) {
		for ( var i = 0; i < spans.length; i++ ) {
			if( spans[i].className == 'smwsortkey' ) {
				return spans[i].innerHTML;
			}
		}
	}
	
	return el.innerHTML;
}
