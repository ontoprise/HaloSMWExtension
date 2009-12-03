var us_psc_done;
var httpRequest;

doPathSearch = function (input) {
	// if the function was called already, html is still there and we are done
	if (us_psc_done) return;
    
    // show loading image
    document.getElementById('us_pathsearch_results').innerHTML = 
      '<img src="' + US_PATHSEARCH_DIR + '/../scripts/GreyBox/indicator.gif" alt="Loading pathsearch"/>';
	
	// call backend
	// Mozilla, Safari and other browsers
    if (window.XMLHttpRequest) { 
        httpRequest = new XMLHttpRequest(); 
    } 
    // IE 
    else if (window.ActiveXObject) {
    	try {
            httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }
    
    if (!httpRequest) return;

    httpRequest.onreadystatechange = handleResponsePsc;
    httpRequest.open("GET", wgServer + wgScriptPath + '/index.php?action=ajax&rs=us_doPathSearch&rsargs[]=' + input);
	httpRequest.send(null);
}

handleResponsePsc = function() {
	var result;
	var resObj;

	if (httpRequest.readyState == 4 && httpRequest.status == 200) { 
    	result = httpRequest.responseText;
    	httpRequest = null;
    }
    else return;

    resObj = !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(result.replace(/"(\\.|[^"\\])*"/g, ''))) 
             && eval('(' + result + ')');
    if (resObj.result == NULL) return;
    
    if (resObj.result.length == 0)
    	document.getElementById('us_pathsearch_results').innerHTML = 'An internal error occurred.';
	else
    	document.getElementById('us_pathsearch_results').innerHTML = resObj.result;
    
    us_psc_done = 1;
}

switchTabs = function(click) {
	var tab = document.getElementById('us_searchresults_tab');
	var tab_fulltext = tab.getElementsByTagName('td')[1];
	var tab_path = tab.getElementsByTagName('td')[3];
	
	// div for content of tabs
	var styleTabEnabled = document.createAttribute("style");
    styleTabEnabled.nodeValue = 'font-weight: bold; color: black; border-top: 2px solid #FF8C00; border-left: 2px solid #AAA; border-right: 2px solid #AAA;';
	var styleTabDisabled = document.createAttribute("style");
    styleTabDisabled.nodeValue = 'font-weight: normal; border: 2px solid #AAA;';
    
	if (click == 1) { // click on tab path
		document.getElementById('us_pathsearch_results').style.display = "inline";
		tab_path.setAttributeNode(styleTabEnabled);
		document.getElementById('us_fulltext_results').style.display = "none";
		tab_fulltext.setAttributeNode(styleTabDisabled);
		document.getElementById('us_browsing_top_div').style.display = "none";
		document.getElementById('us_browsing_bottom_div').style.display = "none";
		document.getElementById('us_refineresults').style.display = "none";
		document.getElementById('us_refineresults_label').style.display = "none";
		document.getElementById('us_browsing_top_hide_div').style.display = "block";
		document.getElementById('us_browsing_bottom_hide_div').style.display = "block";
		document.getElementById('us_refineresults_hide').style.display = "block";
		document.getElementById('us_refineresults_label_hide').style.display = "block";
	}
	else {
		document.getElementById('us_pathsearch_results').style.display = "none";
		tab_path.setAttributeNode(styleTabDisabled);
		document.getElementById('us_fulltext_results').style.display = "block";
		tab_fulltext.setAttributeNode(styleTabEnabled);
		document.getElementById('us_browsing_top_div').style.display = "block";
		document.getElementById('us_browsing_bottom_div').style.display = "block";
		document.getElementById('us_refineresults').style.display = "block";
		document.getElementById('us_refineresults_label').style.display = "block";
		document.getElementById('us_browsing_top_hide_div').style.display = "none";
		document.getElementById('us_browsing_bottom_hide_div').style.display = "none";
		document.getElementById('us_refineresults_hide').style.display = "none";
		document.getElementById('us_refineresults_label_hide').style.display = "none";
	}
	document.getElementById('doPathSearch').value = click;
}