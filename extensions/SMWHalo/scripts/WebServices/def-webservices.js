var DefineWebServiceSpecial = Class.create();

DefineWebServiceSpecial.prototype = {
	initialize : function() {
	},

	/**
	 * called when the user finishes step 1 define uri
	 * 
	 * @return
	 */
	processStep1 : function() {
		var uri = document.getElementById("step1-uri").value;
		sajax_do_call("smwf_ws_processStep1", [ uri ],
				this.processStep1CallBack.bind(this));
	},

	processStep1CallBack : function(request) {
		// clear the widget for step 2
		var existingOptions = document.getElementById("step2-methods")
				.cloneNode(false);
		document.getElementById("step2-methods").id = "old-step2-methods";
		document.getElementById("old-step2-methods").parentNode.insertBefore(
				existingOptions, document.getElementById("old-step2-methods"));
		document.getElementById("old-step2-methods").parentNode
				.removeChild(document.getElementById("old-step2-methods"));
		existingOptions.id = "step2-methods";

		// fill the widget for step2 with content
		var wsMethods = request.responseText.split(";");
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
	},

	/**
	 * called when the user finishes step 2 choose method
	 * 
	 * @return
	 */
	processStep2 : function() {
		var method = document.getElementById("step2-methods").value;
		var uri = document.getElementById("step1-uri").value;
		sajax_do_call("smwf_ws_processStep2", [ uri, method ],
				this.processStep2CallBack.bind(this));
	},

	processStep2CallBack : function(request) {
		// clear widgets of step 3
		var okButton = document.getElementById("step3-ok").cloneNode(true);
		document.getElementById("step3-ok").id = "old-step3-ok";
		document.getElementById("old-step3-ok").parentNode.removeChild(document
				.getElementById("old-step3-ok"));
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
		var wsParameters = request.responseText.split(";");
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
					var addButtonText = document.createTextNode("+");
					addButton.appendChild(addButtonText);
					var addButtonOnClick = document.createAttribute("onclick");
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
		;
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

		// todo: remove parameters
		sajax_do_call("smwf_ws_processStep3", [ uri, method, parameters ],
				this.processStep3CallBack.bind(this));
	},

	processStep3CallBack : function(request) {
		// clear widgets of step 4
		var okButton = document.getElementById("step4-ok").cloneNode(true);
		document.getElementById("step4-ok").id = "old-step4-ok";
		document.getElementById("old-step4-ok").parentNode.removeChild(document
				.getElementById("old-step4-ok"));

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
		var wsResults = request.responseText.split(";");
		for (i = 0; i < wsResults.length; i++) {
			var resultRow = document.createElement("tr");
			document.getElementById("step4-results").childNodes[0]
					.appendChild(resultRow);

			var resultTD0 = document.createElement("td");
			resultRow.appendChild(resultTD0);

			var resultTD1 = document.createElement("td");
			resultRow.appendChild(resultTD1);

			var resultPath = document.createElement("span");
			var arraySteps = wsResults[i].split("[");
			var ppText = "";
			for ( var k = 0; k < arraySteps.length; k++) {
				var paramPathText;
				if (k == 0 && arraySteps.length > 1) {
					var addButton = document.createElement("span");
					addButton.className = "OuterLeftIndent";
					addButton.style.cursor = "pointer";
					var addButtonText = document.createTextNode("+");
					addButton.appendChild(addButtonText);
					var addButtonOnClick = document.createAttribute("onclick");
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

					paramPathText = document
							.createTextNode(arraySteps[k] + "[");

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

	},

	/**
	 * called when the user finishes step 4 define results
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
	},

	processStep5 : function() {
		// hide or display widgets of other steps
		document.getElementById("step6").style.display = "block";

		document.getElementById("menue-step6").style.fontWeight = "bold";
		document.getElementById("step5-help").style.display = "none";
		document.getElementById("step6-help").style.display = "block";
		document.getElementById("step5-img").style.visibility = "hidden";
		document.getElementById("step6-img").style.visibility = "visible";
	},

	processStep6 : function() {
		var result = "<WebService>\n";

		var uri = document.getElementById("step1-uri").value;
		result += "<uri name=\"" + uri + "\" />";

		result += "<protocol>SOAP</protocol>";

		var method = document.getElementById("step2-methods").value;
		result += "<method name=\"" + method + "\" />";

		for ( var i = 0; i < document.getElementById("step3-parameters").childNodes[0].childNodes.length - 1; i++) {
			result += "<parameter name=\""
					+ document.getElementById("s3-alias" + i).value + "\" ";
			var optional = document.getElementById("s3-optional-true" + i).checked;
			result += " optional=\"" + optional + "\" ";
			if (document.getElementById("s3-default" + i).value != "") {
				result += " defaultValue=\""
						+ document.getElementById("s3-default" + i).value
						+ "\" ";
			}
			var path = "";
			for ( var k = 0; k < document.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes.length; k += 2) {
				var pathStep = document.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes[k].nodeValue;
				if (k == document.getElementById("step3-parameters").childNodes[0].childNodes[i].childNodes[0].firstChild.childNodes.length - 1) {
					pathStep = pathStep
							.substr(0, pathStep.lastIndexOf("(") - 1);
				}
				path += pathStep;
			}
			result += " path=\"" + path + "\" />";
		}

		result += "<result name=\"result\" >";

		var results = document.getElementById("step4-results").childNodes[0].childNodes;
		for (i = 0; i < results.length - 1; i++) {
			result += "<part name=\""
					+ document.getElementById("s4-alias" + i).value + "\" ";
			result += " path=\"" + "\" />";
		}
		result += "</result>";

		result += "<displayPolicy>"
		if (document.getElementById("step4-display-once").checked == true) {
			result += "<once/>";
		} else {
			result += "<maxAge value=\"";
			var minutes = 0;
			minutes += document.getElementById("step5-display-days").value * 60 * 24;
			minutes += document.getElementById("step5-display-hours").value * 60;
			minutes += document.getElementById("step5-display-minutes").value * 1;
			result += minutes;
			result += "\"></maxAge>";
		}
		result += "</displayPolicy>"

		result += "<queryPolicy>"
		if (document.getElementById("step4-query-once").checked == true) {
			result += "<once/>";
		} else {
			result += "<maxAge value=\"";
			var minutes = 0;
			minutes += document.getElementById("step5-query-days").value * 60 * 24;
			minutes += document.getElementById("step5-query-hours").value * 60;
			minutes += document.getElementById("step5-query-minutes").value * 1;
			result += "\"></maxAge>";
		}
		result += "</queryPolicy>"

		result += "</WebService>";

		sajax_do_call("smwf_om_EditArticle", [ "webservice:ws6", result, "" ],
				this.processStep6CallBack.bind(this));
	},

	processStep6CallBack : function(request) {
		alert(request.responseText);
	},

	generateParameterAliases : function() {
		var paramCount = document.getElementById("step3-parameters").childNodes[0].childNodes.length - 1;

		var aliases = new Array();

		for (i = 0; i < paramCount; i++) {
			var alias = document.getElementById("s3-alias"+i).value;
			if (alias.length == 0) {
				alias = document.getElementById("s3-path" + i).childNodes[document
						.getElementById("s3-path" + i).childNodes.length - 1].nodeValue;

				var openBracketPos = alias.lastIndexOf("(");
				alias = alias.substr(0, openBracketPos - 1);
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

	generateResultAliases : function() {
		var resultsCount = document.getElementById("step4-results").childNodes[0].childNodes.length;

		var aliases = new Array();

		for (i = 0; i < resultsCount-1; i++) {
			var alias = document.getElementById("s4-alias" + i).value;
			if (alias.length == 0) {
				alias = document.getElementById("s4-path" + i).childNodes[document
						.getElementById("s4-path" + i).childNodes.length - 1].nodeValue;
			}
			var openBracketPos = alias.lastIndexOf("(");
			if (openBracketPos != -1) {
				alias = alias.substr(0, openBracketPos - 1);
			}
			var dotPos = alias.lastIndexOf(".");
			alias = alias.substr(dotPos + 1);
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

	addParameter : function(i, k) {

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
				var okButton = document.getElementById("step3-ok").cloneNode(
						true);
				okButton.id = "step3-ok"
				document.getElementById("step3-ok").parentNode
						.removeChild(document.getElementById("step3-ok"));

				paramRow.childNodes[3].appendChild(okButton);
				var pos = elementIdCount - 1;

				document.getElementById("step3-parameters").childNodes[0]
						.appendChild(paramRow);

			}
		}
	},

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
	}
}

webServiceSpecial = new DefineWebServiceSpecial();
