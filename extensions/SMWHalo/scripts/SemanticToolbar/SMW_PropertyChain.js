var PropertyChain = Class.create();

//todo: pending indicator
//todo: remove onclick attribute from first input

PropertyChain.prototype = {

	initialize : function(ruleName, rt) {
		this.ruleName = ruleName;
		this.ruleType = rt;
		this.chainCount = 0;
		smwhgPropertyChain = this;
		this.annotation = null;
		
	},

	createChain : function(){
		this.getCreateChainHtml();
	},
	
	getCreateChainHtml : function() {
		var bodyContent = $('bodyContent');
		bodyContent.hide();

		var headHtml = this.getHeadHtml(2);

		var ifHtml = this.getIfHtml();
		
		var buttonHtml = this.getButtonHtml();
		
		html = '<div id="pc-content" class="rules-complete-content">' 
			+ headHtml + ifHtml + buttonHtml + '</div>';
		
		new Insertion.After(bodyContent, html);
	

		$("pc-chain-input-0").value = gLanguage.getMessage('PC_enter_prop');
		var onFocusAtt = document.createAttribute("onfocus");
		onFocusAtt.value = 'smwhgPropertyChain.cleanFirstInput()';
		$("pc-chain-input-0").setAttributeNode(onFocusAtt);
		
		$("pc-chain-x1-0").style.fontWeight = "bold";
		$("pc-chain-x2-0").style.fontWeight = "bold";
		$("pc-chain-remove-0").style.visibility = "hidden";
	},
	
	cleanFirstInput : function(){
		if($("pc-chain-input-0").value == "Enter property"){
			$("pc-chain-input-0").value = "";
		}
	},

	getChainHtml : function(cCount) {
		var chainHtml = '<div style="margin-bottom: 5px"'  
			+ ' id="pc-chain-div-'
			+ cCount 
			+ '"><span id="pc-chain-x1-' + cCount
			+ '" style="margin-right: 5px">Article X<sub>'
			+ (cCount*1+1)
			+ '</sub></span>' 
			+ '<input style="margin-right: 5px" type="text" id="pc-chain-input-'
			+ cCount
			+ '" class="wickEnabled" typeHints="'
			+ SMW_CATEGORY_NS
			+ '" onkeypress ="smwhgPropertyChain.checkEnterKey(event, ' 
			+ cCount + ')"></input>' 
			+ '<span + id="pc-chain-x2-' + cCount
			+ '" style="margin-right: 5px; font-weight: bold">Article X<sub>'
			+ (cCount*1+2)
			+ '</sub></span>';
		
		chainHtml += '<img id="pc-chain-remove-'
			+ cCount
			+ '" onclick="smwhgPropertyChain.removeChain('
			+ cCount
			+ ')" src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png" style="cursor:pointer"/>'; 	
		
		chainHtml += '<br>' 
			+ '<img id="pc-chain-add-'
			+ cCount
			+ '" onclick="smwhgPropertyChain.addChain('
			+ cCount
			+ ')" src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"'
			+ ' style="margin-top: 3px; cursor:pointer"/>'; 
			+ '</div>'
		
		return chainHtml;
	},
	
	addChain : function(){
		var html = this.getChainHtml(this.chainCount);
		var lastDiv = $("pc-chain-div-"+(this.chainCount-1));
		new Insertion.After(lastDiv, html);
		$("pc-chain-remove-"+(this.chainCount-1)).style.visibility = "hidden";
		$("pc-chain-add-"+(this.chainCount-1)).style.display = "none";
		this.chainCount += 1;
		$("pc-propertyValue").firstChild.nodeValue = (this.chainCount*1+1);
		$("pc-chain-input-" + (this.chainCount-1)).focus();
		$("pc-chain-x2-" + (this.chainCount-2)).style.fontWeight = "normal";
		$("pc-chain-x2-" + (this.chainCount-1)).style.fontWeight = "bold";
	},
	
	removeChain : function(){
		var lastDiv = $("pc-chain-div-"+(this.chainCount-1));
		lastDiv.parentNode.removeChild(lastDiv);
		if(this.chainCount > 2){
			$("pc-chain-remove-"+(this.chainCount-2)).style.visibility = "visible";
		}
		$("pc-chain-add-"+(this.chainCount-2)).style.display = "block";
		this.chainCount -= 1;
		$("pc-propertyValue").firstChild.nodeValue = (this.chainCount*1+1);
		
		$("pc-chain-x2-" + (this.chainCount-1)).style.fontWeight = "bold";
	},
	
	serializeChain: function() {
		
		var xml;
		
		xml = '<?xml version="1.0" encoding="UTF-8"?>'
			  + '<SimpleRule>';
			  
		xml += '<head>';
		xml += '<property>'
			+ '<subject>X1</subject>'
			+ '<name>'
			+ wgTitle.replace(/ /g, '_')
			+ '</name>'
			+ '<variable>'
			+ "X" + (this.chainCount+1)
			+ '</variable>'
			+ '</property>'
			+ '</head>';
			  
		xml += '<body>';
		for (var i = 0; i < this.chainCount; i++) {
			var prop = $("pc-chain-input-"+i).value.replace(/ /g, '_');
			xml += '<property>'
				+ '<subject>'
				+ 'X'+(i+1)
				+ '</subject>'
				+ '<name>'
				+ prop
				+ '</name>'
				+ '<variable>'
				+ 'X'+(i+2)
				+ '</variable>'
				+ '</property>';
		}
		
		xml += '</body></SimpleRule>';
		
		return xml;
		
	},
	
	saveChain : function(){
		var serializedChain = this.serializeChain();
		
		sajax_do_call('smwf_sr_AddRule', 
		          [this.ruleName, serializedChain], 
		          this.saveChainCallBack.bind(this));
	},
	
	saveChainCallBack : function(request) {
		if (request.status == 200) {
			if ($('rule-name')) {
				this.ruleName = $('rule-name').value;
			}			
			
			var chainText = "\n\n" 
				+ '<rule hostlanguage="f-logic" '
				+ 'name="' + this.ruleName + '" '
				+ 'type="' + this.ruleType + '">' + "\n"
				+ request.responseText +
				"\n</rule>\n";
			 	
			$("pc-content").remove();
			
			$("bodyContent").show();
			
			if (this.annotation) {
				// update an existing annotation
				this.annotation.replaceAnnotation(chainText); 
			} else {
				var ei = new SMWEditInterface();
				ei.setValue(ei.getValue() + chainText);
			}
			ruleToolBar.fillList(true);
		}
	},
	
	checkEnterKey : function(event, cCount){
		if (event.which == 13) {
			if(cCount == this.chainCount-1){
				this.addChain();
			} else {
				$("pc-chain-input-" + (cCount +1)).focus();
			}
		}
	},
	
	editChain : function(ruleAnnotation) {
		var ruleText = ruleAnnotation.getRuleText();
		this.annotation = ruleAnnotation;
		sajax_do_call('smwf_sr_ParseRule',
		 	[this.ruleName, ruleText],
		    this.parseChainCallBack.bind(this));
	},
	
	parseChainCallBack : function(request) {
		if (request.status == 200) {
			var xml = request.responseText;
			if (xml == 'false') {
				//TODO
				return;
			}
			this.getEditChainHtml(xml);
		} else {
		}
	},
	
	/**
	 * Cancels editing or creating the rule. Closes the rule edit part of the UI and
	 * reopens the wiki text edit part.
	 *  
	 */
	cancel: function() {
		
		$('bodyContent').show();
		if ($('pc-content')) {
			$('pc-content').remove();
		}
			
	},
	
	getEditChainHtml : function(xml){
		var rule = GeneralXMLTools.createDocumentFromString(xml);
		
		var head = rule.getElementsByTagName("head")[0].childNodes;
		var body = rule.getElementsByTagName("body")[0].childNodes;
		this.chainCount = 0;
			
		var headName = head[0].childNodes[1].firstChild.nodeValue;
		
		var bodyContent = $('bodyContent');
		bodyContent.hide();

		var headHtml = this.getHeadHtml(2);

		var ifHtml = this.getIfHtml();
		
		var buttonHtml = this.getButtonHtml();
		
		html = '<div id="pc-content">' 
			+ headHtml + ifHtml + buttonHtml + '</div>';
		
		new Insertion.After(bodyContent, html);
	

		$("pc-chain-input-0").value = body[0].childNodes[1].firstChild.nodeValue;
		
		for (var i=1, n = body.length-1; i < n; ++i) {
			this.addChain();
			
			var propertyName = body[i].childNodes[1].firstChild.nodeValue; 
			$("pc-chain-input-" + (this.chainCount-1)).value = propertyName;
			
			$("pc-chain-x1-0").style.fontWeight = "bold";
			$("pc-chain-x2-" + (this.chainCount-1)).style.fontWeight = "bold";
			
			$("pc-chain-remove-0").style.visibility = "hidden";
		}
	},
	
	getHeadHtml : function(){
		var derive = gLanguage.getMessage('PC_DERIVE_BY');
		derive = derive.replace(/\$1/g, '<span class="rules-category">'+wgTitle+'</span>');
		
		var headHtml = '<div style="padding-bottom: 5px">' + derive + '</div>';
		
		headHtml += '<div id="pc-head" class="rules-frame" style="border-bottom:0px">'
			+ '<div id="headTitle" class="rules-title">'
			+ gLanguage.getMessage('SR_HEAD')
			+ '</div>'
			+ '<div id="headContent" class="rules-content" >';
		
		var headline = gLanguage.getMessage('PC_headline');
		headline = headline.replace(/\$1/g, '<b>' + wgTitle + '</b>');
		headline = headline.replace(/\$2/g, '<b>Article X<sub>1</sub></b>');
		headline = headline.replace(/\$3/g, '<b>Article X<sub><span id="pc-propertyValue">2</span></sub></b>');
		headline += '</span></div>';

		headHtml += headline;
		
		headHtml += '<div style="height:20px"></div>';
		
		return headHtml;
	},

	getIfHtml : function(){
		var ifHtml = '<div id="pc-if" class="rules-frame">'
			+ '<div class="rules-title">'
			+ gLanguage.getMessage('SR_BODY')
			+ '</div>'
			+ '<div class="rules-content">'
			+ this.getChainHtml(0)
			+ '</div></div>'; 
		
		this.chainCount += 1;
		
		ifHtml += '<div style="height:20px"></div>';
		
		return ifHtml;
	},
	
	getButtonHtml : function(){
		var buttonHtml = '<button id="pc-save-button"'
		+ 'onclick="smwhgPropertyChain.saveChain()"'
		+ 'style="float:left;">'
		+ gLanguage.getMessage('SR_SAVE_RULE')
		+ '</button>';
	
		return buttonHtml;
	}
}


var smwhgPropertyChain = null;
