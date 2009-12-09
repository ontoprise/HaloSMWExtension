var AjaxAsk = {
	queries : [], 
	helper : {
		getResultPrinter:function(id, qno, query){
			sajax_do_call('smwf_up_Access', ["ajaxAsk", "" + qno + "," + query], document.getElementById(id));
		}
	}
};

function initialize_ajaxask(){
	for(var i=0;i<AjaxAsk.queries.length;++i) {
		AjaxAsk.helper.getResultPrinter(AjaxAsk.queries[i].id, AjaxAsk.queries[i].qno, AjaxAsk.queries[i].query)
	}
}

addOnloadHook(initialize_ajaxask);