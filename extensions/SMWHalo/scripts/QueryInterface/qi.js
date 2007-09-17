//var qihelper = new QIHelper();
var qihelper = null;
Event.observe(window, 'load', initialize);

function initialize(){
	qihelper = new QIHelper();
}

function plusminus(){
	if($('tcpplusminus').className == "plus"){
		$('tcpplusminus').removeClassName("plus");
		$('tcpplusminus').addClassName("minus");
	} else {
		$('tcpplusminus').removeClassName("minus");
		$('tcpplusminus').addClassName("plus");
	}
}

function switchtcp(){
	if($("tcp_boxcontent").style.visibility == "hidden"){
		$("tcp_boxcontent").style.visibility = "visible";
		$("tcptitle-link").removeClassName("plusminus");
		$("tcptitle-link").addClassName("minusplus");
	}
	else {
		$("tcp_boxcontent").style.visibility = "hidden";
		$("tcptitle-link").removeClassName("minusplus");
		$("tcptitle-link").addClassName("plusminus");
	}
}

function switchlayout(){
	if($("layoutcontent").style.display == "none"){
		$("layoutcontent").style.display = "";
		$("layouttitle-link").removeClassName("plusminus");
		$("layouttitle-link").addClassName("minusplus");
	}
	else {
		$("layoutcontent").style.display = "none";
		$("layouttitle-link").removeClassName("minusplus");
		$("layouttitle-link").addClassName("plusminus");
	}
}