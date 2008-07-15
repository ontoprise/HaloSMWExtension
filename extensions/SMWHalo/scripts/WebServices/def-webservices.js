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
		var uri = $("step1-uri").value;
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
			// hide or display widgets of other steps
			$("step2").style.display = "none";

			$("menue-step1").className = "ActualMenueStep";
			$("menue-step2").className = "TodoMenueStep";

			$("step1-help").style.display = "block";
			$("step2-help").style.display = "none";

			$("step1-img").style.visibility = "visible";
			$("step2-img").style.visibility = "hidden";

			$("step1-error").style.display = "block";

			this.step = "step1";
			$("errors").style.display = "block";
		} else {
			wsMethods.shift();
			$("errors").style.display = "none";
			$("step1-error").style.display = "none";

			// clear the widget for step 2
			var existingOptions = $("step2-methods").cloneNode(false);
			$("step2-methods").id = "old-step2-methods";
			$("old-step2-methods").parentNode.insertBefore(existingOptions,
					document.getElementById("old-step2-methods"));
			$("old-step2-methods").parentNode
					.removeChild($("old-step2-methods"));
			existingOptions.id = "step2-methods";

			// fill the widget for step2 with content

			for (i = 0; i < wsMethods.length; i++) {
				var option = document.createElement("option");
				var mName = document.createTextNode(wsMethods[i]);
				option.appendChild(mName);
				$("step2-methods").appendChild(option);
			}

			// hide or display widgets of other steps
			$("step2").style.display = "block";

			$("menue-step1").className = "DoneMenueStep";
			$("menue-step2").className = "ActualMenueStep";

			$("step1-help").style.display = "none";
			$("step2-help").style.display = "block";

			$("step1-img").style.visibility = "hidden";
			$("step2-img").style.visibility = "visible";

			$("step1-error").style.display = "none";
		}

		// hide or display widgets of other steps
		$("step3").style.display = "none";
		$("step4").style.display = "none";
		$("step5").style.display = "none";
		$("step6").style.display = "none";

		$("menue-step3").className = "TodoMenueStep";
		$("menue-step4").className = "TodoMenueStep";
		$("menue-step5").className = "TodoMenueStep";
		$("menue-step6").className = "TodoMenueStep";

		$("step3-help").style.display = "none";
		$("step4-help").style.display = "none";
		$("step5-help").style.display = "none";
		$("step6-help").style.display = "none";

		$("step3-img").style.visibility = "hidden";
		$("step4-img").style.visibility = "hidden";
		$("step5-img").style.visibility = "hidden";
		$("step6-img").style.visibility = "hidden";

		$("step2a-error").style.display = "none";
		$("step2b-error").style.display = "none";
		$("step3-error").style.display = "none";
		$("step4-error").style.display = "none";
		$("step5-error").style.display = "none";
		$("step6-error").style.display = "none";
		$("step6b-error").style.display = "none";

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
		var method = $("step2-methods").value;
		var uri = $("step1-uri").value;
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

		this.preparedPathSteps = new Array();

		if (wsParameters[0] == "todo:handle noparams") {
			this.processStep3();
			return;
		}

		var treeView = false;
		var aTreeRoot = false;

		var overflow = false;
		for (i = 0; i < wsParameters.length; i++) {
			if (wsParameters[i].indexOf("##overflow##") > 0) {
				wsParameters[i] = wsParameters[i].substr(0, wsParameters[i]
						.indexOf("##overflow##"));
			}
		}

		for ( var i = 1; i < wsParameters.length; i++) {
			var steps = wsParameters[i].split(".");
			var preparedPathStepsDot = new Array();
			for ( var k = 0; k < steps.length; k++) {
				var tO = new Object();
				tO["value"] = steps[k];
				tO["i"] = "null";
				tO["k"] = "null";
				preparedPathStepsDot[k] = tO;
			}
			this.preparedPathSteps[i - 1] = preparedPathStepsDot;

		}

		if (wsParameters[0] != "todo:handle exceptions" || overflow) {
			this.step = "step2";
			// hide or display widgets of other steps
			$("step3").style.display = "none";

			$("menue-step2").className = "ActualMenueStep";
			$("menue-step3").className = "TodoMenueStep";

			$("step2-help").style.display = "block";
			$("step3-help").style.display = "none";

			$("step2-img").style.visibility = "visible";
			$("step3-img").style.visibility = "hidden";

			$("step2a-error").style.display = "none";
			$("step2b-error").style.display = "none";

			if (overflow) {
				$("step2b-error").style.display = "block";
			} else {
				$("step2a-error").style.display = "block";
			}
		} else {
			wsParameters.shift();
			// clear widgets of step 3
			var okButton = $("step3-ok").cloneNode(true);
			$("step3-ok").id = "old-step3-ok";
			$("old-step3-ok").parentNode.removeChild($("old-step3-ok"));
			var tempHead = $("step3-parameters").childNodes[0].childNodes[0]
					.cloneNode(true);
			var tempTable = $("step3-parameters").childNodes[0]
					.cloneNode(false);
			$("step3-parameters").removeChild(
					$("step3-parameters").childNodes[0]);
			$("step3-parameters").appendChild(tempTable);
			$("step3-parameters").childNodes[0].appendChild(tempHead);

			// fill widgets for step 3 with content

			for (i = 0; i < wsParameters.length; i++) {
				var paramRow = document.createElement("tr");
				paramRow.id = "step3-paramRow-" + i;
				$("step3-parameters").childNodes[0].appendChild(paramRow);

				var paramTD0 = document.createElement("td");
				paramTD0.id = "step3-paramTD0-" + i;
				paramRow.appendChild(paramTD0);

				var paramPath = document.createElement("div");
				var dotSteps = wsParameters[i].split(".");

				paramTD0.appendChild(paramPath);
				paramPath.id = "s3-path" + i;
				paramPath.className = "OuterLeftIndent";

				for (k = 0; k < dotSteps.length; k++) {
					var paramPathStep = document.createElement("span");
					paramPathStep.id = "s3-pathstep-" + i + "-" + k;
					if (aTreeRoot) {
						paramPathStep.style.visibility = "hidden";
					}

					var paramPathText = "";
					if (k > 0) {
						paramPathText += ".";
					}
					paramPathText += dotSteps[k];
					paramPathTextNode = document.createTextNode(paramPathText);
					paramPathStep.appendChild(paramPathTextNode);
					paramPath.appendChild(paramPathStep);
					if (i > 0) {
						if ($("s3-pathstep-" + (i - 1) + "-" + k) != null) {
							if (paramPathText == $("s3-pathstep-" + (i - 1)
									+ "-" + k).firstChild.nodeValue) {
								$("s3-pathstep-" + i + "-" + k).style.visibility = "hidden";
								treeView = true;
							}
						}
					}

					if (i < wsParameters.length - 1) {
						if (this.preparedPathSteps[i + 1][k] != null) {
							if (paramPathText == this.preparedPathSteps[i + 1][k]["value"]
									|| paramPathText == "."
											+ this.preparedPathSteps[i + 1][k]["value"]) {
								this.preparedPathSteps[i][k]["i"] = i + 1;
								this.preparedPathSteps[i][k]["k"] = k;
								aTreeRoot = true;

								var expandPathStep = document
										.createElement("span");
								expandPathStep.id = "step3-expand-" + i + "-"
										+ k;
								expandPathStep.expanded = false;

								var expandOnClick = document
										.createAttribute("onclick");
								expandOnClick.value = "webServiceSpecial.expandParamPathStep(\""
										+ i + "\",\"" + k + "\")";
								expandPathStep.setAttributeNode(expandOnClick);

//								expandPathStepText = document
//										.createTextNode("+");
//								expandPathStep.appendChild(expandPathStepText);

								var expandIMG = document.createElement("img");
								expandIMG.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
								expandPathStep.appendChild(expandIMG);
								
								
								expandPathStep.style.cursor = "pointer";
								$("s3-pathstep-" + i + "-" + k)
										.insertBefore(
												expandPathStep,
												$("s3-pathstep-" + i + "-" + k).firstChild);
							}
						}
					}
				}

				var paramTD1 = document.createElement("td");
				paramTD1.id = "step3-paramTD1-" + i;
				paramRow.appendChild(paramTD1);

				var aliasInput = document.createElement("input");
				aliasInput.id = "s3-alias" + i;
				aliasInput.size = "15";
				aliasInput.maxLength = "40";
				paramTD1.appendChild(aliasInput);

				if (aTreeRoot || treeView) {
					paramTD1.style.visibility = "hidden";
				}

				var paramTD2 = document.createElement("td");
				paramTD2.id = "step3-paramTD2-" + i;
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

				if (aTreeRoot || treeView) {
					paramTD2.style.visibility = "hidden";
				}

				var paramTD3 = document.createElement("td");
				paramTD3.id = "step3-paramTD3-" + i;
				paramRow.appendChild(paramTD3);

				var defaultInput = document.createElement("input");
				defaultInput.id = "s3-default" + i;
				defaultInput.size = "15";
				defaultInput.maxLength = "40";
				paramTD3.appendChild(defaultInput);

				if (aTreeRoot || treeView) {
					paramTD3.style.visibility = "hidden";
				}

				var paramTD4 = document.createElement("td");
				paramTD4.id = "step3-paramTD4-" + i;
				paramRow.appendChild(paramTD4);

				if (i == wsParameters.length - 1) {
					paramTD4.appendChild(okButton);
				}
				if (aTreeRoot || treeView) {
					paramTD4.style.visibility = "hidden";
				}

				if (treeView) {
					paramRow.style.display = "none";
				}
			}
			// hide or display widgets of other steps
			$("step3").style.display = "block";

			$("menue-step2").className = "DoneMenueStep";
			$("menue-step3").className = "ActualMenueStep";

			$("step2-help").style.display = "none";
			$("step3-help").style.display = "block";

			$("step2-img").style.visibility = "hidden";
			$("step3-img").style.visibility = "visible";

			$("step2a-error").style.display = "none";
			$("step2b-error").style.display = "none";
		}

		// hide or display widgets of other steps
		$("step4").style.display = "none";
		$("step5").style.display = "none";
		$("step6").style.display = "none";

		$("menue-step4").className = "TodoMenueStep";
		$("menue-step5").className = "TodoMenueStep";
		$("menue-step6").className = "TodoMenueStep";

		$("step4-help").style.display = "none";
		$("step5-help").style.display = "none";
		$("step6-help").style.display = "none";

		$("step4-img").style.visibility = "hidden";
		$("step5-img").style.visibility = "hidden";
		$("step6-img").style.visibility = "hidden";

		$("step3-error").style.display = "none";
		$("step4-error").style.display = "none";
		$("step5-error").style.display = "none";
		$("step6-error").style.display = "none";
		$("step6b-error").style.display = "none";
	},

	/**
	 * called when the user finishes step 3 define parameters
	 * 
	 * @return
	 */
	processStep3 : function() {
		this.generateParameterAliases();
		var method = $("step2-methods").value;
		var uri = $("step1-uri").value;
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

		var overflow = false;
		for (i = 0; i < wsResults.length; i++) {
			if (wsResults[i].indexOf("##overflow##") > 0) {
				wsResults[i] = wsResults[i].substr(0, wsResults[i]
						.lastIndexOf("."));
			}
		}

		var aTreeRoot = false;
		var treeView = false;

		this.preparedRPathSteps = new Array();
		for ( var i = 1; i < wsResults.length; i++) {
			if (wsResults[i].length > 0) {
				wsResults[i] = "result." + wsResults[i];
			} else {
				wsResults[i] = "result";
			}
			var steps = wsResults[i].split(".");
			var preparedPathStepsDot = new Array();
			for ( var k = 0; k < steps.length; k++) {
				var tO = new Object();
				tO["value"] = steps[k];
				tO["i"] = "null";
				tO["k"] = "null";
				preparedPathStepsDot[k] = tO;
			}

			this.preparedRPathSteps[i - 1] = preparedPathStepsDot;
		}

		if (wsResults[0] != "todo:handle exceptions" || overflow) {
			// hide or display widgets of other steps
			$("step3-error").style.display = "block";
		} else {
			wsResults.shift();
			// clear widgets of step 4
//			var okButton = $("step4-ok").cloneNode(true);
//			$("step4-ok").id = "old-step4-ok";
//			$("old-step4-ok").parentNode.removeChild($("old-step4-ok"));

			var tempHead = $("step4-results").childNodes[0].childNodes[0]
					.cloneNode(true);
			var tempTable = $("step4-results").childNodes[0].cloneNode(false);
			$("step4-results").removeChild($("step4-results").childNodes[0]);
			$("step4-results").appendChild(tempTable);
			$("step4-results").childNodes[0].appendChild(tempHead);

			// fill the widgets of step4 with content

			for (i = 0; i < wsResults.length; i++) {
				var resultRow = document.createElement("tr");
				resultRow.id = "step4-resultRow-" + i;
				$("step4-results").childNodes[0].appendChild(resultRow);

				var resultTD0 = document.createElement("td");
				resultRow.appendChild(resultTD0);

				var resultTD1 = document.createElement("td");
				resultTD1.id = "step4-resultTD1-" + i;
				resultRow.appendChild(resultTD1);

				var resultPath = document.createElement("span");
				resultTD1.appendChild(resultPath);

				for (k = 0; k < this.preparedRPathSteps[i].length; k++) {
					var resultPathStep = document.createElement("span");
					resultPathStep.id = "s4-pathstep-" + i + "-" + k;
					if (aTreeRoot) {
						resultPathStep.style.visibility = "hidden";
					}

					var resultPathText = "";
					if (k > 0) {
						resultPathText += ".";
					}
					resultPathText += this.preparedRPathSteps[i][k]["value"];
					var resultPathTextNode = document
							.createTextNode(resultPathText);
					resultPathStep.appendChild(resultPathTextNode);
					resultPath.appendChild(resultPathStep);
					if (i > 0) {
						if ($("s4-pathstep-" + (i - 1) + "-" + k) != null) {
							if (resultPathText == $("s4-pathstep-" + (i - 1)
									+ "-" + k).firstChild.nodeValue) {
								$("s4-pathstep-" + i + "-" + k).style.visibility = "hidden";
								treeView = true;
							}
						}
					}

					if (i < this.preparedRPathSteps.length - 1) {
						if (this.preparedRPathSteps[i + 1][k] != null) {
							if (resultPathText == this.preparedRPathSteps[i + 1][k]["value"]
									|| resultPathText == "."
											+ this.preparedRPathSteps[i + 1][k]["value"]) {
								this.preparedRPathSteps[i][k]["i"] = i + 1;
								this.preparedRPathSteps[i][k]["k"] = k;
								aTreeRoot = true;

								var expandPathStep = document
										.createElement("span");
								expandPathStep.id = "step4-expand-" + i + "-"
										+ k;

								var expandOnClick = document
										.createAttribute("onclick");
								expandOnClick.value = "webServiceSpecial.expandResultPathStep(\""
										+ i + "\",\"" + k + "\")";
								expandPathStep.setAttributeNode(expandOnClick);
								expandPathStep.expanded = false;
								
								var expandIMG = document.createElement("img");
								expandIMG.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
								expandPathStep.appendChild(expandIMG);

								expandPathStep.style.cursor = "pointer";
								$("s4-pathstep-" + i + "-" + k)
										.insertBefore(
												expandPathStep,
												$("s4-pathstep-" + i + "-" + k).firstChild);
							}
						}
					}
				}

				resultPath.id = "s4-path" + i;
				resultTD1.appendChild(resultPath);

				var resultTD2 = document.createElement("td");
				resultTD2.id = "step4-resultTD2-" + i;
				resultRow.appendChild(resultTD2);

				var aliasInput = document.createElement("input");
				aliasInput.id = "s4-alias" + i;
				aliasInput.size = "15";
				aliasInput.maxLength = "40";
				resultTD2.appendChild(aliasInput);

				var resultTD3 = document.createElement("td");
				resultTD3.id = "step4-resultTD3-" + i;

				if (aTreeRoot || treeView) {
					resultTD2.style.visibility = "hidden";
					resultTD3.style.visibility = "hidden";
				}

				resultRow.appendChild(resultTD3);

				if (treeView) {
					resultRow.style.display = "none";
				}
				
//				if (i == wsResults.length - 1) {
//					resultTD3.appendChild(okButton);
//				}
			}

			// hide or display widgets of other steps
			$("step4").style.display = "block";

			$("menue-step2").className = "DoneMenueStep";
			$("menue-step3").className = "DoneMenueStep";
			if ($("menue-step4").className == "TodoMenueStep") {
				$("menue-step4").className = "ActualMenueStep";
			}

			$("step3-help").style.display = "none";
			$("step4-help").style.display = "block";

			$("step3-img").style.visibility = "hidden";
			$("step4-img").style.visibility = "visible";

			$("step3-error").style.display = "none";
		}

		// hide or display widgets of other steps
		$("step4-error").style.display = "none";
		$("step5-error").style.display = "none";
		$("step6-error").style.display = "none";
		$("step6b-error").style.display = "none";
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
		$("step5").style.display = "block";

		$("menue-step4").className = "DoneMenueStep";
		$("menue-step5").className = "ActualMenueStep";
		$("step4-help").style.display = "none";
		$("step5-help").style.display = "block";
		$("step4-img").style.visibility = "hidden";
		$("step5-img").style.visibility = "visible";

		$("step5-display-once").checked = true;
		$("step5-display-days").value = "";
		$("step5-display-hours").value = "";
		$("step5-display-minutes").value = "";

		$("step5-query-once").checked = true;
		$("step5-query-days").value = "";
		$("step5-query-hours").value = "";
		$("step5-query-minutes").value = "";
		$("step5-delay").value = "";

		$("step5-spanoflife").value = "";
		$("step5-expires-yes").checked = true;
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
		$("step6").style.display = "block";

		$("menue-step5").className = "DoneMenueStep";
		$("menue-step6").className = "ActualMenueStep";

		$("step5-help").style.display = "none";
		$("step6-help").style.display = "block";
		$("step5-img").style.visibility = "hidden";
		$("step6-img").style.visibility = "visible";
		$("step6-name").value = "";
	},

	/**
	 * called after step 6 specify ws-name this method constructs the wwsd
	 */
	processStep6 : function() {
		if ($("step6-name").value.length > 0) {
			$("errors").style.display = "none";
			$("step6-error").style.display = "none";
			$("step6b-error").style.display = "none";
			var result = "<WebService>\n";

			var uri = $("step1-uri").value;
			result += "<uri name=\"" + uri + "\" />\n";

			result += "<protocol>SOAP</protocol>\n";

			var method = $("step2-methods").value;
			result += "<method name=\"" + method + "\" />\n";

			for ( var i = 0; i < this.preparedPathSteps.length; i++) {
				result += "<parameter name=\"" + $("s3-alias" + i).value
						+ "\" ";
				var optional = $("s3-optional-true" + i).checked;
				result += " optional=\"" + optional + "\" ";
				if ($("s3-default" + i).value != "") {
					if ($("s3-default" + i).value != "") {
						result += " defaultValue=\""
								+ $("s3-default" + i).value + "\" ";
					}
				}
				var path = "";
				for ( var k = 0; k < this.preparedPathSteps[i].length; k++) {
					var pathStep = "";
					if (k > 0) {
						pathStep += ".";
					}
					pathStep += this.preparedPathSteps[i][k]["value"];
					if (pathStep.lastIndexOf("(") > 0) {
						pathStep = pathStep.substr(0,
								pathStep.lastIndexOf("(") - 1);
					}
					if (pathStep != ".") {
						path += pathStep;
					}
				}
				result += " path=\"" + path + "\" />\n";
			}

			result += "<result name=\"result\" >\n";

			for (i = 0; i < this.preparedRPathSteps.length; i++) {
				result += "<part name=\"" + $("s4-alias" + i).value + "\" ";

				var rPath = "";
				for (k = 1; k < this.preparedRPathSteps[i].length; k++) {
					var rPathStep = "";

					if (k > 1) {
						rPathStep += ".";
					}
					rPathStep += this.preparedRPathSteps[i][k]["value"];

					if (rPathStep.lastIndexOf("(") > 0) {
						rPathStep = rPathStep.substr(0, rPathStep
								.lastIndexOf("(") - 1);
					}
					if (rPathStep != ".") {
						rPath += rPathStep;
					}
				}
				result += " path=\"" + rPath + "\" />\n";

			}
			result += "</result>\n";

			result += "<displayPolicy>\n"
			if ($("step5-display-once").checked == true) {
				result += "<once/>\n";
			} else {
				result += "<maxAge value=\"";
				var minutes = 0;
				minutes += $("step5-display-days").value * 60 * 24;
				minutes += $("step5-display-hours").value * 60;
				minutes += $("step5-display-minutes").value * 1;
				result += minutes;
				result += "\"></maxAge>\n";
			}
			result += "</displayPolicy>\n"

			result += "<queryPolicy>\n"
			if ($("step5-query-once").checked == true) {
				result += "<once/>\n";
			} else {
				result += "<maxAge value=\"";
				minutes = 0;
				minutes += $("step5-query-days").value * 60 * 24;
				minutes += $("step5-query-hours").value * 60;
				minutes += $("step5-query-minutes").value * 1;
				result += minutes;
				result += "\"></maxAge>\n";
			}
			var delay = $("step5-delay").value;
			if (delay.length == 0) {
				delay = 0;
			}
			result += "<delay value=\"" + delay + "\"/>\n";
			result += "</queryPolicy>\n"
			result += "<spanOfLife value=\""
					+ (0 + $("step5-spanoflife").value * 1);
			if ($("step5-expires-yes").checked) {
				result += "\" expiresAfterUpdate=\"true\" />\n";
			} else {
				result += "\" expiresAfterUpdate=\"false\" />\n";
			}
			result += "</WebService>";
			this.wwsd = result;
			var wsName = $("step6-name").value;

			// the three additional "#" tell the ws-syntax processor not to
			// process
			// this ws-syntax
			var wsSyntax = "\n== Syntax for using the WWSD in an article==";
			wsSyntax += "\n<nowiki>{{#ws: " + $("step6-name").value
					+ "</nowiki>\n";
			for (i = 0; i < $("step3-parameters").childNodes[0].childNodes.length - 1; i++) {
				wsSyntax += "| " + $("s3-alias" + i).value
						+ " = [Please enter a value here]\n";
			}
			results = $("step4-results").childNodes[0].childNodes;
			for (i = 0; i < results.length - 1; i++) {
				wsSyntax += "| ?result." + $("s4-alias" + i).value + "\n";
			}

			wsSyntax += "}}";

			this.wsSyntax = wsSyntax;

			sajax_do_call("smwf_om_ExistsArticle", [ "webservice:" + wsName ],
					this.processStep6CallBack.bind(this));

		} else {
			$("errors").style.display = "block";
			$("step6-error").style.display = "block";
			$("step6b-error").style.display = "none";
		}

	},

	processStep6CallBack : function(request) {
		if (request.responseText == "false") {
			var wsName = $("step6-name").value;
			sajax_do_call("smwf_om_EditArticle", [ "webservice:" + wsName,
					this.wwsd + this.wsSyntax, "" ], this.processStep6CallBack1
					.bind(this));
		} else {
			$("errors").style.display = "block";
			$("step6b-error").style.display = "block";
			$("step6-error").style.display = "none";
		}
	},

	/**
	 * callback method for step 6Callback
	 * 
	 */
	processStep6CallBack1 : function(request) {
		var wsName = $("step6-name").value;
		sajax_do_call("smwf_ws_processStep6", [ wsName, this.wwsd ],
				this.processStep6CallBack2.bind(this));

	},

	/**
	 * callback method for step-6
	 * 
	 */
	processStep6CallBack2 : function(request) {
		var wsName = $("step6-name").value;
		sajax_do_call("smwf_om_TouchArticle", [ "webservice:" + wsName ],
				this.processStep6CallBack3.bind(this));
	},

	/**
	 * callback method for step-6 this method initializes the gui for step which
	 * provides an example for the #ws-syntax
	 * 
	 */
	processStep6CallBack3 : function(request) {
		var container = $("step7-container").cloneNode(false);
		$("step7-container").id = "old-step7-container";
		$("old-step7-container").parentNode.insertBefore(container,
				$("old-step7-container"));
		$("old-step7-container").parentNode
				.removeChild($("old-step7-container"));

		var wsNameText = document.createTextNode(document
				.getElementById("step6-name").value);
		$("step7-name").appendChild(wsNameText);

		var rowDiv = document.createElement("div");
		var rowText = document
				.createTextNode("{{#ws: " + $("step6-name").value);
		rowDiv.appendChild(rowText);
		$("step7-container").appendChild(rowDiv);

		for ( var i = 0; i < $("step3-parameters").childNodes[0].childNodes.length - 1; i++) {
			rowDiv = document.createElement("div");
			rowDiv.className = "OuterLeftIndent";
			rowText = document.createTextNode("| " + $("s3-alias" + i).value
					+ " = [Please enter a value here]");
			rowDiv.appendChild(rowText);
			$("step7-container").appendChild(rowDiv);
		}

		var results = $("step4-results").childNodes[0].childNodes;
		for (i = 0; i < results.length - 1; i++) {
			rowDiv = document.createElement("div");
			rowDiv.className = "OuterLeftIndent";
			rowText = document.createTextNode("| ?result."
					+ $("s4-alias" + i).value);
			rowDiv.appendChild(rowText);
			$("step7-container").appendChild(rowDiv);
		}

		rowDiv = document.createElement("div");
		rowText = document.createTextNode("}}");
		rowDiv.appendChild(rowText);
		$("step7-container").appendChild(rowDiv);

		$("step7").style.display = "block";
		$("step1").style.display = "none";
		$("step2").style.display = "none";
		$("step3").style.display = "none";
		$("step4").style.display = "none";
		$("step5").style.display = "none";
		$("step6").style.display = "none";
		$("step6-help").style.display = "none";
		$("menue").style.display = "none";
		$("help").style.display = "none";
	},

	/**
	 * called after step 7 this method initializes the gui for step 1
	 * 
	 */
	processStep7 : function(request) {
		this.step = "step1";
		$("step1-img").style.visibility = "visible";
		$("step1-help").style.display = "block";
		$("step7").style.display = "none";
		$("menue").style.display = "block";
		$("menue-step2").style.fontWeight = "normal";
		$("menue-step3").style.fontWeight = "normal";
		$("menue-step4").style.fontWeight = "normal";
		$("menue-step5").style.fontWeight = "normal";
		$("menue-step6").style.fontWeight = "normal";
		$("help").style.display = "block";
		$("step1").style.display = "block";
		$("step1-uri").Value = "";
	},

	/**
	 * this method is responsible for automatic alias-creation in step 3 specify
	 * parameters
	 * 
	 */
	generateParameterAliases : function() {
		var paramCount = this.preparedPathSteps.length;

		var aliases = new Array();

		for (i = 0; i < paramCount; i++) {
			var alias = $("s3-alias" + i).value;
			if (alias.length == 0) {
				alias = this.preparedPathSteps[i][this.preparedPathSteps[i].length - 1]["value"];

				var openBracketPos = alias.lastIndexOf("(");
				if (openBracketPos > 0) {
					alias = alias.substr(0, openBracketPos - 1);
				}

				openBracketPos = alias.lastIndexOf("[");
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
			$("s3-alias" + i).value = alias;
			aliases.push(alias);
		}
	},

	/**
	 * this method is responsible for automatic alias-creation in step 4 specify
	 * result aliases
	 * 
	 */
	generateResultAliases : function() {
		var resultsCount = this.preparedRPathSteps.length;

		var aliases = new Array();

		for (i = 0; i < resultsCount; i++) {
			var alias = $("s4-alias" + i).value;
			if (alias.length == 0) {
				alias = this.preparedRPathSteps[i][this.preparedRPathSteps[i].length - 1]["value"];
			}
			if (alias == "]") {
				alias = "";
			}
			var openBracketPos = alias.lastIndexOf("(");
			if (openBracketPos != -1) {
				alias = alias.substr(0, openBracketPos - 1);
			}

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
			$("s4-alias" + i).value = alias;
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
		var okButton = $("step3-ok").cloneNode(true);
		okButton.id = "step3-ok"
		$("step3-ok").parentNode.removeChild(document
				.getElementById("step3-ok"));

		// detect the new index of the parameters to create
		var pathPatterns = new Array(k + 1);
		for ( var m = 0; m < k + 1; m++) {
			pathPatterns[m] = $("step3-parameters").childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k * 2].nodeValue;
		}
		var arrayIndex = pathPatterns[k].substr(pathPatterns[k]
				.lastIndexOf("[") + 1, pathPatterns[k].length) * 1 + 1;

		for (i = 1; i < $("step3-parameters").childNodes[0].childNodes.length; i++) {
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

		for (i = 1; i < $("step3-parameters").childNodes[0].childNodes.length; i++) {
			var paramRow = $("step3-parameters").childNodes[0].childNodes[i]
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

				$("step3-parameters").childNodes[0].appendChild(paramRow);

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
		var elementIdCount = $("step4-results").childNodes[0].childNodes.length - 1;
		var okButton = $("step4-ok").cloneNode(true);
		$("step4-ok").id = "old-step4-ok";
		$("old-step4-ok").parentNode.removeChild(document
				.getElementById("old-step4-ok"));

		var pos = elementIdCount - 1;
		var resultRow = $("step4-results").childNodes[0].childNodes[i + 1]
				.cloneNode(true);
		for (m = 3; m < resultRow.childNodes[1].firstChild.childNodes.length; m += 2) {
			resultRow.childNodes[1].firstChild.childNodes[m].value = "";
		}

		resultRow.childNodes[1].childNodes[0].id = "s4-path" + elementIdCount;
		resultRow.childNodes[2].childNodes[0].id = "s4-alias" + elementIdCount;
		resultRow.childNodes[3].appendChild(okButton);
		$("step4-results").childNodes[0].appendChild(resultRow);
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
		$(radioId).checked = true;
	},

	selectRadioOnce : function(radioId) {
		if (radioId == "step5-display-once") {
			$("step5-display-days").value = "";
			$("step5-display-hours").value = "";
			$("step5-display-minutes").value = "";
		} else if (radioId == "step5-query-once") {
			$("step5-query-days").value = "";
			$("step5-query-hours").value = "";
			$("step5-query-minutes").value = "";
		}
	},

	expandParamPathStep : function(i, k) {
		$("step3-expand-" + i + "-" + k).setAttribute(
				"onclick",
				"webServiceSpecial.contractParamPathStep(\"" + i + "\",\"" + k
						+ "\")");
		$("step3-expand-" + i + "-" + k).firstChild.src = "../extensions/SMWHalo/skins/webservices/Minus.gif";
		$("step3-expand-" + i + "-" + k).expanded = true;
		
		var goon = true;
		while (goon) {
			var display = true;
			var complete = true;
			for ( var m = k * 1 + 1; m < this.preparedPathSteps[i].length; m++) {
				var visible = true;
				if (i > 0) {
					if (this.preparedPathSteps[i - 1][m] != null) {
						if (this.preparedPathSteps[i][m]["value"] == this.preparedPathSteps[i - 1][m]["value"]) {
							m = this.preparedPathSteps[i].length;
							visible = false;
							display = false;
						}
					}
				}
				if (visible) {
					$("s3-pathstep-" + i + "-" + m).style.visibility = "visible";
					if (this.preparedPathSteps[i][m]["i"] != "null") {
						if ($("step3-expand-" + i + "-" + m).expanded){
							this.expandParamPathStep(i, m);
						}
						m = this.preparedPathSteps[i].length;
						complete = false;
					}
				}
			}
			if (display) {
				$("step3-paramRow-" + i).style.display = "";

				if (complete) {
					$("step3-paramTD1-" + i).style.visibility = "visible";
					$("step3-paramTD2-" + i).style.visibility = "visible";
					$("step3-paramTD3-" + i).style.visibility = "visible";
					$("step3-paramTD4-" + i).style.visibility = "visible";
				}
			}

			if (this.preparedPathSteps[i][k]["i"] != "null") {
				iTemp = this.preparedPathSteps[i][k]["i"];
				k = this.preparedPathSteps[i][k]["k"];
				i = iTemp;
			} else {
				goon = false;
			}
		}
	},

	contractParamPathStep : function(i, k) {
		$("step3-expand-" + i + "-" + k).setAttribute("onclick",
				"webServiceSpecial.expandParamPathStep(\"" + i + "\",\"" + k

				+ "\")");
		$("step3-expand-" + i + "-" + k).firstChild.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
		$("step3-expand-" + i + "-" + k).expanded = false;
		
		for ( var m = k * 1 + 1; m < this.preparedPathSteps[i].length; m++) {
			$("s3-pathstep-" + i + "-" + m).style.visibility = "hidden";
		}

		var goon = true;
		var root = true;
		while (goon) {
			if (!root) {
				$("step3-paramRow-" + i).style.display = "none";
			}
			root = false;

			$("step3-paramTD1-" + i).style.visibility = "hidden";
			$("step3-paramTD2-" + i).style.visibility = "hidden";
			$("step3-paramTD3-" + i).style.visibility = "hidden";
			$("step3-paramTD4-" + i).style.visibility = "hidden";

			if (this.preparedPathSteps[i][k]["i"] != "null") {
				iTemp = this.preparedPathSteps[i][k]["i"];
				k = this.preparedPathSteps[i][k]["k"];
				i = iTemp;
			} else {
				goon = false;
			}
		}
	},

	expandResultPathStep : function(i, k) {
		$("step4-expand-" + i + "-" + k).setAttribute(
				"onclick",
				"webServiceSpecial.contractResultPathStep(\"" + i + "\",\"" + k
						+ "\")");
		$("step4-expand-" + i + "-" + k).firstChild.src = "../extensions/SMWHalo/skins/webservices/Minus.gif";
		$("step4-expand-" + i + "-" + k).expanded = true;
		
		var goon = true;
		while (goon) {
			var display = true;
			var complete = true;
			for ( var m = k * 1 + 1; m < this.preparedRPathSteps[i].length; m++) {
				var visible = true;
				if (i > 0) {
					if (this.preparedRPathSteps[i - 1][m] != null) {
						if (this.preparedRPathSteps[i][m]["value"] == this.preparedRPathSteps[i - 1][m]["value"]) {
							m = this.preparedRPathSteps[i].length;
							visible = false;
							display = false;
						}
					}
				}
				if (visible) {
					$("s4-pathstep-" + i + "-" + m).style.visibility = "visible";
					if (this.preparedRPathSteps[i][m]["i"] != "null") {
						if ($("step4-expand-" + i + "-" + m).expanded) {
							this.expandResultPathStep(i, m);
						}
						m = this.preparedRPathSteps[i].length;
						complete = false;
					}
				}
			}
			if (display) {
				$("step4-resultRow-" + i).style.display = "";

				if (complete) {
					$("step4-resultTD2-" + i).style.visibility = "visible";
					$("step4-resultTD3-" + i).style.visibility = "visible";
				}
			}

			if (this.preparedRPathSteps[i][k]["i"] != "null") {
				iTemp = this.preparedRPathSteps[i][k]["i"];
				k = this.preparedRPathSteps[i][k]["k"];
				i = iTemp;
			} else {
				goon = false;
			}
		}
	},

	contractResultPathStep : function(i, k) {
		$("step4-expand-" + i + "-" + k).setAttribute("onclick",
				"webServiceSpecial.expandResultPathStep(\"" + i + "\",\"" + k

				+ "\")");
		$("step4-expand-" + i + "-" + k).firstChild.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
		$("step4-expand-" + i + "-" + k).expanded = false;
		
		for ( var m = k * 1 + 1; m < this.preparedRPathSteps[i].length; m++) {
			$("s4-pathstep-" + i + "-" + m).style.visibility = "hidden";
		}

		var goon = true;
		var root = true;
		while (goon) {
			if (!root) {
				$("step4-resultRow-" + i).style.display = "none";
			}
			root = false;

			$("step4-resultTD2-" + i).style.visibility = "hidden";
			$("step4-resultTD3-" + i).style.visibility = "hidden";

			if (this.preparedRPathSteps[i][k]["i"] != "null") {
				iTemp = this.preparedRPathSteps[i][k]["i"];
				k = this.preparedRPathSteps[i][k]["k"];
				i = iTemp;
			} else {
				goon = false;
			}
		}
	}

}

webServiceSpecial = new DefineWebServiceSpecial();
