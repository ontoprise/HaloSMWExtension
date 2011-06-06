//steal/js d:/mediawiki/halosmwextension/extensions/semanticnotifications/scripts/sngui/scripts/compress.js

load("steal/rhino/steal.js");
steal.plugins('steal/clean',function(){
	steal.clean('d:/mediawiki/halosmwextension/extensions/semanticnotifications/scripts/sngui/sngui.html',{
		indent_size: 1, 
		indent_char: '\t', 
		jslint : false,
		ignore: /jquery\/jquery.js/,
		predefined: {
			steal: true, 
			jQuery: true, 
			$ : true,
			window : true
			}
	});
});
