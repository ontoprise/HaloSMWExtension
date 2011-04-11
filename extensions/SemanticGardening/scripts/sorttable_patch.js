

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