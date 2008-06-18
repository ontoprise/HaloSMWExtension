var DefineWebServiceSpecial = Class.create();

DefineWebServiceSpecial.prototype = {
	initialize : function() {
	},

	processStep1 : function() {
		var uri = document.getElementById("step1-uri").value;
		sajax_do_call("smwf_ws_processStep1", [ uri ],
				this.processStep1CallBack.bind(this));
	},

	processStep1CallBack : function(request) {
		var existingOptions = document.getElementById("step2-methods").childNodes;
		for ( var i = 0; i < existingOptions.length; i++) {
			document.getElementById("step2-methods").removeChild(
					existingOptions[i]);
		}

		var wsMethods = request.responseText.split(";");
		for (i = 0; i < wsMethods.length; i++) {
			var option = document.createElement("option");
			var mName = document.createTextNode(wsMethods[i]);
			option.appendChild(mName);
			document.getElementById("step2-methods").appendChild(option);
		}
		document.getElementById("step2").style.visibility = "visible";
		document.getElementById("menue-step2").style.fontWeight = "bold";
	},

	processStep2 : function() {
		var method = document.getElementById("step2-methods").value;
		var uri = document.getElementById("step1-uri").value;
		sajax_do_call("smwf_ws_processStep2", [ uri, method ],
				this.processStep2CallBack.bind(this));
	},

	processStep2CallBack : function(request) {
		var wsParameters = request.responseText.split(";");

		for (i = 0; i < wsParameters.length; i++) {
			var paramRow = document.createElement("tr");
			document.getElementById("step3-parameters").childNodes[0]
					.appendChild(paramRow);

			var paramTD0 = document.createElement("td");
			paramRow.appendChild(paramTD0);

			var paramPath = document.createElement("span");
			var paramPathText = document.createTextNode(wsParameters[i]);
			paramPath.appendChild(paramPathText);
			paramTD0.appendChild(paramPath);

			var paramPathId = document.createAttribute("id");
			paramPathId.nodeValue = "s3-path" + i;
			paramPath.setAttributeNode(paramPathId);

			var paramPathStyle = document.createAttribute("style");
			paramPathStyle.nodeValue = "padding-left: 40px";
			paramPath.setAttributeNode(paramPathStyle);

			var paramTD1 = document.createElement("td");
			paramRow.appendChild(paramTD1);

			// var aliasSpan = document.createElement("span");
			// var aliasSpanText = document.createTextNode(" alias: ");
			// aliasSpan.appendChild(aliasSpanText);
			// paramTD1.appendChild(aliasSpan);

			var aliasInput = document.createElement("input");

			var aliasInputId = document.createAttribute("id");
			aliasInputId.nodeValue = "s3-alias" + i;
			aliasInput.setAttributeNode(aliasInputId);

			var aliasInputSize = document.createAttribute("size");
			aliasInputSize.nodeValue = "15";
			aliasInput.setAttributeNode(aliasInputSize);

			var aliasInputMax = document.createAttribute("smaxlength");
			aliasInputMax.nodeValue = "40";
			aliasInput.setAttributeNode(aliasInputMax);

			paramTD1.appendChild(aliasInput);

			var paramTD2 = document.createElement("td");
			paramRow.appendChild(paramTD2);

			// var optionalSpan = document.createElement("span");
			// var optionalSpanText = document.createTextNode(" optional: ");
			// optionalSpan.appendChild(optionalSpanText);
			// paramTD2.appendChild(optionalSpan);
			
			var optionalRadio1 = document.createElement("input");
			
			var optionalRadio1Id = document.createAttribute("id");
			optionalRadio1Id.nodeValue = "s3-optional-true" + i;
			optionalRadio1.setAttributeNode(optionalRadio1Id);
			
			var optionalRadio1Name = document.createAttribute("name");
			optionalRadio1Name.nodeValue = "s3-optional-radio" + i;
			optionalRadio1.setAttributeNode(optionalRadio1Name);

			var optionalRadio1Type = document.createAttribute("type");
			optionalRadio1Type.nodeValue = "radio";
			optionalRadio1.setAttributeNode(optionalRadio1Type);
			
			var optionalRadio1Value = document.createAttribute("value");
			optionalRadio1Value.nodeValue = "yes";
			optionalRadio1.setAttributeNode(optionalRadio1Value);

			paramTD2.appendChild(optionalRadio1);
			
			var optionalRadio1Span = document.createElement("span");
			var optionalRadio1TextY = document.createTextNode("Yes");
			optionalRadio1Span.appendChild(optionalRadio1TextY);
			paramTD2.appendChild(optionalRadio1Span);
			
var optionalRadio2 = document.createElement("input");
			
			var optionalRadio2Id = document.createAttribute("id");
			optionalRadio2Id.nodeValue = "s3-optional-false" + i;
			optionalRadio2.setAttributeNode(optionalRadio2Id);
			
			var optionalRadio2Name = document.createAttribute("name");
			optionalRadio2Name.nodeValue = "s3-optional-radio" + i;
			optionalRadio2.setAttributeNode(optionalRadio2Name);

			var optionalRadio2Type = document.createAttribute("type");
			optionalRadio2Type.nodeValue = "radio";
			optionalRadio2.setAttributeNode(optionalRadio2Type);
			
			var optionalRadio2Value = document.createAttribute("value");
			optionalRadio2Value.nodeValue = "yes";
			optionalRadio2.setAttributeNode(optionalRadio2Value);

			paramTD2.appendChild(optionalRadio2);
			
			var optionalRadio2Span = document.createElement("span");
			var optionalRadio2TextN = document.createTextNode("No");
			optionalRadio2Span.appendChild(optionalRadio2TextN);
			paramTD2.appendChild(optionalRadio2Span);
			
			var paramTD3 = document.createElement("td");
			paramRow.appendChild(paramTD3);

			// var defaultSpan = document.createElement("span");
			// var defaultSpanText = document.createTextNode(" default value:
			// ");
			// defaultSpan.appendChild(defaultSpanText);
			// paramTD3.appendChild(defaultSpan);

			var defaultInput = document.createElement("input");

			var defaultInputId = document.createAttribute("id");
			defaultInputId.nodeValue = "s3-default" + i;
			defaultInput.setAttributeNode(defaultInputId);

			var defaultInputSize = document.createAttribute("size");
			defaultInputSize.nodeValue = "15";
			defaultInput.setAttributeNode(defaultInputSize);

			var defaultInputMax = document.createAttribute("smaxlength");
			defaultInputMax.nodeValue = "40";
			defaultInput.setAttributeNode(defaultInputMax);

			paramTD3.appendChild(defaultInput);

			document.getElementById("step3").style.visibility = "visible";
			document.getElementById("menue-step3").style.fontWeight = "bold";
		}
	},

	processStep3 : function() {
		var method = document.getElementById("step2-methods").value;
		var uri = document.getElementById("step1-uri").value;
		var parameters = "";
//		var parameterSpecifications = document
//				.getElementById("step3-parameters").childNodes[0].childNodes;
//		for ( var i = 0; i < parameterSpecifications.length - 1; i++) {
//			parameters += document.getElementById("s3-alias" + i).value + ";";
//			parameters += document.getElementById("s3-optional" + i).value
//					+ ";";
//			parameters += document.getElementById("s3-default" + i).value + ";";
//		}

		sajax_do_call("smwf_ws_processStep3", [ uri, method, parameters],
				this.processStep3CallBack.bind(this));
	},

	processStep3CallBack : function(request) {
		var wsResults = request.responseText.split(";");

		for (i = 0; i < wsResults.length; i++) {
			var resultRow = document.createElement("tr");
			document.getElementById("step4-results").childNodes[0]
					.appendChild(resultRow);

			var resultTD0 = document.createElement("td");
			resultRow.appendChild(resultTD0);

			var resultPath = document.createElement("span");
			var resultPathText = document.createTextNode(wsResults[i]);
			resultPath.appendChild(resultPathText);
			resultTD0.appendChild(resultPath);

			var resultPathStyle = document.createAttribute("style");
			resultPathStyle.nodeValue = "padding-left: 40px";
			resultPath.setAttributeNode(resultPathStyle);

			var resultTD1 = document.createElement("td");
			resultRow.appendChild(resultTD1);

			// var aliasSpan = document.createElement("span");
			// var aliasSpanText = document.createTextNode(" alias: ");
			// aliasSpan.appendChild(aliasSpanText);
			// resultTD1.appendChild(aliasSpan);

			var aliasInput = document.createElement("input");

			var aliasInputId = document.createAttribute("id");
			aliasInputId.nodeValue = "s4-alias" + i;
			aliasInput.setAttributeNode(aliasInputId);

			var aliasInputSize = document.createAttribute("size");
			aliasInputSize.nodeValue = "15";
			aliasInput.setAttributeNode(aliasInputSize);

			var aliasInputMax = document.createAttribute("smaxlength");
			aliasInputMax.nodeValue = "40";
			aliasInput.setAttributeNode(aliasInputMax);

			resultTD1.appendChild(aliasInput);

		}
		document.getElementById("step4").style.visibility = "visible";
		document.getElementById("menue-step4").style.fontWeight = "bold";
	},

	processStep4 : function() {
		document.getElementById("step5").style.visibility = "visible";
		document.getElementById("menue-step5").style.fontWeight = "bold";
	},

	processStep5 : function() {
		var result = "<WebService>\n";

		var uri = document.getElementById("step1-uri").value;
		result += "<uri name=\"" + uri + "\" />";

		result += "<protocol>SOAP</protocol>";

		var method = document.getElementById("step2-methods").value;
		result += "<method name=\"" + method + "\" />";

		for ( var i = 0; i < document.getElementById("step3-parameters").childNodes[0].childNodes.length - 1; i++) {
			result += "<parameter name=\""
					+ document.getElementById("s3-alias" + i).value + "\" ";
			result += " optional=\""
					+ document.getElementById("s3-optional" + i).value + "\" ";
			result += " defaultValue=\""
					+ document.getElementById("s3-default" + i).value + "\" ";
			result += " path=\"" + "\" />\n";
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

		var p = document.createElement("p");
		var resultText = document.createTextNode(result);
		p.appendChild(resultText);
		document.getElementById("step4-results").appendChild(p);
	}
}

webServiceSpecial = new DefineWebServiceSpecial();
