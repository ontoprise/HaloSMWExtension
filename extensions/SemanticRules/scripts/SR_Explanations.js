var explanation = null;

var Explanations = Class.create();
Explanations.prototype = {

initialize:function(){

},
//TODO: i18n

trigger:function(){
	// Check inputs
	if($("pcheck").checked){
		var ins = escape($("instance").value.strip());
		var prop = escape($("property").value.strip());
		var val = escape($("value").value.strip());
		if (ins == "" || prop == "" || val == ""){
			alert("Please fill out all three input fields");
			return;
		}
		// If all inputs set: trigger explanation
		var path = wgArticlePath.replace(/\$1/g, wgPageName);
		var concat = "?";
		if (path.indexOf("?") != -1)
			concat = "&";
		var url = wgServer + path + concat + "mode=property&i=" + ins + "&p=" + prop + "&v=" + val;
		window.location.href=url;
	} else {
		var ins = escape($("instance1").value.strip());
		var cat = escape($("category").value.strip());
		if (ins == "" || cat == ""){
			alert("Please fill out all two input fields");
			return;
		}
		// If all inputs set: trigger explanation
		var path = wgArticlePath.replace(/\$1/g, wgPageName);
		var url = wgServer + path + "?mode=category&i=" + ins + "&c=" + cat;
		window.location.href=url;
	}
	
},

radioChecked:function(){
	if($("pcheck").checked){
		$("propertyrow").style.display="";
		$("categoryrow").style.display="none";
	} else {
		$("propertyrow").style.display="none";
		$("categoryrow").style.display="";
	}
	//;
	
}
	
}


Event.observe(window, 'load', initialize_exp);

function initialize_exp(){
	explanation = new Explanations();
}
