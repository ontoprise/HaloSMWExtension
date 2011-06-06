module("sngui test", { 
	setup: function(){
		S.open("//d:/mediawiki/halosmwextension/extensions/semanticnotifications/scripts/sngui/sngui.html");
	}
});

test("Copy Test", function(){
	equals(S("h1").text(), "Welcome to JavaScriptMVC 3.0!","welcome text");
});