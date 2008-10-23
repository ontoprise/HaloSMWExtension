/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

Event.observe(window, 'load', dummy);

// dummy function - just make sure the page is loaded properly before we
// display the toolbar
function dummy() {
}

function trim(string) {
	return string.replace(/(^\s+|\s+$)/g, "");
}

// *************************
// cookie handling functions
// *************************

function stGetCookieTab() {
	var cookie = document.cookie;
	var length = cookie.length-1;
	if (cookie.charAt(length) != ";")
		cookie += ";";
	var a = cookie.split(";");

	// walk through cookies...
	for (var i=0; i<a.length; i++) {
		var cookiename = trim(a[i].substring(0, a[i].search('=')));
		var cookievalue = a[i].substring(a[i].search('=')+1,a[i].length);
		if (cookiename == "stbpreftab") {
			var cookievalue = cookievalue.split(",");
			var retval = new Array();
			for (var j =0; j<cookievalue.length;j++) {
				retval[j] = parseInt(cookievalue[j]);
			}
			stCookiePrefTab = retval;
		} else if (cookiename == "stbprefhelp") {
			stCookieHelpTab = parseInt(cookievalue);
		}
	}
}

function stSetCookie(curtabpos) {

	var a = new Date();
	a = new Date(a.getTime() +1000*60*60*24*365);
	var implode = '';
	var first = true;
	for (var i=0; i<curtabpos.length; i++) {
		if (first == true)
			first = false;
		else
			implode += ",";
		implode += curtabpos[i];
	}

	document.cookie = 'stbpreftab='+implode+'; expires='+a.toGMTString()+';';
}

function stSetHelpCookie(helpshown) {

	var a = new Date();
	a = new Date(a.getTime() +1000*60*60*24*365);

	document.cookie = 'stbprefhelp='+helpshown+'; expires='+a.toGMTString()+';';
}


// *****************************************
// Observer Design pattern for stb framework
// *****************************************

// IE needs this extra piece of code
if ( !Array.prototype.forEach )
{
	Array.prototype.forEach = function(fn, thisObj)
	{
	   	var scope = thisObj || window;
	   	for ( var i=0, j=this.length; i < j; ++i )
	   	{
	       	fn.call(scope, this[i], i, this);
   		}
	};
}

if ( !Array.prototype.filter ) {
	Array.prototype.filter = function(fn, thisObj)
	{
    	var scope = thisObj || window;
    	var a = [];
    	for ( var i=0, j=this.length; i < j; ++i )
    	{
        	if ( !fn.call(scope, this[i], i, this) )
        	{
            	continue;
        	}
        	a.push(this[i]);
    	}
    	return a;
	};
}

// our observer pattern
function Observer() {
    this.fns = [];
    this.postUpdateHTMLFunctions = [];
}
Observer.prototype = {
    subscribe : function(fn) {
        this.fns.push(fn);
    },
    subscribeOnce : function(fn) {
		fn.call();
    },
    unsubscribe : function(fn)
    {
        this.fns = this.fns.filter(
            function(el)
            {
                if ( el !== fn )
                {
                    return el;
                }
            }
        );
    },
    addPostUpdateHTMLFunction: function(fn) {
    	this.postUpdateHTMLFunctions.push(fn);
    },
    // notify notifies/calls previously registered functions to update their content
    notify : function(o, thisObj)
    {
    	// only notify one container
    	if (o) {
			showMenu(o);
    	} else {
	        var scope = thisObj || window;
	        this.fns.forEach(
	        	function(el)
	        	{
	                el.call(scope, o);
	          	}
	        );
	        showMenu();
	    }

		this.postUpdateHTMLFunctions.forEach(
        	function(puhf)
        	{
                puhf.call(scope, o);
          	}
        );
    }
};


// main functionality

// **********************************
// Display and handling of actual stb
// **********************************

// create observer object
stObserver = new Observer();

var stNumDefinedContainers = 0;
var	stCurrentTabShown = 0;
var stInitialTab = 0;
var stTabPosition = new Array ();
var stDivCount = 0;
var stCookiePrefTab = new Array ();
var stCookieHelpTab = 1; 				// default to display helpcontainer

function updtContainer(tmpcontainer) {
	stResult = "";
	stFillContainers(tmpcontainer);
	switch (tmpcontainer.container) {
		case PROPERTIESCONTAINER:
				var tmpdiv = document.getElementById("properties-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case RELATIONCONTAINER:
				var tmpdiv = document.getElementById("relation-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case RULESCONTAINER:
				var tmpdiv = document.getElementById("rules-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case ATTRIBUTECONTAINER:
				var tmpdiv = document.getElementById("attribute-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case CATEGORYCONTAINER:
				var tmpdiv = document.getElementById("category-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case TYPECONTAINER:
				var tmpdiv = document.getElementById("typecont-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case HELPCONTAINER:
				var tmpdiv = document.getElementById("help-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case CBSRCHCONTAINER:
				var tmpdiv = document.getElementById("cbsrch-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case COMBINEDSEARCHCONTAINER:
				var tmpdiv = document.getElementById("cbsrch-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case SAVEANNOTATIONSCONTAINER:
				var tmpdiv = document.getElementById("saveannotation-headline");
				tmpdiv.innerHTML = stResult;
			break;
		case ANNOTATIONHINTCONTAINER:
				var tmpdiv = document.getElementById("annotationhint-headline");
				tmpdiv.innerHTML = stResult;
			break;
		}
}

function stFillContainers(tmpcontainer)
{
	switch (stCurrentTabShown)
	{
		case 0:
			switch (tmpcontainer.container)
			{
				case HELPCONTAINER:
						stResult += "<div id=\"help-headline\" style=\"cursor:pointer;cursor:hand\" onclick=\"switchVisibility(" + HELPCONTAINER + ", \'help\', \'help-headline-link\')\"><a id=\"help-headline-link\" class=\"";
						if (stCookieHelpTab == 0 ) {
							stResult +="plusminus";
							tmpcontainer.ishidden = true;
						}
						else stResult +="minusplus";
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";
						stResult += "<div id=\"help\"";
						if (stCookieHelpTab == 0) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";
					break;
				case TYPECONTAINER:
						stResult += "<div id=\"typecont-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + TYPECONTAINER + ", \'typecont\', \'type-headline-link'\)\"><a id=\"type-headline-link\" class=\"minusplus\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";
						stResult += "<div id=\"typecont\">" + tmpcontainer.content + "</div>";
					break;
				case CATEGORYCONTAINER:
						stResult += "<div id=\"category-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + CATEGORYCONTAINER + ", \'category-content\', \'category-headline-link\')\"><a id=\"category-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						}
						else stResult +="minusplus";
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"category-content\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";

						stDivCount++;
					break;
				case ATTRIBUTECONTAINER:
						stResult += "<div id=\"attribute-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + ATTRIBUTECONTAINER + ", \'attribute-content\', \'attribute-headline-link\')\"><a id=\"attribute-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						}
						else stResult +="minusplus";
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"attribute-content\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";
						stDivCount++;
					break;
				case RELATIONCONTAINER:
						stResult += "<div id=\"relation-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + RELATIONCONTAINER + ", \'relation-content\', \'relation-headline-link\')\"><a id=\"relation-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						}
						else stResult +="minusplus";
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"relation-content\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";
						stDivCount++;
					break;
				case RULESCONTAINER:
						stResult += "<div id=\"rules-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + RULESCONTAINER + ", \'rules-content\', \'rules-headline-link\')\"><a id=\"rules-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						} else 
							stResult +="minusplus";
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"rules-content\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";
						stDivCount++;
					break;
				case PROPERTIESCONTAINER:
						stResult += "<div id=\"properties-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + PROPERTIESCONTAINER + ", \'properties-content\', \'properties-headline-link\')\"><a id=\"properties-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						} else {
							stResult +="minusplus";
						}
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"properties-content\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";

						stDivCount++;
					break;
				case CBSRCHCONTAINER:
						stResult += "<div id=\"cbsrch-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + CBSRCHCONTAINER + ", \'cbsrch\', \'cbsrch-headline-link\')\"><a id=\"cbsrch-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						}
						else stResult +="minusplus";
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"cbsrch\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";
					break;
				case COMBINEDSEARCHCONTAINER:
						stResult += "<div id=\"cbsrch-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + COMBINEDSEARCHCONTAINER + ", \'cbsrch\', \'cbsrch-headline-link\')\"><a id=\"cbsrch-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						}
						else stResult +="minusplus";
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"cbsrch\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";
					break;
				case SAVEANNOTATIONSCONTAINER:
						stResult += "<div id=\"saveannotation-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + SAVEANNOTATIONSCONTAINER + ", \'saveannotation-content\', \'saveannotation-headline-link\')\"><a id=\"saveannotation-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						} else {
							stResult +="minusplus";
						}
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"saveannotation-content\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";

						stDivCount++;
					break;
				case ANNOTATIONHINTCONTAINER:
						stResult += "<div id=\"annotationhint-headline\" style=\"cursor:pointer;cursor:hand;\" onclick=\"switchVisibility(" + ANNOTATIONHINTCONTAINER + ", \'annotationhint-content\', \'annotationhint-headline-link\')\"><a id=\"annotationhint-headline-link\" class=\"";
						if (tmpcontainer.ishidden) {
							stResult +="plusminus";
						} else {
							stResult +="minusplus";
						}
						stResult += "\" href=\"javascript:void(0)\">&nbsp;</a>" + tmpcontainer.headline + "</div>";

						stResult += "<div id=\"annotationhint-content\""
						if (tmpcontainer.ishidden) {
							stResult += " style=\"display:none\"";
						}
						stResult += ">" + tmpcontainer.content + "</div>";

						stDivCount++;
					break;
				default:
					break;
			}
			break;
		case 1:
			switch (tmpcontainer.container)
			{
				case EDITCONTAINER:
					stResult += tmpcontainer.content;
					break;
				default:
					break;
			}
			break;
		case 2:
			switch (tmpcontainer.container)
			{
				case FACTCONTAINER:
					stResult += "<div id=\"freshfacts-headline\">" + tmpcontainer.headline + "</div>";
					stResult += "<div id=\"factsAttributes\">	<div id=\"factsAttributes-headline\">Attributes</div></div>";
					stResult += "<div id=\"factsAttributes-body\"> <div id=\"factsAttributes-bodycontent\">" + tmpcontainer.attributes + "</div></div>";
					stResult += "<div id=\"factsRelations\">	<div id=\"factsRelations-headline\">Relations</div></div>";
					stResult += "<div id=\"factsRelations-body\"> <div id=\"factsRelations-bodycontent\">" + tmpcontainer.relations + "</div></div>";
					stResult += "<div id=\"factsCategories\">	<div id=\"factsCategories-headline\">Categories</div></div>";
					stResult += "<div id=\"factsCategories-body\"> <div id=\"factsCategories-bodycontent\">" + tmpcontainer.categories + "</div></div>";
					stResult += "<div id=\"freshfacts\">" + tmpcontainer.content + "</div>";
					break;
//				case DBGCONTAINER:
//					stResult += "<div id=\"debug-headline\">" + tmpcontainer.headline + "</div>";
//					stResult += "<div id=\"debug-content\">" + tmpcontainer.content + "</div>";
//					break;
				default:
					break;
			}
			break;
		default:
			break;
	}
}

function stFillTabs()
{
	var tabadd = new Array(0,0,0);	// used to decide if tab is shown and added

	// fetch cookie information
	stGetCookieTab();

	stResult += "<div id=\"tabcontainer\">";
	for (var i = 0; i < divcontainerarray.length; i++)
	{
		var tmpcontainer = divcontainerarray[i];
		if (typeof tmpcontainer != 'undefined')
		{
			if (tmpcontainer.container == FACTCONTAINER)
			{
				tabadd[2] = 1;
			}
			if ((tmpcontainer.container == EDITCONTAINER))
			{
				tabadd[1] = 1;
			}
			if ((tmpcontainer.container == HELPCONTAINER
			     || tmpcontainer.container == CATEGORYCONTAINER
			     || tmpcontainer.container == ATTRIBUTECONTAINER
			     || tmpcontainer.container == RELATIONCONTAINER
			     || tmpcontainer.container == RULESCONTAINER
			     || tmpcontainer.container == PROPERTIESCONTAINER
			     || tmpcontainer.container == CBSRCHCONTAINER
			     || tmpcontainer.container == TYPECONTAINER
			     || tmpcontainer.container == SAVEANNOTATIONSCONTAINER
			     || tmpcontainer.container == ANNOTATIONHINTCONTAINER))
			{
				tabadd[0] = 1;
			}
		}
	}

	// count shown tabs - if only 1 tab visible, no tab selector necessary
	var count = 0;
	stNumDefinedContainers = 0;

	for (var j = 0; j < tabadd.length; j++)
		if (tabadd[j] == 1)
		{
			stNumDefinedContainers++;
		}

	if (stInitialTab == 0)
	{
	 	for (var i = tabadd.length; i > -1; i--)
		{
			if (tabadd[i] == 1)
			{
				stTabPosition[count] = i;
				count ++;
			}
		}

		// check if saved cookie tabpos is available -> yes, set to actual pos
		if (count == stCookiePrefTab.length) {
			stTabPosition = stCookiePrefTab;
			stCurrentTabShown = stTabPosition[stNumDefinedContainers-1];
		}
		else
			stCurrentTabShown = 0;
	}


	// init variable
	stInitialTab = 1;

	// more than one tab should be displayed -> put inactive tabs in inactive tab container
	if (stNumDefinedContainers > 1)
	for (var i = 0; i < (stTabPosition.length-1); i++)
	{
		if (tabadd[stTabPosition[i]] == 1)
		{
			switch (stTabPosition[i])
			{
				case 2:
					stResult += "<div id=\"expandable\" style=\"cursor:pointer;cursor:hand;\" onclick=\"stRefreshTab(2)\"><img src=\"" + wgScriptPath + "/skins/ontoskin/plus.gif\" onmouseover=\"(src='" + wgScriptPath + "/skins/ontoskin/plus-act.gif')\" onmouseout=\"(src='" + wgScriptPath + "/skins/ontoskin/plus.gif')\"></div><div id=\"thirdtab\" style=\"cursor:pointer;cursor:hand;\" onclick=\"stRefreshTab(2)\">Facts about this Article</div>";
					break;
				case 1:
					stResult += "<div id=\"expandable\" style=\"cursor:pointer;cursor:hand;\" onclick=\"stRefreshTab(1)\"><img src=\"" + wgScriptPath + "/skins/ontoskin/plus.gif\" onmouseover=\"(src='" + wgScriptPath + "/skins/ontoskin/plus-act.gif')\" onmouseout=\"(src='" + wgScriptPath + "/skins/ontoskin/plus.gif')\"></div><div id=\"secondtab\" style=\"cursor:pointer;cursor:hand;\" onclick=\"stRefreshTab(1)\">Links to Other Pages</div>";
					break;
				case 0:
					stResult += "<div id=\"expandable\" style=\"cursor:pointer;cursor:hand;\" onclick=\"stRefreshTab(0)\"><img src=\"" + wgScriptPath + "/skins/ontoskin/plus.gif\" onmouseover=\"(src='" + wgScriptPath + "/skins/ontoskin/plus-act.gif')\" onmouseout=\"(src='" + wgScriptPath + "/skins/ontoskin/plus.gif')\"></div><div id=\"firsttab\" style=\"cursor:pointer;cursor:hand;\" onclick=\"stRefreshTab(0)\">Tools</div>";
					break;
				default:
					break;
			}
		}
	}
	stResult += "</div>";
}

function stRemoveContainerFromArray(container)
{
	for(i=0;i<stTabPosition.length;i++)
	{
		if(container==stTabPosition[i]) stTabPosition.splice(i, 1);
	}
}

function stRefreshTab(tab)
{
//	stCurrentTabShown = tab;
	// 1. position clicked - rotate tabs
//	alert("before: " + tabposition[0] + [1] + tabposition[2]);
//	if (stTabPosition[0] == tab)
//	{
//		stTabPosition.shift();
//		stTabPosition.push(tab);
//		stCurrentTabShown = tab;
//	}

	// 2. position clicked - only switch current and 2. tab! don't touch 1. tab
	stRemoveContainerFromArray(tab);
	stTabPosition.push(tab);
	stSetCookie(stTabPosition);
	stCurrentTabShown = (stTabPosition[stTabPosition.length-1]);
//	alert("after: " + tabposition[0] + tabposition[1] + tabposition[2]);
   	showMenu();
}


function showMenu(updatecontainer)
{
	// clear stResult
	stResult = "";
	stDivCount = 0;

	// fill tabheader
	stFillTabs();

	// create header of semantic toolbar depending on selected tab...
	if (stNumDefinedContainers > 1)
	{
		switch (stCurrentTabShown)
		{
			case 0:
				stResult += "<div id=\"expandable\"><img src=\"" + wgScriptPath + "/skins/ontoskin/minus.gif\"></div><div id=\"firsttab\">Tools</div>";
				break;
			case 1:
				stResult += "<div id=\"expandable\"><img src=\"" + wgScriptPath + "/skins/ontoskin/minus.gif\"></div><div id=\"secondtab\">Links to Other Pages</div>";
				break;
			case 2:
				stResult += "<div id=\"expandable\"><img src=\"" + wgScriptPath + "/skins/ontoskin/minus.gif\"></div><div id=\"thirdtab\">Facts about this Article</div>";
				break;
			default:
				break;
		}
		stResult +=	"</div>";
	}
	stResult +=	"<div id=\"semtoolbar\">"

	// check current mode if toolbar should be shown...
	switch (wgAction)
	{
		case "view":
			/**if (wgNamespaceNumber == '102')
			{
				var onto = document.getElementById("ontomenuanchor");
				if (typeof onto == 'undefined')
					break;
				// build result

				for (var i = 0; i < divcontainerarray.length; i++)
				{
					var tmpcontainer = divcontainerarray[i];
					if (typeof tmpcontainer != 'undefined')
					{
						stFillContainers(tmpcontainer);
					}
				}
				if (onto)
					onto.innerHTML = stResult + "</div>";
			}*/
			if (wgCanonicalSpecialPageName == 'Search' && wgCanonicalNamespace == 'Special') {
				var onto = document.getElementById("ontomenuanchor");
				if (typeof onto == 'undefined')
					break;
				// build result
				if (!updatecontainer) {
					if (divcontainerarray[COMBINEDSEARCHCONTAINER]) {
						stFillContainers(divcontainerarray[HELPCONTAINER]);
						stFillContainers(divcontainerarray[COMBINEDSEARCHCONTAINER]);
					}
					if (onto) {
						onto.innerHTML = stResult + "</div>";
						$("ontomenuanchor").setStyle({position: 'absolute'});
					}
				} else {
					updtContainer(dicvontainerarray[updatecontainer]);
				}
				stSetMaxHeight();
			}
			break;
		case "edit":
			var onto = document.getElementById("ontomenuanchor");
			if (typeof onto == 'undefined')
				break;
			// build result

			if (updatecontainer) {
				updtContainer(divcontainerarray[updatecontainer]);
			} else {
				for (var i = 0; i < divcontainerarray.length; i++)
				{
					var tmpcontainer = divcontainerarray[i];
					if (typeof tmpcontainer != 'undefined')
					{
						stFillContainers(tmpcontainer);
					}
				}
				if (onto) {
					onto.innerHTML = stResult + "</div>";
					$("ontomenuanchor").setStyle({position: 'absolute'});
				}
			}
			stSetMaxHeight();
			break;
		default:
			break;
	}
}

function switchVisibility(container, tohide, headlinelink)
{
	if (divcontainerarray[container].ishidden)
	{
		if (container == HELPCONTAINER) {
			stSetHelpCookie(1);
		}
		divcontainerarray[container].ishidden = false;
		document.getElementById(headlinelink).className='minusplus';
		document.getElementById(tohide).style.display = '';
	} else {
		if (container == HELPCONTAINER) {
			stSetHelpCookie(0);
		}
		divcontainerarray[container].ishidden = true;
		document.getElementById(headlinelink).className='plusminus';
		document.getElementById(tohide).style.display = 'none';
	}
	stSetMaxHeight();
}

function smw_togglemenuvisibility(){
	//Check if menu is currently hidden or shown
	if( $('ontomenuanchor').visible() ){
		//Hide menu
		$('ontomenuanchor').hide();
		//Resize normal content to max minus a space of 15 pixel
		$('innercontent').setStyle({ right: '15px'});
		$('innercontent').setStyle({ width: 'auto'});
	} else {
		//Show menu
		$('ontomenuanchor').show();
		smw_settoolbar(0.85);
		//Calculate the space needed for the menu
//		var rightspace = $('ontomenuanchor').getWidth() + 30;
		//Resize the normal Content so the menu fits right to it
//		$('innercontent').setStyle({ right: rightspace + 'px' });
	}

}

function smw_settoolbar(ratio){

	//$('content').setStyle({ height: getWindowHeight() - 110 + 'px' });
	$('content').setStyle({ width: getWindowWidth() - 200 + 'px'});
	//$('innercontent').setStyle({ height: getWindowHeight() - 110 + 'px'});
	$('innercontent').setStyle({ width: getWindowWidth() - 420 + 'px'});
	$('ontomenuanchor').setStyle({ height: getWindowHeight() -110 + 'px' });
}

function stSetMaxHeight() {

	if (stCurrentTabShown == 0) {
		if (   typeof divcontainerarray[CATEGORYCONTAINER] != 'undefined'
		    && typeof divcontainerarray[RELATIONCONTAINER] != 'undefined') {
			var count = 0;

			// calculate maxheight
			var maxheight = getWindowHeight()-100;

			if (stNumDefinedContainers > 1)
				maxheight -= (stNumDefinedContainers*30)+20;
			else
				maxheight -= 70;

			if (document.getElementById("help"))
				maxheight -= document.getElementById("help").scrollHeight+20;
			if (document.getElementById("cbsrch"))
				maxheight -= document.getElementById("cbsrch").scrollHeight+20;

			// count containers which are finally treated equally and get the same amount of free space
			if (!divcontainerarray[CATEGORYCONTAINER].ishidden)
				count++;
			if (!divcontainerarray[RELATIONCONTAINER].ishidden)
				count++;
//			if (!divcontainerarray[ATTRIBUTECONTAINER].ishidden)
//				count++;
//			if (!divcontainerarray[PROPERTIESCONTAINER].ishidden)
//				count++;

			if( document.getElementById("category-content")) {
				$("category-content").setStyle({maxHeight: maxheight/(count) + 'px'});
			}
//			if( document.getElementById("attribute-content")) {
//				$("attribute-content").setStyle({maxHeight: maxheight/(count) + 'px'});
//			}
			if( document.getElementById("relation-content")) {
				$("relation-content").setStyle({maxHeight: maxheight/(count) + 'px'});
			}
//			if( document.getElementById("properties-content")) {
//				$("properties-content").setStyle({maxHeight: maxheight/(count) + 'px'});
//			}
		}
	} else if (stCurrentTabShown == 1) {
		if( document.getElementById("edit")){
			$("edit").setStyle({maxHeight: (getWindowHeight()-150) + 'px'});
		}
	}
}

function getWindowHeight()
{
    //Common for Opera&Gecko
    if (window.innerHeight) {
        return window.innerHeight;
    } else {
	//Common for IE
        if (window.document.documentElement && window.document.documentElement.clientHeight)
        {
            return window.document.documentElement.clientHeight;
        } else {
		//Fallback solution for IE, does not always return usable values
		if (document.body && document.body.offsetHeight) {
        		return win.document.body.offsetHeight;
	        }
		return 0;
	}
    }
}

function getWindowWidth()
{
    //Common for Opera&Gecko
    if (window.innerWidth) {
        return window.innerWidth;
    } else {
	//Common for IE
        if (window.document.documentElement && window.document.documentElement.clientWidth)
        {
            return window.document.documentElement.clientWidth;
        } else {
		//Fallback solution for IE, does not always return usable values
		if (document.body && document.body.offsetWidth) {
        		return win.document.body.offsetWidth;
	        }
		return 0;
	}
	}
}
