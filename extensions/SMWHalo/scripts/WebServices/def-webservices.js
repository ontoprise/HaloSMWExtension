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
			var paramPath = document.createElement("p");
			var paramPathText = document.createTextNode(wsParameters[i]);
			paramPath.appendChild(paramPathText);
			document.getElementById("step3").appendChild(paramPath);

			var aliasSpan = document.createElement("span");
			var aliasSpanText = document.createTextNode(" alias: ");
			aliasSpan.appendChild(aliasSpanText);
			document.getElementById("step3").appendChild(aliasSpan);

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

			document.getElementById("step3").appendChild(aliasInput);

			var optionalSpan = document.createElement("span");
			var optionalSpanText = document.createTextNode(" optional: ");
			optionalSpan.appendChild(optionalSpanText);
			document.getElementById("step3").appendChild(optionalSpan);

			var optionalSelect = document.createElement("select");
			var optionalSelectId = document.createAttribute("id");
			optionalSelectId.nodeValue = "s3-optional" + i;
			optionalSelect.setAttributeNode(optionalSelectId);
			
			var optionalSelectOptionY = document.createElement("option");
			var optionalSelectOptionYText = document.createTextNode("yes");
			optionalSelectOptionY.appendChild(optionalSelectOptionYText);
			optionalSelect.appendChild(optionalSelectOptionY);

			var optionalSelectOptionN = document.createElement("option");
			var optionalSelectOptionNText = document.createTextNode("no");
			optionalSelectOptionN.appendChild(optionalSelectOptionNText);
			optionalSelect.appendChild(optionalSelectOptionN);

			document.getElementById("step3").appendChild(optionalSelect);

			var defaultSpan = document.createElement("span");
			var defaultSpanText = document.createTextNode(" alias: ");
			defaultSpan.appendChild(defaultSpanText);
			document.getElementById("step3").appendChild(defaultSpan);
			
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

			document.getElementById("step3-parameters").appendChild(defaultInput);
		}
	}
}

webServiceSpecial = new DefineWebServiceSpecial();
