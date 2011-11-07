window.LiveQuery = {
    helper : {
        getResultPrinter:function(id, query, frequency){
        	var target = function(x) {
                var node = document.getElementById(id);
                if (x.status == 200) node.innerHTML = x.responseText;
                else node.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
                smw_makeSortable(node.firstChild);
                smw_tooltipInit();
                
                if(frequency*1 > 0){
                	window.setTimeout('LiveQuery.helper.getResultPrinter("' + id + '", "' + query + '", "' + frequency + '")', frequency*1000);
        		}
             };
             sajax_do_call('smwf_lq_refresh', [id, query], target);
        }
    }
};

// copied this code since resource loader dependencies did not work for me here
var SMW_PATH = wgScriptPath + '/extensions/SemanticMediaWiki/skins';
function smw_makeSortable( table ) {
	if ( table.rows && table.rows.length > 0 ) {
		var firstRow = table.rows[0];
	}
	if ( !firstRow ) {
		return;
	}
	if ( ( firstRow.cells.length == 0 ) || ( firstRow.cells[0].tagName.toLowerCase() != 'th' ) ) {
		return;
	}

	// We have a first row that is a header; make its contents clickable links:
	for ( var i = 0; i < firstRow.cells.length; i++ ) {
		var cell = firstRow.cells[i];
		
		cell.innerHTML = '<a href="#" class="sortheader" '+
		'onclick="smw_resortTable(this, '+i+');return false;">' +
		'<span class="sortarrow"><img alt="[&lt;&gt;]" src="' + SMW_PATH + '/images/sort_none.gif"/></span></a>&nbsp;<span style="margin-left: 0.3em; margin-right: 1em;">' + cell.innerHTML + '</span>'; // the &nbsp; is for Opera ...
	}

	/**
	 * make sortkeys invisible
	 * for now done in CSS
	 * this code provides the possibility to do it via JS, so that non-JS
	 * clients can see the keys
	 */
/*	for( var ti = 0; ti < table.rows.length; ti++ ) {
		for ( var tj = 0; tj < table.rows[ti].cells.length; tj++ ) {
			var spans = table.rows[ti].cells[tj].getElementsByTagName( 'span' );
			if( spans.length > 0 ) {
				for ( var tk = 0; tk < spans.length; tk++ ) {
					if( spans[tk].className == 'smwsortkey' ) {
						spans[tk].style.display = 'none';
					}
				}
			}
		}
	}*/
}

addOnloadHook( function lq_init(){
	jQuery('.lq-container').each(function(){
		LiveQuery.helper.getResultPrinter(
			jQuery(this).attr('id'),
			jQuery('.lq-query', this).html(),
			jQuery(this).attr('lq-frequency'));
	});
});