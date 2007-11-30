window.onload = function() {
	var sc = document.getElementById('collation');
	sc.setAttribute("disabled", "disabled");
	document.getElementById('sc').onclick = function(event) {
		
		if (sc.hasAttribute('disabled')) {
			sc.removeAttribute("disabled");
		} else {
			sc.setAttribute("disabled", "disabled");
		}
	}
}

