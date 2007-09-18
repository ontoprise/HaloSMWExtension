var Query = Class.create();
Query.prototype = {

	initialize:function(id, parent, name){
		//create basic query xml structure
		this.id = id;
		this.parent = parent;
		this.name = name;
		this.hasSubquery = false;
		this.categories = Array();
		this.instances = Array();
		this.properties = Array();
		this.subqueryIds = Array();
	},

	addCategoryGroup:function(cat, oldid){
		if(oldid==null)
			this.categories[this.categories.length] = cat;
		else
			this.categories[oldid] = cat;
	},

	addInstanceGroup:function(ins, oldid){
		if(oldid==null)
			this.instances[this.instances.length] = ins;
		else
			this.instances[oldid] = ins;
	},

	addPropertyGroup:function(pgroup, subIds, oldid){
		if(oldid == null)
			this.properties.push(pgroup);
		else
			this.properties[oldid] = pgroup;
		if (subIds.length > 0){
			this.hasSubquery = true;
			for(var i=0; i<subIds.length; i++){
				this.subqueryIds.push(subIds[i]);
			}
		}
	},

	hasSubqueries:function(){
		return this.hasSubquery;
	},

	updateTreeXML:function(){
		var treexml = '<?xml version="1.0" encoding="UTF-8"?>';
		treexml += '<treeview title=" Query"><folder title=" ' + this.name + '" code="root" expanded="true" img="question.gif">';
		for(var i=0; i<this.categories.length; i++){
			treexml += '<folder title="' + gLanguage.getMessage('QI_CATEGORIES') + '" code="categories' + i +'" expanded="true" img="category.gif">';
			for(var j=0; j<this.categories[i].length; j++){
					treexml += '<leaf title=" ' + this.categories[i][j] + '" code="category' + i + '-' + j + '" img="blue_ball.gif"/>';
			}
			treexml += '</folder>';
		}
		for(var i=0; i<this.instances.length; i++){
			treexml += '<folder title="' + gLanguage.getMessage('QI_INSTANCES') + '" code="instances' + i +'" expanded="true" img="instance.gif">';
			for(var j=0; j<this.instances[i].length; j++){
				treexml += '<leaf title=" ' + this.instances[i][j] + '" code="instance' + i + '-' + j + '" img="red_ball.gif"/>';
			}
			treexml += '</folder>';
		}
		for(var i=0; i<this.properties.length; i++){
			treexml += '<folder title=" ' + this.properties[i].getName() + '" code="properties' + i +'" expanded="true" img="property.gif">';
			propvalues = this.properties[i].getValues();
			for(var j=0; j<propvalues.length; j++){
				if(propvalues[j][0] == "subquery")
					treexml += '<leaf title=" ' + gLanguage.getMessage('QI_PAGE') + ' = ' + gLanguage.getMessage('QI_SUBQUERY') + ' ' + propvalues[j][2] + '" code="property' + i + '-' + j + '" img="yellow_ball.gif"/>';
				else {
					var res = "";
					switch(propvalues[j][1]){
						case "<=":
							res = "&lt;=";
							break;
						case ">=":
							res = "&gt;=";
							break;
						default:
							res = propvalues[j][1];
							break;
					}
					treexml += '<leaf title=" ' + propvalues[j][0] + " " + res + " " + propvalues[j][2] + '" code="property' + i + '-' + j + '" img="yellow_ball.gif"/>';
				}
			}
			treexml += '</folder>';
		}
		treexml += '</folder></treeview>';
		updateQueryTree(treexml);
	},

	getAskText:function(){
		var asktext = "";
		for(var i=0; i<this.categories.length; i++){
			asktext += "[[Category:";
			for(var j=0; j<this.categories[i].length; j++){
				asktext += this.categories[i][j];
				if(j<this.categories[i].length-1){
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		for(var i=0; i<this.instances.length; i++){
			asktext += "[[";
			for(var j=0; j<this.instances[i].length; j++){
				asktext += this.instances[i][j];
				if(j<this.instances[i].length-1){
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		for(var i=0; i<this.properties.length; i++){
			if(this.properties[i].isShown()){
				asktext += "[[" + this.properties[i].getName() + ":=*]]";
			}
			asktext += "[[" + this.properties[i].getName() + ":=";
			if(this.properties[i].getArity() > 2){
				var vals = this.properties[i].getValues();
				for(var j=0; j<vals.length; j++){
					if(j!=0)
						asktext += ";";
					if(vals[j][1]!="=")
						asktext += vals[j][1].substring(0,1);
					asktext += vals[j][2];
				}
			} else {
				var vals = this.properties[i].getValues();
				for(var j=0; j<vals.length; j++){
					if(j!=0)
						asktext += "||";
					if(vals[j][1]!= "=")
						asktext += vals[j][1].substring(0,1);
					if(vals[j][0] == "subquery")
						asktext += "Subquery:" + vals[j][2] + ":";
					else
						asktext += vals[j][2];
				}
			}
			asktext += "]]";
		}
		return asktext;
	},

	getName:function(){
		return this.name;
	},

	getSubqueryIds:function(){
		return this.subqueryIds;
	},

	getParent:function(){
		return this.parent;
	},

	getCategoryGroup:function(id){
		return this.categories[id];
	},

	getInstanceGroup:function(id){
		return this.instances[id];
	},

	getPropertyGroup:function(id){
		return this.properties[id];
	},

	getAllProperties:function(){
		return this.properties;
	},

	removeCategoryGroup:function(id){
		if(id < this.categories.length-1)
			this.categories[id]= this.categories.pop();
		else
			this.categories.pop();
	},

	removeInstanceGroup:function(id){
		if(id < this.instances.length-1)
			this.instances[id]= this.instances.pop();
		else
			this.instances.pop();
	},

	removePropertyGroup:function(id){
		if(id < this.properties.length-1){
			this.properties[id]= this.properties.pop();
			}
		else{
			this.properties.pop();
		}
	}


};