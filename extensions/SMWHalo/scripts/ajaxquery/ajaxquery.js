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

AjaxAsk.helper = {
        getResultPrinter:function(id, qno, query){
             var target = function(x) {
                var node = document.getElementById(id);
                if (x.status == 200) node.innerHTML = x.responseText;
                else node.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
                smw_makeSortable(node.firstChild);
                smw_tooltipInit();
             }
            sajax_do_call('smwf_aq_Access', ["ajaxAsk", "" + qno + "," + query], target);
        }
    };

AjaxSparql.helper = {
        getResultPrinter:function(id, qno, query){
        	 var target = function(x) {
        	 	var node = document.getElementById(id);
                if (x.status == 200) node.innerHTML = x.responseText;
                else node.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
                smw_tooltipInit();
                smw_makeSortable(node);
             }
            sajax_do_call('smwf_aq_Access', ["ajaxSparql", "" + qno + "," + query], target);
        }
    };

function initialize_ajaxqueries(){
	for(var i=0;i<AjaxAsk.queries.length;++i) {
		AjaxAsk.helper.getResultPrinter(AjaxAsk.queries[i].id, AjaxAsk.queries[i].qno, AjaxAsk.queries[i].query)
	}
	for(var i=0;i<AjaxSparql.queries.length;++i) {
        AjaxSparql.helper.getResultPrinter(AjaxSparql.queries[i].id, AjaxSparql.queries[i].qno, AjaxSparql.queries[i].query)
    }
}

addOnloadHook(initialize_ajaxqueries);
