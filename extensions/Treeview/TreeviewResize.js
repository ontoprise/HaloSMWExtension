/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var smwhg_oldonload = new Array();
if (typeof window.onload == 'function'){
	smwhg_oldonload.push(window.onload);
}

window.onload= function(){  
		for(var i = smwhg_oldonload.length-1; i >= 0; i--) {
			var func = smwhg_oldonload[i];
			func();
		}
};

var smwhg_oldonresize = new Array();
if (typeof window.onresize == 'function'){
	smwhg_oldonresize.push(window.onresize);
}

window.onresize= function(){  
		for(var i = smwhg_oldonresize.length-1; i >= 0; i--) {
			var func = smwhg_oldonresize[i];
			func();
		}
};

function initializeTreeviewResize(){
	
function SmwhgTreeviewResize() {};

SmwhgTreeviewResize.prototype = {
	
	/**
	* default constructor
	* Constructor
	*
	*/
	initialize: function() {
		$$('.smwf_navihead').each(
			function(item){
				
				//Adds resizefunction to element without overriding old
				var oldfunc = item.onclick;
				item.onclick = function(){
									if(smwhgTreeviewResize != null){
										oldfunc();
										smwhgTreeviewResize.resize();
									}
								};
									
			});
            
	},
	
	
	resize: function(){
		$$('#smwf_browserview .dtree').each(
			function(item){
				var bottom = document.viewport.getHeight();
				var top = item.viewportOffset()[1];
				var gap = bottom - top;
				var treeheight = gap-20; 
				
				if( gap > 100){
					
					item.setStyle({
						  height: treeheight+"px"
						});
				} else {
					item.setStyle({
						  height: "100%"
						});
				}
			})
	}
}

//Initialize Treeview resizing
var smwhgTreeviewResize = new SmwhgTreeviewResize();
//Resize on Startup
setTimeout(smwhgTreeviewResize.resize,1000);
smwhg_oldonresize.push(smwhgTreeviewResize.resize);
}


smwhg_oldonload.push(initializeTreeviewResize);
