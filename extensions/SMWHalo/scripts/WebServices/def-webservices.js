/*  This file is part of the halo-Extension.
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
 *   along with this program.  If not, see <http:// www.gnu.org/licenses/>.
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
		
		this.showPendingIndicator("step1-go");
		
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

		this.hidePendingIndicator();
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
		
		this.showPendingIndicator("step2-go");
		
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
			$("step3").style.display = "block";
			$("step2-img").style.visibility = "hidden";
			$("step3").childNodes[1].nodeValue = "3. This method does not ask for any parameters.";
			$("step3-parameters").style.display = "none";
			$("step3-go-img").style.display = "none";
			this.processStep3();
			return;
		} else {
			// todo: find better solution
			$("step2-img").style.visibility = "visible";
			$("step3").childNodes[1].nodeValue = "3. The method asks for the following parameters.";
			$("step3-parameters").style.display = "block";
			$("step3-go-img").style.display = "block";
		}

		var overflow = false;
		for (i = 0; i < wsParameters.length; i++) {
			if (wsParameters[i].indexOf("##overflow##") > 0) {
				wsParameters[i] = wsParameters[i].substr(0, wsParameters[i]
						.indexOf("##overflow##"));
			}
		}

		for ( var i = 1; i < wsParameters.length; i++) {
			var arrayIndexes = new Object();

			var steps = wsParameters[i].split(".");
			var preparedPathStepsDot = new Array();
			for ( var k = 0; k < steps.length; k++) {
				var tO = new Object();
				tO["value"] = steps[k];
				tO["i"] = "null";
				tO["k"] = "null";
				tO["arrayIndex"] = "null";
				tO["arrayIndexOrigin"] = null;
				tO["arrayIndexUsers"] = new Array();
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
			var tempHead = $("step3-parameters").childNodes[0].childNodes[0]
					.cloneNode(true);
			var tempTable = $("step3-parameters").childNodes[0]
					.cloneNode(false);
			$("step3-parameters").removeChild(
					$("step3-parameters").childNodes[0]);
			$("step3-parameters").appendChild(tempTable);
			$("step3-parameters").childNodes[0].appendChild(tempHead);

			this.parameterContainer = $("step3-parameters").cloneNode(true);

			// fill widgets for step 3 with content

			var treeView = false;
			var aTreeRoot = false;

			for (i = 0; i < wsParameters.length; i++) {
				treeView = false;
				aTreeRoot = false;
				var paramRow = document.createElement("tr");
				paramRow.id = "step3-paramRow-" + i;

				var paramTD0 = document.createElement("td");
				paramTD0.id = "step3-paramTD0-" + i;
				paramRow.appendChild(paramTD0);

				var paramPath = document.createElement("div");
				var dotSteps = wsParameters[i].split(".");

				paramTD0.appendChild(paramPath);
				paramPath.id = "s3-path" + i;
				paramPath.className = "OuterLeftIndent";

				for (k = 0; k < dotSteps.length; k++) {
					var treeViewK = -1;
					var aTreeRootK = -1;

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

					if (this.preparedPathSteps[i][k]["value"].indexOf("[") > 0) {
						paramPathStep.firstChild.nodeValue = this.preparedPathSteps[i][k]["value"]
								.substr(0,
										this.preparedPathSteps[i][k]["value"]
												.indexOf("]"));
						this.preparedPathSteps[i][k]["arrayIndex"] = 1;
						var pathIndexSpan = document.createElement("span");
						pathIndexSpan.id = "step3-arrayspan-" + i + "-" + k;
						var pathIndexText = document.createTextNode("1");
						pathIndexSpan.appendChild(pathIndexText);

						paramPathStep.appendChild(pathIndexSpan);
						pathTextEnd = document.createTextNode("]");
						paramPathStep.appendChild(pathTextEnd);

						// the add-button
						var addButton = document.createElement("span");
						addButton.style.cursor = "pointer";
						var addButtonIMG = document.createElement("img");
						addButtonIMG.src = "../extensions/SMWHalo/skins/webservices/Add.png";
						addButton.appendChild(addButtonIMG);
						var addButtonOnClick = document
								.createAttribute("onclick");
						addButtonOnClick.value = "webServiceSpecial.addParameter("
								+ i + "," + k + ")";
						addButton.setAttributeNode(addButtonOnClick);
						paramPathStep.appendChild(addButton);
					}

					if (i > 0) {
						if (this.preparedPathSteps[i - 1][k] != null) {
							if (paramPathText == "."
									+ this.preparedPathSteps[i - 1][k]["value"]
									|| paramPathText == this.preparedPathSteps[i - 1][k]["value"]) {
								paramPathStep.style.visibility = "hidden";
								treeView = true;
								treeViewK = k;
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
								aTreeRootK = k;

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

								// expandPathStepText = document
								// .createTextNode("+");
								// expandPathStep.appendChild(expandPathStepText);

								var expandIMG = document.createElement("img");
								expandIMG.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
								expandPathStep.appendChild(expandIMG);

								expandPathStep.style.cursor = "pointer";
								paramPathStep.insertBefore(expandPathStep,
										paramPathStep.firstChild);
							}
						}
					}
					if (k == treeViewK && k != aTreeRootK) {
						expandPathStep = document.createElement("span");
						expandIMG = document.createElement("img");
						expandIMG.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
						expandPathStep.appendChild(expandIMG);
						paramPathStep.insertBefore(expandPathStep,
								paramPathStep.firstChild);
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

				if (treeView) {
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

				if (aTreeRoot || treeView) {
					paramTD4.style.visibility = "hidden";
				}

				if (treeView) {
					paramRow.style.display = "none";
				}
				this.parameterContainer.childNodes[0].appendChild(paramRow);
			}

			var parent = $("step3-parameters").parentNode;
			parent.removeChild($("step3-parameters"));
			parent.insertBefore(this.parameterContainer, parent.childNodes[2]);

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
		$("step3-parameters").style.display = "none";

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

		$("step3-parameters").style.display = "block";
		
		this.hidePendingIndicator();
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

		this.showPendingIndicator("step3-go");
		
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
				tO["root"] = "null";
				tO["enabled"] = "null";
				tO["sK"] = "null";
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

			var tempHead = $("step4-results").childNodes[0].childNodes[0]
					.cloneNode(true);
			var tempTable = $("step4-results").childNodes[0].cloneNode(false);
			$("step4-results").removeChild($("step4-results").childNodes[0]);
			$("step4-results").appendChild(tempTable);
			$("step4-results").childNodes[0].appendChild(tempHead);

			this.resultContainer = $("step4-results").cloneNode(true);

			// fill the widgets of step4 with content
			var aTreeRoot;
			var treeView;

			for (i = 0; i < wsResults.length; i++) {
				aTreeRoot = false;
				treeView = false;

				var resultRow = document.createElement("tr");
				resultRow.id = "step4-resultRow-" + i;

				var resultTD1 = document.createElement("td");
				resultTD1.id = "step4-resultTD1-" + i;
				resultRow.appendChild(resultTD1);

				var resultPath = document.createElement("span");
				resultTD1.appendChild(resultPath);

				for (k = 0; k < this.preparedRPathSteps[i].length; k++) {
					var treeViewK = -1;
					var aTreeRootK = -1;

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

					if (this.preparedRPathSteps[i][k]["value"].indexOf("[") > 0) {
						// the input-field
						resultPathStep.firstChild.nodeValue = this.preparedRPathSteps[i][k]["value"]
								.substr(0,
										this.preparedRPathSteps[i][k]["value"]
												.indexOf("]"));
						var pathIndexInput = document.createElement("input");
						pathIndexInput.type = "text";
						pathIndexInput.size = "1";
						pathIndexInput.maxLength = "5";
						pathIndexInput.style.width = "7px";
						pathIndexInput.id = "step4-arrayinput-" + i + "-" + k;
						pathIndexInput.value = "";

						var pathIndexInputOnBlur = document
								.createAttribute("onblur");
						pathIndexInputOnBlur.value = "webServiceSpecial.updateInputBoxes("
								+ i + "," + k + ")";
						pathIndexInput.setAttributeNode(pathIndexInputOnBlur);

						resultPathStep.appendChild(pathIndexInput);
						pathTextEnd = document.createTextNode("]");
						resultPathStep.appendChild(pathTextEnd);

						// the add-button
						var addButton = document.createElement("span");
						addButton.style.cursor = "pointer";
						var addButtonIMG = document.createElement("img");
						addButtonIMG.src = "../extensions/SMWHalo/skins/webservices/Add.png";
						addButton.appendChild(addButtonIMG);
						var addButtonOnClick = document
								.createAttribute("onclick");
						addButtonOnClick.value = "webServiceSpecial.addResultPart("
								+ i + "," + k + ")";
						addButton.setAttributeNode(addButtonOnClick);
						resultPathStep.appendChild(addButton);
					}

					if (i > 0) {
						if (this.preparedRPathSteps[i - 1][k] != null) {
							if (resultPathText == "."
									+ this.preparedRPathSteps[i - 1][k]["value"]
									|| resultPathText == this.preparedRPathSteps[i - 1][k]["value"]) {
								resultPathStep.style.visibility = "hidden";
								treeView = true;
								treeViewK = k;
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
								aTreeRootK = k;

								if (aTreeRootK == treeViewK) {
									this.preparedRPathSteps[i][k]["root"] = "true";
								}

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
								resultPathStep.insertBefore(expandPathStep,
										resultPathStep.firstChild);
							}
						}
					}
					if (k == treeViewK && k != aTreeRootK) {
						var expandPathStep = document.createElement("span");
						var expandIMG = document.createElement("img");
						expandIMG.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
						expandPathStep.appendChild(expandIMG);
						resultPathStep.insertBefore(expandPathStep,
								resultPathStep.firstChild);
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

				if (aTreeRoot || treeView) {
					resultTD2.style.visibility = "hidden";
				}

				if (treeView) {
					resultRow.style.display = "none";
				}
				this.resultContainer.childNodes[0].appendChild(resultRow);
			}

			var parent = $("step4-results").parentNode;
			parent.removeChild($("step4-results"));
			parent.insertBefore(this.resultContainer, parent.childNodes[2]);
			
			this.resultContainer = $("step4-results");

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
		
		this.hidePendingIndicator();
	},

	/**
	 * called when the user finishes step 4 define results initialises the gui
	 * for step-5 define update policy
	 * 
	 * @return
	 */
	processStep4 : function() {
		this.showPendingIndicator("step4-go");
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
	
		this.hidePendingIndicator();
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
		this.showPendingIndicator("step6-go");
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
				if (this.preparedPathSteps[i] != "null") {
					result += "<parameter name=\"" + $("s3-alias" + i).value
							+ "\" ";
					var optional = this.parameterContainer.firstChild.childNodes[i + 1].childNodes[2].firstChild.checked;
					result += " optional=\"" + optional + "\" ";

					var defaultValue = this.parameterContainer.firstChild.childNodes[i + 1].childNodes[3].firstChild.value;
					if (defaultValue != "") {
						if (defaultValue != "") {
							result += " defaultValue=\"" + defaultValue + "\" ";
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
							pathStep = pathStep.substr(0, pathStep
									.lastIndexOf("(") - 1);
						}
						if (pathStep.lastIndexOf("[") > 0) {
							pathStep = pathStep.substring(0, pathStep
									.lastIndexOf("["));
							pathStep += "[";
							pathStep += $("step3-arrayspan-" + i + "-" + k).firstChild.nodeValue;
							pathStep += "]";
						}
						if (pathStep != ".") {
							path += pathStep;
						}
					}
					result += " path=\"" + path + "\" />\n";
				}
			}
			result += "<result name=\"result\" >\n";

			for (i = 0; i < this.preparedRPathSteps.length; i++) {
				if (this.preparedRPathSteps[i] != "null") {
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
						if (rPathStep.lastIndexOf("[") > 0) {
							rPathStep = rPathStep.substring(0, rPathStep
									.lastIndexOf("["));
							rPathStep += "[";
							rPathStep += $("step4-arrayinput-" + i + "-" + k).value;
							rPathStep += "]";
						}
						if (rPathStep != ".") {
							rPath += rPathStep;
						}
					}
					if(rPath.indexOf("Request.CartAddRequest.Items.Item.MerchantItemAttributes.Cuisine") == -1){
					result += "<part name=\""
						+ this.resultContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value
						+ "\" ";
					result += " path=\"" + rPath + "\" />\n";
					}
				}
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
			parameters = this.preparedPathSteps;
			for (i = 0; i < parameters.length; i++) {
				if (this.preparedPathSteps[i] != "null") {
					wsSyntax += "| "
							+ this.parameterContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value
							+ " = [Please enter a value here]\n";
				}
			}

			results = this.preparedRPathSteps;
			for (i = 0; i < results.length; i++) {
				if (this.preparedRPathSteps[i] != "null") {
					wsSyntax += "| ?result."
							+ this.resultContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value
							+ "\n";
				}
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
		alert(request.responseText);
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
		alert(request.responseText);
		var wsName = $("step6-name").value;
		sajax_do_call("smwf_ws_processStep6", [ wsName, this.wwsd ],
				this.processStep6CallBack2.bind(this));

	},

	/**
	 * callback method for step-6
	 * 
	 */
	processStep6CallBack2 : function(request) {
		alert(request.responseText);
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
		alert(request.responseText);
		var container = $("step7-container").cloneNode(false);
		$("step7-container").id = "old-step7-container";
		$("old-step7-container").parentNode.insertBefore(container,
				$("old-step7-container"));
		$("old-step7-container").parentNode
				.removeChild($("old-step7-container"));

		var step7Container = $("step7-container").cloneNode(true);

		var wsNameText = document.createTextNode(document
				.getElementById("step6-name").value);
		$("step7-name").appendChild(wsNameText);

		var rowDiv = document.createElement("div");
		var rowText = document
				.createTextNode("{{#ws: " + $("step6-name").value);
		rowDiv.appendChild(rowText);
		step7Container.appendChild(rowDiv);

		var parameters = this.preparedPathSteps;
		for (i = 0; i < parameters.length; i++) {
			if (this.preparedPathSteps[i] != "null") {
				rowDiv = document.createElement("div");
				rowDiv.className = "OuterLeftIndent";
				rowText = document
						.createTextNode("| "
								+ this.parameterContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value
								+ " = [Please enter a value here]");
				rowDiv.appendChild(rowText);
				step7Container.appendChild(rowDiv);
			}
		}

		var results = this.preparedRPathSteps;
		for (i = 0; i < results.length; i++) {
			if (this.preparedRPathSteps[i] != "null") {
				rowDiv = document.createElement("div");
				rowDiv.className = "OuterLeftIndent";
				rowText = document
						.createTextNode("| ?result."
								+ this.resultContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value);
				rowDiv.appendChild(rowText);
				step7Container.appendChild(rowDiv);
			}
		}

		rowDiv = document.createElement("div");
		rowText = document.createTextNode("}}");
		rowDiv.appendChild(rowText);
		step7Container.appendChild(rowDiv);

		var parentOf = $("step7-container").parentNode;
		parentOf.insertBefore(step7Container, $("step7-container"));
		parentOf.removeChild(parentOf.childNodes[6]);

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
	
		this.hidePendingIndicator();
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
		var aliasesObject = new Object();

		for (i = 0; i < paramCount; i++) {
			if (this.preparedPathSteps[i] != "null") {
				var alias = this.parameterContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value;
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

				var goon = true;
				var aliasTemp = alias;
				var k = 0;

				while (goon) {
					if (aliasesObject[aliasTemp] != 1) {
						goon = false;
						alias = aliasTemp;
						aliasesObject[alias] = 1;
					} else {
						aliasTemp = alias + "-" + k;
						k++;
					}
				}

				this.parameterContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value = alias;
				aliases.push(alias);
			}
		}
	},

	generateResultAliases : function() {
		var resultsCount = this.preparedRPathSteps.length;

		var aliases = new Array();
		var aliasesObject = new Object();

		for (i = 0; i < resultsCount; i++) {
			if (this.preparedRPathSteps[i] != "null") {
				var alias = this.resultContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value;
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

				var goon = true;
				var aliasTemp = alias;
				var k = 0;

				while (goon) {
					if (aliasesObject[aliasTemp] != 1) {
						goon = false;
						alias = aliasTemp;
						aliasesObject[alias] = 1;
					} else {
						aliasTemp = alias + "-" + k;
						k++;
					}
				}

				this.resultContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value = alias;
				aliases.push(alias);
			}
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
		// find position where to insert the new rows

		for ( var m = i + 1; m < this.preparedPathSteps.length; m++) {
			if (this.preparedPathSteps[m] != "null") {
				if (this.preparedPathSteps[m][k]["value"] == this.preparedPathSteps[i][k]["value"]) {
					appendIndex = m;
				}
			}
		}

		// get nodes to insert
		var goon = true;
		var appendRows = new Array();
		var appendRowsIndex = i;

		while (goon) {
			var tAR = $("step3-paramRow-" + appendRowsIndex);
			appendRows.push(tAR);
			if (this.preparedPathSteps[appendRowsIndex][k]["i"] != "null") {
				appendRowsIndex = this.preparedPathSteps[appendRowsIndex][k]["i"];
			} else {
				goon = false;
			}
		}

		// create new row
		var newI = this.preparedPathSteps.length;
		appendRowsIndex = i;

		for (m = 0; m < appendRows.length; m++) {
			var appendRow = appendRows[m].cloneNode(true);

			if (appendIndex == this.preparedPathSteps.length - 1) {
				$("step3-parameters").childNodes[0].appendChild(appendRow);
			} else {
				$("step3-parameters").childNodes[0].insertBefore(appendRow,
						$("step3-paramRow-" + (appendIndex + 1)));
			}

			appendRow.id = "step3-paramRow-" + newI;

			appendRow.childNodes[0].id = "step3-paramTD0-" + newI;
			var pathSteps = appendRow.childNodes[0].childNodes[0].childNodes;

			appendRow.childNodes[0].childNodes[0].id = "s3-path" + newI;

			var objectRow = new Array();
			for (r = 0; r < pathSteps.length; r++) {
				pathSteps[r].id = "s3-pathstep-" + newI + "-" + r;
				if (r < k) {
					pathSteps[r].style.visibility = "hidden";
				}
				// an aTreeRoot
				if (pathSteps[r].childNodes.length == 2) {
					pathSteps[r].firstChild.id = "step3-expand-" + newI + "-"
							+ r;
					pathSteps[r].firstChild.setAttribute("onclick",
							"webServiceSpecial.expandParamPathStep(" + newI
									+ "," + r + ")");
				} // an array
				else if (pathSteps[r].childNodes.length == 4) {
					pathSteps[r].childNodes[1].id = "step3-arrayspan-" + newI
							+ "-" + r;
					pathSteps[r].childNodes[3].setAttribute("onclick",
							"webServiceSpecial.removeParameter(" + newI + ","
									+ r + ")");

					pathSteps[r].childNodes[3].firstChild.src = "../extensions/SMWHalo/skins/webservices/delete.png";

					this.preparedPathSteps[i + m][r]["arrayIndex"] = (this.preparedPathSteps[i
							+ m][r]["arrayIndex"] * 1) + 1;
					pathSteps[r].childNodes[1].firstChild.nodeValue = this.preparedPathSteps[i
							+ m][r]["arrayIndex"];
				} // both
				else if (pathSteps[r].childNodes.length == 5) {
					pathSteps[r].childNodes[2].id = "step3-arrayspan-" + newI
							+ "-" + r;

					pathSteps[r].firstChild.id = "step3-expand-" + newI + "-"
							+ r;
					pathSteps[r].firstChild.src = "../extensions/SMWHalo/skins/webservices/delete.gif";
					pathSteps[r].firstChild.setAttribute("onclick",
							"webServiceSpecial.expandParamPathStep(" + newI
									+ "," + r + ")");

					pathSteps[r].childNodes[4].firstChild.src = "../extensions/SMWHalo/skins/webservices/delete.png";

					pathSteps[r].childNodes[4].setAttribute("onclick",
							"webServiceSpecial.removeParameter(" + newI + ","
									+ r + ")");
					this.preparedPathSteps[i + m][r]["arrayIndex"] = (this.preparedPathSteps[i
							+ m][r]["arrayIndex"] * 1) + 1;
					this.preparedPathSteps[i + m][r]["arrayIndexUsers"]
							.push(newI + "-" + r);

					pathSteps[r].childNodes[2].firstChild.nodeValue = this.preparedPathSteps[i
							+ m][r]["arrayIndex"];

				}

				var tO = new Object();
				tO["value"] = this.preparedPathSteps[i + m][r]["value"];
				if (this.preparedPathSteps[i + m][r]["i"] != "null") {
					tO["i"] = newI + 1;
					tO["k"] = this.preparedPathSteps[i + m][r]["k"];
				} else {
					tO["i"] = "null";
					tO["k"] = "null";
				}
				if (this.preparedPathSteps[i + m][r]["arrayIndex"] != "null") {
					tO["arrayIndexOrigin"] = (i + m) + "-" + r;
					tO["arrayIndex"] = this.preparedPathSteps[i + m][r]["arrayIndex"];
				}
				tO["root"] = this.preparedPathSteps[i + m][r]["root"];

				objectRow.push(tO);

			}

			appendRow.childNodes[1].id = "step3-paramTD1-" + newI;

			appendRow.childNodes[1].childNodes[0].id = "s3-alias" + newI;

			appendRow.childNodes[2].id = "step3-paramTD2-" + newI;
			appendRow.childNodes[2].childNodes[0].id = "s3-optional-true"
					+ newI;
			appendRow.childNodes[2].childNodes[0].name = "s3-optional-radio"
					+ newI;
			appendRow.childNodes[2].childNodes[3].id = "s3-optional-false"
					+ newI;
			appendRow.childNodes[2].childNodes[3].name = "s3-optional-radio"
					+ newI;

			appendRow.childNodes[3].id = "step3-paramTD3-" + newI;
			appendRow.childNodes[3].childNodes[0].id = "s3-default" + newI;

			newI += 1;
			appendIndex += 1;

			this.preparedPathSteps.push(objectRow);

		}
	},

	removeParameter : function(i, k) {
		var goon = true;

		while (goon) {
			$("step3-paramRow-" + i).parentNode.removeChild($("step3-paramRow-"
					+ i));
			var iTemp = i;

			if (this.preparedPathSteps[i][k]["arrayIndex"] != "null") {
				var tempArrayIndexO = this.preparedPathSteps[i][k]["arrayIndexOrigin"];
				var tempArrayIndex = this.preparedPathSteps[i][k]["arrayIndex"];

				s = tempArrayIndexO.substr(0, tempArrayIndexO.indexOf("-"));
				w = tempArrayIndexO.substr(tempArrayIndexO.indexOf("-") + 1,
						tempArrayIndexO.length);
				this.preparedPathSteps[s][w]["arrayIndex"] = this.preparedPathSteps[s][w]["arrayIndex"] - 1;

				var users = this.preparedPathSteps[s][w]["arrayIndexUsers"];

				for ( var c = 0; c < users.length; c++) {
					s = users[c].substr(0, users[c].indexOf("-"));
					w = users[c].substr(users[c].indexOf("-") + 1,
							users[c].length);
					if (this.preparedPathSteps[s][w]["arrayIndex"] * 1 > tempArrayIndex) {
						this.preparedPathSteps[s][w]["arrayIndex"] = this.preparedPathSteps[s][w]["arrayIndex"] * 1 - 1
						if ($("s3-pathstep-" + s + "-" + w).childNodes.length == 4) {
							$("s3-pathstep-" + s + "-" + w).childNodes[1].firstChild.nodeValue = this.preparedPathSteps[s][w]["arrayIndex"];
						} else {
							$("s3-pathstep-" + s + "-" + w).childNodes[2].firstChild.nodeValue = this.preparedPathSteps[s][w]["arrayIndex"];
						}
					}
				}
			}

			if (this.preparedPathSteps[i][k]["i"] != "null") {
				i = this.preparedPathSteps[i][k]["i"];
			} else {
				goon = false;
			}
			this.preparedPathSteps[iTemp] = "null";
		}
	},

	/**
	 * this method is responsible for removing result parts in step 4
	 * 
	 * @param int
	 *            i index of the result-part where the add-button was pressed
	 * 
	 */
	removeResultPart : function(i, k) {
		var goon = true;

		// var removed = 0;

		while (goon) {
			$("step4-resultRow-" + i).parentNode
					.removeChild($("step4-resultRow-" + i));
			var iTemp = i;
			if (this.preparedRPathSteps[i][k]["i"] != "null") {
				i = this.preparedRPathSteps[i][k]["i"];
			} else {
				goon = false;
			}
			this.preparedRPathSteps[iTemp] = "null";
		}
	},

	addResultPart : function(i, k) {
		// find position where to insert the new rows
		var appendIndex = -1;

		for ( var m = i + 1; m < this.preparedRPathSteps.length; m++) {
			if (this.preparedRPathSteps[m] != "null") {
				if (this.preparedRPathSteps[m][k]["value"] == this.preparedRPathSteps[i][k]["value"]) {
					appendIndex = m;
				}
			}
		}

		// get nodes to insert
		var goon = true;
		var appendRows = new Array();
		var appendRowsIndex = i;

		while (goon) {
			var tAR = $("step4-resultRow-" + appendRowsIndex);
			appendRows.push(tAR);
			if (this.preparedRPathSteps[appendRowsIndex][k]["i"] != "null") {
				appendRowsIndex = this.preparedRPathSteps[appendRowsIndex][k]["i"];
			} else {
				goon = false;
			}
		}

		// create new row
		var newI = this.preparedRPathSteps.length;
		appendRowsIndex = i;

		for (m = 0; m < appendRows.length; m++) {
			var appendRow = appendRows[m].cloneNode(true);

			if (appendIndex == this.preparedRPathSteps.length - 1) {
				$("step4-results").childNodes[0].appendChild(appendRow);
			} else {
				$("step4-results").childNodes[0].insertBefore(appendRow,
						$("step4-resultRow-" + (appendIndex + 1)));
			}

			appendRow.id = "step4-resultRow-" + newI;

			appendRow.childNodes[0].id = "step4-resultTD1-" + newI;
			var pathSteps = appendRow.childNodes[0].childNodes[0].childNodes;

			appendRow.childNodes[0].childNodes[0].id = "s4-path" + newI;

			var objectRow = new Array();
			for (r = 0; r < pathSteps.length; r++) {
				pathSteps[r].id = "s4-pathstep-" + newI + "-" + r;
				if (r < k) {
					pathSteps[r].style.visibility = "hidden";
				}
				// an aTreeRoot
				if (pathSteps[r].childNodes.length == 2) {
					pathSteps[r].firstChild.id = "step4-expand-" + newI + "-"
							+ r;
					pathSteps[r].firstChild.setAttribute("onclick",
							"webServiceSpecial.expandResultPathStep(" + newI
									+ "," + r + ")");
					if (appendRows[m].childNodes[0].childNodes[0].childNodes[r].firstChild.id == "step4-expand-"
							+ (i * 1 + m) + "-" + r) {
						$("step4-expand-" + newI + "-" + r).expanded = $("step4-expand-"
								+ (i * 1 + m) + "-" + r).expanded;
					}
				} // an array
				else if (pathSteps[r].childNodes.length == 4) {
					pathSteps[r].childNodes[1].id = "step4-arrayinput-" + newI
							+ "-" + r;
					pathSteps[r].childNodes[3].setAttribute("onclick",
							"webServiceSpecial.addResultPart(" + newI + "," + r
									+ ")");

					pathSteps[r].childNodes[1].setAttribute("onblur",
							"webServiceSpecial.updateInputBoxes(" + newI + ","
									+ r + ")");

					pathSteps[r].childNodes[3].firstChild.src = "../extensions/SMWHalo/skins/webservices/delete.png";
				} // both
				else if (pathSteps[r].childNodes.length == 5) {
					pathSteps[r].childNodes[2].id = "step4-arrayinput-" + newI
							+ "-" + r;

					pathSteps[r].firstChild.id = "step4-expand-" + newI + "-"
							+ r;
					pathSteps[r].firstChild.src = "../extensions/SMWHalo/skins/webservices/delete.gif";
					pathSteps[r].firstChild.setAttribute("onclick",
							"webServiceSpecial.expandResultPathStep(" + newI
									+ "," + r + ")");

					pathSteps[r].childNodes[2].setAttribute("onblur",
							"webServiceSpecial.updateInputBoxes(" + newI + ","
									+ r + ")");

					pathSteps[r].childNodes[4].firstChild.src = "../extensions/SMWHalo/skins/webservices/delete.png";
					if (appendRows[m].childNodes[0].childNodes[0].childNodes[r].firstChild.id == "step4-expand-"
							+ (i * 1 + m) + "-" + r) {
						$("step4-expand-" + newI + "-" + r).expanded = $("step4-expand-"
								+ (i * 1 + m) + "-" + r).expanded;
					}
					pathSteps[r].childNodes[4].setAttribute("onclick",
							"webServiceSpecial.removeResultPart(" + newI + ","
									+ r + ")");
				}

				var tO = new Object();
				tO["value"] = this.preparedRPathSteps[i + m][r]["value"];
				if (this.preparedRPathSteps[i + m][r]["i"] != "null") {
					tO["i"] = newI + 1;
					tO["k"] = this.preparedRPathSteps[i + m][r]["k"];
				} else {
					tO["i"] = "null";
					tO["k"] = "null";
				}
				tO["root"] = this.preparedRPathSteps[i + m][r]["root"];

				objectRow.push(tO);

			}

			appendRow.childNodes[1].id = "step4-resultTD2-" + newI;

			appendRow.childNodes[1].childNodes[0].id = "s4-alias" + newI;

			// insert element
			// todo: remove >=;

			newI += 1;
			appendIndex += 1;

			this.preparedRPathSteps.push(objectRow);

		}
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
		i = i * 1;
		k = k * 1;

		this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[k].firstChild
				.setAttribute("onclick",
						"webServiceSpecial.contractParamPathStep(\"" + i
								+ "\",\"" + k + "\")");
		this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[k].firstChild.firstChild.src = "../extensions/SMWHalo/skins/webservices/Minus.gif";
		this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[k].firstChild.expanded = true;

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
					this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[m].style.visibility = "visible";
					if (this.preparedPathSteps[i][m]["i"] != "null") {
						if ($("step3-expand-" + i + "-" + m).expanded) {
							this.expandParamPathStep(i, m);
						}
						m = this.preparedPathSteps[i].length;
						complete = false;
					}
				}
			}
			if (display) {
				this.parameterContainer.firstChild.childNodes[i + 1].style.display = "";

				if (complete) {
					this.parameterContainer.firstChild.childNodes[i + 1].childNodes[1].style.visibility = "visible";
					this.parameterContainer.firstChild.childNodes[i + 1].childNodes[2].style.visibility = "visible";
					this.parameterContainer.firstChild.childNodes[i + 1].childNodes[3].style.visibility = "visible";
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

		i = i * 1;
		k = k * 1;

		this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[k].firstChild
				.setAttribute("onclick",
						"webServiceSpecial.expandParamPathStep(\"" + i
								+ "\",\"" + k

								+ "\")");
		this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[k].firstChild.firstChild.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
		this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[k].firstChild.expanded = false;

		for ( var m = k * 1 + 1; m < this.preparedPathSteps[i].length; m++) {
			this.parameterContainer.firstChild.childNodes[i + 1].firstChild.firstChild.childNodes[m].style.visibility = "hidden";
		}

		var goon = true;
		var root = true;
		while (goon) {
			if (!root) {
				this.parameterContainer.firstChild.childNodes[i + 1].style.display = "none";
			}
			root = false;

			this.parameterContainer.firstChild.childNodes[i + 1].childNodes[1].style.visibility = "hidden";
			this.parameterContainer.firstChild.childNodes[i + 1].childNodes[2].visibility = "hidden";
			this.parameterContainer.firstChild.childNodes[i + 1].childNodes[3].style.visibility = "hidden";

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
		i = i * 1;
		k = k * 1;

		this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k].childNodes[0]
				.setAttribute("onclick",
						"webServiceSpecial.contractResultPathStep(\"" + i
								+ "\",\"" + k + "\")");
		this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k].childNodes[0].firstChild.src = "../extensions/SMWHalo/skins/webservices/Minus.gif";
		this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k].childNodes[0].expanded = "true";

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
					this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[m].style.visibility = "visible";
					if (this.preparedRPathSteps[i][m]["i"] != "null") {
						if (this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[m].childNodes[0].expanded == "true") {
							this.expandResultPathStep(i, m);
						}
						m = this.preparedRPathSteps[i].length;
						complete = false;
					}
				}
			}
			if (display) {
				this.resultContainer.childNodes[0].childNodes[i + 1].style.display = "";

				if (complete) {
					this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[1].style.visibility = "visible";
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
		i = i * 1;
		k = k * 1;

		this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k].childNodes[0]
				.setAttribute("onclick",
						"webServiceSpecial.expandResultPathStep(\"" + i
								+ "\",\"" + k + "\")");

		this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k].childNodes[0].firstChild.src = "../extensions/SMWHalo/skins/webservices/Plus.gif";
		this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[k].childNodes[0].expanded = "false";
		for ( var m = k + 1; m < this.preparedRPathSteps[i].length; m++) {
			this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[0].firstChild.childNodes[m].style.visibility = "hidden";
		}

		var goon = true;
		var root = true;
		while (goon) {
			i = i * 1;
			k = k * 1;
			if (!root) {
				this.resultContainer.childNodes[0].childNodes[i + 1].style.display = "none";
			}
			root = false;

			this.resultContainer.childNodes[0].childNodes[i + 1].childNodes[1].style.visibility = "hidden";

			if (this.preparedRPathSteps[i][k]["i"] != "null") {
				var iTemp = this.preparedRPathSteps[i][k]["i"];
				k = this.preparedRPathSteps[i][k]["k"];
				i = iTemp;
			} else {
				goon = false;
			}
		}
	},

	updateInputBoxes : function(i, k) {
		var inputValue;
		var root = true;
		var goon = true;
		while (goon) {
			if (!root) {
				if ($("s4-pathstep-" + i + "-" + k).childNodes.length == 4) {
					$("s4-pathstep-" + i + "-" + k).childNodes[1].value = rootValue;
				} else if ($("s4-pathstep-" + i + "-" + k).childNodes.length == 5) {
					$("s4-pathstep-" + i + "-" + k).childNodes[2].value = rootValue;
				}
			} else {
				if ($("s4-pathstep-" + i + "-" + k).childNodes.length == 4) {
					rootValue = $("s4-pathstep-" + i + "-" + k).childNodes[1].value;
				} else if ($("s4-pathstep-" + i + "-" + k).childNodes.length == 5) {
					rootValue = $("s4-pathstep-" + i + "-" + k).childNodes[2].value;
				}
				root = false;
			}

			if (this.preparedRPathSteps[i][k]["i"] != "null") {
				var iTemp = this.preparedRPathSteps[i][k]["i"];
				k = this.preparedRPathSteps[i][k]["k"];
				i = iTemp;
			} else {
				goon = false;
			}
		}
	},
	
	/*
	 * Shows the pending indicator on the element with the DOM-ID <onElement>
	 * 
	 * @param string onElement
	 * 			DOM-ID if the element over which the indicator appears
	 */
	showPendingIndicator: function(onElement) {
		this.hidePendingIndicator();
		$(onElement + "-img").style.visibility = "hidden";
		this.pendingIndicator = new OBPendingIndicator($(onElement));
		this.pendingIndicator.show();
		this.pendingIndicator.onElement = onElement;
	},
	
	/*
	 * Hides the pending indicator.
	 */
	hidePendingIndicator: function() {
		if (this.pendingIndicator != null) {
			$(this.pendingIndicator.onElement + "-img").style.visibility = "visible";
			this.pendingIndicator.hide();
			this.pendingIndicator = null;
		}
	},
}

webServiceSpecial = new DefineWebServiceSpecial();
