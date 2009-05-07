/*   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
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
		$("step2-methods").removeAttribute("onclick");

		if ($("step1-protocol-rest").checked) {
			this.processStep1REST();
			return;
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
		if (wsMethods[0].indexOf("todo:handle exceptions") == -1) {
			$("step2").style.display = "none";
			$("menue-step1").className = "ActualMenueStep";
			$("menue-step2").className = "TodoMenueStep";
			this.hideHelp(2);
			$("step1-error").style.display = "";
			this.step = "step1";
			$("errors").style.display = "";
		} else {
			$("step1-protocol-rest").setAttribute("onclick",
					"webServiceSpecial.confirmStep1Change(\"rest\")");
			$("step1-uri").setAttribute("onclick",
					"webServiceSpecial.confirmStep1Change(\"rest\")");
			if (!this.editMode) {
				$("step1-go-img").style.display = "none";
				$("step2-go-img").style.display = "";
			}

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
				option.value = wsMethods[i];
				$("step2-methods").appendChild(option);
			}

			// hide or display widgets of other steps
			$("step2").style.display = "";
			$("menue-step1").className = "DoneMenueStep";
			$("menue-step2").className = "ActualMenueStep";
			this.hideHelp(1);
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

		this.hideHelp(2);
		this.hideHelp(3);
		this.hideHelp(4);
		this.hideHelp(5);
		this.hideHelp(6);

		$("step2a-error").style.display = "none";
		$("step2b-error").style.display = "none";
		$("step3-error").style.display = "none";
		$("step4-error").style.display = "none";
		$("step5-error").style.display = "none";
		$("step6-error").style.display = "none";
		$("step6b-error").style.display = "none";
		$("step6c-error").style.display = "none";

		this.hidePendingIndicator();
	},

	/**
	 * called when the user finishes step 2 choose method
	 * 
	 */
	processStep2 : function() {
		if ($("step1-protocol-rest").checked) {
			this.processStep2REST();
			return;
		}

		if (this.step != "step2") {
			check = confirm(diLanguage.getMessage('smw_wws_proceed'));
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

	processStep2CallBack : function(request) {
		this.processStep2Do(request.responseText, false);
	},

	/**
	 * callback-method for the ajax-call of step 2 this method initializes the
	 * gui for step 3 specify parameters
	 * 
	 * @param request
	 * 
	 */
	processStep2Do : function(parameterString, edit) {
		var wsParameters = parameterString.split(";");

		this.numberOfUsedParameters = 0;
		
		this.firstParameterPathStep = "";
		
		if (!this.editMode) {
			$("step2-go-img").style.display = "none";
			$("step3-go-img").style.display = "";
		}

		$("step2-methods").setAttribute("onclick",
				"webServiceSpecial.confirmStep2Change()");

		this.preparedPathSteps = new Array();
		if (wsParameters[0] == "##no params required##") {
			$("step3").childNodes[1].nodeValue = "3. This method does not ask for any parameters.";
			if (!edit) {
				$("step3").style.display = "";
				$("step3-parameters").style.display = "none";
				$("step3-go-img").style.display = "none";
				this.processStep3();
			}
			return;
		} else {
			if (!edit) {
				$("step3-parameters").style.display = "";
				$("step3-go-img").style.display = "";
			}
		}

		var duplicate = false;
		
		var steps = wsParameters[1].split("/");
		var startK = 1;
		if(steps.length > 1){
			this.firstParameterPathStep = steps[1];
			startK = 2;
		}
		
		for ( var i = 1; i < wsParameters.length; i++) {
			steps = wsParameters[i].split("/");
			var preparedPathStepsDot = new Array();
			for ( var k = startK; k < steps.length; k++) {
				if (steps[k].length == 0) {
					s1 = steps.slice(0, k);
					s2 = steps.slice(k + 1, steps.length);
					steps = s1.concat(s2);
					k -= 1;
					continue;
				}

				var tO = new Object();
				if (steps[k].indexOf("##duplicate") > -1) {
					tO["value"] = steps[k].substr(0, steps[k]
							.indexOf("##duplicate"));
					tO["duplicate"] = true;
					duplicate = true;
				} else if (steps[k].indexOf("##overflow") > -1) {
					tO["value"] = steps[k].substr(0, steps[k]
							.indexOf("##overflow"));
					tO["overflow"] = true;
				} else {
					tO["value"] = steps[k];
				}

				tO["i"] = "null";
				tO["k"] = "null";
				tO["arrayIndex"] = "null";
				tO["arrayIndexOrigin"] = null;
				tO["arrayIndexUsers"] = new Array();
				tO["arrayIndexRoot"] = false;
				preparedPathStepsDot[k-startK] = tO;
			}
			this.preparedPathSteps[i - 1] = preparedPathStepsDot;
		}
		if (duplicate) {
			$("step3-duplicates").style.display = "";
		}

		if (wsParameters[0] != "##handle exceptions##") {
			this.step = "step2";

			$("menue-step2").className = "ActualMenueStep";
			$("menue-step3").className = "TodoMenueStep";

			this.hideHelp(3);

			$("errors").style.display = "";
			$("step2a-error").style.display = "none";
			$("step2b-error").style.display = "none";

			// todo:werden die errors richtig angezeigt?
			if (overflow) {
				$("step2b-error").style.display = "";
			} else {
				$("step2a-error").style.display = "";
			}
		} else {
			wsParameters.shift();
			// clear widgets of step 3
			// prepare table for rest parameters
			$("step3-parameters").childNodes[0].childNodes[0].childNodes[1].style.display = "";
			$("step3-parameters").childNodes[0].childNodes[0].childNodes[2].childNodes[1].style.display = "";

			$("step3-rest-intro").style.display = "none";
			$("step3-parameters").style.display = "";

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

			for (i = 0; i < this.preparedPathSteps.length; i++) {
				treeView = false;
				aTreeRoot = false;
				var paramRow = document.createElement("tr");
				paramRow.style.borderTopStyle = "solid";
				paramRow.id = "step3-paramRow-" + i;

				var paramTD0 = document.createElement("td");
				paramTD0.id = "step3-paramTD0-" + i;
				paramRow.appendChild(paramTD0);

				var paramPath = document.createElement("div");
				paramPath.style.textAlign = "left";

				paramTD0.appendChild(paramPath);
				paramPath.id = "s3-path" + i;
				// paramPath.className = "OuterLeftIndent";

				for (k = 0; k < this.preparedPathSteps[i].length; k++) {
					var treeViewK = -1;
					var aTreeRootK = -1;

					var paramPathStep = document.createElement("span");
					paramPathStep.id = "s3-pathstep-" + i + "-" + k;
					if (aTreeRoot) {
						paramPathStep.style.visibility = "hidden";
					}

					var paramPathText = "";
					// if (k > 0) {
					// paramPathText += "/";
					// }

					paramPathText += this.preparedPathSteps[i][k]["value"];
					paramPathTextNode = document.createTextNode(paramPathText);
					if (this.preparedPathSteps[i][k]["duplicate"]) {
						paramPathStep.style.color = "red";
					}
					paramPathStep.appendChild(paramPathTextNode);
					paramPath.appendChild(paramPathStep);

					// if (this.preparedPathSteps[i][k]["value"].indexOf("[") >
					// 0) {
					// paramPathStep.firstChild.nodeValue =
					// this.preparedPathSteps[i][k]["value"]
					// .substr(0,
					// this.preparedPathSteps[i][k]["value"]
					// .indexOf("]"));
					// this.preparedPathSteps[i][k]["arrayIndex"] = 1;
					// var pathIndexSpan = document.createElement("span");
					// pathIndexSpan.id = "step3-arrayspan-" + i + "-" + k;
					// var pathIndexText = document.createTextNode("1");
					// pathIndexSpan.appendChild(pathIndexText);
					//
					// paramPathStep.appendChild(pathIndexSpan);
					// pathTextEnd = document.createTextNode("]");
					// paramPathStep.appendChild(pathTextEnd);
					//
					// // the add-button
					// var addButton = document.createElement("span");
					// addButton.style.cursor = "pointer";
					// var addButtonIMG = document.createElement("img");
					// addButtonIMG.src = wgScriptPath
					// + "/extensions/DataImport/skins/webservices/Add.png";
					// addButton.appendChild(addButtonIMG);
					//
					// addButtonIMG.i = i;
					// addButtonIMG.k = k;
					// addButtonIMG.addA = true;
					// Event.observe(addButtonIMG, "click",
					// this.addRemoveParameter
					// .bindAsEventListener(this));
					//
					// paramPathStep.appendChild(addButton);
					// }

					if (i < wsParameters.length - 1) {
						if (this.preparedPathSteps[i + 1][k] != null) {
							if (this.preparedPathSteps[i][k]["value"] == this.preparedPathSteps[i + 1][k]["value"]
									|| this.preparedPathSteps[i][k]["value"] == "/"
											+ this.preparedPathSteps[i + 1][k]["value"]) {
								this.preparedPathSteps[i][k]["i"] = i + 1;
								this.preparedPathSteps[i][k]["k"] = k;
								this.preparedPathSteps[i][k]["arrayIndexRoot"] = true;
								aTreeRoot = true;
								aTreeRootK = k;

								var expandPathStep = document
										.createElement("span");
								expandPathStep.id = "step3-expand-" + i + "-"
										+ k;
								expandPathStep.expanded = false;

								var expandIMG = document.createElement("img");
								expandIMG.src = wgScriptPath
										+ "/extensions/DataImport/skins/webservices/plus.gif";
								expandIMG.i = i;
								expandIMG.k = k;
								var el = this.paramPathStepClick
										.bindAsEventListener(this)
								expandIMG.expand = true;
								Event.observe(expandIMG, "click", el);

								expandPathStep.appendChild(expandIMG);

								expandPathStep.style.cursor = "pointer";
								paramPathStep.insertBefore(expandPathStep,
										paramPathStep.firstChild);
							}
						}
					}

					if (i > 0) {
						if (this.preparedPathSteps[i - 1][k] != null) {
							if (this.preparedPathSteps[i][k]["value"] == "/"
									+ this.preparedPathSteps[i - 1][k]["value"]
									|| this.preparedPathSteps[i][k]["value"] == this.preparedPathSteps[i - 1][k]["value"]) {
								paramPathStep.style.visibility = "hidden";
								this.preparedPathSteps[i][k]["arrayIndexRoot"] = false;
								treeView = true;
								treeViewK = k;
							}
						}
					}

					// if (k == treeViewK && k != aTreeRootK) {
					if (k != aTreeRootK) {
						expandPathStep = document.createElement("span");
						expandIMG = document.createElement("img");
						expandIMG.src = wgScriptPath
								+ "/extensions/DataImport/skins/webservices/seperator.gif";
						// expandIMG.style.visibility = "hidden";
						expandPathStep.appendChild(expandIMG);
						paramPathStep.insertBefore(expandPathStep,
								paramPathStep.firstChild);
					}
				}

				var paramTD05 = document.createElement("td");
				paramTD05.id = "step3-paramTD05-" + i;
				paramTD05.style.textAlign = "right";
				paramRow.appendChild(paramTD05);

				var useInput = document.createElement("input");
				useInput.id = "s3-use" + i;
				useInput.type = "checkbox";
				useInput.setAttribute("onclick", "webServiceSpecial.useParameter(event)");
				paramTD05.appendChild(useInput);

				if (aTreeRoot || treeView) {
					paramTD05.style.visibility = "hidden";
				}

				var paramTD1 = document.createElement("td");
				paramTD1.id = "step3-paramTD1-" + i;
				paramRow.appendChild(paramTD1);

				var aliasInput = document.createElement("input");
				aliasInput.id = "s3-alias" + i;
				aliasInput.size = "25";
				aliasInput.maxLength = "40";
				paramTD1.appendChild(aliasInput);

				if (aTreeRoot || treeView) {
					paramTD1.style.visibility = "hidden";
				}

				var paramTD2 = document.createElement("td");
				paramTD2.id = "step3-paramTD2-" + i;
				paramRow.appendChild(paramTD2);

				if (navigator.appName.indexOf("Explorer") != -1) {
					var optionalRadio1 = document
							.createElement("<input type=\"radio\" name=\"s3-optional-radio"
									+ i + "\">");
				} else {
					var optionalRadio1 = document.createElement("input");
					optionalRadio1.type = "radio";
					optionalRadio1.name = "s3-optional-radio" + i;
				}
				optionalRadio1.id = "s3-optional-true" + i;
				optionalRadio1.value = diLanguage.getMessage('smw_wws_yes');
				paramTD2.appendChild(optionalRadio1);

				var optionalRadio1Span = document.createElement("span");
				var optionalRadio1TextY = document.createTextNode(diLanguage
						.getMessage('smw_wws_yes'));
				optionalRadio1Span.appendChild(optionalRadio1TextY);
				paramTD2.appendChild(optionalRadio1Span);

				if (navigator.appName.indexOf("Explorer") != -1) {
					var optionalRadio2 = document
							.createElement("<input type=\"radio\" name=\"s3-optional-radio"
									+ i + "\" checked=\"checked\">");
				} else {
					var optionalRadio2 = document.createElement("input");
					optionalRadio2.type = "radio";
					optionalRadio2.name = "s3-optional-radio" + i;
					optionalRadio2.checked = true;
				}

				optionalRadio2.id = "s3-optional-false" + i;
				optionalRadio2.value = "false";
				paramTD2.appendChild(optionalRadio2);

				var optionalRadio2Span = document.createElement("span");
				var optionalRadio2TextN = document.createTextNode(diLanguage
						.getMessage('smw_wws_no'));
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
				defaultInput.size = "25";
				defaultInput.maxLength = "40";
				paramTD3.appendChild(defaultInput);

				if (aTreeRoot || treeView) {
					paramTD3.style.visibility = "hidden";
				}

				var paramTD4 = document.createElement("td");
				paramTD4.id = "step3-paramTD4-" + i;
				paramRow.appendChild(paramTD4);
				
				var addSubParameterButton = document.createElement("input");
				addSubParameterButton.type = "button";
				addSubParameterButton.value = 
					diLanguage.getMessage('smw_wws_add_subparameters');
				addSubParameterButton.setAttribute("onclick",
						"webServiceSpecial.appendSubParameters(" + (i) + ")");
				paramTD4.appendChild(addSubParameterButton);

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
			var parent = $("step3");
			parent.insertBefore(this.parameterContainer, parent.childNodes[2]);

			// hide or display widgets of other steps
			if (!edit) {
				$("step3").style.display = "";

				$("menue-step2").className = "DoneMenueStep";
				$("menue-step3").className = "ActualMenueStep";

				this.hideHelp(2);

				$("step2a-error").style.display = "none";
				$("step2b-error").style.display = "none";

				$("errors").style.display = "none";

			}
		}

		// hide or display widgets of other steps
		if (!edit) {
			$("step4").style.display = "none";
			$("step5").style.display = "none";
			$("step6").style.display = "none";

			$("menue-step4").className = "TodoMenueStep";
			$("menue-step5").className = "TodoMenueStep";
			$("menue-step6").className = "TodoMenueStep";

			this.hideHelp(4);
			this.hideHelp(5);
			this.hideHelp(6);

			$("step3-error").style.display = "none";
			$("step4-error").style.display = "none";
			$("step5-error").style.display = "none";
			$("step6-error").style.display = "none";
			$("step6b-error").style.display = "none";
			$("step6c-error").style.display = "none";
		}
		// $("step3-parameters").style.display = "";

		this.hidePendingIndicator();
	},

	/**
	 * called when the user finishes step 3 define parameters
	 * 
	 * @return
	 */
	processStep3 : function() {
		if ($("step1-protocol-rest").checked) {
			this.processStep3REST();
			return;
		}

		this.generateParameterAliases(false);
		var method = $("step2-methods").value;
		var uri = $("step1-uri").value;
		var parameters = "";

		this.showPendingIndicator("step3-go");

		sajax_do_call("smwf_ws_processStep3", [ uri, method ],
				this.processStep3CallBack.bind(this));
	},

	processStep3CallBack : function(request) {
		this.processStep3Do(request.responseText, false);
	},

	/**
	 * callback-method for the ajax-call of step 3 this method initializes the
	 * gui for step 4 specify result aliases
	 * 
	 * @param request
	 * 
	 */
	processStep3Do : function(resultsString, edit) {
		this.numberOfUsedResultParts = 0;
		
		var wsResults = resultsString.split(";");

		if (!this.editMode) {
			$("step3-go-img").style.display = "none";
			$("step4-go-img").style.display = "";
		}

		this.preparedRPathSteps = new Array();

		var duplicate = false;
		for ( var i = 1; i < wsResults.length; i++) {
			if (wsResults[i].length > 0) {
				wsResults[i] = wsResults[i];
			} else {
				wsResults[i] = "result";
			}
			var steps = wsResults[i].split("/");
			var preparedPathStepsDot = new Array();
			for ( var k = 0; k < steps.length; k++) {
				if (steps[k].length == 0) {
					s1 = steps.slice(0, k);
					s2 = steps.slice(k + 1, steps.length);
					steps = s1.concat(s2);
					k -= 1;
					continue;
				}

				var tO = new Object();
				if (steps[k].indexOf("##duplicate") > -1) {
					tO["value"] = steps[k].substr(0, steps[k]
							.indexOf("##duplicate"));
					tO["duplicate"] = true;
					duplicate = true;
				} else if (steps[k].indexOf("##overflow") > -1) {
					tO["value"] = steps[k].substr(0, steps[k]
							.indexOf("##overflow"));
					tO["overflow"] = true;
				} else {
					tO["value"] = steps[k];
				}

				tO["i"] = "null";
				tO["k"] = "null";
				tO["root"] = "null";
				tO["arrayIndexRoot"] = false;
				tO["enabled"] = "null";
				tO["sK"] = "null";
				preparedPathStepsDot[k] = tO;
			}

			this.preparedRPathSteps[i - 1] = preparedPathStepsDot;
		}

		if (duplicate) {
			$("step4-duplicates").style.display = "";
		}

		if (wsResults[0] != "todo:handle exceptions") {
			// hide or display widgets of other steps
			$("step3-error").style.display = "";
			$("errors").style.display = "";
		} else {
			wsResults.shift();
			// clear widgets of step 4
			$("step4-results").childNodes[0].childNodes[0].childNodes[0].style.display = "";
			$("step4-results").childNodes[0].childNodes[0].childNodes[1].style.display = "";
			$("step4-results").childNodes[0].childNodes[0].childNodes[2].childNodes[1].style.display = "";
			$("step4-results").childNodes[0].childNodes[0].childNodes[3].style.display = "none";
			$("step4-results").childNodes[0].childNodes[0].childNodes[4].style.display = "none";

			$("step4-rest-intro").style.display = "none";

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

				if (aTreeRoot || treeView) {
					paramTD05.style.visibility = "hidden";
				}

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
					// if (k > 0) {
					// resultPathText += "/";
					// }

					resultPathText += this.preparedRPathSteps[i][k]["value"];
					var resultPathTextNode = document
							.createTextNode(resultPathText);
					if (this.preparedRPathSteps[i][k]["duplicate"]) {
						resultPathStep.style.color = "red";
					}
					resultPathStep.appendChild(resultPathTextNode);
					resultPath.appendChild(resultPathStep);

					// if (this.preparedRPathSteps[i][k]["value"].indexOf("[") >
					// 0) {
					// // the input-field
					// resultPathStep.firstChild.nodeValue =
					// this.preparedRPathSteps[i][k]["value"]
					// .substr(0,
					// this.preparedRPathSteps[i][k]["value"]
					// .indexOf("[") + 1);
					// var pathIndexInput = document.createElement("input");
					// pathIndexInput.type = "text";
					// pathIndexInput.size = "2";
					// pathIndexInput.maxLength = "5";
					// pathIndexInput.style.width = "14px";
					// pathIndexInput.id = "step4-arrayinput-" + i + "-" + k;
					// pathIndexInput.value =
					// this.preparedRPathSteps[i][k]["value"]
					// .substr(
					// this.preparedRPathSteps[i][k]["value"]
					// .indexOf("[") + 1,
					// this.preparedRPathSteps[i][k]["value"]
					// .indexOf("]")
					// - 1
					// - this.preparedRPathSteps[i][k]["value"]
					// .indexOf("["));
					//
					// pathIndexInput.i = i;
					// pathIndexInput.k = k;
					// Event
					// .observe(pathIndexInput, "blur",
					// this.updateInputBoxes
					// .bindAsEventListener(this));
					//
					// resultPathStep.appendChild(pathIndexInput);
					// pathTextEnd = document.createTextNode("]");
					// resultPathStep.appendChild(pathTextEnd);
					//
					// // the add-button
					// var addButton = document.createElement("span");
					// addButton.style.cursor = "pointer";
					// var addButtonIMG = document.createElement("img");
					// addButtonIMG.src = wgScriptPath
					// + "/extensions/DataImport/skins/webservices/Add.png";
					// addButtonIMG.i = i;
					// addButtonIMG.k = k;
					// addButtonIMG.addA = true;
					// Event.observe(addButtonIMG, "click",
					// this.addRemoveResultPart
					// .bindAsEventListener(this));
					//
					// addButton.appendChild(addButtonIMG);
					//
					// resultPathStep.appendChild(addButton);
					// }

					if (i < this.preparedRPathSteps.length - 1) {
						if (this.preparedRPathSteps[i + 1][k] != null) {
							if (this.preparedRPathSteps[i][k]["value"] == this.preparedRPathSteps[i + 1][k]["value"]
									|| this.preparedRPathSteps[i][k]["value"] == "/"
											+ this.preparedRPathSteps[i + 1][k]["value"]) {
								this.preparedRPathSteps[i][k]["i"] = i + 1;
								this.preparedRPathSteps[i][k]["k"] = k;
								aTreeRoot = true;
								this.preparedRPathSteps[i][k]["arrayIndexRoot"] = true;
								aTreeRootK = k;

								if (aTreeRootK == treeViewK) {
									this.preparedRPathSteps[i][k]["root"] = "true";
								}

								var expandPathStep = document
										.createElement("span");
								expandPathStep.id = "step4-expand-" + i + "-"
										+ k;
								expandPathStep.expanded = false;

								var expandIMG = document.createElement("img");
								expandIMG.src = wgScriptPath
										+ "/extensions/DataImport/skins/webservices/plus.gif";
								expandIMG.i = i;
								expandIMG.k = k;
								var el = this.resultPathStepClick
										.bindAsEventListener(this)
								expandIMG.expand = true;
								Event.observe(expandIMG, "click", el);
								expandPathStep.appendChild(expandIMG);

								expandPathStep.style.cursor = "pointer";
								resultPathStep.insertBefore(expandPathStep,
										resultPathStep.firstChild);
								
								//do not display the first parameter path step
							}
						}
					}

					if (i > 0) {
						if (this.preparedRPathSteps[i - 1][k] != null) {
							if (this.preparedRPathSteps[i][k]["value"] == "/"
									+ this.preparedRPathSteps[i - 1][k]["value"]
									|| this.preparedRPathSteps[i][k]["value"] == this.preparedRPathSteps[i - 1][k]["value"]) {
								resultPathStep.style.visibility = "hidden";
								this.preparedRPathSteps[i][k]["arrayIndexRoot"] = false;
								treeView = true;
								treeViewK = k;
							}
						}
					}

					// if (k == treeViewK && k != aTreeRootK) {
					if (k != aTreeRootK) {
						var expandPathStep = document.createElement("span");
						var expandIMG = document.createElement("img");
						expandIMG.src = wgScriptPath
								+ "/extensions/DataImport/skins/webservices/seperator.gif";
						// expandIMG.style.visibility = "hidden";
						expandPathStep.appendChild(expandIMG);
						resultPathStep.insertBefore(expandPathStep,
								resultPathStep.firstChild);
					}
				}

				resultPath.id = "s4-path" + i;
				resultTD1.appendChild(resultPath);

				var resultTD05 = document.createElement("td");
				resultTD05.id = "step4-resultTD05-" + i;
				resultTD05.style.textAlign = "right";
				resultRow.appendChild(resultTD05);

				var useInput = document.createElement("input");
				useInput.setAttribute("onclick", "webServiceSpecial.useResultPart(event)");
				useInput.id = "s4-use" + i;
				useInput.type = "checkbox";
				resultTD05.appendChild(useInput);

				if (aTreeRoot || treeView) {
					resultTD05.style.visibility = "hidden";
				}

				var resultTD2 = document.createElement("td");
				resultTD2.id = "step4-resultTD2-" + i;
				resultRow.appendChild(resultTD2);

				var aliasInput = document.createElement("input");
				aliasInput.id = "s4-alias" + i;
				aliasInput.size = "25";
				aliasInput.maxLength = "40";
				resultTD2.appendChild(aliasInput);

				if (aTreeRoot || treeView) {
					resultTD2.style.visibility = "hidden";
				}

				var resultTD3 = document.createElement("td");
				resultTD3.id = "step4-resultTD3-" + i;
				resultRow.appendChild(resultTD3);

				var subPathButton = document.createElement("input");
				subPathButton.id = "s4-add-subpath" + i;
				subPathButton.type = "button";
				subPathButton.value = diLanguage
						.getMessage('smw_wws_add_subpath');
				subPathButton.style.cursor = "pointer";
				subPathButton.style.cursor = "pointer";
				subPathButton.setAttribute("onclick",
						"webServiceSpecial.addSubPath(" + i + ")");
				resultTD3.appendChild(subPathButton);

				if (aTreeRoot || treeView) {
					resultTD3.style.visibility = "hidden";
				}

				if (treeView) {
					resultRow.style.display = "none";
				}
				this.resultContainer.childNodes[0].appendChild(resultRow);
			}

			var parent = $("step4-results").parentNode;
			parent.removeChild($("step4-results"));
			var parent = $("step4");
			parent.insertBefore(this.resultContainer, parent.childNodes[3]);

			this.resultContainer = $("step4-results");

			// hide or display widgets of other steps
			if (!edit) {
				$("step4").style.display = "";

				$("menue-step2").className = "DoneMenueStep";
				$("menue-step3").className = "DoneMenueStep";
				if ($("menue-step4").className == "TodoMenueStep") {
					$("menue-step4").className = "ActualMenueStep";
				}

				this.hideHelp(3);

				$("step3-error").style.display = "none";
				$("errors").style.display = "none";
				this.generateParameterAliases();
			}
		}

		// hide or display widgets of other steps
		if (!edit) {
			$("step4-error").style.display = "none";
			$("step5-error").style.display = "none";
			$("step6-error").style.display = "none";
			$("step6b-error").style.display = "none";
			$("step6c-error").style.display = "none";
		}
		this.hidePendingIndicator();
	},

	/**
	 * called when the user finishes step 4 define results initialises the gui
	 * for step-5 define update policy
	 * 
	 * @return
	 */
	processStep4 : function() {
		if ($("step1-protocol-rest").checked) {
			this.processStep4REST();
			return;
		}

		$("step4-go-img").style.display = "none";
		$("step5-go-img").style.display = "";

		this.showPendingIndicator("step4-go");
		// hide or display widgets of other steps
		this.generateResultAliases();
		$("step5").style.display = "";

		$("menue-step4").className = "DoneMenueStep";
		$("menue-step5").className = "ActualMenueStep";
		this.hideHelp(4);
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
		if ($("step1-protocol-rest").checked) {
			this.processStep5REST();
			return;
		}

		$("step5-go-img").style.display = "none";
		$("step6-go-img").style.display = "";

		// hide or display widgets of other steps
		$("step6").style.display = "";

		$("menue-step5").className = "DoneMenueStep";
		$("menue-step6").className = "ActualMenueStep";

		this.hideHelp(5);
		this.hideHelp(6);
		$("step6-name").value = "";
	},

	/**
	 * called after step 6 specify ws-name this method constructs the wwsd
	 */
	processStep6 : function() {
		if ($("step1-protocol-rest").checked) {
			this.processStep6REST();
			return;
		}

		this.generateParameterAliases(false);
		this.generateResultAliases(false);

		this.showPendingIndicator("step6-go");
		if ($("step6-name").value.length > 0) {
			$("errors").style.display = "none";
			$("step6-error").style.display = "none";
			$("step6b-error").style.display = "none";
			$("step6c-error").style.display = "none";

			var result = "<WebService>\n";
			var wsSyntax = "\n<pre>{{#ws: " + $("step6-name").value + "\n";

			var uri = $("step1-uri").value;
			result += "<uri name=\"" + uri + "\" />\n";

			if ($("step1-protocol-soap").checked) {
				result += "<protocol>SOAP</protocol>\n";
			} else {
				result += "<protocol>REST</protocol>\n";
			}

			if ($("step1-auth-yes").checked) {
				result += "<authentication type=\"http\" login=\""
						+ $("step1-username").value + "\" password=\""
						+ $("step1-password").value + "\"/>\n";
			}

			var method = $("step2-methods").value;
			result += "<method name=\"" + method + "\" />\n";

			//offset is necessary due to subparameters
			var offset = 0;
			
			for ( var i = 0; i < this.preparedPathSteps.length; i++) {
				if (this.preparedPathSteps[i] != "null") {
					if ($("s3-use" + i).checked != true) {
						continue;
					}

					var name = this.parameterContainer.firstChild.childNodes[i + 1 + offset].childNodes[2].firstChild.value;
					result += "<parameter name=\"" + name + "\" ";

					wsSyntax += "| " + name
							+ " = [Please enter a value here]\n";

					var optional = this.parameterContainer.firstChild.childNodes[i + 1 + offset].childNodes[3].firstChild.checked;
					result += " optional=\"" + optional + "\" ";

					var defaultValue = this.parameterContainer.firstChild.childNodes[i + 1 + offset].childNodes[4].firstChild.value;
					if (defaultValue != "") {
						if (defaultValue != "") {
							result += " defaultValue=\"" + defaultValue + "\" ";
						}
					}
					var path = "";
					for ( var k = 0; k < this.preparedPathSteps[i].length; k++) {
						var pathStep = "/";

						if (k == 0){
							pathStep = "/" + this.firstParameterPathStep + "/";
						}
						
						pathStep += this.preparedPathSteps[i][k]["value"];
						if (pathStep.lastIndexOf("(") > 0) {
							pathStep = pathStep.substr(0, pathStep
									.lastIndexOf("(") - 1);
						}

						// if (pathStep.lastIndexOf("[") > 0) {
						// pathStep = pathStep.substring(0, pathStep
						// .lastIndexOf("["));
						// pathStep += "[";
						// pathStep += $("step3-arrayspan-" + i + "-" +
						// k).firstChild.nodeValue;
						// pathStep += "]";
						// }
						if (pathStep != "/" && pathStep != "//") {
							path += pathStep;
						}
					}
					result += " path=\"" + path;
					
					//process subpaths
					if(this.parameterContainer.firstChild.childNodes[i + 1 + offset].hasSubParameter){
						result += "\">";
						result += this.parameterContainer.firstChild.childNodes[i + 2 + offset].childNodes[1].childNodes[0].value;
						result += "</parameter>\n";
						offset += 1;
					} else {
						result += "\" />\n";
					}
				}
			}

			result += "<result name=\"result\" >\n";
			var rPath = "";
			var offset = 0;
			var manualResultPart = false;
			for (i = 1; i < this.resultContainer.firstChild.childNodes.length; i++) {
				// only required for arays
				// if (this.preparedRPathSteps[i] != "null") {
				// process subpaths
				if (this.resultContainer.firstChild.childNodes[i].id == "step4-separatorRow") {
					i += 1;
					manualResultPart = true;
					continue;
				}

				if (this.resultContainer.firstChild.childNodes[i].childNodes[1].childNodes[0].type != "checkbox") {
					if (!this.resultContainer.firstChild.childNodes[i].removed) {
						var name = this.resultContainer.firstChild.childNodes[i].childNodes[1].firstChild.value;
						result += "<part name=\"" + name + "\" ";
						wsSyntax += "| ?result." + name + "\n";

						if (rPath == "") {
							if (manualResultPart) {
								rPath = this.resultContainer.firstChild.childNodes[i - 1].firstChild.firstChild.value;
							} else {
								rPath = this.getRPath(i - 2 - offset);
							}
						}

						result += " path=\"" + rPath + "\"";

						if (this.resultContainer.firstChild.childNodes[i].childNodes[0].childNodes[1].value == "xpath") {
							result += " xpath=\"";
						} else {
							result += " json=\"";
						}
						result += this.resultContainer.firstChild.childNodes[i].childNodes[0].childNodes[2].value;
						result += "\"/>\n";
					}
					offset += 1;
					continue;
				}

				// process normal result parts
				if (this.resultContainer.firstChild.childNodes[i].childNodes[1].firstChild.checked != true) {
					rPath = "";
					continue;
				}

				if (manualResultPart) {
					rPath = this.resultContainer.firstChild.childNodes[i].firstChild.firstChild.value;
				} else {
					rPath = this.getRPath(i - 1 - offset);
				}

				var name = this.resultContainer.firstChild.childNodes[i].childNodes[2].firstChild.value;
				result += "<part name=\"" + name + "\" ";

				wsSyntax += "| ?result." + name + "\n";

				result += " path=\"" + rPath + "\"";

				result += " />\n";
				// }
			}
			result += "</result>\n";

			result += this.createWWSDPolicyPart();

			result += "</WebService>";

			this.wwsd = result;
			var wsName = $("step6-name").value;

			wsSyntax += "}}</pre>";
			this.wsSyntax = wsSyntax;

			sajax_do_call("smwf_om_ExistsArticle", [ "webservice:" + wsName ],
					this.processStep6CallBack.bind(this));
		} else {
			$("errors").style.display = "";
			$("step6-error").style.display = "";
			$("step6b-error").style.display = "none";
			$("step6c-error").style.display = "none";
		}

	},

	processStep6CallBack : function(request) {
		if (request.responseText.indexOf("false") >= 0 || this.editMode == true) {
			var wsName = $("step6-name").value;

			wsSyntax = "\n== Syntax for using the WWSD in an article==";
			wsSyntax += this.wsSyntax;

			sajax_do_call("smwf_ws_processStep6", [ wsName, this.wwsd,
					wgUserName, wsSyntax ], this.processStep6CallBack1
					.bind(this));
		} else {
			$("errors").style.display = "";
			$("step6b-error").style.display = "";
			$("step6-error").style.display = "none";
			$("step6c-error").style.display = "none";
			this.hidePendingIndicator();
		}
	},

	/**
	 * callback method for step-6 this method initializes the gui for step which
	 * provides an example for the #ws-syntax
	 * 
	 */
	processStep6CallBack1 : function(request) {
		if (request.responseText.indexOf("true") >= 0) {
			window.location.hash = '#top';

			$("breadcrumb-menue").style.display = "none";

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
			this.wsSyntax = this.wsSyntax.replace(/<pre>/g, "");
			this.wsSyntax = this.wsSyntax.replace(/<\/pre>/g, "");
			rowDiv.innerHTML = this.wsSyntax.replace(/\n/g, "<br/>");

			step7Container.appendChild(rowDiv);

			var parentOf = $("step7-container").parentNode;
			parentOf.insertBefore(step7Container, $("step7-container"));
			parentOf.removeChild(parentOf.childNodes[6]);

			$("step7").style.display = "";
			$("step1").style.display = "none";
			$("step2").style.display = "none";
			$("step3").style.display = "none";
			$("step4").style.display = "none";
			$("step5").style.display = "none";
			$("step6").style.display = "none";
			this.hideHelp(6);

			this.hidePendingIndicator();
		} else {
			$("errors").style.display = "";
			$("step6b-error").style.display = "none";
			$("step6-error").style.display = "none";
			$("step6c-error").style.display = "";
			this.hidePendingIndicator();
		}
	},

	/**
	 * called after step 7 this method initializes the gui for step 1
	 * 
	 */
	processStep7 : function(request) {
		this.editMode = false;

		this.step = "step1";
		$("step7").style.display = "none";
		$("breadcrumb-menue").style.display = "";
		$("menue-step1").className = "ActualMenueStep";
		$("menue-step2").className = "TodoMenueStep";
		$("menue-step3").className = "TodoMenueStep";
		$("menue-step4").className = "TodoMenueStep";
		$("menue-step5").className = "TodoMenueStep";
		$("menue-step6").className = "TodoMenueStep";

		$("step1").style.display = "";
		$("step1-uri").value = "";
		$("step1-uri").value = "";
		$("step1-protocol-rest").checked = "true";
		$("step1-auth-no").checked = "true";
		$("step1-username").value = "";
		$("step1-password").value = "";
		$("step1-go-img").style.display = "";

		$("step1-protocol-soap").removeAttribute("onclick");
		$("step1-protocol-rest").removeAttribute("onclick");
		$("step1-uri").removeAttribute("onclick");

		$("step2").style.display = "none";
		$("step3").style.display = "none";
		$("step4").style.display = "none";
		$("step5").style.display = "none";
		$("step6").style.display = "none";

		this.hideHelp(1);
		this.hideHelp(2);
		this.hideHelp(3);
		this.hideHelp(4);
		this.hideHelp(5);
		this.hideHelp(6);

	},

	/**
	 * this method is responsible for automatic alias-creation in step 3 specify
	 * parameters boolean createAll : create aliases for empty alias-fields
	 * 
	 */
	generateParameterAliases : function(createAll) {
		var aliases = new Array();
		var aliasesObject = new Object();

		var offset = 0;
		for (i = 0; i < this.preparedPathSteps.length; i++) {
			if (this.preparedPathSteps[i] != "null") {
				if (this.parameterContainer.firstChild.childNodes[i + 1
						- offset].childNodes[1].firstChild.checked != true) {
					continue;
				}

				var alias = this.parameterContainer.firstChild.childNodes[i + 1
						- offset].childNodes[2].firstChild.value;
				if (alias.length == 0) {
					alias = this.preparedPathSteps[i][this.preparedPathSteps[i].length - 1]["value"];

					var openBracketPos = alias.lastIndexOf("(");
					if (openBracketPos > 0) {
						alias = alias.substr(0, openBracketPos - 1);
					}

					openBracketPos = alias.lastIndexOf("[");
					if (openBracketPos > 0) {
						alias = alias.substr(0, openBracketPos);
					}

					var dotPos = alias.lastIndexOf("/");
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

				this.parameterContainer.firstChild.childNodes[i + 1 - offset].childNodes[2].firstChild.value = alias;
				aliases.push(alias);
				
				//handle subparameters
				if(this.parameterContainer.firstChild.childNodes[i + 1 - offset].hasSubParameter){
					offset -= 1;
				}
			} else {
				offset += 1;
			}
		}
	},

	generateResultAliases : function(createAll) {
		var offset = 0;
		var aliases = new Array();
		var aliasesObject = new Object();
		var manualResultPart = false;
		var lastAlias = "";
		for (i = 1; i < this.resultContainer.firstChild.childNodes.length; i++) {
			var isSubPath = false;
			if (this.resultContainer.firstChild.childNodes[i].id == "step4-separatorRow") {
				i += 1;
				offset = 0;
				manualResultPart = true;
				continue;
			}
			if (this.resultContainer.firstChild.childNodes[i].childNodes[1].firstChild.type != "checkbox") {
				isSubPath = true;
				var alias = this.resultContainer.firstChild.childNodes[i].childNodes[1].firstChild.value;

				if (alias.length == 0) {
					alias = lastAlias;
				}

				if (alias.length == 0) {
					if (manualResultPart) {
						alias = "alias";
					} else {
						alias = this.preparedRPathSteps[i - 2 - offset][this.preparedRPathSteps[i
								- 2 - offset].length - 1]["value"];
					}
				}
				offset += 1;
			} else {
				if (this.resultContainer.firstChild.childNodes[i].childNodes[1].firstChild.checked != true) {
					lastAlias = "";
					continue;
				}
				var alias = this.resultContainer.firstChild.childNodes[i].childNodes[2].firstChild.value;
				if (alias.length == 0) {
					if (manualResultPart) {
						alias = "alias";
					} else {
						alias = this.preparedRPathSteps[i - 1 - offset][this.preparedRPathSteps[i
								- 1 - offset].length - 1]["value"];
					}
				}
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

			if (isSubPath) {
				this.resultContainer.firstChild.childNodes[i].childNodes[1].firstChild.value = alias;
			} else {
				this.resultContainer.firstChild.childNodes[i].childNodes[2].firstChild.value = alias;
				lastAlias = alias;
			}
			aliases.push(alias);
		}
	},

	/**
	 * this method is responsible for adding new parameters respectivelyin
	 * parameters that are not used any more in step 3
	 * 
	 * @param event
	 *            from the event handler
	 * 
	 * 
	 */
	addRemoveParameter : function(event) {
		var node = Event.element(event);
		var i = node.i * 1;
		var k = node.k * 1;

		if (node.addA) {
			// find position where to insert the new rows

			var goon = true;
			var m = i;
			var nextSibling = null;
			var goon = true;
			var appendIndex = i;

			paramsContainerNode = $("step3-parameters");
			rowIndex = $("step3-paramRow-" + m).rowIndex;
			while (goon) {
				rowIndex += 1;
				nextSibling = paramsContainerNode.firstChild.childNodes[rowIndex];
				if (nextSibling != null) {
					if (this.preparedPathSteps[m] != "null") {
						m = nextSibling.id.substr(nextSibling.id
								.lastIndexOf("-") + 1);
						if (this.preparedPathSteps[m][k] != null) {
							if (this.preparedPathSteps[m][k]["value"] == this.preparedPathSteps[i][k]["value"]) {
								appendIndex = m;
							} else {
								goon = false;
							}
						} else {
							goon = false;
						}
					} else {
						goon = false;
					}
				} else {
					goon = false;
				}
			}

			var rememberedIs = new Array();
			for ( var s = 0; s < k; s++) {
				rememberedIs.push(this.preparedPathSteps[appendIndex][s]["i"]);
				this.preparedPathSteps[appendIndex][s]["i"] = this.preparedPathSteps.length;
				this.preparedPathSteps[appendIndex][s]["k"] = s;
			}

			// get nodes to insert
			var goon = true;
			var appendRows = new Array();
			var appendRowsIndex = i;
			var lastC = i - 1;
			rowIndex = $("step3-paramRow-" + appendRowsIndex).rowIndex;
			while (goon) {
				if (appendRowsIndex == lastC + 1) {
					var tAR = paramsContainerNode.firstChild.childNodes[rowIndex];
					appendRows.push(tAR);
					lastC = appendRowsIndex;
				}
				if (this.preparedPathSteps[appendRowsIndex][k]["i"] != "null") {
					appendRowsIndex = this.preparedPathSteps[appendRowsIndex][k]["i"];
				} else {
					goon = false;
				}
				rowIndex += 1;
			}

			// create new row
			var newI = this.preparedPathSteps.length;

			for (m = 0; m < appendRows.length; m++) {
				var appendRow = appendRows[m].cloneNode(true);

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
						pathSteps[r].firstChild.id = "step3-expand-" + newI
								+ "-" + r;
						pathSteps[r].firstChild.firstChild.i = newI;
						pathSteps[r].firstChild.firstChild.k = r;
						if (pathSteps[r].firstChild.firstChild.expand == null) {
							if (pathSteps[r].firstChild.firstChild.src == wgScriptPath
									+ "/extensions/DataImport/skins/webservices/plus.gif") {
								pathSteps[r].firstChild.firstChild.expand = true;
								pathSteps[r].firstChild.expanded = false;
							} else {
								pathSteps[r].firstChild.firstChild.expand = false;
								pathSteps[r].firstChild.expanded = true;
							}
							var el = this.paramPathStepClick
									.bindAsEventListener(this);
							pathSteps[r].firstChild.firstChild.el = el;
							Event.observe(pathSteps[r].firstChild.firstChild,
									"click", el);
						}
					} // an array
					else if (pathSteps[r].childNodes.length == 4) {
						pathSteps[r].childNodes[1].id = "step3-arrayspan-"
								+ newI + "-" + r;

						if (pathSteps[r].childNodes[3].firstChild.addA == null) {
							var el = this.addRemoveParameter
									.bindAsEventListener(this);
							Event.observe(
									pathSteps[r].childNodes[3].firstChild,
									"click", el);
						}

						pathSteps[r].childNodes[3].firstChild.i = newI;
						pathSteps[r].childNodes[3].firstChild.k = r;
						if (r <= k) {
							pathSteps[r].childNodes[3].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/delete.png";

							pathSteps[r].childNodes[3].firstChild.addA = false;
							pathSteps[r].childNodes[1].firstChild.nodeValue = this.preparedPathSteps[i
									+ m][r]["arrayIndex"] + 1;
						} else {
							pathSteps[r].childNodes[3].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/Add.png";

							pathSteps[r].childNodes[3].firstChild.addA = true;

							pathSteps[r].childNodes[1].firstChild.nodeValue = 1;
						}
						if (r == k) {
							this.preparedPathSteps[i + m][r]["arrayIndexUsers"]
									.push(newI + "-" + r);
							this.preparedPathSteps[i + m][r]["arrayIndex"] = (this.preparedPathSteps[i
									+ m][r]["arrayIndex"] * 1) + 1;
						}
					} // both
					else if (pathSteps[r].childNodes.length == 5) {
						pathSteps[r].firstChild.id = "step3-expand-" + newI
								+ "-" + r;
						pathSteps[r].firstChild.src = wgScriptPath
								+ "/extensions/DataImport/skins/webservices/delete.gif";

						pathSteps[r].firstChild.firstChild.i = newI;
						Event.stopObserving(pathSteps[r].firstChild.firstChild,
								"click", pathSteps[r].firstChild.firstChild.el);

						pathSteps[r].firstChild.firstChild.k = r;
						if (pathSteps[r].firstChild.firstChild.expand == null) {
							if (pathSteps[r].firstChild.firstChild.src
									.indexOf("plus.gif") != -1) {
								pathSteps[r].firstChild.firstChild.expand = true;
								pathSteps[r].firstChild.expanded = false;
							} else {
								pathSteps[r].firstChild.firstChild.expand = false;
								pathSteps[r].firstChild.expanded = true;
							}
							var el = this.paramPathStepClick
									.bindAsEventListener(this);
							Event.observe(pathSteps[r].firstChild.firstChild,
									"click", el);
						}
						if (pathSteps[r].childNodes[4].firstChild.addA == null) {
							var el = this.addRemoveParameter
									.bindAsEventListener(this);
							Event.observe(
									pathSteps[r].childNodes[4].firstChild,
									"click", el);
						}
						if (r <= k) {
							pathSteps[r].childNodes[4].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/delete.png";

							pathSteps[r].childNodes[4].firstChild.i = newI;
							pathSteps[r].childNodes[4].firstChild.k = r;

							pathSteps[r].childNodes[4].firstChild.addA = false;

							pathSteps[r].childNodes[2].firstChild.nodeValue = this.preparedPathSteps[i
									+ m][r]["arrayIndex"] + 1;
						} else {
							pathSteps[r].childNodes[4].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/Add.png";

							pathSteps[r].childNodes[4].firstChild.i = newI;
							pathSteps[r].childNodes[4].firstChild.k = r;

							pathSteps[r].childNodes[4].firstChild.addA = true;

							pathSteps[r].childNodes[2].firstChild.nodeValue = 1;
						}
						if (r == k) {
							this.preparedPathSteps[i + m][r]["arrayIndexUsers"]
									.push(newI + "-" + r);
							this.preparedPathSteps[i + m][r]["arrayIndex"] = (this.preparedPathSteps[i
									+ m][r]["arrayIndex"] * 1) + 1;
						}
						pathSteps[r].childNodes[2].id = "step3-arrayspan-"
								+ newI + "-" + r;
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
					if (m == 0 && r == k) {
						tO["arrayIndexRoot"] = true;
					}
					if (this.preparedPathSteps[i + m][r]["arrayIndex"] != "null") {
						if (r <= k) {
							tO["arrayIndexOrigin"] = (i + m) + "-" + r;
							tO["arrayIndex"] = this.preparedPathSteps[i + m][r]["arrayIndex"];
						} else {
							tO["arrayIndex"] = 1;
							tO["arrayIndexOrigin"] = null;
						}
						tO["arrayIndexUsers"] = new Array();
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

				this.preparedPathSteps.push(objectRow);

				if (nextSibling == null) {
					paramsContainerNode.childNodes[0].appendChild(appendRow);
				} else {
					paramsContainerNode.childNodes[0].insertBefore(appendRow,
							nextSibling);
				}
			}
			for (s = 0; s < rememberedIs.length; s++) {
				this.preparedPathSteps[this.preparedPathSteps.length - 1][s]["i"] = rememberedIs[s];
			}
		} else {
			var goon = true;

			var prevSibling = $("step3-paramRow-" + i).previousSibling;
			var prevI = prevSibling.id
					.substr(prevSibling.id.lastIndexOf("-") + 1);

			paramsContainerNode = $("step3-parameters");
			rowIndex = $("step3-paramRow-" + i).rowIndex;

			while (goon) {
				removeNode = paramsContainerNode.firstChild.childNodes[rowIndex];
				paramsContainerNode.firstChild.removeChild(removeNode);

				var iTemp = i;
				if (this.preparedPathSteps[i][k]["arrayIndex"] != "null") {
					var tempArrayIndexO = this.preparedPathSteps[i][k]["arrayIndexOrigin"];
					var tempArrayIndex = this.preparedPathSteps[i][k]["arrayIndex"];

					s = tempArrayIndexO.substr(0, tempArrayIndexO.indexOf("-"));
					w = tempArrayIndexO.substr(
							tempArrayIndexO.indexOf("-") + 1,
							tempArrayIndexO.length);
					this.preparedPathSteps[s][w]["arrayIndex"] = this.preparedPathSteps[s][w]["arrayIndex"] - 1;

					var users = this.preparedPathSteps[s][w]["arrayIndexUsers"];
					if (users == null) {
						users = new Array();
					}
					// todo: here the performance is improvable by removing the
					// $-access
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

			var nextSibling = prevSibling.nextSibling;
			if (nextSibling == null) {
				var nextI = "null";
			} else {
				var nextI = nextSibling.id.substr(nextSibling.id
						.lastIndexOf("-") + 1);
			}
			for (r = 0; r <= k; r++) {
				if (this.preparedPathSteps[prevI][r]["i"] != "null") {
					if ($("step3-paramRow-"
							+ this.preparedPathSteps[prevI][r]["i"]) == null) {
						this.preparedPathSteps[prevI][r]["i"] = nextI;
					}
				}
			}
		}
	},

	/**
	 * this method is responsible for adding new result parts respectivelyin
	 * result parts that are not used any more in step 4
	 * 
	 * @param event
	 *            from the event handler
	 * 
	 * 
	 */
	addRemoveResultPart : function(event) {
		var node = Event.element(event);
		var i = node.i * 1;
		var k = node.k * 1;
		if (node.addA) {
			// find position where to insert the new rows
			var goon = true;
			var m = i;
			var nextSibling = null;
			var goon = true;
			var appendIndex = i;
			resultsContainerNode = $("step4-results");
			rowIndex = $("step4-resultRow-" + m).rowIndex;
			while (goon) {
				rowIndex += 1;
				nextSibling = resultsContainerNode.firstChild.childNodes[rowIndex];
				if (nextSibling != null) {
					if (this.preparedRPathSteps[m] != "null") {
						m = nextSibling.id.substr(nextSibling.id
								.lastIndexOf("-") + 1);
						if (this.preparedRPathSteps[m][k] != null) {
							if (this.preparedRPathSteps[m][k]["value"] == this.preparedRPathSteps[i][k]["value"]) {
								appendIndex = m;
							} else {
								goon = false;
							}
						} else {
							goon = false;
						}
					} else {
						goon = false;
					}
				} else {
					goon = false;
				}
			}
			var rememberedIs = new Array();
			for ( var s = 0; s < k; s++) {
				rememberedIs.push(this.preparedRPathSteps[appendIndex][s]["i"]);
				this.preparedRPathSteps[appendIndex][s]["i"] = this.preparedRPathSteps.length;
				this.preparedRPathSteps[appendIndex][s]["k"] = s;
			}

			// get nodes to insert
			var goon = true;
			var appendRows = new Array();
			var appendRowsIndex = i;
			var lastC = i - 1;

			rowIndex = $("step4-resultRow-" + appendRowsIndex).rowIndex;
			while (goon) {
				if (appendRowsIndex == lastC + 1) {
					var tAR = resultsContainerNode.firstChild.childNodes[rowIndex];
					appendRows.push(tAR);
					lastC = appendRowsIndex;
				}
				if (this.preparedRPathSteps[appendRowsIndex][k]["i"] != "null") {
					appendRowsIndex = this.preparedRPathSteps[appendRowsIndex][k]["i"];
				} else {
					goon = false;
				}
				rowIndex += 1;
			}

			// create new row
			var newI = this.preparedRPathSteps.length;
			appendRowsIndex = i;

			for (m = 0; m < appendRows.length; m++) {
				var appendRow = appendRows[m].cloneNode(true);

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
						pathSteps[r].firstChild.id = "step4-expand-" + newI
								+ "-" + r;

						pathSteps[r].firstChild.firstChild.i = newI;
						pathSteps[r].firstChild.firstChild.k = r;

						if (pathSteps[r].firstChild.firstChild.expand == null) {
							if (pathSteps[r].firstChild.firstChild.src == wgScriptPath
									+ "/extensions/DataImport/skins/webservices/plus.gif") {
								pathSteps[r].firstChild.firstChild.expand = true;
								pathSteps[r].firstChild.expanded = false;
							} else {
								pathSteps[r].firstChild.firstChild.expand = false;
								pathSteps[r].firstChild.expanded = true;
							}
							var el = this.resultPathStepClick
									.bindAsEventListener(this);
							Event.observe(pathSteps[r].firstChild.firstChild,
									"click", el);
						}
					} // an array
					else if (pathSteps[r].childNodes.length == 4) {
						pathSteps[r].childNodes[1].id = "step4-arrayinput-"
								+ newI + "-" + r;
						pathSteps[r].childNodes[1].setAttribute("onblur",
								"webServiceSpecial.updateInputBoxes(" + newI
										+ "," + r + ")");

						pathSteps[r].childNodes[3].firstChild.i = newI;
						pathSteps[r].childNodes[3].firstChild.k = r;

						if (pathSteps[r].childNodes[3].firstChild.addA == null) {
							var el = this.addRemoveResultPart
									.bindAsEventListener(this);
							Event.observe(
									pathSteps[r].childNodes[3].firstChild,
									"click", el);
						}

						if (r <= k) {
							pathSteps[r].childNodes[3].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/delete.png";

							pathSteps[r].childNodes[3].firstChild.addA = false;
						} else {
							pathSteps[r].childNodes[3].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/Add.png";

							pathSteps[r].childNodes[3].firstChild.addA = true;
						}

						if (pathSteps[r].childNodes[1].i == null) {
							Event.observe(pathSteps[r].childNodes[1], "blur",
									this.updateInputBoxes
											.bindAsEventListener(this));
						}
						pathSteps[r].childNodes[1].i = newI;
						pathSteps[r].childNodes[1].k = r;
					} // both
					else if (pathSteps[r].childNodes.length == 5) {
						pathSteps[r].childNodes[2].id = "step4-arrayinput-"
								+ newI + "-" + r;

						pathSteps[r].childNodes[4].firstChild.i = newI;
						pathSteps[r].childNodes[4].firstChild.k = r;

						if (pathSteps[r].childNodes[4].firstChild.addA == null) {
							var el = this.addRemoveResultPart
									.bindAsEventListener(this);
							Event.observe(
									pathSteps[r].childNodes[4].firstChild,
									"click", el);
						}
						if (r <= k) {
							pathSteps[r].childNodes[4].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/delete.png";
							pathSteps[r].childNodes[4].firstChild.addA = false;
						} else {
							pathSteps[r].childNodes[4].firstChild.src = wgScriptPath
									+ "/extensions/DataImport/skins/webservices/Add.png";

							pathSteps[r].childNodes[4].firstChild.addA = true;
						}

						pathSteps[r].firstChild.id = "step4-expand-" + newI
								+ "-" + r;
						pathSteps[r].firstChild.firstChild.i = newI;
						pathSteps[r].firstChild.firstChild.k = r;

						pathSteps[r].firstChild.src = wgScriptPath
								+ "/extensions/DataImport/skins/webservices/delete.gif";
						if (pathSteps[r].firstChild.firstChild.expand == null) {
							if (pathSteps[r].firstChild.firstChild.src == wgScriptPath
									+ "/extensions/DataImport/skins/webservices/plus.gif") {
								pathSteps[r].firstChild.firstChild.expand = true;
							} else {
								pathSteps[r].firstChild.firstChild.expand = false;
							}
							var el = this.resultPathStepClick
									.bindAsEventListener(this);
							Event.observe(pathSteps[r].firstChild.firstChild,
									"click", el);
						}

						if (pathSteps[r].childNodes[2].i == null) {
							Event.observe(pathSteps[r].childNodes[2], "blur",
									this.updateInputBoxes
											.bindAsEventListener(this));
						}
						pathSteps[r].childNodes[2].i = newI;
						pathSteps[r].childNodes[2].k = r;

						if (appendRows[m].childNodes[0].childNodes[0].childNodes[r].firstChild.id == "step4-expand-"
								+ (i * 1 + m) + "-" + r) {
							pathSteps[r].firstChild.expanded = appendRows[m].childNodes[0].childNodes[0].childNodes[r].firstChild.expanded;
						}
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

					if (m == 0 && k == r) {
						tO["arrayIndexRoot"] = true;
					}

					tO["root"] = this.preparedRPathSteps[i + m][r]["root"];
					objectRow.push(tO);
				}

				appendRow.childNodes[1].id = "step4-resultTD2-" + newI;

				appendRow.childNodes[1].childNodes[0].id = "s4-alias" + newI;

				// insert element

				newI += 1;
				appendIndex += 1;

				this.preparedRPathSteps.push(objectRow);

				if (nextSibling == null) {
					resultsContainerNode.childNodes[0].appendChild(appendRow);
				} else {
					resultsContainerNode.childNodes[0].insertBefore(appendRow,
							nextSibling);
				}
			}

			for (s = 0; s < rememberedIs.length; s++) {
				this.preparedRPathSteps[this.preparedRPathSteps.length - 1][s]["i"] = rememberedIs[s];
			}
		} else {
			var goon = true;

			var prevSibling = $("step4-resultRow-" + i).previousSibling;
			var prevI = prevSibling.id
					.substr(prevSibling.id.lastIndexOf("-") + 1);

			resultsContainerNode = $("step4-results");
			rowIndex = $("step4-resultRow-" + i).rowIndex;
			while (goon) {
				removeNode = resultsContainerNode.firstChild.childNodes[rowIndex];
				resultsContainerNode.firstChild.removeChild(removeNode);
				var iTemp = i;
				if (this.preparedRPathSteps[i][k]["i"] != "null") {
					i = this.preparedRPathSteps[i][k]["i"];
				} else {
					goon = false;
				}
				this.preparedRPathSteps[iTemp] = "null";
			}

			var nextSibling = prevSibling.nextSibling;
			if (nextSibling == null) {
				var nextI = "null";
			} else {
				var nextI = nextSibling.id.substr(nextSibling.id
						.lastIndexOf("-") + 1);
			}

			for (r = 0; r <= k; r++) {
				if (this.preparedRPathSteps[prevI][r]["i"] != "null") {
					if ($("step4-resultRow-"
							+ this.preparedRPathSteps[prevI][r]["i"]) == null) {
						this.preparedRPathSteps[prevI][r]["i"] = nextI;
					}
				}
			}
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
		var key;
		if (window.event) {
			key = window.event.keyCode; // IE
		} else {
			key = event.which;
		}

		if (key == 13) {
			if (step == "step1") {
				this.processStep1();
				this.showPendingIndicator("step1-go");
			} else if (step == "step6") {
				this.processStep6();
				this.showPendingIndicator("step6-go");
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

	/**
	 * used for the click event in the tree view of step 3
	 * 
	 * @param event
	 * @return
	 */
	paramPathStepClick : function(event) {
		var node = Event.element(event);
		Event.stop(event);

		if (node.expand) {
			this.expandParamPathStep(node.i, node.k);
			node.expand = false;
		} else {
			this.contractParamPathStep(node.i, node.k);
			node.expand = true;
		}
	},

	/**
	 * used to expand the elements of the tree view in step 3
	 * 
	 * @param i
	 * @param k
	 * @return
	 */
	expandParamPathStep : function(i, k) {
		i = i * 1;
		k = k * 1;

		var r = $("step3-paramRow-" + i).rowIndex;

		this.parameterContainer.firstChild.childNodes[r].firstChild.firstChild.childNodes[k].firstChild.firstChild.src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/minus.gif";
		this.parameterContainer.firstChild.childNodes[r].firstChild.firstChild.childNodes[k].firstChild.expanded = true;

		var goon = true;
		r = r - 1;
		while (goon) {
			r = r + 1;
			var display = true;
			var complete = true;
			for ( var m = k * 1 + 1; m < this.preparedPathSteps[i].length; m++) {
				var visible = true;
				if (i > 0) {
					if (this.preparedPathSteps[i - 1][m] != null) {
						if (this.preparedPathSteps[i][m]["value"] == this.preparedPathSteps[i - 1][m]["value"]) {
							if (!this.preparedPathSteps[i][m]["arrayIndexRoot"]) {
								m = this.preparedPathSteps[i].length;
								visible = false;
								display = false;
							}
						}
					}
				}
				if (visible) {
					this.parameterContainer.firstChild.childNodes[r].firstChild.firstChild.childNodes[m].style.visibility = "visible";
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
				this.parameterContainer.firstChild.childNodes[r].style.display = "";

				if (complete) {
					this.parameterContainer.firstChild.childNodes[r].childNodes[1].style.visibility = "visible";
					this.parameterContainer.firstChild.childNodes[r].childNodes[2].style.visibility = "visible";
					this.parameterContainer.firstChild.childNodes[r].childNodes[3].style.visibility = "visible";
					this.parameterContainer.firstChild.childNodes[r].childNodes[4].style.visibility = "visible";
					this.parameterContainer.firstChild.childNodes[r].childNodes[5].style.visibility = "visible";
					if(this.parameterContainer.firstChild.childNodes[r].hasSubParameter){
						this.parameterContainer.firstChild.childNodes[r+1].style.display = "";
						r += 1;
					}
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

	/**
	 * used to contract elements of the tree view in step 3
	 * 
	 * @param i
	 * @param k
	 * @return
	 */
	contractParamPathStep : function(i, k) {
		i = i * 1;
		k = k * 1;
		var r = $("step3-paramRow-" + i).rowIndex;

		this.parameterContainer.firstChild.childNodes[r].firstChild.firstChild.childNodes[k].firstChild.firstChild.src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/plus.gif";
		this.parameterContainer.firstChild.childNodes[r].firstChild.firstChild.childNodes[k].firstChild.expanded = false;

		for ( var m = k * 1 + 1; m < this.preparedPathSteps[i].length; m++) {
			this.parameterContainer.firstChild.childNodes[r].firstChild.firstChild.childNodes[m].style.visibility = "hidden";
		}

		var goon = true;
		var root = true;
		r = r - 1;
		while (goon) {
			r = r + 1;
			if (!root) {
				this.parameterContainer.firstChild.childNodes[r].style.display = "none";
			}
			root = false;

			this.parameterContainer.firstChild.childNodes[r].childNodes[1].style.visibility = "hidden";
			this.parameterContainer.firstChild.childNodes[r].childNodes[2].style.visibility = "hidden";
			this.parameterContainer.firstChild.childNodes[r].childNodes[3].style.visibility = "hidden";
			this.parameterContainer.firstChild.childNodes[r].childNodes[4].style.visibility = "hidden";
			this.parameterContainer.firstChild.childNodes[r].childNodes[5].style.visibility = "hidden";
			if(this.parameterContainer.firstChild.childNodes[r].hasSubParameter){
				this.parameterContainer.firstChild.childNodes[r+1].style.display = "none";
				r += 1;
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

	/**
	 * used for the onclick event in the tree view of step 4
	 * 
	 * @param event
	 * @return
	 */
	resultPathStepClick : function(event) {
		var node = Event.element(event);
		Event.stop(event);
		if (node.expand) {
			node.expand = false;
			this.expandResultPathStep(node.i, node.k);
		} else {
			node.expand = true;
			this.contractResultPathStep(node.i, node.k);
		}
	},

	/**
	 * used in step 4 to expand elements of the tree view
	 * 
	 * @param i
	 * @param k
	 * @return
	 */
	expandResultPathStep : function(i, k) {
		i = i * 1;
		k = k * 1;
		var r = $("step4-resultRow-" + i).rowIndex;

		this.resultContainer.childNodes[0].childNodes[r].childNodes[0].firstChild.childNodes[k].childNodes[0].firstChild.src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/minus.gif";
		this.resultContainer.childNodes[0].childNodes[r].childNodes[0].firstChild.childNodes[k].childNodes[0].expanded = "true";

		var goon = true;
		r = r - 1;
		while (goon) {
			var display = true;
			var complete = true;
			r = r + 1;

			for ( var m = k * 1 + 1; m < this.preparedRPathSteps[i].length; m++) {
				var visible = true;
				if (i > 0) {
					if (this.preparedRPathSteps[i - 1][m] != null) {
						if (this.preparedRPathSteps[i][m]["value"] == this.preparedRPathSteps[i - 1][m]["value"]) {
							if (!this.preparedRPathSteps[i][m]["arrayIndexRoot"]) {
								m = this.preparedRPathSteps[i].length;
								visible = false;
								display = false;
							}
						}
					}
				}
				if (visible) {
					this.resultContainer.childNodes[0].childNodes[r].childNodes[0].firstChild.childNodes[m].style.visibility = "visible";
					if (this.preparedRPathSteps[i][m]["i"] != "null") {
						if (this.resultContainer.childNodes[0].childNodes[r].childNodes[0].firstChild.childNodes[m].childNodes[0].expanded == "true") {
							this.expandResultPathStep(i, m);
						}
						m = this.preparedRPathSteps[i].length;
						complete = false;
					}
				}
			}

			var offset = this.resultContainer.childNodes[0].childNodes[r].subPathOffset;

			if (display) {
				this.resultContainer.childNodes[0].childNodes[r].style.display = "";

				if (complete) {
					this.resultContainer.childNodes[0].childNodes[r].childNodes[1].style.visibility = "visible";
					this.resultContainer.childNodes[0].childNodes[r].childNodes[2].style.visibility = "visible";
					this.resultContainer.childNodes[0].childNodes[r].childNodes[3].style.visibility = "visible";

					if (offset != null) {
						for (o = 0; o < offset; o++) {
							if (!this.resultContainer.childNodes[0].childNodes[r
									+ o + 1].removed) {
								this.resultContainer.childNodes[0].childNodes[r
										+ o + 1].style.display = "";
							}
						}
					}
				}
			}

			if (offset != null) {
				r += offset;
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

	/**
	 * used for step 4 to contract elements of the tree view
	 * 
	 * @param i
	 * @param k
	 * @return
	 */
	contractResultPathStep : function(i, k) {
		i = i * 1;
		k = k * 1;
		var r = $("step4-resultRow-" + i).rowIndex;

		this.resultContainer.childNodes[0].childNodes[r].childNodes[0].firstChild.childNodes[k].childNodes[0].firstChild.src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/plus.gif";
		this.resultContainer.childNodes[0].childNodes[r].childNodes[0].firstChild.childNodes[k].childNodes[0].expanded = "false";
		for ( var m = k + 1; m < this.preparedRPathSteps[i].length; m++) {
			this.resultContainer.childNodes[0].childNodes[r].childNodes[0].firstChild.childNodes[m].style.visibility = "hidden";
		}

		var goon = true;
		var root = true;

		r = r - 1;
		while (goon) {
			i = i * 1;
			k = k * 1;
			r = r + 1;

			if (!root) {
				this.resultContainer.childNodes[0].childNodes[r].style.display = "none";
			}
			root = false;

			this.resultContainer.childNodes[0].childNodes[r].childNodes[1].style.visibility = "hidden";
			this.resultContainer.childNodes[0].childNodes[r].childNodes[2].style.visibility = "hidden";
			this.resultContainer.childNodes[0].childNodes[r].childNodes[3].style.visibility = "hidden";

			var offset = this.resultContainer.childNodes[0].childNodes[r].subPathOffset;
			if (offset != null) {
				for (o = 0; o < offset; o++) {
					this.resultContainer.childNodes[0].childNodes[r + o + 1].style.display = "none";
				}
				r += offset;
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

	/**
	 * this method is used in step 4 to update array indexes of path steps that
	 * are hidden
	 * 
	 * @param i
	 * @param k
	 * @return
	 */
	updateInputBoxes : function(event) {
		var node = Event.element(event);
		var i = node.i;
		var k = node.k;
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
	 * @param string onElement DOM-ID if the element over which the indicator
	 * appears
	 */
	showPendingIndicator : function(onElement) {
		this.hidePendingIndicator();
		$(onElement + "-img").style.visibility = "hidden";
		this.pendingIndicator = new OBPendingIndicator($(onElement));
		this.pendingIndicator.show();
		this.pendingIndicator.onElement = onElement;
	},

	/*
	 * Hides the pending indicator.
	 */
	hidePendingIndicator : function() {
		if (this.pendingIndicator != null) {
			$(this.pendingIndicator.onElement + "-img").style.visibility = "visible";
			this.pendingIndicator.hide();
			this.pendingIndicator = null;
		}
	},

	editWWSD : function() {
		var editParameterContainer = $("editparameters");
		if (editParameterContainer == null) {
			return;
		}

		if ($("editparameters").processed) {
			return;
		}

		var editResultContainer = $("editresults");

		this.editMode = true;
		// necessary so that this method will not be called twice
		$("editparameters").processed = true;

		var editParameters = "";

		// this is necessary because firefox splits up long divs into several
		// ones
		for ( var i = 0; i < editParameterContainer.childNodes.length; i++) {
			editParameters += editParameterContainer.childNodes[i].nodeValue;
		}
		editParameters = editParameters.split(";");
		editParameters.pop();

		var protocol = editParameters.shift();
		if (protocol == "soap") {
			var ps2Parameters = "##handle exceptions##";
			var parametersUpdate = new Array();

			for (i = 0; i < editParameters.length; i += 5) {
				var o = new Object();
				o["alias"] = editParameters[i];
				ps2Parameters += ";" + editParameters[i + 1];
				o["optional"] = editParameters[i + 2];
				o["defaultValue"] = editParameters[i + 3];
				o["subParameter"] = editParameters[i + 4];
				parametersUpdate.push(o);
			}
			this.processStep2Do(ps2Parameters, true);
			this.updateParameters(parametersUpdate);
		} else {
			var parametersUpdate = new Array();
			for (i = 0; i < editParameters.length; i += 5) {
				var o = new Object();
				o["alias"] = editParameters[i + 1];
				o["path"] = editParameters[i];
				o["optional"] = editParameters[i + 2];
				o["defaultValue"] = editParameters[i + 3];
				o["subParameter"] = unescape(editParameters[i + 4]);
				parametersUpdate.push(o);
			}
			this.processStep2REST();
			this.updateParametersREST(parametersUpdate);
		}

		var editResults = "";
		for ( var i = 0; i < editResultContainer.childNodes.length; i++) {
			editResults += editResultContainer.childNodes[i].nodeValue;
		}
		editResults = editResults.split(";");
		editResults.pop();
		editResults.shift();

		if (protocol == "soap") {
			var ps3Results = "todo:handle exceptions";
			var resultsUpdate = new Array();
			var resultsUpdateUnmatched = new Array();
			for (i = 0; i < editResults.length; i += 4) {
				var o = new Object();
				o["alias"] = editResults[i];
				o["xpath"] = editResults[i + 2];
				o["json"] = editResults[i + 3];
				if (editResults[i + 1].indexOf("##unmatched") == 0) {
					o["path"] = editResults[i + 1];
					resultsUpdateUnmatched.push(o);
					continue;
				}
				if (o["json"] == "##" && o["xpath"] == "##") {
					ps3Results += ";" + editResults[i + 1];
				}
				resultsUpdate.push(o);
			}
			this.processStep3Do(ps3Results, true);
			this.updateResults(resultsUpdate);
			this.updateResultsUnmatched(resultsUpdateUnmatched);
		} else {
			var resultsUpdate = new Array();
			for (i = 0; i < editResults.length; i += 3) {
				var o = new Object();
				o["alias"] = editResults[i];
				o["format"] = editResults[i + 1];
				o["path"] = editResults[i + 2];
				resultsUpdate.push(o);
			}
			this.processStep3REST();
			this.updateResultsREST(resultsUpdate);
		}

		if (protocol == "soap") {
			$("step1-protocol-rest").setAttribute("onclick",
					"webServiceSpecial.confirmStep1Change(\"rest\")");
			$("step1-uri").setAttribute("onclick",
					"webServiceSpecial.confirmStep1Change(\"rest\")");
			$("step2-methods").setAttribute("onclick",
					"webServiceSpecial.confirmStep2Change()");
		} else {
			$("step1-protocol-soap").setAttribute("onclick",
					"webServiceSpecial.confirmStep1Change(\"soap\")");
		}
	},

	updateParameters : function(updates) {
		
		//offset is necessary bevause of subparameters
		var offset = 0;
		for (i = 0; i < updates.length; i++) {
			if (updates[i]["alias"] != "##") {
				this.numberOfUsedParameters += 1;
				this.parameterContainer.firstChild.childNodes[i + 1 + offset].childNodes[1].firstChild.checked = true;
				this.parameterContainer.firstChild.childNodes[i + 1 + offset].childNodes[2].firstChild.value = updates[i]["alias"];
			}
			if (updates[i]["optional"] == "true") {
				this.parameterContainer.firstChild.childNodes[i + 1 + offset].childNodes[3].firstChild.checked = true;
			}
			if (updates[i]["defaultValue"] != "##") {
				this.parameterContainer.firstChild.childNodes[i + 1 + offset].childNodes[4].firstChild.value = updates[i]["defaultValue"];
			}
			if (updates[i]["subParameter"] != "##") {
				this.appendSubParameters(i);
				this.parameterContainer.firstChild.childNodes[i + 2 + offset].childNodes[1].firstChild.value = updates[i]["subParameter"];
				this.parameterContainer.firstChild.childNodes[i + 2 + offset].style.display = "none";
				offset += 1;
			}
		}
		
		if(this.numberOfUsedParameters > 0){
			$("step-3-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_go.png";
			$("step-3-alias-generate-button").style.cursor = "pointer";
		}
	},

	updateResults : function(updates) {
		var offset = 0;
		for (i = 0; i < updates.length; i++) {
			if (updates[i]["alias"] != "##") {
				if (updates[i]["json"] != "##" || updates[i]["xpath"] != "##") {
					offset += 1;
					this.addSubPath(i - offset);

					if ($("step4-resultRow-" + (i - offset)).firstChild.firstChild.lastChild.style.visibility == "hidden") {
						this.resultContainer.firstChild.childNodes[i + 1].style.display = "none";
					}
					this.resultContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.value = updates[i]["alias"];
					if (updates[i]["json"] != "##") {
						this.resultContainer.firstChild.childNodes[i + 1].childNodes[0].childNodes[1].value = "json";
						this.resultContainer.firstChild.childNodes[i + 1].childNodes[0].childNodes[2].value = updates[i]["json"];
					} else {
						this.resultContainer.firstChild.childNodes[i + 1].childNodes[0].childNodes[1].value = "xpath";
						this.resultContainer.firstChild.childNodes[i + 1].childNodes[0].childNodes[2].value = updates[i]["xpath"];
					}
				} else {
					this.numberOfUsedResultParts += 1;
					this.resultContainer.firstChild.childNodes[i + 1].childNodes[1].firstChild.checked = "true";
					this.resultContainer.firstChild.childNodes[i + 1].childNodes[2].firstChild.value = updates[i]["alias"];
				}
			}
		}
		
		if(this.numberOfUsedResultParts > 0){
			$("step-4-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_go.png";
			$("step-4-alias-generate-button").style.cursor = "pointer";
		}
	},

	displayHelp : function(id) {
		if ($("step1-protocol-rest").checked && 2 <= id && id <= 4) {
			$("step" + id + "-rest-help").style.display = "";
		} else {
			$("step" + id + "-help").style.display = "";
		}

		$("step" + id + "-help-img").getAttributeNode("onclick").nodeValue = "webServiceSpecial.hideHelp("
				+ id + ")";
	},

	hideHelp : function(id) {
		if ($("step1-protocol-rest").checked && 2 <= id && id <= 4) {
			$("step" + id + "-rest-help").style.display = "none";
		}

		$("step" + id + "-help").style.display = "none";

		$("step" + id + "-help-img").getAttributeNode("onclick").nodeValue = "webServiceSpecial.displayHelp("
				+ id + ")";
	},

	showAuthenticationBox : function(what) {
		if (what == diLanguage.getMessage('smw_wws_yes')) {
			$("step1-auth-box").style.display = "";
		} else {
			$("step1-auth-box").style.display = "none";
		}
	},

	addSubPath : function(id) {
		if ($("step4-resultRow-" + id).subPathOffset == null) {
			$("step4-resultRow-" + id).subPathOffset = 1;
			$("step4-resultRow-" + id).tempNextSibling = $("step4-resultRow-"
					+ id).nextSibling;
		} else {
			$("step4-resultRow-" + id).subPathOffset += 1;
		}

		var sid = $("step4-resultRow-" + id).subPathOffset;

		var subPathRow = document.createElement("tr");
		subPathRow.id = "step4-resultRow-sb-" + id + "-" + sid;

		var td0 = document.createElement("td");
		td0.setAttribute("colspan", "2");

		var formatLabel = document.createTextNode("format: ");
		td0.appendChild(formatLabel);

		var format = document.createElement("select");
		format.id = "step4-format-" + id + "-" + sid;

		var xpathOption = document.createElement("option");
		var xpathOptName = document.createTextNode("xpath");
		xpathOption.appendChild(xpathOptName);
		xpathOption.value = "xpath";
		format.appendChild(xpathOption);

		var jsonOption = document.createElement("option");
		var jsonOptName = document.createTextNode("json");
		jsonOption.appendChild(jsonOptName);
		jsonOption.value = "json";
		format.appendChild(jsonOption);

		td0.appendChild(format);

		var subPathInput = document.createElement("input");
		subPathInput.id = "step4-subpath-" + id + "-" + sid;
		subPathInput.style.marginLeft = "15px";
		subPathInput.style.width = "300px";
		td0.appendChild(subPathInput);

		subPathRow.appendChild(td0);

		var td2 = document.createElement("td");

		var subPathInput = document.createElement("input");
		subPathInput.id = "step4-alias-" + id + "-" + sid;
		subPathInput.size = "25";
		td2.appendChild(subPathInput);

		subPathRow.appendChild(td2);

		var td3 = document.createElement("td");

		var removeButton = document.createElement("input");
		removeButton.type = "button";
		removeButton.value = diLanguage.getMessage('smw_wws_remove_subpath');
		removeButton.setAttribute("onclick", "webServiceSpecial.removeSubPath("
				+ id + "," + sid + ")");
		removeButton.style.cursor = "pointer";

		td3.appendChild(removeButton);
		subPathRow.appendChild(td3);

		$("step4-results").childNodes[0].insertBefore(subPathRow,
				$("step4-resultRow-" + id).tempNextSibling);
		
		//handle alias generate icon
		if(this.numberOfUsedResultParts == 0){
			$("step-4-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_go.png";
			$("step-4-alias-generate-button").style.cursor = "pointer";
		}
		this.numberOfUsedResultParts += 1;
	},

	removeSubPath : function(id, sid) {
		$("step4-resultRow-sb-" + id + "-" + sid).style.display = "none";
		$("step4-resultRow-sb-" + id + "-" + sid).removed = true;
		
		//handle alias generate icon
		if(this.numberOfUsedResultParts == 1){
			$("step-4-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_grey.png";
			$("step-4-alias-generate-button").style.cursor = "default";
		}
		this.numberOfUsedResultParts -= 1;
	},

	processStep1REST : function() {
		$("errors").style.display = "none";
		$("step1-error").style.display = "none";
		$("step2a-error").style.display = "none";
		$("step2b-error").style.display = "none";
		$("step3-error").style.display = "none";
		$("step4-error").style.display = "none";
		$("step5-error").style.display = "none";
		$("step6-error").style.display = "none";
		$("step6b-error").style.display = "none";
		$("step6c-error").style.display = "none";

		$("step1-protocol-soap").setAttribute("onclick",
				"webServiceSpecial.confirmStep1Change(\"soap\")");

		$("step2").style.display = "";
		$("menue-step1").className = "DoneMenueStep";
		$("menue-step2").className = "ActualMenueStep";
		this.hideHelp(1);
		$("step1-go-img").style.display = "none";
		$("step2-go-img").style.display = "";

		var existingOptions = $("step2-methods").cloneNode(false);
		$("step2-methods").id = "old-step2-methods";
		$("old-step2-methods").parentNode.insertBefore(existingOptions,
				document.getElementById("old-step2-methods"));
		$("old-step2-methods").parentNode.removeChild($("old-step2-methods"));
		existingOptions.id = "step2-methods";

		var option = document.createElement("option");
		var mName = document.createTextNode("get");
		option.appendChild(mName);
		option.value = "get";
		$("step2-methods").appendChild(option);

		option = document.createElement("option");
		mName = document.createTextNode("post");
		option.appendChild(mName);
		option.value = "post";
		$("step2-methods").appendChild(option);
	},

	processStep2REST : function() {
		$("step3").style.display = "";
		$("menue-step2").className = "DoneMenueStep";
		$("menue-step3").className = "ActualMenueStep";
		this.hideHelp(2);
		$("step2-go-img").style.display = "none";

		$("step3-parameters").childNodes[0].childNodes[0].childNodes[0].childNodes[0].nodeValue = "Path:";
		$("step3-parameters").childNodes[0].childNodes[0]
				.removeChild($("step3-parameters").childNodes[0].childNodes[0].childNodes[1]);
		$("step3-parameters").childNodes[0].childNodes[0].childNodes[1]
				.removeChild($("step3-parameters").childNodes[0].childNodes[0].childNodes[1].childNodes[1]);

		this.appendRESTParameter();
	},

	appendRESTParameter : function() {
		//for subparameter handling
		var offset = 0;
		for(var i = 1; i < $("step3-parameters").childNodes[0].childNodes.length; i++){
			if($("step3-parameters").childNodes[0].childNodes[i].hasSubParameter){
				offset += 1;
			}
		}
		
		var id = $("step3-parameters").childNodes[0].childNodes.length - offset;

		var row = document.createElement("tr");

		// add name-input
		var td = document.createElement("td");
		var input = document.createElement("input");
		input.size = "25";
		td.appendChild(input);
		row.appendChild(td);

		// add alias-input
		td = document.createElement("td");
		input = document.createElement("input");
		input.size = "25";
		td.appendChild(input);
		row.appendChild(td);

		// create optional
		td = document.createElement("td");
		if (navigator.appName.indexOf("Explorer") != -1) {
			input = document
					.createElement("<input type=\"radio\" name=\"s3-optional-radio"
							+ id + "\">");
		} else {
			input = document.createElement("input");
			input.type = "radio";
			input.name = "s3-optional-radio" + id;
		}
		input.value = diLanguage.getMessage('smw_wws_yes');
		td.appendChild(input);
		var text = document
				.createTextNode(diLanguage.getMessage('smw_wws_yes'));
		td.appendChild(text);

		if (navigator.appName.indexOf("Explorer") != -1) {
			input = document
					.createElement("<input type=\"radio\" name=\"s3-optional-radio"
							+ id + "\">");
		} else {
			input = document.createElement("input");
			input.type = "radio";
			input.name = "s3-optional-radio" + id;
		}
		input.value = diLanguage.getMessage('smw_wws_no');
		input.checked = true;
		td.appendChild(input);
		text = document.createTextNode(diLanguage.getMessage('smw_wws_no'));
		td.appendChild(text);
		row.appendChild(td);

		// add default-value-input
		td = document.createElement("td");
		input = document.createElement("input");
		input.size = "25";
		td.appendChild(input);
		row.appendChild(td);

		// add default-value-input
		td = document.createElement("td");
		var select = document.createElement("select");

		var option = document.createElement("option");
		text = document.createTextNode(diLanguage
				.getMessage('smw_wws_add_parameter'));
		option.appendChild(text);
		option.value = diLanguage.getMessage('smw_wws_add_parameter');
		select.appendChild(option);

		option = document.createElement("option");
		text = document.createTextNode(diLanguage
				.getMessage('smw_wws_remove_parameter'));
		option.appendChild(text);
		option.value = diLanguage.getMessage('smw_wws_remove_parameter');
		select.appendChild(option);

		option = document.createElement("option");
		text = document.createTextNode(diLanguage
				.getMessage('smw_wws_add_subparameters'));
		option.appendChild(text);
		option.value = diLanguage.getMessage('smw_wws_add_subparameters');
		select.appendChild(option);

		td.appendChild(select);

		input = document.createElement("input");
		input.type = "button";
		input.value = "OK";
		input.style.cursor = "pointer";
		input.setAttribute("onclick",
				"webServiceSpecial.processRESTParameterButton(" + id + ")");
		td.appendChild(input);

		row.appendChild(td);

		$("step3-parameters").childNodes[0].appendChild(row);
	},

	processRESTParameterButton : function(id) {
		//for subparameter handlingvar offset = 0;
		var offset = 0;
		for(var i = 1; i < $("step3-parameters").childNodes[0].childNodes.length; i++){
			if(i - offset == id){
				id += offset;
				break;
			}
			if($("step3-parameters").childNodes[0].childNodes[i].hasSubParameter){
				offset += 1;
			}
		}
		
		var select = $("step3-parameters").childNodes[0].childNodes[id].childNodes[4].childNodes[0];
		var action = select.value;

		if (action == diLanguage.getMessage('smw_wws_add_parameter')) {
			this.appendRESTParameter();
		} else if (action == diLanguage.getMessage('smw_wws_remove_parameter')) {
			$("step3-parameters").childNodes[0].childNodes[id].removed = true;
			$("step3-parameters").childNodes[0].childNodes[id].style.display = "none";

			if($("step3-parameters").childNodes[0].childNodes[id].hasSubParameter){
				$("step3-parameters").childNodes[0].childNodes[id].hasSubParameter = false;
				$("step3-parameters").childNodes[0].childNodes[id + 1].parentNode
						.removeChild($("step3-parameters").childNodes[0].childNodes[id + 1]);
			}
			
			var remove = true;
			for ( var i = 1; i < $("step3-parameters").childNodes[0].childNodes.length; i++) {
				if (!$("step3-parameters").childNodes[0].childNodes[i].removed) {
					remove = false;
				}
			}

			if (remove) {
				$("step3-rest-intro").style.display = "";
				$("step3-parameters").style.display = "none";
			}
		} else if (action == diLanguage.getMessage('smw_wws_add_subparameters')) {
			this.appendRESTSubParameters(id);
		} else if (action == diLanguage
				.getMessage('smw_wws_remove_subparameters')) {
			this.removeRESTSubParameters(id);
		}
	},

	processStep2REST : function() {
		if (!this.editMode) {
			$("step3").style.display = "";
			$("menue-step2").className = "DoneMenueStep";
			$("menue-step3").className = "ActualMenueStep";
			this.hideHelp(2);
			$("step2-go-img").style.display = "none";
			$("step3-go-img").style.display = "";
		}

		$("step3-duplicates").style.display = "none";

		// clear widgets of step 3
		var tempHead = $("step3-parameters").childNodes[0].childNodes[0]
				.cloneNode(true);
		var tempTable = $("step3-parameters").childNodes[0].cloneNode(false);
		$("step3-parameters").removeChild($("step3-parameters").childNodes[0]);
		$("step3-parameters").appendChild(tempTable);
		$("step3-parameters").childNodes[0].appendChild(tempHead);

		// prepare table for rest parameters
		$("step3-rest-intro").style.display = "";
		if ($("step3-rest-intro").childNodes.length <= 0) {
			var button = document.createElement("input");
			button.setAttribute("type", "button");
			button.setAttribute("value", diLanguage
					.getMessage('smw_wws_add_parameters'));
			button.setAttribute("onclick",
					"webServiceSpecial.displayRestParameterTable()");
			$("step3-rest-intro").appendChild(button);
		}

		$("step3-parameters").style.display = "none";
		$("step3-parameters").childNodes[0].childNodes[0].childNodes[1].style.display = "none";
		$("step3-parameters").childNodes[0].childNodes[0].childNodes[2].childNodes[1].style.display = "none";

		// todo: remove this
		this.appendRESTParameter();
		$("step3-parameters").childNodes[0].childNodes[1].removed = true;
	},

	appendRESTResultPart : function() {
		var id = $("step4-results").childNodes[0].childNodes.length;

		var row = document.createElement("tr");

		// add alias-input
		var td = document.createElement("td");
		var input = document.createElement("input");
		input.size = "25";
		td.appendChild(input);
		row.appendChild(td);

		// add format-input
		td = document.createElement("td");
		var select = document.createElement("select");

		var option = document.createElement("option");
		text = document.createTextNode("xpath");
		option.appendChild(text);
		option.value = "xpath";
		select.appendChild(option);

		option = document.createElement("option");
		text = document.createTextNode("json");
		option.appendChild(text);
		option.value = "json";
		select.appendChild(option);

		td.appendChild(select);
		row.appendChild(td);

		// add subpath-input
		td = document.createElement("td");
		input = document.createElement("input");
		input.size = "70";
		td.appendChild(input);
		row.appendChild(td);

		// add additional buttons
		td = document.createElement("td");
		select = document.createElement("select");

		option = document.createElement("option");
		text = document.createTextNode(diLanguage
				.getMessage('smw_wws_add_resultpart'));
		option.appendChild(text);
		option.value = diLanguage.getMessage('smw_wws_add_resultpart');
		select.appendChild(option);

		option = document.createElement("option");
		text = document.createTextNode(diLanguage
				.getMessage('smw_wws_remove_resultpart'));
		option.appendChild(text);
		option.value = diLanguage.getMessage('smw_wws_remove_resultpart');
		select.appendChild(option);

		td.appendChild(select);

		input = document.createElement("input");
		input.type = "button";
		input.value = "OK";
		input.setAttribute("onclick",
				"webServiceSpecial.processRESTResultPartButton(" + id + ")");
		input.style.cursor = "pointer";
		td.appendChild(input);

		row.appendChild(td);

		$("step4-results").childNodes[0].appendChild(row);
	},

	processStep3REST : function() {
		if (!this.editMode) {
			$("step4").style.display = "";
			$("menue-step3").className = "DoneMenueStep";
			$("menue-step4").className = "ActualMenueStep";
			this.hideHelp(3);
			$("step3-go-img").style.display = "none";
			$("step4-go-img").style.display = "";
		}

		$("step4-duplicates").style.display = "none";

		$("step4-rest-intro").style.display = "";
		if ($("step4-rest-intro").childNodes.length <= 0) {
			var span = document.createElement("span");
			var text = document.createTextNode(diLanguage
					.getMessage('smw_wws_use_complete'));
			span.appendChild(text);
			$("step4-rest-intro").appendChild(span);

			var input = document.createElement("input");
			input.type = "checkbox";
			input.style.marginLeft = "5px";
			input.style.marginRight = "20px";
			$("step4-rest-intro").appendChild(input);

			span = document.createElement("span");
			text = document.createTextNode(diLanguage
					.getMessage('smw_wws_alias'));
			span.appendChild(text);
			$("step4-rest-intro").appendChild(span);

			var input = document.createElement("input");
			input.width = 25;
			input.value = "complete";
			$("step4-rest-intro").appendChild(input);

			var br = document.createElement("br");
			$("step4-rest-intro").appendChild(br);
			var br = document.createElement("br");
			$("step4-rest-intro").appendChild(br);

			var button = document.createElement("input");
			button.setAttribute("type", "button");
			button.setAttribute("value", diLanguage
					.getMessage('smw_wws_add_resultparts'));
			button.setAttribute("onclick",
					"webServiceSpecial.displayRestResultsTable()");
			$("step4-rest-intro").appendChild(button);
		} else {
			$("step4-rest-intro").childNodes[1].checked = false;
			$("step4-rest-intro").childNodes[3].value = "complete";
		}

		$("step4-rest-intro").childNodes[6].style.display = "";

		$("step4-results").style.display = "none";
		var tempHead = $("step4-results").childNodes[0].childNodes[0]
				.cloneNode(true);
		var tempTable = $("step4-results").childNodes[0].cloneNode(false);
		$("step4-results").removeChild($("step4-results").childNodes[0]);
		$("step4-results").appendChild(tempTable);
		$("step4-results").childNodes[0].appendChild(tempHead);

		// prepare table for rest result parts
		$("step4-results").childNodes[0].childNodes[0].childNodes[0].style.display = "none";
		$("step4-results").childNodes[0].childNodes[0].childNodes[1].style.display = "none";
		$("step4-results").childNodes[0].childNodes[0].childNodes[2].childNodes[1].style.display = "none";
		$("step4-results").childNodes[0].childNodes[0].childNodes[3].style.display = "";
		$("step4-results").childNodes[0].childNodes[0].childNodes[4].style.display = "";

		this.appendRESTResultPart();
		$("step4-results").childNodes[0].childNodes[1].removed = true;
	},

	processRESTResultPartButton : function(id) {
		var select = $("step4-results").childNodes[0].childNodes[id].childNodes[3].childNodes[0];
		var action = select.value;

		if (action == diLanguage.getMessage('smw_wws_add_resultpart')) {
			this.appendRESTResultPart();
		} else if (action == diLanguage.getMessage('smw_wws_remove_resultpart')) {
			$("step4-results").childNodes[0].childNodes[id].removed = true;
			$("step4-results").childNodes[0].childNodes[id].style.display = "none";

			var remove = true;
			for ( var i = 1; i < $("step4-results").childNodes[0].childNodes.length; i++) {
				if (!$("step4-results").childNodes[0].childNodes[i].removed) {
					remove = false;
				}
			}

			if (remove) {
				$("step4-rest-intro").childNodes[6].style.display = "";
				$("step4-results").style.display = "none";
			}
		}
	},
	processStep4REST : function() {
		$("step5").style.display = "";
		$("menue-step4").className = "DoneMenueStep";
		$("menue-step5").className = "ActualMenueStep";
		this.hideHelp(4);
		$("step4-go-img").style.display = "none";
		$("step5-go-img").style.display = "";

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

	processStep5REST : function() {
		$("step6").style.display = "";
		$("menue-step5").className = "DoneMenueStep";
		$("menue-step6").className = "ActualMenueStep";
		this.hideHelp(5);
		$("step5-go-img").style.display = "none";
		$("step6-go-img").style.display = "";

		$("step6-name").value = "";
	},

	processStep6REST : function() {
		this.showPendingIndicator("step6-go");

		if ($("step6-name").value.length > 0) {
			$("errors").style.display = "none";
			$("step6-error").style.display = "none";
			$("step6b-error").style.display = "none";
			$("step6c-error").style.display = "none";

			var result = "<WebService>\n";

			var wsSyntax = "\n<pre>{{#ws: " + $("step6-name").value + "\n";
			result += "<uri name=\"" + $("step1-uri").value + "\" />\n";

			result += "<protocol>REST</protocol>\n";

			if ($("step1-auth-yes").checked) {
				result += "<authentication type=\"http\" login=\""
						+ $("step1-username").value + "\" password=\""
						+ $("step1-password").value + "\"/>\n";
			}

			result += "<method name=\"" + $("step2-methods").value + "\" />\n";

			var parameterTable = $("step3-parameters").childNodes[0];
			for ( var i = 1; i < parameterTable.childNodes.length; i++) {
				if (parameterTable.childNodes[i].removed == true) {
					continue;
				}

				if (parameterTable.childNodes[i].childNodes[0].childNodes[0].value == "") {
					continue;
				}
				
				var hasSubParameter = false;
				if(parameterTable.childNodes[i].hasSubParameter){
					hasSubParameter = true;
				}

				var alias = parameterTable.childNodes[i].childNodes[1].childNodes[0].value;
				if (alias == "") {
					alias = parameterTable.childNodes[i].childNodes[0].childNodes[0].value;
				}
				result += "<parameter name=\"" + alias + "\" ";

				wsSyntax += "| " + alias + " = [Please enter a value here]\n";

				var optional = parameterTable.childNodes[i].childNodes[2].firstChild.checked;
				result += " optional=\"" + optional + "\" ";

				var defaultValue = parameterTable.childNodes[i].childNodes[3].firstChild.value;
				if (defaultValue != "") {
					result += " defaultValue=\"" + defaultValue + "\" ";
				}
				result += " path=\""
						+ parameterTable.childNodes[i].childNodes[0].childNodes[0].value;
						
				// process subparameters
				if(hasSubParameter){
					result += "\">";
					result += parameterTable.childNodes[i+1].childNodes[1].childNodes[0].value;
					result += "</parameter>\n";
					i += 1;
				} else {
					result += "\"/>\n";
				}
			}

			result += "<result name=\"result\" >\n";

			//for alias generation
			var rememberedAliases = new Array();
			
			if ($("step4-rest-intro").childNodes[1].checked) {
				var name = $("step4-rest-intro").childNodes[3].value;
				result += "<part name=\"" + name + "\" path=\"\"/>\n";
				wsSyntax += "| ?result." + name + "\n";
				rememberedAliases.push(name);				
			}

			var resultTable = $("step4-results").childNodes[0];
			
			for (i = 1; i < resultTable.childNodes.length; i++) {
				if (resultTable.childNodes[i].removed) {
					continue;
				}

				var name = resultTable.childNodes[i].childNodes[0].firstChild.value;
				if (name == "") {
					name = "alias-" + i;
				}
				
				done = false;
				while(!done){
					done = true;
					for(var k=0; k < rememberedAliases.length; k++){
						if(name == rememberedAliases[k]){
							name = name + "-" + 1;
							done = false;
							break;
						}
					}
				}
				rememberedAliases.push(name);
				
				result += "<part name=\"" + name + "\" ";

				wsSyntax += "| ?result." + name + "\n";

				result += resultTable.childNodes[i].childNodes[1].firstChild.value
						+ "=\"";
				result += resultTable.childNodes[i].childNodes[2].firstChild.value
						+ "\"/>\n";

			}
			result += "</result>\n";

			result += this.createWWSDPolicyPart();

			result += "</WebService>";
			this.wwsd = result;

			var wsName = $("step6-name").value;

			wsSyntax += "}}\n</pre>";
			this.wsSyntax = wsSyntax;
			wsSyntax = "\n== Syntax for using the WWSD in an article==";
			wsSyntax += this.wsSyntax;

			sajax_do_call("smwf_om_ExistsArticle", [ "webservice:" + wsName ],
					this.processStep6CallBack.bind(this));
		} else {
			$("errors").style.display = "";
			$("step6-error").style.display = "";
			$("step6b-error").style.display = "none";
			$("step6c-error").style.display = "none";
			this.hidePendingIndicator("step6-go");
		}
	},

	createWWSDPolicyPart : function() {
		var result = "";
		result += "<displayPolicy>\n";
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
		return result;
	},

	updateParametersREST : function(updates) {
		if (updates.length > 0) {
			this.displayRestParameterTable(false);
			$("step3-parameters").firstChild
					.removeChild($("step3-parameters").firstChild.childNodes[1]);
		}
		
		//for subparameter handling
		var offset = 0;

		for (i = 0; i < updates.length; i++) {
			this.appendRESTParameter();
			$("step3-parameters").firstChild.childNodes[i + 1 + offset].childNodes[0].firstChild.value = updates[i]["path"];
			$("step3-parameters").firstChild.childNodes[i + 1+ offset].childNodes[1].firstChild.value = updates[i]["alias"];
			if (updates[i]["optional"] == "true") {
				$("step3-parameters").firstChild.childNodes[i + 1 + offset].childNodes[2].firstChild.checked = true;
			}
			if (updates[i]["defaultValue"] != "##") {
				$("step3-parameters").firstChild.childNodes[i + 1 + offset].childNodes[3].firstChild.value = updates[i]["defaultValue"];
			}
			if(updates[i]["subParameter"] != "##") {
				this.appendRESTSubParameters(i + 1 + offset);
				offset += 1;
				$("step3-parameters").firstChild.childNodes[i + 1 + offset].childNodes[1].firstChild.value = updates[i]["subParameter"];
			}
		}
	},

	updateResultsREST : function(updates) {
		if (updates.length > 0) {
			this.displayRestResultsTable(false);
			$("step4-results").firstChild
					.removeChild($("step4-results").firstChild.childNodes[1]);
		}
		var offset = 0;
		var pathAdded = false;
		for (i = 0; i < updates.length; i++) {
			if (updates[i]["format"] != "##") {
				this.appendRESTResultPart();
				$("step4-results").firstChild.childNodes[i + 1 - offset].childNodes[0].firstChild.value = updates[i]["alias"];
				$("step4-results").firstChild.childNodes[i + 1 - offset].childNodes[1].firstChild.value = updates[i]["format"];
				if (updates[i]["path"] != "##") {
					$("step4-results").firstChild.childNodes[i + 1 - offset].childNodes[2].firstChild.value = updates[i]["path"];
				}
				pathAdded = true;
			} else {
				offset += 1;
				$("step4-rest-intro").childNodes[1].checked = true;
				$("step4-rest-intro").childNodes[3].value = updates[i]["alias"];
			}
		}

		// only complete results were added
		// and the table has to be hidden again
		if (!pathAdded) {
			$("step4-rest-intro").childNodes[6].style.display = "";
			$("step4-results").style.display = "none";
		}
	},

	confirmStep1Change : function(protocol) {
		check = confirm(diLanguage.getMessage('smw_wws_proceed'));
		if (check == false) {
			if (protocol == "soap") {
				$("step1-protocol-rest").checked = true;
			} else {
				$("step1-protocol-soap").checked = true;
			}

			$("step1-uri").blur();
			return;

		}

		this.processStep7();
		if (protocol == "soap") {
			$("step1-protocol-soap").checked = true;
		}
	},

	confirmStep2Change : function() {
		check = confirm(diLanguage.getMessage('smw_wws_proceed'));
		if (check == false) {
			$("step2-methods").blur();
			return;
		}
		$("step2").style.display = "none";
		$("step3").style.display = "none";
		$("step4").style.display = "none";
		$("step5").style.display = "none";
		$("step6").style.display = "none";
		$("step1-go-img").style.display = "";
		this.processStep1();
	},

	displayRestParameterTable : function() {
		$("step3-rest-intro").style.display = "none";
		$("step3-parameters").style.display = "";
		if ($("step3-parameters").childNodes[0].childNodes[1] == null) {
			this.appendRESTParameter();
		}
		$("step3-parameters").childNodes[0].childNodes[1].style.display = "";
		$("step3-parameters").childNodes[0].childNodes[1].removed = false;
	},

	displayRestResultsTable : function() {
		$("step4-rest-intro").childNodes[6].style.display = "none";
		if ($("step4-results").childNodes[0].childNodes[1] == null) {
			this.appendRESTResultPart();
		}
		$("step4-results").style.display = "";
		$("step4-results").childNodes[0].childNodes[1].style.display = "";
		$("step4-results").childNodes[0].childNodes[1].removed = false;
	},

	useParameters : function() {
		var checked = false;
		if ($("step3-use").checked) {
			checked = true;
		}

		for ( var i = 0; i < this.preparedPathSteps.length; i++) {
			if (this.preparedPathSteps[i] != "null") {
				if($("s3-use" + i).checked != checked){
					if(checked){
						this.numberOfUsedParameters += 1;
					} else {
						this.numberOfUsedParameters -= 1;
					}
				}
				
				$("s3-use" + i).checked = checked;
			}
		}
		
		//handle alias icon
		if(this.numberOfUsedParameters == 0){
			$("step-3-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_grey.png";
			$("step-3-alias-generate-button").style.cursor = "default";
		} else {
			$("step-3-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_go.png";
			$("step-3-alias-generate-button").style.cursor = "pointer";
		}
	},

	useResults : function() {
		var checked = false;
		if ($("step4-use").checked) {
			checked = true;
		}

		var offset = 0;
		for ( var i = 0; i < this.preparedRPathSteps.length; i++) {
			if (this.preparedPathSteps[i] != "null") {
				if($("s4-use" + (i + offset)).checked != checked){
					if(checked){
						this.numberOfUsedResultParts += 1;
					} else {
						this.numberOfUsedResultParts -= 1;
					}
				}
				
				$("s4-use" + (i + offset)).checked = checked;
			}
		}
		
		//handle alias icon
		if(this.numberOfUsedResultParts == 0){
			$("step-4-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_grey.png";
			$("step-4-alias-generate-button").style.cursor = "default";
		} else {
			$("step-4-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_go.png";
			$("step-4-alias-generate-button").style.cursor = "pointer";
		}
	},

	getRPath : function(i) {
		var rPath = "";
		for (k = 0; k < this.preparedRPathSteps[i].length; k++) {
			var rPathStep = "//";

			if (k > 1) {
				rPathStep = "/";
			}
			rPathStep += this.preparedRPathSteps[i][k]["value"];

			if (rPathStep.lastIndexOf("(") > 0) {
				rPathStep = rPathStep.substr(0, rPathStep.lastIndexOf("(") - 1);
			}
			// if (rPathStep.lastIndexOf("[") > 0) {
			// rPathStep = rPathStep.substring(0, rPathStep
			// .lastIndexOf("["));
			// rPathStep += "[";
			// rPathStep += $("step4-arrayinput-" + i + "-" + k).value;
			// rPathStep += "]";
			// }
			if (rPathStep != "/" && rPathStep != "//") {
				rPath += rPathStep;
			}
		}

		return rPath;
	},

	updateResultsUnmatched : function(rParts) {
		var sepRow = document.createElement("tr");
		sepRow.id = "step4-separatorRow";
		var placeHolder = document.createElement("td");
		placeHolder.style.height = "15px";
		placeHolder.style.borderBottomStyle = "solid";
		placeHolder.style.borderBottomWidth = "2px";
		placeHolder.setAttribute("colspan", "4");
		sepRow.appendChild(placeHolder);
		this.resultContainer.firstChild.appendChild(sepRow);
		sepRow = document.createElement("tr");
		placeHolder = document.createElement("td");
		placeHolder.style.height = "15px";
		placeHolder.setAttribute("colspan", "4");
		sepRow.appendChild(placeHolder);
		this.resultContainer.firstChild.appendChild(sepRow);

		var rows = this.resultContainer.firstChild.childNodes.length;
		var offset = 0;
		var rememberedNormalRPs = new Array();
		var rememberedPath = "";
		for ( var i = 0; i < rParts.length; i++) {
			rParts[i]["path"] = rParts[i]["path"].substring(11,
					rParts[i]["path"].length);
			var subPath = false;
			if (rParts[i]["json"] != "##" || rParts[i]["xpath"] != "##") {
				subPath = true;
			}

			var createRow = true;
			var useResultPart = true;
			if (subPath) {
				if (rParts[i]["path"] == rememberedPath) {
					createRow = false;
					offset += 1;
				} else {
					offset = 0;
					useResultPart = false;
				}

			} else {
				offset = 0;
			}

			if (createRow) {
				rememberedNormalRPs.push(rows + i);
				var row = document.createElement("tr");
				row.id = "step4-resultRow-" + (rows + i);

				var td1 = document.createElement("td");
				var aliasInput = document.createElement("input");
				aliasInput.style.width = "100%";
				aliasInput.value = rParts[i]["path"];
				td1.appendChild(aliasInput);
				row.appendChild(td1);

				var resultTD2 = document.createElement("td");
				resultTD2.id = "step4-resultTD05-" + (rows + i);
				resultTD2.style.textAlign = "right";
				row.appendChild(resultTD2);
				var useInput = document.createElement("input");
				useInput.id = "s4-use" + i + rows;
				useInput.type = "checkbox";
				if (useResultPart) {
					useInput.checked = true;
				}
				resultTD2.appendChild(useInput);

				var resultTD3 = document.createElement("td");
				resultTD3.id = "step4-resultTD2-" + (i + rows);
				row.appendChild(resultTD3);
				var aliasInput = document.createElement("input");
				aliasInput.id = "s4-alias" + (i + rows);
				if (useResultPart) {
					aliasInput.value = rParts[i]["alias"];
				}
				aliasInput.size = "25";
				aliasInput.maxLength = "40";
				resultTD3.appendChild(aliasInput);

				var resultTD4 = document.createElement("td");
				resultTD4.id = "step4-resultTD3-" + (i + rows);
				row.appendChild(resultTD4);
				var subPathButton = document.createElement("input");
				subPathButton.id = "s4-add-subpath" + (i + rows);
				subPathButton.type = "button";
				subPathButton.value = diLanguage
						.getMessage('smw_wws_add_subpath');
				subPathButton.setAttribute("onclick",
						"webServiceSpecial.addSubPath(" + (rows + i) + ")");
				subPathButton.style.cursor = "pointer";
				resultTD4.appendChild(subPathButton);

				this.resultContainer.firstChild.appendChild(row);
			}

			if (subPath) {
				this.addSubPath(rows + i - offset);
				if (createRow) {
					rows += 1;
					offset += 1;
				}
				$("step4-results").firstChild.childNodes[rows + i].childNodes[1].firstChild.value = rParts[i]["alias"];
				if (rParts[i]["json"] != "##") {
					$("step4-results").firstChild.childNodes[rows + i].childNodes[0].childNodes[1].value = "json";
					$("step4-results").firstChild.childNodes[rows + i].childNodes[0].childNodes[2].value = rParts[i]["json"];
				} else {
					$("step4-results").firstChild.childNodes[rows + i].childNodes[0].childNodes[1].value = "xpath";
					$("step4-results").firstChild.childNodes[rows + i].childNodes[0].childNodes[2].value = rParts[i]["xpath"];
				}
			}

			rememberedPath = rParts[i]["path"];
		}

		// update next siblings
		for (i = 0; i < rememberedNormalRPs.length; i++) {
			var nextSibling = $("step4-resultRow-" + rememberedNormalRPs[i + 1]);
			$("step4-resultRow-" + rememberedNormalRPs[i]).tempNextSibling = nextSibling;
		}

		// remove placegolder if no rows were added
		if (rows == this.resultContainer.firstChild.childNodes.length) {
			this.resultContainer.firstChild.childNodes[rows - 1].style.display = "none";
			this.resultContainer.firstChild.childNodes[rows - 2].style.display = "none";
		}
	},

	appendRESTSubParameters : function(id) {
//		// add remove option
		option = document.createElement("option");
		text = document.createTextNode(diLanguage
			.getMessage('smw_wws_remove_subparameters'));
		option.appendChild(text);
		option.value = diLanguage.getMessage('smw_wws_remove_subparameters');

		var select = $("step3-parameters").childNodes[0].childNodes[id].childNodes[4].childNodes[0];
		select.removeChild(select.childNodes[2]);
		select.appendChild(option);

		//add subparameter row
		$("step3-parameters").childNodes[0].childNodes[id].hasSubParameter = true;
		
		var row = document.createElement("tr");
		var td = document.createElement("td");
		td.style.verticalAlign = "top";

		var span = document.createElement("span");
		var text = document.createTextNode(diLanguage.getMessage('smw_wws_subparameters'));
		span.appendChild(text);
		td.appendChild(span);
		row.appendChild(td);

		td = document.createElement("td");
		td.setAttribute("colspan", "4");
		var textArea = document.createElement("textarea");
		textArea.setAttribute("rows", "2");
		td.appendChild(textArea);
		row.appendChild(td);

		$("step3-parameters").childNodes[0].childNodes[id].parentNode
			.insertBefore(
					row,
					$("step3-parameters").childNodes[0].childNodes[id].nextSibling);

	},
	
	removeRESTSubParameters : function(id) {
		// add remove option
		option = document.createElement("option");
		text = document.createTextNode(diLanguage
			.getMessage('smw_wws_add_subparameters'));
		option.appendChild(text);
		option.value = diLanguage.getMessage('smw_wws_add_subparameters');

		var select = $("step3-parameters").childNodes[0].childNodes[id].childNodes[4].childNodes[0];
		select.removeChild(select.childNodes[2]);
		select.appendChild(option);

		//add subparameter row
		$("step3-parameters").childNodes[0].childNodes[id].hasSubParameter = false;
		
		$("step3-parameters").childNodes[0].childNodes[id].parentNode
			.removeChild($("step3-parameters").childNodes[0].childNodes[id].nextSibling);
	},
	
	appendSubParameters : function(id) {
		// add remove option
		$("step3-paramRow-" + id).childNodes[5].childNodes[0].value = diLanguage
			.getMessage('smw_wws_remove_subparameters');
		$("step3-paramRow-" + id).childNodes[5].childNodes[0].setAttribute("onclick",
				"webServiceSpecial.removeSubParameters(" + id + ")");
		
		//add subparameter row
		$("step3-paramRow-" + id).hasSubParameter = true;
		
		var row = document.createElement("tr");
		var td = document.createElement("td");
		td.style.verticalAlign = "top";

		var span = document.createElement("span");
		var text = document.createTextNode(diLanguage
				.getMessage('smw_wws_subparameters'));
		span.appendChild(text);
		td.appendChild(span);
		row.appendChild(td);

		td = document.createElement("td");
		td.setAttribute("colspan", "5");
		var textArea = document.createElement("textarea");
		textArea.setAttribute("rows", "2");
		td.appendChild(textArea);
		row.appendChild(td);

		if(id == this.preparedPathSteps.length - 1){
			$("step3-paramRow-" + id).parentNode.appendChild(row);
		} else {
			$("step3-paramRow-" + id).parentNode
				.insertBefore(
						row,
						$("step3-paramRow-" + id).nextSibling);
		}

	},
	
	removeSubParameters : function(id) {
		// add remove option
		$("step3-paramRow-" + id).childNodes[5].childNodes[0].value = diLanguage
			.getMessage('smw_wws_add_subparameters');
		$("step3-paramRow-" + id).childNodes[5].childNodes[0].setAttribute("onclick",
			"webServiceSpecial.appendSubParameters(" + id + ")");
	
		//add subparameter row
		$("step3-paramRow-" + id).hasSubParameter = false;

		$("step3-paramRow-" + id).parentNode
			.removeChild($("step3-paramRow-" + id).nextSibling);
	},
	
	useResultPart : function(event){
		var node = Event.element(event);
		var oldNumber = this.numberOfUsedResultParts;
		
		if(node.checked){
			this.numberOfUsedResultParts += 1;
		} else {
			this.numberOfUsedResultParts -= 1;
		}
		
		if(this.numberOfUsedResultParts == 0 && oldNumber == 1){
			$("step-4-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_grey.png";
			$("step-4-alias-generate-button").style.cursor = "default";
		} else if(this.numberOfUsedResultParts == 1 && oldNumber == 0){
			$("step-4-alias-generate-button").src = wgScriptPath
			+ "/extensions/DataImport/skins/webservices/Pencil_go.png";
			$("step-4-alias-generate-button").style.cursor = "pointer";
		}
	},
	
	useParameter : function(event){
		var node = Event.element(event);
		var oldNumber = this.numberOfUsedParameters;
		
		if(node.checked){
			this.numberOfUsedParameters += 1;
		} else {
			this.numberOfUsedParameters -= 1;
		}
		
		if(this.numberOfUsedParameters == 0 && oldNumber == 1){
			$("step-3-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_grey.png";
			$("step-3-alias-generate-button").style.cursor = "default";
		} else if(this.numberOfUsedParameters == 1 && oldNumber == 0){
			$("step-3-alias-generate-button").src = wgScriptPath
				+ "/extensions/DataImport/skins/webservices/Pencil_go.png";
			$("step-3-alias-generate-button").style.cursor = "pointer";
		}
	}
}

var webServiceSpecial;
if (webServiceSpecial == undefined) {
	webServiceSpecial = new DefineWebServiceSpecial();
}

Event.observe(window, 'load', webServiceSpecial.editWWSD
		.bindAsEventListener(webServiceSpecial));