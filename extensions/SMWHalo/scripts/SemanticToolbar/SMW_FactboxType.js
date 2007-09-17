function factboxTypeChanged(select, title){
		$('typeloader').show();
		var type = select.options[select.options.selectedIndex].value;
		sajax_do_call('smwgNewAttributeWithType', [title, type], refreshAfterTypeChange);
}

function refreshAfterTypeChange(request){
	window.location.href=location.href;
}