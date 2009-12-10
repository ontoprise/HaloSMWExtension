var AjaxAsk = {
	queries : [], 
	helper : {
		getResultPrinter:function(id, qno, query){
			sajax_do_call('smwf_up_Access', ["ajaxAsk", "" + qno + "," + query], document.getElementById(id));
		}
	}
};

var AjaxSparql = {
    queries : [], 
    helper : {
        getResultPrinter:function(id, qno, query){
            sajax_do_call('smwf_up_Access', ["ajaxSparql", "" + qno + "," + query], document.getElementById(id));
        }
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