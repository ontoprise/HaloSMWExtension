/*  Copyright 2008, ontoprise GmbH
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

/**
 * This file provides methods for the special page define wiki web service
 * description
 * 
 * @author Ingo Steinbauer
 * 
 */

var DefineWebServiceSpecial = Class.create();

DefineWebServiceSpecial.prototype = {

	initialize : function() {
		this.step = "step1";
	},

	/**
	 * called when the user finishes step 1 define uri
	 * 
	 * @return
	 */
	processStep1 : function() {
		if (this.step != "step1") {
			check = confirm("If you proceed, all input you allready gave in the subsequent steps will be lost!");
			if (check == false) {
				return;
			}
		}
		this.step = "step2";
		var uri = document.getElementById("step1-uri").value;
		sajax_do_call("smwf_ws_processStep1", [ uri ],
				this.processStep1CallBack.bind(this));
	},

	/**
	 * callback-method for the ajax-call of step 1 this method initializes the
	 * gui for step 2 choose methods
	 * 
	 * @param request
	 * 
	 */
	processStep1CallBack : function(request) {
		var wsMethods = request.responseText.split(";");
		if (wsMethods[0] != "todo:handle exceptions") {
			this.step = "step1";
			document.getElementById("errors").style.display = "block";
			document.getElementById("step1-error").style.display = "block";
			document.getElementById("step2a-error").style.display = "none";
			document.getElementById("step2b-error").style.display = "none";
			document.getElementById("step3-error").style.display = "none";
			document.getElementById("step4-error").style.display = "none";
			document.getElementById("step5-error").style.display = "none";
			document.getElementById("step6-error").style.display = "none";
			document.getElementById("step6b-error").style.display = "none";

			document.getElementById("step2").style.display = "none";
			document.getElementById("step3").style.display = "none";
			document.getElementById("step4").style.display = "none";
			document.getElementById("step5").style.display = "none";
			document.getElementById("step6").style.display = "none";

			document.getElementById("step1-help").style.display = "block";
			document.getElementById("step2-help").style.display = "none";
			document.getElementById("step3-help").style.display = "none";
			document.getElementById("step4-help").style.display = "none";
			document.getElementById("step5-help").style.display = "none";
			document.getElementById("step6-help").style.display = "none";

			document.getElementById("step1-img").style.visibility = "visible";
			document.getElementById("step2-img").style.visibility = "hidden";
			document.getElementById("step3-img").style.visibility = "hidden";
			document.getElementById("step4-img").style.visibility = "hidden";
			document.getElementById("step5-img").style.visibility = "hidden";
			document.getElementById("step6-img").style.visibility = "hidden";
		} else {
			wsMethods.shift();
			document.getElementById("errors").style.display = "none";
			document.getElementById("step1-error").style.display = "none";

			// clear the widget for step 2
			var existingOptions = document.getElementById("step2-methods")
					.cloneNode(false);
			document.getElementById("step2-methods").id = "old-step2-methods";
			document.getElementById("old-step2-methods").parentNode
					.insertBefore(existingOptions, document
							.getElementById("old-step2-methods"));
			document.getElementById("old-step2-methods").parentNode
					.removeChild(document.getElementById("old-step2-methods"));
			existingOptions.id = "step2-methods";

			// fill the widget for step2 with content

			for (i = 0; i < wsMethods.length; i++) {
				var option = document.createElement("option");
				var mName = document.createTextNode(wsMethods[i]);
				option.appendChild(mName);
				document.getElementById("step2-methods").appendChild(option);
			}

			// hide or display widgets of other steps
			document.getElementById("step2").style.display = "block";
			document.getElementById("step3").style.display = "none";
			document.getElementById("step4").style.display = "none";
			document.getElementById("step5").style.display = "none";
			document.getElementById("step6").style.display = "none";
			;
			document.getElementById("menue-step2").style.fontWeight = "bold";
			document.getElementById("menue-step3").style.fontWeight = "normal";
			document.getElementById("menue-step4").style.fontWeight = "normal";
			document.getElementById("menue-step5").style.fontWeight = "normal";
			document.getElementById("menue-step6").style.fontWeight = "normal";
			document.getElementById("step1-help").style.display = "none";
			document.getElementById("step3-help").style.display = "none";
			document.getElementById("step4-help").style.display = "none";
			document.getElementById("step5-help").style.display = "none";
			document.getElementById("step6-help").style.display = "none";
			document.getElementById("step2-help").style.display = "block";
			document.getElementById("step1-img").style.visibility = "hidden";
			document.getElementById("step3-img").style.visibility = "hidden";
			document.getElementById("step4-img").style.visibility = "hidden";
			document.getElementById("step5-img").style.visibility = "hidden";
			document.getElementById("step6-img").style.visibility = "hidden";
			document.getElementById("step2-img").style.visibility = "visible";
		}
	},

	/**
	 * called when the user finishes step 2 choose method
	 * 
	 */
	processStep2 : function() {
		if (this.step != "step2") {
			check = confirm("If you proceed, all input you allready gave in the subsequent steps will be lost!");
			if (check == false) {
				return;
			}
		}

		this.step = "step3+";
		var method = document.getElementById("step2-methods").value;
		var uri = document.getElementById("step1-uri").value;
		sajax_do_call("smwf_ws_processStep2", [ uri, method ],
				this.processStep2CallBack.bind(this));
	},

	/**
	 * callback-method for the ajax-call of step 2 this method initializes the
	 * gui for step 3 specify parameters
	 * 
	 * @param request
	 * 
	 */
	processStep2CallBack : function(request) {
		var wsParameters = request.responseText.split(";");
		if(wsParameters[0] == "todo:handle noparams"){
			this.processStep3();
			return;
		}
		var overflow = false;

		for (i = 0; i < wsParameters.length; i++) {
			if (wsParameters[i].indexOf("##overflow##") > 0) {
				overflow = true;
			}
		}

		if (wsParameters[0] != "todo:handle exceptions" || overflow) {
			this.step = "step2";
			document.getElementById("step3").style.display = "none";
			document.getElementById("step4").style.display = "none";
			document.getElementById("step5").style.display = "none";
			document.getElementById("step6").style.display = "none";

			document.getElementById("errors").style.display = "block";
			if (overflow) {
				document.getElementById("step2b-error").style.display = "block";
			} else {
				document.getElementById("step2a-error").style.display = "block";
			}
			document.getElementById("step3-error").style.display = "none";
			document.getElementById("step4-error").style.display = "none";
			document.getElementById("step5-error").style.display = "none";
			document.getElementById("step6-error").style.display = "none";
			document.getElementById("step6b-error").style.display = "none";
			
			document.getElementById("step2-help").style.display = "block";
			document.getElementById("step3-help").style.display = "none";
			document.getElementById("step4-help").style.display = "none";
			document.getElementById("step5-help").style.display = "none";
			document.getElementById("step6-help").style.display = "none";

			document.getElementById("step2-img").style.visibility = "visible";
			document.getElementById("step3-img").style.visibility = "hidden";
			document.getElementById("step4-img").style.visibility = "hidden";
			document.getElementById("step5-img").style.visibility = "hidden";
			document.getElementById("step6-img").style.visibility = "hidden";
		} else {
			wsParameters.shift();
			// clear widgets of step 3
			var okButton = document.getElementById("step3-ok").cloneNode(true);
			document.getElementById("step3-ok").id = "old-step3-ok";
			document.getElementById("old-step3-ok").parentNode
					.removeChild(document.getElementById("old-step3-ok"));
			var tempHead = document.getElementById("step3-parameters").childNodes[0].childNodes[0]
					.cloneNode(true);
			var tempTable = document.getElementById("step3-parameters").childNodes[0]
					.cloneNode(false);
			document.getElementById("step3-parameters").removeChild(
					document.getElementById("step3-parameters").childNodes[0]);
			document.getElementById("step3-parameters").appendChild(tempTable);
			document.getElementById("step3-parameters").childNodes[0]
					.appendChild(tempHead);

			// fill widgets for step 3 with content

			for (i = 0; i < wsParameters.length; i++) {
				var paramRow = document.createElement("tr");
				document.getElementById("step3-parameters").childNodes[0]
						.appendChild(paramRow);

				var paramTD0 = document.createElement("td");
				paramRow.appendChild(paramTD0);

				var paramPath = document.createElement("span");
				var arraySteps = wsParameters[i].split("[");
				var ppText = "";
				for ( var k = 0; k < arraySteps.length; k++) {
					var paramPathText;
					if (k != arraySteps.length - 1) {
						paramPathText = document.createTextNode(arraySteps[k]
								+ "[0");
					} else {
						paramPathText = document.createTextNode(arraySteps[k]);
					}
					paramPath.appendChild(paramPathText);
					if (k != arraySteps.length - 1) {
						var addButton = document.createElement("span");
						addButton.style.cursor = "pointer";
						var addButtonIMG = document.createElement("img");
						addButtonIMG.src = "../extensions/SMWHalo/skins/webservices/Add.png";
						addButtonIMG.alt = "Please click here to generate Aliases";
						addButton.appendChild(addButtonIMG);
						var addButtonOnClick = document
								.createAttribute("onclick");
						addButtonOnClick.value = "webServiceSpecial.addParameter("
								+ i + ", " + k + ")";
						addButton.setAttributeNode(addButtonOnClick);
						paramPath.appendChild(addButton);
					}
				}
				paramTD0.appendChild(paramPath);
				paramPath.id = "s3-path" + i;
				paramPath.className = "OuterLeftIndent";

				var paramTD1 = document.createElement("td");
				paramRow.appendChild(paramTD1);

				var aliasInput = document.createElement("input");
				aliasInput.id = "s3-alias" + i;
				aliasInput.size = "15";
				aliasInput.maxLength = "40";
				paramTD1.appendChild(aliasInput);

				var paramTD2 = document.createElement("td");
				paramRow.appendChild(paramTD2);

				var optionalRadio1 = document.createElement("input");
				optionalRadio1.id = "s3-optional-true" + i;
				optionalRadio1.name = "s3-optional-radio" + i;
				optionalRadio1.type = "radio";
				optionalRadio1.value = "yes";
				paramTD2.appendChild(optionalRadio1);

				var optionalRadio1Span = document.createElement("span");
				var optionalRadio1TextY = document.createTextNode("Yes");
				optionalRadio1Span.appendChild(optionalRadio1TextY);
				paramTD2.appendChild(optionalRadio1Span);

				var optionalRadio2 = document.createElement("input");
				optionalRadio2.checked = true;
				optionalRadio2.id = "s3-optional-false" + i;
				optionalRadio2.name = "s3-optional-radio" + i;
				optionalRadio2.type = "radio";
				optionalRadio2.value = "false";
				paramTD2.appendChild(optionalRadio2);

				var optionalRadio2Span = document.createElement("span");
				var optionalRadio2TextN = document.createTextNode("No");
				optionalRadio2Span.appendChild(optionalRadio2TextN);
				paramTD2.appendChild(optionalRadio2Span);

				var paramTD3 = document.createElement("td");
				paramRow.appendChild(paramTD3);

				var defaultInput = document.createElement("input");
				defaultInput.id = "s3-default" + i;
				defaultInput.size = "15";
				defaultInput.maxLength = "40";
				paramTD3.appendChild(defaultInput);

				var paramTD4 = document.createElement("td");
				paramRow.appendChild(paramTD4);

				if (i == wsParameters.length - 1) {
					paramTD4.appendChild(okButton);
				}
			}

			// hide or display widgets of other steps
			document.getElementById("step3").style.display = "block";
			document.getElementById("step4").style.display = "none";
			document.getElementById("step5").style.display = "none";
			document.getElementById("step6").style.display = "none";

			document.getElementById("menue-step3").style.fontWeight = "bold";
			document.getElementById("menue-step4").style.fontWeight = "normal";
			document.getElementById("menue-step5").style.fontWeight = "normal";
			document.getElementById("menue-step6").style.fontWeight = "normal";
			document.getElementById("step2-help").style.display = "none";
			document.getElementById("step4-help").style.display = "none";
			document.getElementById("step5-help").style.display = "none";
			document.getElementById("step6-help").style.display = "none";
			document.getElementById("step3-help").style.display = "block";
			document.getElementById("step2-img").style.visibility = "hidden";
			document.getElementById("step4-img").style.visibility = "hidden";
			document.getElementById("step5-img").style.visibility = "hidden";
			document.getElementById("step6-img").style.visibility = "hidden";
			document.getElementById("step3-img").style.visibility = "visible";
		}
	},

	/**
	 * called when the user finishes step 3 define parameters
	 * 
	 * @return
	 */
	processStep3 : function() {
		this.generateParameterAliases();
		var method = document.getElementById("step2-methods").value;
		var uri = document.getElementById("step1-uri").value;
		var parameters = "";

		sajax_do_call("smwf_ws_processStep3", [ uri, method ],
				this.processStep3CallBack.bind(this));
	},

	/**
	 * callback-method for the ajax-call of step 3 this method initializes the
	 * gui for step 4 specify result aliases
	 * 
	 * @param request
	 * 
	 */
	processStep3CallBack : function(request) {
		var wsResults = request.responseText.split(";");
		if (wsResults[0] != "todo:handle exceptions") {
			document.getElementById("errors").style.display = "block";
			document.getElementById("step3-error").style.display = "block";
			document.getElementById("step4-error").style.display = "none";
			document.getElementById("step5-error").style.display = "none";
			document.getElementById("step6-error").style.display = "none";
			document.getElementById("step6b-error").style.display = "none";
			
			document.getElementById("step3").style.display = "block";
			document.getElementById("step4").style.display = "none";
			document.getElementById("step5").style.display = "none";
			document.getElementById("step6").style.display = "none";

			document.getElementById("step3-help").style.display = "block";
			document.getElementById("step4-help").style.display = "none";
			document.getElementById("step5-help").style.display = "none";
			document.getElementById("step6-help").style.display = "none";

			document.getElementById("step3-img").style.visibility = "visible";
			document.getElementById("step4-img").style.visibility = "hidden";
			document.getElementById("step5-img").style.visibility = "hidden";
			document.getElementById("step6-img").style.visibility = "hidden";
		} else {
			wsResults.shift();
			// clear widgets of step 4
			var okButton = document.getElementById("step4-ok").cloneNode(true);
			document.getElementById("step4-ok").id = "old-step4-ok";
			document.getElementById("old-step4-ok").parentNode
					.removeChild(document.getElementById("old-step4-ok"));

			var tempHead = document.getElementById("step4-results").childNodes[0].childNodes[0]
					.cloneNode(true);
			var tempTable = document.getElementById("step4-results").childNodes[0]
					.cloneNode(false);
			document.getElementById("step4-results").removeChild(
					document.getElementById("step4-results").childNodes[0]);
			document.getElementById("step4-results").appendChild(tempTable);
			document.getElementById("step4-results").childNodes[0]
					.appendChild(tempHead);

			// fill the widgets of step4 with content

			for (i = 0; i < wsResults.length; i++) {
				var resultRow = document.createElement("tr");
				document.getElementById("step4-results").childNodes[0]
						.appendChild(resultRow);

				var resultTD0 = document.createElement("td");
				resultRow.appendChild(resultTD0);

				var resultTD1 = document.createElement("td");
				resultRow.appendChild(resultTD1);

				var resultPath = document.createElement("span");

				var dotSteps = wsResults[i].split(".");
				if (wsResults[i].length > 0) {
					wsResults[i] = "result." + wsResults[i];
				} else {
					wsResults[i] = "result" + wsResults[i];
				}

				var arraySteps = wsResults[i].split("[");
				var ppText = "";
				for ( var k = 0; k < arraySteps.length; k++) {
					var paramPathText;
					if (k == 0 && arraySteps.length > 1) {
						var addButton = document.createElement("span");
						addButton.className = "OuterLeftIndent";
						addButton.style.cursor = "pointer";
						var addButtonIMG = document.createElement("img");
						addButtonIMG.src = "../extensions/SMWHalo/skins/webservices/Add.png";
						addButtonIMG.alt = "Please click here to generate Aliases";
						addButton.appendChild(addButtonIMG);
						var addButtonOnClick = document
								.createAttribute("onclick");
						addButtonOnClick.value = "webServiceSpecial.addResultPart("
								+ i + ")";
						addButton.setAttributeNode(addButtonOnClick);
						resultTD0.appendChild(addButton);
					} else {
						var spacer = document.createElement("span");
						spacer.className = "OuterLeftIndent";
						var spacerText = document.createTextNode("");
						spacer.appendChild(spacerText);
						resultTD0.appendChild(spacer);
					}

					if (k != arraySteps.length - 1) {

						paramPathText = document.createTextNode(arraySteps[k]
								+ "[");

						resultPath.appendChild(paramPathText);

						var pathIndexInput = document.createElement("input");
						pathIndexInput.type = "text";
						pathIndexInput.size = "1";
						pathIndexInput.maxLength = "10";
						pathIndexInput.value = "";
						resultPath.appendChild(pathIndexInput);
					} else {
						paramPathText = document.createTextNode(arraySteps[k]);
						resultPath.appendChild(paramPathText);
					}

				}

				resultPath.id = "s4-path" + i;
				resultTD1.appendChild(resultPath);

				var resultTD2 = document.createElement("td");
				resultRow.appendChild(resultTD2);

				var aliasInput = document.createElement("input");
				aliasInput.id = "s4-alias" + i;
				aliasInput.size = "15";
				aliasInput.maxLength = "40";
				resultTD2.appendChild(aliasInput);

				var resultTD3 = document.createElement("td");
				resultRow.appendChild(resultTD3);

				if (i == wsResults.length - 1) {
					resultTD3.appendChild(okButton);
				}
			}
			// hide or display widgets of other steps
			document.getElementById("step4").style.display = "block";

			document.getElementById("menue-step4").style.fontWeight = "bold";
			document.getElementById("step3-help").style.display = "none";
			document.getElementById("step4-help").style.display = "block";
			document.getElementById("step3-img").style.visibility = "hidden";
			document.getElementById("step4-img").style.visibility = "visible";
		}
	},

	/**
	 * called when the user finishes step 4 define results initialises the gui
	 * for step-5 define update policy
	 * 
	 * @return
	 */
	processStep4 : function() {
		// hide or display widgets of other steps
		this.generateResultAliases();
		document.getElementById("step5").style.display = "block";

		document.getElementById("menue-step5").style.fontWeight = "bold";
		document.getElementById("step4-help").style.display = "none";
		document.getElementById("step5-help").style.display = "block";
		document.getElementById("step4-img").style.visibility = "hidden";
		document.getElementById("step5-img").style.visibility = "visible";

		document.getElementById("step5-display-once").checked = true;
		document.getElementById("step5-display-days").value = "";
		document.getElementById("step5-display-hours").value = "";
		document.getElementById("step5-display-minutes").value = "";

		document.getElementById("step5-query-once").checked = true;
		document.getElementById("step5-query-days").value = "";
		document.getElementById("step5-query-hours").value = "";
		document.getElementById("step5-query-minutes").value = "";
		document.getElementById("step5-delay").value = "";

		document.getElementById("step5-spanoflife").value = "";
		document.getElementById("step5-expires-yes").checked = true;
	},

	/**
	 * called after step 5 specify query policy this method initializes the gui
	 * for step 6 specify wwsd-name
	 * 
	 * @param request
	 * 
	 */
	processStep5 : function() {
		// hide or display widgets of other steps
		document.getElementById("step6").style.display = "block";

		document.getElementById("menue-step6").style.fontWeight = "bold";
		document.getElementById("step5-help").style.display = "none";
		document.getElementById("step6-help").style.display = "block";
		document.getElementById("step5-img").style.visibility = "hidden";
		document.getElementById("step6-img").style.visibility = "visible";
		document.getElementById("step6-name").value = "";
	},

	/**
	 * called after step 6 specify ws-name this method constructs the wwsd
	 */
	processStep6 : function() {
		if (document.getElementById("step6-name").value.length > 0) {
			document.getElementById("errors").style.display = "none";
			document.getElementById("step6-error").style.display = "none";
			document.getElementById("step6b-error").style.display = "none";
			var result = "<WebService>\n";

			var uri = document.getElementById("step1-uri").value;
			result += "<uri name=\"" + uri + "\" />\n";

			result += "<protocol>SOAP</protocol>\n";

			var method = document.getElementById("step2-methods").value;
			result += "<method name=\"" + method + "\" />\n";

			for ( var i = 0; i < document.getElementById("step3-parameters").childNodes[0].childNodes.length - 1; i++) {
				result += "<parameter name=\""
						+ document.getElementById("s3-alias" + i).value + "\" ";
				var optional = document.getElementById("s3-optional-true" + i).checked;
				result += " optional=\"" + optional + "\" ";
				if (document.getElementById("s3-default" + i).value != "") {
					if (document.getElementById("s3-default" + i).value != "") {
						result += " defaultValue=\""
								+ document.getElementById("s3-default" + i).value
								+ "\" ";
					}
				}
				var path = "";
				for ( var k = 0; k < document
						.getElementById("step3-parameters").childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes.length; k += 2) {
					var pathStep = document.getElementById("step3-parameters").childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k].nodeValue;
					if (k == document.getElementById("step3-parameters").childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes.length - 1) {
						if (pathStep.lastIndexOf("(") > 0) {
							pathStep = pathStep.substr(0, pathStep
									.lastIndexOf("(") - 1);
						}
					}
					path += pathStep;
				}
				result += " path=\"" + path + "\" />\n";
			}

			result += "<result name=\"result\" >\n";

			var results = document.getElementById("step4-results").childNodes[0].childNodes;
			for (i = 0; i < results.length - 1; i++) {
				result += "<part name=\""
						+ document.getElementById("s4-alias" + i).value + "\" ";

				var rPath = "";
				for (k = 0; k < document.getElementById("step4-results").childNodes[0].childNodes[i + 1].childNodes[1].firstChild.childNodes.length; k += 2) {
					var rPathStep = document.getElementById("step4-results").childNodes[0].childNodes[i + 1].childNodes[1].firstChild.childNodes[k].nodeValue;
					if (k > 0) {
						rPath += document.getElementById("step4-results").childNodes[0].childNodes[i + 1].childNodes[1].firstChild.childNodes[k - 1].value;
					}
					if (k == document.getElementById("step4-results").childNodes[0].childNodes[i + 1].childNodes[1].firstChild.childNodes.length - 1) {
						if (rPathStep.lastIndexOf("(") > 0) {
							rPathStep = rPathStep.substr(0, rPathStep
									.lastIndexOf("(") - 1);
						}
					}
					if (k == 0) {
						tPath = rPathStep;
						if (tPath.indexOf("result.") == 0) {
							tPath = tPath.substr(7, tPath.length);
						} else {
							tPath = tPath.substr(6, tPath.length);
						}
						rPathStep = tPath;
					}
					rPath += rPathStep;
				}
				result += " path=\"" + rPath + "\" />\n";

			}
			result += "</result>\n";

			result += "<displayPolicy>\n"
			if (document.getElementById("step5-display-once").checked == true) {
				result += "<once/>\n";
			} else {
				result += "<maxAge value=\"";
				var minutes = 0;
				minutes += document.getElementById("step5-display-days").value * 60 * 24;
				minutes += document.getElementById("step5-display-hours").value * 60;
				minutes += document.getElementById("step5-display-minutes").value * 1;
				result += minutes;
				result += "\"></maxAge>\n";
			}
			result += "</displayPolicy>\n"

			result += "<queryPolicy>\n"
			if (document.getElementById("step5-query-once").checked == true) {
				result += "<once/>\n";
			} else {
				result += "<maxAge value=\"";
				minutes = 0;
				minutes += document.getElementById("step5-query-days").value * 60 * 24;
				minutes += document.getElementById("step5-query-hours").value * 60;
				minutes += document.getElementById("step5-query-minutes").value * 1;
				result += minutes;
				result += "\"></maxAge>\n";
			}
			var delay = document.getElementById("step5-delay").value;
			if (delay.length == 0) {
				delay = 0;
			}
			result += "<delay value=\"" + delay + "\"/>\n";
			result += "</queryPolicy>\n"
			result += "<spanOfLife value=\""
					+ (0 + document.getElementById("step5-spanoflife").value * 1);
			if (document.getElementById("step5-expires-yes").checked) {
				result += "\" expiresAfterUpdate=\"true\" />\n";
			} else {
				result += "\" expiresAfterUpdate=\"false\" />\n";
			}
			result += "</WebService>";
			this.wwsd = result;
			var wsName = document.getElementById("step6-name").value;

			// the three additional "#" tell the ws-syntax processor not to
			// process
			// this ws-syntax
			var wsSyntax = "\n== Syntax for using the WWSD in an article==";
			wsSyntax += "\n<nowiki>{{#ws: "
					+ document.getElementById("step6-name").value
					+ "</nowiki>\n";
			for (i = 0; i < document.getElementById("step3-parameters").childNodes[0].childNodes.length - 1; i++) {
				wsSyntax += "| "
						+ document.getElementById("s3-alias" + i).value
						+ " = [Please enter a value here]\n";
			}
			results = document.getElementById("step4-results").childNodes[0].childNodes;
			for (i = 0; i < results.length - 1; i++) {
				wsSyntax += "| ?result."
						+ document.getElementById("s4-alias" + i).value + "\n";
			}

			wsSyntax += "}}";

			this.wsSyntax = wsSyntax;

			sajax_do_call("smwf_om_ExistsArticle", [ "webservice:" + wsName ],
					this.processStep6CallBack.bind(this));

		} else {
			document.getElementById("errors").style.display = "block";
			document.getElementById("step6-error").style.display = "block";
			document.getElementById("step6b-error").style.display = "none";
		}

	},

	processStep6CallBack : function(request) {
		if (request.responseText == "false") {
			var wsName = document.getElementById("step6-name").value;
			sajax_do_call("smwf_om_EditArticle", [ "webservice:" + wsName,
					this.wwsd + this.wsSyntax, "" ], this.processStep6CallBack1
					.bind(this));
		} else {
			document.getElementById("errors").style.display = "block";
			document.getElementById("step6b-error").style.display = "block";
			document.getElementById("step6-error").style.display = "none";
		}
	},

	/**
	 * callback method for step 6Callback
	 * 
	 */
	processStep6CallBack1 : function(request) {
		var wsName = document.getElementById("step6-name").value;
		sajax_do_call("smwf_ws_processStep6", [ wsName, this.wwsd ],
				this.processStep6CallBack2.bind(this));

	},

	/**
	 * callback method for step-6
	 * 
	 */
	processStep6CallBack2 : function(request) {
		var wsName = document.getElementById("step6-name").value;
		sajax_do_call("smwf_om_TouchArticle", [ "webservice:" + wsName ],
				this.processStep6CallBack3.bind(this));
	},

	/**
	 * callback method for step-6 this method initializes the gui for step which
	 * provides an example for the #ws-syntax
	 * 
	 */
	processStep6CallBack3 : function(request) {
		var container = document.getElementById("step7-container").cloneNode(
				false);
		document.getElementById("step7-container").id = "old-step7-container";
		document.getElementById("old-step7-container").parentNode.insertBefore(
				container, document.getElementById("old-step7-container"));
		document.getElementById("old-step7-container").parentNode
				.removeChild(document.getElementById("old-step7-container"));

		var wsNameText = document.createTextNode(document
				.getElementById("step6-name").value);
		document.getElementById("step7-name").appendChild(wsNameText);

		var rowDiv = document.createElement("div");
		var rowText = document.createTextNode("{{#ws: "
				+ document.getElementById("step6-name").value);
		rowDiv.appendChild(rowText);
		document.getElementById("step7-container").appendChild(rowDiv);

		for ( var i = 0; i < document.getElementById("step3-parameters").childNodes[0].childNodes.length - 1; i++) {
			rowDiv = document.createElement("div");
			rowDiv.className = "OuterLeftIndent";
			rowText = document.createTextNode("| "
					+ document.getElementById("s3-alias" + i).value
					+ " = [Please enter a value here]");
			rowDiv.appendChild(rowText);
			document.getElementById("step7-container").appendChild(rowDiv);
		}

		var results = document.getElementById("step4-results").childNodes[0].childNodes;
		for (i = 0; i < results.length - 1; i++) {
			rowDiv = document.createElement("div");
			rowDiv.className = "OuterLeftIndent";
			rowText = document.createTextNode("| ?result."
					+ document.getElementById("s4-alias" + i).value);
			rowDiv.appendChild(rowText);
			document.getElementById("step7-container").appendChild(rowDiv);
		}

		rowDiv = document.createElement("div");
		rowText = document.createTextNode("}}");
		rowDiv.appendChild(rowText);
		document.getElementById("step7-container").appendChild(rowDiv);

		document.getElementById("step7").style.display = "block";
		document.getElementById("step1").style.display = "none";
		document.getElementById("step2").style.display = "none";
		document.getElementById("step3").style.display = "none";
		document.getElementById("step4").style.display = "none";
		document.getElementById("step5").style.display = "none";
		document.getElementById("step6").style.display = "none";
		document.getElementById("step6-help").style.display = "none";
		document.getElementById("menue").style.display = "none";
		document.getElementById("help").style.display = "none";
	},

	/**
	 * called after step 7 this method initializes the gui for step 1
	 * 
	 */
	processStep7 : function(request) {
		this.step = "step1";
		document.getElementById("step1-img").style.visibility = "visible";
		document.getElementById("step1-help").style.display = "block";
		document.getElementById("step7").style.display = "none";
		document.getElementById("menue").style.display = "block";
		document.getElementById("menue-step2").style.fontWeight = "normal";
		document.getElementById("menue-step3").style.fontWeight = "normal";
		document.getElementById("menue-step4").style.fontWeight = "normal";
		document.getElementById("menue-step5").style.fontWeight = "normal";
		document.getElementById("menue-step6").style.fontWeight = "normal";
		document.getElementById("help").style.display = "block";
		document.getElementById("step1").style.display = "block";
		document.getElementById("step1-uri").Value = "";
	},

	/**
	 * this method is responsible for automatic alias-creation in step 3 specify
	 * parameters
	 * 
	 */
	generateParameterAliases : function() {
		var paramCount = document.getElementById("step3-parameters").childNodes[0].childNodes.length - 1;

		var aliases = new Array();

		for (i = 0; i < paramCount; i++) {
			var alias = document.getElementById("s3-alias" + i).value;
			if (alias.length == 0) {
				alias = document.getElementById("s3-path" + i).childNodes[document
						.getElementById("s3-path" + i).childNodes.length - 1].nodeValue;
				if (alias == "]") {
					alias = document.getElementById("s3-path" + i).childNodes[document
							.getElementById("s3-path" + i).childNodes.length - 3].nodeValue;
					alias = alias.substr(0, alias.length - 2);
				}
				var openBracketPos = alias.lastIndexOf("(");
				if (openBracketPos > 0) {
					alias = alias.substr(0, openBracketPos - 1);
				}
				var dotPos = alias.lastIndexOf(".");
				alias = alias.substr(dotPos + 1);
			}

			for ( var k = 0; k < aliases.length; k++) {
				var endPosA = alias.length;
				if (alias.lastIndexOf("-") != -1) {
					endPosA = alias.lastIndexOf("-");
				}

				var endPosB = aliases[k].length;
				if (aliases[k].lastIndexOf("-") != -1) {
					endPosB = aliases[k].lastIndexOf("-");
				}

				if (alias.substr(0, endPosA) == aliases[k].substr(0, endPosB)) {
					if (alias.lastIndexOf("-") != -1) {
						if (aliases[k].lastIndexOf("-") != -1) {
							if (alias.substr(endPosA) * 1 <= aliases[k]
									.substr(endPosB) * 1) {
								var newIndex = (aliases[k].substr(endPosB + 1) * 1) + 1;
								alias = alias.substr(0, endPosA) + "-"
										+ newIndex;
							}
						}
					} else {
						if (aliases[k].lastIndexOf("-") != -1) {
							newIndex = (aliases[k].substr(endPosB + 1) * 1) + 1;
							alias = alias.substr(0, endPosA) + "-" + newIndex;
						} else {
							alias = alias + "-1";
						}
					}
				}
			}
			document.getElementById("s3-alias" + i).value = alias;
			aliases.push(alias);
		}
	},

	/**
	 * this method is responsible for automatic alias-creation in step 4 specify
	 * result aliases
	 * 
	 */
	generateResultAliases : function() {
		var resultsCount = document.getElementById("step4-results").childNodes[0].childNodes.length;

		var aliases = new Array();

		for (i = 0; i < resultsCount - 1; i++) {
			var alias = document.getElementById("s4-alias" + i).value;
			if (alias.length == 0) {
				alias = document.getElementById("s4-path" + i).childNodes[document
						.getElementById("s4-path" + i).childNodes.length - 1].nodeValue;
			}
			if (alias == "]") {
				alias = "";
			}
			var openBracketPos = alias.lastIndexOf("(");
			if (openBracketPos != -1) {
				alias = alias.substr(0, openBracketPos - 1);
			}
			var dotPos = alias.lastIndexOf(".");
			alias = alias.substr(dotPos + 1);
			if (alias.length == 0) {
				alias = "result";
			}
			for ( var k = 0; k < aliases.length; k++) {
				var endPosA = alias.length;
				if (alias.lastIndexOf("-") != -1) {
					endPosA = alias.lastIndexOf("-");
				}

				var endPosB = aliases[k].length;
				if (aliases[k].lastIndexOf("-") != -1) {
					endPosB = aliases[k].lastIndexOf("-");
				}

				if (alias.substr(0, endPosA) == aliases[k].substr(0, endPosB)) {
					if (alias.lastIndexOf("-") != -1) {
						if (aliases[k].lastIndexOf("-") != -1) {
							if (alias.substr(endPosA) * 1 <= aliases[k]
									.substr(endPosB) * 1) {
								var newIndex = (aliases[k].substr(endPosB + 1) * 1) + 1;
								alias = alias.substr(0, endPosA) + "-"
										+ newIndex;
							}
						}
					} else {
						if (aliases[k].lastIndexOf("-") != -1) {
							newIndex = (aliases[k].substr(endPosB + 1) * 1) + 1;
							alias = alias.substr(0, endPosA) + "-" + newIndex;
						} else {
							alias = alias + "-1";
						}
					}
				}
			}
			document.getElementById("s4-alias" + i).value = alias;
			aliases.push(alias);
		}
	},

	/**
	 * this method is responsible for adding new parameters in step 3
	 * 
	 * @param int
	 *            i index of the parameter where the add-button was pressed
	 * @param int
	 *            k index of the path-step where the add-button was pressed
	 * 
	 */
	addParameter : function(i, k) {
		var okButton = document.getElementById("step3-ok").cloneNode(true);
		okButton.id = "step3-ok"
		document.getElementById("step3-ok").parentNode.removeChild(document
				.getElementById("step3-ok"));

		// detect the new index of the parameters to create
		var pathPatterns = new Array(k + 1);
		for ( var m = 0; m < k + 1; m++) {
			pathPatterns[m] = document.getElementById("step3-parameters").childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k * 2].nodeValue;
		}
		var arrayIndex = pathPatterns[k].substr(pathPatterns[k]
				.lastIndexOf("[") + 1, pathPatterns[k].length) * 1 + 1;

		for (i = 1; i < document.getElementById("step3-parameters").childNodes[0].childNodes.length; i++) {
			if (!(pathPatterns.length * 2 > document
					.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes.length * 2)) {
				for (m = 0; m < pathPatterns.length; m++) {
					if (m != pathPatterns.length - 1) {
						if (pathPatterns[m] != document
								.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes[m * 2].nodeValue) {
							m = pathPatterns.length;
						}
					} else {
						var tempPathStep = document
								.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes[(pathPatterns.length - 1) * 2].nodeValue;
						if (tempPathStep.substr(0, tempPathStep
								.lastIndexOf("[")) == pathPatterns[m].substr(0,
								pathPatterns[m].lastIndexOf("["))) {
							var tempPathIndex = tempPathStep
									.substr(tempPathStep.lastIndexOf("[") + 1) * 1;
							if (arrayIndex * 1 <= tempPathIndex * 1) {
								arrayIndex = tempPathIndex * 1 + 1;
							}
						}
					}
				}
			}
		}

		// create the new parameters
		var newPathStep = pathPatterns[k].substr(0, pathPatterns[k]
				.lastIndexOf("[") + 1)
				+ arrayIndex;

		for (i = 1; i < document.getElementById("step3-parameters").childNodes[0].childNodes.length; i++) {
			var paramRow = document.getElementById("step3-parameters").childNodes[0].childNodes[i]
					.cloneNode(true);
			var matches = true;
			if (pathPatterns.length * 2 > document
					.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes.length * 2) {
				matches = false;
			} else {
				for (m = 0; m < pathPatterns.length; m++) {
					if (pathPatterns[m] != document
							.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes[m * 2].nodeValue) {
						matches = false;
					}
				}
			}
			if (matches) {
				var elementIdCount = document
						.getElementById("step3-parameters").childNodes[0].childNodes.length - 1;
				paramRow.childNodes[0].firstChild.childNodes[k * 2].nodeValue = newPathStep;
				for (m = 1; m < paramRow.childNodes[0].firstChild.childNodes.length; m += 2) {
					var onclick = paramRow.childNodes[0].firstChild.childNodes[m]
							.getAttributeNode("onclick");
					onclick.value = "webServiceSpecial.addParameter("
							+ elementIdCount + ", " + k + ")";
					paramRow.childNodes[0].firstChild.childNodes[m]
							.setAttributeNode(onclick);
				}
				paramRow.childNodes[0].childNodes[0].id = "s3-path"
						+ elementIdCount;
				paramRow.childNodes[1].childNodes[0].id = "s3-alias"
						+ elementIdCount;
				paramRow.childNodes[1].childNodes[0].value = "";
				paramRow.childNodes[2].childNodes[0].id = "s3-optional-true"
						+ elementIdCount;
				paramRow.childNodes[2].childNodes[2].id = "s3-optional-false"
						+ elementIdCount;
				paramRow.childNodes[2].childNodes[0].name = "s3-optional-radio"
						+ elementIdCount;
				paramRow.childNodes[2].childNodes[2].name = "s3-optional-radio"
						+ elementIdCount;
				paramRow.childNodes[2].childNodes[2].checked = "true";
				paramRow.childNodes[3].childNodes[0].id = "s3-default"
						+ elementIdCount;
				paramRow.childNodes[3].childNodes[0].value = "";

				paramRow.childNodes[3].appendChild(okButton);
				var pos = elementIdCount - 1;

				document.getElementById("step3-parameters").childNodes[0]
						.appendChild(paramRow);

			}
		}
	},

	/**
	 * this method is responsible for adding new result parts in step 4
	 * 
	 * @param int
	 *            i index of the result-part where the add-button was pressed
	 * 
	 */
	addResultPart : function(i) {
		var elementIdCount = document.getElementById("step4-results").childNodes[0].childNodes.length - 1;
		var okButton = document.getElementById("step4-ok").cloneNode(true);
		document.getElementById("step4-ok").id = "old-step4-ok";
		document.getElementById("old-step4-ok").parentNode.removeChild(document
				.getElementById("old-step4-ok"));

		var pos = elementIdCount - 1;
		var resultRow = document.getElementById("step4-results").childNodes[0].childNodes[i + 1]
				.cloneNode(true);
		for (m = 3; m < resultRow.childNodes[1].firstChild.childNodes.length; m += 2) {
			resultRow.childNodes[1].firstChild.childNodes[m].value = "";
		}

		resultRow.childNodes[1].childNodes[0].id = "s4-path" + elementIdCount;
		resultRow.childNodes[2].childNodes[0].id = "s4-alias" + elementIdCount;
		resultRow.childNodes[3].appendChild(okButton);
		document.getElementById("step4-results").childNodes[0]
				.appendChild(resultRow);
	},

	/**
	 * The method checks if the enter button was pressed in a input-element and
	 * calls the right method
	 * 
	 * @param event :
	 *            the keyevent
	 * @param string
	 *            step : defines which process step to call
	 */
	checkEnterKey : function(event, step) {
		if (event.which == 13) {
			if (step == "step1") {
				this.processStep1();
			} else if (step == "step6") {
				this.processStep6();
			}
		}
	},

	selectRadio : function(radioId) {
		document.getElementById(radioId).checked = true;
	},

	selectRadioOnce : function(radioId) {
		if (radioId == "step5-display-once") {
			document.getElementById("step5-display-days").value = "";
			document.getElementById("step5-display-hours").value = "";
			document.getElementById("step5-display-minutes").value = "";
		} else if (radioId == "step5-query-once") {
			document.getElementById("step5-query-days").value = "";
			document.getElementById("step5-query-hours").value = "";
			document.getElementById("step5-query-minutes").value = "";
		}
	}

}

webServiceSpecial = new DefineWebServiceSpecial();
